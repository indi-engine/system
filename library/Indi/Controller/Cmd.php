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
        Indi::model('QueueTask')->fetchRow($queueTaskId)->start();
    }

    /**
     * Manage channels
     */
    public function channelAction($arg) {

        // If $arg is an array - create `realtime` entry, that will represent browser tab / websocket channel
        if (is_array($arg)) {

            // If $_COOKIE['prevcid'] given and such channel exists
            if (Indi::rexm('wskey', $prevcid = str_replace(' ', '+', $_COOKIE['prevcid']))
                && $r = m('Realtime')->fetchRow([
                    '`realtimeId` = "' . $arg['realtimeId'] . '"',
                    '`token` = "' . $prevcid . '"'
                ])) {
                i('spoof ' . $prevcid . ' with ' . $arg['token'], 'a');

                // Spoof `token` of an existing `realtime` entry of `type` = 'channel'
                $r->assign($arg)->save();

            // Else create new `realtime` entry of `type` = 'channel'
            } else {
                i('newtab ' . $arg['token']  . ' done', 'a');
                m('Realtime')->createRow($arg, true)->save();
            }
        }

        // Else if $arg is integer - delete such `realtime` entry
        elseif (Indi::rexm('int11', $arg)) {
            i('closetab ' . $arg  . ' done', 'a');
            m('Realtime')->row($arg)->delete();
        }
    }
}