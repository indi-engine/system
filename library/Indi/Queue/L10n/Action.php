<?php
class Indi_Queue_L10n_Action extends Indi_Queue_L10n_FieldToggleL10nUpload {

    /**
     * Create queue chunks
     *
     * @param array $params
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

        // Get table and field
        list ($section, $action) = explode(':', $params['action']);

        // Create separate `queueChunk`-trees for each fraction
        foreach ($params['target'] as $fraction => $targets)
            $this->appendChunk($queueTaskR, section($section), action($action), [], $fraction);

        // Return `queueTask` entry
        return $queueTaskR;
    }

    /**
     * @param $queueTaskR
     * @param $sectionR
     * @param $actionR
     * @param array $where
     * @param string $fraction
     * @return Indi_Db_Table_Row|string
     */
    public function appendChunk(&$queueTaskR, $sectionR, $actionR, $where = [], $fraction = 'none') {

        // Create parent `queueChunk` entry and setup basic props
        $queueChunkR = Indi_Queue_L10n::appendChunk($queueTaskR, $sectionR, $actionR, $where);

        // Setup `fraction` and `where` props
        $queueChunkR->set(['fraction' => $fraction])->save();

        // Return `queueChunk` entry
        return $queueChunkR;
    }

    /**
     * @param $params
     * @return Field_Row|Field_Rowset
     */
    public function getLocalizable($params) {

        // Split `location` on $table and $field
        list ($section, $action) = explode(':', $params['action']);

        // Return `field` entry
        return section2action($section, $action);
    }
}