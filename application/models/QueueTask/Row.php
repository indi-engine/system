<?php
class QueueTask_Row extends Indi_Db_Table_Row {

    /**
     * Title describing whether queue task is started from scratch or is resumed afther the interruption
     * Should not be set up here, as the value is setup and retrieved automatically when needed
     *
     * @var string
     */
    private static $title;

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

        // Get fresh instance of current entry
        $fresh = $this->model()->row($this->id);

        // Get total iterations quantity
        $total = $fresh->countSize * (2 + (int) ($fresh->queueState !== 'noneed'));

        // Get quantity of iterations already done
        $index = $this->itemsSize + $this->queueSize + $this->applySize;

        // Prepare title prefix
        $prefix = $this->model()->title() . " ID#{id} - ";

        // Prepare progress title
        self::$title = join('', [
            $prefix,
            $this->affected("procID", true) ? I_QUEUE_RESUMED : I_QUEUE_STARTED,
            ": {percent}%"
        ]);

        // Setup progress
        if ($total) progress(self::$title, [$index, $total], $this->id);

        // Create queue items
        $queue->items($this->id);

        // Process queue items
        $queue->queue($this->id);

        // Apply results
        $queue->apply($this->id);

        // Refresh progress
        if ($total) progress(true, $prefix . I_QUEUE_COMPLETED, $this->id);
    }

    /**
     * @return int|void
     */
    public function basicUpdate($notices = false, $amerge = true, $realtime = true, $inQtySum = true) {

        // Call onBeforeUpdate
        $this->onBeforeUpdate();

        // Call parent
        $this->callParent();

        // If progress changed
        if ($this->countSize && $this->affected('itemsSize,queueSize,applySize')) {

            // Refresh progress
            progress($this->itemsSize + $this->queueSize + $this->applySize, self::$title, $this->id);
        }
    }

    /**
     * Make sure queue will be resumed after valid API key prompted and submitted
     */
    public function onUpdate() {

        // If queueState-field was changed to 'error'
        if ($this->fieldWasChangedTo('queueState', 'error')) {

            // Show error
            progress(false, __(I_GAPI_RESPONSE, $this->error), $this->id);

            // Make sure queue will be resumed after valid API key provided
            Indi::ws([
                'type' => 'load',
                'to' => 'dev',
                'href' => "/queueTask/run/id/$this->id/gapikey/"
            ]);
        }
    }

    /**
     * Update time spent on this queue task so far
     */
    public function onBeforeUpdate() {

        // Update time spent on this queue task so far
        if (!Indi::rexm('zerodate', $this->procSince))
            $this->procSpan = time() - strtotime($this->procSince);
    }
}