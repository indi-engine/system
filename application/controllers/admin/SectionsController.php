<?php
class Admin_SectionsController extends Indi_Controller_Admin_Exportable {

    /**
     * Contents of admin/classic/app.js for checking js-controllers files presence
     *
     * @var string
     */
    public static $systemAppJs = '';

    /**
     * Create js-controller file for selected section
     */
    public function jsAction() {

        // JS-controller files for sections of type 'system' - will be created in '/core',
        // 'often' - in '/coref', 'project' - in '/www', but beware that js-controller files
        // created for system sections should be moved from /core/js/admin/app/controller
        // to Indi Engine system app source code into app/controller folder, and then should be
        // compiled by 'sencha app build --production' command, so that admin/classic/app.js bundle to be refreshed
        $repoDirA = array('s' => 'core', 'o' => 'coref', 'p' => 'www');

        // If current section has a type, that is (for some reason) not in the list of known types
        if (!in($this->row->type, array_keys($repoDirA)))

            // Flush an error
            jflush(false, 'Can\'t detect fraction of selected section');

        // Build the dir name, that controller's js-file should be created in
        $dir = Indi::dir(DOC . STD . '/' . $repoDirA[$this->row->type] . '/js/admin/app/controller/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the controller name
        $ctrl = $this->row->alias;

        // If controller file is not yet exist
        if (!is_file($ctrlAbs = $dir . '/' . $ctrl . '.js')) {

            // Build template model absolute file name
            $tplAbs = DOC. STD . '/core/js/admin/app/controller/{controller}.js';

            // If it is not exists - flush an error, as we have no template for creating a model file
            if (!is_file($tplAbs)) jflush(false, 'No template-controller file found');

            // Get the template contents (source code)
            $tplRaw = file_get_contents($tplAbs);

            // Replace {controller} keyword with an actual section name
            $ctrlRaw = preg_replace(':\{controller\}:', $ctrl, $tplRaw);

            // Replace {extends} keyword with an actual parent class name
            $ctrlRaw = preg_replace(':\{extends\}:', $this->row->extendsJs, $ctrlRaw);

            // Put the contents to a model file
            file_put_contents($ctrlAbs, $ctrlRaw);

            // Chmod
            chmod($ctrlAbs, 0765);
        }

        // Flush success
        jflush(true);
    }

    /**
     * Create php-controller file for selected section
     */
    public function phpAction() {

        // JS-controller files for sections of type 'system' - will be created in '/core',                                                          //$repositoryDirA = array('s' => 'core', 'o' => 'coref', 'p' => 'www');
        // 'often' - in '/coref', 'project' - in '/www'
        $repoDirA = array('s' => 'core', 'o' => 'coref', 'p' => 'www');

        // If current section has a type, that is (for some reason) not in the list of known types
        if (!in($this->row->type, array_keys($repoDirA)))

            // Flush an error
            jflush(false, 'Can\'t detect the alias of repository, associated with a type of the chosen section');

        // Build the dir name, that controller's js-file should be created in
        $dir = Indi::dir(DOC . STD . '/' . $repoDirA[$this->row->type] . '/application/controllers/admin/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the controller name
        $ctrl = ucfirst($this->row->alias);

        // If controller file is not yet exist
        if (is_file($ctrlAbs = $dir . '/' . $ctrl . 'Controller.php'))
            jflush(false, 'PHP-controller file for that section already exists');

        // Build template model absolute file name
        $tplAbs = DOC. STD . '/core/application/controllers/admin/{controller}.php';

        // If it is not exists - flush an error, as we have no template for creating a model file
        if (!is_file($tplAbs)) jflush(false, 'No template-controller file found');

        // Get the template contents (source code)
        $tplRaw = file_get_contents($tplAbs);

        // Replace {controller} keyword with an actual section name
        $ctrlRaw = preg_replace(':\{controller\}:', $ctrl, $tplRaw);

        // Replace {extends} keyword with an actual parent class name
        $ctrlRaw = preg_replace(':\{extends\}:', $this->row->extendsPhp ?: $this->row->extends, $ctrlRaw);

        // Put the contents to a model file
        file_put_contents($ctrlAbs, $ctrlRaw);

        // Chmod
        chmod($ctrlAbs, 0765);

        // Flush success
        jflush(true);
    }

    /**
     * todo: make 'Inversion' checkbox-field for filters
     */
    public function indexAction() {

        //
        Indi::trail()->model->fields('roleIds')->storeRelationAbility = 'one';

        // Call parent
        $this->callParent();
    }
    /**
     * Append sort direction clickable icon to the sort field
     *
     * @param $item
     */
    public function adjustGridDataItem(&$item) {

        // Add icon for `defaultSortField` prop
        if ($item['defaultSortField']
            && $info = Indi::rexm('~<span (.*?) title="(.*?)"></span>$~', $item['defaultSortDirection'])) {

            // Setup jump
            $item['_system']['jump']['defaultSortField'] = [[
                'href' => 'cell:defaultSortDirection',
                'ibox' => $info[1],
                'over' => $info[2]
            ]];
        }
    }

    /**
     * 1.Hide default values for `extendsPhp` and `extendsJs` props, to prevent it from creating a mess in eyes
     * 2.Check php/js-controller files exist, and if yes, check whether it's actual parent class is
     *   as per specified in `extendsPhp`/`extendsJs` prop
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Get default values
        foreach (ar('extendsPhp,extendsJs') as $prop) $default[$prop] = t()->fields($prop)->defaultValue;

        // Dirs dict by section type
        $dir = array('s' => 'core', 'p' => 'www', 'o' => 'coref');

        // Foreach data item
        foreach ($data as &$item) {

            // Get php-controller class name
            $php = 'Admin_' . ucfirst($item['alias']) . 'Controller';

            // If php-controller file exists for this section
            if (class_exists($php)) {

                // Setup flag
                $item['_system']['php-class'] = true;

                // Get parent class
                $parent = get_parent_class($php);

                // If actual parent class is not as per section `extendsPhp` prop - setup error
                if ($parent != $item['extendsPhp']) $item['_system']['php-error']
                    = sprintf('Файл php-контроллера существует, но в нем родительский класс указан как %s', $parent);
            }

            // Add icon for `extendsPhp` prop
            if (($_ = $item['extendsPhp']) != 'Indi_Controller_Admin') $item['_render']['extendsPhp']
                = '<img src="resources/images/icons/btn-icon-php-parent.png" class="i-cell-img">' . $_;

            // Add icon for `extendsJs` prop
            if (($_ = $item['extendsJs']) != 'Indi.lib.controller.Controller') $item['_render']['extendsJs']
                = '<img src="resources/images/icons/btn-icon-js-parent.png" class="i-cell-img">' . $_;

            // Add icon for `filter` prop
            if ($_ = $item['filter']) $item['_render']['filter']
                = '<img src="resources/images/icons/btn-icon-filter.png" class="i-cell-img">' . $_;

            if ($item['$keys']['type'] != 's') {

                // Get js-controller file name
                $js = DOC . STD . '/' . $dir[$item['$keys']['type']] . '/js/admin/app/controller/' . $item['alias']. '.js';

                // If js-controller file exists
                if (file_exists($js)) {

                    // Setup flag
                    $item['_system']['js-class'] = true;

                    // If js-controller file is empty - setup error
                    if (!$js = file_get_contents($js)) $item['_system']['js-error'] = 'Файл js-контроллера пустой';

                    // Else we're unable to find parent class mention - setup error
                    else if (!preg_match('~extend:\s*(\'|")([a-zA-Z0-9\.]+)\1~', $js, $m))
                        $item['_system']['js-error'] = 'В файле js-контроллера не удалось найти родительский класс';

                    // Else if parent class is not as per `extendsJs` prop - setup error
                    else if (($parent = $m[2]) != $item['extendsJs']) $item['_system']['js-error']
                        = sprintf('Файл js-контроллера существует, но в нем родительский класс указан как %s', $parent);;
                }

            // Else
            } else {

                // If system app js is not yet set up - do it
                if (!self::$systemAppJs) self::$systemAppJs = file_get_contents(DOC . STD . '/admin/classic/app.js');

                // If js-controller file exists
                if (preg_match('~Ext\.cmd\.derive\(\'Indi\.controller\.' . $item['alias'] . '\',([^,]+),~', self::$systemAppJs, $m)) {

                    // Setup flag
                    $item['_system']['js-class'] = true;

                    // If parent class is not as per `extendsJs` prop - setup error
                    if ($m[1] != $item['extendsJs']) $item['_system']['js-error']
                        = sprintf('Файл js-контроллера существует, но в нем родительский класс указан как %s', $m[1]);
                }
            }

            // Hide default values
            foreach ($default as $prop => $defaultValue) if ($item[$prop] == $defaultValue) $item[$prop] = '';
        }
    }

    /**
     * Append additional props to the list of to be converted to grid data
     * for js-controller php-controller files badges to be refreshed
     *
     * @return array|mixed
     */
    public function affected4grid() {

        // Get parent
        $affected = $this->callParent();

        // Append props
        foreach (ar('alias,extendsJs,extendsPhp,type') as $prop) $affected []= $prop;

        // Return
        return $affected;
    }

    /**
     * Created copies of selected sections and attach under section, chosen within prompt-window
     * Caution! Do not use it, it's not completed and works properly only in specific situations
     */
    public function copyAction() {

        // Get selected entries ids
        $sectionId_disabled = $this->selected->column('id');

        // If prompt has no answer yet
        if (!Indi::get('answer')) {

            // Create blank `section` entry
            $sectionR = t()->model->createRow();

            // Get `sectionId` field extjs config
            $sectionId_field = $sectionR->combo('sectionId') + array('disabledOptions' => $sectionId_disabled);

            // Prompt for timeId
            jprompt(I_SECTION_CLONE_SELECT_PARENT, array($sectionId_field));

        // If answer is 'ok'
        } else if (Indi::get('answer') == 'ok') {

            // Validate prompt data and flush error is something is not ok
            $_ = jcheck(array(
                'sectionId' => array(
                    'req' => true,
                    'rex' => 'int11',
                    'key' => 'section',
                    'dis' => $sectionId_disabled
                )
            ), json_decode(Indi::post('_prompt'), true));

            // Get prefix
            $prefix = Indi::model($_['sectionId']->entityId)->table();

            // Get sectionId
            $sectionId_parent = $_['sectionId']->id;

            // For each section to be copied
            foreach ($this->selected as $r) {

                // Prepare data
                $config = $r->toArray();

                // Unset id
                unset($config['id']);

                // Append values
                $config['sectionId'] = $sectionId_parent;
                $config['alias'] = $prefix .= ucfirst($r->foreign('entityId')->table);

                // Create new entry, assign props and save
                $new = Indi::model('Section')->createRow($config, true);
                $new->save();

                // Use new entry's id as parent for next iteration
                $sectionId_parent = $new->id;

                // Remove auto-created grid columns
                $new->nested('grid')->delete();

                // Foreach nested entity
                foreach (ar('section2action,grid,alteredField,search') as $nested) {

                    // Get tree-column, if set
                    if ($tc = Indi::model($nested)->treeColumn()) $parent[$nested] = array(0 => 0);

                    // Foreach nested entry
                    foreach ($r->nested($nested) as $nestedR) {

                        // Prepare data
                        $values = $nestedR->toArray();

                        // Unset values that we're going to change
                        foreach (ar('id,sectionId') as $prop) unset($values[$prop]);

                        // Assign `sectionId`
                        $values['sectionId'] = $new->id;

                        // Create new nested entry, assign props and save
                        $clone = $nestedR->model()->createRow($values, true);

                        // If have tree-column - assign value
                        if ($tc) $clone->$tc = $parent[$nested][$nestedR->system('level')];

                        // Save
                        $clone->save();

                        // If have tree-column - remember it's value for child entries
                        if ($tc) $parent[$nested][$nestedR->system('level') + 1] = $clone->id;
                    }
                }
            }

            // Flush success
            jflush(true, 'Copied');

        // Else flush failure
        } else jflush(false, 'Copying cancelled');
    }

    /**
     * Auto-create `grid` entries for `section` entry, if it's a new entry or `entityId` was changed
     */
    public function postSave() {

        // If entityId was not changed - return
        if (!in('entityId', $this->row->affected())) return;

        // Delete old grid info when associated entity has changed
        m('Grid')->fetchAll('`sectionId` = "' . $this->id . '"')->delete();

        // Set up new grid, if associated entity remains not null, event after change
        if (!$this->row->entityId) return;

        // Get entity fields as grid columns candidates
        $fields = m('Field')->fetchAll('`entityId` = "' . $this->row->entityId . '"', '`move`')->toArray();

        // If no fields - return
        if (!count($fields)) return;

        // Declare exclusions array, because not each entity field will have corresponding column in grid
        $exclusions = array();

        // Exclude tree column, if exists
        if ($tc = m($this->row->entityId)->treeColumn()) $exclusions[] = $tc;

        // Exclude columns that have controls of several types, listed below
        for ($i = 0; $i < count($fields); $i++) {
            // 13 - html-editor
            if (in_array($fields[$i]['elementId'], array(13))) {
                if ($fields[$i]['elementId'] == 6 && $fields[$i]['alias'] == 'title') {} else {
                    $exclusions[] = $fields[$i]['alias'];
                }
            }
        }

        // Exclude columns that are links to parent sections
        $parentSectionId = $this->row->sectionId;
        do {
            $parentSection = t()->model->fetchRow('`id` = "' . $parentSectionId . '"');
            if ($parentSection && $parentEntity = $parentSection->foreign('entityId')){
                for ($i = 0; $i < count($fields); $i++) {
                    if ($fields[$i]['alias'] == $parentEntity->table . 'Id' && $fields[$i]['relation'] == $parentEntity->id) {
                        $exclusions[] = $fields[$i]['alias'];
                    }
                }
                $parentSectionId = $parentSection->sectionId;
            }
        } while ($parentEntity);

        // Create grid, stripping exclusions from final grid column list
        $j = 0; $gridId = 0;
        for ($i = 0; $i < count($fields); $i++) {
            if (!in_array($fields[$i]['alias'], $exclusions)) {
                $gridR = m('Grid')->createRow();
                $gridR->gridId = $fields[$i]['elementId'] == 16 ? 0 : $gridId;
                $gridR->sectionId = $this->row->id;
                $gridR->fieldId = $fields[$i]['id'];
                $gridR->save();
                $j++;
                if ($fields[$i]['elementId'] == 16) $gridId = $gridR->id;
            }
        }
    }
}