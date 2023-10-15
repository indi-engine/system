<?php
class Admin_EntitiesController extends Indi_Controller_Admin_Exportable {

    /**
     * Backup the whole db as sql dump
     */
    public function backupAction() {
        
        // Prompt for filename
        $prompt = $this->prompt('Please specify db dump filename to be created in sql/ directory', [[
            'xtype' => 'textfield',
            'name' => 'dump',
            'value' => 'custom-demo.sql.gz'
        ]]);
        
        // Prepare variables
        $user = ini()->db->user;
        $pass = ini()->db->pass;
        $name = ini()->db->name;
        $host = ini()->db->host;
        $dump = $prompt['dump'];

        // Make sure dump name ends with .sql or .sql.gz
        jcheck([
            'dump' => [
                'req' => true,
                'rex' => '~\.(sql|sql\.gz)$~'
            ]
        ], $prompt);

        // Make sure dump name contains alphanumeric and basic punctuation chars
        jcheck([
            'dump' => [
                'req' => true,
                'rex' => '~^[a-zA-Z0-9\.\-]+$~'
            ]
        ], $prompt);

        // Exec mysqldump-command
        $exec = preg_match('~\.gz$~', $dump) 
            ? `mysqldump -h $host -u $user -p$pass $name | gzip > sql/$dump`
            : `mysqldump -h $host -u $user -p$pass $name > sql/$dump`;
        
        // Flush result
        jflush(!$exec, $exec ?: 'Done');
    }

    /**
     * Restore the whole db from sql dump
     */
    public function restoreAction() {

        // Prompt for filename
        $prompt = $this->prompt('Please specify db dump filename to be imported from sql/ directory', [[
            'xtype' => 'textfield',
            'name' => 'dump',
            'value' => 'custom-demo.sql.gz'
        ]]);

        // Prepare variables
        $user = ini()->db->user;
        $pass = ini()->db->pass;
        $name = ini()->db->name;
        $host = ini()->db->host;
        $dump = $prompt['dump'];

        // Make sure dump name ends with .sql or .sql.gz
        jcheck([
            'dump' => [
                'req' => true,
                'rex' => '~\.(sql|sql\.gz)$~'
            ]
        ], $prompt);

        // If given dump name is actually an URL
        if (Indi::rexm('url', $dump)) {

            // Flush step
            msg('Downloading dump...');

            // Download dump
            $raw = file_get_contents($dump);

            // Spoof $dump for it to contain just filename with extension
            $dump = $prompt['dump'] = pathinfo($dump, PATHINFO_BASENAME);

            // Save dump in sql/ directory
            file_put_contents("sql/$dump", $raw);
        }

        // Make sure dump name contains alphanumeric and basic punctuation chars
        jcheck([
            'dump' => [
                'rex' => '~^[a-zA-Z0-9\.\-]+$~'
            ]
        ], $prompt);

        // Whether maxwell is enabled
        $maxwell = ini()->rabbitmq->maxwell;

        // If yes
        if ($maxwell) {

            // Flush step
            msg('Disabling maxwell...');

            // Disable maxwell
            `php indi realtime/maxwell/disable`;
        }

        // Flush step
        msg('Importing dump...');

        // Exec mysqldump-command
        $exec = preg_match('~\.gz$~', $dump)
            ? `zcat sql/$dump | mysql -h $host -u $user -p$pass $name`
            : `mysql -h $host -u $user -p$pass $name < sql/$dump`;

        // If maxwell was enabled
        if ($maxwell) {

            // Flush step
            msg('Enabling maxwell back...');

            // Enable maxwell back
            `php indi realtime/maxwell/enable`;
        }

        // Flush result
        jflush(!$exec, $exec ?: 'Done');
    }
    
    /**
     * Create php classes files
     */
    public function phpAction() {

        // PHP class files for entities of fraction:
        // - 'system' - will be created in VDR . '/system',
        // - 'public' -                 in VDR . '/public',
        // - 'custom' -                 in ''
        $repoDirA = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // Build the dir name, that model's php-file will be created in
        $dir = Indi::dir(DOC . STD . $repoDirA[$this->row->fraction] . '/application/models/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the model name with first letter upper-cased
        $model = ucfirst(t()->row->table);

        // Messages
        $msg = [];

        // Get absolute and relative paths
        $modelAbs = $dir . $model . '.php';
        $modelRel = preg_replace('~^' . preg_quote(DOC) . '/~', '', $modelAbs);

        // If model file already exists
        if (is_file($modelAbs)) {

            // Add msg
            $msg [] = __(I_FILE_EXISTS, $modelRel);

        // Else
        } else {

            // Build template model file name
            $tplModelFn = DOC. STD . VDR . '/system/application/models/{Model}.php';

            // If it is not exists - flush an error, as we have no template for creating a model file
            if (!is_file($tplModelFn)) jflush(false, I_ENTITIES_TPLMDL_404);

            // Get the template contents (source code)
            $emptyModelSc = file_get_contents($tplModelFn);

            // Replace {Model} keyword with an actual model name
            $modelSc = preg_replace(':\{Model\}:', $model, $emptyModelSc);

            // Replace {extends} keyword with an actual parent class name
            $modelSc = preg_replace(':\{extends\}:', $this->row->extends, $modelSc);

            // Put the contents to a model file
            file_put_contents($modelAbs, $modelSc);

            // Chmod
            chmod($modelAbs, 0765);

            // Reload this row in all grids it's currently shown in
            t()->row->realtime('reload');

            // Flush success
            $msg []= __(I_FILE_CREATED, $modelRel);
        }

        // Build the model's own dir name, and try to create it, if it not yet exist
        $modelDir = Indi::dir($dir . '/' . $model . '/');

        // If model's own dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $modelDir)) jflush(false, $modelDir);

        // Get absolute and relative paths
        $modelRowAbs = $dir . $model . '/Row.php';
        $modelRowRel = preg_replace('~^' . preg_quote(DOC) . '/~', '', $modelRowAbs);

        // If model's row-class file already exists
        if (is_file($modelRowAbs)) {

            // Add msg
            $msg [] = __(I_FILE_EXISTS, $modelRowRel);

        // Else
        } else {

            // Build template model's rowClass file name
            $tplModelRowFn = DOC. STD . VDR . '/system/application/models/{Model}/Row.php';

            // If it is not exists - flush an error, as we have no template for creating a model's rowClass file
            if (!is_file($tplModelRowFn)) jflush(false, I_ENTITIES_TPLMDLROW_404);

            // Get the template contents (source code)
            $tplModelRowSc = file_get_contents($tplModelRowFn);

            // Replace {Model} keyword with an actual model name
            $modelRowSc = preg_replace(':\{Model\}:', $model, $tplModelRowSc);

            // Put the contents to a model's rowClass file
            file_put_contents($modelRowAbs, $modelRowSc);

            // Chmod
            chmod($modelRowAbs, 0765);

            // Flush success
            $msg []= __(I_FILE_CREATED, $modelRowRel);
        }

        // Reload this row in all grids it's currently shown in
        t()->row->realtime('reload');

        // Flush success
        jflush(true, join('<br>', $msg));
    }

    /**
     * Append created(|ByRole|ByUser|At) fields to store info about by whom and when each entry was created
     */
    public function authorAction() {

        // Get current entity's table name
        $table = t()->row->table;

        // Span field
        field($table, 'created', ['title' => I_ENT_AUTHOR_SPAN, 'elementId' => 'span']);

        // Shared config for two fields
        $shared = ['columnTypeId' => 'INT(11)',  'elementId' => 'combo', 'storeRelationAbility' => 'one'];

        // Author role field
        field($table, 'createdByRole', $shared + ['defaultValue' => '<?=admin()->roleId?>', 'title' => I_ENT_AUTHOR_ROLE, 'relation' => 'role']);

        // Author field
        field($table, 'createdByUser', $shared + ['defaultValue' => '<?=admin()->id?>', 'title' => I_ENT_AUTHOR_USER]);

        // Author field depends on author role field
        consider($table, 'createdByUser', 'createdByRole', ['foreign' => 'entityId', 'required' => 'y']);

        // Datetime field
        field($table, 'createdAt', [
            'title' => I_ENT_AUTHOR_TIME, 'columnTypeId' => 'DATETIME', 'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>'
        ]);

        // Flush success
        jflush(true, 'OK');
    }

    /**
     * Create a `toggle` field within given entity
     */
    public function toggleAction() {

        // Foreach selected entity
        foreach (t()->rows as $row) {

            // Create field
            field($row->table, 'toggle', [
                'title' => I_ENT_TOGGLE,
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'y',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);

            // Create enum option
            enumset($row->table, 'toggle', 'y', ['title' => I_ENT_TOGGLE_Y, 'boxColor' => 'lime', 'move' => '']);
            enumset($row->table, 'toggle', 'n', ['title' => I_ENT_TOGGLE_N, 'boxColor' => 'red' , 'move' => 'y']);
        }

        // Flush success
        jflush(true, 'OK');
    }

    /**
     * 1.Hide default values for `extends` prop, to prevent it from creating a mess in eyes
     * 2.Check php-model file exist, and if yes, check whether it's actual parent class is
     *   as per specified in `extends` prop
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Foreach data item
        foreach ($data as &$item) {

            // Get php-mode class name
            $php = ucfirst($item['table']);

            // If php-controller file exists for this section
            if (class_exists($php)) {

                // Setup flag
                $item['_system']['php-class'] = true;

                // Get parent class
                $parent = get_parent_class($php);

                // If actual parent class is not as per entity `extends` prop - setup error
                if ($parent != $item['extends']) $item['_system']['php-error'] = sprintf(I_ENT_EXTENDS_OTHER, $parent);
            }
        }
    }
}