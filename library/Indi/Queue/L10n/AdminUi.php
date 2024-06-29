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
        if (is_array($params)) { $parts = explode('_', get_class($this));

            // Create `queueTask` entry
            $queueTaskR = m('QueueTask')->new([
                'title' => 'L10n_' . array_pop($parts),
                'params' => json_encode($params),
                'queueState' => ($params['toggle'] ?? null) == 'n' ? 'noneed' : 'waiting'
            ]);

            // Save `queueTask` entries
            $queueTaskR->save();

        // Else assume is's and ID of specific field, that we need to detect WHERE clause for
        } else $this->fieldId = $params;

        // Dict of entities having purpose-distinction
        $master = $this->master; $queueTaskR ??= null;

        // Additional info for detecting entries
        foreach ($master as $table => &$info) $info += [
            'entityId' => m($table)->id(),
            'instances' => m($table)->all('`' . $info['field'] . '` IN ("' . im(ar($info['value']), '","') . '")')->column('id', true) ?: 0
        ];

        // Collect id of enties
        $masterIds = array_column($master, 'entityId');

        // Foreach `entity` entry, having `fraction` = "custom" (e.g. project's custom entities)
        if ($master['entity']['value'] == 'custom') foreach (m('Entity')->all('`fraction` = "custom"') as $entityR) {

            // If $this->fieldId prop is set, it means that we're here
            // because of Indi_Queue_L10n_FieldToggleL10n->getFractionChunkWHERE() call
            // so out aim here to obtain WHERE clause for certain field's chunk,
            // and appendChunk() call will return WHERE clause rather than `queueChunk` instance
            if ($this->fieldId) {
                if ($fieldR_certain = m($entityR->id)->fields($this->fieldId))
                    return $this->appendChunk($queueTaskR, $entityR, $fieldR_certain, []);

                // Foreach `field` entry, having `l10n` = "y"
            } else foreach (m($entityR->id)->fields()->select('y', 'l10n') as $fieldR_having_l10nY)
                if ($fieldR_having_l10nY->relation == 6)
                    $this->appendChunk($queueTaskR, $entityR, $fieldR_having_l10nY, []);
        }

        // Foreach `entity` entry, having `fraction` = "system" (e.g. project's system entities)
        foreach (m('Entity')->all('`fraction` = "system"', '`table`') as $entityR) {

            // If current entity is a multi-fraction entity
            if ($master[$entityR->table] ?? 0)
                $where = '`' . $master[$entityR->table]['field'] . '`'
                    . (count($ar = ar($v = $master[$entityR->table]['value'])) > 1
                        ? ' IN ("' . im($ar, '","') . '")'
                        : ' = "' . $v . '"');

            // Else if entries of current entity are nested under at least one of multi-purpose entities entries
            else if ($fieldR = m($entityR->id)->fields()->select($masterIds, 'relation')->at(0))
                $where = '`' . $fieldR->alias . '` IN (' . $master[m($fieldR->relation)->table()]['instances'] . ')';

            // Else no WHERE clause
            else $where = false;

            // Skip changeLog-records. todo: make it work for cases when changeLog is turned On for system entities
            if ($entityR->table == 'changeLog') continue;

            // Append special WHERE-clase for noticeGetter-records
            if ($entityR->table == 'noticeGetter') {
                $where .= ' AND `roleId` IN (' . $master['role']['instances'] . ')';

            /**
             *
             */
            } else if ($entityR->table == 'param') {

                //
                if ($this->fieldId) {

                    //
                    if (m('Field')->row($this->fieldId)->entityId == $entityR->id) {

                        // Get config field ids
                        $cfgFieldIds = im(db()->query('
                            SELECT DISTINCT `p`.`cfgField` 
                            FROM `param` `p`, `field` `f`
                            WHERE 1
                              AND `p`.`cfgField` = `f`.`id`
                              AND `f`.`entityId` IN (' . $master['entity']['instances'] . ')
                        ')->col()) ?: 0;

                        //
                        $where = '`cfgField` IN ('. $cfgFieldIds . ')';

                    //
                    } else {

                        // Get config field ids
                        $cfgFieldIds = m('Field')->all([
                            '`entityId` = "4"',
                            '`entry` != "0"',
                            '`id` = "' . $this->fieldId . '"',
                        ])->column('id', true) ?: 0;

                        // Get field ids
                        $fieldIds = im(db()->query('
                            SELECT DISTINCT `p`.`fieldId` 
                            FROM `param` `p`, `field` `f`
                            WHERE 1
                              AND `p`.`cfgField` IN (' . $cfgFieldIds . ')
                              AND `p`.`fieldId` = `f`.`id`
                              AND `f`.`entityId` IN (' . $master['entity']['instances'] . ')
                        ')->col()) ?: 0;

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
                    if ($master['entity']['value'] == 'system' || ($where && $fieldR_certain->relation != 6))
                        return $this->appendChunk($queueTaskR, $entityR, $fieldR_certain, $where ? [$where] : []);

                // Else if it's a config field
                } else if ($fieldR_certain = m('Field')->row(['`id` = "' . $this->fieldId . '"', '`entry` != "0"'])) {
                    if ($entityR->table == 'param' && $where && $fieldR_certain->relation != 6) return $where;
                    else if ($entityR->table == 'enumset' && $fieldR_certain->relation == 6)
                        return $this->appendChunk($queueTaskR, $entityR, $fieldR_certain, $where ? [$where] : []);
                }

            // Foreach `field` entry, having `l10n` = "y"
            } else foreach (m($entityR->id)->fields()->select('y', 'l10n') as $fieldR_having_l10nY)
                if ($master['entity']['value'] == 'system' || ($where && $fieldR_having_l10nY->relation != 6))
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
        $queueTaskR = m('QueueTask')->row($queueTaskId);

        // If `queueState` is 'noneed' - do nothing
        if ($queueTaskR->queueState == 'noneed') return;

        // Require and instantiate Google Cloud Translation PHP API and
        $gapi = new Google\Cloud\Translate\V2\TranslateClient(['key' => ini('lang')->gapi->key]);

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
            $queueChunkR->set(['queueState' => 'progress'])->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "items"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Check whether we should use setter method call instead of google translate api call
            $setter = $field && method_exists(m($table)->new(), $_ = 'set' . ucfirst($field)) ? $_ : false;

            // If setter-method exists, it means that we won't be calling google translate api for this chunk,
            // but we'll be calculating translations by calling setter-methods, preliminary prodiving master-fields
            // translations, as at this monent they're available only within `queueItem` entries
            if ($setter) foreach ($this->considerChunks($queueTaskId, $queueChunkR->id) as $queueChunkI) {

                // Split `location` on $table and $field
                list ($ptable, $pfield) = explode(':', $queueChunkI['location']);

                // Load tarnslations
                foreach(db()->query('
                    SELECT `target`, `result` FROM `queueItem` WHERE `queueChunkId` = "' . $queueChunkI['queueChunkId'] . '"
                ')->pairs() as $entryId => $targetTranslation)
                    Indi_Queue_L10n_FieldToggleL10n::$l10n[$ptable][$pfield][$entryId] = json_encode([$target => $targetTranslation], JSON_UNESCAPED_UNICODE);
            }

            // Get queue items by 50 entries at a time
            m('QueueItem')->batch(function (&$rs, &$deduct) use (&$queueTaskR, &$queueChunkR, &$gapi, $source, $target, $table, $field, $setter) {

                // If chunk's field is a dependent field and setter method exists
                if ($setter) {

                    // Foreach chunk's `queueItem` entry
                    foreach ($rs as $idx => $r) {

                        // Get target entry
                        $te = m($table)->row($r->target);

                        // Backup current language
                        $_lang = ini('lang')->admin;

                        // Spoof current language
                        ini('lang')->admin = $target;

                        // Rebuild value
                        $te->{$setter}();

                        // Collect it
                        $result[$idx] = $te->$field;

                        // Restore current language
                        ini('lang')->admin = $_lang;
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

                        // Get error
                        $error = $e->getMessage();
                        if ($json = json_decode($error))
                            $error = $json->error->message;

                        // Save error to queueTask props
                        $queueTaskR->set([
                            'queueState' => 'error',
                            'error' => $error
                        ])->save();

                        // Log error
                        ehandler(1, $queueTaskR->error, __FILE__, __LINE__);

                        // Exit
                        exit;
                    }
                }

                // Foreach fetched `queueItem` entry
                foreach ($rs as $idx => $r) {

                    // Amend result
                    $this->amendResult($result[$idx], $r);

                    // Write translation result
                    $r->set(['result' => $result[$idx], 'stage' => 'queue'])->basicUpdate();

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
            $queueChunkR->set(['queueState' => 'finished'])->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'queueState' => 'finished'])->save();
    }

    /**
     * Apply results
     *
     * @param $queueTaskId
     */
    public function apply($queueTaskId) {

        // Get `queueTask` entry
        $queueTaskR = m('QueueTask')->row($queueTaskId);

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
            $queueChunkR->set(['applyState' => 'progress'])->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "' . ($params['toggle'] == 'n' ? 'items' : 'queue') . '"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Get queue items
            m('QueueItem')->batch(function (&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, $table, $field) {

                // Get cell's current value
                $json = db()->query('SELECT `:p` FROM `:p` WHERE `id` = :p', $field, $table, $r->target)->cell();

                // If cell value is not a json - force it to be json
                if (!preg_match('~^{"~', $json)) $json = json_encode([$params['source'] => $json], JSON_UNESCAPED_UNICODE);

                // Decode translations
                $data = json_decode($json ?: '{}');

                // If 'toggle'-param is 'n' - unset translation, else append it
                if ($params['toggle'] == 'n') unset($data->{$params['source']}); else $data->{$params['target']} = $r->result;

                // Encode back
                $json = json_encode($data, JSON_UNESCAPED_UNICODE);

                // Update cell value
                db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $table, $field, $json, $r->target);

                // Write translation result
                $r->set(['stage' => 'apply'])->basicUpdate();

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
            $queueChunkR->set(['applyState' => 'finished'])->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'applyState' => 'finished'])->save();

        // Update target `lang` entry's state for current fraction
        $langR_target = m('Lang')->row('`alias` = "' . $params[$params['toggle'] == 'n' ? 'source' : 'target'] . '"');
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
                foreach ($item['consider'] ?? [] as $fieldId) if ($dict[$fieldId] ?? 0) ___($dict, $dict[$fieldId], $ordered);
                $ordered[$item['fieldId']] = $item['queueChunkId'];
            }
        }

        // Build new order as index => queueChunkId pairs
        foreach ($dict as $item) ___($dict, $item, $ordered);

        // Convert keys for them to be indexes instead of field ids
        $ordered = array_values($ordered);

        // Apply new order
        foreach ($ordered as $idx => $queueChunkId) db()->query('
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
        $rs = m('QueueChunk')->all('`queueTaskId` = "' . $queueTaskId . '"', '`id` ASC');

        // Build dictionary
        $dict = [];

        // Foreach `queueChunk` entry
        foreach ($rs as $r) {

            // Start building dict item
            $item = ['queueChunkId' => $r->id, 'location' => $r->location];

            // If it's a enumset-item
            if ($r->location === 'enumset:title') {

                // Get `fieldId` from WHERE clause
                $item['fieldId'] = Indi::rexm('~^`fieldId` = "([0-9]+)"$~', $r->where, 1);

            // Else
            } else {

                // Get table and field from location which may look like either
                // just '<table>:<field>' or /data/tpldoc/<table>-<field>(-<lang>).php
                if (preg_match('~/([a-zA-Z0-9_]+)-([a-zA-Z0-9_]+)(-[a-zA-Z\-]+)?\.php~', $r->location, $m)) {
                    $table = $m[1]; $field = $m[2];
                } else {
                    list($table, $field) = explode(':', $r->location);
                }

                // Get `fieldId` from `location`
                $item['fieldId'] = m($table)->fields($field)->id;

                // Collect dependencies
                if ($_ = db()->query('
                    SELECT IF(IFNULL(`foreign`, 0) = "0", `consider`, `foreign`) AS `consider` 
                    FROM `consider`
                    WHERE `fieldId` = "' . $item['fieldId'] . '"
                ')->col()) $item['consider'] = $_;
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