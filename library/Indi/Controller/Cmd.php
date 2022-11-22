<?php
class Indi_Controller_Cmd extends Indi_Controller {

    public function preDispatch() {

        // Stop
        if (!CMD) jflush(false); else session_write_close();

        // Call parent
        $this->callParent();
    }

    /**
     * Start running queue
     *
     * @param $queueTaskId
     */
    public function queueAction($queueTaskId) {
        m('QueueTask')->row($queueTaskId)->start();
    }
}