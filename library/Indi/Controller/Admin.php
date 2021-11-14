<?php
class Indi_Controller_Admin extends Indi_Controller {

    /**
     * Flag for set up whether rowset data should be fetched ONLY
     * as a result of a separate http request
     *
     * @var bool
     */
    protected $_isRowsetSeparate = false;

    /**
     * Flag for set up whether nested tabs rowset data should be fetched ONLY
     * as a result of a separate http request
     * This flag is involved in cases when current action is a row-action,
     * but current section has nested sections, and first of them can be
     * displayed as a south-panel tab within main wrapper panel.
     * For example current section is 'Countries', and first among nested sections is 'Cities',
     * so if user is at some country's details screen, $_isNestedSeparate flag will be responsible
     * for whether or not 'Cities' south-panel tab will be build using response got by additional ajax request
     *
     * @var bool
     */
    protected $_isNestedSeparate = false;

    /**
     * Rowset, containing Indi_Db_Table_Row instances according to current selection.
     * Those instances are collected by `ids`, given by Indi::uri()->id and Indi::post()->others
     *
     * @var Indi_Db_Table_Rowset|array
     */
    public $selected = array();

    /**
     * Array of section ids, starting from current section and up to the top.
     *
     * @var array
     */
    private $_routeA = array();

    /**
     * Modes and views for typical actions. Can be adjusted with adjustActionCfg() method
     *
     * @var array
     */
    public $actionCfg = array(
        'mode' => array(
            'index' => 'rowset',
            'form' => 'row',
            'up' => 'row',
            'down' => 'row',
            'toggle' => 'row',
            'print' => 'row',
            'call' => 'row'
        ),
        'view' => array(
            'index' => 'grid',
            'form' => 'form',
            'print' => 'print',
            'call' => 'call'
        )
    );

    /**
     * If the purpose of current request is to build an excel spreadsheet - this variable will be used
     * for collecting filters usage information, and retrieving that information in the process of building
     * spreadsheet
     *
     * @var array
     */
    protected $_excelA = array();

    /**
     * Array for explanation messages to be shown in case if some action was denied by $this->deny('myAction', 'some reason');
     */
    protected $_deny = array();
    
    /**
     * Constructor
     */
    public function __construct() {

        // Prevent Fatal error plain msg from breaking json output,
        // This won't stop showing errors, as they are passed to json
        ini_set('display_errors', 0);

        // Call parent constructor
        parent::__construct();

        // Remove non-module-related script paths
        for ($i = 0; $i < 3; $i++) Indi::view()->popPaths('script');
    }

    /**
     * Init all general cms features
     */
    public function preDispatch() {

        // Perform authentication
        $this->auth();

        // Delete `realtime` entry having `type` = "context", if $_POST['destroy'] is given
        $this->deleteContextIfNeed();

        // Jump, if need
        $this->jump();

        // Adjust params of certain scope, if $_REQUEST['forScope'] param exists
        $this->adjustCertainScope();

        // If we are in some section, mean not in just '/admin/', but at least in '/admin/somesection/'
        if (Indi::trail(true) && Indi::trail()->model) {

            // Adjust trail
            $this->adjustTrail();

            // Edit ui, if need
            $this->uiedit();

            // If tileField defined for current section - change type of view
            if (t()->section->tileField) $this->actionCfg['view']['index'] = 'tile';

            // Adjust action mode and view config.
            $this->adjustActionCfg();

            // Setup view. This call will create an action-view object instance, especially for current trail item
            Indi::trail()->view();

            // If action is 'index'
            if (Indi::trail()->action->rowRequired == 'n') {

                // Set rowset mode
                $this->_isRowsetSeparate();

                // Get the primary WHERE clause
                $primaryWHERE = $this->primaryWHERE();

                // Set 'hash' scope param at least. Additionally, set scope info about primary hash and row index,
                // related to parent section, if these params are passed within the uri.
                $applyA = array('hash' => Indi::trail()->section->primaryHash);
                if (Indi::uri()->ph) $applyA['upperHash'] = Indi::uri()->ph;
                if (Indi::uri()->aix) $applyA['upperAix'] = Indi::uri()->aix;
                if (Indi::get()->stopAutosave) $applyA['toggledSave'] = false;
                if (Indi::get()->filter) $applyA['filters'] = $this->_filter2search();
                Indi::trail()->scope->apply($applyA);

                // If there was no 'format' param passed within the uri
                // we extract all fetch params from current scope
                if (!Indi::uri()->format && !$this->_isRowsetSeparate) {

                    // Prepare search data for $this->filtersWHERE()
                    Indi::get()->search = Indi::trail()->scope->filters == '[]'
                        ? Indi::trail()->jsonDefaultFilters()
                        : Indi::trail()->scope->filters;

                    // Prepare search data for $this->keywordWHERE()
                    Indi::get()->keyword = urlencode(Indi::trail()->scope->keyword);

                    // Prepare sort params for $this->finalORDER()
                    Indi::get()->sort = Indi::trail()->scope->order == '[]'
                        ? Indi::trail()->section->jsonSort()
                        : Indi::trail()->scope->order;

                    // Prepare sort params for $this->finalORDER()
                    Indi::get()->page = Indi::trail()->scope->page ? Indi::trail()->scope->page : 1;

                    // Prepare sort params for $this->finalORDER()
                    Indi::get()->limit = Indi::trail()->section->rowsOnPage;
                }

                // If a rowset should be fetched
                if (Indi::uri()->format || Indi::uri('action') != 'index' || !$this->_isRowsetSeparate || strlen(Indi::uri('single'))) {

                    // Get final WHERE clause, that will implode primaryWHERE, filterWHERE and keywordWHERE
                    $finalWHERE = $this->finalWHERE($primaryWHERE);

                    // If $_GET['group'] is an json-encoded object rather than array containing that object
                    if (json_decode(Indi::get()->group) instanceof stdClass)

                        // Prepend it to the list of sorters, to provide compatibility with ExtJS 6.7 behaviour,
                        // because ExtJS 4.1 auto-added grouping to the list of sorters but ExtJS 6.7 does not do that
                        Indi::get()->sort = preg_replace('~^\[~', '$0' . Indi::get()->group . ',', Indi::get()->sort);

                    // Get final ORDER clause, built regarding column name and sorting direction
                    $finalORDER = $this->finalORDER($finalWHERE, Indi::get()->sort);

                    // Build fetch method name
                    $fetchMethod = t()->model->treeColumn() && !$this->actionCfg['misc']['index']['ignoreTreeColumn']
                        ? 'fetchTree'
                        : 'fetchAll';

                    // Else if $_GET['required'] given
                    if ($required = (int) Indi::get('required')) {

                        // Fetch rowset consisting of only single row
                        $this->rowset = Indi::trail()->model->{$fetchMethod}('`id` = "' . $required . '"' . rif($finalWHERE, ' AND ($1)'));

                    // Else behave in an standard way
                    } else {

                        // Try to get rowset
                        do {

                            // Get the rowset, fetched using WHERE and ORDER clauses, and with built LIMIT clause,
                            // constructed with usage of Indi::get('limit') and Indi::get('page') params
                            $this->rowset = Indi::trail()->model->{$fetchMethod}($finalWHERE, $finalORDER,
                                $limit = Indi::uri()->format == 'json' || !Indi::uri()->format ? (int)Indi::get('limit') : null,
                                $page = Indi::uri()->format == 'json' || !Indi::uri()->format ? (int)Indi::get('page') : null,
                                null,
                                null,
                                ($fetchMethod == 'fetchAll') ?: null,
                                false,
                                $fetchMethod == 'fetchTree');

                            // If we're at 2nd or further page, but no results - try to detect new prev page
                            $shift = $limit && $page > 1 && !$this->rowset->count() && ($found = $this->rowset->found()) ? ceil($found / $limit) : 0;

                        // If we should try another page - do it
                        } while ($shift && (Indi::get()->page = $shift));

                        // Scope params
                        $scope = [
                            'primary' => $primaryWHERE, 'filters' => Indi::get()->search, 'keyword' => Indi::get()->keyword,
                            'order' => Indi::get()->sort, 'page' => Indi::get()->page, 'found' => $this->rowset->found(),
                            'WHERE' => $finalWHERE, 'ORDER' => $finalORDER, 'hash' => t()->section->primaryHash,
                            'pgupLast' => $this->rowset->pgupLast()->id, 'rowsOnPage' => t()->section->rowsOnPage,
                            'tree' => $fetchMethod == 'fetchTree',
                            'rowReqIfAffected' => t()->grid->select('y', 'rowReqIfAffected')->column('fieldId', true),
                            'sum' => t()->model->fields()->select(
                                t()->grid->select('sum', 'summaryType')->column('fieldId')
                            )->column('alias', ',')
                        ];

                        // Create context if need
                        $this->createContextIfNeed($scope);

                        /**
                         * Remember current rowset properties SQL - WHERE, ORDER, LIMIT clauses - to be able to apply these properties in cases:
                         *
                         * 1. We were in one section, made some search by filters and/or keyword, did sorting by some column, went to some page.
                         *    After that we went to another section, and then decide to return to the first. So, by this function, system will be
                         *    able to retrieve first section's params from $_SESSION, and display the grid in the exact same way as it was when
                         *    we had left it.
                         * 2. We were in one section, made some search by filters and/or keyword, did sorting by some column, went to some page and
                         *    clicked 'Details' on some row on that page, so the details form was displayed. So, this function is one of providing
                         *    an ability to navigate/jump to current row's siblings - go to prev/next rows. For example if we have a States section,
                         *    and we go to it, and types 'Ala' in fast-keyword-search field, so the corresponding results were displayed. Then, if we
                         *    go to 'Alabama' form screen, there will be buttons titled "Prev" and "Next". For example, by clicking Next, the Alaska's
                         *    editing form will be displayed instead of Alabama's. But there will be certainly no 'Ohio'.
                         *
                         * Actually, the only one param is stored as SQL-string - $primary param. This params includes all parts of WHERE clause, that
                         * was used to retrieve a current rowset, but except parts, related to filters/keyword search usage. There parts are
                         * stored as JSON-string, because it is much more easier to get last used filters's values from JSON rather than SQL.
                         *
                         * Function creates a hash-key (md5 from $primary param) to place the array of scope params under this key in $_SESSION
                         *
                         * $order param is stored in JSON format too, because it will be passed to Ext.grid
                         */
                        Indi::trail()->scope->apply($scope);
                    }
                }

            // Else if where is some another action
            } else {

                // Array of selected entries initially contain only $this->row->id
                if ($this->row->id) $idA[] = $this->row->id; else $idA = array();

                // If 'others' param exists in $_POST, and it's not empty
                if ($otherIdA = ar(Indi::post()->others)) {

                    // Unset invalid values
                    foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

                    // If $otherIdA array is still not empty append it's item into $idA array
                    if ($otherIdA) $idA = array_merge($idA, $otherIdA);
                }

                // Fetch selected rows
                $this->selected = $idA
                    ? Indi::trail()->model->fetchAll(array('`id` IN (' . im($idA) . ')', Indi::trail()->scope->WHERE), t()->scope->ORDER)
                    : Indi::trail()->model->createRowset();

                // Prepare scope params
                $applyA = array('hash' => Indi::uri()->ph, 'aix' => Indi::uri()->aix, 'lastIds' => $this->selected->column('id'));

                // Append 'toggledSave' scope-param
                if (Indi::get()->stopAutosave) $applyA['toggledSave'] = false;

                // Apply prepared scope params
                Indi::trail()->scope->apply($applyA);

                // If we are here for just check of row availability, do it
                if (Indi::uri()->check) jflush(true, $this->checkRowIsInScope());

                // Set last accessed rows
                $this->setScopeRow(false, null, $this->selected->column('id'));

            }
        }
    }

    /**
     * Edit ui
     */
    public function uiedit() {

        // If uri's 'uiedit' param is not given - return
        if (!$ui = Indi::uri()->uiedit) return;

        // If current user is not allowed to edit ui - flush failure
        if (Indi::admin()->uiedit != 'y') jflush(false);

        // Setup mapping that will help to prevent cross-section ui-editing
        $scope = array('grid' => 'grid', 'field' => 'fields', 'entity' => t()->model->id(),
            'section' => 'sections', 'section2action' => 'actions', 'search' => 'filters');

        // List of ui allowed for editing
        if (!in($ui, array_keys($scope))) jflush(false);

        // Validity check
        $_ = jcheck(array('indiId' => array('req' => true, 'rex' => 'int11', 'key' => $ui)), Indi::post());

        // Access check
        if (!in($_['indiId']->id, is_numeric($scope[$ui]) ? $scope[$ui] : t()->{$scope[$ui]}->column('id'))) jflush(false);

        // If we are editing grid column title, that was moved to tooltip due to icon
        if (isset(Indi::post()->tooltip)
            && $ui == 'grid'
            && Indi::post()->icon
            && !$_['indiId']->tooltip
            && !$_['indiId']->foreign($_['indiId']->further ? 'further' : 'fieldId')->tooltip) {

            // Force 'rename' logic to be executed instead of 'tooltip' logic
            Indi::post()->rename = Indi::post()->tooltip; unset(Indi::post()->tooltip);
        }

        // If $_POST['rename'] is set, e.g editing type is 'rename'
        if (isset(Indi::post()->rename)) {

            // Map
            $rename = array('grid' => 'alterTitle', 'field' => 'title', 'entity' => 'title', 'search' => 'alt');

            // If ctrl-key was hold and we were editing grid column title
            if ($ui == 'grid' && Indi::post()->ctrlKey) {

                // Update grid column's underlying field's title
                $_['indiId']
                    ->foreign($_['indiId']->further ? 'further' : 'fieldId')
                    ->assign(['title' => Indi::post()->rename])->save();

                // Force grid column's own title to be empty string,
                // so that underlying field's title will be used
                Indi::post()->rename = '';

            // Else if no ctrl-key was hold but new own title for grid column is going to be
            // the same as for it's underlying field - force own one to be empty
            } else if ($ui == 'grid'
                && $_['indiId']->foreign($_['indiId']->further ? 'further' : 'fieldId')->title == Indi::post()->rename)
                Indi::post()->rename = '';

            // Do rename
            $_['indiId']->assign([$rename[$ui] => Indi::post()->rename])->save();

        // If $_POST['rename'] is set, e.g editing type is 'rename'
        } else if (isset(Indi::post()->tooltip)) {

            // Map
            $rename = array('grid' => 'tooltip', 'section' => 'help', 'section2action' => 'rename', 'search' => 'tooltip');

            // If ctrl-key was hold and we were editing grid column tooltip
            if ($ui == 'grid' && Indi::post()->ctrlKey) {

                // Update grid column's underlying field's tooltip
                $_['indiId']
                    ->foreign($_['indiId']->further ? 'further' : 'fieldId')
                    ->assign(['tooltip' => Indi::post()->tooltip])->save();

                // Force grid column's own tooltip to be empty string,
                // so that underlying field's tooltip will be used
                Indi::post()->tooltip = '';

            // Else if no ctrl-key was hold but new own tooltip for grid column is going to be
            // the same as for it's underlying field - force own one to be empty
            } else if ($ui == 'grid'
                && $_['indiId']->foreign($_['indiId']->further ? 'further' : 'fieldId')->tooltip == Indi::post()->tooltip)
                Indi::post()->tooltip = '';

            // Do rename
            $_['indiId']->assign([$rename[$ui] => Indi::post()->tooltip])->save();

        // Else if editing type if not rename
        } else {

            // If ui is grid
            if ($ui == 'grid') {

                // Validity check
                $_ += jcheck(array('gridId' => array('req' => false, 'rex' => 'int11', 'key' => $ui)), Indi::post());

                // Get ids of grid columns that are currently accessible for current user
                $idA = t()->grid->select(': != 0')->column('id');

                // Access check
                if ($_['gridId'] && !in($_['gridId']->id, $idA)) jflush(false);

                // Do reattach
                $_['indiId']->assign(array('gridId' => Indi::post()->gridId, 'group' => Indi::post()->group))->save();

                // Validity check
                jcheck(array('index' => array('req' => false, 'rex' => 'int11')), Indi::post());

                // Get all grid columns having same `gridId` and same level
                $siblingIdA = Indi::db()->query('
                    SELECT `id` 
                    FROM `grid` 
                    WHERE 1 
                      AND `id` IN (' . im($idA) . ') 
                      AND `group` = "' . $_['indiId']->group . '" 
                      AND `gridId` = "' . $_['indiId']->gridId . '"
                    ORDER BY `move`
                ')->fetchAll(PDO::FETCH_COLUMN);

                // Do reorder
                if ($move = array_search($_['indiId']->id, $siblingIdA) - Indi::post()->index)
                    $_['indiId']->move($move, 'FIND_IN_SET(`id`, "' . im($siblingIdA) . '")');
            }
        }

        // Flush success
        jflush(true);
    }

    /**
     * Provide default downAction (Move down) for Admin Sections controllers
     *
     * @param string $where
     */
    public function downAction($where = null) {
        $this->move('down', $where);
    }

    /**
     * Provide default upAction (Move up) for Admin Sections controllers
     *
     * @param string $where
     */
    public function upAction($where = null) {
        $this->move('up', $where);
    }

    /**
     * Gets $within param and call $row->move() method with that param.
     * This was created just for use in in $controller->downAction() and $controller->upAction()
     *
     * @param $direction
     */
    public function move($direction) {

        // Get the scope of rows to move within
        $within = Indi::trail()->scope->WHERE;

        // Get shift, 1 by default
        $shift = (int) Indi::post('shift') ?: 1;

        // Declare array of ids of entries, that should be moved, and push main entry's id as first item
        $toBeMovedIdA[] = $this->row->id;

        // If 'others' param exists in $_POST, and it's not empty
        if ($otherIdA = ar(Indi::post()->others)) {

            // Unset unallowed values
            foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

            // If $otherIdA array is still not empty append it's item into $toBeMovedIdA array
            if ($otherIdA) $toBeMovedIdA = array_merge($toBeMovedIdA, $otherIdA);
        }

        // Fetch rows that should be moved
        $toBeMovedRs = Indi::trail()->model->fetchAll(
            array('`id` IN (' . im($toBeMovedIdA) . ')', Indi::trail()->scope->WHERE),
            '`move` ' . ($direction == 'up' ? 'ASC' : 'DESC')
        );

        // Get grouping field
        $groupBy = t()->section->groupBy ? t()->section->foreign('groupBy')->alias : '';

        // For each row
        for ($i = 0; $i < $shift; $i++)
            foreach ($toBeMovedRs as $toBeMovedR)
                if (!$toBeMovedR->move($direction, $within, $groupBy)) break;

        // Get the page of results, that we were at
        $wasPage = Indi::trail()->scope->page;

        // If current model has a tree-column, detect new row index by a special algorithm
        if (Indi::trail()->model->treeColumn()) Indi::uri()->aix = Indi::trail()->model->detectOffset(
            Indi::trail()->scope->WHERE, Indi::trail()->scope->ORDER, $toBeMovedRs->at(0)->id);

        // Else just shift current row index by inc/dec-rementing
        else Indi::uri()->aix += ($direction == 'up' ? -1 : 1) * $shift;

        // Apply new index
        $this->setScopeRow(false, null, $toBeMovedRs->column('id'));

        // Flush json response, containing new page index, in case if now row
        // index change is noticeable enough for rowset current page was shifted
        jflush(true, $wasPage != ($nowPage = Indi::trail()->scope->page) ? array('page' => $nowPage) : array());
    }

    /**
     * Provide delete action
     */
    public function deleteAction($flush = true) {
        
        // Demo mode
        Indi::demo();
        
        // Do pre delete maintenance
        $this->preDelete();

        // Declare array of ids of entries, that should be deleted, and push main entry's id as first item
        $toBeDeletedIdA[] = $this->row->id;

        // If 'others' param exists in $_POST, and it's not empty
        if ($otherIdA = ar(Indi::post()->others)) {

            // Unset unallowed values
            foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

            // If $otherIdA array is still not empty append it's item into $toBeMovedIdA array
            if ($otherIdA) $toBeDeletedIdA = array_merge($toBeDeletedIdA, $otherIdA);
        }

        // Fetch rows that should be moved
        $toBeDeletedRs = Indi::trail()->model->fetchAll(
            array('`id` IN (' . im($toBeDeletedIdA) . ')', Indi::trail()->scope->WHERE),
            Indi::trail()->scope->ORDER
        );

        // Array of deleted ids
        $deleted = [];

        // For each row
        foreach ($toBeDeletedRs as $toBeDeletedR) if ($deleted []= (int) $toBeDeletedR->delete()) {

            // Get the page of results, that we were at
            $wasPage = Indi::trail()->scope->page;

            // Decrement row index. This line, and one line below - are required to provide an ability to shift
            // the selection within rowset (grid, tile, etc) panel, after current row deletion
            Indi::uri()->aix -= 1;

            // Apply new index
            $this->setScopeRow();
        }

        // Do post delete maintenance
        $this->postDelete($deleted);

        // Prepare args for jflush() call, containing new page index, in case if now row
        // index change is noticeable enough for rowset current page was shifted
        $args = array(
            (bool) count($deleted),
            $wasPage != ($nowPage = Indi::trail()->scope->page) ? array('page' => $nowPage) : array()
        );

        // Flush or return json response, depending on $flush arg
        if ($flush) jflush($args[0], $args[1]); else return $args;
    }

    /**
     * Provide form action
     */
    public function formAction() {

    }

    /**
     * Set scope last accessed row
     *
     * @param bool $upper
     * @return null
     */
    public function setScopeRow($upper = false, Indi_Db_Table_Row $r = null, $lastIds = null) {

        // If no primary hash param passed within the uri - return
        if (!Indi::uri()->ph) return;

        // Get the current state of scope
        $original = $_SESSION['indi']['admin'][Indi::trail((int) $upper)->section->alias][Indi::uri()->ph];

        // If there is no current state yet - return
        if (!is_array($original)) return;

        // If $r argis not given, use $this->row
        $r = $r ?: $this->row;

        // If current action deals with row, that is not yet exists in database - return
        if (!$r->id) return;

        // Setup $modified array with 'aix' param as first item in. This array may be additionally fulfilled with
        // 'page' param, if passed 'aix' value is too large or too small to match initial results page number (this
        // mean that page number should be recalculated, so 'page' param will store recalculated page number). After
        // all necessary operations will be done - valued from this ($modified) array will replace existing values
        // in scope
        $modified = array('aix' => Indi::uri()->aix != 'undefined' ? Indi::uri()->aix : 1, 'lastIds' => ar($lastIds ?: $r->id));

        // Start and end indexes. We calculate them, because we should know, whether page number should be changed or no
        $start = ($original['page'] - 1) * Indi::trail((int) $upper)->section->rowsOnPage + 1;
        $end = ($original['page']) * Indi::trail((int) $upper)->section->rowsOnPage;

        // If last accessed row index is out of latest fetched rowset page bounds
        if ($modified['aix'] < $start || $modified['aix'] > $end) {

            // Calculate new page number, so when user will return to grid panel - an appropriate page
            // will be displayed, and last accessed row will be within it
            $modified['page'] = ceil($modified['aix']/Indi::trail((int) $upper)->section->rowsOnPage);
        }

        // Remember all scope params in $_SESSION under a hash
        Indi::trail((int) $upper)->scope->apply($modified);
    }

    /**
     * Builds an array of WHERE clauses, that will be imploded with AND, and used to determine a possible border limits
     * of scope of rows that section will be dealing with, and that are allowed for section to deal with them.
     * While building, it handles:
     *
     * 1. Childs-by-parent logic
     * 2. Alternates logic
     * 3. Custom additional adjustments (adjustments of WHERE clauses stack)
     *
     * After an array is built, function calcs a hash for imploded array, and assigns that hash as a temporary property
     * of current section, for hash to be accessbile within View object.
     *
     * primaryWHERE = parentWHERE + alternates handling, adjusted with adjustPrimaryWHERE()
     *
     * @return array
     */
    public function primaryWHERE() {

        // Define an array for WHERE clauses
        $where = array();

        // Append a childs-by-parent clause to primaryWHERE stack
        if ($parentWHERE = $this->parentWHERE()) $where['parent'] = $parentWHERE;

        // If a special section's primary filter was defined, add it to primary WHERE clauses stack
        if (strlen(Indi::trail()->section->compiled('filter'))) $where['static'] = '(' . Indi::trail()->section->compiled('filter') . ')';

        // Owner control. There can be a situation when some cms users are not stored in 'admin' db table - these users
        // called 'alternates'. Example: we have 'Experts' cms section (rows are fetched from 'expert' db table) and
        // public (mean non-cms) area logic allow any visitor to ask a questions to certain expert. So, if we want to
        // provide an ability for experts to answer these questions, and if we do not want to create a number of special
        // web-pages in public area that will handle all related things, we can provide a cms access for experts instead.
        // So we can create a 'Questions' section within cms, and if `question` table will contain `expertId` column
        // (it will contain - we will create it for that purpose) - the only questions, addressed to curently logged-in
        // expert will be available for view and answer.
        if ($alternateWHERE = $this->alternateWHERE()) $where['alternate'] =  $alternateWHERE;

        // Adjust primary WHERE clauses stack - apply some custom adjustments
        $where = $this->adjustPrimaryWHERE($where);

        // If uri has 'single' param - append it to primary WHERE clause
        if (strlen($single = Indi::uri('single')) && $single) $where['single'] = '`id` = "' . (int) Indi::uri('single') . '"';

        //
        if (Indi::uri('action') == 'index') {

            // Get a string version of WHERE stack
            $whereS = count($where) ? implode(' AND ', $where) : null;

            // Set a hash
            Indi::trail()->section->primaryHash = substr(md5($whereS), 0, 10);
        }

        // Return primary WHERE clauses stack
        return $where;
    }

    /**
     * Get part of WHERE clause, that provide owner control access restriction
     *
     * @param int $trailStepsUp
     * @return string
     */
    public function alternateWHERE($trailStepsUp = 0) {

        // If current admin is not alternate - return
        if (!Indi::admin()->alternate) return;

        // Get alternate-connector
        $af = Indi::admin()->alternate(Indi::trail($trailStepsUp)->model->table());

        // If one of model's fields relates to alternate
        if ($alternateFieldR = Indi::trail($trailStepsUp)->model->fields($af))
            return $alternateFieldR->original('storeRelationAbility') == 'many'
                ? 'FIND_IN_SET("' . Indi::admin()->id . '", `' . $af . '`)'
                : '`' . $af . '` = "' . Indi::admin()->id . '"';

        // Else if model itself is the same as alternate
        else if (Indi::trail($trailStepsUp)->model->table() == Indi::admin()->alternate)
            return '`id` = "' . Indi::admin()->id . '"';
    }

    /**
     * @return null
     */
    public function checkRowIsInScope(){

        // If row should be detected by it's index, within the current scope
        if (Indi::uri()->aix && !Indi::uri()->id) {

            // Get row by it's index
            $R = Indi::trail()->model->fetchRow(
                Indi::trail()->scope->WHERE,
                Indi::trail()->scope->ORDER,
                Indi::uri()->aix - 1
            );

            // Return basic data
            return array('id' => $R->id, 'title' => $R->title);

        // Else if row index should be found by it's id within the current scope
        } else if (Indi::uri()->id) {

            // Prepare checking-WHERE clause (for checking the row existence)
            $where = array('`id` = "' . Indi::uri()->id . '"');

            // Append current scope's WHERE clause to checking-WHERE clause
            if (strlen(Indi::trail()->scope->WHERE)) $where[] = Indi::trail()->scope->WHERE;

            // Check that row exists with such an id within the current scope
            $R = Indi::trail()->model->fetchRow($where);

            // If row index should be additionally detected
            if (Indi::post()->forceOffsetDetection && $R)

                // Get that offset and return it along with row title
                return array(
                    'aix' => Indi::trail()->model->detectOffset(
                        Indi::trail()->scope->WHERE,
                        Indi::trail()->scope->ORDER,
                        $R->id
                    ),
                    'title' => $R->title()
                );

            // Or just return the id, as an ensurement, that such row exists
            else return $R ? array('id' => $R->id, 'title' => $R->title()) : array();
        }
    }

    /**
     * Provide a download of a excel spreadsheet
     *
     * @param $data
     * @param $format
     */
    public function export($data, $format = 'excel'){

        /** Include path **/
        ini_set('include_path', ini_get('include_path').';../Classes/');

        /** PHPExcel */
        include 'PHPExcel.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator($_SESSION['admin']['title']);
        $objPHPExcel->getProperties()->setLastModifiedBy($_SESSION['admin']['title']);
        $objPHPExcel->getProperties()->setTitle(Indi::trail()->section->title);

        $font = 'Tahoma';

        // Set up $noBorder variable, containing style definition to be used as an argument while applyFromArray() calls
        // in cases then no borders should be displayed
        $_noBorder = array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'ffffff'));
        $noBorder = array('borders' => array('right' => $_noBorder, 'left' => $_noBorder, 'top' => $_noBorder));

        // Set active sheet by index
        $objPHPExcel->setActiveSheetIndex(0);

        // Get the columns, that need to be presented in a spreadsheet
        $_columnA = json_decode(Indi::get()->columns, true);

        // Build columns array, indexed by 'dataIndex' prop
        foreach ($_columnA as $_columnI) $columnA[$_columnI['dataIndex']] = $_columnI;

        // Adjust exported columns
        $this->adjustExportColumns($columnA);

        // Switch back to numeric indexes
        $columnA = array_values($columnA);

        // Get grouping info
        $group = json_decode(Indi::get()->group, true);
        if (!array_key_exists($group['property'], $data[0] ?: array())) $group = false;

        // Setup a row index, which data rows are starting from
        $currentRowIndex = 1;

        // Setup groups quantity
        $groupQty = $group ? count($this->rowset->column($group['property'], false, true)) : 0;

        // Calculate last row index
        $lastRowIndex =
            1 /* bread crumbs row*/ +
                1 /* row with total number of results found */ +
                (is_array($this->_excelA) && count($this->_excelA) ? count($this->_excelA) + 1 : 0) /* filters count */ +
                (bool) (Indi::get()->keyword || (is_array($this->_excelA) && count($this->_excelA) > 1)) +
                1 /* data header row */+
                count($data) + /* data rows*/
                (Indi::get()->summary ? 1 : 0) + /* summary row*/
                $groupQty;

        // Set default row height
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15.75);

        // Apply general styles for the whole spreadsheet
        foreach ($columnA as $n => $columnI) {

            // Get column letter
            $columnL = PHPExcel_Cell::stringFromColumnIndex($n);

            // Apply styles for all rows within current column (font and alignment)
            $objPHPExcel->getActiveSheet()
                ->getStyle($columnL . '1:' . $columnL . $lastRowIndex)
                ->applyFromArray(array(
                    'font' => array(
                        'size' => 8,
                        'name' => $font,
                        'color' => array(
                            'rgb' => '04408C'
                        )
                    ),
                    'alignment' => array(
                        'vertical' => 'center'
                    )
                )
            );
        }

        // Capture last column letter(s)
        $lastColumnLetter = $columnL;

        // Merge all cell at first row, as bread crumbs will be placed here
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $lastColumnLetter . '1');

        // Write bread crumbs, where current spreadsheet was got from
        $crumbA = Indi::trail(true)->toString(false);

        // Defined a PHPExcel_RichText object
        $objRichText = new PHPExcel_RichText();

        // For each crumb
        for ($i = 0; $i < count($crumbA); $i++) {

            // Set font name, size and color
            $objSelfStyled = $objRichText->createTextRun(strip_tags($crumbA[$i]));
            $objSelfStyled->getFont()->setName($font)->setSize('9')->getColor()->setRGB('04408C');

            // Check if crubs contains html-code
            if (mb_strlen($crumbA[$i], 'utf-8') != mb_strlen(strip_tags($crumbA[$i]), 'utf-8')) {

                // Set italic if detected
                if (preg_match('/<\/i>/', $crumbA[$i])) $objSelfStyled->getFont()->setItalic(true);

                // Set color if detected
                if (preg_match('/color[:=][ ]*[\'"]{0,1}([#a-zA-Z0-9]+)/i', $crumbA[$i], $c))

                    // If we find a hex equivalent for found color definition (if it's not already in hex format)
                    if ($hex = Indi::hexColor($c[1]))

                        // We set font color
                        $objSelfStyled->getFont()->getColor()->setRGB(ltrim($hex, '#'));

            }

            // Append separator
            if ($i < count($crumbA) -1) {
                $objSelfStyled = $objRichText->createTextRun(' » ');
                $objSelfStyled->getFont()->setName($font)->setSize('9');
                $objSelfStyled->getFont()->getColor()->setRGB('04408C');
            }
        }

        // Write prepared rich text object to first row
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', $objRichText);
        if (Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->applyFromArray($noBorder);

        // Here we set row height, because OpenOffice Writer (unlike Microsoft Excel)
        // ignores previously set default height definition
        $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(15.75);

        // Increment current row index as we need to keep it actual after each new row added to the spreadsheet
        $currentRowIndex++;

        // Set total number of $data items
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $currentRowIndex, I_TOTAL . ': ' . count($data));
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex);
        if (Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->applyFromArray($noBorder);
        $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(15.75);
        $currentRowIndex++;

        // If filters were used
        if (is_array($this->_excelA) && count($this->_excelA)) {

            // Before shifting current row index to provide a empty row for visual separation between bread
            // crumbs row and filters rows, we check if export type is 'pdf', and if so
            if (Indi::uri()->format == 'pdf') {

                // Insert a whitespace into first cell of that visual-separator-row, as DomPDF ignores height
                // definition for empty row, and makes it tiny, so, by inserting a whitespace - we ensure that
                // visual separation will be properly displayed
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $currentRowIndex, ' ');

                // Merge all cell within that separator-row before no-border-style apply
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex);

                // Apply no-border style
                $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->applyFromArray($noBorder);
            }

            // We shift current row index to provide a empty row for visual separation between bread crumbs row and filters rows,
            $currentRowIndex++;

            // Info about filters was prepared to $this->filtersWHERE() method, as an array of used filters
            // For each used filter:
            foreach ($this->_excelA as $alias => $excelI) {

                // Create rich text object
                $objRichText = new PHPExcel_RichText();

                // Merge all cell within current row
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex);

                // Write a filter title and setup a font name, size and color for it
                $objSelfStyled = $objRichText->createTextRun($excelI['title'] . ' -» ');
                $objSelfStyled->getFont()->setName($font)->setSize('8')->getColor()->setRGB('04408C');

                // If filter type is 'date' (or 'datetime'. There is no difference at this case)
                if ($excelI['type'] == 'date') {

                    // Get the format
                    foreach (Indi::trail()->fields as $fieldR) {
                        if ($fieldR->alias == $alias) {
                            $dformat = $fieldR->params['display' . ($fieldR->elementId == 12 ? '' : 'Date') . 'Format'];
                        }
                    }

                    // If start point for date range specified
                    if (isset($excelI['value']['gte'])) {

                        // Write the 'from ' string before actual filter date
                        $objSelfStyled = $objRichText->createTextRun(I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('04408C');

                        // Deal with date converstion
                        if (preg_match(Indi::rex('date'), $excelI['value']['gte'])) {
                            if ($excelI['value']['gte'] == '0000-00-00' && $dformat == 'd.m.Y') {
                                $excelI['value']['gte'] = '00.00.0000';
                            } else if ($excelI['value']['gte'] != '0000-00-00'){
                                $excelI['value']['gte'] = date($dformat, strtotime($excelI['value']['gte']));
                                if ($excelI['value']['gte'] == '30.11.-0001') $excelI['value']['gte'] = '00.00.0000';
                            }
                        }

                        // Write the converted date
                        $objSelfStyled = $objRichText->createTextRun($excelI['value']['gte'] . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                    }

                    // If end point for date range specified
                    if (isset($excelI['value']['lte'])) {

                        // Write the 'until ' string before actual filter date
                        $objSelfStyled = $objRichText->createTextRun(I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('04408C');

                        // Deal with date converstion
                        if (preg_match(Indi::rex('date'), $excelI['value']['lte'])) {
                            if ($excelI['value']['lte'] == '0000-00-00' && $dformat == 'd.m.Y') {
                                $excelI['value']['lte'] = '00.00.0000';
                            } else if ($excelI['value']['gte'] != '0000-00-00'){
                                $excelI['value']['lte'] = date($dformat, strtotime($excelI['value']['lte']));
                                if ($excelI['value']['lte'] == '30.11.-0001') $excelI['value']['lte'] = '00.00.0000';
                            }
                        }

                        // Write the converted date
                        $objSelfStyled = $objRichText->createTextRun($excelI['value']['lte'] . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                    }

                    // If filter type is 'number'
                } else if ($excelI['type'] == 'number') {

                    // If start point for number range specified
                    if (isset($excelI['value']['gte'])) {

                        // Write the 'from ' string before actual filter value
                        $objSelfStyled = $objRichText->createTextRun(I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('04408C');

                        // Write the actual filter start point value
                        $objSelfStyled = $objRichText->createTextRun($excelI['value']['gte'] . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                    }

                    // If start point for number range specified
                    if (isset($excelI['value']['lte'])) {

                        // Write the 'to ' string before actual filter value
                        $objSelfStyled = $objRichText->createTextRun(I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('04408C');

                        // Write the actual filter end point value
                        $objSelfStyled = $objRichText->createTextRun($excelI['value']['lte'] . ' ');
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                    }


                    // If filter type is 'number'
                } else if ($excelI['type'] == 'color') {

                    // Create the GD canvas image for hue background and thumbs to be placed there
                    $canvasIm = imagecreatetruecolor(197, 15);
                    imagecolortransparent($canvasIm, imagecolorallocate($canvasIm, 0, 0, 0));

                    // Pick hue bg and place it on canvas
                    $hueFn = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . STD . '/core/i/admin/i-color-slider-bg.png';
                    $hueIm = imagecreatefrompng($hueFn);
                    imagecopy($canvasIm, $hueIm, 7, 2, 0, 0, 183, 11);

                    // Pick first thumb and place it on canvas
                    $firstThumbFn = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . STD . '/core/i/admin/i-color-slider-thumb-first.png';
                    $firstThumbIm = imagecreatefrompng($firstThumbFn);
                    imagecopy($canvasIm, $firstThumbIm, floor(183/360*$excelI['value'][0]), 0, 0, 0, 15, 15);

                    // Pick last thumb and place it on canvas
                    $firstThumbFn = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . STD . '/core/i/admin/i-color-slider-thumb-last.png';
                    $firstThumbIm = imagecreatefrompng($firstThumbFn);
                    imagecopy($canvasIm, $firstThumbIm, floor(183/360*$excelI['value'][1]), 0, 0, 0, 15, 15);

                    //  Add the GD image to a spreadsheet
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setCoordinates('A' . $currentRowIndex);
                    $objDrawing->setImageResource($canvasIm);
                    $objDrawing->setHeight(11)->setWidth(183)->setOffsetY(4)->setOffsetX($excelI['offset'] + 5);
                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

                // If filter type is combo, with foreign table relation
                } else if ($excelI['table']) {

                    // Prepare the array of keys
                    if (!is_array($excelI['value'])) $excelI['value'] = array($excelI['value']);

                    // Fetch rows by keys
                    $rs = Indi::model($excelI['table'])->fetchAll('`id` IN (' . implode(',', $excelI['value']) . ')')->toArray();

                    // Foreach row
                    for ($i = 0; $i < count($rs); $i++) {

                        // Set default color
                        $color = '7EAAE2';

                        // Check if row title contains color definition
                        if (preg_match('/color[:=][ ]*[\'"]{0,1}([#a-zA-Z0-9]+)/i', $rs[$i]['title'], $c)) {

                            // If we find a hex equivalent for found color definition (if it's not already in hex format)
                            if ($hex = Indi::hexColor($c[1])) {

                                // Strip html from row title
                                $rs[$i]['title'] = strip_tags($rs[$i]['title']);

                                // Capture color, for being available to setup as a font color
                                $color = ltrim($hex, '#');
                            }
                        }

                        // Write row title
                        $objSelfStyled = $objRichText->createTextRun($rs[$i]['title']);
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB($color);

                        // Write separator, if needed
                        if ($i < count($rs) - 1) {
                            $objSelfStyled = $objRichText->createTextRun(', ');
                            $objSelfStyled->getFont()->setName($font)->setSize('8');
                            $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                        }
                    }

                // If filter type if also combo, but keys are from 'enumset' table
                } else if ($excelI['fieldId']) {

                    // Prepare the array of keys
                    if (!is_array($excelI['value'])) $excelI['value'] = array($excelI['value']);

                    // Fetch rows by keys
                    $rs = Indi::model('Enumset')->fetchAll(array(
                        '`fieldId` = "' . $excelI['fieldId'] . '"',
                        '`alias` IN ("' . implode('","', $excelI['value']) . '")'
                    ))->toArray();

                    // Foreach row
                    for ($i = 0; $i < count($rs); $i++) {

                        // Set default color
                        $color = '7EAAE2';

                        // Check if row title contains color definition
                        if (preg_match('/color[:=][ ]*[\'"]{0,1}([#a-zA-Z0-9]+)/i', $rs[$i]['title'], $c)) {

                            // If we find a hex equivalent for found color definition (if it's not already in hex format)
                            if ($hex = Indi::hexColor($c[1])) {

                                // Strip html from row title
                                $rs[$i]['title'] = strip_tags($rs[$i]['title']);

                                // Capture color, for being available to setup as a font color
                                $color = ltrim($hex, '#');
                            }

                        // Else if filter value contains an .i-color-box item, we replace it with same-looking GD image box
                        } else if (preg_match('/<span class="i-color-box" style="[^"]*background:\s*([^;]+);">/', $rs[$i]['title'], $c)) {

                            // If color was detected
                            if ($h = trim(Indi::hexColor($c[1]), '#')) {

                                // Create the GD image
                                $gdImage = @imagecreatetruecolor(14, 11) or iexit('Cannot Initialize new GD image stream');
                                imagefill($gdImage, 0, 0, imagecolorallocate(
                                    $gdImage, hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2)))
                                );

                                // Setup additional x-offset for color-box, for it to be centered within the cell
                                $additionalOffsetX = mb_strlen($objSelfStyled->getText(), 'utf-8') * 5.5;

                                //  Add the image to a worksheet
                                $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                                $objDrawing->setCoordinates('A' . $currentRowIndex);
                                $objDrawing->setImageResource($gdImage);
                                $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                                $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                                $objDrawing->setHeight(11);
                                $objDrawing->setWidth(14);
                                $objDrawing->setOffsetY(5)->setOffsetX($additionalOffsetX);
                                $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

                            // Else if cell value contains a .i-color-box item, that uses an image by background:url(...)
                            } else if (preg_match('/^ ?url\(([^)]+)\)/', $c[1], $src)) {

                                // If detected image exists
                                if ($abs = Indi::abs(trim($src[1], '.'))) {

                                    // Setup additional x-offset for color-box, for it to be centered within the cell
                                    //$additionalOffsetX = ceil(($columnI['width']-16)/2) - 3;

                                    //  Add the image to a worksheet
                                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                                    $objDrawing->setPath($abs);
                                    $objDrawing->setCoordinates('A' . $currentRowIndex);
                                    //$objDrawing->setOffsetY(3)->setOffsetX($additionalOffsetX);
                                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                                }
                            }

                            // Replace .i-color-box item from value, and prepend it with 6 spaces to provide an indent,
                            // because gd image will override cell value otherwise
                            $rs[$i]['title'] = str_pad('', 6, ' ') . strip_tags($rs[$i]['title']);
                        }

                        // Write row title
                        $objSelfStyled = $objRichText->createTextRun($rs[$i]['title']);
                        $objSelfStyled->getFont()->setName($font)->setSize('8');
                        $objSelfStyled->getFont()->getColor()->setRGB($color);

                        // Write separator, if needed
                        if ($i < count($rs) - 1) {
                            $objSelfStyled = $objRichText->createTextRun(', ');
                            $objSelfStyled->getFont()->setName($font)->setSize('8');
                            $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                        }
                    }

                // Else if filter type is 'text' or 'html' (simple text search)
                } else {

                    // Write the filter value
                    $objSelfStyled = $objRichText->createTextRun($excelI['value']);
                    $objSelfStyled->getFont()->setName($font)->setSize('8');
                    $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');
                }

                // Set rich text object as a cell value
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $currentRowIndex, $objRichText);
                if (Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->applyFromArray($noBorder);

                // Here we set row height, because OpenOffice Writer (unlike Excel) ignores previously setted default height
                $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(15.75);

                // Increment current row index as we need to keep it actual after each new row added to the spreadsheet
                $currentRowIndex++;
            }
        }

        // Append row with keyword, if keyword search was used
        if (Indi::get()->keyword) {

            // Setup new rich text object for keyword search usage mention
            $objRichText = new PHPExcel_RichText();

            // Merge current row sells and set alignment as 'right'
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // Write the keyword search method title, with a separator
            $objSelfStyled = $objRichText->createTextRun('Искать -» ');
            $objSelfStyled->getFont()->setName($font)->setSize('8');
            $objSelfStyled->getFont()->getColor()->setRGB('04408C');

            // Write used keyword
            $objSelfStyled = $objRichText->createTextRun(urldecode(Indi::get()->keyword));
            $objSelfStyled->getFont()->setName($font)->setSize('8');
            $objSelfStyled->getFont()->getColor()->setRGB('7EAAE2');

            // Set rich text object as cell value
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $currentRowIndex, $objRichText);

            // Set no-border style
            if (Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->applyFromArray($noBorder);

            // Here we set row height, because OpenOffice Writer (unlike Excel) ignores previously setted default height
            $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(15.75);

            // Increment current row index as we need to keep it actual after each new row added to the spreadsheet
            $currentRowIndex++;


        // If no keyword search was used, but number of filters, involved in search is more than 1
        // we provide an empty row, as a separator between filters mentions and found data
        } else if (is_array($this->_excelA) && count($this->_excelA) > 1) {

            // Here we set row height, because OpenOffice Writer (unlike Excel) ignores previously setted default height
            $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(15.75);

            // Set no-border style
            if (Indi::uri()->format == 'pdf') {

                // Merge all cell within that separator-row before no-border-style apply
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex);

                // Apply no-border style
                $objPHPExcel->getActiveSheet()->getStyle('A' . $currentRowIndex)->applyFromArray($noBorder);
            }

            // Increment current row index
            $currentRowIndex++;
        }

        // Get the order column alias and direction
        $order = @array_shift(json_decode(Indi::get()->sort));

        // For each column
        foreach ($columnA as $n => $columnI) {

            // Get column letter
            $columnL = PHPExcel_Cell::stringFromColumnIndex($n);

            // Setup column width
            $m = Indi::uri()->format == 'excel' ? 7.43 : 6.4;
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnL)->setWidth(ceil($columnI['width']/$m));

            // Replace &nbsp;
            $columnI['title'] = str_replace('&nbsp;', ' ', $columnI['title']);

            // Try detect an image
            if (preg_match('/<img src="([^"]+)"/', $columnI['title'], $src)) {

                // If detected image exists
                if ($abs = Indi::abs($src[1])) {

                    // Setup additional x-offset for color-box, for it to be centered within the cell
                    $additionalOffsetX = ceil(($columnI['width']-16)/2);

                    //  Add the image to a worksheet
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setPath($abs);
                    $objDrawing->setCoordinates($columnL . $currentRowIndex);
                    $objDrawing->setOffsetY(3)->setOffsetX($additionalOffsetX);
                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                }

                // Replace .i-color-box item from value, and prepend it with 6 spaces to provide an indent,
                // because gd image will override cell value otherwise
                $columnI['title'] = str_pad('', 6, ' ');

            // Else if current column is a row-numberer column
            } else if (preg_match('/^&#160;?$/', $columnI['title'])) {
                $columnI['title'] = ' ';
                $columnA[$n]['type'] = 'rownumberer';
            }

            // Write header title of a certain column to a header cell
            $objPHPExcel->getActiveSheet()->SetCellValue($columnL . $currentRowIndex, $columnI['title']);

            if ($columnI['dataIndex'] == $order->property) {

                // Create the GD canvas image for hue background and thumbs to be placed there
                $canvasIm = imagecreatetruecolor(13, 5);
                imagecolortransparent($canvasIm, imagecolorallocate($canvasIm, 0, 0, 0));

                // Pick hue bg and place it on canvas
                $iconFn = DOC . STD . '/core/library/extjs4/resources/themes/images/default/grid/sort_' . strtolower($order->direction) . '.gif';
                $iconIm = imagecreatefromgif($iconFn);
                imagecopy($canvasIm, $iconIm, 0, 0, 0, 0, 13, 5);

                //  Add the GD image to a spreadsheet
                $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                $objDrawing->setCoordinates($columnL . $currentRowIndex);
                $objDrawing->setImageResource($canvasIm);
                $objDrawing->setWidth(13)->setHeight(5)->setOffsetY(10)->setOffsetX($columnI['titleWidth'] + 6);
                $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
            }

            // Apply header row style
            $objPHPExcel->getActiveSheet()
                ->getStyle($columnL . $currentRowIndex)
                ->applyFromArray(
                array(
                    'borders' => array(
                        'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array(
                                'rgb' => ($columnI['dataIndex'] == $order->property ? 'aaccf6':'c5c5c5')
                            )
                        ),
                        'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array(
                                'rgb' => ($columnI['dataIndex'] == $order->property ? 'BDD5F1':'d5d5d5')
                            )
                        ),
                        'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array(
                                'rgb' => ($columnI['dataIndex'] == $order->property ? 'A7C7EE':'c5c5c5')
                            )
                        ),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startcolor' => array(
                            'rgb' => ($columnI['dataIndex'] == $order->property ? 'ebf3fd' : 'F9F9F9'),
                        ),
                        'endcolor' => array(
                            'rgb' => ($columnI['dataIndex'] == $order->property ? 'd9e8fb' : 'E3E4E6'),
                        ),
                    )
                )
            );


            // Ensure header title to be wrapped, if need
            if ($columnI['height']) {
                $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                if ($wrap < $columnI['height']) $wrap = $columnI['height'];
            }

            // Apply style for first cell within header row
            if (!$n && Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)
                ->applyFromArray(array(
                    'borders' => array(
                        'left' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array(
                                'rgb' => ($columnI['dataIndex'] == $order->property ? 'BDD5F1':'d5d5d5')
                            )
                        ),
                    )
                )
            );

            // Apply align for all rows within current column, except header rows
            $objPHPExcel->getActiveSheet()
                ->getStyle($columnL . ($currentRowIndex + 1) . ':' . $columnL . $lastRowIndex)
                ->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => $columnI['align']
                    )
                )
            );

            // Apply background color for all cells within current column,
            // in case if current column is a rownumberer-column
            if ($columnA[$n]['type'] == 'rownumberer') $objPHPExcel->getActiveSheet()
                ->getStyle($columnL . ($currentRowIndex + 1) . ':' . $columnL . $lastRowIndex)
                ->applyFromArray(
                array(
                    'borders' => array(
                        'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array(
                                'rgb' => ($columnI['dataIndex'] == $order->property ? 'BDD5F1':'d5d5d5')
                            )
                        ),
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 0,
                        'startcolor' => array(
                            'rgb' => 'F9F9F9',
                        ),
                        'endcolor' => array(
                            'rgb' => 'E3E4E6',
                        ),
                    )
                )
            );

        }

        // Here we set row height, because OpenOffice Writer (unlike Excel) ignores previously setted default height
        $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(($wrap ? $wrap/22 : 1) * 15.75);
        $currentRowIndex++;

        // We remember a current row index at this moment, because it is the index which data rows are starting from
        $dataStartAtRowIndex = $currentRowIndex;

        // Foreach item in $data array
        for ($i = 0; $i < count($data); $i++) {

            // If grouing is turned on, and current row is within a new group
            if ($group && $prevGroup != $data[$i][$group['property']]) {

                // Here we set row height, because OpenOffice Writer (unlike Excel) ignores previously setted default height
                $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(20.75);

                // Convert the column index to excel column letter
                $columnL = PHPExcel_Cell::stringFromColumnIndex(0);

                // Merge cells
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex);

                // Set cell value
                $objPHPExcel->getActiveSheet()->SetCellValue($columnL . $currentRowIndex, $data[$i][$group['property']]);

                // Set style
                $objPHPExcel->getActiveSheet()
                    ->getStyle($columnL . $currentRowIndex . ':' . $lastColumnLetter . $currentRowIndex)
                    ->applyFromArray(array(
                        'borders' => array(
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                                'color' => array('rgb' => '7EAAE2')
                            ),
                        ),
                        'alignment' => array('vertical' => 'bottom', 'horizontal' => 'left', 'indent' => 2),
                        'fill' => array('type' => PHPExcel_Style_Fill::FILL_NONE)
                    ));

                //  Add the image to a worksheet
                $objDrawing = new PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath(DOC . STD . '/core/library/extjs4/resources/themes/images/default/grid/group-collapse.gif');
                $objDrawing->setCoordinates($columnL . $currentRowIndex);
                $objDrawing->setOffsetY(10)->setOffsetX(6);
                $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

                // Increment current row index;
                $currentRowIndex++;

                // Set previous group
                $prevGroup = $data[$i][$group['property']];
            }

            // Here we set row height, because OpenOffice Writer (unlike Excel) ignores previously sett default height
            $objPHPExcel->getActiveSheet()->getRowDimension($currentRowIndex)->setRowHeight(15.75);

            // Foreach column
            foreach ($columnA as $n => $columnI) {

                // Convert the column index to excel column letter
                $columnL = PHPExcel_Cell::stringFromColumnIndex($n);

                // Get the index/value
                if ($columnI['dataIndex']) $value = $data[$i]['_render'][$columnI['dataIndex']] ?: $data[$i][$columnI['dataIndex']];
                else if ($columnI['type'] == 'rownumberer') $value = $i + 1;
                else $value = '';

                // If cell value contains a .i-color-box item, we replaced it with same-looking GD image box
                if (preg_match('/<span class="i-color-box" style="[^"]*background:\s*([^;]+);" title="[^"]+">/', $value, $c)) {

                    // If color was detected
                    if ($h = trim(Indi::hexColor($c[1]), '#')) {

                        // Create the GD image
                        $gdImage = @imagecreatetruecolor(14, 11) or iexit('Cannot Initialize new GD image stream');
                        imagefill($gdImage, 0, 0, imagecolorallocate(
                            $gdImage, hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2)))
                        );

                        // Setup additional x-offset for color-box, for it to be centered within the cell
                        $additionalOffsetX = ceil(($columnI['width']-14)/2) - 2;

                        //  Add the image to a worksheet
                        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                        $objDrawing->setCoordinates($columnL . $currentRowIndex);
                        $objDrawing->setImageResource($gdImage);
                        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                        $objDrawing->setHeight(11);
                        $objDrawing->setWidth(14);
                        $objDrawing->setOffsetY(5)->setOffsetX($additionalOffsetX);
                        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());


                    // Else if cell value contains a .i-color-box item, that uses an image by background:url(...)
                    } else if (preg_match('/^ ?url\(([^)]+)\)/', $c[1], $src)) {

                        // If detected image exists
                        if ($abs = Indi::abs(trim($src[1], '.'))) {

                            // Setup additional x-offset for color-box, for it to be centered within the cell
                            $additionalOffsetX = ceil(($columnI['width']-16)/2) - 3;

                            //  Add the image to a worksheet
                            $objDrawing = new PHPExcel_Worksheet_Drawing();
                            $objDrawing->setPath($abs);
                            $objDrawing->setCoordinates($columnL . $currentRowIndex);
                            $objDrawing->setOffsetY(3)->setOffsetX($additionalOffsetX);
                            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                        }
                    }

                    // Replace .i-color-box item from value, and prepend it with 6 spaces to provide an indent,
                    // because gd image will override cell value otherwise
                    $value = str_pad('', 6, ' ') . strip_tags($value);

                // Else if cell value contain a color definition within 'color' attribute,
                // or as a 'color: xxxxxxxx' expression within 'style' attribute, we extract that color definition
                } else if (preg_match('/color[:=][ ]*[\'"]{0,1}([#a-zA-Z0-9]+)/i', $value, $c)) {

                    // If we find a hex equivalent for found color definition (if it's not already in hex format)
                    if ($hex = Indi::hexColor($c[1]))

                        // Set cell's color
                        $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)
                            ->getFont()->getColor()->setRGB(ltrim($hex, '#'));
                }

                // If cell contains an 'a' tag with href attribute, we set a hyperlink to current cell
                if (preg_match('/<a/', $value) && preg_match('/href="([^"]+)"/', $value, $a)) {


                    // If there is no protocol definition within href, we set it as 'http://'
                    $protocol = preg_match('/:\/\//', $a[1]) || preg_match('~^(mailto|tel):~', $a[1]) ? '' : 'http://';

                    // If href start with a '/', it means that there is no hostname specified, so we define default
                    $server = preg_match('/^\/[^\/]{0,1}/', $a[1]) ? $_SERVER['HTTP_HOST'] : '';

                    // Prepend href with protocol and hostname
                    $a[1] = $protocol . $server . $a[1];

                    // Filter
                    $url = array_shift(explode(' ', trim($a[1])));

                    // Try detect an image
                    if (preg_match('/<img src="([^"]+)"/', $value, $src) && $abs = Indi::abs($src[1])) {

                        // Setup additional x-offset for color-box, for it to be centered within the cell
                        $additionalOffsetX = ceil(($columnI['width']-16)/2);

                        //  Add the image to a worksheet
                        $objDrawing = new PHPExcel_Worksheet_Drawing();
                        $objDrawing->setPath($abs);
                        $objDrawing->setCoordinates($columnL . $currentRowIndex);
                        $objDrawing->setOffsetY(3)->setOffsetX($additionalOffsetX);
                        $objDrawing->setHyperlink(new PHPExcel_Cell_Hyperlink($url));
                        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

                        // Replace .i-color-box item from value, and prepend it with 6 spaces to provide an indent,
                        // because gd image will override cell value otherwise
                        $value = str_pad('', 6, ' ');

                    // Else
                    } else {

                        // Set cell value as hyperlink
                        $objPHPExcel->getActiveSheet()->getCell($columnL . $currentRowIndex)->getHyperlink()->setUrl($url);
                        $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getFont()->setUnderline(true);
                    }
                }

                // Strip html content from $value
                $value = strip_tags($value);

                // Replace some special characters definitions to its actual symbols
                $value = str_replace(
                    array('&nbsp;','&laquo;','&raquo;','&mdash;','&quot;','&lt;','&gt;'),
                    array(' ','«','»','—','"','<','>'), $value);

                // Set right and bottom border, because cell fill will hide default Excel's ot OpenOffice Write's cell borders
                $objPHPExcel->getActiveSheet()
                    ->getStyle($columnL . $currentRowIndex)
                    ->applyFromArray(
                    array(
                        'borders' => array(
                            'right' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => 'D0D7E5'
                                )
                            ),
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => 'D0D7E5'
                                )
                            )
                        )
                    )
                );

                // Apply style for first cell within header row
                if (!$n && Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)
                    ->applyFromArray(array(
                        'borders' => array(
                            'left' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => 'c5c5c5'
                                )
                            ),
                        )
                    )
                );

                // Get the control element
                $el = $columnI['dataIndex'] == 'id'
                    ? 'number'
                    : Indi::trail()->model->fields($columnI['dataIndex'])->foreign('elementId')->alias;

                // If control element is 'price' or 'number'
                if (in($el, 'price,number')) {

                    // Display zero-values only if `displayZeroes` flag for current column is `true`
                    if ($value == 0 && !$columnI['displayZeroes']) $value = '';

                    // Set format
                    if ($el == 'price') $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    else $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getNumberFormat()
                        ->setFormatCode('#,##0');

                }

                // Set cell value
                $objPHPExcel->getActiveSheet()->SetCellValue($columnL . $currentRowIndex, $value);

                // Set odd-even rows background difference
                if ($i%2 && $columnA[$n]['type'] != 'rownumberer') {
                    $objPHPExcel->getActiveSheet()
                        ->getStyle($columnL . $currentRowIndex)
                        ->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FAFAFA');
                }

                // Cell style custom adjustments
                $this->adjustExcelExportCellStyle($objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex), $columnI, $value, $i);
            }

            // Increment current row index;
            $currentRowIndex++;
        }

        // Apply right border for the most right column
        $objPHPExcel->getActiveSheet()
            ->getStyle($lastColumnLetter . $dataStartAtRowIndex . ':' . $lastColumnLetter . $lastRowIndex)
            ->applyFromArray(array(
                'borders' => array(
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'rgb' => 'c5c5c5'
                        )
                    )
                )
            )
        );

        // Apply last row style (bottom border)
        $objPHPExcel->getActiveSheet()
            ->getStyle('A' . (count($data) + $dataStartAtRowIndex - 1) . ':' . $columnL . (count($data) + $groupQty + $dataStartAtRowIndex - 1))
            ->applyFromArray(
            array(
                'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'rgb' => 'c5c5c5'
                        )
                    )
                )
            )
        );

        // Append summary row
        if ($summary = $this->rowsetSummary()) {

            // Foreach column
            foreach ($columnA as $n => $columnI) {

                // Convert the column index to excel column letter
                $columnL = PHPExcel_Cell::stringFromColumnIndex($n);

                // Get the value
                $value = $summary->{$columnI['dataIndex']};

                // Get the control element
                $el = $columnI['dataIndex'] == 'id'
                    ? 'number'
                    : Indi::trail()->model->fields($columnI['dataIndex'])->foreign('elementId')->alias;

                // If control element is 'price' or 'number'
                if (in($el, 'price,number')) {

                    // Display zero-values only if `displayZeroes` flag for current column is `true`
                    if ($value == 0 && !$columnI['displayZeroes']) $value = '';

                    // Set format
                    if ($el == 'price') $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    else $objPHPExcel->getActiveSheet()->getStyle($columnL . $currentRowIndex)->getNumberFormat()
                        ->setFormatCode('#,##0');

                }

                // Set cell value
                $objPHPExcel->getActiveSheet()->SetCellValue($columnL . $currentRowIndex, $value);

                // Set up no-border style
                if (Indi::uri()->format == 'pdf') $objPHPExcel->getActiveSheet()->getStyle($columnL . (count($data) + $dataStartAtRowIndex - 1 + 1))
                    ->applyFromArray($noBorder)
                    ->applyFromArray(array(
                    'borders' => array(
                        'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array(
                                'rgb' => 'ffffff'
                            )
                        )
                    )
                ));
            }

            // Increment current row index
            $currentRowIndex++;
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle(Indi::trail()->section->title);

        // Freeze header
        $objPHPExcel->getActiveSheet()->freezePane('A' . ($dataStartAtRowIndex));

        // Primary Excel document custom adjustments
        $this->adjustExport($objPHPExcel);

        // Apply adjustments, especially for a given kind of format
        if (in(Indi::uri()->format, 'pdf,excel')) $this->{'adjust' . ucfirst(Indi::uri()->format) . 'Export'}($objPHPExcel);

        // Possible formats details
        $formatCfg = array(
            'excel' => array(
                'ext' => 'xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'writer' => 'Excel2007'
            ),
            'pdf' => array(
                'ext' => 'pdf',
                'mime' => 'application/pdf',
                'writer' => 'PDF_DomPDF',
                'renderer' => PHPExcel_Settings::PDF_RENDERER_DOMPDF
            ),
        );

        // Output
        $file = $this->exportFname() . '.' . $formatCfg[$format]['ext'];
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) $file = iconv('utf-8', 'windows-1251', $file);
        header('Content-Type: ' . $formatCfg[$format]['mime']);
        header('Content-Disposition: attachment; filename="' . $file . '"');

        // Load PHPExcel's IO Factory class
        require_once 'PHPExcel/IOFactory.php';

        // If export format is 'pdf'
        if ($format == 'pdf') {

            // Setup $rName and $rPath
            $rName = $formatCfg[$format]['renderer'];
            $rPath = DOC . STD . '/core/library/' . $rName;

            // Try to set up pdf renderer
            if (!PHPExcel_Settings::setPdfRenderer($rName, $rPath))
                jflush(false, 'Can\'t set up pdf renderer with such name and/or path');
        }

        // Create writer
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $formatCfg[$format]['writer']);


        // Create temporary file
        $tmp = tempnam(ini_get('upload_tmp_dir'), 'xls');

        // Save into temporary file
        $objWriter->save($tmp);

        // Get raw file contents
        $raw = file_get_contents($tmp);

        // Flush Content-Length header
        header('Content-Length: ' . strlen($raw));

        // Flush raw
        echo $raw;

        // Delete temporary file
        unlink($tmp);
		
        // Exit
        iexit();
    }

    /**
     * Adjust style of an excel-spreadsheet's cell
     *
     * @param $cellStyleObj
     * @param $columnI
     * @param $value
     * @param $i
     */
    public function adjustExcelExportCellStyle($cellStyleObj, $columnI, $value, $i) {

    }

    /**
     * Default export file name builder
     */
    public function exportFname() {
        return Indi::trail()->section->title;
    }

    /**
     * Export file name builder for excel spreadsheets
     */
    public function exportFnameExcel() {
        return $this->exportFname();
    }

    /**
     * Export file name builder for pdf documents
     */
    public function exportFnamePdf() {
        return $this->exportFname();
    }

    /**
     * Empty function. To be redeclared in child classes in case of a need for an export document adjustments
     *
     * @param $objPHPExcel
     */
    public function adjustExport(&$objPHPExcel) {

    }

    /**
     * Empty function. To be redeclared in child classes in case of a need for an excel-export document adjustments
     *
     * @param $objPHPExcel
     */
    public function adjustExcelExport(&$objPHPExcel) {

    }

    /**
     * Empty function. To be redeclared in child classes in case of a need for an pdf-export document adjustments
     *
     * @param $objPHPExcel
     */
    public function adjustPdfExport(&$objPHPExcel) {

    }

    /**
     * Try to find user data in certain place (database table), identified by $place argument
     *
     * @param $username
     * @param $password
     * @param string $place
     * @param null $profileId
     * @param array $level1ToggledSectionIdA
     * @return array|mixed
     */
    protected function _findSigninUserData($username, $password, $place = 'admin', $profileId = null,
                                           $level1ToggledSectionIdA = array()) {

        // If $username arg is an instance of Indi_Db_Table_Row class, this means that
        // there are some user already logged in, but wants to switch to another account
        if ($username instanceof Indi_Db_Table_Row) {

            // Pick username and password
            $switchTo = $username;
            $username = $switchTo->email;
            $password = $switchTo->password;
        }

        $profileId = Indi::model($place)->fields('profileId') ? '`a`.`profileId`' : '"' . $profileId . '"';
        $adminToggle = Indi::model($place)->fields('toggle') ? '`a`.`toggle` = "y"' : '1';
        return Indi::db()->query('
            SELECT
                `a`.*,
                `a`.`password` IN (IF(' . ($_SESSION['admin'] || $place != 'admin' ? 1 : 0) . ', :s, ""), PASSWORD(:s)) AS `passwordOk`,
                '. $adminToggle . ' AS `adminToggle`,
                IF(`p`.`entityId`, `p`.`entityId`, 11) as `mid`,
                `p`.`toggle` = "y" AS `profileToggle`,
                `p`.`title` AS `profileTitle`,
                COUNT(`sa`.`sectionId`) > 0 AS `atLeastOneSectionAccessible`
            FROM `' . $place . '` `a`
                LEFT JOIN `profile` `p` ON (`p`.`id` = ' . $profileId . ')
                LEFT JOIN `section2action` `sa` ON (
                    FIND_IN_SET(' . $profileId . ', `sa`.`profileIds`)
                    AND `sa`.`actionId` = "1"
                    AND `sa`.`toggle` = "y"
                    AND FIND_IN_SET(`sa`.`sectionId`, "' . implode(',', $level1ToggledSectionIdA) . '")
                )
            WHERE `a`.`email` = :s
            GROUP BY `a`.`id`
            LIMIT 1
        ', $password, $password, $username)->fetch();
    }

    /**
     * Perform first level of authentication - check that:
     * 1. User exists
     * 2. Password is ok
     * 3. User is not disabled
     * 4. User have a type, that is not disabled
     * 5. There is at least one toggled 'On' section at level 1, that user have access to
     *
     * @param $username
     * @param $password
     * @return array|mixed|string
     */
    private function _authLevel1($username, $password = null) {

        // Get array of most top toggled 'On' sections ids
        $level0ToggledOnSectionIdA = Indi::db()->query('
            SELECT `id`
            FROM `section`
            WHERE `sectionId` = "0" AND `toggle` = "y"
        ')->fetchAll(PDO::FETCH_COLUMN);

        // Get array of level 1 toggled 'On' sections ids.
        $level1ToggledOnSectionIdA = Indi::db()->query('
            SELECT `id`
            FROM `section`
            WHERE FIND_IN_SET(`sectionId`, "' . implode(',', $level0ToggledOnSectionIdA) . '") AND `toggle` = "y"
        ')->fetchAll(PDO::FETCH_COLUMN);

        // Try to find user data in primary place - `admin` table
        $data = $this->_findSigninUserData($username, $password, 'admin', null, $level1ToggledOnSectionIdA);

        // If not found
        if (!$data['id']) {

            // Get the list of other possible places, there user with given credentials can be found
            $profile2tableA = Indi::db()->query('
                SELECT `e`.`table`, `p`.`id` AS `profileId`
                FROM `entity` `e`, `profile` `p`
                WHERE `p`.`entityId` != "0"
                    AND `p`.`entityId` = `e`.`id`
                    AND `e`.`table` != "admin"
            ')->fetchAll();

            // Foreach possible place - try to find
            foreach ($profile2tableA as $profile2tableI) {
                $data = $this->_findSigninUserData($username, $password, $profile2tableI['table'],
                    $profile2tableI['profileId'], $level1ToggledOnSectionIdA);
                if ($data['id']) break;
            }

            // If found - assign some additional info to found data
            if ($data && $data['id'] && $profile2tableI) {
                $data['alternate'] = $profile2tableI['table'];
                $data['profileId'] = $profile2tableI['profileId'];
            }
        }

        // Set approriate error messages if:
        // 1. User data is still not found
        if (!$data) $error = I_LOGIN_ERROR_NO_SUCH_ACCOUNT;

        // 2. Given password is wrong
        else if (!$data['passwordOk']) $error = I_LOGIN_ERROR_WRONG_PASSWORD;

        // 3. User's signin ability is turned off
        else if (!$data['adminToggle']) $error = I_LOGIN_ERROR_ACCOUNT_IS_OFF;

        // 4. User's profile is turned off (So all users with such profile are unable to signin)
        else if (!$data['profileToggle']) $error = I_LOGIN_ERROR_PROFILE_IS_OFF;

        // 5. User have no accessbile sections
        else if (!$data['atLeastOneSectionAccessible']) $error = I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS;

        // Return error or signin-data
        return $error ?: $data;
    }

    /**
     * Check is user has access to perform $action action within $section section. Checks include:
     * 1. Section exists
     * 2. Section is switched on
     * 3. Action exists
     * 4. Action is switched on
     * 5. Action exists within section
     * 6. Action is switched in within section
     * 7. User has access to action within section
     * 8. Section is a child of a section that is switched on, and all parents up to the top are switched on too
     *
     * @param $section
     * @param $action
     * @return array|mixed|string
     */
    private function _authLevel2($section, $action) {

        // If action name is not valid - return an error message
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $action)) return I_URI_ERROR_ACTION_FORMAT;

        // Try to find use data
        $data = Indi::db()->query('
            SELECT
                `s`.`id`,
                `s`.`toggle` != "n" AS `sectionToggle`,
                `a`.`id` > 0 AS `actionExists`,
                `a`.`toggle` = "y" AS `actionToggle`,
                `sa`.`id` > 0 AS `section2actionExists`,
                `sa`.`toggle` = "y" AS `section2actionToggle`,
                FIND_IN_SET("' . $_SESSION['admin']['profileId'] . '", `sa`.`profileIds`) > 0 AS `granted`,
                `s`.`sectionId` as `sectionId`
            FROM `section` `s`
               LEFT JOIN `action` `a` ON (`a`.`alias` = "' . $action . '")
               LEFT JOIN `section2action` `sa` ON (`sa`.`actionId` = `a`.`id` AND `sa`.`sectionId` = `s`.`id`)
            WHERE 1
                AND `s`.`alias` = "' . $section . '"
                AND `s`.`sectionId` != "0"
        ')->fetch();

        // Set approriate error messages if:
        // 1. Section was not found
        if (!$data) $error = I_ACCESS_ERROR_NO_SUCH_SECTION;

        // 2. Section is switched off
        else if (!$data['sectionToggle']) $error = I_ACCESS_ERROR_SECTION_IS_OFF;

        // 3. Action does not exist at all
        else if (!$data['actionExists']) $error = I_ACCESS_ERROR_NO_SUCH_ACTION;

        // 4. Action is switched off
        else if (!$data['actionToggle']) $error = I_ACCESS_ERROR_ACTION_IS_OFF;

        // 5. Action does not exits in that section
        else if (!$data['section2actionExists']) $error = I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION;

        // 6. Action is switched off in that section
        else if (!$data['section2actionToggle']) $error = I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION;

        // 7. User have no rights on that action in that section
        else if (!$data['granted'] && !Indi::uri('consider')) $error = I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE;

        // 8. One of parent sections for current section - is switched off
        else {

            // Start fulfil section id stack
            $this->_routeA = array($data['id'], $data['sectionId']);

            // Setup initial id of parent section
            $parent = array('sectionId' => $data['sectionId']);

            // Navigate through parent sections up to the root
            while ($parent = Indi::db()->query('
                SELECT `sectionId`, `toggle` FROM `section` WHERE `id` = "' . $parent['sectionId'] . '" LIMIT 1
            ')->fetch()) {

                // If any of parent sections if switched off - setup an error and break the loop
                if ($parent['toggle'] == 'n') {
                    $error = I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF;
                    break;

                // Else push new item in $this->_routeA stack
                } else if ($parent['sectionId']) $this->_routeA[] = $parent['sectionId'];

                // Else stop loop, as $parent['sectionId'] = 0, so there is no sense to find a section with such an `id`
                else break;
            }
        }

        // If $error was set - return error, or return $data otherwise
        return $error ? $error : $data;
    }

    /**
     * Perform authentication by 3 levels
     */
    public function auth() {

        // Allow CORS
        header('Access-Control-Allow-Headers: x-requested-with, indi-auth');
        header('Access-Control-Allow-Origin: *');

        // If visitor is a visitor, e.g. he has not signed in yet
        if (!$_SESSION['admin']) {

            // If 'consider' param is passed within the uri
            if (Indi::uri('consider')) {

                // Do the second level access check
                $data = $this->_authLevel2(Indi::uri()->section, Indi::uri()->action);

                // If $data is not an array, e.g some error there, output it as json with that error
                if (!is_array($data)) jflush(false, $data);

                // Else go further and perform last auth check, within Indi_Trail_Admin::__construct()
                else Indi::trail($this->_routeA, $this)->authLevel3();

            // Else do ordinary check
            } else {

                // If he is trying to do that
                if (Indi::post()->enter && Indi::uri('section') == 'index' && Indi::uri('action') == 'index') {

                    // If no username given
                    if (!Indi::post()->username) $data = I_LOGIN_ERROR_ENTER_YOUR_USERNAME;

                    // Else if no password given
                    else if (!Indi::post()->password) $data = I_LOGIN_ERROR_ENTER_YOUR_PASSWORD;

                    // Else try to find user's data
                    else $data = $this->_authLevel1(Indi::post()->username, Indi::post()->password);

                    // If $data is not an array, e.g some error there, output it as json with that error
                    if (!is_array($data)) jflush(false, $data);

                    // Else start a session for user and report that sing-in was ok
                    $allowedA = array('id', 'title', 'email', 'password', 'profileId', 'profileTitle', 'alternate', 'mid');
                    foreach ($allowedA as $allowedI) $_SESSION['admin'][$allowedI] = $data[$allowedI];

                    // Create `realtime` entry having `type` = 'session'
                    m('Realtime')->createRow([
                        'type' => 'session',
                        'profileId' => $_SESSION['admin']['profileId'],
                        'adminId' => $_SESSION['admin']['id'],
                        'token' => session_id(),
                        'langId' => m('Lang')->fetchRow('`alias` = "' . $_COOKIE['i-language'] . '"')->id,
                    ], true)->save();

                    // Flush response
                    jflush(true, APP ? $this->info() : array('ok' => '1'));
                }

                // If user was thrown out from the system, assign a throwOutMsg to Indi::view() object, for this message
                // to be available for picking up and usage as Ext.MessageBox message, as a reason of throw out
                if ($_SESSION['indi']['throwOutMsg']) {
                    Indi::view()->throwOutMsg = $_SESSION['indi']['throwOutMsg'];
                    unset($_SESSION['indi']['throwOutMsg']);
                }

                // If user is trying to access server-app using standalone client-app
                if (APP) {

                    // Flush basic info
                    jflush(true, array(
                        'std' => STD,
                        'com' => COM ? '' : '/admin',
                        'pre' => PRE,
                        'uri' => Indi::uri()->toArray(),
                        'title' => Indi::ini('general')->title ?: 'Indi Engine',
                        'throwOutMsg' => Indi::view()->throwOutMsg,
                        'lang' => $this->lang(),
                        'css' => @file_get_contents(DOC . STD . '/www/css/admin/app.css') ?: '',
                        'logo' => Indi::ini('general')->logo
                    ));

                // Else if '/admin' folder exists and contains Indi standalone client app
                } else if (file_exists($client = DOC . STD . '/admin/index.html') && !isset(Indi::get()->classic)) {

                    // Flush client app's bootstrap file
                    iexit(readfile($client));

                // Else if user is trying to access server-app using usual way
                } else {

                    // Setup l10n data
                    $this->lang();

                    // Render login page
                    $out = Indi::view()->render('login.php');

                    // Do paths replacements, if current project runs within webroot subdirectory
                    if (STD) {
                        $out = preg_replace('/(<link[^>]+)(href)=("|\')\//', '$1$2=$3' . STD . '/', $out);
                        $out = preg_replace('/(<script[^>]+)(src)=("|\')\//', '$1$2=$3' . STD . '/', $out);
                        $out = preg_replace('/(<img[^>]+)(src)=("|\')\//', '$1$2=$3' . STD . '/', $out);
                    }

                    // Flush the login page
                    iexit($out);
                }
            }

        // Else if user is already signed in, and is trying to perform some action in some section
        } else {

            // Do the first level access check
            $data = $this->_authLevel1($_SESSION['admin']['email'], $_SESSION['admin']['password']);

            // If $data is not an array, e.g some error there, output it as json with that error
            if (!is_array($data)) {

                // Change the error message to it's version for case then user was thrown out
                if ($data == I_LOGIN_ERROR_NO_SUCH_ACCOUNT) $data = I_THROW_OUT_ACCOUNT_DELETED;
                else if ($data == I_LOGIN_ERROR_WRONG_PASSWORD) $data = I_THROW_OUT_PASSWORD_CHANGED;
                else if ($data == I_LOGIN_ERROR_ACCOUNT_IS_OFF) $data = I_THROW_OUT_ACCOUNT_IS_OFF;
                else if ($data == I_LOGIN_ERROR_PROFILE_IS_OFF) $data = I_THROW_OUT_PROFILE_IS_OFF;
                else if ($data == I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS) $data = I_THROW_OUT_NO_ACCESSIBLE_SECTIONS;

                // Save error message in session, for ability to display it in message box
                $_SESSION['indi']['throwOutMsg'] = $data;

                // Logout
                if (APP) $this->logout(); else
                if (Indi::uri()->section == 'index') iexit(header('Location: ' . PRE . '/logout/'));
                else if (!Indi::uri()->format) iexit('<script>top.window.location="' . PRE .'/logout/"</script>');
                else jflush(false, array('throwOutMsg' => $data));

            // Else if current section is not 'index', e.g we are not in the root of interface
            } else if (Indi::uri()->section != 'index') {

                // Do the second level access check
                $data = $this->_authLevel2(Indi::uri()->section, Indi::uri()->action);

                // If $data is not an array, e.g some error there, output it as json with that error
                if (!is_array($data)) jflush(false, $data);

                // Else go further and perform last auth check, within Indi_Trail_Admin::__construct()
                else Indi::trail($this->_routeA, $this)->authLevel3();

            // Else if current section is 'index', e.g we are in the root of interface
            } else if (!m('Realtime')->fetchRow(['`type` = "session"', '`token` = "' . session_id() . '"'])) {

                // Create `realtime` entry having `type` = 'session'
                m('Realtime')->createRow([
                    'type' => 'session',
                    'profileId' => $_SESSION['admin']['profileId'],
                    'adminId' => $_SESSION['admin']['id'],
                    'token' => session_id(),
                    'langId' => m('Lang')->fetchRow('`alias` = "' . $_COOKIE['i-language'] . '"')->id,
                ], true)->save();
            }
        }

        // If current request had a only aim to check access - report that all is ok
        if (array_key_exists('check', (array) Indi::get())) die('ok');
    }

    /**
     * Calculate summary data to be included in the json output
     *
     * @param bool $force
     * @return mixed
     */
    function rowsetSummary($force = false) {

        // Retrieve summary definitions from $_GET['summary'] if given, else from `grid`.`summaryType`
        if (!$summary = t()->summary()) return;

        // If $summary is not json-decodable - return
        if (!($summary = json_decode($summary, true))) return;

        // If all possible results are already fetched, and if section view type is grid - return,
        // as in such sutuation we can fully rely on grid's own summary feature, built on javascript
        if ((Indi::trail()->section->rowsOnPage >= Indi::trail()->scope->found && !$force) && !Indi::trail()->model->treeColumn())
            if ($this->actionCfg['view']['index'] == 'grid' && !in(Indi::uri('format'), 'excel,pdf'))
                if (!APP) return;

        // Define an array containing extjs summary types and their sql representatives
        $js2sql = array('sum' => 'SUM', 'min' => 'min', 'max' => 'MAX', 'average' => 'AVG');//, 'count' => 'COUNT');

        // Get grid columns aliases
        $cols = Indi::trail()->gridFields->column('alias');

        // Build an array containing sql-function calls for each column, that have a summary to be retrieved for
        foreach ($js2sql as $type => $fn)
            if ($summary[$type])
                foreach ($summary[$type] as $col)
                    if (in($col, $cols))
                        $sql[] = 'IFNULL(' . $fn . '(`' . $col . '`), 0) AS `' . $col .'`';

        // If no sql-function calls collected - return
        if (!$sql) return;

        // Build basic sql query for summaries calculation
        $sql = 'SELECT ' . implode(', ', $sql) . ' FROM `' . Indi::trail()->model->table() . '`';

        // Declare array for WHERE clauses stack
        $where = array();

        // If current model has a tree column, and it is not forced to be ignored - append special
        // clause to WHERE-clauses stack for summaries to be calculated only for top-level entries
        if (Indi::trail()->model->treeColumn() && !$this->actionCfg['misc']['index']['ignoreTreeColumn'])
            $where['rootRowsOnly'] = '`' . Indi::trail()->model->treeColumn() . '` = "0"';

        // Append scope's WHERE clause to the stack
        if (strlen(Indi::trail()->scope->WHERE)) $where[] = Indi::trail()->scope->WHERE;

        // Adjust WHERE clause
        $where = $this->adjustRowsetSummaryWHERE($where);

        // Append WHERE clause to that query
        if ($where) $sql .= ' WHERE ' . im($where, ' AND ');

        // Fetch summary
        $summary = db()->query($sql)->fetchObject();

        // Convert to integer
        array_walk($summary, fn(&$v) => $v += 0);

        // Fetch and return calculated summaries
        return $summary;
    }

    /**
     * Adjust WHERE clause especially for rowset's summary calculation. This function is empty here, but may be useful in
     * some situations
     */
    function adjustRowsetSummaryWHERE($where) {

        // Return
        return $where;
    }

    /**
     * Render the output. If $return argument is true, builded output will be returned instead of flushing into the
     * browser
     *
     * @param bool $return
     * @return mixed|string|void
     */
    public function postDispatch($return = false) {

        // If we are in a root of interface, build the general layout
        if (Indi::uri('section') == 'index' && Indi::uri('action') == 'index') {

            // If it's a refresh-menu request - flush fresh menu
            if (isset(Indi::get()->menu)) jflush(true, ['menu' => $this->menu()]);

            // Get info
            $info = $this->info();

            // If request was made via Indi Engine client app - flush info right now
            if (APP) jflush(true, $info);

            // Else if '/admin' folder exists and contains Indi standalone client app
            else if (file_exists($client = DOC . STD . '/admin/index.html') && !isset(Indi::get()->classic))

                // Flush client app's bootstrap file
                iexit(readfile($client));

            // Setup info about current logged in cms user, and accessible menu
            Indi::view()->admin = $info['user']['title'] . ' [' . $info['user']['role']  . ']';
            Indi::view()->menu = $info['user']['menu'];

            // Render the layout
            $out = Indi::view()->render('index.php');


        // Else, if we are doing something in a certain section
        } else {

            // Prevent sub-request
            Indi::trail()->section->southSeparate = $this->_isNestedSeparate;

            // Get the action
            $action = Indi::trail()->view(true);

            // If action is an object-instance of Indi_View_Action_Admin class, call render() method,
            // otherwise assume that action is just a view script
            $out = $action instanceof Indi_View_Action_Admin ? $action->render() : $action;
        }

        // Strip '/admin' from $out, if cms-only mode is enabled
        if (COM) $out = preg_replace('/(action|src|href)=("|\')\/admin/', '$1=$1', $out);

        // Make a src|href replacements, if project is running in a subfolder of document root
        if (STD) {
            $out = preg_replace('/(<link[^>]+)(href)=("|\')\//', '$1$2=$3' . STD . '/', $out);
            $out = preg_replace('/(<a[^>]+)(href)=("|\')\//', '$1$2=$3' . STD . '/', $out);
            $out = preg_replace('/(<script[^>]+)(src)=("|\')\//', '$1$2=$3' . STD . '/', $out);
            $out = preg_replace('/(<img[^>]+)(src)=("|\')\//', '$1$2=$3' . STD . '/', $out);
            $out = preg_replace('/(<form[^>]+)(action)=("|\')\//', '$1$2=$3' . STD . '/', $out);
        }

        // If $return argument is true, return builded data, else
        if ($return) return $out; else {

            // If $out is a json-encoded data - flush content-type header
            if (preg_match('/^[\[{]/', $out)) header('Content-Type: application/json');

            // Flush output
            iexit($out);
        }
    }

    /**
     * Get menu, accessible for current user
     *
     * @return array
     */
    protected function menu() {

        // Get menu
        $menu = Section::menu(); $this->menuNotices($menu); $this->adjustMenu($menu);

        // Return menu
        return $menu;
    }

    /**
     * Prepare params to be flushed on successful sign-in or on existing session resume
     *
     * @return array
     */
    protected function info() {

        // Return
        return array(
            'std' => STD,
            'com' => COM ? '' : '/admin',
            'pre' => PRE,
            'uri' => Indi::uri()->toArray(),
            'time' => time(),
            'ini' => array(
                'ws' => array_merge((array) Indi::ini('ws'), array('pem' => is_file(DOC . STD . '/core/application/ws.pem'))),
                'demo' => Indi::demo(false)
            ),
            'css' => @file_get_contents(DOC . STD . '/www/css/admin/app.css') ?: '',
            'lang' => $this->lang(),
            'logo' => Indi::ini('general')->logo,
            'title' => Indi::ini('general')->title ?: 'Indi Engine',
            'user' => array(
                'title' => Indi::admin()->title(),
                'uid' => Indi::admin()->profileId . '-' . Indi::admin()->id,
                'role' => Indi::admin()->foreign('profileId')->title,
                'menu' => $this->menu(),
                'auth' => session_id() . ':' . Indi::ini('lang')->admin,
                'dashboard' => Indi::admin()->foreign('profileId')->dashboard ?: false,
                'maxWindows' => Indi::admin()->foreign('profileId')->maxWindows ?: 15,
                'uiedit' => Indi::admin()->uiedit == 'y',
                'isDev' => Indi::admin()->profileId == '1'
            )
        );
    }

    /**
     * Redirect from non-index action to index action, with taking in attention such things as
     * 1. Parentness
     * 2. Primary hash
     * 3. Row index
     * Or redirect to custom location, if $location argument is given
     *
     * @param string $location
     * @param bool $return
     * @return string
     */
    public function redirect($location = '', $return = false){

        // If $location argument is empty
        if (!$location) {

            // Setup parentness
            if (Indi::trail(1)->row) $id = Indi::trail(1)->row->id;

            // Get scope data
            if (Indi::uri()->ph) $scope = $_SESSION['indi']['admin'][Indi::uri('section')][Indi::uri()->ph];

            // Build uri location for redirect
            $location = '/' . Indi::trail()->section->alias  . '/' .
                ($id ? 'index/id/' . $id . '/' : ($scope ? 'index/' : '')) .
                ($scope['upperHash'] ? 'ph/' . $scope['upperHash'] . '/aix/' . $scope['upperAix'] . '/' : '');

        }

        // Redirect
        if ($return) return $location; else jflush(true, array('redirect' => $location));
    }

    /**
     * Toggle current row, and redirect back
     */
    public function toggleAction() {

        // Demo mode
        Indi::demo();

        // Toggle
        Indi::trail()->row->toggle();

        // If 'others' param exists in $_POST, and it's not empty
        if ($otherIdA = ar(Indi::post()->others)) {

            // Unset unallowed values
            foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

            // If $otherIdA array is not empty
            if ($otherIdA) {

                // Fetch rows
                $otherRs = Indi::trail()->model->fetchAll(array('`id` IN (' . im($otherIdA) . ')', Indi::trail()->scope->WHERE));

                // For each row
                foreach ($otherRs as $otherR) $otherR->toggle();

                // Prepare array of selected rows ids
                $scopeRowIdA = $otherIdA; array_unshift($scopeRowIdA, t()->row->id);

                // Apply those
                $this->setScopeRow(false, null, $scopeRowIdA);
            }
        }

        // Redirect
        $this->redirect();
    }

    /**
     * Mark-for-delete current row, and redirect back
     */
    public function m4dAction() {

        // Toggle
        Indi::trail()->row->m4d();

        // If 'others' param exists in $_POST, and it's not empty
        if ($otherIdA = ar(Indi::post()->others)) {

            // Unset unallowed values
            foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

            // If $otherIdA array is not empty
            if ($otherIdA) {

                // Fetch rows
                $otherRs = Indi::trail()->model->fetchAll(array('`id` IN (' . im($otherIdA) . ')', Indi::trail()->scope->WHERE));

                // For each row
                foreach ($otherRs as $otherR) $otherR->m4d();
            }
        }

        // Redirect
        $this->redirect();
    }

    /**
     * Do custom things before saveAction() call
     */
    public function postSave() {

    }

    /**
     * Do custom things after saveAction() call
     */
    public function preSave() {

    }

    /**
     * Do custom things before deleteAction() call
     */
    public function preDelete() {

    }

    /**
     * Do custom things after deleteAction() call
     *
     * @param $deleted
     */
    public function postDelete($deleted) {

    }

    /**
     * Save form data
     *
     * @param bool $redirect
     * @param bool $return
     */
    public function saveAction($redirect = true, $return = false) {

        // Demo mode
        Indi::demo();

        // If 'ref' or 'cell' uri-param given
        if (($ref = Indi::uri()->ref) || $cell = Indi::uri()->cell) {

            // Assign 'ref' it into entry's system props
            $this->row->system('ref', $ref ?: 'rowset');

            // Call onBeforeCellSave(), if need
            if ($cell) $this->onBeforeCellSave($cell, Indi::post()->$cell);
        }

        // Get array of aliases of fields, that are actually represented in database table
        $possibleA = Indi::trail()->model->fields(null, 'columns');

        // Pick values from Indi::post()
        $data = array();
        foreach ($possibleA as $possibleI)
            if (array_key_exists($possibleI, (array) Indi::post()))
                $data[$possibleI] = Indi::post($possibleI);

        // Unset 'move' key from data, because 'move' is a system field, and it's value will be set up automatically
        unset($data['move']);

        // If there was disabled fields defined for current section, we check if default value was additionally set up
        // and if so - assign that default value under that disabled field alias in $data array, or, if default value
        // was not set - drop corresponding key from $data array
        foreach (Indi::trail()->fields as $fieldR)
            if (in($fieldR->mode, 'hidden,readonly'))
                if (!strlen($fieldR->defaultValue) || $this->row->id || $this->row->isModified($fieldR->alias)) unset($data[$fieldR->alias]);
                else $data[$fieldR->alias] = $fieldR->compiled('defaultValue');

        // If current cms user is an alternate, and if there is corresponding field within current entity structure
        if ($this->alternateWHERE() && Indi::admin()->alternate 
            && in($aid = Indi::admin()->alternate . 'Id', $possibleA) && !$this->allowOtherAlternateForSave())

            // Prevent alternate field to be set via POST, as it was already (properly)
            // set at the stage of trail item row initialization
            unset($data[$aid]);

        // Update current row properties with values from $data array
        $this->row->assign($data);

        // If some of the fields are CKEditor-fields, we shoudl check whether they contain '<img>' and other tags
        // having STD injections at the beginning of 'src' or other same-aim html attributes, and if found - trim
        // it, for avoid problems while possible move from STD to non-STD, or other-STD directories
        $this->row->trimSTDfromCKEvalues();

        // Get the aliases of fields, that are file upload fields, and that are not disabled,
        // and are to be some changes applied on
        $filefields = array();
        foreach (Indi::trail()->fields as $fieldR)
            if (!in($fieldR->mode, 'hidden,readonly'))
                if ($fieldR->foreign('elementId')->alias == 'upload')
                    if (preg_match('/^m|d$/', Indi::post($fieldR->alias)) ||
                        preg_match(Indi::rex('url'), Indi::post($fieldR->alias)))
                        $filefields[] = $fieldR->alias;

        // If we're going to save new row - setup $updateAix flag
        if (!$this->row->id) $updateAix = true;

        // Prepare metadata, related to fileupload fields contents modifications
        $this->row->files($filefields);

        // Do pre-save operations
        $this->preSave();

        // Setup 'zeroValue'-mismatches
        /*foreach (Indi::trail()->fields as $fieldR)
            if ($fieldR->mode == 'required' && $this->row->fieldIsZero($fieldR->alias))
                $this->row->mismatch($fieldR->alias, sprintf(I_ROWSAVE_ERROR_VALUE_REQUIRED, $fieldR->title));

        // Flush 'zeroValue'-mismatches
        $this->row->mflush();*/

        // Save the row
        $this->row->save();

        // If current row has been just successfully created
        if ($updateAix && $this->row->id) {

            // Update Indi::uri('aix')
            $this->updateAix($this->row);

            // Update parent id, so nested entries will be mapped under entry, that was just saved
            $_SESSION['indi']['admin']['trail']['parentId'][t()->section->id] = $this->row->id;
        }

        // Setup row index
        $this->setScopeRow();

        // Do post-save operations
        $this->postSave();

        // If 'redirect-url' param exists within post data
        if ($location = Indi::post('redirect-url')) {

            // Get the primary hash either from $location, or from current trail item scope info
            $hash = ($inLocation = preg_match('#/ph/([0-9a-f]+)/#', $location, $matches))
                ? $matches[1]
                : Indi::trail()->scope->hash;

            // Remember the fact that save button was toggled on
            $_SESSION['indi']['admin'][Indi::uri()->section][$hash]['toggledSave'] = true;

            // Chech if $url contains primary hash value
            if ($inLocation) {

                // If it was a new row, that we've just saved
                if (!Indi::uri()->id) {

                    // Increment 'found' scope param
                    $_SESSION['indi']['admin'][Indi::uri()->section][$hash]['found']++;

                    // Replace the null id with id of newly created row
                    $location = str_replace(array('/id/null/', '/id//'), '/id/' . Indi::trail()->row->id . '/', $location);
                    $location = str_replace(array('/aix/null/', '/aix//'), '/aix/' . Indi::uri()->aix . '/', $location);
                    $location = str_replace(array('/parent/null/', '/parent//'), '/parent/' . Indi::trail()->row->id . '/', $location);
                }

            // Replace the null id with id of newly created row
            } else if (!Indi::uri()->id) {

                $location = str_replace(array('/id/null/', '/id//'), '/id/' . Indi::trail()->row->id . '/', $location);
                $location = str_replace(array('/aix/null/', '/aix//'), '/aix/' . Indi::uri()->aix . '/', $location);
                $location = str_replace(array('/parent/null/', '/parent//'), '/parent/' . Indi::trail()->row->id . '/', $location);
            }
        }

        // Prepare response. Here we mention a number of properties, related to saved row, as a proof that row saved ok
        $response = array('title' => $this->row->title(), 'aix' => Indi::uri()->aix, 'id' => $this->row->id);

        // If redirect should be performed, include the location address under 'redirect' key within $response array
        if ($redirect) $response['redirect'] = $this->redirect($location, true);

        // Assign row's grid data into 'affected' key within $response
        $response['affected'] = $this->affected();
        $response['success'] = true;

        // Flush response
        if ($return) return $response; else jflush($response);
    }

    /**
     * Pick fresh values for affected for current row's affected fields
     * and prepare them for being used as a grid-cells replacements
     *
     * @param bool $phantom
     * @return mixed
     */
    public function affected($phantom = false) {

        // Wrap row in a rowset, process it by $this->adjustGridDataRowset(), and unwrap back
        $this->rowset = Indi::trail()->model->createRowset(array('rows' => array($this->row)));
        $this->adjustGridDataRowset();
        $this->row = $this->rowset->at(0);

        // If $phantom arg is true, it means that new phantom entry is going to be added into grid,
        // so here we need to pass ALL grid columns as 1st arg for $this->row->toGridData() call,
        // rather than just affected columns, because entry does not yet exists but may have default values
        // which should be anyway prepared to appear in ExtJS grid panel
        $dataColumns = $phantom ? t()->gridFields->column('alias') : $this->affected4grid();

        // Wrap data entry in an array, process it by $this->adjustGridData(), and uwrap back
        $data = array($this->row->toGridData($dataColumns));
        $this->adjustGridData($data);

        // Adjust grid each data item
        foreach ($data as $idx => &$item) {
            $r = $this->rowset->at($idx);
            $this->adjustGridDataItem($item, $r);
            $this->renderGridDataItem($item, $r);
        }

        // Return affected data, prepared for being displayed
        return array_shift($data);
    }

    /**
     * Override this method in child classes if you need custom props to be included
     * in the process of converting their values to be displayed in the view
     *
     * @return mixed
     */
    public function affected4grid() {

        // Basic affected fields
        $affected = $this->row->affected();

        // If grouping is used, append grouping field to the list
        if ($g = t()->section->foreign('groupBy')) $affected[] = $g->alias;

        // If tile-field is used, append tile-field to the list
        if ($t = t()->section->foreign('tileField')) $affected[] = $t->alias;

        // Return
        return $affected;
    }

    /**
     * Assign Indi::uri()->aix according to given $r instance
     *
     * @param Indi_Db_Table_Row $r
     */
    public function updateAix(Indi_Db_Table_Row $r) {

        // If $scope's WHERE clause is not empty
        if (Indi::trail()->scope->WHERE) {

            // Prepare WHERE clause to ensure that newly created row does match all the requirements, that are
            // used for fetching rows that are suitable for displaying in rowset (grid, calendar, etc) panel
            $where = '`id` = "' . $r->id . '" AND ' . Indi::trail()->scope->WHERE;

            // Do the check
            $R = Indi::trail()->model->fetchRow($where);

            // Else we assume that there are no requirements for current row to be displayed in rowset panel
        } else $R = $r;

        // Here we should do check for row existence, because there can be situation when we have just created
        // a row, but values of some of it's properties do not match the requirements of current scope, and in that
        // case current scope 'aix' and/or 'page' params should not be adjusted
        if ($R) Indi::uri()->aix = Indi::trail()->model
            ->detectOffset(Indi::trail()->scope->WHERE, Indi::trail()->scope->ORDER, $R->id);
    }

    /**
     * Function return a sql-string containing a WHERE clause, that do especially provide an ability to deal with
     * childs-by-parent logic, mean that if current section have parent section, we should fetch only records,
     * related to parent row, for example if we want to see cities, we must define in WHAT country these cities
     * are located
     *
     * @return string|null
     */
    public function parentWHERE() {

        // If current section does not have a parent section, or have, but is a root section - return
        if (!Indi::trail(1)->section->sectionId) return;

        // Force parent WHERE to be 'FALSE' for cases when we're going to browse nested section
        // mapped under non yet existing parent entry, so we prevent `<parent>Id` = "0" clause because
        // there may be some entries in nested section that are actually should not be displayed,
        // e.g. if we're going to display UI with create-form for `country` entry and grid
        // of `city` entries in same UI's south panel, that grid should have NO entries, despite even
        // if there are actually do exist entries in `city` db table having `countryId` = "0"
        if (Indi::uri('action') == 'index' && Indi::uri('id') === '0') return 'FALSE';

        // We check if a non-standard parent connector field name should be used to fetch childs
        // For example, if we have 'Countries' section (displayed rows a fetched from 'country' db table)
        // and 'Cities' section (displayed rows a fetched from 'city' db table) and 'city' table have a column
        // where country identifier of each city is specified, but this column is not named (for some reason)
        // as 'countryId', and we need it to have some another name - so in that cases we use parentSectionConnector
        // logic.
        $connectorAlias = Indi::trail()->section->parentSectionConnector
            ? Indi::trail()->section->foreign('parentSectionConnector')->alias
            : Indi::trail(1)->model->table() . 'Id';

        // Get the connector value. For 'jump' uris, we we are getting it as is - from a certain row's prop
        $connectorValue = Indi::uri('action') == 'index'
            ? Indi::uri('id')
            : (Indi::uri('jump')
                ? Indi::trail()->model->fetchRow('`id` = "' . Indi::uri('id') . '"')->$connectorAlias
                : $_SESSION['indi']['admin']['trail']['parentId'][Indi::trail(1)->section->id]);

        // Connector field shortcut
        $connectorField = t()->model->fields($connectorAlias);

        // Return clause
        $return = $connectorField->storeRelationAbility == 'many'
            ? 'CONCAT(",", `' . $connectorAlias . '`, ",") REGEXP ",(' . im(ar($connectorValue), '|') . '),"'
            : '`' . $connectorAlias . '` = "' . $connectorValue . '"';

        // If connector field - is a field having Variable Entity consider dependency
        if ($connectorField->storeRelationAbility != 'none' && !$connectorField->relation) {
            $eField = $connectorField->nested('consider')->at(0)->foreign('consider')->alias;
            $prepend = '`' . $eField . '` = "' . t(1)->section->entityId . '"';
            $return = '(' . $prepend . ' AND ' . $return . ')';
        }

        // Return clause
        return $return;
    }

    /**
     * Do custom actions configuration adjustments. This can be useful in case then there is a need in
     * some new action, so this function is useful to specify mode and view for that new action/actions
     */
    public function adjustActionCfg() {

    }

    /**
     * This function is an injection that allows to adjust disabled fields before they take effect,
     * so if there is a need to add some fields to the list of disabled, or exclude some - this should
     * be done within this function's body, using appendDisabledField() and excludeDisabledFields() methods
     */
    public function adjustDisabledFields() {

    }

    /**
     * Do auth for selected row, assuming it is a row of `User` model
     */
    public function loginAction() {

        // Force signin for selected user
        if (Indi::trail()->model->table() == 'user')  {

            $_SESSION['user'] = $this->row->toArray();

            // Redirect
            $this->redirect();
        }

        // Check that current model is linked to some role, and if not - flush failure
        if (!Indi::trail()->model->hasRole())
            jflush(false, sprintf('Model "%s" is not linked to any role', Indi::trail()->model->title()));

        // If no username given
        if (!$this->row->email) $data = I_LOGIN_ERROR_ENTER_YOUR_USERNAME;

        // Else if no password given
        else if (!$this->row->password) $data = I_LOGIN_ERROR_ENTER_YOUR_PASSWORD;

        // Else try to find user's data
        else $data = $this->_authLevel1($this->row);

        // If $data is not an array, e.g some error there, output it as json with that error
        if (!is_array($data)) jflush(false, $data);

        // Delete current `realtime` entry, representing the session
        m('Realtime')->row([
            '`token` = "' . session_id() . '"',
            '`type` = "session"',
            'profileId' => $_SESSION['admin']['profileId'],
            'adminId' => $_SESSION['admin']['id'],
        ])->delete();

        // Start a session for user and report that sing-in was ok
        foreach (ar('id,title,email,password,profileId,profileTitle,alternate,mid') as $allowedI)
            $_SESSION['admin'][$allowedI] = $data[$allowedI];

        // Create `realtime` entry having `type` = 'session'
        m('Realtime')->createRow([
            'type' => 'session',
            'profileId' => $_SESSION['admin']['profileId'],
            'adminId' => $_SESSION['admin']['id'],
            'token' => session_id(),
            'langId' => m('Lang')->fetchRow('`alias` = "' . $_COOKIE['i-language'] . '"')->id,
        ], true)->save();

        // Reload main window for new session data to be picked
        jflush(true, array('throwOutMsg' => true));
    }

    /**
     *
     */
    public function logout() {

        // Allow CORS
        header('Access-Control-Allow-Headers: x-requested-with, indi-auth');
        header('Access-Control-Allow-Origin: *');

        // Unset language
        setcookie('i-language', null);

        // Unset session
        if ($_SESSION['admin']['id'])  unset($_SESSION['admin'], $_SESSION['indi']['admin']);

        // If `realtime` entry of `type` = "session" exists
        if ($_ = m('Realtime')->fetchRow([
            '`type` = "session"',
            '`token` = "' . session_id() . '"'
        ])) {

            // Refresh other tabs
            foreach($channelA = Indi::db()->query('
                SELECT `token` 
                FROM `realtime` 
                WHERE 1
                  AND `type` = "channel" 
                  AND `realtimeId` = "' . $_->id . '" 
                  AND `token` != "' . CID . '"
            ')->fetchAll(PDO::FETCH_COLUMN) as $channel)

                // Reload tabs
                Indi::ws([
                    'type' => 'F5',
                    'to' => $channel
                ]);

            // Delete `realtime` entry
            $_->delete();
        }

        // Flush basic info
        if (APP) jflush(true, array(
            'std' => STD,
            'com' => COM ? '' : '/admin',
            'pre' => PRE,
            'uri' => Indi::uri()->toArray(),
            'title' => Indi::ini('general')->title ?: 'Indi Engine',
            'throwOutMsg' => $_SESSION['indi']['throwOutMsg'],
            'lang' => $this->lang(),
            'logo' => Indi::ini('general')->logo
        ));

        // Else redirect
        else iexit('<script>window.location.replace("' . PRE . '/")</script>');
    }

    /**
     * Default 'print' action
     */
    public function printAction() {
        Indi::trail()->view->mode = 'view';
    }

    /**
     * Call the desired action method
     */
    public function call($action) {

        // If no trail - call action and return
        if (!Indi::trail(true)) return $this->{$action . 'Action'}();

        // Adjust access rights
        $this->adjustAccess();

        // If we're operating in single-row mode
        if ($row = Indi::trail()->row) {

            // Adjust trailing row access with no attention to whether is existing or new
            $this->adjustTrailingRowAccess($row);

            // Adjust trailing row access with attention to whether is existing or new
            $this->{$row->id ? 'adjustExistingRowAccess' : 'adjustCreatingRowAccess'}($row);

        // Else if we're operating in multi-row mode - adjust rowset access
        } else $this->adjustRowsetAccess();

        // If only row creation is allowed, but now we deal with existing row - prevent it from being saved
        if (Indi::trail()->section->disableAdd == 2 && Indi::trail()->row->id) $this->deny('save');

        // If action was not excluded from the list of allowed actions
        if (Indi::trail()->actions->select($action, 'alias')->at(0)) {

            // Call that action it
            $this->{$action . 'Action'}();

            // If new entry is going to be created via grid rather than via form - flush entry template
            if ($action == 'form' && !t()->row->id && Indi::uri()->phantom)
                jflush(array('success' => true, 'phantom' => $this->affected(true)));

        // Else flush an error message
        } else {
        
            // Get title
            $title = Indi::model('Action')->fetchRow('`alias` = "' . $action . '"')->title;
            
            // Get reason
            foreach ($this->_deny as $actions => $reason)
                if (in($action, $actions))
                    break;
        
            // Flush msg
            jflush(false, $reason ?: sprintf(I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES, $title));
        }
    }

    /**
     * Empty function. To be overridden in child classes, if there is a need to adjust
     * access mode consider to certain circumstances
     */
    public function adjustAccess() {

    }

    /**
     * Empty function. To be overridden in child classes, if there is a need to adjust
     * trail object's properties before any operation to be performed
     */
    public function adjustTrail() {

    }

    /**
     * Deny actions, enumerated within $actions argument, from being called
     *
     * @param $actions
     * @param string $msg
     */
    public function deny($actions, $msg = '') {

        // Convert $actions arg to an array
        $actions = ar($actions);

        // If 'create' action is in the list of actions to be denied
        if (in('create', $actions)) {

            // Setup current section's `disableAdd` property as 1
            Indi::trail()->section->disableAdd = 1;

            // If we deal with a new row - ensure it wouldn't be saved, and creation form wouldn't even be displayed
            if (Indi::trail()->row && !Indi::trail()->row->id) {
                $actions[] = 'save';
                $actions[] = 'form';
            }
        }

        // Apply exclusions to the list of allowed actions
        Indi::trail()->actions->exclude($actions, 'alias');
        
        // Remember the reason, if given
        if ($msg) $this->_deny[im($actions)] = $msg;
    }

    /**
     * This function is used in case if user wants to jump from to some location (within the interface), that is
     * represented by a section, that can't be accessed via menu, and can only be accessed by step-by-step
     * navigation to a certain level of deepness within the system. For example, we have countries, regions, and cities
     * sections and each of them is a subsection within the previous. Usually, if user wants to go to the list of cities
     * within 'Region 1', he should click on the 'Countries' menu item at first, then select 'Country 1' in the list of
     * countries, then click 'Regions' (so all regions within 'Country 1' are displayed), then select 'Region 1',
     * then click 'Cities' - and only after that user will be able to view all cities within 'Region 1'. At all points
     * within this navigation history, system is remembering each list's parameters, such as sorting, index of a row for
     * 'Country 1' and 'Region 1' within the list they were displayed', and a number of other params, for ability,
     * for example, to highlight 'Country 1' if user occasionally will return back to countries list. But if user wants
     * to go directly to cities list within 'Region 1', system doesn't remembering all mentioned bread-crumb trail params,
     * because it usually do it separately at each of all those navigation steps, that are all skipped in our case. So,
     * this function allows to simulate these steps wouldn't be skipped
     *
     * @return mixed
     */
    public function jump() {

        // If uri has no 'jump' param - return
        if (!Indi::uri('jump')) return;

        // Backup current trail's items
        $ti = Indi_Trail_Admin::$items;

        // Get the navigation steps
        $nav = Indi::trail(true)->nav();

        // Set up $_GET 'jump' param, that will tell controllers to perform only preDispatch() call
        Indi::get('jump', 1); $ph = ''; $aix = '';

        // Walk through hierarchy, setup scopes for each step as if user would manually navigate to each uri subsequently
        for ($i = 0; $i < count($nav); $i++) {

            // If $_GLOBALS['cmsOnlyMode'] is not `true` - prepend $nav[$i] with '/admin'
            if (!COM) $nav[$i] = '/admin' . $nav[$i];

            // Append primary hash and row index (none of them will be appended at first-iteration)
            $nav[$i] .=  $ph . $aix;

            // If current step is not a rowset step, and is the last step
            if (!preg_match('~/index/~', $nav[$i]) && $i == count($nav) - 1) {

                // Remove 'jump' param from $_GET
                Indi::get('jump', null);

                // Replace '/id//' and '/aix//' width '/'
                $to = preg_replace(':/(id|aix)//:', '/', array_pop($nav));

                // Now we have proper (containing `ph` and `aix` params) uri, so we dispatch it
                jflush(true, array('redirect' => $to));

            // Simulate as if rowset panel was loaded
            } else Indi::uri()->dispatch($nav[$i]);

            // Setup sorting
            if (Indi::trail()->section->defaultSortField)
                Indi::get('sort', json_encode(array(array(
                    'property' => Indi::trail()->section->foreign('defaultSortField')->alias,
                    'direction' => Indi::trail()->section->defaultSortDirection
                ))));

            // Simulate as if rowset data was loaded into rowset panel. This provide
            // Indi::trail()->scope's fulfilness with `found` and `ORDER` properties
            //if (preg_match('~/index/~', $nav[$i])) Indi::uri()->dispatch($nav[$i]. 'format/json/');

            // Get the id of a row, that we will be simulating navigation
            // to subsection, there that row's nested entries are located
            preg_match('~/id/([0-9]+)/~', $nav[$i+1], $m); $id = $m[1];

            // If next url (within $nav) is related to non-same section
            if ($ti[$i+1]) {

                // Get row index
                $aix = $ti[$i+1]->model->detectOffset(Indi::trail()->scope->WHERE, Indi::trail()->scope->ORDER, $id);

                // Use the primary hash and row index for building corresponding parts of the uri for the next iteration
                $ph = 'ph/' . Indi::trail()->scope->hash . '/';
                $aix = 'aix/' . $aix . '/';
            }
        }

        // Remove 'jump' param from $_GET
        Indi::get('jump', null);

        // Now we have proper (containing `ph` and `aix` params) uri, so we dispatch it
        jflush(true, array('redirect' => array_pop($nav)));
    }

    /**
     * This function is for adjusting params of a scope, identified by it's section and hash
     *
     * @return mixed
     */
    public function adjustCertainScope() {

        // If no `forScope` param exists within request data - return
        if (!$apply = Indi::post('forScope')) return;

        // If try to json-decode the value of the $apply variable has no success - return
        if (!$apply = json_decode($apply, true)) return;

        // If $apply object does not have either `section` or `hash` properties - return
        if (!$apply['section'] || !$apply['hash']) return;

        // If there is no such a scope - return
        if (!$_SESSION['indi']['admin'][$apply['section']][$apply['hash']]) return;

        // Preliminary unset 'section' and 'hash' params from $merge array, before merging with $_SESSION[...]
        $merge = $apply; unset($merge['section'], $merge['hash']);

        // Adjust scope, according to params
        $_SESSION['indi']['admin'][$apply['section']][$apply['hash']] = array_merge(
            $_SESSION['indi']['admin'][$apply['section']][$apply['hash']],
            $merge
        );
    }

    /**
     * This methos sets up a special read/write access mode,
     * so that only new entries creation is allowed, but modification of existing entries
     * is restricted. If $ownerCheck arg is given as non-false then this method will perform and additional
     * owner-check, so this will prevent existing entries
     * from being modified by users who are not their creators
     *
     * @param bool $ownerCheck
     * @return mixed
     */
    public function createOnly($ownerCheck = false) {

        // If entries creation is disabled - do nothing
        if (Indi::trail()->section->disableAdd) return;

        // If we do not deal with an existing row - do nothing
        if (!Indi::trail()->row->id) return;

        // If current entry belongs to current system user- do nothing
        if ($ownerCheck)

            // If belonging mode is represented with a pairof special 'authorType' and 'authorId' fields
            if (Indi::trail()->model->fields('authorId') && Indi::trail()->model->fields('authorType')) {

                // If current entry does not belongto current system user - return
                if (Indi::trail()->row->authorId == Indi::me('id') && Indi::trail()->row->authorType == Indi::me('mid'))
                    return;

            // Else if belonging mode is represented by 'alternate' concept, and current system user is an alternate
            } else if (Indi::admin()->alternate && Indi::trail()->model->fields($af = Indi::admin()->alternate(t()->model->table()))) {

                // If current entry does not belongto current system user - return
                if (in(Indi::admin()->id, $this->row->$af)) return;
            
            // Else if there is no any kind of belonging
            } else return;

        // Deny 'save' and 'delete' actions
        $this->deny('save');

        // Setup special value for section's `disableAdd` prop, indicating that creation is still possible
        Indi::trail()->section->disableAdd = 2;
    }

    /**
     * Empty function. To be overridden in child classes
     *
     * @param Indi_Db_Table_Row $row
     */
    public function adjustTrailingRowAccess(Indi_Db_Table_Row $row) {

    }

    /**
     * Empty function. To be overridden in child classes
     *
     * @param Indi_Db_Table_Row $row
     */
    public function adjustCreatingRowAccess(Indi_Db_Table_Row $row) {

    }

    /**
     * Empty function. To be overridden in child classes
     *
     * @param Indi_Db_Table_Row $row
     */
    public function adjustExistingRowAccess(Indi_Db_Table_Row $row) {
        
    }

    /**
     * Empty function. To be overridden on child classes
     *
     * @param $menu
     * @return mixed
     */
    public function adjustMenu(&$menu) {

    }

    /**
     * @param $menu
     * @return mixed|void
     */
    public function menuNotices(&$menu) {

        // Get array of ids of 1st-level sections
        $sectionIdA = array_column($menu, 'id');

        // If no 'Notice' entity found - return
        if (!Indi::model('NoticeGetter', true) || !Indi::model('NoticeGetter')->fields('criteriaRelyOn')) return;

        // Get ids of relyOnGetter-notices, that should be used to setup menu-qty counters for current user's menu
        $noticeIdA_relyOnGetter = Indi::db()->query('
            SELECT `noticeId`
            FROM `noticeGetter`
            WHERE 1
              AND `criteriaRelyOn` = "getter"
              AND `profileId` = "' . Indi::admin()->profileId . '"
              AND `toggle` = "y"
        ')->fetchAll(PDO::FETCH_COLUMN);

        // Get ids of relyOnEvent-notices, that should be used to setup menu-qty counters for current user's menu
        $noticeIdA_relyOnEvent = Indi::db()->query('
            SELECT `noticeId`, `criteriaEvt`
            FROM `noticeGetter`
            WHERE 1
              AND `criteriaRelyOn` = "event"
              AND `profileId` = "' . Indi::admin()->profileId . '"
              AND `toggle` = "y"
        ')->fetchAll(PDO::FETCH_KEY_PAIR);

        // Remove relyOnEvent-notices having criteria that current user/getter not match
        foreach ($noticeIdA_relyOnEvent as $noticeId => $criteriaEvt)
            if ($criteriaEvt && !Indi::admin()->model()->fetchRow('`id` = "' . Indi::admin()->id . '" AND ' . $criteriaEvt))
                unset($noticeIdA_relyOnEvent[$noticeId]);
                $noticeIdA_relyOnEvent = array_keys($noticeIdA_relyOnEvent);

        // Get notices
        $_noticeRs = Indi::model('Notice')->fetchAll(array(
            'FIND_IN_SET("' . Indi::admin()->profileId . '", `profileId`)',
            'CONCAT(",", `sectionId`, ",") REGEXP ",(' . im($sectionIdA, '|') . '),"',
            'FIND_IN_SET(`id`, IF(`qtyDiffRelyOn` = "event", "' . im($noticeIdA_relyOnEvent) . '", "' . im($noticeIdA_relyOnGetter) . '"))',
            '`toggle` = "y"'
        ));

        // If no notices - return
        if (!$_noticeRs->count()) return;

        // Qtys array, containing quantities of rows, matched for each notice, per each section/menu-item
        $qtyA = array();

        // Foreach notice
        foreach ($_noticeRs as $_noticeR) {

            // Get qty
            $_noticeR->qty = Indi::db()->query('
                SELECT COUNT(`id`)
                FROM `' . Indi::model($_noticeR->entityId)->table().'`
                WHERE ' . $_noticeR->compiled('qtySql')
            )->fetchColumn();

            // Collect qtys for each sections
            foreach (ar($_noticeR->sectionId) as $sectionId)
                $qtyA[$sectionId][] = array(
                    'qty' => $_noticeR->qty ?: 0,
                    'id' => $_noticeR->id,
                    'bg' => $_noticeR->colorHex('bg'),
                    'fg' => $_noticeR->colorHex('fg'),
                    'tip' => $_noticeR->tooltip
                );
        }

        // Foreach menu item
        foreach ($menu as &$item) {

            // If $item relates to 0-level section, or is not linked to some entity - return
            if (!$qtyA[$item['id']]) continue;

            // Append each qty to menu item's title
            foreach ($qtyA[$item['id']] as $qtyI)
                $item['title'] .= '<span class="menu-qty menu-qty-' . $qtyI['id'] . '"'
                    . '" style="' . ($qtyI['bg'] ? 'background: ' . $qtyI['bg'] : '') . '; color: ' . ($qtyI['fg'] ?: 'initial') . ';'
                    . ($qtyI['qty'] ? '' : 'display: none')
                    . '" data-qtip="' . $qtyI['tip'] . '" data-qtip-side="right">' . $qtyI['qty'] . '</span>';
        }
    }

    /**
     * Set $this->_isRowsetSeparate prop
     */
    public function _isRowsetSeparate() {

        // Setup shortcut
        $mode = Indi::trail()->section->rowsetSeparate;

        // If mode feature is not yet implemented - set it as 'auto'
        if (!$mode) $mode = 'auto';

        // If mode is 'auto'
        if ($mode == 'auto') {

            // If current section is a first-level section - set data to be loaded via separate request
            if (Indi::trail()->level == 1) $mode = 'yes';

            // Else if grid columns count is more than 10 - set data to be loaded via separate request
            else if (Indi::trail()->gridFields->count() > 10) $mode = 'yes';
        }

        // Set trail section's `rowsetSeparate` prop` and _isRowsetSeparate` flag
        $this->_isRowsetSeparate = ((Indi::trail()->section->rowsetSeparate = $mode) == 'yes');
    }

    /**
     * Include additional model's properties into response json, representing rowset data
     *
     * @param $propS string|array Comma-separated prop names (e.g. field aliases)
     * @param array $ctor
     * @return mixed
     */
    public function inclGridProp($propS, $ctor = array()) {

        // Get fields
        $fieldRs = $this->callParent();

        // Call patent
        if (Indi::trail()->grid)
            foreach ($fieldRs as $fieldR)
                Indi::trail()->grid->append(array_merge(array(
                    'fieldId' => $fieldR->id,
                    'gridId' => 0
                ), $ctor));

        // Return
        return $fieldRs;
    }

    /**
     * Include additional model's properties into response json, representing rowset data
     *
     * @param $propS string|array Comma-separated prop names (e.g. field aliases)
     * @return string
     */
    public function exclGridProp($propS) {

        // Call parent
        $fieldIds = $this->callParent();

        // Exclude grid columns
        if (Indi::trail()->grid) Indi::trail()->grid->exclude($fieldIds, 'fieldId');

        // Return
        return $fieldIds;
    }

    /**
     * Adjust columns, before they will be exported.
     * Function has empty body here, but it can be overridden in child classes,
     * in cases when, for example, there will be a need to prevent certain columns from being exported
     *
     * @param array $columnA
     */
    public function adjustExportColumns(&$columnA = array()) {

    }

    /**
     *
     */
    public function callAction() {

        // Check that 'sipnet' section exists within loaded configuration file
        if (!$sipnet = Indi::ini('sipnet')) jflush(false, 'No "sipnet" section found in config.ini');

        // Check other required configuration options
        foreach (ar('url,uid,pwd') as $key) if (!$sipnet->$key)
            jflush(false, 'No "' . $key . '" key found within "sipnet" section in config.ini');

        // Basic params
        $_paramA = array(
            'sipuid' => $sipnet->uid,
            'password' => $sipnet->pwd,
            'format' => '2',
            'lang' => Indi::ini('lang')->admin,
            'operation' => 'balance'
        );

        // If we're only going to check given phone number validity - skip balance detection
        if (!Indi::get()->count()) {

            // Make 'balance' request and decode json
            $out = file_get_contents($sipnet->url . '?' . http_build_query($_paramA));
            $out = json_decode($out, true);

            // If there was something wrong - flush error
            if (!$out['Result']) jflush($out['Result'], 'SIPNET: ' . $out['ErrorStr']);

            // Pass balance info
            Indi::trail()->data['sipnet']['balance'] = $out;
        }

        // If 'phone' key exists within $_POST
        if (array_key_exists('phone', Indi::post())) {

            // Check that phone
            jcheck(array('phone' => array('req' => true)), Indi::post());
        
            // Pick that phone
            $phone = Indi::post('phone');
        
        // Else
        } else {
        
            // Check entry's phone
            jcheck(array('phone' => array('req' => true)), array('phone' => $this->row->clientPhone));
            
            // Pick that phone
            $phone = $this->row->clientPhone;
        }
        
        // Strip non-numeric characters
        $phone = preg_replace('~[^0-9]~', '', $phone);

        //
        if (array_key_exists('pricing', Indi::get())) {

            // Prepare params for getting price per minute
            $paramA = array(
                'operation' => 'getphoneprice',
                'Phone' => $phone,
            ) + $_paramA;

            // Get price per minute
            $pricing = file_get_contents($sipnet->url . '?' . http_build_query($paramA));
            $pricing = json_decode($pricing, true);

            // If we were only going to check given phone number pricing - flush pricing info
            jflush(true, $pricing);
        }

        // Pass pricing info
        Indi::trail()->data['sipnet']['pricing'] = $pricing ?: array();

        // Pass demo phone
        if (Indi::demo(false) || true) Indi::trail()->data['sipnet']['demophone'] = $sipnet->demophone;

        // If no 'make' $_GET key given - return, so if there is pricing error - it will be picked by dialer UI
        if (!array_key_exists('make', Indi::get())) return;

        /*$paramA = array(
            'operation' => 'webbuttons',
            'Phone' => $phone
        ) + $_paramA;

        $button = file_get_contents($url . '?' . http_build_query($paramA));
        $button = json_decode($button, true);
        i($button, 'a');*/

        // Params for new button's request
        $paramA = array(
            'operation' => 'addwebbutton',
            'phone' => $phone
        ) + $_paramA;

        // Get button json
        $button = Indi::demo(false) || true
            ? '{"success":true,"Result":true,"mbaccount":"--","cid":"'. $sipnet->democid . '"}'
            : file_get_contents($sipnet->url . '?' . http_build_query($paramA));

        // Decode button json
        $button = json_decode($button, true);

        // Flush button details
        jflush($button['Result'], $button['ErrorStr'] ?: $button);
    }

    /**
     * Show confirmation prompt
     *
     * @param $msg
     * @param string $buttons OKCANCEL, YESNO, YESNOCANCEL
     * @param string|null $cancelMsg Msg, that will be shown in case if 'Cancel'
     *                    button was pressed or confirmation window was closed
     */
    public function confirm($msg, $buttons = 'OKCANCEL', $cancelMsg = null) {

        // Get answer
        $answer = Indi::get()->{'answer' . rif(Indi::$answer, count(Indi::$answer) + 1)};

        // If no answer, flush confirmation prompt
        if (!$answer) jconfirm(is_array($msg) ? im($msg, '<br>') : $msg, $buttons);

        // If answer is 'cancel' - stop request processing
        else if ($answer == 'cancel') jflush(false, $cancelMsg);

        // Return answer
        return Indi::$answer[count(Indi::$answer)] = $answer;
    }

    /**
     * Show prompt with additional fields
     *
     * @param $msg
     * @param array $cfg
     * @return mixed
     */
    public function prompt($msg, $cfg = array()) {

        // Answer index
        $answerIdx = rif(Indi::$answer, count(Indi::$answer) + 1);

        // Get answer
        $answer = Indi::get()->{'answer' . $answerIdx};

        // Build meta
        $meta = array(); foreach($cfg as $field) $meta[$field['name']] = $field;
        
        // If no answer, flush confirmation prompt
        if (!$answer) jprompt($msg, $cfg);

        // If answer is 'cancel' - stop request processing
        else if ($answer == 'cancel') jflush(false);

        // Push answer
        Indi::$answer[count(Indi::$answer)] = $answer;

        // Return prompt data
        return json_decode(Indi::post('_prompt' . $answerIdx), true) + array('_meta' => $meta);
    }

    /**
     * Empty function. Can be overridden in child classes for cases when there is a need to
     * show some confirmation prompt before new cell value will be saved
     */
    public function onBeforeCellSave($cell, $value) {

    }

    /**
     * Remember width usage.
     *
     * Pick grid columns width usage from $_POST and save those widths
     * separately for each grid column, so next time grid columns will use
     * that saved width to prevent re-calculation and improve client-side performance
     */
    public function rwuAction() {

        // Check request data for 'widthUsage' prop, that should be json
        $_ = jcheck(array(
            'widthUsage' => array(
                'req' => true,
                'rex' => 'json'
            )
        ), Indi::post());

        // Foreach key-value pair within $_['widthUsage']
        foreach ($_['widthUsage'] as $gridId => $width) {

            // If no such grid column - skip
            if (!$gridR = t()->section->nested('grid')->gb($gridId)) continue;

            // Set `width`
            $gridR->width = $width;

            // Save
            $gridR->save();
        }

        // Flush success
        jflush(true, 'OK');
    }

    /**
     * Convert $_GET['filter'], which is in {param1: value1, param2: value2} format,
     * into $_GET['search'] format (e.g. [{param1: value1}, {param2: value2}])
     *
     * @return mixed
     */
    protected function _filter2search() {

        // If $_GET['filter'] is not an array - return
        if (!is_array($filter = Indi::get()->filter)) return;

        // Convert filter values format
        // from {param1: value1, param2: value2}
        // to [{param1: value1}, {param2: value2}]
        $search = array(); foreach ($filter as $param => $value) $search []= array($param => $value);

        // Json-encode and return
        return json_encode($search);
    }

    /**
     * Empty function, to be overridden in child classes.
     * Can be useful for switching on/off or doing another changes in grid columns, filters, actions, etc
     */
    public function adjustRowsetAccess() {

    }
    
    /**
     *
     */
    public function allowOtherAlternateForSave() {
        return false;
    }

    /**
     * Get lang info
     */
    public function lang() {

        // If was set up previously - return as is
        if (Indi::view()->lang) return Indi::view()->lang;

        // Get available languages
        $langA = Indi::db()->query('SELECT `alias`, `title`, `toggle` FROM `lang` WHERE `toggle` = "y" ORDER BY `move`')->fetchAll();

        // Get default/current language
        $lang = in($_COOKIE['lang'], array_column($langA, 'alias')) ? $_COOKIE['lang'] : Indi::ini('lang')->admin;

        // Get all languages' versions for 4 constants
        foreach ($langA as &$langI) {

            // Declare
            $langI['const'] = array();

            // Foreach dir
            foreach (ar('core,www') as $dir) {

                // Build filename of a php-file, containing l10n constants
                $l10n_file = DOC . STD . '/' . $dir . '/application/lang/admin/' . $langI['alias'] . '.php';

                // If no file - skip
                if (!file_exists($l10n_file)) continue;

                // If emtpy file - skip
                if (!$php = file_get_contents($l10n_file)) continue;

                // Collect all-languages versions of small number of constants, required for loginbox
                foreach (array(
                     'I_LOGIN_BOX_USERNAME',
                     'I_LOGIN_BOX_PASSWORD',
                     'I_LOGIN_BOX_ENTER',
                     'I_LOGIN_ERROR_MSGBOX_TITLE',
                     'I_MSG',
                     'I_ERROR'
                ) as $const) if (preg_match('~define\(\'' . $const . '\', \'(.*?)\'\);~', $php, $m))
                    $langI['const'][$const] = $m[1];

                // Collect all l10n constants for a default/current language
                if ($langI['alias'] == $lang && Indi::admin(true)) {
                    $const = Indi::rexma('~define\(\'(.*?)\', ?\'(.*?)\'\);~', $php);
                    foreach ($const[2] as &$value) $value = stripslashes($value);
                    $l10n = array_combine($const[1], $const[2]) + ($l10n ?: array());
                }
            }
        }

        // Setup list of possible translations and current/last chosen one
        return Indi::view()->lang = array('odata' => $langA, 'name' => $lang) + ($l10n ?: array());
    }

    /**
     * Create `realtime` entry having `type` = "context", if not yet created, and assign `scope` and `entries` props
     *
     * @param $scope
     */
    public function createContextIfNeed($scope) {

        // Prevent `realtime` entry from being created in case of excel-export
        if (Indi::uri()->format == 'excel') return;

        // Track involved entries
        if ($_ = m('realtime')->fetchRow([
            '`type` = "context"',
            '`token` = "' . t()->bid() . '"',
            '`realtimeId` = "' . m('realtime')->fetchRow('`token` = "' . CID . '"')->id . '"'
        ]) ?: t()->context()) $_->assign([
            'entries' => $this->rowset->column('id', ','),
            'scope' => json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT)
        ])->save();
    }

    /**
     * Delete `realtime` entry having `type` = "context", if $_POST['destroy'] is given
     */
    public function deleteContextIfNeed() {

        // If $_POST['destroy'] is only given
        if ((count(Indi::post()) == 1) && ($ctx = Indi::post('destroy'))) {

            // Check $token format and CID format
            jcheck([
                'ctx' => ['rex' => 'ctx'],
                'cid' => ['rex' => 'wskey']
            ], ['ctx' => $ctx, 'cid' => CID]);

            // If websocket-channel is known - remove context
            if (CID
                && ($chl = m('realtime')->row(['`type` = "channel"', '`token` = "' . CID . '"']))
                && ($ctx = m('realtime')->row(['`type` = "context"', '`token` = "' . $ctx . '"', '`realtimeId` = "' . $chl->id . '"']))
            ) {

                // Delete context
                $ctx->delete();

                // Flush success
                jflush(true);
            }

            // Flush success if realtime is turned Off, else flush failure
            jflush(!Indi::ini('ws')->realtime);
        }
    }
}