<?php
class Indi_Queue_L10n_AdminConst extends Indi_Queue_L10n_AdminUi {

    /**
     * Use 'core' for system fraction, or 'www' for custom fraction
     *
     * @var string
     */
    public $fractionDir = null;

    /**
     * Create queue chunks
     *
     * @param array $params
     * @return QueueTask_Row
     */
    public function chunk($params) {

        // Create `queueTask` entry
        $queueTaskR = m('QueueTask')->new([
            'title' => 'L10n_' . array_pop(explode('_', get_class($this))),
            'params' => json_encode($params),
            'queueState' => $params['toggle'] == 'n' ? 'noneed' : 'waiting'
        ]);

        // Save `queueTask` entries
        $queueTaskR->save();

        // Create `queueChunk` entry and setup basic props
        m('QueueChunk')->new([
            'queueTaskId' => $queueTaskR->id,
            'location' => '/' . $this->fractionDir . '/application/lang/admin/' . $params['source']. '.php'
        ])->save();

        // Increment `chunk`
        $queueTaskR->chunk ++;
        $queueTaskR->basicUpdate(false, false);

        // Return
        return $queueTaskR;
    }

    /**
     * Count queue items
     *
     * @param $queueTaskId
     * @throws Exception
     */
    public function count($queueTaskId) {

        // Fetch `queueTask` entry
        $queueTaskR = m('QueueTask')->row($queueTaskId);

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`countState` != "finished"',
            'order' => '`countState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->set(['countState' => 'progress'])->basicUpdate();

            // Build filename of a php-file, containing l10n constants for source language
            $l10n_source_abs = DOC . STD . $queueChunkR->location;

            // If no file - skip
            if (!file_exists($l10n_source_abs)) jflush(false, 'File ' . $l10n_source_abs . ' - not found');

            // If emtpy file - skip
            if (!$l10n_source_raw = file_get_contents($l10n_source_abs))  jflush(false, 'File ' . $l10n_source_abs . ' - is empty');

            // Parse constants-file contents to pick name and value for each constant
            $const = Indi::rexma('~define\(\'(.*?)\', ?\'(.*?)\'\);~', $l10n_source_raw);

            // Count items
            $queueChunkR->countSize = count($const[2]);

            // Remember that our try to count was successful
            $queueChunkR->set(['countState' => 'finished'])->basicUpdate();

            // Update `queueTask` entry's `countSize` prop
            $queueTaskR->countSize += $queueChunkR->countSize;
            $queueTaskR->basicUpdate(false, false);
        }

        // Mark first stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'countState' => 'finished'])->save();
    }

    /**
     * Create queue items, accodring to already created chunks
     *
     * @param $queueTaskId
     * @throws Exception
     */
    public function items($queueTaskId) {

        // Get `queueTask` entry
        $queueTaskR = m('QueueTask')->row($queueTaskId);

        // Update `stage` and `state`
        $queueTaskR->stage = 'items';
        $queueTaskR->state = 'progress';
        $queueTaskR->itemsState = 'progress';
        $queueTaskR->basicUpdate(false, false);

        // Get params
        $params = json_decode($queueTaskR->params, true);

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`itemsState` != "finished"',
            'order' => '`itemsState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->set(['itemsState' => 'progress'])->basicUpdate();

            // Get last target
            $last = m('QueueItem')->row('`queueChunkId` = "' . $queueChunkR->id . '"', '`id` DESC')->target;

            // Build filename of a php-file, containing l10n constants for source language
            $l10n_source_abs = DOC . STD . $queueChunkR->location;

            // If no file - skip
            if (!file_exists($l10n_source_abs)) jflush(false, 'File ' . $l10n_source_abs . ' - not found');

            // If emtpy file - skip
            if (!$l10n_source_raw = file_get_contents($l10n_source_abs))  jflush(false, 'File ' . $l10n_source_abs . ' - is empty');

            // Parse constants-file contents to pick name and value for each constant definition
            $const = Indi::rexma('~define\(\'(.*?)\', ?\'(.*?)\'\);~', $l10n_source_raw);

            // Foreach detected constant definition
            foreach ($const[2] as $idx => $value) {

                // Constant name
                $target = $const[1][$idx];

                // If something went wrong at last time - make sure we'll continue from right point
                if ($last && !$found) {

                    // If we reached last successful item - setup $found flag as `true`
                    if ($last == $target) $found = true;

                    // Jump to next iteration
                    continue;
                }

                // Get value
                $value = stripslashes($value);

                // Create `queueItem` entry
                $queueItemR = m('QueueItem')->new([
                    'queueTaskId' => $queueTaskR->id,
                    'queueChunkId' => $queueChunkR->id,
                    'target' => $target,
                    'value' => $value
                ]);

                // Save `queueItem` entry
                $queueItemR->save();

                // Increment `queued` prop on `queueChunk` entry and save it
                $queueChunkR->itemsSize ++;
                $queueChunkR->itemsBytes += ($bytes = mb_strlen($value, 'utf-8') * $this->itemsBytesMultiplier($params));
                $queueChunkR->basicUpdate();

                // Increment `itemsSize` prop on `queueTask` entry and save it
                $queueTaskR->itemsSize ++;
                $queueTaskR->itemsBytes += $bytes;
                $queueTaskR->basicUpdate(false, false);
            }

            // Remember that our try to count was successful
            $queueChunkR->set(['itemsState' => 'finished'])->basicUpdate();
        }

        // Mark first stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'itemsState' => 'finished'])->save();
    }

    /**
     * Amend translation result before saving it into `queueItem` entry's `result` prop
     *
     * @param $result
     * @param $queueItemR
     * @return mixed|void
     */
    public function amendResult(&$result, $queueItemR) {

        // Fix placeholders
        if (preg_match('~%s~', $queueItemR->value)) $result = preg_match('~"%s"~', $queueItemR->value)
            ? preg_replace('~(&#39;|&quot;)% s\1~i', '"%s"', $result)
            : (preg_match('~\'%s\'~', $queueItemR->value)
                ? preg_replace('~(&#39;)% s\1~i', '\'%s\'', $result)
                : preg_replace('~% s~i', ' %s', $result));

        // Fix time
        $result = str_replace('HH: MM: SS', 'HH:MM:SS', $result);

        // Fix ' > ' problem
        if (preg_match('~ > ~', $queueItemR->value))  $result = preg_replace('~([^\s])(&gt;) ~', '$1 $2 ', $result);

        // Other replacements
        $result = str_replace('&quot;% s&quot;', '&quot;%s&quot;', $result);
        $result = str_replace('# %s', '#%s', $result);
        $result = str_replace('% s ', '%s ', $result);
        $result = str_replace(',%s ', ', %s', $result);
        $result = preg_replace('~([a-z])% ?s~', '$1 %s', $result);
        $result = preg_replace('~%s([a-z])~', '%s $1', $result);

        // Decode entities
        $result = html_entity_decode($result);

        // More replacements
        $result = str_replace('" %s"', '"%s"', $result);
        $result = str_replace('&#39;', "'", $result);

        // Fix tbq-translations
        if (preg_match('~_TBQ$~', $queueItemR->target)) $result = str_replace(', ', ',', $result);
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

            // Build filename of a php-file, containing l10n constants for source language
            $l10n_source_abs = DOC . STD . $queueChunkR->location;

            // This thing may seem strange, but provides empty string to be set up as the value of each php-constant
            if ($params['toggle'] == 'n') $l10n_target_raw = file_get_contents($l10n_target_abs = $l10n_source_abs); else {

                // Build filename of a php-file, containing l10n constants for target language
                $l10n_target_abs = preg_replace('~'. $params['source'] . '(\.php)$~', $params['target'] . '$1', $l10n_source_abs);

                // If target file not yet exists
                if (!file_exists($l10n_target_abs)) {

                    // If no file - skip
                    if (!file_exists($l10n_source_abs)) jflush(false, 'File ' . $l10n_source_abs . ' - not found');

                    // If target file is emtpy - flush error
                    if (!$l10n_source_raw = file_get_contents($l10n_source_abs))  jflush(false, 'Source file ' . $l10n_source_abs . ' - is empty');

                    // If can't copy - flush error
                    if (!copy($l10n_source_abs, $l10n_target_abs)) jflush(false, 'Can\'t copy ' . $l10n_source_abs . ' into ' . $l10n_target_abs);

                    // Copy contents
                    $l10n_target_raw = $l10n_source_raw;

                    // If target file exists but is emtpy - flush error
                } else if (!$l10n_target_raw = file_get_contents($l10n_target_abs))  jflush(false, 'Target file ' . $l10n_source_abs . ' - is empty');
            }

            // Get queue items
            m('QueueItem')->batch(function (&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params, $table, $field, &$l10n_target_raw, $l10n_target_abs) {

                // Replace &#39; with \'
                $r->result = str_replace(['&#39;', "'"], "\'", $r->result);

                // Replace source-language definition with target-language definition
                $l10n_target_raw = preg_replace('~(define\(\'' . $r->target . '\', ?\')(.*?)(\'\);)~', '$1' . $r->result . '$3', $l10n_target_raw);

                // Update target file
                file_put_contents($l10n_target_abs, $l10n_target_raw);

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

            // Unlink constants file
            if ($params['toggle'] == 'n') unlink(DOC . STD . $queueChunkR->location);

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
}