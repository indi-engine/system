<?php
/**
 * Controller for auxiliary abilities
 */
class Indi_Controller_Auxiliary extends Indi_Controller {

    /**
     * Provide a file download ability
     */
    public function downloadAction(){

        // If user is not logged in - flush error
        if (!admin()) jflush(false, 'Authentication required');

        // If 'id' param is not set or is not an integer
        if (!preg_match('/^[0-9]+$/', uri('id'))) jflush(false, I_DOWNLOAD_ERROR_NO_ID);

        // If 'field' param is not set or is not an integer
        if (!preg_match('/^[0-9]+$/', uri('field'))) jflush(false, I_DOWNLOAD_ERROR_NO_FIELD);

        // Get the field
        $fieldR = m('Field')->row(uri('field'));

        // If field was not found
        if (!$fieldR) jflush(false, I_DOWNLOAD_ERROR_NO_SUCH_FIELD);

        // Get extended info about field
        $fieldR = m($fieldR->entityId)->fields($fieldR->alias);

        // If field was not found
        if (!$fieldR) jflush(false, I_DOWNLOAD_ERROR_NO_SUCH_FIELD);

        // If field is not a file upload field, e.g does not deal with files
        if ($fieldR->foreign('elementId')->alias != 'upload') jflush(false, I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES);

        // Prevent download if we're in demo-mode and shade-param is enabled for this field
        if ($fieldR->param('shade')) Indi::demo();

        // Get the row
        $r = m($fieldR->entityId)->row(uri('id'));

        // If row was not found
        if (!$r) jflush(false, I_DOWNLOAD_ERROR_NO_SUCH_ROW);

        // Get the file
        list($abs) = glob($r->dir() . $r->id . '_' . $fieldR->alias . '.*');

        // If there is no file
        if (!$abs) jflush(false, I_DOWNLOAD_ERROR_NO_FILE);

        // Declare an array, for containing downloading file title parts
        $title = [];

        // Append entity title to filename parts array, if needed
        //if ($fieldR->params['prependEntityTitle'] == 'true') $title[] = m($fieldR->entityId)->title() . ',';

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
            if (!$type) jflush(false, I_DOWNLOAD_ERROR_FILEINFO_FAILED);

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
     * Flush contents of all app js files, concatenated into single text blob
     */
    public function appjsAction($exit = true) {

        // Header
        header('Content-Type: application/javascript');

        // Dirs to scan
        $dirs = [];

        // If lang is given in uri
        if ($lang = uri()->lang ?? 0) {

            // Replace dash with underscore to comply with ExtJS locale files naming
            $lang = str_replace('-', '_', uri()->lang);

            // If it's match the pattern we expect
            if (Indi::rexm('~^[a-zA-Z_]{2,5}$~', $lang))

                // Use to build dir and append to the to-be-scanned list
                $dirs []= VDR . '/client/classic/resources/locale/' . $lang;

        // Else
        } else {

            // Temporary system js, e.g. not compiled into app.js so far
            // Handy for cases when some new system js controller is created
            // but not yet moved into client-dev repo for being compiled so that it's temporary
            // possible to develop with no re-build with 'sencha app build --production' on each change
            $dirs []= VDR . '/system/js/admin/app/lib';
            $dirs []= VDR . '/system/js/admin/app/controller';

            // Custom js
            $dirs []= '/js/admin/app/proxy';
            $dirs []= '/js/admin/app/data';
            $dirs []= '/js/admin/app/lib';
            $dirs []= '/js/admin/app/controller';
        }

        // Flush
        echo appjs($dirs); if ($exit) exit;
    }
}