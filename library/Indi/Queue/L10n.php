<?php
class Indi_Queue_L10n extends Indi_Queue {

    /**
     * Create `queueTask` entry and nested `queueChunk` entries.
     * Empty method, to be redeclared in child classes
     *
     * @param $params
     */
    public function chunk($params) {

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

            // Get table
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Count items
            $queueChunkR->countSize = db()->query('
                SELECT COUNT(`id`) FROM `' . $table . '`' . rif($queueChunkR->where, ' WHERE $1')
            )->fetchColumn();

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

            // Split `location` on $table and $field
            list ($table, $field) = explode(':', $queueChunkR->location);

            // Get last target
            $last = m('QueueItem')->row('`queueChunkId` = "' . $queueChunkR->id . '"', '`id` DESC')->target;

            // Check whether we will use setter method call (instead of google translate api call) as queue-stage
            $setter = method_exists(m($table)->new(), $_ = 'set' . ucfirst($field)) ? $_ : false;

            // Build WHERE clause
            $where = [];
            if ($queueChunkR->where) $where []= $queueChunkR->where;
            if ($last) $where []= '`id` > ' . $last;
            $where = $where ? im($where, ' AND ') : null;

            // Detect order column
            $orderColumn = m($table)->fields('move')->alias ?: m($table)->fields('alias')->alias ?: 'id';

            // Foreach entry matching chunk's definition
            m($table)->batch(function(&$r) use (&$queueTaskR, &$queueChunkR, $field, $params, $setter) {

                // Get value
                $value = $params['toggle'] == 'n'
                    ? $r->language($field, $params['source'])
                    : (preg_match('~^{"~', $r->$field)
                        ? json_decode($r->$field)->{$params['source']}
                        : $r->language($field, $params['source']) ?: $r->$field);

                // Create `queueItem` entry
                $queueItemR = m('QueueItem')->new([
                    'queueTaskId' => $queueTaskR->id,
                    'queueChunkId' => $queueChunkR->id,
                    'target' => $r->id,
                    'value' => $value
                ]);

                // Save `queueItem` entry
                $queueItemR->save();

                // Increment `queued` prop on `queueChunk` entry and save it
                $queueChunkR->itemsSize ++;
                $queueChunkR->itemsBytes += ($bytes = mb_strlen($value, 'utf-8') * $this->itemsBytesMultiplier($params, $setter));
                $queueChunkR->basicUpdate();

                // Increment `itemsSize` prop on `queueTask` entry and save it
                $queueTaskR->itemsSize ++;
                $queueTaskR->itemsBytes += $bytes;
                $queueTaskR->basicUpdate(false, false);

                // Fetch entries according to chunk's WHERE clause, and order by `id` ASC
            }, $where, '`' . $orderColumn . '` ASC');

            // Remember that our try to count was successful
            $queueChunkR->set(['itemsState' => 'finished'])->basicUpdate();
        }

        // Mark first stage as 'Finished' and save `queueTask` entry
        $queueTaskR->set(['state' => 'finished', 'itemsState' => 'finished'])->save();
    }

    public function queue($queueTaskId) {
    }
    public function apply($queueTaskId) {
    }

    /**
     * @param $queueTaskR
     * @param $entityR
     * @param $fieldR_having_l10nY
     * @param $where
     */
    public function appendChunk(&$queueTaskR, $entityR, $fieldR_having_l10nY, $where = []) {

        // Create `queueChunk` entry and setup basic props
        $queueChunkR = m('QueueChunk')->new([
            'queueTaskId' => $queueTaskR->id,
            'queueState' => $queueTaskR->queueState
        ]);

        // If $entity arg is really entity
        if ($entityR instanceof Entity_Row) {

            // Table and field names
            $table = $entityR->table; $field = $fieldR_having_l10nY->alias;

            // If field have real column within db table
            if ($fieldR_having_l10nY->columnTypeId) {

                // If it's an enumset-field
                if ($fieldR_having_l10nY->relation == 6) {

                    // Table and field names
                    $table = 'enumset'; $field = 'title';

                    // Build WHERE clause
                    $queueChunkR->where = sprintf('`fieldId` = "%s"', $fieldR_having_l10nY->id);

                // Else if it's a config-field
                } else if ($fieldR_having_l10nY->entry) {

                    // Table and field names
                    $table = 'param'; $field = 'cfgValue';

                    // Build WHERE clause
                    if ($where) $queueChunkR->where = im($where, ' AND ');

                //
                } else {

                    // Build WHERE clause
                    if ($where) $queueChunkR->where = im($where, ' AND ');
                }

                // Setup `location`
                $queueChunkR->location = $table . ':' . $field;

            // Else if it's a file-upload field
            } else if ($fieldR_having_l10nY->foreign('elementId')->alias == 'upload') {

                // Get params
                $params = json_decode($queueTaskR->params, true);

                // Get tpldoc file abs path
                $tpl = m($table)->tpldoc($field, true, $fieldR_having_l10nY->l10n == 'y' ? $params['source'] : false);

                // If exists
                if (is_file($tpl)) {

                    // If l10n is turned on, e.g. we're going to turn it Off, spoof tpldoc path
                    // for it to be pointing to filename of a template, that will remaining after turning Off
                    if ($fieldR_having_l10nY->l10n == 'y') $tpl = m($table)->tpldoc($field, true, false);

                    // Set location
                    $queueChunkR->location = str_replace(DOC . STD , '', $tpl);
                }
            }

        // Else if it is an instance of Section_Row
        } else {

            // Get params
            $params = json_decode($queueTaskR->params, true);

            // Get table and field
            list ($section, $action) = explode(':', $params['action']);

            // Set `section2action` entry
            $section2actionR = section2action($section, $action);

            // Get tpldoc file abs path
            $tpl = DOC . STD . '/' . ini('view')->scriptPath . '/admin/'
                . $section . '/' . $action . rif($section2actionR->l10n == 'y', '-' . $params['source']) . '.php';

            // If exists
            if (is_file($tpl)) {

                // If l10n is turned on, e.g. we're going to turn it Off, spoof tpldoc path
                // for it to be pointing to filename of a template, that will remaining after turning Off
                if ($section2actionR->l10n == 'y') $tpl = preg_replace('~-' . $params['source'] . '.php$~', '.php', $tpl);

                // Set location
                $queueChunkR->location = str_replace(DOC . STD , '', $tpl);
            }
        }

        // If current method is used only for detecting WHERE clause - return detected
        if ($this->fieldId) return $queueChunkR->where;

        // Save `queueChunk` entry
        $queueChunkR->save();

        // Increment `countChunk`
        $queueTaskR->chunk ++;
        $queueTaskR->basicUpdate(false, false);

        // Return `queueChunk` entry
        return $queueChunkR;
    }

    /**
     * Used with $queueChunkR->itemsSize
     *
     * @return int
     */
    public function itemsBytesMultiplier($params, $setter = false) {
        return $params['toggle'] != 'n' && !$setter;
    }

    /**
     * @param $result
     * @param $queueItemR
     * @return mixed
     */
    public function amendResult(&$result, $queueItemR) {

    }
}