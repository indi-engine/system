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
     * Path to pid-file
     *
     * @var string
     */
    protected $pid = DOC . STD . '/application/ws.pid';

    /**
     * Path to err-file
     *
     * @var string
     */
    protected $err = DOC . STD . '/application/ws.err';

    /**
     * Path to WebSocket-server executable php file
     *
     * @var string
     */
    protected $wss = VDR . '/system/application/ws.php';

    /**
     * @throws Exception
     */
    public function preDispatch() {

        // If it's special action
        if (in($action = uri()->action, 'restart,cleanup,opentab,closetab,status'))
            $this->{$action . 'Action'}();

        // Else
        else {

            // Do cleanup each time admin opened realtime section
            if ($action == 'index' && !uri()->format) $this->cleanupAction(false);

            // Call parent
            parent::preDispatch();
        }
    }

    /**
     * Check whether websocket server is already running, and start it if not
     */
    public function statusAction() {

        // Check session
        $this->auth(true);

        // Check status of websocket-server
        $flush = $this->status();

        // If websocket-server is not running - start it
        if ($flush['success'] === false) $flush = $this->start();

        // Flush result
        jflush($flush);
    }

    /**
     * Check whether websocket-server pid-file exists and contains websocket-server's existing process ID
     *
     * @return array jflush-able
     */
    public function status() {

        // If no pid-file exists - return false
        if (!is_file($this->pid)) return ['success' => false, 'msg' => 'No pid file exists'];

        // Else if no PID contained inside that pid-file
        if (!$pid = (int) trim(file_get_contents($this->pid))) return ['success' => false, 'msg' => 'Empty pid file'];

        // Else if process having such PID is not found
        return checkpid($pid)
            ? ['success' => true , 'pid' => $pid]
            : ['success' => false, 'msg' => 'No process found with such pid'];
    }

    /**
     * Start websocket-server
     *
     * @return array
     */
    public function start() {

        // Check whether both pid- and err- files are writable, and if no - return jflush-able result
        foreach (['pid', 'err'] as $file)
            if (file_exists($this->$file) && !is_writable($this->$file))
                return ['success' => false, 'msg' => "ws.$file file is not writable"];

        // Get host with no port
        $host = $_SERVER['SERVER_NAME'];

        // Trim left slash from wss
        $this->wss = ltrim($this->wss, '/');

        // Close session
        session_write_close();

        // Build websocket startup cmd
        $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
            ? sprintf('start /B %sphp %s 2>&1', rif(ini('general')->phpdir, '$1/'), $this->wss)
            : 'nohup wget --no-check-certificate -qO- "'. ($_SERVER['REQUEST_SCHEME'] ?: 'http') . '://' . $host . STD . '/' . $this->wss . '" > /dev/null &';

        // Start websocket server
        wslog('------------------------------');
        wslog('Exec: ' . $result['cmd']);

        // Exec
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
     * Stop websocket-server.
     * Returns jflush-able array-result, having at least 'success'-key
     * It is assumed that 'success' should be `true` if no websocket-process is running, and this covers the following cases:
     * - no pid-file exists
     * - it exists but empty, e.g. contains no PID
     * - it contains, but no process with such PID found
     * - process with such PID found and it was successfully killed
     *
     * @return array
     */
    public function stop() {

        // Get status of websocket-server
        $status = $this->status();

        // If websocket-server is NOT currently running - nothing to do here
        if ($status['success'] === false) return ['success' => true] + $status;

        // Build websocket server kill cmd
        $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
            ? sprintf('taskkill /f /PID  %s 2>&1', $status['pid'])
            : sprintf('kill -9 %s', $status['pid']);

        // Kill websocket server
        wslog('------------------------------');
        wslog('Exec: ' . $result['cmd']);
        exec($result['cmd'], $result['output'], $result['return']);
        wslog('Output: ' . print_r($result['output'], true) . ', return: ' . $result['return']);

        // Unset 'cmd'-key
        unset($result['cmd']);

        //
        $flush = ['success' => !$result['return']];
        if (is_array($result['output']) && isset($result['output'][0]))
            if (strlen($flush['msg'] = mb_convert_encoding($result['output'][0], 'utf-8', 'CP-866')));
                unset($result['output']);

        //
        $flush['result'] = $result;

        // Truncate err- and pid- files
        file_put_contents($this->err, '');
        file_put_contents($this->pid, '');

        // Return flushable result
        return $flush;
    }

    /**
     * Restart websocket-server
     */
    public function restartAction() {

        // Check session
        $this->auth(true);

        // Stop websocker-server, if it's running
        $stop = $this->stop();

        // If stopping failed - flush failure
        if (!$stop['success']) jflush($stop);

        // Start websocket-server
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
        $session = ini_get('session.save_path') ?: sys_get_temp_dir();

        // Collect session ids
        foreach (glob($session . '/sess_*') as $file)
            $sess [] = Indi::rexm('~/sess_([0-9a-z]+)$~', $file, 1);

        // Fetch `realtime` entries by chunks containing 500 entries each
        m('realtime')->batch(function(Realtime_Row $r){

            // Delete entry
            $r->delete();

        // Only entries of type=session which are expired
        }, ['`type` = "session"', '`token` NOT IN ("' . im($sess, '","') . '")']);

        // Flush success
        if ($jflush) jflush(true);
    }

    /**
     * Create `realtime`-record having `type` = "channel"
     */
    public function opentabAction() {

        // Check CID
        jcheck(['cid' => ['req' => true, 'rex' => 'wskey']], ['cid' => CID]);

        // If `realtime` entry of `type` = "session" found
        if ($session = m('Realtime')->row(['`type` = "session"', '`token` = "' . session_id() . '"'])) {

            // Prepare data for `realtime` entry of `type` = "channel"
            $data = [
                'type' => 'channel', 'token' => CID,
                'realtimeId' => $session->id, 'spaceSince' => date('Y-m-d H:i:s')
            ] + $session->toArray();

            // Unset 'id'
            unset($data['id'], $data['title']);

            // Save into `realtime` table using separate background process
            Indi::cmd('channel', ['arg' => $data]);

            // Flush success
            jflush(true);
        }

        // Flush failure
        jflush(false);
    }

    /**
     * Delete `realtime`-record having `type` = "channel"
     */
    public function closetabAction() {

        // Check CID
        jcheck(['cid' => ['req' => true, 'rex' => 'wskey']], ['cid' => CID]);

        // Try to found `realtime` entry having such CID and `type` = 'channel'
        if ($r = m('realtime')->row(['`token` = "' . CID . '"', '`type` = "channel"'])) {

            // Delete via CmdController->channelAction()
            Indi::cmd('channel', ['arg' => $r->id]);

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