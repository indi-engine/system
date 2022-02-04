<?php
class Admin_QueueTaskController extends Indi_Controller_Admin {

    /**
     * Run queue
     */
    public function runAction() {

        // Start queue as a background process
        Indi::cmd('queue', ['queueTaskId' => $this->row->id]);

        // Flush msg saying that queue task started running
        jflush(true, sprintf('Queue Task "%s" started running', $this->row->title));
    }
}