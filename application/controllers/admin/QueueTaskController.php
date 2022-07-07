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

    /**
     * If there is an error - make sure it will be shown as a native tooltip
     *
     * @param $item
     * @param $row
     */
    public function adjustGridDataItem(&$item, Indi_Db_Table_Row $row) {
        if ($item['$keys']['queueState'] == 'error')
            $item['_render']['queueState']
                = preg_replace('~color=red~', '$0 title="' . $row->attr('error') . '"', $item['queueState']);
    }
}