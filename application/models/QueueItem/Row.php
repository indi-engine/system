<?php
class QueueItem_Row extends Indi_Db_Table_Row {

    /**
     * Reapply result, if need
     */
    public function onSave() {

        // If systen `reapply` flag is set
        if ($this->_system['reapply'] ?? null) {

            // Get `queueTask` entry
            $queueTaskR = $this->foreign('queueTaskId');

            // Prepare `queueChunk` and `queueItems` entries for reapply
            $this->foreign('queueChunkId')->set(['applyState' => 'waiting'])->save();
            $this->foreign('queueChunkId')->nested('queueItem')->set(['stage' => 'queue'])->basicUpdate();

            // Reapply
            $queueClassName = 'Indi_Queue_' . ucfirst($queueTaskR->title);
            $queue = new $queueClassName();
            $queue->apply($queueTaskR->id);
        }
    }
}