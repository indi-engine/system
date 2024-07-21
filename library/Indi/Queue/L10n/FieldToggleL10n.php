<?php
class Indi_Queue_L10n_FieldToggleL10n extends Indi_Queue_L10n {

    /**
     * @var array
     */
    public static $l10n = [];

    /**
     * Create queue chunks
     *
     * @param array $params
     */
    public function chunk($params) { $parts = explode('_', __CLASS__);

        // Create `queueTask` entry
        $queueTaskR = m('QueueTask')->new([
            'title' => 'L10n_' . array_pop($parts),
            'params' => json_encode($params),
            'queueState' => ($params['toggle'] ?? null) == 'n' ? 'noneed' : 'waiting'
        ]);

        // Save `queueTask` entries
        $queueTaskR->save();

        // Get table and field
        $parts = explode(':', $params['field']); $table = $parts[0]; $field = $parts[1]; $entry = $parts[2] ?? null;

        //
        $fieldR = $entry ? cfgField($table, $entry, $field) : field($table, $field);

        // Create separate `queueChunk`-trees for each fraction
        foreach ($params['target'] as $fraction => $targets)
            $this->appendChunk($queueTaskR, entity($table), $fieldR, [], $fraction);

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
        $params = json_decode($queueTaskR->params);
        $source = $params->source;
        $target = $params->target;

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`queueState` != "finished"',
            'order' => '`queueState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            //
            if ($queueChunkR->queueChunkId) {

                // Split `location` on $table and $field
                list ($ptable, $pfield) = explode(':', $queueChunkR->foreign('queueChunkId')->location);

                //
                self::$l10n[$ptable][$pfield] = db()->query('
                    SELECT `target`, `result` FROM `queueItem` WHERE `queueChunkId` = "' . $queueChunkR->queueChunkId . '"
                ')->pairs();
            }

            // If it's parent chunk
            if ($queueChunkR->system('disabled')) continue;

            // Remember that we're going to count
            $queueChunkR->set(['queueState' => 'progress'])->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "items"';

            // Target languages
            $targets = $queueChunkR->location == $params->field && $params->rootTarget
                ? $params->rootTarget
                : $target->{$queueChunkR->fraction};

            // Check whether we should use setter method call instead of google translate api call
            $setter = $queueChunkR->queueChunkId && method_exists(m($table)->new(), $_ = 'set' . ucfirst($field)) ? $_ : false;

            // Get queue items by 50 entries at a time
            m('QueueItem')->batch(function(&$rs, &$deduct) use (&$queueTaskR, &$queueChunkR, &$gapi, $source, $targets, $table, $field, $setter) {

                // If chunk's field is a dependent field and setter method exists
                if ($setter) {

                    // Foreach chunk's `queueItem` entry
                    foreach ($rs as $idx => $r) {

                        // Get target entry
                        $te = m($table)->row($r->target);

                        // Backup current language
                        $_lang = ini('lang')->admin;

                        // Foreach target language
                        foreach (ar($targets) as $target) {

                            // Spoof current language
                            ini('lang')->admin = $target;

                            // Rebuild value
                            $te->{$setter}();

                            // Collect it
                            $resultByLang[$target][$idx] = $te->$field;
                        }

                        // Restore current language
                        ini('lang')->admin = $_lang;
                    }

                // Else
                } else {

                    // Get values
                    $values = $rs->column('value');

                    // Try to call Google Cloud Translate API
                    try {

                        // Foreach target language - make api call to google passing source values
                        foreach (ar($targets) as $target)
                            $resultByLang[$target] = ini('lang')->gapi->off
                                ? $values
                                : array_column($gapi->translateBatch($values, [
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

                    // Collect result for each target language
                    $result = new stdClass(); foreach ($targets ? $resultByLang : [] as $target => $byIdx) {
                        $result->$target = $byIdx[$idx]; $this->amendResult($result->$target, $r);
                    }

                    // Write translation result
                    $r->set(['result' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT), 'stage' => 'queue'])->basicUpdate();

                    // Increment `queueSize` prop on `queueChunk` entry and save it
                    $queueChunkR->queueSize ++; $queueChunkR->basicUpdate();

                    // Increment `queueSize` prop on `queueTask` entry and save it
                    $queueTaskR->queueSize ++; $queueTaskR->basicUpdate(false, false);

                    // Increment $deduct
                    $deduct ++;
                }

            }, $where, '`id` ASC', 10, true);

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

            // Skip parent entries
            if ($queueChunkR->system('disabled')) continue;

            // Remember that we're going to count
            $queueChunkR->set(['applyState' => 'progress'])->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "' . (($params['toggle'] ?? null) == 'n' ? 'items' : 'queue') . '"';

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Get `field` entry
            $fieldR = $table == 'enumset'
                ? m('Field')->row(Indi::rexm('~`fieldId` = "([0-9]+)"~', $queueChunkR->where, 1))
                : field($table, $field);

            // Convert column type to TEXT
            if (($params['toggle'] ?? null) != 'n') {
                if ($table == 'param' && $field == 'cfgValue') {
                    list ($_table, $_field, $_entry) = explode(':', $params['field']);
                    $fieldR = cfgField($_table, $_entry, $_field, ['columnTypeId' => 'TEXT']);
                } else if ($table != 'enumset') {
                    field($table, $field, ['columnTypeId' => 'TEXT']);
                }
            }

            // Setup $hasLD flag
            $hasLD = $fieldR->hasLocalizedDependency();

            // Get queue items
            m('QueueItem')->batch(function(&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, $table, $field, $hasLD) {

                // If localization is going to turned Off - use `queueItem` entry's `value` as target value, else
                if (($params['toggle'] ?? null) == 'n') $value = $r->value; else {

                    // Get cell's current value
                    $json = db()->query('SELECT `:p` FROM `:p` WHERE `id` = :p', $field, $table, $r->target)->cell();

                    // If cell value is not an empty string, but is not a json - force it to be json
                    if (!preg_match('~^{"~', $json)) $json = json_encode([$params['source'] => $json], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);

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
                db()->query('UPDATE `:p` SET `:p` = :s WHERE `id` = :i', $table, $field, $value, $r->target);

                // Write translation result
                $r->set(['stage' => 'apply'])->basicUpdate();

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
                    field($table, $field, ['columnTypeId' => 'VARCHAR(255)']);

            // Switch field's l10n-prop from intermediate to final value
            if (($params['toggle'] ?? null) != 'n' || !$queueChunkR->queueChunkId || !$hasLD)
                $fieldR->set(['l10n' => $params['toggle'] ?? 'y'])->save();

            // Remember that our try to count was successful
            $queueChunkR->set(['applyState' => 'finished'])->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'applyState' => 'finished'])->save();
    }

    /**
     * Count symbols properly, e.g. multiply on target languages queantity,
     * as Google charges 20 USD per 1 million symbols
     *
     * @return int
     */
    public function itemsBytesMultiplier($params, $setter = false) {

        // If we're turning field's localization off, or using setter method instead of actual api call - return 0
        if (($params['toggle'] ?? null) == 'n' || $setter) return 0;

        // If target-param is comma-separated string - return number of items
        if (is_string($params['target'] ?? 0)) return count(ar($params['target']));

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
    public function appendChunk(&$queueTaskR, $entityR, $fieldR, $where = [], $fraction = 'none') {

        // Create parent `queueChunk` entry and setup basic props
        $queueChunkR = parent::appendChunk($queueTaskR, $entityR, $fieldR, $where);

        // Setup `fraction` and `where` props
        $queueChunkR->set([
            'fraction' => $fraction,
            'where' => $this->getFractionChunkWHERE($fraction, $fieldR->id)
        ])->save();

        foreach (m('Consider')->all('"' . $fieldR->id . '" = IF(IFNULL(`foreign`, 0) = "0", `consider`, `foreign`)') as $considerR) {

            // Skip foreign-key fields
            if ($considerR->foreign('fieldId')->storeRelationAbility != "none") continue;

            // Create `queueChunk` entries for dependent fields
            $dependent = $this->appendChunk($queueTaskR,
                $considerR->foreign('fieldId')->foreign('entityId'),
                $considerR->foreign('fieldId'), [], $fraction);

            // Make those to be child under parent `queueChunk` entry and setup `fraction` and `where` props
            $dependent->set([
                'queueChunkId' => $queueChunkR->id,
                'fraction' => $fraction,
                'where' => $this->getFractionChunkWHERE($fraction, $considerR->fieldId)
            ])->save();
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