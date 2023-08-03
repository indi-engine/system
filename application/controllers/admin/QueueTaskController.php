<?php
class Admin_QueueTaskController extends Indi_Controller_Admin {

    /**
     * Run queue
     */
    public function runAction() {

        // If command is 'gapikey'
        if (uri()->command == 'gapikey') {

            // Setup ini-prop name
            $name = 'lang.gapi.key';

            // Prepare message
            $msg = __(I_GAPI_RESPONSE, t()->row->error);

            // Prompt for valid Google Cloud Translate API key
            $prompt = $this->prompt($msg, [[
                'xtype' => 'textfield',
                'emptyText' => I_GAPI_KEY_REQUIRED,
                'width' => 250,
                'name' => $name
            ]]);

            // Check prompt data
            jcheck([
                $name => [
                    'rex' => '~^[a-zA-Z0-9]+$~'
                ]
            ], $prompt);

            // Write into ini-file
            ini($name, $prompt[$name]);
        }

        // Start queue as a background process
        Indi::cmd('queue', ['queueTaskId' => $this->row->id]);

        // Flush success
        jflush(true);
    }

    /**
     * If there is an error - make sure it will be shown as a native tooltip
     *
     * @param $item
     * @param $row
     */
    public function adjustGridDataItem(&$item, Indi_Db_Table_Row $row) {
        if ($item['queueState'] === 'error')
            $item['_render']['queueState'] = preg_replace(
                '~color="[^"]+"~',
                '$0 title="' . $row->attr('error') . '"',
                $item['_render']['queueState']
            );
    }
}