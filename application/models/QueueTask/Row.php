<?php
class QueueTask_Row extends Indi_Db_Table_Row {

    /**
     * Do the job
     *
     * @return int
     */
    public function start(){

        // Set `procID` and `procSince`
        $this->set(['procID'  => getmypid(), 'procSince' => date('Y-m-d H:i:s')])->save();

        // Build queue class name
        $queueClassName = 'Indi_Queue_' . ucfirst($this->title);

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

    /**
     * @return int|void
     */
    public function basicUpdate() {

        // Call onBeforeUpdate
        $this->onBeforeUpdate();

        // Call parent
        $this->callParent();
    }

    /**
     * Make sure queue will be resumed after valid API key prompted and submitted
     */
    public function onUpdate() {

        // Moved to noticeGetter-level
        return;

        // If queueState-field was changed to 'error'
        if ($this->fieldWasChangedTo('queueState', 'error')) {

            // Make sure queue will be resumed after valid API key provided
            Indi::ws([
                'type' => 'load',
                'to' => 'dev',
                'href' => "/queueTask/run/id/{$this->id}/gapikey/"
            ]);
        }
    }
}