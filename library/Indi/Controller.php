<?php
class Indi_Controller {

    /**
     * Encoding of contents, that will be sent to the client browser
     *
     * @var string
     */
    public $encoding = 'utf-8';

    /**
     * Constructor
     */
    public function __construct() {

        // Set locale
        if (Indi::ini()->lang->{Indi::uri()->module} == 'ru')
            setlocale(LC_TIME, 'ru_RU.UTF-8', 'ru_utf8', 'Russian_Russia.UTF8', 'ru_RU', 'Russian');

        // Create an Indi_View instance
		$view = class_exists('Project_View') ? new Project_View : new Indi_View();

        // Get the script path
        $spath = Indi::ini('view')->scriptPath;

        // If module is 'front', and design-specific config was set up,
        // detect design specific dir name, that will be used to build
        // additional paths for both scripts and helpers
        if (Indi::uri('module') == 'front' && is_array($dsdirA = (array) Indi::ini('view')->design))
            foreach($dsdirA as $dsdirI => $domainS)
                if (in($_SERVER['HTTP_HOST'], explode(' ', $domainS)))
                    Indi::ini()->design = $dsdirI;

        // Do paths setup twice: first for module-specific paths, second for general-paths
        for ($i = 0; $i < 2; $i++) {

            // Get the module paths and prefixes
            $mpath =  !$i ? '/' . Indi::uri('module') : '';
            $mhpp =   !$i ? '/' . ucfirst(Indi::uri('module')) : '';
            $mhcp =   !$i ? ucfirst(Indi::uri('module')) . '_' : '';

            // Add script paths for certain/current project
            if (is_dir(DOC . STD . '/www/' . $spath)) {

                // Add design-specific script path
                if (Indi::ini()->design) $view->addScriptPath(DOC . STD . '/www/' . $spath . $mpath . '/' . Indi::ini()->design);

                // Add general script path
                $view->addScriptPath(DOC . STD . '/www/' . $spath . $mpath);
            }

            // Add script paths for major core part and for front core part
            $view->addScriptPath(DOC . STD . '/coref/' . $spath . $mpath);
            $view->addScriptPath(DOC . STD . '/core/' . $spath . $mpath);

            // If certain project has 'library' dir
            if (is_dir(DOC . STD . '/www/library')) {

                // Add design-specific helper path
                if (Indi::ini()->design) $view->addHelperPath(DOC . STD . '/www/library/Project/View/Helper' . $mhpp . '/' . Indi::ini()->design, 'Project_View_Helper_'. $mhcp);

                // Add default helper path
                $view->addHelperPath(DOC . STD . '/www/library/Project/View/Helper' . $mhpp, 'Project_View_Helper_'. $mhcp);
            }

            // Add helper paths for major core part and for front core part
            $view->addHelperPath(DOC . STD . '/coref/library/Indi/View/Helper' . $mhpp, 'Indi_View_Helper_' . $mhcp);
            $view->addHelperPath(DOC . STD . '/core/library/Indi/View/Helper' . $mhpp, 'Indi_View_Helper_' . $mhcp);
        }

        // Put view object into the registry
		Indi::registry('view', $view);
	}

    /**
     * Dispatch the request
     */
    public function dispatch($args = array()) {

        // Setup the Content-Type header
        header('Content-Type: text/html; charset=' . $this->encoding);

        // Do the pre-dispatch maintenance
        $this->preDispatch();

        // Here we provide an ability for operations, required for
        // a certain item to be performed, instead actual action call
        if (preg_match('/^[A-Za-z_][A-Za-z_0-9]*$/', Indi::uri()->consider)) {

            // Call the function, that will do these operations
            $this->{Indi::uri()->action . 'ActionI' . ucfirst(Indi::uri()->consider)}(Indi::post());

            // Force to stop the execution. Usually, execution won't reach this line, as in most cases
            // jflush() is called earlier than here at this line
            jflush(false, 'No consider');
        }

        // Here we provide an ability for a combo options data to be fetched instead of actual action call
        if (preg_match('/^[A-Za-z_][A-Za-z_0-9]*$/', Indi::uri()->odata)) {

            // Fetch the combo options data
            $this->{Indi::uri()->action . 'ActionOdata'}(Indi::uri()->odata, Indi::post());

            // Force to stop the execution. Usually, execution won't reach this line, as in most cases
            // execution is stopped earlier than here at this line
            jflush(false, 'No odata');
        }

        // If $_GET's 'jump' param is given - skip all below operations
        if (Indi::get('jump')) return;

        // Call the desired action method
        $this->call(Indi::uri()->action, $args);

        // Do the post-dispatch maintenance
        $this->postDispatch();
    }

    /**
     * Call the desired action method
     */
    public function call($action, $args = array()) {
        call_user_func_array(array($this, $action . 'Action'), $args);
    }

    /**
     * Empty method
     */
    public function preDispatch() {

    }

    /**
     * Empty method
     */
    public function postDispatch() {

    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (preg_match('/^row(set|)$/i', $property)) return Indi::trail()->$property;
    }

    /**
     * Setter
     *
     * @param string $property
     * @param $value
     */
    public function __set($property, $value) {
        if (preg_match('/^row(set|)$/i', $property)) Indi::trail()->$property = $value;
    }

    /**
     * Checker
     *
     * @param string $property
     * @return bool
     */
    public function __isset($property) {
        if (preg_match('/^row(set|)$/i', $property)) return isset(Indi::trail()->$property);
    }

    /**
     * Does nothing. Declared for possibility to adjust primary WHERE clause
     *
     * @param $where
     * @return mixed
     */
    public function adjustPrimaryWHERE($where) {
        return $where;
    }

    /**
     * Builds the ORDER clause
     *
     * todo: Сделать для variable entity и storeRelationАbility=many
     *
     * @param $finalWHERE
     * @param string $json
     * @return null|string
     */
    public function finalORDER($finalWHERE, $json = '') {

        // If no sorting params provided - ORDER clause won't be built
        if (!$json || $json == '[]') return null;

        // Array for ORDER clauses
        $orderA = array();

        // Decode json
        $jsonA = json_decode($json, 1);

        // If json decode failed - return null
        if (!is_array($jsonA)) return null;

        // Foreach level or sorting, detected within json
        foreach ($jsonA as $jsonI) {

            // Extract column name and direction from json param
            //list($column, $direction) = array_values($jsonI);
            $column = $jsonI['property']; $direction = $jsonI['direction'];

            // If no sorting is needed - skip current and jump to next order clause candidate
            if (!$column) continue;

            // Find a field, that column is linked to
            foreach (Indi::trail()->fields as $fieldR) if ($fieldR->alias == $column) break;

            // If no direction - set as ASC by default
            if (!preg_match('/^(ASC|DESC)$/', $direction)) $direction = 'ASC';

            // If there is no field with such a name
            if ($fieldR->alias !== $column) {

                // If column's name is 'id' create new item in $orderA array
                if ($column == 'id') $orderA[] = '`' . $column . '` ' . $direction;

                // Continue
                continue;
            }

            //
            if (strlen($order = $fieldR->order($direction, $finalWHERE))) $orderA[] = $order;
        }

        // Return
        return $orderA;
    }

    /**
     * Builds and returns a stack of WHERE clauses, that are representing grid's filters usage
     *
     * @param $FROM string table/model/entity name. Current model will be used by default
     * @param $search string Special formatted string containing filters values like
     *                       [{"field1":"val1"}, {"field2":"val2"}] . If not given - Indi::get()->search will be
     *                       used by default
     * @return array
     */
    public function filtersWHERE($FROM = '', $search = '') {

        // Setup model, that should have fields, mentioned as filtering params names
        $model = $FROM ? Indi::model($FROM) : Indi::trail()->model;

        // Defined an array for collecting data, that may be used in the process of building an excel spreadsheet
        $excelA = array();

        // Use Indi::get()->search if $search arg is not given
        $search = $search ?: Indi::get()->search;

        // Clauses stack
        $where = array();

        // If we have no 'search' param in query string, there is nothing to do here
        if ($search) {

            // Decode 'search' param from json to an associative array
            $search = json_decode($search, true);

            // Foreach passed filter pair (alias => value)
            foreach ($search as $searchOnField) {

                // Get the filter's alias (same as entity field's and db table column's name) and value
                $filterSearchFieldAlias = key($searchOnField);
                $filterSearchFieldValue = current($searchOnField);

                // Check $filterSearchFieldAlias
                if (!preg_match('/^[a-zA-Z\-0-9_]+$/', $filterSearchFieldAlias)) continue;
                
                // Get a field row object, that is related to current filter field alias. We need to do it because there
                // can be a case then filter field alias can be not the same as any field's alias - if filter is working
                // in range-mode. This can only happen for filters, that are linked to fields, that have column types:
                // DATE, INT (- just INT, no foreign keys, only simple numbers) and DATETIME. Actally there is one more
                // column type VARCHAR(10) that is used for color fields (xxx#rrggbb, with xxx - hue value of color), but
                // this does not matter, as color type of filters does not affect passed filter alias, so it's the same
                // as field alias and corresponding db table column name
                $found = null;
                foreach ($model->fields() as $fieldR)
                    if ($fieldR->alias == preg_replace('/-(lte|gte)$/','',$filterSearchFieldAlias))
                        $found = $fieldR;

                // Pick the current filter field title to $excelA
                if (array_key_exists($found->alias, $excelA) == false) {

                    // Get filter `alt` property
                    if (Indi::trail()->filters instanceof Indi_Db_Table_Rowset)
                        $alt = Indi::trail()->filters->select($found->id, 'fieldId')->current()->alt;

                    // Set excel filter mention title
                    $excelA[$found->alias] = array('title' => $alt ? $alt : $found->title);
                }

                // If field is not storing foreign keys
                if ($found->storeRelationAbility == 'none') {

                    // If $found field's control element is 'Color'
                    if ($found->elementId == 11) {

                        // Get the hue range borders
                        list($hueFrom, $hueTo) = $filterSearchFieldValue;

                        // Check $hueFrom and $hueTo
                        if (!Indi::rexm('int11', $hueFrom) || !Indi::rexm('int11', $hueTo) || $hueFrom < 0 || $hueTo > 360)  continue;
                        
                        // Build a WHERE clause for that hue range borders. If $hueTo > $hueFrom, use BETWEEN clause,
                        // else if $hueTo < $hueFrom, use '>=' and '<=' clauses, or else if $hueTo = $hueFrom, use '='
                        // clause
                        if ($hueTo > $hueFrom) {
                            $where[$found->alias] = 'SUBSTRING(`' . $filterSearchFieldAlias . '`, 1, 3) BETWEEN "' . str_pad($hueFrom, 3, '0', STR_PAD_LEFT) . '" AND "' . str_pad($hueTo, 3, '0', STR_PAD_LEFT) . '"';
                        } else if ($hueTo < $hueFrom) {
                            $where[$found->alias] = '(SUBSTRING(`' . $filterSearchFieldAlias . '`, 1, 3) >= "' . str_pad($hueFrom, 3, '0', STR_PAD_LEFT) . '" OR SUBSTRING(`' . $filterSearchFieldAlias . '`, 1, 3) <= "' . str_pad($hueTo, 3, '0', STR_PAD_LEFT) . '")';
                        } else {
                            $where[$found->alias] = 'SUBSTRING(`' . $filterSearchFieldAlias . '`, 1, 3) = "' . str_pad($hueFrom, 3, '0', STR_PAD_LEFT) . '"';
                        }

                        // Pick the current filter value and field type to $excelA
                        $excelA[$found->alias]['type'] = 'color';
                        $excelA[$found->alias]['value'] = array($hueFrom, $hueTo);
                        $excelA[$found->alias]['offset'] = $searchOnField['_xlsLabelWidth'];

                    // Else if $found field's control element is 'Check' or 'Combo', we use '=' clause
                    } else if ($found->elementId == 9 || $found->elementId == 23) {

                        // Build WHERE clause for current field
                        $where[$found->alias] = Indi::db()->sql('`' . $filterSearchFieldAlias . '` = :s', $filterSearchFieldValue);

                        // Pick the current filter value to $excelA
                        $excelA[$found->alias]['value'] = $filterSearchFieldValue ? I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES : I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO;

                    // Else if $found field's control element is 'String', we use 'LIKE "%xxx%"' clause
                    } else if ($found->elementId == 1) {

                        // Build WHERE clause for current field
                        $where[$found->alias] = Indi::db()->sql('`' . $filterSearchFieldAlias . '` LIKE :s', '%' . $filterSearchFieldValue . '%');

                        // Pick the current filter value to $excelA
                        $excelA[$found->alias]['value'] = $filterSearchFieldValue;

                    // Else if $found field's alias is 'spaceSince'
                    } else if ($found->alias == 'spaceSince' && preg_match('/^spaceSince-(lte|gte)$/', $filterSearchFieldAlias, $m)) {

                        // Remember current bound. We need both bounds.
                        if (Indi::rex('date', $filterSearchFieldValue)) {
                            if ($m[1] == 'gte') $filterSearchFieldValue .=' 00:00:00';
                            else $filterSearchFieldValue = date('Y-m-d H:i:s', strtotime($filterSearchFieldValue) + 86400);
                            $where['spaceSince'][$m[1]] = $filterSearchFieldValue;
                        }

                        // If we now have both bounds - setup WHERE clause using special Indi_Schedule::where($since, $until) method
                        if (count($where['spaceSince']) == 2) $where['spaceSince']
                            = Indi_Schedule::where($where['spaceSince']['gte'], $where['spaceSince']['lte']);

                        // Pick the current filter value and field type to $excelA
                        $excelA[$found->alias]['type'] = 'date';
                        $excelA[$found->alias]['value'][$m[1]] = $filterSearchFieldValue;

                    // Else if $found field's control element are 'Number', 'Date', 'Datetime', 'Price' or 'Decimal143'
                    } else if (preg_match('/^(18|12|19|24|25)$/', $found->elementId)) {

                        // Detect the type of filter value - bottom or top, in 'range' terms mean
                        // greater-or-equal or less-or-equal
                        if (preg_match('/([a-zA-Z0-9_\-]+)-(lte|gte)$/', $filterSearchFieldAlias, $matches)) {

                            // Currently both filter for Date and Datetime fields looks exactly the same, despite on Datetime
                            // fields have time in addition to date. But here, in php-code we need to handle these filter types
                            // with small differences, that are 1) trim up to 10 characters 2) ' 00:00:00' should be appended
                            // to filter that is associated with Datetime field to provide a valid WHERE clause
                            if (preg_match('/^12|19$/', $found->elementId))
                                $filterSearchFieldValue = substr($filterSearchFieldValue, 0, 10);

                            // Pick the current filter value and field type to $excelA
                            $excelA[$found->alias]['type'] = in($found->elementId, '18,24,25') ? 'number' : 'date';
                            $excelA[$found->alias]['value'][$matches[2]] = $filterSearchFieldValue;

                            // If we deal with DATETIME column, append a time postfix for a proper comparison
                            if ($found->elementId == 19)
                                $filterSearchFieldValue .= preg_match('/gte$/', $filterSearchFieldAlias) ? ' 00:00:00' : ' 23:59:59';

                            // Use a '>=' or '<=' clause, according to specified range border's type
                            $where[$found->alias][$matches[2]] = Indi::db()->sql('`' . $matches[1] . '` ' . ($matches[2] == 'gte' ? '>' : '<') . '= :s', $filterSearchFieldValue);

                        // Else
                        } else $where[$found->alias] = Indi::db()->sql('`' . $found->alias . '` = :s', $filterSearchFieldValue);

                    // If $found field's column type is TEXT ( - control elements 'Text' and 'HTML')
                    } else if ($found->columnTypeId == 4) {

                        // Use 'MATCH AGAINST' clause
                        $where[$found->alias] = Indi::db()->sql('MATCH(`' . $filterSearchFieldAlias . '`) AGAINST(:s IN BOOLEAN MODE)', $filterSearchFieldValue . '*');

                        // Pick the current filter value and field type to $excelA
                        $excelA[$found->alias]['value'] = $filterSearchFieldValue;
                    }

                // Else if $found field is able to store only one foreign key, use '=' clause
                } else if ($found->storeRelationAbility == 'one') {

                    // Set $any as `false`
                    $any = false;

                    // Try to find filter
                    if (Indi::trail()->filters instanceof Indi_Db_Table_Rowset) {

                        // Get filter row
                        $filterR = Indi::trail()->filters->gb($found->id, 'fieldId');

                        // If filter is multiple (desipite field is singe) set up $mode as `any`
                        if ($filterR->any()) $any = true;
                    }

                    // Set up WHERE clause according to value of $any flag
                    $where[$found->alias] = Indi::db()->sql($any
                        ? 'FIND_IN_SET(`' . $filterSearchFieldAlias . '`, :s)'
                        : '`' . $filterSearchFieldAlias . '` = :s', $filterSearchFieldValue);

                    // Pick the current filter value and fieldId (if foreign table name is 'enumset')
                    // or foreign table name, to $excelA
                    $excelA[$found->alias]['value'] = $filterSearchFieldValue;
                    if ($found->relation == 6) {
                        $excelA[$found->alias]['fieldId'] = $found->id;
                    } else {
                        $excelA[$found->alias]['table'] = $found->relation;
                    }

                // Else if $found field is able to store many foreign keys, use FIND_IN_SET clause
                } else if ($found->storeRelationAbility == 'many') {

                    // Declare array for FIND_IN_SET clauses
                    $fisA = array();

                    // Set $any as `false`
                    $any = false;

                    // Try to find filter
                    if (Indi::trail()->filters instanceof Indi_Db_Table_Rowset) {

                        // Get filter row
                        $filterR = Indi::trail()->filters->gb($found->id, 'fieldId');

                        // If filter should search any match rather than all matches
                        if ($filterR->any()) $any = true;
                    }

                    // If $filterSearchFieldValue is a non-empty string, convert it to array
                    if (is_string($filterSearchFieldValue) && strlen($filterSearchFieldValue))
                        $filterSearchFieldValue = explode(',', $filterSearchFieldValue);

                    // Fill that array
                    foreach ($filterSearchFieldValue as $filterSearchFieldValueItem)
                        $fisA[] = Indi::db()->sql('FIND_IN_SET(:s, `' . $filterSearchFieldAlias . '`)', $filterSearchFieldValueItem);

                    // Implode array of FIND_IN_SET clauses with AND, and enclose by round brackets
                    $where[$found->alias] = '(' . implode(' ' . ($any ? 'OR' : 'AND') . ' ', $fisA) . ')';

                    // Pick the current filter value and fieldId (if foreign table name is 'enumset')
                    // or foreign table name, to $excelA
                    $excelA[$found->alias]['value'] = $filterSearchFieldValue;
                    if ($found->relation == 6) {
                        $excelA[$found->alias]['fieldId'] = $found->id;
                    } else {
                        $excelA[$found->alias]['table'] = $found->relation;
                    }
                }
            }
        }

        // Setup filters usage information in $this->_excelA property
        $this->_excelA = $excelA;

        // Force $where array to be single-dimension
        foreach ($where as $filter => $clause) if (is_array($clause)) $where[$filter] = '(' . im($clause, ' AND ') . ')';

        // Return WHERE clause
        return $where;
    }

    /**
     * Prepare arguments for $this->_odata() function call, and call that function for fetching filter-combo options data.
     * This function handles all cases, related to combo options data fetch, such as
     * page-by-page appending/prepending, combo-keyword lookup, fetch satellited data (for example fetch cities for second
     * combo when country was selected in first combo), and all this for filter combos
     *
     * @param string $for A name of field, that combo data should be fetched for
     * @param array $post Request params, required to make a proper fetch (page number, keyword, value of satellite)
     */
    public function indexActionOdata($for, $post) {

        // Get the field
        $field = Indi::trail()->model->fields($for);

        // Get filter
        if (Indi::trail()->filters) $filter = Indi::trail()->filters->select($field->id, 'fieldId')->at(0);

        // Declare WHERE array
        $where = array();

        // Append statiс WHERE, defined for filter
        if (strlen($filter->filter)) $where[] = $filter->filter;

        // Append special part to WHERE clause, responsible for filter combo to do not contain inconsistent options
        if ($filter->consistence && $relation = $field->relation) {

            // Get table name
            $tbl = Indi::trail()->model->table();

            // Setup a shortcut for scope WHERE
            $sw = Indi::trail()->scope->WHERE;

            // Append part of WHERE clause, that will be involved in the process of fetching filter combo data
            $where[] = '`id` IN (' . (($in = Indi::db()->query('
                SELECT DISTINCT `'. $for . '` FROM `' . $tbl .'`' .  (strlen($sw) ? 'WHERE ' . $sw : '')
            )->fetchAll(PDO::FETCH_COLUMN)) ? trim(implode(',', $in), ',') : 0) . ')';
        }

        // Setup a row
        $this->row = Indi::trail()->filtersSharedRow;

        // Prepare and flush json-encoded combo options data
        $this->_odata($for, $post, $field, $where);
    }

    /**
     * Prepare arguments for $this->_odata() function call, and call that function for fetching combo options data.
     * This function handles all cases, related to combo options data fetch, such as
     * page-by-page appending/prepending, combo-keyword lookup, fetch satellited data (for example fetch cities for second
     * combo when country was selected in first combo), and all this for form and sibling combos
     *
     * @param string $for A name of field, that combo data should be fetched for
     * @param array $post Request params, required to make a proper fetch (page number, keyword, value of satellite)
     */
    public function formActionOdata($for, $post) {

        // If options data is for sibling combo
        if ($for == 'sibling') {

            // Create pseudo field for sibling combo
            $field = Indi_View_Helper_Admin_SiblingCombo::createPseudoFieldR(
                $for, Indi::trail()->section->entityId, Indi::trail()->scope->WHERE);
            $this->row->$for = Indi::uri()->id;
            $order = is_array(Indi::trail()->scope->ORDER) ? end(Indi::trail()->scope->ORDER) : Indi::trail()->scope->ORDER;
            $dir = array_pop(explode(' ', $order));
            $order = trim(preg_replace('/ASC|DESC/', '', $order), ' `');
            if (preg_match('/\(/', $order)) $offset = Indi::uri()->aix - 1;

        // Else if options data is for combo, associated with a existing form field - pick that field
        } else $field = Indi::trail()->model->fields($for);

        // If field having $for as it's `alias` was not found in existing fields, try to finв it within pseudo fields
        if (!$field) $field = Indi::trail()->pseudoFields->field($for);

        // Do some things, custom for certain field, before odata fetch
        if (($method = 'formActionOdata' . ucfirst(Indi::uri()->odata)) && method_exists($this, $method))
            $this->$method(json_decode(Indi::post('consider'), true));

        // Prepare and flush json-encoded combo options data
        $this->_odata($for, $post, $field, null, $order, $dir, $offset);
    }

    /**
     * Fetch combo options data. This function handles all cases, related to combo options data fetch, such as
     * page-by-page appending/prepending, combo-keyword lookup, fetch satellited data (for example fetch cities for second
     * combo when country was selected in first combo), and all this for form, filter and sibling combos
     *
     * @param string $for A name of field, that combo data should be fetched for
     * @param array $post Request params, required to make a proper fetch (page number, keyword, value of satellite)
     * @param Field_Row $field
     * @param string $where
     * @param string $order
     * @param string $dir
     * @param string $offset
     */
    protected function _odata($for, $post, $field, $where, $order = null, $dir = null, $offset = null, $subTplData = null) {

        // If field was not found neither within existing field, nor within pseudo fields
        if (!$field instanceof Field_Row) jflush(false, sprintf(I_COMBO_ODATA_FIELD404, $for));

        // Set $noSatellite flag
        $noSatellite = false; if (!$post->satellite && $field->param('allowZeroSatellite')) $noSatellite = true;

        // If $_POST['selected'] is given, assume combo-UI is trying to retrieve data for an entry,
        // not yet represented in the combo's store, because of, for example, store contains only first
        // 100 entries, and does not contain 101st. So, in this case, we fetch combo data as if $_POST['selected']
        // would be a currently selected value of $this->row->$for
        if ($post->selected && $field->relation && $field->relation != 6 && $field->storeRelationAbility == 'one') {

            // Check $_POST['selected']
            jcheck(array(
                'selected' => array(
                    'rex' => 'int11',
                    'key' => $field->relation
                )
            ), $post);

            // Assign
            $this->row->$for = $post->selected;
        }

        // Get combo data rowset
        $comboDataRs = $post->keyword
            ? $this->row->getComboData(
                $for, $post->page, $post->keyword, true, $post->satellite, $where, $noSatellite, $field, $order, $dir)
            : $this->row->getComboData(
                $for, $post->page, $this->row->$for, false, $post->satellite, $where, $noSatellite, $field, $order, $dir, $offset);


        // Prepare combo options data
        $comboDataA = $comboDataRs->toComboData($field->params);

        $options = $comboDataA['options'];
        $titleMaxLength = $comboDataA['titleMaxLength'];

        $options = array('ids' => array_keys($options), 'data' => array_values($options));

        // Setup number of found rows
        if ($comboDataRs->found()) $options['found'] = $comboDataRs->found();

        // Setup tree flag
        if ($comboDataRs->model()->treeColumn()) $options['tree'] = true;

        // Setup groups for options
        if ($comboDataRs->optgroup) $options['optgroup'] = $comboDataRs->optgroup;

        // Setup additional attributes names list
        if ($comboDataRs->optionAttrs) $options['attrs'] = $comboDataRs->optionAttrs;

        // Setup `titleMaxLength` property
        $options['titleMaxLength'] = $titleMaxLength;

        // Setup `enumset` property
        $options['enumset'] = $field->relation == 6;

        // Flush
        jflush(true, $options);
    }

    /**
     * Calls the parent class's same function, passing same arguments.
     * This is similar to ExtJs's callParent() function, except that agruments are
     * FORCED to be passed (in extjs, if you call this.callParent() - no arguments would be passed,
     * unless you use this.callParent(arguments) expression instead)
     */
    public function callParent() {

        // Get call info from backtrace
        $call = array_pop(array_slice(debug_backtrace(), 1, 1));

        // Make the call
        return call_user_func_array(get_parent_class($call['class']) . '::' . $call['function'], func_num_args() ? func_get_args() : $call['args']);
    }

    /**
     * Provide default index action
     */
    public function indexAction() {

        // If data should be got as json or excel
        if (Indi::uri('format') || (!$this->_isRowsetSeparate && Indi::trail(true))) {

            // Adjust rowset, before using it as a basement of grid data
            $this->adjustGridDataRowset();

            // Build the grid data, based on current rowset
            $data = $this->rowset->toGridData(Indi::trail()->gridFields ? Indi::trail()->gridFields->column('alias') : array());

            // Adjust grid data
            $this->adjustGridData($data);

            // Else if data is gonna be used in the excel spreadsheet building process, pass it to a special function
            if (in(Indi::uri('format'), 'excel,pdf')) $this->export($data, Indi::uri('format'));

            // If data is needed as json for extjs grid store - we convert $data to json with a proper format and flush it
            else {

                // Get scope
                $scope = Indi::trail()->scope->toArray();

                // Unset tabs definitions from json-encoded scope data, as we'd already got it previously
                unset($scope['actionrowset']['south']['tabs']);

                // Setup basic data
                $pageData = array(
                    'totalCount' => $this->rowset->found(),
                    'blocks' => $data,
                    'scope' => $scope
                );

                // Append summary data
                if ($summary = $this->rowsetSummary()) $pageData['summary'] = $summary;

                // Provide combo filters consistence
                foreach (Indi::trail()->filters ?: array() as $filter)
                    if ($filter->consistence && ($filter->foreign('fieldId')->relation || $filter->foreign('fieldId')->columnTypeId == 12)) {
                        $alias = $filter->foreign('fieldId')->alias;
                        Indi::view()->filterCombo($filter, 'extjs');
                        $pageData['filter'][$alias] = array_pop(Indi::trail()->filtersSharedRow->view($alias));
                    }

                // Adjust json export
                $this->adjustJsonExport($pageData);

                // If uri's 'format' param is specified, and it is 'json' - flush json-encoded $pageData
                if (Indi::uri('format') == 'json') jflush(true, $pageData);

                // Else assign that data into scope's `pageData` prop
                else Indi::trail()->scope->pageData = $pageData;
            }
        }
    }

    /**
     * Build and return a final WHERE clause, that will be passed to fetchAll() method, for fetching section's main
     * rowset. Function use a $primaryWHERE, merge it with $this->filtersWHERE() and append to it $this->keywordWHERE()
     * if return values of these function are not null
     *
     * @param string|array $primaryWHERE
     * @param string|array $customWHERE
     * @param bool $merge
     * @return null|string|array
     */
    public function finalWHERE($primaryWHERE, $customWHERE = null, $merge = true) {

        // Empty array yet
        $finalWHERE = array();

        // If there was a primaryHash passed instead of $primaryWHERE param - then we extract all scope params from
        if (is_string($primaryWHERE) && preg_match('/^[0-9a-zA-Z]{10}$/', $primaryWHERE)) {

            // Prepare $primaryWHERE
            $primaryWHERE = Indi::trail()->scope->primary;

            // Prepare search data for $this->filtersWHERE()
            Indi::get()->search = Indi::trail()->scope->filters;

            // Prepare search data for $this->keywordWHERE()
            Indi::get()->keyword = urlencode(Indi::trail()->scope->keyword);

            // Prepare sort params for $this->finalORDER()
            Indi::get()->sort = Indi::trail()->scope->order;
        }

        // Push primary part
        if ($primaryWHERE || $primaryWHERE == '0') $finalWHERE['primary'] = $primaryWHERE;

        // Get a WHERE stack of clauses, related to filters search and push it into $finalWHERE under 'filters' key
        if (count($filtersWHERE = $this->filtersWHERE())) $finalWHERE['filters'] = $filtersWHERE;

        // Get a WHERE clause, related to keyword search and push it into $finalWHERE under 'keyword' key
        if ($keywordWHERE = $this->keywordWHERE()) $finalWHERE['keyword'] = $keywordWHERE;

        // Append custom WHERE
        if ($customWHERE || $customWHERE == '0') $finalWHERE['custom'] = $customWHERE;

        // If WHERE clause should be a string
        if ($merge) {

            // Force $finalWHERE to be single-dimension array
            foreach ($finalWHERE as $part => $where) if (is_array($where)) $finalWHERE[$part] = im($where, ' AND ');

            // Stringify
            $finalWHERE = implode(' AND ', $finalWHERE);
        }

        // Return
        return $finalWHERE;
    }

    /**
     * Builds a SQL string from an array of clauses, imploded with OR. String will be enclosed by round brackets, e.g.
     * '(`column1` LIKE "%keyword%" OR `column2` LIKE "%keyword%" OR `columnN` LIKE "%keyword%")'. Result string will
     * not contain search clauses for columns, that are involved in building of set of another kind of WHERE clauses -
     * related to grid filters
     *
     * @param $keyword
     * @return string
     */
    public function keywordWHERE($keyword = '') {

        // If $keyword param is not passed we pick Indi::get()->keyword as $keyword
        if (strlen($keyword) == 0) $keyword = Indi::get()->keyword;

        // Exclusions array - we will be not trying to find a keyword in columns, that will be involved in search process
        // in $this->filtersWHERE() function, so one column can be used to find either selected-grid-filter-value or keyword,
        // not both at the same time
        $exclude = array_keys(Indi::obar());

        // Use keywordWHERE() method call on fields rowset to obtain a valid WHERE clause for the given keyword
        return Indi::trail()->{Indi::trail()->gridFields ? 'gridFields' : 'fields'}->keywordWHERE($keyword, $exclude);
    }

    /**
     * Adjust rowset, before using it as a basement of grid data. This function is empty here, but may be useful in
     * some situations
     */
    function adjustGridDataRowset() {

    }

    /**
     * Adjust data, that was already prepared for usage in grid. This function is for ability to post-adjustments
     *
     * @param array $data This param is passed by reference
     */
    function adjustGridData(&$data) {

    }

    /**
     * Empty function. To be redeclared in child classes in case of a need for an json-export adjustments
     *
     * @param $json
     */
    public function adjustJsonExport(&$json) {

    }

    /**
     * Append the field, identified by $alias, to the list of disabled fields
     *
     * @param string $alias Field name/alias
     * @param bool $displayInForm Whether or not field should be totally disabled, or disabled but however visible
     * @param string $defaultValue The default value for the disabled field
     */
    public function appendDisabledField($alias, $displayInForm = false, $defaultValue = '') {

        // Append
        foreach(ar($alias) as $a) Indi::trail()->disabledFields->append(array(
            'id' => 0,
            'sectionId' => Indi::trail()->section->id,
            'fieldId' => Indi::trail()->model->fields($a)->id,
            'defaultValue' => $defaultValue,
            'displayInForm' => $displayInForm ? 1 : 0,
        ));
    }

    /**
     * Exclude field/fields from the list of disabled fields by their aliases/names
     *
     * @param string $fields Comma-separated list of fields's aliases to be excluded from the list of disabled fields
     */
    public function excludeDisabledFields($fields) {

        // Convert $fields argument into an array
        $fieldA_alias = ar($fields);

        // Get the ids
        $fieldA_id = Indi::trail()->fields->select($fieldA_alias, 'alias')->column('id');

        // Exclude
        Indi::trail()->disabledFields->exclude($fieldA_id, 'fieldId');
    }
    
    /**
     * This function is an injection that allows to adjust any trail items before their involvement
     */
    public function adjustTrail() {

    }

    /**
     * Include additional model's properties into response json, representing rowset data
     *
     * @param $propS string|array Comma-separated prop names (e.g. field aliases)
     */
    public function inclGridProp($propS) {

        // Get `field` instances rowset with value of `alias` prop, mentioned in $propS arg
        $fieldRs = Indi::trail()->model->fields(im(ar($propS)), 'rowset');

        // Merge existing grid fields with additional
        if (Indi::trail()->gridFields) Indi::trail()->gridFields->merge($fieldRs);

        // Return
        return $fieldRs;
    }

    /**
     * Prevent certain model's props from being included into response json, representing rowset data
     *
     * @param $propS string|array Comma-separated prop names (e.g. field aliases)
     * @return string Comma-separated list containing ids of excluded fields
     */
    public function exclGridProp($propS) {

        // If no gridFields object - return
        if (!Indi::trail()->gridFields) return '';

        // If ids of fields to be excluded
        $fieldIds = Indi::trail()->gridFields->select($propS, 'alias')->column('id', true);

        // Merge existing grid fields with additional
        Indi::trail()->gridFields->exclude($fieldIds);

        // Return ids of excluded fields
        return $fieldIds;
    }
}