<?php
class QueueTask_Row extends Indi_Db_Table_Row {

    /**
     * Do the job
     *
     * @return int
     */
    public function start(){

        // Set `procID` and `procSince`
        $this->assign(['procID'  => getmypid(), 'procSince' => date('Y-m-d H:i:s')])->save();

        // Build queue class name
        $queueClassName = 'Indi_Queue_L10n_' . ucfirst($this->title);

        // Create queue class instance
        $queue = new $queueClassName();

        // Count how many queue items should be created
        $queue->count($this->id);

        // Create queue items
        $queue->items($this->id);

        // Process queue items
        $queue->queue($this->id);

        // Apply results
        $queue->apply($this->id);
    }
}