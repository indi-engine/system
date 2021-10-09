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
     */
    public function __construct($sectionR, $level) {

        // Call parent
        parent::__construct();

        // Setup $this->section
        $config = array();
        $dataTypeA = array('original', 'temporary', 'compiled', 'foreign');
        foreach ($dataTypeA as $dataTypeI) $config[$dataTypeI] = $sectionR->$dataTypeI();
        $this->section = Indi::model('Section')->createRow($config);

        // Setup index
        $this->level = $level;

        // Setup section href
        $this->section->href = (COM ? '' : '/admin') . '/' . $this->section->alias;

        // Setup $this->actions
        $this->actions = Indi::model('Action')->createRowset();
        foreach ($sectionR->nested('section2action') as $section2actionR) {
            $actionI = $section2actionR->foreign('actionId')->toArray();
            if (strlen($section2actionR->rename)) $actionI['title'] = $section2actionR->rename;
            $actionI['id'] = $section2actionR->id;
            $actionI['south'] = $section2actionR->south;
            $actionI['fitWindow'] = $section2actionR->fitWindow;
            $actionI['indi'] = array('ui' => 'section2action', 'id' => $section2actionR->id);
            $actionI['l10n'] = $section2actionR->l10n;
            $actionR = m('Action')->createRow()->assign($actionI);
            $this->actions->append($actionR);
        }

        // Setup subsections
        $this->sections = $sectionR->nested('section');

        // Setup nested section2action-s for subsections
        $sectionR->nested('section')->nested('section2action', array(
            'where' => array(
                '`toggle` = "y"',
                'FIND_IN_SET("' . $_SESSION['admin']['profileId'] . '", `profileIds`)',
                'FIND_IN_SET(`actionId`, "' . implode(',', Indi_Trail_Admin::$toggledActionIdA) . '")',
                '`actionId` IN (1, 2, 3)'
            ),
            'order' => 'move',
            'foreign' => 'actionId'
        ));

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
            foreach ($this->filters = $sectionR->nested('search') as $filterR) {

                // Get field
                if (!$fieldR = $this->fields($filterR->fieldId)) continue;

                // If further-foreign field is not defined for current filter - skip
                if (!$filterR->further) continue;

                // Get further-foreign field
                $fieldR_further = clone $fieldR->rel()->fields($filterR->further);

                // Prepend foreign field alias to further-foreign field alias
                $fieldR_further->alias = $fieldR->alias . '_' . $fieldR_further->alias;

                // Append to fields list
                $this->fields->append($fieldR_further);
            }

            // Setup action
            foreach ($this->actions as $actionR)
                if ($actionR->alias == Indi::uri('action'))
                    $this->action = $actionR;

            // Set fields, that will be used as grid columns in case if current action is 'index'
            if ($this->action->rowRequired == 'n' || Indi::uri()->phantom) $this->gridFields($sectionR);

            // Alter fields
            $originalDefaults = array();
            foreach ($sectionR->nested(entity('alteredField') ? 'alteredField' : 'disabledField') as $_) {

                // Prepare modifications
                $modify = array();
                if (strlen($_->rename)) $modify['title'] = $_->rename;
                if (strlen($_->defaultValue)) $modify['defaultValue'] = $_->defaultValue;
                if (!$_->mode) $modify['mode'] = $_->displayInForm ? 'readonly' : 'hidden';
                else if ($_->mode != 'inherit') $modify['mode'] = $_->mode;
                if ($_->elementId) $modify['elementId'] = $_->elementId;

                // Apply modifications
                $fieldR = $this->fields->gb($_->fieldId);
                $fieldR->assign($modify);

                // If field's `defaultValue` prop changed - collect 'field's alias' => 'original default value' pairs
                if ($fieldR->isModified('defaultValue'))
                    $originalDefaults[$fieldR->alias] = $fieldR->original('defaultValue');
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
     */
    public function gridFields($sectionR = null) {

        // If $sectionR arg is not given / null / false / zero - use $this->section instead
        if (!$sectionR) $sectionR = $this->section;

        // Set `grid`
        $this->grid = $sectionR->nested('grid');

        // If `groupBy` is non-zero, and there is no such grid column yet - append
        if ($sectionR->groupBy && !$this->grid->gb($sectionR->groupBy, 'fieldId'))
            $this->grid->append(array('fieldId' => $sectionR->groupBy));

        // If `tileField` is non-zero, and there is no such grid column yet - append
        if ($sectionR->tileField && !$this->grid->gb($sectionR->tileField, 'fieldId'))
            $this->grid->append(array('fieldId' => $sectionR->tileField));

        // Build and assign `gridFields` prop
        $this->gridFields = Indi::model('Field')->createRowset();

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
     */
    public function row($index){

        // If current trail item relates to current section
        if ($index == 0) {

            // If there is an id
            if (Indi::uri('id')) {

                // If action is not 'index', so it mean that we are dealing with not rowset, but certain row
                if ($this->action->rowRequired == 'y') {

                    // Get primary WHERE clause
                    $where = Indi_Trail_Admin::$controller->primaryWHERE();

                    // Prepend an additional part to WHERE clause array, so if row would be found,
                    // it will mean that that row match all necessary requirements
                    array_unshift($where, Indi::db()->sql('`id` = :s', Indi::uri('id')));
                    //i($where, 'a');

                    // Try to find a row by given id, that, hovewer, also match all requirements,
                    // mentioned in all other WHERE clause parts
                    if (!($this->row = $this->model->fetchRow($where)))

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
                            $connector = $i == 1 && Indi::trail($i-1)->section->parentSectionConnector
                                ? Indi::trail($i-1)->section->foreign('parentSectionConnector')->alias
                                : Indi::trail($i)->model->table() . 'Id';

                            // Get the connector value from session special place and assign it to current row, but only
                            // in case if that connector is not a one of existing fields
                            if (!$this->model->fields($connector))
                                $this->row->$connector = $_SESSION['indi']['admin']['trail']['parentId']
                                [Indi::trail($i)->section->id];
                        }
                }

            // Else there was no id passed within uri, and action is 'form' or 'save', so we assume that
            // user it trying to add a new row within current section
            } else if (Indi::uri('action') == 'form' || Indi::uri('action') == 'save') {

                // Create an empty row object
                $this->row = $this->model->createRow();

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
                if (Indi::admin()->alternate && in($aid = Indi::admin()->alternate . 'Id', $this->model->fields(null, 'columns')))

                    // Force setup of that field value as id of current cms user
                    $this->row->$aid = Indi::admin()->id;

                // Setup several properties within the empty row, e.g if we are trying to create a 'City' row, and
                // a moment ago we were browsing cities list within Canada - we should autosetup a proper `countryId`
                // property for that empty 'City' row, for ability to save it as one of Canada's cities
                for ($i = 1; $i < count(Indi_Trail_Admin::$items) - 1; $i++) {

                    // Determine the connector field between 'country' and 'city'. Usually it is '<parent-table-name>Id'
                    // but in some custom cases, this may differ. We do custom connector field autosetup only if it was
                    // set and only in case of one-level-up parent section. This mean that if we have 'Continents' as
                    // upper level, and we are creating city, city's property name will be determined as `continentId`
                    // mean parentSectionConnector logic won't be used for that
                    $connector = $i == 1 && Indi::trail($i-1)->section->parentSectionConnector
                        ? Indi::trail($i-1)->section->foreign('parentSectionConnector')->alias
                        : Indi::trail($i)->model->table() . 'Id';

                    // Get the connector value from session special place
                    //if ($this->model->fields($connector))
                        $this->row->$connector = $_SESSION['indi']['admin']['trail']['parentId']
                        [Indi::trail($i)->section->id];
                }
            }

        // Else if current trail item relates to one of parent sections
        } else {

            // Declare array for WHERE clause
            $where = array();

            // Determine the connector field
            $connector = Indi::trail($index-1)->section->parentSectionConnector
                ? Indi::trail($index-1)->section->foreign('parentSectionConnector')->alias
                : Indi::trail($index)->model->table() . 'Id';

            // Create empty row to be used as parent row, if need
            if (Indi::uri('id') === '0' && Indi::trail($index-1)->action->rowRequired == 'n' && $index == 1)
                if ($this->row = $this->model->createRow())
                    return;

            // Get the id
            $id = Indi::trail($index-1)->action->rowRequired == 'n' && $index == 1
                ? Indi::uri('id')
                : (preg_match('/,/', Indi::trail($index-1)->row->$connector) // ambiguous check
                    ? $_SESSION['indi']['admin']['trail']['parentId'][$this->section->id]
                    : Indi::trail($index-1)->row->$connector);

            // Add main item to WHERE clause stack
            $where[] = '`id` = "' . $id . '"';

            // If a special section's primary filter was defined add it to WHERE clauses stack
            if (strlen(Indi::trail($index)->section->compiled('filter')))
                $where[] = Indi::trail($index)->section->compiled('filter');

            // Owner control
            if ($alternateWHERE = Indi_Trail_Admin::$controller->alternateWHERE($index))
                $where[] =  $alternateWHERE;

            // Try to find a row by given id, that, hovewer, also match all requirements,
            // mentioned in all other WHERE clause parts
            if (!($this->row = $this->model->fetchRow($where)))

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

        // Setup scope
        if (array_key_exists('scope', $array)) {
            if (strlen($tabs = $array['scope']['actionrowset']['south']['tabs'])) {
                $tabA = array_unique(ar($tabs));
                if ($tabIdA = array_filter($tabA)) {
                    $where = array('`id` IN (' . implode(',', $tabIdA) . ')');
                    if (strlen($array['scope']['WHERE'])) $where[] = $array['scope']['WHERE'];
                    $tabRs = $this->model->fetchAll($where);
                }
                foreach ($tabA as $i => $id) {
                    if ($id) {
                        if ($tabRs && $r = $tabRs->gb($id)) {
                            $tabA[$i] = array(
                                'id' => $id,
                                'title' => $r->title(),
                                'aix' => $this->model->detectOffset(
                                    $array['scope']['WHERE'], $array['scope']['ORDER'], $id
                                )
                            );
                        } else {
                            unset($tabA[$i]);
                        }
                    } else if ($id == '0') $tabA[$i] = array('id' => $id, 'title' => I_CREATE);
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
        $json = array();

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
                        $json[] = array($fieldR->alias . '-' . $bound => $value);

            // Append filter's default value as an array, containing single kay and value
            } else $json[] = array($fieldR->alias => $compiled);
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

        // If current trail item has a row - append it's id
        if ($this->row)
            $bid .= '-row-' . (int) $this->row->id;

        // Else if current trail item doesn't have a row, but parent trail item do - append it's id
        else if ($this->parent()->row)
            $bid .= '-parentrow-' . (int) $this->parent()->row->id;

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
        $script = $section . '/' . $action . rif($this->action->l10n == 'y', '-' . Indi::ini('lang')->admin) . '.php';

        // Build action-view class name
        $actionClass = 'Admin_' . ucfirst($section) . 'Controller' . ucfirst($action) . 'ActionView';

        // Setup $actionClassDefined flag as `false`, initially
        $actionClassDefinition = false;

        // If template with such filename exists, render the template
        if ($actionClassFile = Indi::view()->exists($script)
            ?: Indi::view()->exists($script = $section . '/' . $action . '-' . Indi::ini('lang')->admin . '.php')) {

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
                    $this->view->plain = Indi::view()->render($script);

                    // Pass replacements
                    if (Indi::view()->replace) $this->view->replace = Indi::view()->replace;

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
        $summary = array();
        foreach ($this->grid as $gridR)
            if ($gridR->summaryType != 'none')
                $summary[$gridR->summaryType] []= $gridR->foreign('fieldId')->alias;

        // Return array, but before json-encode it if need
        return $json ? json_encode($summary) : $summary;
    }

    /**
     * Create `realtime` entry having `type` = "context"
     *
     * @return Indi_Db_Table_Row
     */
    public function context() {

        // If channel found
        if ($channel = m('realtime')->fetchRow('`token` = "' . CID . '"')) {

            // Get data to be copied
            $data = $channel->original(); unset($data['id'], $data['spaceSince']);

            // Get involved fields
            $fields = t()->row
                ? t()->fields->select('readonly,ordinary', 'mode')->column('id', ',')
                : t()->gridFields->select(': > 0')->column('id', ',');

            // Create `realtime` entry of `type` = "context"
            $realtimeR = m('realtime')->createRow([
                'realtimeId' => $channel->id,
                'type' => 'context',
                'token' => t()->bid(),
                'sectionId' => t()->section->id,
                'entityId' => t()->section->entityId,
                'fields' => $fields,
                'title' => Indi::trail(true)->toString(),
                'mode' => $this->action->rowRequired == 'y' ? 'row' : 'rowset'
            ] + $data, true);

            // Save it
            $realtimeR->save();

            // Return it
            return $realtimeR;
        }
    }
}