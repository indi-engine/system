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
     * Path to err-file
     *
     * @var string
     */
    protected $err = DOC . STD . '/application/ws.err';

    /**
     * Path to 'queue.deleted'-events listener-server executable php file
     *
     * @var string
     */
    protected $wss = VDR . '/system/application/ws.php';

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

        // Check whether both pid- and err- files are writable, and if no - return jflush-able result
        foreach (['err'] as $file)
            if (file_exists($this->$file) && !is_writable($this->$file))
                return ['success' => false, 'msg' => "ws.$file file is not writable"];

        // Trim left slash from wss
        $this->wss = ltrim($this->wss, '/');

        // Prepare args, which will be used to identify the background process
        $args = "--instance=" . ini()->db->name;

        // Build listener-server startup cmd
        $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
            ? "start /B php {$this->wss} $args 2>&1"
            : "php {$this->wss} $args > /dev/null &";

        // Start listener server
        wslog('------------------------------');
        wslog('Exec: ' . $result['cmd']);
        preg_match('/^WIN/i', PHP_OS)
            ? pclose(popen($result['cmd'], "r"))
            : exec($result['cmd'], $result['output'], $result['return']);

        // Log output
        wslog('Output: ' . print_r($result['output'], true) . ', return: ' . $result['return']);

        // Unset 'cmd'-key
        unset($result['cmd']); $result['success'] = true;

        // Return jflush-able result
        return $result;
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

        // Get status of listener-server
        $status = $this->status();

        // If listener-server is NOT currently running - nothing to do here
        if ($status['success'] === false) return ['success' => true] + $status;

        // Build listener server kill cmd
        $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
            ? "taskkill /f /PID  {$status['pid']} 2>&1"
            : "kill -9 {$status['pid']}";

        // Kill listener-server process
        wslog('------------------------------');
        wslog('Exec: ' . $result['cmd']);
        exec($result['cmd'], $result['output'], $result['return']);
        wslog('Output: ' . print_r($result['output'], true) . ', return: ' . $result['return']);

        // Prepare flushable result
        $flush = ['success' => !$result['return']];
        if (is_array($result['output']) && isset($result['output'][0]))
            if (strlen($flush['msg'] = mb_convert_encoding($result['output'][0], 'utf-8', 'CP-866')))
                unset($result['output']);

        // Append command execution raw result
        $flush['result'] = $result;

        // Truncate err- and pid- files
        file_put_contents($this->err, '');
        file_put_contents($this->pid, '');

        // Return flushable result
        return $flush;
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
     * Delete `realtime`-record having `type` = "channel"
     */
    public function closetabAction($cid) {

        // Check CID
        jcheck(['cid' => ['req' => true, 'rex' => 'cid']], ['cid' => $cid]);

        // Try to found `realtime` entry having such CID and `type` = 'channel'
        if ($r = m('realtime')->row(['`token` = "' . $cid . '"', '`type` = "channel"'])) {

            // Delete
            $r->delete();

            // Flush success
            jflush(true);
        }

        // Flush failure
        jflush(false);
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