<?php
class Admin_LangController extends Indi_Controller_Admin {

    /**
     * @return mixed
     */
    public function dictAction() {

        // Get languages, already existing as `lang` entries
        $langA = Indi::db()->query('SELECT `alias`, `title` FROM `lang`')->fetchAll(PDO::FETCH_KEY_PAIR);

        // Create Google Cloud Translation PHP API
        $gapi = new Google\Cloud\Translate\V2\TranslateClient(['key' => Indi::ini('lang')->gapi->key]);

        // New languages counter
        $l = 0;

        // Get languages, available from google
        foreach ($gapi->localizedLanguages() as $langI) {

            // If `lang` entry is already created - skip
            if ($langA[$langI['code']]) continue;

            // Increment new languages counter
            $l++;

            // Create `lang` entry
            Indi::model('Lang')->createRow(array(
                'title' => $langI['name'],
                'alias' => $langI['code'],
                'toggle' => 'n'
            ), true)->save();
        }

        // Flush result
        jflush(true, 'Новых языков: ' .  $l);
    }

    /**
     * Create `queueTask` entry
     *
     * @param $cell
     * @param $value
     */
    public function onBeforeCellSave($cell, $value) {

        // Get field
        $fieldR = t()->model->fields($cell);

        // Skip if $cell is not a l10n-fraction field
        if ($fieldR->rel()->table() != 'enumset' || $cell == 'toggle') return;

        // Get fraction
        $fraction = $fieldR->nested('grid')->column('title', ' - ');

        // If we're going to create queue task for turning selected language either On or Off
        if (in($value, 'qy,qn')) {

            // Ask what we're going to do
            if ('no' == $this->confirm(sprintf(
                'Если вы хотите %s язык "%s" для фракции "%s" нажмите "%s". ' .
                'Если просто нужно привести в соответствие с текущим состоянием - нажмите "%s"',
                $value == 'qy' ? 'добавить' : 'удалить', t()->row->title, $fraction, I_YES, I_NO), 'YESNOCANCEL'))
                return;

        // Else if we're going to setup fraction-status directly
        } else if ('ok' == $this->confirm(sprintf(
            'Для фракции "%s" язык "%s" будет вручную помечен как "%s". Продолжить?',
            $fraction, t()->row->title, t()->row->enumset($cell, $value)
        ), 'OKCANCEL'))
            return;

        // If we're going to add new translation
        if ($value == 'qy') {

            // Create phantom `langId` field
            $langId_combo = Indi::model('Field')->createRow([
                'title' => 'asdasd',
                'alias' => 'langId',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'combo',
                'storeRelationAbility' => 'one',
                'relation' => 'lang',
                'filter' => '`id` != "' . t()->row->id . '" AND `' . $cell . '` = "y"',
                'mode' => 'hidden',
                'defaultValue' => 0
            ], true);

            // Append to fields list
            t()->model->fields()->append($langId_combo);

            // Build config for langId-combo
            $combo = ['fieldLabel' => '', 'allowBlank' => 0] + t()->row->combo('langId');

            // Prompt for source language
            $prompt = $this->prompt('Выберите исходный язык', [$combo]);

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
        if (!class_exists($queueClassName)) jflush(false, sprintf('Не найден класс %s', $queueClassName));

        // Create queue class instance
        $queue = new $queueClassName();

        // Run first stage
        $queueTaskR = $queue->chunk($params);

        // Auto-start queue as a background process, for now only for *Const-fractions
        Indi::cmd('queue', array('queueTaskId' => $queueTaskR->id));
    }

    /**
     * Detect wordings and replace them with constants
     */
    public function wordingsAction() {

        //
        $answer = $this->confirm('Сначала проверить?', 'YESNOCANCEL');

        //
        if ($answer == 'cancel') jflush(true);

        // Foreach fraction
        foreach (ar('www') as $fraction) {

            // Create dir, containing l10n-constants files, for current fraction if not yet exists
            if (!is_dir($_ = DOC . STD . '/' . $fraction . '/application/lang/admin/')) mkdir($_, true, 777);

            // Where will be current language used for building file name
            $out =  $_ . t()->row->alias . '.php';

            // Lines to be written to php-constants file
            $lineA = array();

            // Both both php ans js wordings
            foreach ([
                         'php' => [
                             'dir' => ['application/controllers/admin', 'application/models', 'library/Project'],
                             'rex' => ['~(__\()(\s*)\'(.*?)\'~']
                         ],
                         'js'  => [
                             'dir' => ['js/admin/app/controller', 'js/admin/app/lib/controller'],
                             'rex' => [
                                 '~(title|msg|buttonText|regexText|wand|fieldLabel|tooltip|emptyText|text|printButtonTooltip|infoText)(:\s*)\'(.*[a-zA-Zа-яА-Я]+.*?)\'~u',
                                 '~(wait|alert|update)(\(\s*)\'(.*[a-zA-Zа-яА-Я]+.*?)\'~u',
                             ]
                         ]
                     ] as $type => $cfg) {

                // Collect raw contents
                foreach ($cfg['dir'] as $dir) {

                    // Build constant-name prefix, pointing to dir
                    $pref['dir'] = 'I_'; foreach(explode('/', $dir) as $level) $pref['dir'] .= strtoupper(substr($level, 0, 1));

                    // Absolute fraction path
                    $abs = DOC . STD . '/' . $fraction . '/' . $dir;

                    // Foreach file in dir
                    foreach (scandirr($abs) as $file) {

                        // Skip tmp file
                        if (pathinfo($file, PATHINFO_EXTENSION) != $type) continue;

                        // Mind subdirs
                        $pref['sub'] = '';
                        foreach(explode('/', str_replace($abs, '', str_replace('\\', '/', pathinfo($file, PATHINFO_DIRNAME)))) as $level)
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
                        foreach (preg_split('/(?=[A-Z])/', pathinfo($file, PATHINFO_FILENAME)) as $word) {
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
                        $raw = file_get_contents($file);

                        // Get wordings
                        $fidx = 0;

                        // Wordings-by-method array, for counting purposes
                        $methodA = array();

                        // Foreach regex according to current type
                        foreach ($cfg['rex'] as $rex) {

                            // Split raw php-code by wordings, for searching method names
                            $chunkA = preg_split($rex, $raw);

                            //
                            $raw  = preg_replace_callback($rex, function(&$m) use (&$fidx, &$pref, &$lineA, $const, &$methodA, $chunkA, $type) {

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
                                if ($type == 'php') return $m[1] . $m[2] . $const;
                                else if ($type == 'js') return $m[1] . $m[2] . 'Indi.lang.' . $const;

                                //
                            }, $raw);
                        }

                        //
                        $tmp = $answer == 'yes'; $put = $file . rif($tmp, '_');

                        // Replace wordings with constants in source file
                        file_put_contents($put, $raw);

                        // Remove tmp file
                        if (!$tmp && file_exists($file . '_')) unlink($file . '_');
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
}