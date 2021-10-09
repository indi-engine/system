<?php
class Indi_Queue_UsagesUpdate extends Indi_Queue_L10n_FieldToggleL10n {

    /**
     * Create `queueTask` entry and nested `queueChunk` entries
     *
     * @param $params
     * @return QueueTask_Row
     */
    public function chunk($params) {

        // Fetch usage map entries
        $considerRs = Indi::model('Consider')->fetchAll('`id` IN (' . im($params['considerIdA']) . ')');

        // Group usages by: affected field's id (`foreign` or `consider`) and then by `entityId`
        foreach ($considerRs as $considerR)
            if ($considerR->foreign($considerR->foreign ? 'foreign': 'consider')->storeRelationAbility == 'none') 
                $considerRA_byAffected
                    [$considerR->foreign ?: $considerR->consider]
                    [$considerR->entityId]
                    [$considerR->fieldId]
                        = $considerR->consider;
        
        // If no appropriate dependencies found - return    
        if (!$considerRA_byAffected) return;
        
        // Create `queueTask` entry
        $queueTaskR = Indi::model('QueueTask')->createRow(array(
            'title' => array_pop(explode('_', __CLASS__)),
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT)
        ), true);

        // Save `queueTask` entries
        $queueTaskR->save();

        // Foreach affected field
        foreach ($considerRA_byAffected as $fieldId_affected => $considerRA_byEntityId) {

            // Create `queueChunk` entry
            $queueChunkR_affected = Indi::model('QueueChunk')->createRow([
                'queueTaskId' => $queueTaskR->id,
                'queueState' => $queueTaskR->queueState,
                'where' => 'FALSE',
                'location' => $params['table'] . ':' . m($params['table'])->fields($fieldId_affected)->alias
            ], true);

            // Save `queueChunk` entry
            $queueChunkR_affected->save();

            // Update `queueTask` entry chunk counter
            $queueTaskR->chunk ++;
            $queueTaskR->basicUpdate(false, false);

            // Foreach entity, that affected-field's dependent-field is in
            foreach ($considerRA_byEntityId as $entityId => $considerRA) {

                // Get model instance and table name
                $m = m($entityId); $t = $m->table();

                // Foreach dependent-field
                foreach ($considerRA as $fieldId => $consider) {

                    // If it's a foreign-key field - skip
                    if ($m->fields($fieldId)->storeRelationAbility != 'none') continue;

                    // If it's consider-field is an enumset-field
                    if (($rel = $m->fields($consider)->rel()) && $rel->table() == 'enumset') {

                        // Get enumset alias
                        $alias = m('enumset')->fetchRow($params['entry'])->alias;

                        // Build WHERE clause
                        $where = $m->fields($consider)->storeRelationAbility == 'one'
                            ? '`' . $m->fields($consider)->alias . '` = "' . $alias . '"'
                            : 'FIND_IN_SET("' . $alias . '", `' . $m->fields($consider)->alias . '`)';

                    // Else ordinary behaviour
                    } else $where = '`' . $m->fields($consider)->alias . '` = "' . $params['entry'] . '"';

                    // Create `queueChunk` entry
                    $queueChunkR = Indi::model('QueueChunk')->createRow([
                        'queueTaskId' => $queueTaskR->id,
                        'queueChunkId' => $queueChunkR_affected->id,
                        'queueState' => $queueTaskR->queueState,
                        'where' => $where,
                        'location' => $t . ':' . $m->fields($fieldId)->alias
                    ], true);

                    // Mind that `section2action` entries have second field, that is fraction-dependent
                    // todo: implement more beautiful solution
                    if ($queueChunkR->location == 'section2action:title' && $m->fields($consider)->alias == 'actionId')
                        $queueChunkR->where .= ' AND FIND_IN_SET(`sectionId`, "' . m('Section')->fetchAll('`type` != "o"')->column('id', true) . '")';

                    // Save `queueChunk` entry
                    $queueChunkR->save();

                    // Increment `queueTask` entry's chunk counter
                    $queueTaskR->chunk ++;
                    $queueTaskR->basicUpdate(false, false);
                }
            }
        }

        // Return `queueTask` entry
        return $queueTaskR;
    }

    /**
     * Process queue items
     *
     * @param $queueTaskId
     */
    public function queue($queueTaskId) {

        // Get `queueTask` entry
        $queueTaskR = Indi::model('QueueTask')->fetchRow($queueTaskId);

        // If `queueState` is 'noneed' - do nothing
        if ($queueTaskR->queueState == 'noneed') return;

        // Require and instantiate Google Cloud Translation PHP API and
        $gapi = new Google\Cloud\Translate\V2\TranslateClient(array('key' => Indi::ini('lang')->gapi->key));

        // Update `stage` and `state`
        $queueTaskR->stage = 'queue';
        $queueTaskR->state = 'progress';
        $queueTaskR->queueState = 'progress';
        $queueTaskR->basicUpdate(false, false);

        // Get source and target languages
        $params = json_decode($queueTaskR->params);
        $source = $params->source;

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`queueState` != "finished"',
            'order' => '`queueState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // If it's parent chunk
            if ($queueChunkR->system('disabled')) continue;

            // Remember that we're going to count
            $queueChunkR->assign(array('queueState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "items"';

            // Target languages
            $targets = $params->source;

            // Check whether we should use setter method call instead of google translate api call
            $setter = $queueChunkR->queueChunkId && method_exists(m($table)->createRow(), $_ = 'set' . ucfirst($field)) ? $_ : false;

            // Get queue items by 50 entries at a time
            Indi::model('QueueItem')->batch(function(&$rs, &$deduct) use (&$queueTaskR, &$queueChunkR, &$gapi, $source, $targets, $table, $field, $setter) {

                // If chunk's field is a dependent field and setter method exists
                if ($setter) {

                    // Foreach chunk's `queueItem` entry
                    foreach ($rs as $idx => $r) {

                        // Get target entry
                        $te = m($table)->fetchRow($r->target);

                        // Backup current language
                        $_lang = Indi::ini('lang')->admin;

                        // Foreach target language
                        foreach (ar($targets) as $target) {

                            // Spoof current language
                            Indi::ini('lang')->admin = $target;

                            // Rebuild value
                            $te->{$setter}();

                            // Collect it
                            $resultByLang[$target][$idx] = $te->$field;
                        }

                        // Restore current language
                        Indi::ini('lang')->admin = $_lang;
                    }

                // Else
                } else {

                    // Get values
                    $values = $rs->column('value');

                    // Try to call Google Cloud Translate API
                    try {

                        // Foreach target language - make api call to google passing source values
                        foreach (ar($targets) as $target)
                            $resultByLang[$target] = array_column($target == $source ? $values : $gapi->translateBatch($values, [
                                'source' => $source,
                                'target' => $target,
                            ]), 'text');

                        // Catch exception
                    } catch (Exception $e) {

                        // Log error
                        ehandler(1, json_decode($e->getMessage())->error->message, __FILE__, __LINE__);

                        // Exit
                        exit;
                    }
                }

                // Foreach fetched `queueItem` entry
                foreach ($rs as $idx => $r) {

                    // Collect result for each target language
                    $result = new stdClass(); foreach ($targets ? $resultByLang : [] as $target => $byIdx) {
                        $result->$target = $byIdx[$idx]; $this->amendResult($result->$target, $r);
                    }

                    // Write translation result
                    //$r->assign(array('result' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT), 'stage' => 'queue'))->basicUpdate();

                    // Write updated result
                    $r->assign(array('result' => $result->$target, 'stage' => 'queue'))->basicUpdate();

                    // Increment `queueSize` prop on `queueChunk` entry and save it
                    $queueChunkR->queueSize ++; $queueChunkR->basicUpdate();

                    // Increment `queueSize` prop on `queueTask` entry and save it
                    $queueTaskR->queueSize ++; $queueTaskR->basicUpdate(false, false);

                    // Increment $deduct
                    $deduct ++;
                }

            }, $where, '`id` ASC', 50, true);

            // Remember that our try to count was successful
            $queueChunkR->assign(array('queueState' => 'finished'))->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'queueState' => 'finished'))->save();
    }

    /**
     * Apply results
     *
     * @param $queueTaskId
     */
    public function apply($queueTaskId) {

        // Get `queueTask` entry
        $queueTaskR = Indi::model('QueueTask')->fetchRow($queueTaskId);

        // Update `stage` and `state`
        $queueTaskR->stage = 'apply';
        $queueTaskR->state = 'progress';
        $queueTaskR->applyState = 'progress';
        $queueTaskR->basicUpdate(false, false);

        // Get params
        $params = json_decode($queueTaskR->params);

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`applyState` != "finished"',
            'order' => '`applyState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Skip parent entries
            if ($queueChunkR->system('disabled')) continue;

            // Remember that we're going to count
            $queueChunkR->assign(array('applyState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "queue"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            //
            $queueChunkR->fieldR = m($table)->fields($field);

            // Get queue items
            Indi::model('QueueItem')->batch(function(&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, $table, $field, $hasLD) {

                // Get cell's current value
                $json = Indi::db()->query('SELECT `:p` FROM `:p` WHERE `id` = :p', $field, $table, $r->target)->fetchColumn();

                // If cell value is not an empty string, but is not a json - force it to be json
                if ($queueChunkR->fieldR->l10n == 'y') {
                    $json = json_decode($json ?: '{}');
                    $json->{$params->source} = $r->result;
                    $value = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
                } else {
                    $value = $r->result;
                }

                // Update cell value
                Indi::db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $table, $field, $value, $r->target);

                // Write translation result
                $r->assign(array('stage' => 'apply'))->basicUpdate();

                // Reset batch offset
                $deduct ++;

                // Increment `applySize` prop on `queueChunk` entry and save it
                $queueChunkR->applySize ++; $queueChunkR->basicUpdate();

                // Increment `applySize` prop on `queueTask` entry and save it
                $queueTaskR->applySize ++; $queueTaskR->basicUpdate(false, false);

            }, $where, '`id` ASC');

            // Remember that our try to count was successful
            $queueChunkR->assign(array('applyState' => 'finished'))->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'applyState' => 'finished'))->save();

        // Delete `queueTask` entry
        //$queueTaskR->delete();
    }
}