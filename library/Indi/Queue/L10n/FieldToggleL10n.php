<?php
class Indi_Queue_L10n_FieldToggleL10n extends Indi_Queue_L10n {

    /**
     * @var array
     */
    public static $l10n = array();

    /**
     * Create queue chunks
     *
     * @param array $params
     */
    public function chunk($params) {

        // Create `queueTask` entry
        $queueTaskR = Indi::model('QueueTask')->createRow(array(
            'title' => 'L10n_' . array_pop(explode('_', __CLASS__)),
            'params' => json_encode($params),
            'queueState' => $params['toggle'] == 'n' ? 'noneed' : 'waiting'
        ), true);

        // Save `queueTask` entries
        $queueTaskR->save();

        // Get table and field
        list ($table, $field, $entry) = explode(':', $params['field']);

        //
        $fieldR = $entry ? cfgField($table, $entry, $field) : field($table, $field);

        // Create separate `queueChunk`-trees for each fraction
        foreach ($params['target'] as $fraction => $targets)
            $this->appendChunk($queueTaskR, entity($table), $fieldR, array(), $fraction);

        // Return
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
        $target = $params->target;

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', array(
            'where' => '`queueState` != "finished"',
            'order' => '`queueState` = "progress" DESC, `move`'
        )) as $queueChunkR) {

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            //
            if ($queueChunkR->queueChunkId) {

                // Split `location` on $table and $field
                list ($ptable, $pfield) = explode(':', $queueChunkR->foreign('queueChunkId')->location);

                //
                self::$l10n[$ptable][$pfield] = Indi::db()->query('
                    SELECT `target`, `result` FROM `queueItem` WHERE `queueChunkId` = "' . $queueChunkR->queueChunkId . '"
                ')->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            // If it's parent chunk
            if ($queueChunkR->system('disabled')) continue;

            // Remember that we're going to count
            $queueChunkR->assign(array('queueState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "items"';

            // Target languages
            $targets = $queueChunkR->location == $params->field && $params->rootTarget
                ? $params->rootTarget
                : $target->{$queueChunkR->fraction};

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
                            $resultByLang[$target] = array_column($gapi->translateBatch($values, array(
                                'source' => $source,
                                'target' => $target,
                            )), 'text');

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
                    $result = new stdClass(); foreach ($targets ? $resultByLang : array() as $target => $byIdx) {
                        $result->$target = $byIdx[$idx]; $this->amendResult($result->$target, $r);
                    }

                    // Write translation result
                    $r->assign(array('result' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT), 'stage' => 'queue'))->basicUpdate();

                    // Increment `queueSize` prop on `queueChunk` entry and save it
                    $queueChunkR->queueSize ++; $queueChunkR->basicUpdate();

                    // Increment `queueSize` prop on `queueTask` entry and save it
                    $queueTaskR->queueSize ++; $queueTaskR->basicUpdate(false, false);

                    // Increment $deduct
                    $deduct ++;
                }

            }, $where, '`id` ASC', 10, true);

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
        $params = json_decode($queueTaskR->params, true);

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', array(
            'where' => '`applyState` != "finished"',
            'order' => '`applyState` = "progress" DESC, `move`'
        )) as $queueChunkR) {

            // Skip parent entries
            if ($queueChunkR->system('disabled')) continue;

            // Remember that we're going to count
            $queueChunkR->assign(array('applyState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "' . ($params['toggle'] == 'n' ? 'items' : 'queue') . '"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Get `field` entry
            $fieldR = $table == 'enumset'
                ? m('Field')->fetchRow(Indi::rexm('~`fieldId` = "([0-9]+)"~', $queueChunkR->where, 1))
                : field($table, $field);

            // Convert column type to TEXT
            if ($params['toggle'] != 'n') {
                if ($table == 'param' && $field == 'cfgValue') {
                    list ($_table, $_field, $_entry) = explode(':', $params['field']);
                    $fieldR = cfgField($_table, $_entry, $_field, ['columnTypeId' => 'TEXT']);
                } else if ($table != 'enumset') {
                    field($table, $field, array('columnTypeId' => 'TEXT'));
                }
            }

            // Setup $hasLD flag
            $hasLD = $fieldR->hasLocalizedDependency();

            // Get queue items
            Indi::model('QueueItem')->batch(function(&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, $table, $field, $hasLD) {

                // If localization is going to turned Off - use `queueItem` entry's `value` as target value, else
                if ($params['toggle'] == 'n') $value = $r->value; else {

                    // Get cell's current value
                    $json = Indi::db()->query('SELECT `:p` FROM `:p` WHERE `id` = :p', $field, $table, $r->target)->fetchColumn();

                    // If cell value is not an empty string, but is not a json - force it to be json
                    if (!preg_match('~^{"~', $json)) $json = json_encode(array($params['source'] => $json), JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);

                    // Temporary thing
                    if (!is_array($result = json_decode($r->result, true)))
                        foreach (ar($params['targets']) as $target)
                            $result[$target] = preg_match('~[{,]"(' . $target . ')":"(.*?)"[,}]~', $r->result, $m)
                                ? stripslashes(str_replace('&quot;', '"', $m[2])) : '';

                    // Merge results
                    $data = array_merge(json_decode($json ?: '{}', true), $result);

                    // JSON-encode
                    $value = json_encode($data, JSON_UNESCAPED_UNICODE);
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
                $queueTaskR->applySize ++; $queueTaskR->basicUpdate(false,false);

            }, $where, '`id` ASC');

            if ($table == 'param' && $field == 'cfgValue' && $params['toggle'] == 'n') {
                if (!$queueChunkR->queueChunkId || !$hasLD) {
                    list ($_table, $_field, $_entry) = explode(':', $params['field']);
                    $fieldR = cfgField($_table, $_entry, $_field, ['columnTypeId' => 'VARCHAR(255)']);
                }
            }

            // Convert column type to TEXT
            if ($table != 'enumset' && $params['toggle'] == 'n' && !($table == 'param' && $field == 'cfgValue'))
                if (!$queueChunkR->queueChunkId || !$hasLD)
                    field($table, $field, array('columnTypeId' => 'VARCHAR(255)'));

            // Switch field's l10n-prop from intermediate to final value
            if ($params['toggle'] != 'n' || !$queueChunkR->queueChunkId || !$hasLD)
                $fieldR->assign(array('l10n' => $params['toggle'] ?: 'y'))->save();

            // Remember that our try to count was successful
            $queueChunkR->assign(array('applyState' => 'finished'))->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'applyState' => 'finished'))->save();
    }

    /**
     * Count symbols properly, e.g. multiply on target languages queantity,
     * as Google charges 20 USD per 1 million symbols
     *
     * @return int
     */
    public function itemsBytesMultiplier($params, $setter = false) {

        // If we're turning field's localization off, or using setter method instead of actual api call - return 0
        if ($params['toggle'] == 'n' || $setter) return 0;

        // If target-param is comma-separated string - return number of items
        if (is_string($params['target'])) return count(ar($params['target']));

        // Return 1 by default
        return 1;
    }

    /**
     * Amend result, got from Google translate API
     *
     * @param $result
     * @param $queueItemR
     * @return mixed
     */
    public function amendResult(&$result, $queueItemR) {

        // Convert &quot; to "
        $result = str_replace('&quot;', '"', $result);

        // Trim space after ending span
        $result = str_replace('"></span> ', '"></span>', $result);

        // Fix ' > ' problem
        if (preg_match('~[^\s]#[^\s]~', $queueItemR->value))  $result = preg_replace('~([^\s]) # ([^\s])~', '$1#$2', $result);

        // Fix tbq-translations
        if (preg_match('~^tbq: ([^,]+,) ([^,]+,) ([^,]+)$~', $result, $m)) $result = 'tbq:' . $m[1] . $m[2] . $m[3];
    }

    /**
     * @param $queueTaskR
     * @param $entityR
     * @param $fieldR_having_l10nY
     * @param $where
     */
    public function appendChunk(&$queueTaskR, $entityR, $fieldR, $where = array(), $fraction = 'none') {

        // Create parent `queueChunk` entry and setup basic props
        $queueChunkR = parent::appendChunk($queueTaskR, $entityR, $fieldR, $where);

        // Setup `fraction` and `where` props
        $queueChunkR->assign(array(
            'fraction' => $fraction,
            'where' => $this->getFractionChunkWHERE($fraction, $fieldR->id)
        ))->save();

        foreach (m('Consider')->fetchAll('"' . $fieldR->id . '" = IF(`foreign` = "0", `consider`, `foreign`)') as $considerR) {

            // Skip foreign-key fields
            if ($considerR->foreign('fieldId')->storeRelationAbility != "none") continue;

            // Create `queueChunk` entries for dependent fields
            $dependent = $this->appendChunk($queueTaskR,
                $considerR->foreign('fieldId')->foreign('entityId'),
                $considerR->foreign('fieldId'), array(), $fraction);

            // Make those to be child under parent `queueChunk` entry and setup `fraction` and `where` props
            $dependent->assign(array(
                'queueChunkId' => $queueChunkR->id,
                'fraction' => $fraction,
                'where' => $this->getFractionChunkWHERE($fraction, $considerR->fieldId)
            ))->save();
        }

        // Return `queueChunk` entry
        return $queueChunkR;
    }

    /**
     * Get WHERE clause for a field according to given fraction
     *
     * @param $fraction
     */
    public function getFractionChunkWHERE($fraction, $fieldId) {

        // Build queue class name
        $queueClassName = 'Indi_Queue_L10n_' . ucfirst($fraction);

        // Create queue class instance
        $queue = new $queueClassName();

        // Run first stage in dict-mode
        return $queue->chunk($fieldId);
    }
}