<?php
class Admin_RealtimeController extends Indi_Controller_Admin {

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

        // If no active session found
        if (!Realtime::session(true))
            if ($this->confirm(I_THROW_OUT_SESSION_EXPIRED, 'OKCANCEL', '', '401 Unauthorized'))
                jflush(false, ['throwOutMsg' => true]);

        // Get pid and err files
        $pid = DOC . STD . '/application/ws.pid';
        $err = DOC . STD . '/application/ws.err';

        // If websocket-server lock-file exists, and contains websocket-server's process ID, and it's an integer
        if (is_file($pid) && $wsPid = (int) trim(file_get_contents($pid))) {

            // If such process is found - flush msg and exit
            if (checkpid($wsPid)) jflush(true);
        }

        // Check whether pid-file is writable
        if (file_exists($pid) && !is_writable($pid)) jflush(false, 'ws.pid file is not writable');

        // Check whether err-file is writable
        if (file_exists($err) && !is_writable($err)) jflush(false, 'ws.err file is not writable');

        // Path to websocket-server php script
        $wsServer = ltrim(VDR, '/') . '/system/application/ws.php';

        // Get host with no port
        $host = $_SERVER['SERVER_NAME'];

        // Close session
        session_write_close();

        // Build websocket startup cmd
        $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
            ? sprintf('start /B %sphp %s 2>&1', rif(ini('general')->phpdir, '$1/'), $wsServer)
            : 'nohup wget --no-check-certificate -qO- "'. ($_SERVER['REQUEST_SCHEME'] ?: 'http') . '://' . $host . STD . '/' . $wsServer . '" > /dev/null &';

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
        unset($result['cmd']);

        // Flush msg
        jflush(true, $result);
    }

    /**
     * Prevent `realtime` entry having `type` = "context" from being created for 'restart' action
     */
    public function restartAction() {

        // Get lock file
        $wsLock = DOC . STD . '/application/ws.pid';
        $wsErr  = DOC . STD . '/application/ws.err';

        // If websocket-server lock-file exists, and contains websocket-server's process ID, and it's an integer
        if (is_file($wsLock) && $wsPid = (int) trim(file_get_contents($wsLock))) {

            // If such process is found
            if (checkpid($wsPid)) {

                // Build websocket server kill cmd
                $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
                    ? sprintf('taskkill /f /PID  %s 2>&1', $wsPid)
                    : sprintf('kill -9 %s', $wsPid);

                // Kill websocket server
                wslog('------------------------------');
                wslog('Exec: ' . $result['cmd']);
                exec($result['cmd'], $result['output'], $result['return']);
                wslog('Output: ' . print_r($result['output'], true) . ', return: ' . $result['return']);

                // Unset 'cmd'-key
                unset($result['cmd']);

                $flush = ['success' => true];
                if (is_array($result['output']) && isset($result['output'][0]))
                    if (strlen($flush['msg'] = mb_convert_encoding($result['output'][0], 'utf-8', 'CP-866')));
                        unset($result['output']);

                $flush['result'] = $result;

                // Truncate
                file_put_contents($wsErr, '');

                // Flush msg
                jflush($flush);
            }
        }

        // Flush response
        jflush(false, 'There is nothing to be restarted');
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
}