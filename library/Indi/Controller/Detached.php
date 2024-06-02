<?php
class Indi_Controller_Detached extends Indi_Controller {

    public function preDispatch() {

        // Stop
        if (!CMD) jflush(false); else session_write_close();

        // Call parent
        $this->callParent();
    }

    /**
     * Convert serialized $record back to Indi_Db_Table_Row instance and call the $method, with $params if given
     *
     * @param string $record
     * @param string $method
     * @param array $params
     */
    public function processAction(string $record, string $method, $params = []) {
        call_user_func_array([unserialize($record), $method], $params);
    }
}