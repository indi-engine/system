<?php
class Indi_Queue_L10n_FieldToggleL10nUpload extends Indi_Queue_L10n_FieldToggleL10n {

    /**
     * Create queue chunks
     *
     * @param array $params
     */
    public function chunk($params) {

        // Create `queueTask` entry
        $queueTaskR = Indi::model('QueueTask')->createRow(array(
            'title' => 'L10n_' . array_pop(explode('_', get_class($this))),
            'params' => json_encode($params),
            'queueState' => $params['toggle'] == 'n' ? 'noneed' : 'waiting'
        ), true);

        // Save `queueTask` entries
        $queueTaskR->save();

        // Get table and field
        list ($table, $field) = explode(':', $params['field']);

        // Create separate `queueChunk`-trees for each fraction
        foreach ($params['target'] as $fraction => $targets)
            $this->appendChunk($queueTaskR, entity($table), field($table, $field), [], $fraction);

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
        $queueTaskR = Indi::model('QueueTask')->fetchRow($queueTaskId);

        // Get source and target languages
        $params = json_decode($queueTaskR->params);
        $source = $params->source;
        $target = $params->target;

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`countState` != "finished"',
            'order' => '`countState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->assign(array('countState' => 'progress'))->basicUpdate();

            //
            $lang = [$source]; foreach ($target as $fraction => $targets) $lang = array_merge($lang, ar($targets));

            // Count items
            $queueChunkR->countSize = count($lang);

            // Remember that our try to count was successful
            $queueChunkR->assign(array('countState' => 'finished'))->basicUpdate();

            // Update `queueTask` entry's `countSize` prop
            $queueTaskR->countSize += $queueChunkR->countSize;
            $queueTaskR->basicUpdate(false, false);
        }

        // Mark first stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'countState' => 'finished'))->save();
    }

    /**
     * Create queue items, accodring to already created chunks
     *
     * @param $queueTaskId
     * @throws Exception
     */
    public function items($queueTaskId) {

        // Get `queueTask` entry
        $queueTaskR = Indi::model('QueueTask')->fetchRow($queueTaskId);

        // Update `stage` and `state`
        $queueTaskR->stage = 'items';
        $queueTaskR->state = 'progress';
        $queueTaskR->itemsState = 'progress';
        $queueTaskR->basicUpdate(false, false);

        // Get params
        $params = json_decode($queueTaskR->params, true);
        $source = $params['source'];
        $target = $params['target'];

        // Foreach `queueChunk` entries, nested under `queueTask` entry
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`itemsState` != "finished"',
            'order' => '`itemsState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Remember that we're going to count
            $queueChunkR->assign(array('itemsState' => 'progress'))->basicUpdate();

            //
            $langA = [$source]; foreach ($target as $fraction => $targets) $langA = array_merge($langA, ar($targets));

            // Get existing template current contents
            $value = $params['toggle'] != 'n' ? file_get_contents(DOC . STD . $queueChunkR->location) : '';

            // Foreach entry matching chunk's definition
            foreach ($langA as $lang) {

                // Create `queueItem` entry
                $queueItemR = Indi::model('QueueItem')->createRow(array(
                    'queueTaskId' => $queueTaskR->id,
                    'queueChunkId' => $queueChunkR->id,
                    'target' => preg_replace('~\.php$~', '-' . $lang . '$0', $queueChunkR->location),
                    'value' => $lang
                ), true);

                // Save `queueItem` entry
                $queueItemR->save();

                // Increment `queued` prop on `queueChunk` entry and save it
                $queueChunkR->itemsSize ++;
                if ($source !== $lang)
                    $queueChunkR->itemsBytes += ($bytes = mb_strlen($value, 'utf-8') * $this->itemsBytesMultiplier($params, $setter));
                $queueChunkR->basicUpdate();

                // Increment `itemsSize` prop on `queueTask` entry and save it
                $queueTaskR->itemsSize ++;
                $queueTaskR->itemsBytes += $bytes;
                $queueTaskR->basicUpdate(false, false);
            }

            // Remember that our try to count was successful
            $queueChunkR->assign(array('itemsState' => 'finished'))->basicUpdate();
        }

        // Mark first stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'itemsState' => 'finished'))->save();
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
        foreach ($queueTaskR->nested('queueChunk', [
            'where' => '`queueState` != "finished"',
            'order' => '`queueState` = "progress" DESC, `move`'
        ]) as $queueChunkR) {

            // Get existing template current contents
            $value = file_get_contents($tpl = DOC . STD . $queueChunkR->location);

            // Wrap php-expressions into html-comments, for them to be ignored by google
            $value = str_replace(['<?', '?>'], ['<!-- <?', '?> -->'], $value);

            // Remember that we're going to count
            $queueChunkR->assign(array('queueState' => 'progress'))->basicUpdate();

            // Build WHERE clause for batch() call
            $where = '`queueChunkId` = "' . $queueChunkR->id . '" AND `stage` = "items"';

            // Get queue items by 50 entries at a time
            Indi::model('QueueItem')->batch(function(&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, &$gapi, $source, $value, $tpl) {

                // Try to call Google Cloud Translate API
                try {

                    //
                    if ($r->value == $source) $result = $value;

                    // Foreach target language - make api call to google passing source values
                    else $result = $gapi->translate($value, ['source' => $source, 'target' => $r->value])['text'];

                // Catch exception
                } catch (Exception $e) {

                    // Log error
                    ehandler(1, json_decode($e->getMessage())->error->message, __FILE__, __LINE__);

                    // Exit
                    exit;
                }

                // Collect result for each target language
                $this->amendResult($result);

                // Create temporary file
                $tmp = tempnam(ini_get('upload_tmp_dir'), 'tpldoc');

                //
                file_put_contents($tmp, $result);

                // Write translation result
                $r->assign(array('result' => $tmp, 'stage' => 'queue'))->basicUpdate();

                // Increment `queueSize` prop on `queueChunk` entry and save it
                $queueChunkR->queueSize ++; $queueChunkR->basicUpdate();

                // Increment `queueSize` prop on `queueTask` entry and save it
                $queueTaskR->queueSize ++; $queueTaskR->basicUpdate(false, false);

                // Increment $deduct
                $deduct ++;

            //
            }, $where, '`id` ASC');

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

            // Get field
            $localizable = $this->getLocalizable($params);

            // Get queue items
            Indi::model('QueueItem')->batch(function(&$r, &$deduct) use (&$queueTaskR, &$queueChunkR, $params) {

                $current = DOC . STD . $r->target;

                // If localization is going to turned Off - use `queueItem` entry's `value` as target value, else
                if ($params['toggle'] == 'n') {

                    //
                    if ($r->value == $params['source']) {
                        if (file_exists($current)) rename($current, DOC . STD . $queueChunkR->location);
                    }

                    //
                    else unlink($current);

                //
                } else file_put_contents($current, file_get_contents($r->result));

                // Write translation result
                $r->assign(array('stage' => 'apply'))->basicUpdate();

                // Reset batch offset
                $deduct ++;

                // Increment `applySize` prop on `queueChunk` entry and save it
                $queueChunkR->applySize ++; $queueChunkR->basicUpdate();

                // Increment `applySize` prop on `queueTask` entry and save it
                $queueTaskR->applySize ++; $queueTaskR->basicUpdate(false, false);

            }, $where, '`id` ASC');

            // Switch field's l10n-prop from intermediate to final value
            $localizable->assign(['l10n' => $params['toggle'] ?: 'y'])->save();

            // Remove original tpldoc file
            if ($params['toggle'] != 'n') unlink(DOC . STD . $queueChunkR->location);

            // Remember that our try to count was successful
            $queueChunkR->assign(array('applyState' => 'finished'))->basicUpdate();
        }

        // Mark stage as 'Finished' and save `queueTask` entry
        $queueTaskR->assign(array('state' => 'finished', 'applyState' => 'finished'))->save();
    }

    /**
     * Amend result, got from Google translate API
     *
     * @param $result
     * @return mixed
     */
    public function amendResult(&$result) {
        $result = str_replace(['<!-- <?', '?> -->'], ['<?', '?>'], $result);
    }

    /**
     * @param $params
     * @return Field_Row|Field_Rowset
     */
    public function getLocalizable($params) {

        // Split `location` on $table and $field
        list ($table, $field) = explode(':', $params['field']);

        // Return `field` entry
        return m($table)->fields($field);
    }
}