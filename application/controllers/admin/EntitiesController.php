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

    public function authorAction() {

        // Get model
        $model = m($this->row->id);

        // If `author` field exists - flush error
        if ($model->fields('author'))
            jflush(false, 'Группа полей "Автор" уже существует в структуре сущности "' . $this->row->title . '"');

        // Get involded `element` entries
        $elementRs = m('Element')->all('FIND_IN_SET(`alias`, "span,combo,datetime")');

        // Prepare fields config
        $fieldA = [
            'author' => [
                'title' => 'Создание',
                'elementId' => $elementRs->gb('span', 'alias')->id
            ],
            'authorType' => [
                'title' => 'Кто создал',
                'storeRelationAbility' => 'one',
                'elementId' => $elementRs->gb('combo', 'alias')->id,
                'columnTypeId' => 3,
                'defaultValue' => '<?=Indi::me(\'mid\')?>',
                'relation' => m('Entity')->id(),
                'filter' => '`id` IN (' . db()->query('
                    SELECT GROUP_CONCAT(DISTINCT IF(`entityId`, `entityId`, 11)) FROM `profile` WHERE `toggle` = "y"
                ')->fetchColumn() . ')'
            ],
            'authorId' => [
                'title' => 'Кто именно',
                'storeRelationAbility' => 'one',
                'elementId' => $elementRs->gb('combo', 'alias')->id,
                'columnTypeId' => 3,
                'defaultValue' => '<?=Indi::me(\'id\')?>',
            ],
            'authorTs' => [
                'title' => 'Когда',
                'elementId' => $elementRs->gb('datetime', 'alias')->id,
                'columnTypeId' => 9,
                'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>'
            ]
        ];

        // Create fields
        foreach ($fieldA as $alias => $fieldI) {
            $fieldRA[$alias] = m('Field')->new();
            $fieldRA[$alias]->entityId = $this->row->id;
            $fieldRA[$alias]->alias = $alias;
            $fieldRA[$alias]->set($fieldI);
            $fieldRA[$alias]->save();
        }

        //$fieldRA[$alias]->satellite = $fieldRA['authorType']->id;
        consider($this->row->id, 'authorId', 'authorType', ['required' => 'y']);

        // Flush success
        jflush(true, 'Группа полей "Создание" была добавлена в структуру сущности "' . $this->row->title . '"');
    }

    /**
     * Create a `toggle` field within given entity
     */
    public function toggleAction() {

        // Get model
        $model = m($this->row->id);

        // If `author` field exists - flush error
        if ($model->fields('toggle'))
            jflush(false, 'Группа полей "Статус" уже существует в структуре сущности "' . $this->row->title . '"');

        // Drop `toggle` column from `action` table. We do it here because historically it was
        // set up that column `toggle` was created not by using Indi Engine, so it does not have
        // assotiated entries neither in `field` table, nor in `enumset` table, so the below line
        // just remove the column, for the ability to re-create in as a part of the process of field creation
        if ($this->row->table == 'action') db()->query('ALTER TABLE `action` DROP `toggle`');

        // Create field
        $fieldR = m('Field')->new([
            'entityId' => $this->row->id,
            'title' => 'Статус',
            'alias' => 'toggle',
            'storeRelationAbility' => 'one',
            'elementId' => m('Element')->row('`alias` = "combo"')->id,
            'columnTypeId' => m('ColumnType')->row('`type` = "ENUM"')->id,
            'defaultValue' => 'y'
        ]);

        // Save field
        $fieldR->save();

        // Get first enumset option (that was created automatically)
        $y = $fieldR->nested('enumset')->at(0);
        $y->title = '<span class="i-color-box" style="background: lime;"></span>Включен';
        $y->save();

        // Create one more enumset option within this field
        m('Enumset')->new([
            'fieldId' => $y->fieldId,
            'title' => '<span class="i-color-box" style="background: red;"></span>Выключен',
            'alias' => 'n'
        ])->save();

        // Flush success
        jflush(true, 'Поле "Статус" было добавлено в структуру сущности "' . $this->row->title . '"');
    }
}