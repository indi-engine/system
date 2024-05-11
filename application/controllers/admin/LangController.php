<?php
class Admin_LangController extends Indi_Controller_Admin {

    /**
     * @return mixed
     */
    public function dictAction() {

        // Get languages, already existing as `lang` entries
        $langA = db()->query('SELECT `alias`, `title` FROM `lang`')->pairs();

        // Check if [lang]->gapi.key is given in application/config.ini
        if (!ini('lang')->gapi->key) {

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

        // Create Google Cloud Translation PHP API
        $gapi = new Google\Cloud\Translate\V2\TranslateClient(['key' => ini('lang')->gapi->key]);

        // New languages counter
        $l = 0;

        // Get languages, available from google
        foreach ($gapi->localizedLanguages() as $langI) {

            // If `lang` entry is already created - skip
            if ($langA[$langI['code']]) continue;

            // Increment new languages counter
            $l++;

            // Create `lang` entry
            m('Lang')->new([
                'title' => $langI['name'],
                'alias' => $langI['code'],
                'toggle' => 'n'
            ])->save();
        }

        // Flush result
        jflush(true, __(I_LANG_NEW_QTY, $l));
    }

    /**
     * Create `queueTask` entry
     *
     * @param $cell
     * @param $value
     */
    public function onBeforeCellSave($cell, $value) {

        // Get field
        $fieldR = m()->fields($cell);

        // Skip if $cell is not a l10n-fraction field
        if ($fieldR->rel()->table() != 'enumset' || $cell == 'toggle') return;

        // Get fraction
        $fraction = $fieldR->nested('grid')->column('title', ' - ');

        // If we're going to create queue task for turning selected language either On or Off
        if (in($value, 'qy,qn')) {

            // Ask what we're going to do
            if ('no' == $this->confirm(sprintf(
                I_LANG_QYQN_CONFIRM,
                mb_strtolower($value == 'qy' ? I_ADD : I_DELETE, 'utf-8'), t()->row->title, $fraction, I_YES, I_NO), 'YESNOCANCEL'))
                return;

        // Else if we're going to setup fraction-status directly
        } else if ('ok' == $this->confirm(sprintf(
            I_LANG_QYQN_CONFIRM2,
            $fraction, t()->row->title, t()->row->enumset($cell, $value)
        ), 'OKCANCEL'))
            return;

        // If we're going to add new translation
        if ($value == 'qy') {

            // Prepare WHERE clause for source languages list
            $langId_where = '`id` != "' . t()->row->id . '" AND `' . $cell . '` = "y"';

            // Create phantom `langId` field
            $langId_combo = m('Field')->new([
                'alias' => 'langId',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'combo',
                'storeRelationAbility' => 'one',
                'relation' => 'lang',
                'filter' => $langId_where,
                'mode' => 'hidden',
                'defaultValue' => 0
            ]);

            // Append to fields list
            m()->fields()->append($langId_combo);

            // Setup a value for phantom `langId` field for the current row, as for now
            // it's the only way to make sure it to be a selected value in prompt's combobox
            t()->row->langId = m('lang')->row($langId_where, 'move')->id;

            // Build config for langId-combo
            $combo = ['fieldLabel' => '', 'allowBlank' => 0] + t()->row->combo('langId');

            // Prompt for source language
            $prompt = $this->prompt(I_LANG_QYQN_SELECT, [$combo]);

            // Check prompt data
            $_ = jcheck(['langId' => ['req' => true, 'rex' => 'int11', 'key' => 'lang']], $prompt);

            // Prepare params
            $params = ['source' => $_['langId']->alias, 'target' => t()->row->alias];

        // Else
        } else {

            // Prepare params
            $params = ['source' => t()->row->alias, 'toggle' => 'n'];
        }

        // Build queue class name
        $queueClassName = 'Indi_Queue_L10n_' . ucfirst($cell);

        // Check that class exists
        if (!class_exists($queueClassName)) jflush(false, __(I_CLASS_404, $queueClassName));

        // Create queue class instance
        $queue = new $queueClassName();

        // Run first stage
        $queueTaskR = $queue->chunk($params);

        // Auto-start queue as a background process, for now only for *Const-fractions
        process('detached/queue', ['queueTaskId' => $queueTaskR->id]);
    }

    /**
     * Detect wordings within source code files, and add corresponding constants to the constant-file
     * for selected language. Optionally, those wordings can be replaced with their constants within source code files
     */
    public function wordingsAction() {

        // If $answer is 'no'  - wordings will be replaced with their constants within source code files
        $answer = $this->confirm(I_LANG_WORD_DETECT_ONLY, 'YESNOCANCEL');

        // todo: make prompt asking for fraction
        $repoDirA = [
            'system' => VDR . '/system',
            //'public' => VDR . '/public',
            //'custom' => ''
        ];

        // Foreach fraction
        foreach ($repoDirA as $fraction => $repoDir) {

            // Create dir, containing l10n-constants files, for current fraction if not yet exists
            if (!is_dir($_ = DOC . STD . $repoDir . '/application/lang/admin/')) mkdir($_, true, 777);

            // Where will be current language used for building file name
            $out =  $_ . t()->row->alias . '.php';

            // Lines to be written to php-constants file
            $lineA = [];

            // Both both php ans js wordings
            foreach ([
                 'php' => [
                     'dir' => ['application/controllers/admin', 'application/models', 'library/Project', 'library/Indi'],
                     'rex' => ['~(__\()(\s*)\'(.*?[^\\\])\'~']
                 ],
                 'js'  => [
                     'dir' => ['js/admin/app/controller', 'js/admin/app/lib/controller'],
                     'rex' => [
                         '~(title|msg|buttonText|regexText|wand|fieldLabel|tooltip|emptyText|text|printButtonTooltip|infoText)(:\s*)\'(.*[a-zA-Zа-яА-Я]+.*?)\'~u',
                         '~(wait|alert|update)(\(\s*)\'(.*[a-zA-Zа-яА-Я]+.*?)\'~u',
                     ]
                 ]
             ] as $ext => $cfg) {

                // Collect raw contents
                foreach ($cfg['dir'] as $dir) {

                    // Build constant-name prefix, pointing to dir
                    $pref['dir'] = 'I_'; foreach(explode('/', $dir) as $level) $pref['dir'] .= strtoupper(substr($level, 0, 1));

                    // Absolute fraction path
                    $abs = DOC . STD . $repoDir . '/' . $dir;

                    // Foreach file in dir
                    foreach (scandirr($abs) as $sourceCodeFile) {

                        // Skip tmp file
                        if (pathinfo($sourceCodeFile, PATHINFO_EXTENSION) != $ext) continue;

                        // Mind subdirs
                        $pref['sub'] = '';
                        foreach(explode('/', str_replace($abs, '', str_replace('\\', '/', pathinfo($sourceCodeFile, PATHINFO_DIRNAME)))) as $level)
                            foreach (preg_split('/(?=[A-Z])/', $level) as $word) {
                                $first = strtoupper(substr($word, 0, 1));
                                $last = strtoupper(substr($word, -1, 1));
                                $middle = substr(substr($word, 1), 0, -1);
                                $middle = preg_replace('~[aeioг]~', '', $middle);
                                $middle = strtoupper(substr($middle, 0, 1));
                                $pref['sub'] .= $first . $middle . $last;
                            }

                        // Unset if no subdir
                        if (!$pref['sub']) unset($pref['sub']);

                        // Build constant-name prefix, pointing to file
                        $pref['file'] = '';
                        foreach (preg_split('/(?=[A-Z])/', pathinfo($sourceCodeFile, PATHINFO_FILENAME)) as $word) {
                            if ($word == 'Row') $pref['file'] .= 'R'; else if ($word != 'Controller') {
                                $first = strtoupper(substr($word, 0, 1));
                                $last = strtoupper(substr($word, -1, 1));
                                $middle = substr(substr($word, 1), 0, -1);
                                $middle = preg_replace('~[aeioг]~', '', $middle);
                                $middle = strtoupper(substr($middle, 0, 1));
                                $pref['file'] .= $first . $middle . $last;
                            }
                        }

                        // Build constant name
                        $const = im($pref, '_');

                        // Reset
                        unset($pref['file'], $pref['sub']);

                        // Get raw contents
                        $raw = file_get_contents($sourceCodeFile);

                        // Get wordings
                        $fidx = 0;

                        // Wordings-by-method array, for counting purposes
                        $methodA = [];

                        // Foreach regex according to current type
                        foreach ($cfg['rex'] as $rex) {

                            // Split raw php-code by wordings, for searching method names
                            $chunkA = preg_split($rex, $raw);

                            //
                            $raw  = preg_replace_callback($rex, function(&$m) use (&$fidx, &$pref, &$lineA, $const, &$methodA, $chunkA, $ext) {

                                // Try to detect method name
                                if (preg_match_all('~public function ([a-zA-Z0-9_]+)~', $chunkA[$fidx], $__)) {
                                    $method = array_pop($__);
                                    $method = array_pop($method);
                                }

                                // If method was detected
                                if ($method) {

                                    // Collect wordinds within certain method
                                    $methodA[$method] [] = $m[1];

                                    // Keep only uppercase chars from method name
                                    $const .= '_' . preg_replace('~[a-z]~', '', ucfirst($method));

                                    // Counter
                                    $const .=  rif(count($methodA[$method]) > 1, '_' . count($methodA[$method]));

                                    // Else if method is not detected
                                } else {
                                    $const .= rif($fidx, '_' . ($fidx + 1));
                                    $fidx ++;
                                }

                                // Tbq
                                if (preg_match('~^(.{2})[^,]+,\1[^,]+,\1.*$~u', $m[3], $asd)) $const .= '_TBQ';

                                // Append line
                                $lineA []= sprintf('define(\'%s\', \'%s\');', $const, $m[3]);

                                // Spoof wording by constant usage
                                if ($ext == 'php') return $m[1] . $m[2] . $const;
                                else if ($ext == 'js') return $m[1] . $m[2] . 'Indi.lang.' . $const;

                                //
                            }, $raw);
                        }

                        //
                        $tmpFile = $answer == 'yes'; $put = $sourceCodeFile . rif($tmpFile, '_');

                        // Replace [wordings => constants] within source code file
                        file_put_contents($put, $raw);

                        // Remove tmp file
                        if (!$tmpFile && file_exists($sourceCodeFile . '_')) unlink($sourceCodeFile . '_');
                    }
                }
            }

            // If found
            if ($lineA) {

                // Check if const-file already exists, and if yes - get existing contents
                if (file_exists($out)) $was = file_get_contents($out);

                // Write constants definitions into constants file
                file_put_contents($out, rif($was, '$1' . "\n\n", '<?php' . "\n") . im($lineA, "\n"));

                // Reactivate activate
                include($out);
            }
        }

        //
        jflush(true, 'OK');
    }

    /**
     * Export admin system or custom ui translation as a php-code
     */
    public function exportAction() {

        // Show prompt
        $prompt = $this->_prompt(I_LANG_EXPORT_HEADER);

        // Foreach selected language
        foreach (t()->rows as $row) {

            // Prepare params
            $params = ['source' => $row->alias, 'export' => $prompt['settings']];

            // Build queue class name
            $queueClassName = 'Indi_Queue_L10n_' . ucfirst($prompt['fraction']) . 'Export';

            // Check that class exists
            if (!class_exists($queueClassName)) jflush(false, __(I_CLASS_404, $queueClassName));

            // Create queue class instance
            $queue = new $queueClassName();

            // Run first stage
            $queueTaskR = $queue->chunk($params);

            // If data should be exported
            if ($prompt['settings'] == 'data') {

                // If fraction is adminCustomData - flush failure
                if ($prompt['fraction'] == 'adminCustomData') jflush(false, I_LANG_NOT_SUPPORTED);

                // Else auto-start queue as a background process
                process('detached/queue', ['queueTaskId' => $queueTaskR->id]);
            }
        }

        // Flush ok
        jflush(true, 'OK');
    }

    /**
     * Import admin system or custom ui translation from php-code
     */
    public function importAction() {

        // Set no time limit
        set_time_limit(0);

        // Show prompt
        $prompt = $this->_prompt(I_LANG_IMPORT_HEADER);

        // Dirs
        $dirA = ['adminSystemUi' => VDR . '/system', 'adminCustomUi' => '', 'adminCustomData' => ''];

        // Dir
        $dir = $dirA[$prompt['fraction']];

        // If meta should be imported
        if ($prompt['settings'] == 'meta') {

            // Get file containing meta-part of migration, e.g. the code, that is toggling l10n for required fields
            $meta = DOC . STD . $dir . '/application/lang/'
                . strtolower(preg_replace('~^admin(System|Custom)~', '', $prompt['fraction'])) . '.php';

            // Applicable languages WHERE clause
            $langId_filter = '"y" IN (`' . $prompt['fraction'] . '`)';

            // Create phantom `langId` field
            $langId_combo = m('field')->new([
                'alias' => 'langId',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'combo',
                'storeRelationAbility' => 'one',
                'relation' => 'lang',
                'filter' => $langId_filter,
                'mode' => 'hidden',
                'defaultValue' => 0
            ]);

            // Append to fields list
            m()->fields()->append($langId_combo);

            // Set active value
            t()->row->langId = m('lang')->row($langId_filter, '`move`')->id;

            // Build config for langId-combo
            $combo = ['fieldLabel' => '', 'allowBlank' => 0] + t()->row->combo('langId');

            // Prompt for source language
            $prompt2 = $this->prompt(__(I_LANG_SELECT_CURRENT, $prompt['_fraction'][$prompt['fraction']]), [$combo]);

            // Check language
            $_ = jcheck(['langId' => ['req' => true, 'rex' => 'int11', 'key' => 'lang']], $prompt2);

            // Get current language of selected fraction
            $lang = $_['langId']->alias;

            // Execute file, containing php-code for toggling l10n for certain fields
            require_once $meta;
        }

        // If titles should be imported
        if ($prompt['settings'] == 'data') {

            // If fraction is 'adminCustomData' - flush not supported msg
            if ($prompt['fraction'] == 'adminCustomData') jflush(false, I_LANG_NOT_SUPPORTED);

            // Foreach selected row
            foreach (t()->rows as $row) {

                // Else get file containing data-part of migration, e.g. the code, that is setting up titles for given language
                $data = DIR . $dir . '/application/lang/ui/' . $row->alias . '.php';

                // Backup current language
                $_lang = ini('lang')->admin;

                // Spoof current language with given language
                ini('lang')->admin = $row->alias;

                // Execute file, containing php-code for setting up titles for given language
                require_once $data;

                // Restore current language
                ini('lang')->admin = $_lang;

                // Set 'y' for imported language's fraction-column
                $row->set($prompt['fraction'], 'y')->save();
            }
        }

        // Flush ok
        jflush(true, 'OK');
    }

    /**
     * @param $header
     * @return mixed
     * @throws Exception
     */
    protected function _prompt($header) {

        // Get titles of `lang` fields, representing fractions of ui
        $t = m()
            ->fields('adminSystem,adminSystemUi,adminCustom,adminCustomUi,adminCustomData')
            ->column('title', false, 'alias');

        // Create phantom `fraction` field for being used for radio buttons rendering
        $fraction = m('field')->new([
            'alias' => 'fraction',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'mode' => 'hidden'
        ]);

        // Setup possible values
        $fraction->nested('enumset', $fractionA = [
            ['alias' => $_ = 'adminSystemUi',  'title' => im([$t['adminSystem'], $t['adminSystemUi']],   ' - ')],
            ['alias' => 'adminCustomUi',       'title' => im([$t['adminCustom'], $t['adminCustomUi']],   ' - ')],
            ['alias' => 'adminCustomData',     'title' => im([$t['adminCustom'], $t['adminCustomData']], ' - ')],
        ]);

        // Append to fields list and set first fraction to be selected by default
        m()->fields()->add($fraction); t()->row->fraction = $_;

        // Create phantom `settings` field for being used for checkboxes rendering
        $settings = m('field')->new([
            'alias' => 'settings',
            'columnTypeId' => 'SET',
            'elementId' => 'combo',
            'storeRelationAbility' => 'many',
            'relation' => 'enumset',
            'mode' => 'hidden'
        ]);

        // Setup possible values
        $settings->nested('enumset', [
            ['alias' => 'meta', 'title' => I_LANG_MIGRATE_META],
            ['alias' => 'data', 'title' => I_LANG_MIGRATE_DATA],
        ]);

        // Append to fields list and set first step to be selected by default
        m()->fields()->add($settings); t()->row->settings = 'meta';

        // Show prompt dialog with two fields
        $prompt = $this->prompt($header, [
            t()->row->radio('fraction'),
            t()->row->radio('settings')
        ]);

        // Check prompt data
        jcheck([
            'fraction' => [
                'req' => true,
                'rex' => '~^(adminSystemUi|adminCustomUi|adminCustomData)$~'
            ],
            'settings' => [
                'req' => true,
                'rex' => '~^(meta|data)$~'
            ]
        ], $prompt);

        // Return prompt data
        return $prompt + ['_fraction' => array_column($fractionA, 'title', 'alias')];
    }

    /**
     * Prevent deletion of last language or current language
     *
     * @return array|void
     */
    public function deleteAction($flush = true) {

        // If current entry is the last remaining `lang` entry - flush error
        if (db()->query('SELECT COUNT(*) FROM `lang`')->cell() == 1)
            jflush(false, sprintf(I_LANG_LAST, m('Lang')->title()));

        // If current entry is a translation, that is currently used - flush error
        if (t()->row->alias == ini('lang')->admin) jflush(false, I_LANG_CURR);

        // Call parent
        parent::deleteAction();
    }
}