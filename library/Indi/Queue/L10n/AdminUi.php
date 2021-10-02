<?php
class Indi_Queue_L10n_AdminUi extends Indi_Queue_L10n {

    /**
     * @var array
     */
    public $master = null;

    /**
     * If of specific field, that we need to detect WHERE clause for
     *
     * @var bool
     */
    public $fieldId = false;

    /**
     * Create queue chunks
     *
     * @param array $params
     */
    public function chunk($params) {

        // If $params arg is an array
        if (is_array($params)) {

            // Create `queueTask` entry
            $queueTaskR = Indi::model('QueueTask')->createRow(array(
                'title' => 'L10n_' . array_pop(explode('_', get_class($this))),
                'params' => json_encode($params),
                'queueState' => $params['toggle'] == 'n' ? 'noneed' : 'waiting'
            ), true);

            // Save `queueTask` entries
            $queueTaskR->save();

        // Else assume is's and ID of specific field, that we need to detect WHERE clause for
        } else $this->fieldId = $params;

        // Dict of entities having purpose-distinction
        $master = $this->master;

        // Additional info for detecting entries
        foreach ($master as $table => &$info) $info += array(
            'entityId' => Indi::model($table)->id(),
            'instances' => Indi::model($table)->fetchAll('`' . $info['field'] . '` IN ("' . im(ar($info['value']), '","') . '")')->column('id', true) ?: 0
        );

        // Collect id of enties
        $masterIds = array_column($master, 'entityId');

        // Foreach `entity` entry, having `system` = "n" (e.g. project's custom entities)
        foreach (m('Entity')->fetchAll('`system` = "y"', '`table`') as $entityR) {

            // If current entity is a multi-fraction entity
            if ($master[$entityR->table])
                $where = '`' . $master[$entityR->table]['field'] . '`'
                    . (count($ar = ar($v = $master[$entityR->table]['value'])) > 1
                        ? ' IN ("' . im($ar, '","') . '")'
                        : ' = "' . $v . '"');

            // Else if entries of current entity are nested under at least one of multi-purpose entities entries
            else if ($fieldR = Indi::model($entityR->id)->fields()->select($masterIds, 'relation')->at(0))
                $where = '`' . $fieldR->alias . '` IN (' . $master[Indi::model($fieldR->relation)->table()]['instances'] . ')';

            // Else no WHERE clause
            else $where = false;

            /**
             * Additional WHERE clause for `changeLog` values
             */
            if ($entityR->table == 'changeLog') {

                // Get distinct `fieldId`-values
                $fieldIds = im(Indi::db()->query('SELECT DISTINCT `fieldId` FROM `changeLog`')->fetchAll(PDO::FETCH_COLUMN));

                // Collect ids of applicable fields
                $fieldIdA = [];
                foreach (m('Field')->fetchAll('`id` IN (0' . rif($fieldIds, ',$1') . ') AND (`l10n` = "y" OR `storeRelationAbility` != "none")') as $fieldR)
                    if ($fieldR->l10n == 'y' || $fieldR->rel()->titleField()->l10n == 'y')
                        $fieldIdA []= $fieldR->id;

                // Prepend `fieldId`-clause
                $where = '`fieldId` IN (0' . rif(im($fieldIdA), ',$1') . ') AND ' . $where;

            /**
             *
             */
            } else if ($entityR->table == 'noticeGetter') {
                $where .= ' AND `profileId` IN (' . $master['profile']['instances'] . ')';

            /**
             *
             */
            } else if ($entityR->table == 'param') {

                //
                if ($this->fieldId) {

                    //
                    if (m('Field')->fetchRow($this->fieldId)->entityId == $entityR->id) {

                        // Get config field ids
                        $cfgFieldIds = im(Indi::db()->query('
                            SELECT DISTINCT `p`.`cfgField` 
                            FROM `param` `p`, `field` `f`
                            WHERE 1
                              AND `p`.`cfgField` = `f`.`id`
                              AND `f`.`entityId` IN (' . $master['entity']['instances'] . ')
                        ')->fetchAll(PDO::FETCH_COLUMN)) ?: 0;

                        //
                        $where = '`cfgField` IN ('. $cfgFieldIds . ')';

                    //
                    } else {

                        // Get config field ids
                        $cfgFieldIds = m('Field')->fetchAll([
                            '`entityId` = "4"',
                            '`entry` != "0"',
                            '`id` = "' . $this->fieldId . '"',
                        ])->column('id', true) ?: 0;

                        // Get field ids
                        $fieldIds = im(Indi::db()->query('
                            SELECT DISTINCT `p`.`fieldId` 
                            FROM `param` `p`, `field` `f`
                            WHERE 1
                              AND `p`.`cfgField` IN (' . $cfgFieldIds . ')
                              AND `p`.`fieldId` = `f`.`id`
                              AND `f`.`entityId` IN (' . $master['entity']['instances'] . ')
                        ')->fetchAll(PDO::FETCH_COLUMN)) ?: 0;

                        //
                        $where = '`fieldId` IN ('. $fieldIds . ') AND `cfgField` IN (' . $cfgFieldIds . ')';
                    }

                //
                } else $where = 'FALSE';
            }

            // If $this->fieldId prop is set, it means that we're here
            // because of Indi_Queue_L10n_FieldToggleL10n->getFractionChunkWHERE() call
            // so out aim here to obtain WHERE clause for certain field's chunk,
            // and appendChunk() call will return WHERE clause rather than `queueChunk` instance
            if ($this->fieldId) {

                // If certain field is a regular field
                if ($fieldR_certain = m($entityR->id)->fields($this->fieldId)) {
                    if ($master['entity']['value'] == 'y' || ($where && $fieldR_certain->relation != 6))
                        return $this->appendChunk($queueTaskR, $entityR, $fieldR_certain, $where ? [$where] : []);

                // Else if it's a config field
                } else if ($fieldR_certain = m('Field')->fetchRow(['`id` = "' . $this->fieldId . '"', '`entry` != "0"'])) {
                    if ($entityR->table == 'param' && $where && $fieldR_certain->relation != 6) return $where;
                    else if ($entityR->table == 'enumset' && $fieldR_certain->relation == 6)
                        return $this->appendChunk($queueTaskR, $entityR, $fieldR_certain, $where ? [$where] : []);
                }

            // Foreach `field` entry, having `l10n` = "y"
            } else foreach (m($entityR->id)->fields()->select('y', 'l10n') as $fieldR_having_l10nY)
                if ($master['entity']['value'] == 'y' || ($where && $fieldR_having_l10nY->relation != 6))
                    $this->appendChunk($queueTaskR, $entityR, $fieldR_having_l10nY, $where ? [$where] : []);
        }

        // Order chunks to be sure that all dependent fields will be processed after their dependencies
        $this->orderChunks($queueTaskR->id);

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
        $source = json_decode($queueTaskR->params)->source;
        $target = json_decode($queueTaskR->params)->target;

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`queueState` != "finished"',
            'order' => '`queueState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->assign(array('queueState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "items"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Check whether we should use setter method call instead of google translate api call
            $setter = $field && method_exists(m($table)->createRow(), $_ = 'set' . ucfirst($field)) ? $_ : false;

            // If setter-method exists, it means that we won't be calling google translate api for this chunk,
            // but we'll be calculating translations by calling setter-methods, preliminary prodiving master-fields
            // translations, as at this monent they're available only within `queueItem` entries
            if ($setter) foreach ($this->considerChunks($queueTaskId, $queueChunkR->id) as $queueChunkI) {

                // Split `location` on $table and $field
                list ($ptable, $pfield) = explode(':', $queueChunkI['location']);

                // Load tarnslations
                foreach(Indi::db()->query('
                    SELECT `target`, `result` FROM `queueItem` WHERE `queueChunkId` = "' . $queueChunkI['queueChunkId'] . '"
                ')->fetchAll(PDO::FETCH_KEY_PAIR) as $entryId => $targetTranslation)
                    Indi_Queue_L10n_FieldToggleL10n::$l10n[$ptable][$pfield][$entryId] = json_encode([$target => $targetTranslation], JSON_UNESCAPED_UNICODE);
            }

            // Get queue items by 50 entries at a time
            Indi::model('QueueItem')->batch(function (&$rs, &$deduct) use (&$queueTaskR, &$queueChunkR, &$gapi, $source, $target, $table, $field, $setter) {

                // If chunk's field is a dependent field and setter method exists
                if ($setter) {

                    // Foreach chunk's `queueItem` entry
                    foreach ($rs as $idx => $r) {

                        // Get target entry
                        $te = m($table)->fetchRow($r->target);

                        // Backup current language
                        $_lang = Indi::ini('lang')->admin;

                        // Spoof current language
                        Indi::ini('lang')->admin = $target;

                        // Rebuild value
                        $te->{$setter}();

                        // Collect it
                        $result[$idx] = $te->$field;

                        // Restore current language
                        Indi::ini('lang')->admin = $_lang;
                    }

                // Else
                } else {

                    // Try to call Google Cloud Translate API
                    try {

                        // Get translations from Google Cloud Translate API
                        $result = array_column($gapi->translateBatch($rs->column('value'), [
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

                    // Amend result
                    $this->amendResult($result[$idx], $r);

                    // Write translation result
                    $r->assign(array('result' => $result[$idx], 'stage' => 'queue'))->basicUpdate();

                    // Increment `queueSize` prop on `queueChunk` entry and save it
                    $queueChunkR->queueSize++;
                    $queueChunkR->basicUpdate();

                    // Increment `queueSize` prop on `queueTask` entry and save it
                    $queueTaskR->queueSize++;
                    $queueTaskR->basicUpdate(false, false);

                    // Increment $deduct
                    $deduct++;
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
        $params = json_decode($queueTaskR->params, true);

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`applyState` != "finished"',
            'order' => '`applyState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->assign(array('applyState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "' . ($params['toggle'] == 'n' ? 'items' : 'queue') . '"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Get queue items
            Indi::model('QueueItem')->batch(function (&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, $table, $field) {

                // Get cell's current value
                $json = Indi::db()->query('SELECT `:p` FROM `:p` WHERE `id` = :p', $field, $table, $r->target)->fetchColumn();

                // If cell value is not a json - force it to be json
                if (!preg_match('~^{"~', $json)) $json = json_encode([$params['source'] => $json], JSON_UNESCAPED_UNICODE);

                // Decode translations
                $data = json_decode($json ?: '{}');

                // If 'toggle'-param is 'n' - unset translation, else append it
                if ($params['toggle'] == 'n') unset($data->{$params['source']}); else $data->{$params['target']} = $r->result;

                // Encode back
                $json = json_encode($data, JSON_UNESCAPED_UNICODE);

                // Update cell value
                Indi::db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $table, $field, $json, $r->target);

                // Write translation result
                $r->assign(array('stage' => 'apply'))->basicUpdate();

                // Reset batch offset
                $deduct++;

                // Increment `applySize` prop on `queueChunk` entry and save it
                $queueChunkR->applySize++;
                $queueChunkR->basicUpdate();

                // Increment `applySize` prop on `queueTask` entry and save it
                $queueTaskR->applySize++;
                $queueTaskR->basicUpdate(false, false);

            }, $where, '`id` ASC');

            // Remember that our try to count was successful
            $queueChunkR->assign(array('applyState' => 'finished'))->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'applyState' => 'finished'))->save();

        // Update target `lang` entry's state for current fraction
        $langR_target = Indi::model('Lang')->fetchRow('`alias` = "' . $params[$params['toggle'] == 'n' ? 'source' : 'target'] . '"');
        $langR_target->{lcfirst(preg_replace('~^Indi_Queue_L10n_~', '', get_class($this)))} = $params['toggle'] ?: 'y';
        $langR_target->save();
    }

    /**
     * Order chunks with respect to dependencies
     *
     * @param $queueTaskId
     */
    public function orderChunks($queueTaskId) {

        // Get dict containing chunks info
        $dict = $this->_dict($queueTaskId);

        // Array for proper-ordered `queueChunk` ids
        $ordered = [];

        /**
         * Function for prepending all dependencies of certain field before that field
         *
         * @param $dict
         * @param $item
         * @param $ordered
         */
        if (!function_exists('___')) {
            function ___($dict, $item, &$ordered) {
                foreach ($item['consider'] ?: [] as $fieldId) if ($dict[$fieldId]) ___($dict, $dict[$fieldId], $ordered);
                $ordered[$item['fieldId']] = $item['queueChunkId'];
            }
        }

        // Build new order as index => queueChunkId pairs
        foreach ($dict as $item) ___($dict, $item, $ordered);

        // Convert keys for them to be indexes instead of field ids
        $ordered = array_values($ordered);

        // Apply new order
        foreach ($ordered as $idx => $queueChunkId) Indi::db()->query('
            UPDATE `queueChunk` SET `move` = "' . ($idx + 1) . '" WHERE `id` = "' . $queueChunkId . '"
        ');
    }

    /**
     * Get dict of all chunks within current queueTask
     *
     * @param $queueTaskId
     * @return array
     */
    protected function _dict($queueTaskId) {

        // Get `queueChunk` entries
        $rs = m('QueueChunk')->fetchAll('`queueTaskId` = "' . $queueTaskId . '"');

        // Build dictionary
        $dict = [];

        // Foreach `queueChunk` entry
        foreach ($rs as $r) {

            // Start building dict item
            $item = ['queueChunkId' => $r->id, 'location' => $r->location];

            // If it's a enumset-item
            if ($r->location == 'enumset:title') {

                // Get `fieldId` from WHERE clause
                $item['fieldId'] = Indi::rexm('~^`fieldId` = "([0-9]+)"$~', $r->where, 1);

            // Else
            } else {

                // Get `fieldId` from `location`
                list($table, $field) = explode(':', $r->location); $item['fieldId'] = m($table)->fields($field)->id;

                // Collect dependencies
                if ($_ = Indi::db()->query('
                    SELECT IF(`foreign` = "0", `consider`, `foreign`) AS `consider` 
                    FROM `consider`
                    WHERE `fieldId` = "' . $item['fieldId'] . '"
                ')->fetchAll(PDO::FETCH_COLUMN)) $item['consider'] = $_;
            }

            // Append item to dict
            $dict[$item['fieldId']] = $item;
        }

        // Return dict
        return $dict;
    }

    /**
     * Get info about chunks, related to master-fields of a field, represented by $queueChunkId
     *
     * @param $queueTaskId
     * @param $queueChunkId
     * @return array
     */
    public function considerChunks($queueTaskId, $queueChunkId) {

        // Get dict containing chunks info
        $dict = $this->_dict($queueTaskId);

        // Build consider chunks info
        $consider = [];
        foreach ($dict as $fieldId => $item)
            if ($item['queueChunkId'] == $queueChunkId && $item['consider'])
                foreach ($item['consider'] as $fieldId)
                    $consider[$fieldId] = $dict[$fieldId];

        // Return
        return $consider;
    }
}