<?php
class Admin_RealtimeController extends Indi_Controller_Admin {

    /**
     * Tokens of deleted records grouped by type
     *
     * @var array
     */
    protected $deleted = [
        'session' => [],
        'channel' => [],
        'context' => [],
    ];

    /**
     * @throws Exception
     */
    public function preDispatch($args = []) {

        // If it's special action
        if (in(uri()->action, 'restart,cleanup,opentab,closetab,status,stop')) {

            // Call the desired action method
            $this->call(uri()->action, $args);

        // Else
        } else {

            // Do cleanup each time admin opened realtime section
            if (uri()->action == 'index' && !uri()->format) $this->cleanupAction(false);

            // Call parent
            parent::preDispatch();
        }
    }

    /**
     * Stop server listening for rabbitmq-events of type 'queue.deleted'
     */
    public function stopAction() {

        // Check session
        $this->auth(true);

        // Check status of listener
        $flush = $this->stop();

        // Flush result
        jflush($flush);
    }

    /**
     * Check whether server listening for rabbitmq-events of type 'queue.deleted' is already running, and start if it is not
     */
    public function statusAction() {

        // Check session
        $this->auth(true);

        // Check status of listener-server
        $flush = $this->status();

        // If listener-server is not running - start it
        if ($flush['success'] === false) $flush = $this->start();

        // Flush result
        jflush($flush);
    }

    /**
     * Check whether listener-server pid-file exists and contains PID of existing process
     *
     * @return array jflush-able
     */
    public function status() {

        // Get pid
        $pid = getpid();

        // Else if process having such PID is not found
        return $pid
            ? ['success' => true , 'pid' => $pid]
            : ['success' => false, 'msg' => 'No process found'];
    }

    /**
     * Start server listening for rabbitmq-events of type 'queue.deleted'
     *
     * @return array
     */
    public function start() {

    }

    /**
     * Stop listener-server.
     * Returns jflush-able array-result, having at least 'success'-key
     * It is assumed that 'success' should be `true` if no listener-server is running, and this covers the following cases:
     * - no pid-file exists
     * - it exists but empty, e.g. contains no PID
     * - it contains, but no process with such PID found
     * - process with such PID found and it was successfully killed
     *
     * @return array
     */
    public function stop() {

    }

    /**
     * Restart listener-server
     */
    public function restartAction() {

        // Check session
        $this->auth(true);

        // Stop listener-server, if it's running
        $stop = $this->stop();

        // If stopping failed - flush failure
        if (!$stop['success']) jflush($stop);

        // Start listener-server
        $start = $this->start();

        // Flush result
        jflush($start);
    }

    /**
     * Delete `realtime`-entries of type=session, which have no session-files anymore
     *
     * @throws Exception
     */
    public function cleanupAction($jflush = true) {

        // Get session files dir
        $dir = ini_get('session.save_path') ?: sys_get_temp_dir();

        // Replace slashes
        $dir = str_replace('\\', '/', $dir);

        // Fetch `realtime` entries by chunks containing 500 entries each
        m('realtime')->batch(function(Realtime_Row $r) use ($dir) {

            // Build session file path
            $file = $dir . '/sess_' . $r->token;

            // If session file exists on disk - skip
            if (file_exists($file)) return;

            // Else delete entry
            $r->delete();

        // Only entries of type=session which are expired
        }, '`type` = "session"');

        // Flush success
        if ($jflush) jflush(true);
    }

    /**
     * Create `realtime`-record having `type` = "channel"
     */
    public function opentabAction() {

        // Check session
        $this->auth(true);

        // If no `realtime` entry of `type` = "session" found
        if (!$session = m('Realtime')->row(['`type` = "session"', '`token` = "' . session_id() . '"']))

            // Flush failure
            jflush(false);

        // Else create `realtime` entry of `type` = "channel", create queue and get it's name
        $cid = $session->channel()->queue();

        // Flush success
        jflush(true, ['cid' => $cid]);
    }

    /**
     * Server listening for rabbitmq-events of type 'queue.deleted'
     *
     * Can be:
     *   - started by 'php indi -d realtime/closetab' command. Usage of '-d' flag is optional, and provides detached/background mode
     *   - checked by 'php indi -i realtime/closetab' command. If running - PID will be echoed
     *   - killed  by 'php indi -k realtime/closetab' command
     */
    public function closetabAction() {

        // Name of the queue to be a destination for queue.deleted-events forwarded by 'amq.rabbitmq.event'-exchange
        $queue = ini()->db->name;

        // Declare queue that we'll be working with
        mq()->queue_declare($queue, false, false, true);

        // Make sure event of type 'queue.deleted' will be sent to our queue by 'amq.rabbitmq.event' exchange
        mq()->queue_bind($queue, 'amq.rabbitmq.event', 'queue.deleted');

        // Start serving
        while (true) {

            // While at least 1 unprocessed event of type 'queue.deleted' is available
            while ($msg = mq()->basic_get($queue)) {

                // Get channel token, which is used as the queue name
                $channel = $msg->get_properties()['application_headers']->getNativeData()['name'];

                // Split by --
                list ($dbname, $channel) = explode('--', $channel);

                // If it was queue from this instance of Indi Engine
                if ($dbname == ini()->db->name) {

                    // Delete realtime-record of type=channel
                    m('realtime')->row(['`token` = "' . $channel . '"', '`type` = "channel"'])->delete();

                    // Acknowledge the queue about that message is processed
                    mq()->basic_ack($msg->getDeliveryTag());

                // Else
                } else {

                    // Reject message
                    mq()->basic_nack($msg->getDeliveryTag());
                }
            }

            // Wait 200ms
            usleep(200000);
        }
    }

    /**
     * Append role title to admin title, and highlight tokens
     *
     * @param $item
     * @param $r
     */
    public function renderGridDataItem(&$item, $r) {

        // Append role title to admin title
        $item['adminId'] .= ' [' .  $item['roleId'] . ']';

        // Highlight session token
        if ($item['token'] == $_COOKIE['PHPSESSID'])
            $item['_render']['title']
                = preg_replace('~( - )(.*?)(, )~', '$1<span style="color: #35baf6;">$2</span>$3', $item['_render']['title']);

        // Highlight channel/tab token
        else if (defined('CID') && $item['token'] == CID)
            $item['_render']['title']
                = preg_replace('~( - )(.*?)$~', '$1<span style="color: #35baf6;">$2</span>', $item['_render']['title']);
    }

    /**
     * Collect tokens of entries to be deleted
     *
     * @param Indi_Db_Table_Rowset $toBeDeletedRs
     */
    public function preDelete(Indi_Db_Table_Rowset $toBeDeletedRs) {

        // Collect tokens
        foreach ($this->deleted as $type => &$tokenA)
            $tokenA = $toBeDeletedRs->select($type, 'type')->col('token');

        // Send msg saying it's not possible to delete context-entries manually
        if (count($this->deleted['context'])) msg(I_REALTIME_CONTEXT_AUTODELETE_ONLY, false);

        // Exclude entries having type=context, as they should not be deleted directly
        $toBeDeletedRs->exclude('context', 'type');
    }

    /**
     * Reload browser tabs, if corresponding channel-entries were deleted
     *
     * @param $deleted
     */
    public function postDelete() {
        foreach ($this->deleted as $type => $tokenA)
            foreach ($tokenA as $token)
                if ($type == 'channel') Indi::ws(['type' => 'F5', 'to' => $token]);
    }
}