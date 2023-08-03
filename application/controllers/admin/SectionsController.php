<?php
class Admin_SectionsController extends Indi_Controller_Admin_Exportable {

    /**
     * Contents of VDR . '/client/classic/app.js' for checking js-controllers files presence
     *
     * @var string
     */
    public static $systemAppJs = '';

    /**
     * Create js-controller file for selected section
     */
    public function jsAction() {

        // JS-controller files for sections of fraction:
        // - 'system' - will be created in VDR . '/system',
        // - 'public' -                 in VDR . '/public',
        // - 'custom' -                 in '',
        // but beware that js-controller files created for system-fraction sections should be moved
        // from VDR . '/system/js/admin/app/controller to Indi Engine client app source code
        // into VDR . '/client-dev/app/controller folder, and then should be compiled
        // by 'sencha app build --production' command to VDR . '/client/classic/app.js' bundle to be refreshed
        $repoDirA = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // If current section has a fraction, that is (for some reason) not in the list of known types
        if (!in($this->row->fraction, array_keys($repoDirA)))

            // Flush an error
            jflush(false, __('Can\'t detect fraction of selected section'));

        // Build the dir name, that controller's js-file should be created in
        $dir = Indi::dir(DOC . STD . $repoDirA[$this->row->fraction] . '/js/admin/app/controller/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the controller name
        $ctrl = $this->row->alias;

        // Get absolute and relative paths
        $ctrlAbs = $dir . $ctrl . '.js';
        $ctrlRel = preg_replace('~^' . preg_quote(DOC) . '/~', '', $ctrlAbs);

        // If controller file already exists
        if (is_file($ctrlAbs))
            jflush(false, __('File already exists: %s', $ctrlRel));

        // Build template model absolute file name
        $tplAbs = DOC. STD . VDR . '/client/classic/resources/{controller}.js';

        // If it is not exists - flush an error, as we have no template for creating a model file
        if (!is_file($tplAbs)) jflush(false, __('No template-controller file found'));

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

        // Reload this row in all grids it's currently shown in
        t()->row->realtime('reload');

        // Flush success
        jflush(true, __('Created file: %s', $ctrlRel));
    }

    /**
     * Create php-controller file for selected section
     */
    public function phpAction() {

        // PHP-controller files for sections of fraction:
        // - 'system' - will be created in VDR . '/system',
        // - 'public'                   in VDR . '/public',
        // - 'custom'                   in ''
        $repoDirA = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // If current section has a fraction, that is (for some reason) not in the list of known types
        if (!in($this->row->fraction, array_keys($repoDirA)))

            // Flush an error
            jflush(false, __('Can\'t detect the alias of repository, associated with a fraction of the chosen section'));

        // Build the dir name, that controller's js-file should be created in
        $dir = Indi::dir(DOC . STD . $repoDirA[$this->row->fraction] . '/application/controllers/admin/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the controller name
        $ctrl = ucfirst($this->row->alias);

        // Get absolute and relative paths
        $ctrlAbs = $dir . $ctrl . 'Controller.php';
        $ctrlRel = preg_replace('~^' . preg_quote(DOC) . '/~', '', $ctrlAbs);

        // If controller file is not yet exist
        if (is_file($ctrlAbs))
            jflush(false, __('File already exists: %s', $ctrlRel));

        // Build template model absolute file name
        $tplAbs = DOC. STD . VDR . '/system/application/controllers/admin/{controller}.php';

        // If it is not exists - flush an error, as we have no template for creating a model file
        if (!is_file($tplAbs)) jflush(false, __('No template-controller file found'));

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

        // Reload this row in all grids it's currently shown in
        t()->row->realtime('reload');

        // Flush success
        jflush(true, __('Created file: %s', $ctrlRel));
    }

    /**
     * todo: make 'Inversion' checkbox-field for filters
     */
    public function indexAction() {

        //
        m()->fields('roleIds')->storeRelationAbility = 'one';

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
            && $info = Indi::rexm('~<span (.*?)( title="(.*?)"|)></span>(.*)$~', $item['_render']['defaultSortDirection'])) {

            // If defaultSortField is -1 - assume it's ID-column
            if ($item['defaultSortField'] == -1) $item['_render']['defaultSortField'] = 'ID';

            // Setup jump
            $item['_system']['jump']['defaultSortField'] = [[
                'href' => 'cell:defaultSortDirection',
                'ibox' => $info[1],
                'over' => $info[3] ?: $info[4]
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

        // Dirs dict by section fraction
        $dir = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // Foreach data item
        foreach ($data as &$item) {

            // Get php-controller class name
            $php = 'Admin_' . ucfirst($item['alias']) . 'Controller';

            // If php-controller file exists for this section
            if ($item['_system']['php-class'] = class_exists($php)) {

                // Get parent class
                $parent = get_parent_class($php);

                // If actual parent class is not as per section `extendsPhp` prop - setup error
                if ($parent != $item['extendsPhp']) $item['_system']['php-error']
                    = __('Файл php-контроллера существует, но в нем родительский класс указан как %s', $parent);
            }

            // If it's a system-fraction
            if ($item['fraction'] == 'system') {

                // If system app js is not yet set up - do it
                if (!self::$systemAppJs) self::$systemAppJs = file_get_contents(DOC . STD . VDR . '/client/classic/app.js');

                // If js-controller file exists
                if ($item['_system']['js-class']
                    = preg_match('~Ext\.cmd\.derive\(\'Indi\.controller\.' . $item['alias'] . '\',([^,]+),~', self::$systemAppJs, $m)) {

                    // If parent class is not as per `extendsJs` prop - setup error
                    if ($m[1] != $item['extendsJs']) $item['_system']['js-error']
                        = __('Файл js-контроллера существует, но в нем родительский класс указан как %s', $m[1]);
                }
            }

            // Get js-controller file name
            $js = DOC . STD . $dir[$item['fraction']] . '/js/admin/app/controller/' . $item['alias']. '.js';

            // If js-controller file exists
            if (!$item['_system']['js-class'] && $item['_system']['js-class'] = file_exists($js)) {


                // If js-controller file is empty - setup error
                if (!$js = file_get_contents($js)) $item['_system']['js-error'] = __('Файл js-контроллера пустой');

                // Else we're unable to find parent class mention - setup error
                else if (!preg_match('~extend:\s*(\'|")([a-zA-Z0-9\.]+)\1~', $js, $m))
                    $item['_system']['js-error'] = __('В файле js-контроллера не удалось найти родительский класс');

                // Else if parent class is not as per `extendsJs` prop - setup error
                else if (($parent = $m[2]) != $item['extendsJs']) $item['_system']['js-error']
                    = __('Файл js-контроллера существует, но в нем родительский класс указан как %s', $parent);;
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
        foreach (ar('alias,extendsJs,extendsPhp,fraction') as $prop) $affected []= $prop;

        // Return
        return $affected;
    }

    /**
     * Created copies of selected sections and attach under section, chosen within prompt-window
     * Caution! Do not use it, it's not completed and works properly only in specific situations
     */
    public function copyAction() {

        // Get selected entries ids
        $sectionId_disabled = t()->rows->column('id');

        // If prompt has no answer yet
        if (!Indi::get('answer')) {

            // Create blank `section` entry
            $sectionR = m()->new();

            // Get `sectionId` field extjs config
            $sectionId_field = ['fieldLabel' => ''] + $sectionR->combo('sectionId') + ['disabledOptions' => $sectionId_disabled];

            // Prompt for timeId
            jprompt(I_SECTION_CLONE_SELECT_PARENT, [$sectionId_field]);

        // If answer is 'ok'
        } else if (Indi::get('answer') == 'ok') {

            // Validate prompt data and flush error is something is not ok
            $_ = jcheck([
                'sectionId' => [
                    'req' => true,
                    'rex' => 'int11',
                    'key' => 'section',
                    'dis' => $sectionId_disabled
                ]
            ], json_decode(Indi::post('_prompt'), true));

            // Get prefix
            $prefix = $_['sectionId']->entityId ? m($_['sectionId']->entityId)->table() : $_['sectionId']->alias;

            // Get sectionId
            $sectionId_parent = $_['sectionId']->id;

            // For each section to be copied
            foreach (t()->rows as $r) {

                // Prepare data
                $config = $r->toArray();

                // Unset id
                unset($config['id']);

                // Append values
                $config['sectionId'] = $sectionId_parent;
                $config['alias'] = $prefix .= ucfirst($r->foreign('entityId')->table);

                // Create new entry, assign props and save
                $new = m('Section')->new($config);
                $new->save();

                // Use new entry's id as parent for next iteration
                $sectionId_parent = $new->id;

                // Remove auto-created grid columns
                $new->nested('grid')->delete();

                // Foreach nested entity
                foreach (ar('section2action,grid,alteredField,filter') as $nested) {

                    // Get tree-column, if set
                    if ($tc = m($nested)->treeColumn()) $parent[$nested] = [0 => 0];

                    // Foreach nested entry
                    foreach ($r->nested($nested) as $nestedR) {

                        // Prepare data
                        $values = $nestedR->toArray();

                        // Unset values that we're going to change
                        foreach (ar('id,sectionId') as $prop) unset($values[$prop]);

                        // Assign `sectionId`
                        $values['sectionId'] = $new->id;

                        // Create new nested entry, assign props and save
                        $clone = $nestedR->model()->new($values);

                        // If have tree-column - assign value
                        if ($tc) $clone->$tc = $parent[$nested][$nestedR->system('level')];

                        // Save
                        $clone->save();

                        // If have tree-column - remember it's value for child entries
                        if ($tc) $parent[$nested][$nestedR->system('level') + 1] = $clone->id;
                    }
                }
            }

        // Else flush failure
        } else jflush(false);
    }

    /**
     * Auto-create `grid` entries for `section` entry, if it's a new entry or `entityId` was changed
     */
    public function postSave() {

        // If entityId was not changed - return
        if (!in('entityId', $this->row->affected())) return;

        // Delete old grid info when associated entity has changed
        m('Grid')->all('`sectionId` = "' . $this->id . '"')->delete();

        // Set up new grid, if associated entity remains not null, event after change
        if (!$this->row->entityId) return;

        // Get entity fields as grid columns candidates
        $fields = m('Field')->all('`entityId` = "' . $this->row->entityId . '"', '`move`')->toArray();

        // If no fields - return
        if (!count($fields)) return;

        // Declare exclusions array, because not each entity field will have corresponding column in grid
        $exclusions = [];

        // Exclude tree column, if exists
        if ($tc = m($this->row->entityId)->treeColumn()) $exclusions[] = $tc;

        // Exclude columns that have controls of several types, listed below
        for ($i = 0; $i < count($fields); $i++) {
            // 13 - html-editor
            if (in_array($fields[$i]['elementId'], [13])) {
                if ($fields[$i]['elementId'] == 6 && $fields[$i]['alias'] == 'title') {} else {
                    $exclusions[] = $fields[$i]['alias'];
                }
            }
        }

        // Exclude columns that are links to parent sections
        $parentSectionId = $this->row->sectionId;
        do {
            $parentSection = m()->row($parentSectionId);
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
                $gridR = m('Grid')->new();
                $gridR->gridId = $fields[$i]['elementId'] == 16 ? 0 : $gridId;
                $gridR->sectionId = $this->row->id;
                $gridR->fieldId = $fields[$i]['id'];
                $gridR->save();
                $j++;
                if ($fields[$i]['elementId'] == 16) $gridId = $gridR->id;
            }
        }
    }

    public function onBeforeCellSave($cell, $value) {

        // If something is NOT going to be turned on - return
        if ($value !== 'y') return;

        // If tile-panel is going to be enabled
        if ($cell === 'tileToggle') {

            // If tileField is already defined for current section - return
            // as this field is the minimum required for tile-panel to be enabled
            //if (t()->row->tileField) return;

            // Get panel title
            $panel = t()->fields('tile')->title;

            // Make tileField-prop to be required
            t()->fields('tileField')->mode = 'required';

            // Get shared config
            $shared = ['labelWidth' => 120, 'width' => 400];

            // Prompt data
            $prompt = $this->prompt(__(I_SECTION_ROWSET_MIN_CONF, $panel), [
                $shared + t()->row->combo('tileField'),
                $shared + t()->row->combo('tileThumb'),
            ]);

            // Check that data
            jcheck([
                'tileField' => [
                    'req' => true,
                    'rex' => 'int11',
                    'fis' => m()->fields()->select(element('upload')->id, 'elementId')->fis(),
                ],
                'tileThumb' => [
                    'rex' => 'int11',
                    'key' => 'resize'
                ],
            ], $prompt);

            // Apply tile-props to the section
            t()->row->set([
                'tileField' => $prompt['tileField'],
                'tileThumb' => $prompt['tileThumb']
            ])->save();
        }

        // If plan-panel is going to be enabled
        if ($cell == 'planToggle') {

            // Shortcut to entity
            $entityR = t()->row->foreign('entityId');

            // If spaceScheme- and spaceFields-prop are already defined for the entity
            // linked to current section - return, as those are the minimum required for plan-panel to be enabled
            //if ($entityR->spaceScheme !== 'none' && $entityR->spaceFields) return;

            // Get panel title
            $panel = t()->fields('plan')->title;

            // Get shared config
            $shared = ['labelWidth' => 230, 'width' => 500, 'allowBlank' => 0];

            // Prompt for plan-panel config
            $prompt = $this->prompt(__(I_SECTION_ROWSET_MIN_CONF, $panel), [
                $shared + $entityR->combo('spaceScheme'),
                $shared + $entityR->combo('spaceFields'),
                $shared + t()->row->combo('planTypes')
            ]);

            // Check prompt data
            $_ = jcheck([
                'spaceScheme' => [
                    'req' => true,
                    'dis' => 'none',
                    'fis' => m('entity')->fields('spaceScheme')->nested('enumset')->fis('alias'),
                ],
                'spaceFields' => [
                    'req' => true,
                    'rex' => 'int11list',
                    'key' => 'field*'
                ],
                'planTypes' => [
                    'req' => true,
                    'fis' => m('section')->fields('planTypes')->nested('enumset')->fis('alias'),
                ]
            ], $prompt);

            // Apply space-props to the section's underlying entity
            $entityR->set([
                'spaceScheme' => $prompt['spaceScheme'],
                'spaceFields' => $prompt['spaceFields']
            ]);

            // If space-fields are modified
            if ($entityR->isModified('spaceScheme,spaceFields')) {

                // Show side msg saying params are being applied
                msg(I_SECTION_CONF_SETUP);

                // Apply those
                $entityR->save();
            }

            // Show side msg saying params were successfully applied
            if ($entityR->affected('spaceScheme,spaceFields'))
                msg(I_SECTION_CONF_SETUP_DONE);

            // Set plan types to be further saved
            t()->row->set('planTypes', $prompt['planTypes']);
        }
    }
}