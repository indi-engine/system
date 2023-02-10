<?php
class Indi_Trail_Admin_Item extends Indi_Trail_Item {

    /**
     * @var Indi_View_Action_Admin
     */
    public $view;

    /**
     * Set up all internal properties
     *
     * @param $sectionR
     * @param $level
     * @throws Exception
     */
    public function __construct($sectionR, $level) {

        // Call parent
        parent::__construct();

        // Setup $this->section
        $config = [];
        $dataTypeA = ['original', 'temporary', 'compiled', 'foreign'];
        foreach ($dataTypeA as $dataTypeI) $config[$dataTypeI] = $sectionR->$dataTypeI();
        $this->section = m('Section')->createRow($config);

        // Setup index
        $this->level = $level;

        // Setup section href
        $this->section->href = (COM ? '' : '/admin') . '/' . $this->section->alias;

        // Setup $this->actions
        $this->actions = m('Action')->createRowset();
        foreach ($sectionR->nested('section2action') as $section2actionR) {
            $actionI = $section2actionR->foreign('actionId')->toArray();
            if (strlen($section2actionR->rename)) $actionI['title'] = $section2actionR->rename;
            $actionI['id'] = $section2actionR->id;
            $actionI['south'] = $section2actionR->south;
            $actionI['fitWindow'] = $section2actionR->fitWindow;
            $actionI['filterOwner'] = $section2actionR->filterOwner;
            $actionI['filterOwnerRoles'] = $section2actionR->filterOwnerRoleIds
                ? $section2actionR->foreign('filterOwnerRoleIds')->col('alias', ',')
                : '';
            $actionI['indi'] = ['ui' => 'section2action', 'id' => $section2actionR->id];
            $actionI['l10n'] = $section2actionR->l10n;
            $actionR = m('Action')->new($actionI);
            $this->actions->append($actionR);
        }

        // Setup subsections
        $this->sections = $sectionR->nested('section');

        // Setup nested section2action-s for subsections
        $sectionR->nested('section')->nested('section2action', [
            'where' => [
                '`toggle` = "y"',
                'FIND_IN_SET("' . $_SESSION['admin']['roleId'] . '", `roleIds`)',
                'FIND_IN_SET(`actionId`, "' . implode(',', Indi_Trail_Admin::$toggledActionIdA) . '")',
                '`actionId` IN (1, 2, 3)'
            ],
            'order' => 'move',
            'foreign' => 'actionId'
        ]);

        // Collect inaccessbile subsections ids from subsections list
        foreach ($sectionR->nested('section') as $subsection)
            if (!$subsection->nested('section2action')->count()) $exclude[] = $subsection->id;
            else if ($subsection->nested('section2action')->select('3,2', 'actionId')->count() != 2)
                $subsection->disableAdd = 1;

        // Exclude inaccessible sections
        $this->sections->exclude($exclude);

        // If current trail item will be a first item
        if (count(Indi_Trail_Admin::$items) == 0) {

            // Setup filters
            foreach ($this->filters = $sectionR->nested('filter') as $filterR) {

                // Get field
                if (!$fieldR = $this->fields($filterR->fieldId)) continue;

                // If further-foreign field is not defined for current filter - skip
                if (!$filterR->further) continue;

                // Get further-foreign field
                $fieldR_further = clone $fieldR->rel()->fields($filterR->further);

                // Prepend foreign field alias to further-foreign field alias
                $fieldR_further->alias = $fieldR->alias . '_' . $fieldR_further->alias;

                // Make sure filter field's storeRelationAbility rely on filter's multiSelect-prop
                if ($fieldR_further->storeRelationAbility != 'none')
                    $fieldR_further->storeRelationAbility = $filterR->multiSelect ? 'many' : 'one';

                // Append to fields list
                $this->fields->append($fieldR_further);
            }

            // Setup action
            foreach ($this->actions as $actionR)
                if ($actionR->alias == uri('action'))
                    $this->action = $actionR;

            // Set fields, that will be used as grid columns in case if current action is 'index'
            if ($this->action->selectionRequired == 'n' || uri()->phantom) $this->gridFields($sectionR);
            else $this->gridFields = m('Field')->createRowset();

            // Alter fields
            $originalDefaults = [];
            foreach ($sectionR->nested('alteredField') as $_) {

                // Prepare modifications
                $modify = [];
                if (strlen($_->rename)) $modify['title'] = $_->rename;
                if (strlen($_->defaultValue)) $modify['defaultValue'] = $_->defaultValue;
                if (!$_->mode) $modify['mode'] = $_->displayInForm ? 'readonly' : 'hidden';
                else if ($_->mode != 'inherit') $modify['mode'] = $_->mode;
                if ($_->elementId) $modify['elementId'] = $_->elementId;

                // Apply modifications
                $fieldR = $this->fields->gb($_->fieldId);
                $fieldR->set($modify);

                // If field's `defaultValue` prop changed - collect 'field's alias' => 'original default value' pairs
                if ($fieldR->isModified('defaultValue'))
                    $originalDefaults[$fieldR->alias] = $fieldR->original('defaultValue');

                // Setup field's altered data. Currently only jump-data is stored here
                if ($_->jumpSectionId && $_->jumpSectionActionId) {
                    $fieldR->altered = [
                        'jump' => [
                            'text' => $_->foreign('jumpSectionActionId')->title,
                            'icon' => $_->foreign('jumpSectionActionId')->foreign('actionId')->icon(true),
                            'href' => '/' . $_->foreign('jumpSectionId')->alias
                                . '/' . $_->foreign('jumpSectionActionId')->foreign('actionId')->alias
                                . '/id/{id}/' . $_->jumpArgs
                        ]
                    ];
                }
            }

            // Save save those pairs under 'originalDefaults' key within section's system data
            $this->section->system('originalDefaults', $originalDefaults);

        // Else
        } else {

            // Setup action as 'index'
            foreach ($this->actions as $actionR) if ($actionR->alias == 'index') $this->action = $actionR;
        }
    }

    /**
     * This function is responsible for preparing data related to grid columns/fields
     *
     * @param null $sectionR
     * @return Indi_Db_Table_Rowset|null
     * @throws Exception
     */
    public function gridFields($sectionR = null) {

        // If $sectionR arg is not given / null / false / zero - use $this->section instead
        if (!$sectionR) $sectionR = $this->section;

        // Set `grid`
        $this->grid = $sectionR->nested('grid');

        // If `groupBy` is non-zero, and there is no such grid column yet - append
        if ($sectionR->groupBy && !$this->grid->gb($sectionR->groupBy, 'fieldId'))
            $this->grid->append(['fieldId' => $sectionR->groupBy]);

        // If `tileField` is non-zero, and there is no such grid column yet - append
        if ($sectionR->tileField && !$this->grid->gb($sectionR->tileField, 'fieldId'))
            $this->grid->append(['fieldId' => $sectionR->tileField]);

        // Build and assign `gridFields` prop
        $this->gridFields = m('Field')->createRowset();

        // Foreach grid column
        foreach ($this->grid as $gridR) {

            // Skip inaccessible
            if (!$gridR->accessible()) continue;

            // Get field
            if (!$fieldR = $this->fields($gridR->fieldId)) continue;

            // If further-foreign field defined for current grid column
            if ($gridR->further) {

                // Get further-foreign field
                $fieldR_further = clone $fieldR->rel()->fields($gridR->further);

                // Prepend foreign field alias to further-foreign field alias
                $fieldR_further->alias = $fieldR->alias . '_' . $fieldR_further->alias;

                // Append to fields list
                $this->fields->append($fieldR_further);

            // Else set false
            } else $fieldR_further = false;

            // Append to grid fields list
            $this->gridFields->append($fieldR_further ?: $fieldR);
        }

        // Return
        return $this->gridFields;
    }

    /**
     * Setup rows for each trail item, if possible
     *
     * @param $index
     * @return string
     * @throws Exception
     */
    public function row($index){

        // If current trail item relates to current section
        if ($index == 0) {

            // If there is an id
            if (uri('id')) {

                // If action is not 'index', so it mean that we are dealing with not rowset, but certain row
                if ($this->action->selectionRequired == 'y') {

                    // Get primary WHERE clause
                    $where = Indi_Trail_Admin::$controller->primaryWHERE();

                    // Prepend an additional part to WHERE clause array, so if row would be found,
                    // it will mean that that row match all necessary requirements
                    array_unshift($where, db()->sql('`id` = :s', uri('id')));

                    // Try to find a row by given id, that, hovewer, also match all requirements,
                    // mentioned in all other WHERE clause parts
                    if (!($this->row = $this->model->row($where)))

                        // If row was not found, return an error
                        return I_ACCESS_ERROR_ROW_DOESNT_EXIST;

                    // Else
                    else

                        // Setup several temporary properties within the existing row, as these may be involved in the
                        // process of parent trail items rows retrieving
                        for ($i = 1; $i < count(Indi_Trail_Admin::$items) - 1; $i++) {

                            // Determine the connector field between, for example 'country' and 'city'. Usually it is
                            // '<parent-table-name>Id' but in some custom cases, this may differ. We do custom connector
                            // field autosetup only if it was set and only in case of one-level-up parent section. This
                            // mean that if we have 'Continents' as upper level, and we are creating city, city's property
                            // name will be determined as `continentId` mean parentSectionConnector logic won't be used for that
                            $connector = $i == 1 && t($i-1)->section->parentSectionConnector
                                ? t($i-1)->section->foreign('parentSectionConnector')->alias
                                : t($i)->model->table() . 'Id';

                            // Get the connector value from session special place and assign it to current row, but only
                            // in case if that connector is not a one of existing fields
                            if (!$this->model->fields($connector))
                                $this->row->$connector = Indi::parentId(t($i)->section->id);
                        }
                }

            // Else there was no id passed within uri, and action is 'form' or 'save', so we assume that
            // user it trying to add a new row within current section
            } else if (uri('action') == 'form' || uri('action') == 'save') {

                // Create an empty row object
                $this->row = $this->model->new();

                // If original defaults collected
                if ($od = $this->section->system('originalDefaults'))

                    // Foreach original default value
                    foreach ($od as $fieldAlias => $originalDefaultValue) {

                        // Directly set current value as modified
                        $this->row->modified($fieldAlias, $this->row->original($fieldAlias));

                        // Directly set collected value as original
                        $this->row->original($fieldAlias, $originalDefaultValue);
                    }

                // If current cms user is an alternate, and if there is corresponding column-field within current entity structure
                if (admin()->table() != 'admin' && $ownerColumn = $this->model->ownerField(admin())->alias)

                    // Force setup of that field value as id of current cms user
                    $this->row->$ownerColumn = admin()->id;

                // Setup several properties within the empty row, e.g if we are trying to create a 'City' row, and
                // a moment ago we were browsing cities list within Canada - we should autosetup a proper `countryId`
                // property for that empty 'City' row, for ability to save it as one of Canada's cities
                for ($i = 1; $i < count(Indi_Trail_Admin::$items) - 1; $i++) {

                    // Determine the connector field between 'country' and 'city'. Usually it is '<parent-table-name>Id'
                    // but in some custom cases, this may differ. We do custom connector field autosetup only if it was
                    // set and only in case of one-level-up parent section. This mean that if we have 'Continents' as
                    // upper level, and we are creating city, city's property name will be determined as `continentId`
                    // mean parentSectionConnector logic won't be used for that
                    $connector = $i == 1 && t($i-1)->section->parentSectionConnector
                        ? t($i-1)->section->foreign('parentSectionConnector')->alias
                        : t($i)->model->table() . 'Id';

                    // Get the connector value from session special place
                    //if ($this->model->fields($connector))
                        $this->row->$connector = $i === 1 && isset(uri()->parent)
                            ? (int) uri()->parent
                            : Indi::parentId(t($i)->section->id);
                }
            }

        // Else if current trail item relates to one of parent sections
        } else {

            // Declare array for WHERE clause
            $where = [];

            // Determine the connector field
            $connector = t($index-1)->section->parentSectionConnector
                ? t($index-1)->section->foreign('parentSectionConnector')->alias
                : t($index)->model->table() . 'Id';

            // Create empty row to be used as parent row, if need
            if (uri('id') === '0' && t($index-1)->action->selectionRequired == 'n' && $index == 1)
                if ($this->row = $this->model->new())
                    return;

            // Get the id
            $id = t($index-1)->action->selectionRequired == 'n' && $index == 1
                ? uri('id')
                : (preg_match('~,~', t($index-1)->row->$connector) // ambiguous check
                    ? Indi::parentId($this->section->id)
                    : t($index-1)->row->$connector);

            // Add main item to WHERE clause stack
            $where[] = '`id` = "' . $id . '"';

            // If a special section's primary filter was defined add it to WHERE clauses stack
            if (strlen(t($index)->section->compiled('filter')))
                $where[] = t($index)->section->compiled('filter');

            // Owner control
            if ($ownerWHERE = Indi_Trail_Admin::$controller->ownerWHERE($index))
                $where[] =  $ownerWHERE;

            // Try to find a row by given id, that, hovewer, also match all requirements,
            // mentioned in all other WHERE clause parts
            if (!($this->row = $this->model->row($where)))

                // If row was not found, return an error
                return false;//I_ACCESS_ERROR_ROW_DOESNT_EXIST;
        }
    }

    /**
     * Setup scope properties for trail item at $index index within Indi_Trail_Admin::$items
     *
     * @param $index
     */
    public function scope($index) {
        $this->scope = new Indi_Trail_Admin_Item_Scope($index);
    }

    /**
     * Get array version of internal variables
     *
     * @return array
     */
    public function toArray() {

        // Call parent
        $array = parent::toArray();

        // Append notices info
        $array['notices'] = m('notice')->info(
            admin(),
            [$this->section->id => $this->scope->primary]
        )[$this->section->id] ?? [];

        // Setup pressed-prop
        foreach ($array['notices'] as &$notice) $notice['pressed'] = $notice['id'] == Indi::get()->notice;

        // Setup scope
        if (array_key_exists('scope', $array)) {
            if (strlen($tabs = $array['scope']['actionrowset']['south']['tabs'])) {
                $tabA = array_unique(ar($tabs));
                if ($tabIdA = array_filter($tabA)) {
                    $where = ['`id` IN (' . implode(',', $tabIdA) . ')'];
                    if (strlen($array['scope']['WHERE'])) $where[] = $array['scope']['WHERE'];
                    $tabRs = $this->model->all($where);
                }
                foreach ($tabA as $i => $id) {
                    if ($id) {
                        if ($tabRs && $r = $tabRs->gb($id)) {
                            $tabA[$i] = [
                                'id' => $id,
                                'title' => $r->title(),
                                'aix' => $this->model->detectOffset(
                                    $array['scope']['WHERE'], $array['scope']['ORDER'], $id
                                )
                            ];
                        } else {
                            unset($tabA[$i]);
                        }
                    } else if ($id == '0') $tabA[$i] = ['id' => $id, 'title' => I_CREATE];
                    else unset($tabA[$i]);
                }
                $array['scope']['actionrowset']['south']['tabs'] = $tabA;
            }
        }

        // Return
        return $array;
    }

    /**
     * Get json-encoded default filter values
     *
     * @return string
     */
    public function jsonDefaultFilters() {

        // Json
        $json = [];

        // Array of range-filters
        $rangeA = ar('number,calendar,datetime');

        // Set up foreign data for `fieldId` prop
        $this->filters->foreign('fieldId');

        // Foreach filter
        foreach ($this->filters as $filter) {

            $fieldR = $filter->foreign('fieldId');

            // Get control element
            $control = $fieldR->foreign('elementId')->alias;

            // If no defaultValue - try next filter
            if (!strlen($filter->defaultValue)) continue;

            // Get compiled value
            $compiled = $filter->compiled('defaultValue');

            // If current filter is a range filter
            if (in($control, $rangeA)) {

                // Get bounds, and append each bound as a separate item in $json array
                $boundA = json_decode(str_replace('\'', '"', $compiled));
                foreach ($boundA as $bound => $value)
                    if (in($bound, ar('lte,gte')))
                        $json[] = [$fieldR->alias . '-' . $bound => $value];

            // Append filter's default value as an array, containing single kay and value
            } else $json[] = [$fieldR->alias => $compiled];
        }

        // Return json-encoded default filters values
        return json_encode($json);
    }

    /**
     * Return the trail item, that is parent for current trail item
     *
     * @param int $step
     * @return Indi_Trail_Admin_Item object
     */
    public function parent($step = 1) {
        return Indi_Trail_Admin::$items[$this->level - $step];
    }

    /**
     * Return base id for current trail item. It is used for building unique 'id' attributes of html elements,
     * and for giving direct access to those of javascript objects, who can be got by their id
     *
     * @return string
     */
    public function bid() {

        // Basement if base id - include section alias and action alias
        $bid = 'i-section-' . $this->section->alias . '-action-' . $this->action->alias;

        // Get shortcuts
        $row = $this->row; $prow = $this->parent()->row;

        // If current trail item has a row - append it's id
        if ($this->row) {

            // Append row id
            $bid .= '-row-' . (int) $this->row->id;

            // If row is is zero - append parent row id
            if (!$this->row->id) {
                if ($this->parent()->row) {
                    $bid .= '-parentrow-' . (int) $this->parent()->row->id;
                }
            }

        // Else if current trail item doesn't have a row, but parent trail item do - append it's id
        } else if ($this->parent()->row) {
            $bid .= '-parentrow-' . (int) $this->parent()->row->id;
        }

        // Return base id
        return $bid;
    }

    /**
     * Setup and/or return action-view object instance for trail item's action
     *
     * @param bool $render
     * @return Indi_View_Action_Admin|string
     */
    public function view($render = false) {

        // If action-view object is already set up - return it
        if ($this->view instanceof Indi_View_Action_Admin && !$render) return $this->view;

        // Setup shortcuts
        $action = $this->action->alias;
        $section = $this->section->alias;

        // Construct filename of the template, that should be rendered by default
        $script = $section . '/' . $action . rif($this->action->l10n == 'y', '-' . ini('lang')->admin) . '.php';

        // Build action-view class name
        $actionClass = 'Admin_' . ucfirst($section) . 'Controller' . ucfirst($action) . 'ActionView';

        // Setup $actionClassDefined flag as `false`, initially
        $actionClassDefinition = false;

        // If template with such filename exists, render the template
        if ($actionClassFile = view()->exists($script)
            ?: view()->exists($script = $section . '/' . $action . '-' . ini('lang')->admin . '.php')) {

            // If $render argument is set to `true`
            if ($render) {

                // If action-view class file is in the list of already included files - this will mean
                // that we are sure about action-view class file definitely contains class declaration,
                // and this fact assumes that there is no sense of this file to be rendered, as rendering
                // won't and shouldn't give any plain output, so, instead of rendering, we return existing
                // action-view class instance
                if (in(str_replace('/', DIRECTORY_SEPARATOR, $actionClassFile), get_included_files()))
                    return $this->view;

                // Else, get the plain result of the rendered script, assignt it to `plain` property of
                // already existing action-view object instance, and return that `plain` property value individually
                else {

                    // If demo-mode is turned On - unset value for each shaded field
                    if (Indi::demo(false)) foreach($this->fields as $fieldR)
                        if ($fieldR->param('shade')) $this->row->{$fieldR->alias} = ' ' . I_PRIVATE_DATA;

                    // Setup view's `plain` property
                    $this->view->plain = view()->render($script);

                    // Pass replacements
                    if (view()->replace) $this->view->replace = view()->replace;

                    // Return the value, according to view mode
                    return $this->view->mode == 'view' ? $this->view : $this->view->plain;
                }

            // Else
            } else {

                // Get the action-view-file source
                $lines = file($actionClassFile);

                // If first line consists of '<?php' string
                if (trim($lines[0]) == '<?php') {

                    // Foreach remaining lines
                    for ($i = 1; $i < count($lines); $i++) {

                        // If no enclosing php-tag found yet
                        if (!preg_match('/\?>/', trim($lines[$i]))) {

                            // If current line contains action-view class definition signature
                            if (preg_match('/class\s+' . $actionClass . '(\s|\{)/', trim($lines[$i]))) {

                                // Setup $actionClassDefinition flag as `true`
                                $actionClassDefinition = true;
                                break;
                            }

                        // Else stop searching action-view class definition signature
                        } else break;
                    }
                }

                // If action-view class definition signature was found in action-view class file - include that file
                if ($actionClassDefinition) include_once($actionClassFile);
            }

        // Else if action-view class instance already exists and $render argument is `true` - return instance
        } else if ($this->view instanceof Indi_View_Action_Admin && $render) return $this->view;


        // If such action class does not exist
        if (!class_exists($actionClass, false)) {

            // Setup default action-view parent class
            $actionParentClass = 'Project_View_Action_Admin';

            // Get modes and views config for actions
            $actionCfg = Indi_Trail_Admin::$controller->actionCfg;

            // Detect mode (rowset or row) and save into trail
            if ($mode = ucfirst($actionCfg['mode'][$action])) {
                $actionParentClass .= '_' . $mode;
                $this->action->mode = $mode;
            }

            // Detect view and save into trail
            if ($view = ucfirst($actionCfg['view'][$action])) {
                $actionParentClass .= '_' . $view;
                $this->action->view = $view;
            }

            // If such action parent class does not exist - replace 'Project' with 'Indi' within it's name
            if (!class_exists($actionParentClass)) $actionParentClass = preg_replace('/^Project/', 'Indi', $actionParentClass);

            // If such action parent class does not exist - roll it back to mode-naming only, without appending view-naming
            if (!class_exists($actionParentClass)) $actionParentClass = preg_replace('/_' . $view . '$/', '', $actionParentClass);

            // Auto-declare action-view class
            eval('class ' . ucfirst($actionClass) . ' extends ' . $actionParentClass . '{}');

        // Else
        } else {

            // Get action-view parent class name
            $actionParentClass = get_parent_class($actionClass);

            // If action-view parent class name contains mode definition
            if (preg_match('/(Indi|Project)_View_Action_Admin_(Row|Rowset)/', $actionParentClass, $mode)) {

                // Pick that mode and assing it as a property of trail item's action object
                $this->action->mode = $mode[2];

                // If action-view parent class name also contains view definition
                if (preg_match('/' . $mode[0]. '_([A-Z][A-Za-z0-9_]*)/', $actionParentClass, $view))

                    // Pick that view and also assign it as a property of trail item's action object
                    $this->action->view = $view[1];
            }
        }

        // Get the action-view instance
        return $this->view = new $actionClass();
    }

    /**
     * Retrieve summary definitions from $_GET['summary'] if given, else from `grid`.`summaryType`
     *
     * @param bool $json
     * @return array|string|void
     */
    public function summary($json = true) {

        // If summary definitions given by $_GET['summary'] - return as is
        if ($summary = Indi::get('summary')) return $json ? $summary : json_decode($summary);

        // Else collect default definitions
        $summary = [];
        foreach ($this->grid as $gridR)
            if ($gridR->summaryType != 'none')
                $summary[$gridR->summaryType] []= $gridR->foreign('fieldId')->alias;

        // Return array, but before json-encode it if need
        return $json ? json_encode($summary) : $summary;
    }

    /**
     * Get comma-separated list of fields ids by their aliases
     *
     * @param $aliases
     * @return array
     */
    public function fieldIds($aliases) {
        return $this->model->fields($aliases, 'rowset')->column('id', true);
    }

    /**
     * Get Grid_Rowset instance, containing Grid_Row instances mapped to given fields $aliases
     *
     * @param $aliases
     * @return mixed
     */
    public function grid($aliases) {

        // Get field ids
        $fieldIds = $this->fieldIds($aliases);

        // Search among grid column
        return $this->grid->select($fieldIds, 'fieldId');
    }

    /**
     * Get render config
     *
     * @return array
     * @throws Exception
     */
    public function renderCfg() {

        // Prepare render config for fields
        foreach ($this->gridFields ? $this->gridFields : [] as $field) {
            if ($fieldId = m()->fields($field->alias)->id) {
                if ($icon = t()->scope->icon[$fieldId]) $renderCfg[$field->alias]['icon'] = $icon;
                if ($jump = t()->scope->jump[$fieldId]) $renderCfg[$field->alias]['jump'] = $jump;
                if (null !== ($color = t()->scope->color[$fieldId])) $renderCfg[$field->alias]['color'] = $color;
            }
        }

        // Prepare render config for records
        foreach (ar('colorField,colorFurther') as $prop)
            if (t()->section->$prop)
                $renderCfg['_system'][$prop] = t()->section->foreign($prop)->alias; 

        // If scope's filterOwner is non-false
        if ($this->scope->filterOwner) {
            $renderCfg['_system']['owner'] = [
                'field' => $this->scope->filterOwner,
                'role' => admin()->roleId,
                'id' => admin()->id,
            ];
        }

        // If rows grouping is turned on - add that into $renderCfg as well
        if ($this->scope->groupBy) $renderCfg['_system']['groupBy'] = $this->scope->groupBy;

        // Return render config
        return $renderCfg ?? [];
    }

    /**
     * Get array of [fieldId => jumpUri] pairs
     * Keys are picked from `grid`.`further` (if non-zero) or `grid`.`fieldId`
     *
     * @return array
     */
    public function jumps() {

        // If jumps are not yet introduced - return
        if (!m('grid')->fields('jumpSectionId')) return;

        // Collect jumps info
        foreach ($this->grid->select(': !== null', 'jumpSectionId')->select(': !="0"', 'jumpSectionId') as $gridR)
            $jumpA[$gridR->further ?: $gridR->fieldId]
                = '/' . $gridR->foreign('jumpSectionId')->alias
                . '/' . $gridR->foreign('jumpSectionActionId')->foreign('actionId')->alias
                . '/id/{id}/' . $gridR->jumpArgs;

        // Return jumps info
        return $jumpA ?? [];
    }

    /**
     * Get array of [fieldId => icon] pairs
     * Keys are picked from `grid`.`further` (if non-zero) or `grid`.`fieldId`
     *
     * @return array
     */
    public function icons() {

        // Get icons
        foreach ($this->grid->select(': !=""', 'icon') as $gridR)
            $iconA[$gridR->further ?: $gridR->fieldId] = $gridR->icon;

        // Return icons
        return $iconA ?? [];
    }

    /**
     * Get array of [fieldId => color] pairs
     * color can be either color in #rrggbb-format or level for color to be break down by
     *
     * Keys are picked from `grid`.`further` (if non-zero) or `grid`.`fieldId`
     *
     * @return array
     */
    public function colors() {

        // Get icons
        foreach ($this->grid as $gridR) {

            // Get fieldId-value for either further-prop or fieldId-prop
            $fieldId = $gridR->further ?: $gridR->fieldId;

            // If color break-by-level is defined - get the level
            if ($gridR->colorBreak == 'y') {
                $colorA[$fieldId] = $gridR->foreign($gridR->further ? 'further' : 'fieldId')->param('colorBreakLevel');

            // Else get color directly specified for this column
            } else if ($gridR->colorDirect) {
                $colorA[$fieldId] = $gridR->rgb('colorDirect');

            // Else get color from certain property of an external entry, specified for this column
            } else if ($gridR->colorField && $gridR->colorEntry) {
                $colorA[$fieldId] = $gridR->foreign('colorEntry')->rgb($gridR->foreign('colorField')->alias);

            // Else if only colorField is specified, but it is from the same entity as grid field's relation
            } else if ($gridR->colorField && $gridR->foreign('colorField')->entityId == $gridR->foreign('fieldId')->relation) {
                $colorA[$fieldId] = ':' . $gridR->foreign('colorField')->alias;
            }

            // Unset empty values
            if (!strlen($colorA[$fieldId])) unset($colorA[$fieldId]);
        }

        // Return columns colors
        return $colorA ?? [];
    }

    /**
     * Check whether owner-access restriction should be applied.
     * If no - boolean false will be returned.
     * If yes, but no owner-column applicable for current user does not exist in current model - null will be returned
     * If yes, and owner-column applicable for current user does exist - column name will be returned
     * If yes, but 2 fields are intended for owner ('ownerRole' and 'ownerId') - null will be returned as well
     *
     * @return bool|string
     */
    public function filterOwner($level) {

        // Owner shortcut
        $owner = admin();

        // If $level is action
        if ($level == 'action') {

            // Setup $filterOwner flag based on action's filterOwner-prop
            $filterOwner = $this->action->filterOwner == 'yes' || (
                $this->action->filterOwner == 'certain' && in($owner->role, $this->action->filterOwnerRoles)
            );

        // Else if $level is section
        } else if ($level == 'section') {

            // Setup $filterOwner flag based on section's filterOwner-prop
            $filterOwner = in($this->section->filterOwner, 'yes,certain');
        }

        // If $filterOwner flag is true, so 'Owner only'-restriction should be applied
        // return name of field (if exists), responsible for storing ownerId for a record
        return $filterOwner
            ? $this->model->ownerField($owner)->alias
            : false;
    }
}