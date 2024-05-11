<?php
class Indi_Controller_Detached extends Indi_Controller {

    public function preDispatch() {

        // Stop
        if (!CMD) jflush(false); else session_write_close();

        // Call parent
        $this->callParent();
    }

    /**
     * Convert serialized $entry back to Indi_Db_Table_Row instance and call the $method
     *
     * @param string $entry
     * @param string $method
     */
    public function processAction(string $entry, string $method) {
        unserialize($entry)->$method();
    }
}