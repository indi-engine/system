<?php
trait Indi_Queue_L10n_AdminExport {

    /**
     * @var array
     */
    public $meta = [];

    /**
     * @var array
     */
    public $params = [];

    /**
     * @param $params
     * @return bool|Indi_Db_Table_Row|string|void
     */
    public function chunk($params) {

        //
        if (is_array($params)) $this->params = $params;

        // Call parent and get return value
        $return = parent::chunk($params);

        // If fields l10n toggling code should be exported
        if (in('meta', $this->params['export'])) {

            // Build php-file
            $this->fieldsToggleL10nYExport();

            //
            if ($return instanceof Indi_Db_Table_Row
                && !in('data', $this->params['export']))
                $return->set([
                    'countState' => 'finished',
                    'itemsState' => 'finished',
                    'queueState' => 'finished',
                    'applyState' => 'finished',
                ])->basicUpdate();
        }

        // Return
        return $return;
    }

    public function appendChunk(&$queueTaskR, $entityR, $fieldR_having_l10nY, $where = []) {

        // Call parent
        $return = parent::appendChunk($queueTaskR, $entityR, $fieldR_having_l10nY, $where);

        //
        if (in('meta', $this->params['export'])
            && !$fieldR_having_l10nY->nested('consider')->count()
            && $return instanceof Indi_Db_Table_Row)
            $this->meta[$return->id] = $fieldR_having_l10nY->id;

        // Return
        return $return;
    }

    /**
     *
     */
    public function fieldsToggleL10nYExport() {

        // Absolute path
        $abs = DOC . STD . $this->fractionDir . '/application/lang/' . $this->type . '.php';

        // Put php opening atg
        file_put_contents($abs, '<?php' . "\n");

        // Collect chunk ids in right order
        $chunkIdA = db()->query('
            SELECT `id` 
            FROM `queueChunk`
            WHERE `id` IN (' . im(array_keys($this->meta)) . ')
            ORDER BY `move`
        ')->fetchAll(PDO::FETCH_COLUMN);

        // Collect field ids in right order
        foreach ($chunkIdA as $chunkId) {

            // Build line
            $line = m('field')->row($this->meta[$chunkId])->export(false) . "->toggleL10n('qy', \$lang, false);";

            // Append to ui.php
            file_put_contents($abs, $line . "\n", FILE_APPEND);
        }
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
            if (!$exportable = method_exists(m($table)->new(), 'export'))
                $queueChunkR->set(['queueState' => 'noneed'])->basicUpdate();

            // Get queue items by 50 entries at a time
            m('QueueItem')->batch(function (&$rs, &$deduct) use (&$queueTaskR, &$queueChunkR, &$gapi, $source, $target, $table, $field, $exportable) {

                // Foreach fetched `queueItem` entry
                foreach ($rs as $idx => $r) {

                    //
                    if ($exportable) {

                        // Backup current language
                        $_lang = ini('lang')->admin;

                        // Spoof current language
                        ini('lang')->admin = $source;

                        // Get target entry
                        $te = m($table)->row($r->target);

                        // Do export
                        $result = $te->export($field);

                        // Restore current language
                        ini('lang')->admin = $_lang;

                    //
                    } else $result = '';

                    // Write translation result
                    $r->set(['result' => $result, 'stage' => 'queue'])->basicUpdate();

                    // Increment `queueSize` prop on `queueChunk` entry and save it
                    $queueChunkR->queueSize++;
                    $queueChunkR->basicUpdate();

                    // Increment `queueSize` prop on `queueTask` entry and save it
                    $queueTaskR->queueSize++;
                    $queueTaskR->basicUpdate(false, false);

                    // Increment $deduct
                    $deduct++;
                }

            //
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

        // Build filename of a php-file, containing l10n constants for source language
        $l10n_target_abs = DOC . STD . $this->fractionDir . '/application/lang/' . $this->type . '/' . $params['source'] . '.php';

        // Create dir if not exists
        if (!is_dir($dir = dirname($l10n_target_abs))) mkdir($dir, umask(), true);

        // Put opening php tag
        file_put_contents($l10n_target_abs, '<?php' . "\n");

        // Setup special migration state so that 'basicUpdate' method would be used instead of 'save' method
        // in field(...), section(...), enumset(...) and other shorthand-functions
        file_put_contents($l10n_target_abs, 'ini()->lang->migration = true;' . "\n", FILE_APPEND);

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`applyState` != "finished"',
            'order' => '`applyState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->set(['applyState' => 'progress'])->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "queue"';

            // Get queue items
            m('QueueItem')->batch(function (&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, &$l10n_target_raw, $l10n_target_abs) {

                // Update target file
                if ($r->result) file_put_contents($l10n_target_abs, $r->result . "\n", FILE_APPEND);

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

        // Get file by lines
        $php = file($l10n_target_abs); $tag = array_shift($php); $ini = array_shift($php);

        // Sort lines and re-append first two lines
        sort($php); array_unshift($php, $tag, $ini);

        // Put back into file
        file_put_contents($l10n_target_abs, join('', $php));

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'applyState' => 'finished'])->save();
    }

    /**
     * Used with $queueChunkR->itemsSize
     *
     * @return int
     */
    public function itemsBytesMultiplier($params, $setter = false) {
        return 0;
    }    
}