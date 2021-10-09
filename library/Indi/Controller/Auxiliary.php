<?php
/**
 * Controller for auxiliary abilities
 */
class Indi_Controller_Auxiliary extends Indi_Controller {

    /**
     * Provide a file download ability
     */
    public function downloadAction(){

        // If 'id' param is not set or is not an integer
        if (!preg_match('/^[0-9]+$/', Indi::uri('id'))) die(I_DOWNLOAD_ERROR_NO_ID);

        // If 'field' param is not set or is not an integer
        if (!preg_match('/^[0-9]+$/', Indi::uri('field'))) die(I_DOWNLOAD_ERROR_NO_FIELD);

        // Get the field
        $fieldR = Indi::model('Field')->fetchRow('`id` = "' . Indi::uri('field') . '"');

        // If field was not found
        if (!$fieldR) die(I_DOWNLOAD_ERROR_NO_SUCH_FIELD);

        // Get extended info about field
        $fieldR = Indi::model($fieldR->entityId)->fields($fieldR->alias);

        // If field was not found
        if (!$fieldR) die(I_DOWNLOAD_ERROR_NO_SUCH_FIELD);

        // If field is not a file upload field, e.g does not deal with files
        if ($fieldR->foreign('elementId')->alias != 'upload') die(I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES);

        // Get the row
        $r = Indi::model($fieldR->entityId)->fetchRow('`id` = "' . Indi::uri('id') . '"');

        // If row was not found
        if (!$r) die(I_DOWNLOAD_ERROR_NO_SUCH_ROW);

        // Get the file
        list($abs) = glob($r->dir() . $r->id . '_' . $fieldR->alias . '.*');

        // If there is no file
        if (!$abs) die(I_DOWNLOAD_ERROR_NO_FILE);

        // Declare an array, for containing downloading file title parts
        $title = array();

        // Append entity title to filename parts array, if needed
        //if ($fieldR->params['prependEntityTitle'] == 'true') $title[] = Indi::model($fieldR->entityId)->title() . ',';

        // Append row title to filename parts array
        if ($fieldR->params['rowTitle'] != 'false') $title[] = $r->dftitle($fieldR->alias);

        // Append entity title to filename parts array, if needed
        if ($fieldR->params['appendFieldTitle'] != 'false') $title[] = '- ' . $fieldR->title;

        // Append entity title to filename parts array, if needed
        if (strlen($fieldR->params['postfix'])) {
            Indi::$cmpTpl = $fieldR->params['postfix']; eval(Indi::$cmpRun); $title[] = Indi::cmpOut();
        }

        // Get the extension of the file
        $ext = preg_replace('/.*\.([^\.]+)$/', '$1', $abs);

        // Get the imploded file title, with extension appended
        $title = implode(' ', $title) . '.' . $ext;

        // If user's browser is Microsoft Internet Explorer - do a filename encoding conversion
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) $title = iconv('utf-8', 'windows-1251', $title);

        // If finfo-extension enabled
        if (function_exists('finfo_open')) {
        
            // Create a file_info resource
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            // Get the mime-type
            $type = finfo_file($finfo, $abs);

            // If there was an error while getting info about file
            if (!$type) die(I_DOWNLOAD_ERROR_FILEINFO_FAILED);

            // Close the fileinfo resource
            finfo_close($finfo);
        }

        // Replace " with ', as browsers replaces " with _ or - or, maybe, with something else
        $title = str_replace('"', "'", $title);
        
        // Start download
        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename="' . $title . '";');
        header('Content-Length: ' . filesize($abs));
        readfile($abs);
        die();
    }

    /**
     * Generate link to http://colorzilla.com
     */
    public function shadegradAction() {
        $colors = ar('dddddd,ffffff');
        $step = 2;
        for ($i = 0; $i <= 100;) {
            foreach ($colors as $color) {
                $str[] = $color . '+' . $i;
                $str[] = $color . '+' . ($i + $step);
                $i += $step;
            }
        }
        die('<a target="_blank" href="http://colorzilla.com/gradient-editor/#' . im($str) . '">color</a>');
    }
    
    /**
     * Check whether websocket server is already running, and start it if not
     */
    public function websocketAction() {

        // Close session
        session_write_close();

        // Get lock file
        $wsLock = DOC . STD . '/core/application/ws.pid';
        
        // If websocket-server lock-file exists, and contains websocket-server's process ID, and it's an integer
        if (is_file($wsLock) && $wsPid = (int) trim(file_get_contents($wsLock))) {

            // If such process is found - flush msg and exit
            if (checkpid($wsPid)) jflush(false);
        }

        // Check whether pid-file is writable
        if (!is_writable($wsLock)) jflush(false, 'ws.pid file is not writable');

        // Check whether err-file is writable
        if (!is_writable(DOC . STD . '/core/application/ws.err')) jflush(false, 'ws.err file is not writable');
        
        // Path to websocket-server php script
        $wsServer = '/core/application/ws.php';
        
        // Build websocket startup cmd
        $result['cmd'] = preg_match('/^WIN/i', PHP_OS)
            ? sprintf('start /B %sphp ..%s 2>&1', rif(Indi::ini('general')->phpdir, '$1/'), $wsServer)
            : 'nohup wget --no-check-certificate -qO- "'. ($_SERVER['REQUEST_SCHEME'] ?: 'http') . '://' . $_SERVER['HTTP_HOST'] . STD . $wsServer . '" > /dev/null &';

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
     * Flush contents of all app js files, concatenated into single text blob
     */
    public function appjsAction($exit = true) {

        // Header
        header('Content-Type: application/javascript');

        // Check whether `lang` uri-param is given
        $dirs = Indi::rexm('~^[a-zA-Z_]{2,5}$~', $lang = str_replace('-', '_', Indi::uri()->lang))
            ? '/../core/js/admin/app/locale/' . $lang
            : '/js/admin/app/proxy,/js/admin/app/data,/js/admin/app/lib,/js/admin/app/controller';

        // Flush
        echo appjs($dirs); if ($exit) exit;
    }
}