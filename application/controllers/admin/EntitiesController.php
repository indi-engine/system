<?php
class Admin_EntitiesController extends Indi_Controller_Admin_Exportable {

    /**
     * Create php classes files
     */
    public function phpAction() {

        // PHP class files for entities of fraction:
        // - 'system' - will be created in VDR . '/system',
        // - 'public' -                 in VDR . '/public',
        // - 'custom' -                 in ''
        $repoDirA = [
            'y' => VDR . '/system',
            'o' => VDR . '/public',
            'n' => ''
        ];

        // If current section has a type, that is (for some reason) not in the list of known types
        if (!in($this->row->system, array_keys($repoDirA)))

            // Flush an error
            jflush(false, 'Can\'t detect the alias of repository, associated with a type of the chosen entity');

        // Build the dir name, that model's php-file will be created in
        $dir = Indi::dir(DOC . STD . $repoDirA[$this->row->system] . '/application/models/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the model name with first letter upper-cased
        $model = ucfirst($this->row->table);

        // If model file is not yet exist
        if (!is_file($modelFn = $dir . '/' . $model . '.php')) {

            // Build template model file name
            $tplModelFn = DOC. STD . VDR . '/system/application/models/{Model}.php';

            // If it is not exists - flush an error, as we have no template for creating a model file
            if (!is_file($tplModelFn)) jflush(false, 'No template-model file found');

            // Get the template contents (source code)
            $emptyModelSc = file_get_contents($tplModelFn);

            // Replace {Model} keyword with an actual model name
            $modelSc = preg_replace(':\{Model\}:', $model, $emptyModelSc);

            // Replace {extends} keyword with an actual parent class name
            $modelSc = preg_replace(':\{extends\}:', $this->row->extends, $modelSc);

            // Put the contents to a model file
            file_put_contents($modelFn, $modelSc);            

            // Chmod
            chmod($modelFn, 0765);
        }

        // Build the model's own dir name, and try to create it, if it not yet exist
        $modelDir = Indi::dir($dir . '/' . $model . '/');

        // If model's own dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $modelDir)) jflush(false, $modelDir);

        // If model's row-class file is not yet exist
        if (!is_file($modelRowFn = $dir . '/' . $model . '/Row.php')) {

            // Build template model's rowClass file name
            $tplModelRowFn = DOC. STD . VDR . '/system/application/models/{Model}/Row.php';

            // If it is not exists - flush an error, as we have no template for creating a model's rowClass file
            if (!is_file($tplModelRowFn)) jflush(false, 'No template file for model\'s rowClass found');

            // Get the template contents (source code)
            $tplModelRowSc = file_get_contents($tplModelRowFn);

            // Replace {Model} keyword with an actual model name
            $modelRowSc = preg_replace(':\{Model\}:', $model, $tplModelRowSc);

            // Put the contents to a model's rowClass file
            file_put_contents($modelRowFn, $modelRowSc);

            // Chmod
            chmod($modelRowFn, 0765);
        }

        // Flush success
        jflush(true);
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
        field($table, 'createdByRole', $shared + ['defaultValue' => '<?=admin()->profileId?>', 'title' => I_ENT_AUTHOR_ROLE, 'relation' => 'profile']);

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

        // Get current entity's table name
        $table = t()->row->table;

        // Create field
        field($table, 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);

        // Create enum option
        enumset($table, 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>' . I_ENT_TOGGLE_Y, 'move' => '']);
        enumset($table, 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>'  . I_ENT_TOGGLE_N, 'move' => 'y']);

        // Flush success
        jflush(true, 'OK');
    }
}