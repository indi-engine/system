<?php
class Indi_Db_Table
{
    /**
     * Store the id of entity, related to current model
     *
     * @var string
     */
    protected $_id = 0;

    /**
     * Store the name of database table, related to current model
     *
     * @var string
     */
    protected $_table = '';

    /**
     * Store the title of current model
     *
     * @var string
     */
    protected $_title = '';

    /**
     * Flag, indicating that this model instances may be used as an access accounts
     *
     * @var boolean
     */
    protected $_hasRole = false;

    /**
     * Entity fraction. Possible values: 'system', 'custom' and 'public'
     * or array containing info for l10n-fraction detection
     *
     * @var string|array
     */
    protected $_fraction = null;

    /**
     * Flag, indicating that this model instances was preloaded and stored within Indi_Db::$_preloaded[$entity]
     *
     * @var boolean
     */
    protected $_preload = false;

    /**
     * Id of field, that is used as title-field
     *
     * @var boolean
     */
    protected $_titleFieldId = 0;

    /**
     * Id of field, that is used for grouping files into subdirs
     *
     * @var boolean
     */
    protected $_filesGroupBy = 0;

    /**
     * If set to true, child entries will be deleted by InnoDB internally,
     * so that those won't be bin-logged and therefore not captured by Maxwell
     * replication client, and this means onBeforeDelete(), onDelete() and _afterDelete()
     * methods will NOT be called and such children deletion won't be reflected in UI
     * in the realtime manner. Also, only native ON DELETE = 'SET NULL' / 'CASCADE' rule will be
     * respected, and non-native (e.g. enumset- and multi-entity) references will be NOT.
     *
     * This flag is intended for cases when the above implications ARE NOT a problem,
     * e.g. there are only references indentical to mysql native ones, and onBeforeDelete() and onDelete()
     * methods are empty and no fileupload fields, and if so, the deletion would be much performant then
     *
     * Currently this flag is set to true for queueTask-entity
     *
     * @var
     */
    protected $_nativeCascade = false;

    /**
     * Store array of [foreignKeyField => ON DELETE RULE] pairs,
     * that have foreign key constraints defined on mysql-level
     *
     * @var array
     */
    protected $_ibfk = [];

    /**
     * Foreign keys pointing from any entities to this entity, grouped by their onDelete-rule
     *
     * Example: let's assume this entity is a `country`
     *
     * Array (
     *   [CASCADE] => Array (
             [123] => Array (
     *           [table] => 'city'
     *           [column] => 'countryId'
     *           [multi] => false
     *       )
     *   ),
     *   [SET NULL] => ...
     *   [RESTRICT] => ...
     *
     *  In the example above 123 is the id of the `field`-entry for `city`.`coutnryId`
     * @var array
     */
    protected $_refs = [];

    /**
     * Store array of fields, that current model consists from
     *
     * @var array
     */
    protected $_fields = [];

    /**
     * Store column name, which is used to detect parent-child relationship between
     * rows within rowset
     *
     * @var string
     */
    protected $_treeColumn = '';

    /**
     * Store array of aliases, related to fields, that can contain evaluable php expressions.
     *
     * @var array
     */
    protected $_evalFields = [];

    /**
     * Store array of aliases, related to fields, that are fileupload fields.
     *
     * @var array
     */
    protected $_fileFields = null;

    /**
     * Store array of aliases, related to fields, that are mapped to columns of type SET in database
     *
     * @var array
     */
    protected $_setFields = null;

    /**
     * Scheme of how any instance of current model/entity can be used as a 'space' within the calendar/schedule
     *
     * @var array
     */
    protected $_space = null;

    /**
     *
     * @var Indi_Db_Table_Rowset|array
     */
    protected $_notices = [];

    /**
     * Class name for row
     *
     * @var string
     */
    protected $_rowClass = 'Indi_Db_Table_Row';

    /**
     * Class name for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Indi_Db_Table_Rowset';

    /**
     * Changelog config. Example:
     *
     * protected $_changeLog = [
     *      'toggle' => 'all',
     *      'except' => 'ignoredField1,ignoredField2,etc'
     * ];
     *
     * protected $_changeLog = [
     *      'toggle' => 'none',
     *      'except' => 'loggedField1,loggedField2,etc'
     * ];
     *
     * @var array
     */
    protected $_changeLog = [
        'toggle' => 'none',
        'except' => ''
    ];

    /**
     * Daily time. This can be used to setup working hours, for example since '10:00:00 until '20:00:00'.
     * If daily times are set, schedule will auto-create busy spaces within each separate 24h-hour period,
     * so, if take the above example, periods from 00:00:00 till 10:00:00 and from 20:00:00 till 00:00:00
     * will be set as busy spaces
     *
     * @var array
     */
    protected $_daily = [
        'since' => false,
        'until' => false
    ];

    /**
     * Info about qties and sums, that current entity entries are counted in
     *
     * @var array
     */
    protected $_inQtySum = [];

    /**
     * Detect L10n-fraction for a given *_Row instance, if given
     *
     * @param Indi_Db_Table_Row $r
     */
    public function fraction($r = null) {

        // If no $r arg given - return just current value of
        if (!$r) return $this->_fraction;

        // If it's a custom entity
        if ($this->_fraction == 'custom') return 'adminCustomData';

        // Else if fraction is not defined - assume it's 'adminSystemUi'
        if ($this->_fraction === null || $this->_fraction == 'system') return 'adminSystemUi';

        // Else if values defined for both 'field' and 'value' keys within $this->_fraction
        if ($this->_fraction['value']) return $this->_fraction['value'][$r->{$this->_fraction['field']}];

        // Else if value defined only for 'field' key - go upper
        return $r->foreign($this->_fraction['field'])->fraction();
    }

    /**
     * Construct the instance - setup table name, fields, and tree column if exists
     *
     * @param array $config
     */
    public function __construct($config) {

        // Set db table name and db adapter
        $this->_id = $config['id'];

        // Set db table name and db adapter
        $this->_table = strtolower(substr(get_class($this),0,1)) . substr(get_class($this),1);

        // Set fields
        $this->_fields = $config['fields'];

        // Set array of [foreignKeyField => ON DELETE RULE] pairs
        $this->_ibfk = $config['ibfk'];

        // Set array of references
        $this->_refs = $config['refs'];

        // Set info about sums and quantities where instances of current entity should be counted in
        $this->_inQtySum = $config['inQtySum'];

        // Set notices
        if (isset($config['notices'])) $this->_notices = $config['notices'];

        // Set title
        $this->_title = $config['title'];

        // Detect tree column name
        $this->_treeColumn = $config['fields']->field($this->_table . 'Id') ? $this->_table . 'Id' : '';

        // Setup title field id
        $this->_titleFieldId = $config['titleFieldId'] ?: 0;

        // Setup filesGroupBy field id
        $this->_filesGroupBy = $config['filesGroupBy'] ?: 0;

        // Setup 'hasRole' flag
        $this->_hasRole = $config['hasRole'];

        // Setup changeLog config
        if (isset($config['changeLog'])) $this->_changeLog = $config['changeLog'];

        // Setup 'fraction' prop
        $this->_fraction = is_array($this->_fraction)
            ? ['basic' => $config['fraction']] + $this->_fraction
            : $config['fraction'];

        // Setup 'preload' flag to be false by default
        $this->_preload = false;

        // Setup 'spaceScheme' prop
        $this->_space = $config['space'];

        // If spaceScheme is not 'none'
        if ($this->_space['scheme'] != 'none') {

            // Setup space-coord fields and their rules
            $this->_space['fields']['coords'] = $this->_spaceCoords();

            // Setup space-owner fields and their rules
            $this->_space['fields']['owners'] = $this->_spaceOwners();

            // Setup consider-fields (and their rules) for all space-owner fields
            $this->_space['fields']['relyOn'] = $this->_spaceOwnersRelyOn();

            // Setup fields having ['auto' => true] rule
            $this->_space['fields']['auto'] = $this->_spaceOwnersAuto();

            // Setup fields, responsible for colors
            $this->_space['fields']['colors'] = $this->_spaceColors();

            // If current space-scheme assumes that there is a duration-field
            if ($frameCoord = Indi::rexm('~(minuteQty|dayQty|timespan)~', $this->_space['scheme'], 1)) {

                // Get date-picker coord name
                $dpickCoord = Indi::rexm('~(date|datetime)~', $this->_space['scheme'], 1);

                // Collect field->zeroValue pairs for all space-fields
                foreach (array_keys(
                     $this->_space['fields']['coords']
                     + $this->_space['fields']['owners']
                     + $this->_space['fields']['relyOn']) as $field)
                    $change[$field] = $this->fields($field)->zeroValue();

                // Setup event->field pairs. This info will be used in javascript for:
                // 1.Binding a listeners on 'change' even on all involved fields
                // 2.Binding a listener on 'afterrender' event on duration-field, because
                //   we need to initially setup disabled options for all involved fields, to prevent
                //   user from selecting values leading to events overlapping. But overlapping
                //   problem appears only if we have a duration-field within space's scheme, so
                //   if we do have such field - we need to do a request, and that's why it's
                //   rationally to do such request on behalf on duration-field
                // 3.Binding a listener on 'boundchange' event on datepicker-field, because user
                //   may change datepicker's month to any other month, so the collection of
                //   disabled dates within datepicker's calendar widget - should also be refreshed
                $this->_space['fields']['events'] = [
                    'change' => $change,
                    'afterrender' => $this->_space['coords'][$frameCoord],
                    'boundchange' => $this->_space['coords'][$dpickCoord],
                ];
            }
        }
    }

    /**
     * Alias for $this->fetchAll()
     *
     * @param null $where
     * @param null $order
     * @param null $count
     * @param null $page
     * @param null $offset
     * @param null $split
     * @param bool $pgupLast
     * @return Indi_Db_Table_Rowset
     */
    public function all($where = null, $order = null, $count = null, $page = null, $offset = null, $split = null, $pgupLast = false) {

        // If $where arg is a comma-separated list of integers - assume we have to use '`id` IN (...)' expr
        if (is_string($where) && strlen($where) && Indi::rexm('int11list', $where))
            $where = "`id` IN ($where)";

        // Do fetch
        return $this->fetchAll($where, $order, $count, $page, $offset, $split, $pgupLast);
    }

    /**
     * Fetches all rows, according the given criteria
     *
     * @param null|string|array $where
     * @param null|string|array $order
     * @param null|int $count
     * @param null|int $page
     * @param null|int $offset
     * @param bool $split
     * @param bool $pgupLast If `true` is given, previous page's last entry will be captured by decrementing OFFSET and incrementing LIMIT by 1
     * @return Indi_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $page = null, $offset = null, $split = null, $pgupLast = false) {
        // Build WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, [null, '']))) $where = implode(' AND ', $where);
        if (is_array($order) && count($order = un($order, [null, '']))) $order = implode(', ', $order);

        // Build LIMIT clause
        if ($count !== null || $page !== null) {
            $offset = (is_null($page) ? ($count ? 0 : $page) : $count * ($page - 1)) + ($offset ? $offset : 0);

            // If $pgupLast flag is true, and desired page is 2nd or more
            if ($page > 1 && $pgupLast) {

                // Shift $offset back by 1 and $count front by 1 to capture previous page's last entry
                $offset --; $count ++;

            // Else set $pgupLast flag to false, as if it even was given as true it does make sense only if $page > 1
            } else if ($pgupLast) $pgupLast = false;

            if ($offset < 0) {
                $count -= abs($offset);
                $offset = 0;
            }
            $div = $split ? $this->enumset($split)->count() : 1;
            $limit = ($offset / $div) . ($count ? ',' : '') . ($count / $div);

            // the SQL_CALC_FOUND_ROWS flag
            if (!is_null($page) || !is_null($count)) $calcFoundRows = 'SQL_CALC_FOUND_ROWS ';
        } else {
            $limit = $pgupLast = false;
        }

        // Build the query
        $sql = 'SELECT ' . ($limit ? $calcFoundRows : '') . '* FROM `' . $this->_table . '`'
            . ($where ? ' WHERE ' . $where : '')
            . ($order ? ' ORDER BY ' . $order : '')
            . ($limit ? ' LIMIT ' . $limit : '');

        // If $split arg is given - prepare UNION query chunks
        if ($split) {
            $union = [];
            foreach ($this->enumset($split) as $idx => $enumsetR) {
                $sw = '`' . $split .'` = "' . $enumsetR->alias . '"';
                $su = $where 
                    ? preg_replace('~WHERE ~', '$0 ' . $sw . ' AND ', $sql)
                    : preg_replace('ORDER BY', 'WHERE ' . $sw . ' $0');
                $su = preg_replace('~SQL_CALC_FOUND_ROWS ~', '', $su);
                $union []= $su;
            }
            $sql = '(' . im($union, ') UNION (') . ')';
        }

        // Fetch data
        $data = db()->query($sql)->fetchAll();

        // Prepare data for Indi_Db_Table_Rowset object construction
        $data = [
            'table'   => $this->_table,
            'data' => $data,
            'rowClass' => $this->_rowClass,
            'found'=> $limit ? $this->_found($where, $union) : count($data),
            'page' => $page,
            'pgupLast' => $pgupLast,
            'query' => $sql
        ];

        // Return Indi_Db_Table_Rowset object
        return new $this->_rowsetClass($data);
    }

    /**
     * Redeclare this function in child classes if you need custom logic of how total found rows should be detected.
     * The case that was a reason of why this function was added is that FOUND_ROWS() works (somewhy) not well when
     * query is executed against VIEW, and WHERE clause refer to column(s) that are JOINed by the VIEW declaration
     *
     * @param $where
     * @return array|int|string
     */
    protected function _found($where = '', $union = null) {
        
        // If $union arg given
        if ($union) {
            
            // Foreach SELECT query calc found rows
            foreach ($union as $select) {
                $select = preg_replace('~\* FROM~', 'COUNT(*) FROM', $select);
                $select = preg_replace('~ LIMIT [0-9,]+$~', '', $select);
                $found[] = (int) db()->query($select)->cell();
            }
            
            // Use max
            return max($found);
        }
        
        // Default logic
        return db()->query('SELECT FOUND_ROWS()')->cell();
    }

    /**
     * Get rowset as tree
     *
     * @param null|array|string $where
     * @param null|array|string $order
     * @param null|int $count
     * @param null|int $page
     * @param int $parentId
     * @param int $selected
     * @param null|string $keyword
     * @param bool $offsetDetection
     * @return Indi_Db_Table_Rowset object
     */
    public function fetchTree($where = null, $order = null, $count = null, $page = null, $parentId = 0, $selected = 0, $keyword = null, $offsetDetection = false, $pgupLast = false) {

        // Get raw tree
        $tree = $this->fetchRawTree($order, $where);

        // If we have WHERE clause, we extract values from $tree handled with keys 'tree', 'found' and 'disabledA'
        if ($where) {
            extract($tree);

            // Else we just set $found
        } else {
            $found = count($tree);
        }

        // If we have to deal a keyword search clause we have different behaviour
        if ($keyword) {

            // Get the title column
            $titleColumn = $this->titleColumn();

            // Check if keyword is a part of color value in format #rrggbb, and if so, we use RLIKE mysql command instead
            // of LIKE, and prepare a special regular expression
            if (preg_match('/^#[0-9a-fA-F]{0,6}$/', $keyword)) {
                $rlike = '^[0-9]{3}' . $keyword . '[0-9a-fA-F]{' . (7 - mb_strlen($keyword, 'utf-8')) . '}$';
                $where[] = '`' . $titleColumn . '` RLIKE "' . $rlike . '"';

            // Else
            } else $where[] = ($keyword2 = str_replace('"', '\"', Indi::kl($keyword)))
                ? '(`' . $titleColumn . '` LIKE "' . str_replace('"', '\"', $keyword) . '%" OR `' . $titleColumn . '` LIKE "' . $keyword2 . '%")'
                : '`' . $titleColumn . '` LIKE "' . str_replace('"', '\"', $keyword) . '%"';

            // Fetch rows that match $where clause, ant set foundRows
            $foundRs = $this->all($where, $order, $count, $page);
            $found = $foundRs->found();
            $foundA = $foundRs->toArray();

            // We replace indexes with actual ids in $foundA array
            $tmp = []; foreach ($foundA as $foundI) {
                $tmp[$foundI['id']] = $foundI;
                unset($foundI);
            }
            $foundA = $tmp;

            // Release memory
            unset($foundRs);

            // Remaining branch counter
            $counter = 0;

            // Array of ids. Rows with that ids should be presented in final results.
            // There rows are - needed rows, found by primary search, and all parents rows for each
            // of needed, up to level 0
            foreach ($foundA as $currentId => $foundI) {
                do {
                    // Counter increment. We do it for having total number of branches
                    // that will be displayed. We will use this number to check if
                    // all needed ids are already got, so there is no more need to
                    // contunue walking through tree. This check will be performed
                    // within the process of getting final list of needed ids
                    // Also, we perform additonal check before $counter increment,
                    // because we need count of unique branches ids, because search results
                    // may have same parents
                    if (!$tree[$currentId][2]) $counter++;

                    // We mark branch of global tree with 2 ways:
                    // If current branch is a result of primary search, we mark it with 1 at index 2
                    // Else if it is a one of the parent branches - we mark it with 2 at index 2
                    // This need because in the results (grid rows or dropdown items) we should visually separate
                    // results of primary search and their parents, because parents should not be clickable,
                    // or have other abilities, because they are NOT a results actually, they are displaying
                    // just for visual recognition of results of primary search, and recognition of their parents
                    // Also, we should use integer indexation instead of string (eg $tree[$currentId][2],
                    // not $tree[$currentId]['mark']) because size of trees can be very large, and we should
                    // do all this things using a way, that use mininum memory
                    $tree[$currentId][2] = $foundA[$currentId] ? 1 : 2;

                    // Remember indents
                    $indents[$currentId] = indent($tree[$currentId][1]);
                } while ($currentId = $tree[$currentId][0]);
            }

            // Get the final list of needed ids
            $i = 0;
            $ids = [];
            $disabledA = [];
            foreach ($tree as $id => $info) {
                if ($info[2]) {

                    // Collect all (primary and auxiliary) results rows ids
                    $ids[] = $id;
                    $i++;

                    // Remember id of rows that should be disabled (auxiliary results)
                    if ($info[2] == 2) $disabledA[] = $id;

                    // Break loop if known count of ids is already got
                    if ($i == $counter) break;
                }
            }

        // Standard behaviour
        } else {

            // If $selected argument is specified, we should return page of results, containing that selected branch,
            // so we should calculate needed page number, and replace $page argument with calculated value
            // Also, while retrieving upper and lower page (than page with selected vaue) results, we use $selected
            // argument as start point for distance and scope calculations
            if ($selected && ($found > Indi_Db_Table_Row::$comboOptionsVisibleCount || $offsetDetection)){

                // Get index of selected branch in raw tree
                $i = 0;
                foreach ($tree as $id => $info) {
                    if ($id == $selected)  {
                        $start = $i + ($page ? $page * $count: 0);
                        $selectedIndex = $i;

                        // If we are trying to retrieve upper pages (upper than initial page containing selected value) of results
                        // we need to remember start point, that would be if we would like to get previous page results.
                        // Previous mean that is a one page upper than page with selected value.
                        // We will need this 'previous' start point to properly calculate 'current' start and end point shifts
                        // regarding to possibility of some options to be disabled
                        if ($page < 0) {
                            $prevStart = $start + $count;
                        }
                        break;
                    }
                    unset($id, $info);
                    $i++;
                }

                // Here we calculate $shiftUp, that will be used to adjust page start and end points for 'current' page
                if ($page < 0) {
                    $k = 0;
                    $shiftUp = 0;
                    foreach ($tree as $id => $info) {
                        // Bottom border of range of page results
                        if ($k < $i) {
                            // Top border of range of page results
                            if ($k >= $prevStart) {
                                if ($disabledA[$id]) {
                                    $shiftUp++;
                                }
                            }
                            unset($id, $info);
                        } else break;
                        $k++;
                    }
                }
            }

            // Get list of ids, related to current page of results
            if (isset($start)) {
                $end = $start + $count;
            } else {
                if ($count !== null || $page !== null) {
                    $start = (is_null($page) ? 0 : $count * ($page - 1));
                    $end = $start + $count;
                } else {
                    $start = 0;
                    $end = count($tree);
                }
            }

            $ids = [];
            $i = 0;

            // Declare ids history
            $idsHistory = [];

            foreach ($tree as $id => $info) {
                // Push in idsHistory
                $idsHistory[$i] = $id;

                // Bottom border of range of page results
                if ($i < $end) {

                    // Top border of range of page results
                    if ($i >= $start) {

                        // If we were doing pageUp, and while retrieving results we faced disabled options
                        // we should simulate $start decremention. One disabled = one additional shift upper of start point,
                        // so we will shift start point upper until we face non-disabled option. The reason of this that we
                        // need to get certain number of NON-DISABLED options, not certain number of NOT-MATTER-DISABLED-OR-ENABLED
                        // options
                        if ($page < 0 && $disabledA[$id]) {
                            $ids = array_reverse($ids);
                            do {
                                $start--;
                                $prevId = $idsHistory[$start];
                                $ids[] = $prevId;
                                $level[$prevId] = $tree[$prevId][1];
                            } while ($disabledA[$prevId]);
                            $ids = array_reverse($ids);
                        }

                        // Normal appending
                        $ids[] = $id;
                        $level[$id] = $tree[$id][1];

                        // We shift end point because disabled items should be ignored
                        if ($disabledA[$id] && (is_null($page) || $page > 0)) $end++;

                        // If we have not yet reached start point but faced a disabled option
                        // we shift both start and end points because disabled items should be ignored
                        // and start and end points of page range should be calculated with taking in attention
                        // about disabled options.
                    } else if ($disabledA[$id] && (is_null($page) || $page > 0)) {
                        if (!$selected || $i >= $selectedIndex) {
                            $start++;
                            $end++;
                        }
                    }
                    $i++;
                } else {
                    unset($idsHistory);
                    break;
                }
                unset($id, $info);
            }
        }

        // If $pgupLast flag is true, and desired page is 2nd or more
        if ($page > 1 && $pgupLast && count($ids)) {

            // Get properly ORDER-ed ids array
            $treeKeyA = array_keys($tree);

            // Get index-of-total for first id
            $treeIdxA = array_flip($treeKeyA);

            // Get index-of-total for first id
            $firstIdx = $treeIdxA[$ids[0]];

            // If $treeKeyA array has prev item - prepend it to $ids array, else set $pgupLast to false
            if (isset($treeKeyA[$firstIdx - 1])) array_unshift($ids, $treeKeyA[$firstIdx - 1]); else $pgupLast = false;

        // Else set $pgupLast flag to false, as if it even was given as true it does make sense only if $page > 1
        } else if ($pgupLast) $pgupLast = false;

        // Construct a WHERE and ORDER clauses for getting that particular
        // page of results, get it, and setup nesting level indents
        $wo = 'FIND_IN_SET(`id`, "' . implode(',', $ids) . '")';
        $rowset = $this->all($wo, $wo);
        $sql = $rowset->query();
        $data = $rowset->toArray();
        $assocDataA = [];
        for ($i = 0; $i < count($data); $i++) {
            $assocDataI = $data[$i];
            $assocDataI['_system']['level'] = $level[$data[$i]['id']];
            $assocDataA[$data[$i]['id']] = $assocDataI;
        }
        $data = $assocDataA;
        unset($assocDataA);

        // Set 'disabled' system property for results that should have such a property
        if (is_array($disabledA)) {
            // Here we replace $disabledA values with it's keys, as we have no more need
            // to store info about disabled in array keys instead of store it in array values
            // We need to do this replacement only if we are not running keyword-search, because
            // if we are, disabled array is already filled with ids as values, not keys
            if (!$keyword) $disabledA = array_keys($disabledA);

            // We setup 'disabled' property only for rows, which are to be returned
            foreach ($disabledA as $disabledI) if ($data[$disabledI]) $data[$disabledI]['_system']['disabled'] = true;
        }

        // Set 'parentId' system property. Despite of existence of parent branch identifier in list of properties,
        // we additionally set up this property as system property using static 'parentId' key, (e.g $row->system('parentId'))
        // instead of using $row->{$row->model()->treeColumn()} expression. Also, this will be useful in javascript, where
        // we have no ability to use such an expressions.
        foreach ($data as $id => $props) $data[$id]['_system']['parentId'] = $props[$this->_treeColumn];

        // Setup rowset info
        $data = [
            'table' => $this->_table,
            'data' => array_values($data),
            'rowClass' => $this->_rowClass,
            'found' => $found,
            'page' => $page,
            'pgupLast' => $pgupLast,
            'query' => $sql
        ];

        // Return rowset/offset
        return $offsetDetection ? $start : new $this->_rowsetClass($data);
    }

    /**
     * Fetches a full tree of items, but it will
     * retrieve only `id` and `treeColumn` columns
     *
     * @param null|string|array $order
     * @param null|string|array $where
     * @return array
     */
    public function fetchRawTree($order = null, $where = null) {

        // ORDER clause
        if (is_array($order) && count($order = un($order, null))) $order = implode(', ', $order);

        // Get tree column name
        $tc = $this->_table . 'Id';

        // Construct sql query
        $query = "SELECT `id`, IFNULL(`$tc`, 0) AS `$tc` FROM `{$this->_table}`";

        // If $where arg contains 'important' key - use it's value as WHERE clause to prevent
        // fetching whole tree. This can be useful when in case we need certain part of tree
        if (is_array($where) && isset($where['important'])) $query .= ' WHERE ' . $where['important'];

        // Use $order arg as ORDER clause, if given
        $query .= rif($order, ' ORDER BY $1');

        // Get general tree data for whole table, but only `id` and `treeColumn` columns
        $tree = db()->query($query)->fetchAll();
        $nested = [];
        foreach ($tree as $item) {
            $nested[$item[$tc]][] = $item;
            unset($item);
        }

        // Release memory
        unset($tree);

        // Re-setup tree
        $tree = $this->_append(0, [], $nested, 0);

        // Release memory
        unset($nested);

        // Then we get an associative array, where keys are ids, and values are arrays containing from parent ids and levels
        $return = []; for ($i = 0; $i < count($tree); $i++) $return[$tree[$i]['id']] = [$tree[$i][$tc], $tree[$i]['level']];

        // Release memory
        unset($tree);

        // If we have WHERE clause, we should filter tree so there should remain only needed branches
        if ($where) {

            // Needed branches can be two categories:
            // 1. Branches that directly match WHERE clause (primary results)
            // 2. Branches that do not, but that are parent to branches mentioned in point 1 (disabled results)

            // First we should find primary results
            $primary = [];
            if (is_array($where) && count($where = un($where, null))) $where = implode(' AND ', $where);
            $foundA = db()->query('SELECT `id` FROM `' . $this->_table . '` WHERE ' . $where)->fetchAll();
            foreach ($foundA as $foundI) {
                $primary[$foundI['id']] = true;
                unset($foundI);
            }

            // Get found rows
            $found = count($primary);

            // Then we should find disabled results
            $disabled = [];
            foreach ($primary as $id => $true) {
                $parentId = $return[$id][0];
                while ($parentId) {
                    // We mark branch as disabled only if it is not primary
                    if (!$primary[$parentId]) {
                        $disabled[$parentId] = true;
                    }
                    $parentId = $return[$parentId][0];
                }
                unset($id, $true);
            }

            // Get final tree
            $tmp = [];
            foreach ($return as $id => $data) if ($primary[$id] || $disabled[$id]) {
                $tmp[$id] = $data;
                unset($id, $data);
            }

            // Release memory
            unset($return, $primary);

            // Return array(data, foundRows)
            return ['tree' => $tmp, 'found' => $found, 'disabledA' => $disabled];
        } else {

            // Return array
            return $return;
        }

    }

    /**
     * Recursively create a rows tree
     *
     * @param $parentId
     * @param $data
     * @param $nested
     * @param int $level
     * @param bool $recursive
     * @return mixed
     */
    protected function _append($parentId, $data, $nested, $level = 0, $recursive = true) {
        if (is_array($nested[$parentId])) foreach ($nested[$parentId] as $item) {
            $item['level'] = $level;
            $id = $item['id'];
            $data[] = $item;
            unset($item);
            if ($recursive) $data = $this->_append($id, $data, $nested, $level + 1);
        }
        return $data;
    }

    /**
     * Determine a database table column name, that should be used
     * as a title-column for usage in all combos and all other places there proper title is used
     *
     * @return string
     */
    public function titleColumn() {

        // If current entity has a non-zero `titleFieldId` property
        if ($titleFieldR = $this->titleField()) {

            // If title-field doesn't store foreign keys - set value of $column
            // variable as alias of title-field, else set it as 'title', as current title concept assumes
            // that if entity has a non-zero titleFieldId, and field, that titleFieldId is pointing to - is
            // foreign key - it mean that there was a physical `title` field and column created within that
            // entity
            return $titleFieldR->storeRelationAbility == 'none' ? $titleFieldR->alias : 'title';

        // Else if current entity has no non-zero value for `titleFieldId` property
        } else {

            // If current entity have field with alias 'title' - set 'title' as
            // the value of $column variable, else set value of $column as 'id'
            return $this->fields('title') ? 'title' : 'id';
        }
    }

    /**
     * Detect index of certain row in a ordered scope of rows. Offset is 1-based, unlike mysql OFFSET
     *
     * @param $where
     * @param $order
     * @param $id
     * @return int
     */
    public function detectOffset($where, $order, $id) {

        // Prepare WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, null))) $where = implode(' AND ', $where);
        if (is_array($order) && count($order = un($order, null))) $order = implode(', ', $order);

        // If current model is a tree - use special approach for offset detection
        if ($this->treeColumn()) return ($this->fetchTree($where, $order, 1, null, null, $id, null, true) + 1) . '';

        // Offset variable
        db()->query('SET @o=0;');

        // Random temporary table name. We should ensure that there will be no table with such name
        $tmpTableName = 'offset' . rand(2000, 8000);

        // We are using a temporary table to place data into it, and the get of offset
        db()->query($sql = '
            CREATE TEMPORARY TABLE `' . $tmpTableName . '`
            SELECT @o:=@o+1 AS `offset`, `id`="' . $id . '" AS `found`
            FROM `' . $this->_table .'`'
                . ($where ? ' WHERE ' . $where : '')
                . ($order ? ' ORDER BY ' . $order : '')
        );

        // Get the offset
        $offset = db()->query('
            SELECT `offset`
            FROM `' . $tmpTableName . '`
            WHERE `found` = "1"'
        )->cell();

        // Unset offset variable
        db()->query('SET @o=null;');

        // Truncate temporary table
        db()->query('DROP TABLE `' . $tmpTableName . '`');

        // Return
        return $offset;
    }

    /**
     * Provide readonly access to _evalFields property.
     * If $evalField argument is given - function will return boolean true or false, depends on whether or not
     * $evalField is within list of eval fields
     *
     * @param string $evalField
     * @return array|bool
     */
    public function getEvalFields($evalField = null) {
        return $evalField ? in_array($evalField, $this->_evalFields) : $this->_evalFields;
    }

    /**
     * Provide write access to _evalFields property.
     * If $evalField argument is given - function will return boolean true or false, depends on whether or not
     * $evalField is within list of eval fields
     *
     * @param array $evalFields
     * @return array
     */
    public function setEvalFields($evalFields = []) {
        return $this->_evalFields = $evalFields;
    }

    /**
     * Provide readonly access to _fileFields property.
     * If $fileField argument is given - function will return boolean true or false, depends on whether or not
     * $fileField is within list of file fields
     *
     * @param string $fileField
     * @return array|bool
     */
    public function getFileFields($fileField = null) {

        // Setup $this->_fileFields property, if it wasn't yet
        if ($this->_fileFields === null) $this->_fileFields
            = $this->fields()->select(element('upload')->id, 'elementId')->column('alias');

        // Do the job
        return $fileField ? in_array($fileField, $this->_fileFields) : $this->_fileFields;
    }

    /**
     * Provide readonly access to _setFields property.
     * If $setField argument is given - function will return boolean true or false, depends on whether or not
     * $setField is within list of SET-fields
     *
     * @param string $setField
     * @return array|bool
     */
    public function getSetFields($setField = null) {

        // Setup $this->_setFields property, if it wasn't yet
        if ($this->_setFields === null) $this->_setFields
            = $this->fields()->select(coltype('SET')->id, 'columnTypeId')->column('alias');

        // Do the job
        return $setField ? in_array($setField, $this->_setFields) : $this->_setFields;
    }

    /**
     * Return incremented by 1 maximum value of `move` column within current database table
     *
     * @return int
     */
    public function getNextMove() {
        return $this->row('`move` != "0"', '`move` DESC')->move + 1;
    }

    /**
     * Return row of certain field, related to current entity, if $name argument is specified
     * and contains only one field name. If $names argument contains comma-separated field names,
     * function will return a Indi_Db_Table_Rowset object, containing all found fields
     * or return all fields within current model. Second argument - $format, is applied only if $names argument
     * is empty|null. If $format = 'columns', array of field aliases will be returned, else if $format = 'rowset',
     * fields info will be returned as Indi_Db_Table_Rowset object, else if format = 'rowset', the results will be
     * returned as a rowset
     *
     * @param string $names
     * @param string $format rowset|cols
     * @return Field_Row|Field_Rowset|array
     */
    public function fields($names = '', $format = 'rowset') {

        // If $name argument was not given
        if ($names == '') {

            // If $format argument == 'rowset' - return all fields as Indi_Db_Table_Rowset object
            if ($format == 'rowset') return $this->_fields;

            // Else if $format == 'aliases' - return all fields aliases as array
            else if ($format == 'aliases') return $this->_fields->column('alias');

            // Else if $format == 'columns', return only aliases for fields,
            // that are represented by database table columns
            else if ($format == 'columns') {

                // Declare array for columns
                $columnA = [];

                // For each field check whether it have columnTypeId != 0, and if so, append field alias to columns array
                foreach ($this->_fields as $field) if ($field->columnTypeId) $columnA[] = $field->alias;

                // Return columns array
                return $columnA;
            }

        // Else if $name argument is presented, and it contains only one field name or field id
        } else if (((is_string($names) && !preg_match('/,/', $names)) || is_int($names)) && func_num_args() == 1) {

            // Return certain field as Indi_Db_Table_Row object, if found
            return $this->_fields->field($names);

        // Else if $name argument contains several field names
        } else {

            // Get them as a rowset, if $format argument is 'rowset'
            if ($format == 'rowset') return $this->_fields->select($names, 'alias');

            // Else if $format is set to 'cols', array if field aliases will be returned
            else if ($format == 'aliases') return $this->_fields->select($names, 'alias')->column('alias');
        }
    }

    /**
     * Return array containing some basic info about model. Currently it is only database table name
     *
     * @param bool $object Whether or not return value should be an instance of stdClass instead of array
     * @return array
     */
    public function toArray($object = false) {
        $array['table'] = $this->_table;
        $array['title'] = $this->_title;
        $array['titleFieldId'] = $this->_titleFieldId;
        $array['space'] = $this->_space;
        if ($this->_space['fields'])
            foreach (ar('owners,coords,relyOn') as $group)
                $array['space']['fields'][$group]
                    = array_keys($array['space']['fields'][$group]);
        $array['daily'] = $this->_daily;
        return $object ? (object) l10n_dataI($array, 'title') : l10n_dataI($array, 'title');
    }

    /**
     * Return an array consisting of all values of a single $column column from the result set
     *
     * @param $column
     * @param null|string|array $where
     * @param null|string|array $order
     * @param null|int $count
     * @param null|int $page
     * @param null|int $offset
     * @return array
     */
    public function fetchColumn($column, $where = null, $order = null, $count = null, $page = null, $offset = null) {

        // Build WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, null))) $where = implode(' AND ', $where);
        if (is_array($order) && count($order = un($order, null))) $order = implode(', ', $order);

        // Build LIMIT clause
        if ($count !== null || $page !== null) {
            $offset = (is_null($page) ? ($count ? 0 : $page) : $count * ($page - 1)) + ($offset ? $offset : 0);
            if ($offset < 0) {
                $count -= abs($offset);
                $offset = 0;
            }
            $limit = $offset . ($count ? ',' : '') . $count;

        } else {
            $limit = false;
        }

        // Build the query
        $sql = 'SELECT `' . $column . '` FROM `' . $this->_table . '`'
            . ($where ? ' WHERE ' . $where : '')
            . ($order ? ' ORDER BY ' . $order : '')
            . ($limit ? ' LIMIT ' . $limit : '');

        // Fetch and return result
        return $data = db()->query($sql)->col();
    }

    /**
     * Return the name of database table, related to current model
     *
     * @return string
     */
    public function table($base = false) {
        return $base && $this->_baseTable ? $this->_baseTable : $this->_table;
    }

    /**
     * Check whether this entity is based on VIEW rather that TABLE
     *
     * @return bool
     */
    public function isVIEW() {

        // Database name
        $dn = ini()->db->name;

        // Check whether it's a mysql VIEW
        return !!db()->query("
            SHOW FULL TABLES IN `$dn` WHERE `TABLE_TYPE` LIKE 'view' AND `Tables_in_$dn` LIKE '{$this->_table}'
        ")->cell();
    }

    /**
     * Fetches one row in an object of type Indi_Db_Table_Row,
     * or returns null if no row matches the specified criteria.
     *
     * @param null|array|string $where
     * @param null|array|string $order
     * @param null|int $offset
     * @return null|Indi_Db_Table_Row object
     */
    public function fetchRow($where = null, $order = null, $offset = null) {
        // Build WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, null))) $where = implode(' AND ', $where);
        else if (preg_match('~^[0-9]+$~', $where)) $where = '`id` = "' . $where . '"';
        if (is_array($order) && count($order = un($order, null))) $order = implode(', ', $order);

        // If we are trying to get row by offset, and current model is a tree - use special approach
        if ($offset !== null && $this->treeColumn())
            return $this->fetchTree($where, $order, 1, $offset + 1)->current();

        // Else use usual approach
        else {
            $data = db()->query($sql =
                'SELECT * FROM `' . $this->_table . '`' .
                rif(strlen($where), ' WHERE ' . $where) .
                rif($order, ' ORDER BY ' . $order) .
                ' LIMIT ' . rif($offset, $offset . ',') . '1'
            )->fetch();
        }

        // Build query, fetch row and return it as an Indi_Db_Table_Row object
        if ($data) {

            // Release memory
            unset($where, $order, $offset);

            // Prepare data for Indi_Db_Table_Row object construction
            $constructData = [
                'table'    => $this->_table,
                'original' => $this->ibfkNullTo0($data),
            ];

            // Release memory
            unset($data);

            // Load class if need
            if (!class_exists($this->_rowClass)) {
                require_once 'Indi/Loader.php';
                Indi_Loader::loadClass($this->_rowClass);
            }

            // Construct and return Indi_Db_Table_Row object
            return new $this->_rowClass($constructData);
        }

        // NULL return
        return null;
    }

    /**
     * Covert nulls to 0 for columns having foreign key constraints defined at mysql-level.
     * We do this due to that Indi Engine does not support nulls for foreign keys on both php- and js-level so far
     *
     * @param array $data
     */
    public function ibfkNullTo0($data) {

        // Foreach prop having foreign key constraint
        foreach ($this->_ibfk as $field => $DELETE_RULE)

            // If it's value is null
            if ($data[$field] === null)

                // Convert to 0
                $data[$field] = 0;

        // Return
        return $data;
    }

    /**
     * Create *_Row instance based on raw change event data, received from maxwell daemon
     */
    public function maxwell(array $event) {

        // Implode values for SET-columns, as they're given as arrays by maxwell daemon
        foreach ($this->getSetFields() as $setField)
            if (array_key_exists($setField, $event['data']))
                $event['data'][$setField] = implode(',', $event['data'][$setField]);

        // If new row was created
        if ($event['type'] == 'insert') {

            // Setup row instance
            $row = $this->new();

            // Setup localized value within $row->_language, recognized within given raw data, and return
            // data containing values for certain (current) language only in case of localized field
            $modified = $row->l10n($event['data']);

            // Setup modified data
            $row->modified($modified);

        // Else
        } else {

            // If $event['old'] is not given - make sure it to be empty array,
            // but if not - implode values for SET-columns, as they're given as arrays by maxwell daemon
            if ($old = $event['old'] ?? [])
                foreach ($this->getSetFields() as $setField)
                    if (array_key_exists($setField, $old))
                        $old[$setField] = implode(',', $old[$setField]);

            // Prepare original data
            $original = array_merge($event['data'], $old);

            // Prepare modified data
            $modified = [];
            foreach ($old as $prop => $value)
                $modified[$prop] = $event['data'][$prop];

            // Create Indi_Db_Table_Row instance
            /** @var Indi_Db_Table_Row $row */
            $row = new $this->_rowClass([
                'table'    => $this->_table,
                'original' => $original,
                'modified' => $modified
            ]);

            // Foreach localized prop
            if ($modified) foreach ($row->language() as $prop => $byLang_original) {

                // If value of localized prop was modified
                if (array_key_exists($prop, $modified)) {

                    // If modified value of localized prop is json-decodable
                    if (is_array($byLang_modified = json_decode($modified[$prop], true))) {

                        // Get language, that value was changed at least for
                        $lang = key(array_diff_assoc($byLang_original, $byLang_modified));

                        // As long as Indi_Db_Table_Row's both _original and _modified arrays contain
                        // values only for a single certain language (e.g. for localized fields),
                        // we need to spoof values in both _original and _modified for the first language
                        // for which we detected changed value, because otherwise it may happen that
                        // if ini()->lang->admin is not the same as for which the value was changed,
                        // it will result in that no change is detected, so no aftermaths are triggered
                        $row->original($prop, $byLang_original[$lang]);
                        $row->modified($prop, $byLang_modified[$lang]);

                        // Spoof _language[$prop] in a whole, to handle cases when values for more than 1 languages
                        // were changed on the database level, so all subscribers should receive updates even if they're
                        // logged using different languages
                        $row->language($prop, $byLang_modified);
                    }
                }
            }
        }

        // Prepare and send updates to subscribers
        $row->maxwell($event['type'], $event['xid']);
    }

    /**
     * Alias for fetchRow()
     */
    public function row($where = null, $order = null, $offset = null) {
        return $this->fetchRow($where, $order, $offset);
    }

    /**
     * Create empty row. If non-false $assign argument is given - we assume that $input arg should not be used
     * be used for construction, but should be used for $this->set() call. This may me useful
     * in case when we need to create an instance of a row and assign a values into it - and all
     * this within a single call. So, without $assign arg usage, the desired effect would require:
     *
     *   m('SomeModel')->new()->set(['prop1' => 'value1', 'prop2' => 'value2']);
     *
     * But not, with $assign arg usage, same effect would require
     *
     *   m('SomeModel')->new(['prop1' => 'value1', 'prop2' => 'value2']);
     *
     * So, with $assign arg usage, we can omit the additional 'assign(..)' call
     *
     * @param array $input
     * @param bool $assign
     * @return Indi_Db_Table_Row
     */
    public function createRow($input = [], $assign = false) {

        // If non-false $assign argument is given - we assume that $input arg should not be used
        // be used for construction, but should be used for $this->set() call
        if ($assign) { $assign = is_object($input) ? (array) $input : $input; $input = []; }

        // Prepare data for construction
        $constructData = [
            'table'   => $this->_table,
            'original'     => is_array($input['original']) ? $input['original'] : [],
            'modified' => is_array($input['modified']) ? $input['modified'] : [],
            'system' => is_array($input['system']) ? $input['system'] : [],
            'temporary' => is_array($input['temporary']) ? $input['temporary'] : [],
            'foreign' => is_array($input['foreign']) ? $input['foreign'] : [],
            'nested' => is_array($input['nested']) ? $input['nested'] : [],
        ];

        // If $constructData['original'] is an empty array, we setup it according to model structure
        if (count($constructData['original']) == 0) {
            $constructData['original']['id'] = null;
            foreach ($this->fields() as $fieldR)
                if ($fieldR->columnTypeId)
                    $constructData['original'][$fieldR->alias] = $fieldR->defaultValue;
        }

        // Get row class name
        $rowClass = $this->rowClass();

        // Load row class if need
        if (!class_exists($rowClass)) {
            require_once 'Indi/Loader.php';
            Indi_Loader::loadClass($rowClass);
        }

        // Create an instance of a row
        $row = new $rowClass($constructData);

        // Compile default values for new entry
        if (!$row->id) $row->compileDefaults($level = 'model');

        // Construct and return Indi_Db_Table_Row object,
        // but, if $assign arg is given - preliminary assign data
        return is_array($assign) ? $row->set($assign) : $row;
    }

    /**
     * Create Indi_Db_Table_Rowset object with some data, if passed
     *
     * @param array $input
     * @return Indi_Db_Table_Rowset
     */
    public function createRowset($input = []) {

        // Get the type of construction
        $index = isset($input['rows']) ? 'rows' : 'data';

        // Prepare data for Indi_Db_Table_Rowset object construction
        $data = [
            'table'   => $this->_table,
            $index     => is_array($input[$index]) ? $input[$index] : [],
            'rowClass' => $this->_rowClass,
            'found'=> isset($input['found'])
                ? $input['found']
                : (is_array($input[$index]) ? count($input[$index]) : 0)
        ];

        // Construct and return Indi_Db_Table_Rowset object
        return new $this->_rowsetClass($data);
    }

    /**
     * Returns row class name
     *
     * @return string
     */
    public function rowClass() {
        return $this->_rowClass;
    }

    /**
     * Returns rowset class name
     *
     * @return string
     */
    public function rowsetClass() {
        return $this->_rowsetClass;
    }

    /**
     * Delete all rows from current database table, that match given WHERE clause
     *
     * @param $where
     * @return int Number of affected rows
     * @throws Exception
     */
    public function delete($where) {

        // Basic SQL expression
        $sql = 'DELETE FROM `' . $this->_table . '`';

        // If $where argument is specified, append it as string to basic SQL expression
        if ($where) {

            // Get WHERE clause as string
            if (is_array($where) && count($where)) $where = implode(' AND ', $where);

            // Append WHERE clause to basic expression
            $sql .= ' WHERE ' . $where;

            // Execute the query
            return db()->query($sql);

            // Otherwise throw an exception, to avoid deleting all database table's rows
        } else {
            throw new Exception('No WHERE clause');
        }
    }

    /**
     * Return tree column name
     *
     * @return string
     */
    public function treeColumn() {
        return $this->_treeColumn;
    }

    /**
     * Return id of entity, that current model is representing
     *
     * @return string
     */
    public function id() {
        return $this->_id;
    }

    /**
     * Inserts new row into db table
     *
     * @param array $data
     * @return string
     */
    public function insert($data) {

        // Get existing fields
        $fieldRs = $this->fields();

        // Build the first part of sql expression
        $sql = 'INSERT INTO `' . $this->_table . '` SET ';

        // Declare array for sql SET statements
        $setA = [];

        // If value for `id` is explicitly set - prepend it explicitly,
        // because there is no such a Field_Row instance within $fieldRs
        if ($data['id']) $setA[] = db()->sql('`id` = :s', $data['id']);

        // Foreach field within existing fields
        foreach ($fieldRs as $fieldR) {

            // Skip further / JOINed fields
            if ($fieldR->entityId != $this->_id) continue;

            // We will insert values for fields, that are actually exist in database table structure
            if ($fieldR->columnTypeId) {

                // Declare/reset $set flag
                $set = false;

                // If current field alias is one of keys within data to be inserted - set $set flag to `true`
                if (array_key_exists($fieldR->alias, $data)) $set = true;

                // Else if column type is TEXT - use field's default value as value for insertion,
                // as MySQL does not support native default values for TEXT-columns
                else if ($set = $fieldR->foreign('columnTypeId')->type == 'TEXT')
                    $data[$fieldR->alias] = $fieldR->compiled('defaultValue');

                // If $set flag is `true` - append value with related field alias to $set array
                if ($set) {

                    // If mysql foreign key constraint is defined for this field and value is 0
                    if (isset($this->_ibfk[$fieldR->alias]) && $data[$fieldR->alias] == 0) {

                        // Set value to be NULL
                        $setA []= db()->sql('`' . $fieldR->alias . '` = NULL');

                    // Else
                    } else {

                        // Set value to be as is
                        $setA []= db()->sql('`' . $fieldR->alias . '` = :s', $data[$fieldR->alias]);
                    }
                }
            }
        }

        // Append imploded values from $set array to sql query, or append `id` = NULL expression, if no items in $set
        $sql .= count($setA) ? implode(', ', $setA) : '`id` = NULL';

        // Run the query
        db()->query($sql);

        // Return the id of inserted row
        return db()->getPDO()->lastInsertId();
    }

    /**
     * Update one or more db table columns within rows matching WHERE clause, specified by $where param
     *
     * @param array $data
     * @param string $where
     * @return int
     * @throws Exception
     */
    public function update(array $data, $where = '') {

        // Check if $data array is not empty
        if (!count($data)) return;

        // Get existing fields
        $fieldRs = $this->fields();

        // Build the first part of sql expression
        $sql = 'UPDATE `' . $this->_table . '` SET ';

        // Declare array for sql SET statements
        $setA = [];

        // If value for `id` is explicitly set - prepend it explicitly,
        // because there is no such a Field_Row instance within $fieldRs
        if ($data['id']) $setA[] = db()->sql('`id` = :s', $data['id']);

        // Foreach field within existing fields
        foreach ($fieldRs as $fieldR) {

            // Skip further / JOINed fields
            if ($fieldR->entityId != $this->_id) continue;

            // We will update values for fields, that are actually exist in database table structure
            if (!$fieldR->columnTypeId) continue;

            // If current field alias is not one of keys within data to be updated - skip
            if (!array_key_exists($fieldR->alias, $data)) continue;

            // We append value with related field alias to $set array
            // If mysql foreign key constraint is defined for this field and value is 0
            if (isset($this->_ibfk[$fieldR->alias]) && $data[$fieldR->alias] == 0) {

                // Set value to be NULL
                $setA []= db()->sql('`' . $fieldR->alias . '` = NULL');

            // Else
            } else {

                // Set value to be as is
                $setA []= db()->sql('`' . $fieldR->alias . '` = :s', $data[$fieldR->alias]);
            }
        }

        // Append comma-imploded items of $setA array to sql query
        $sql .= implode(', ', $setA);

        // If $where argument was specified
        if ($where) {

            // Append it to sql query
            if (is_array($where) && count($where)) $where = implode(' AND ', $where);
            $sql .= ' WHERE ' . $where;
        }

        // Execute query and return number of affected rows
        return db()->query($sql);
    }

    /**
     * Return the 'hasRole' flag value
     *
     * @return bool
     */
    public function hasRole() {
        return $this->_hasRole;
    }

    /**
     * Return the 'preload' flag value, with preliminary seeing it up if $on arg given
     *
     * @param bool $on
     * @return bool
     */
    public function preload($on = false) {
        return func_num_args() ? $this->_preload = $on : $this->_preload;
    }

    /**
     * Return title of current model
     *
     * @param string $title
     * @return string
     */
    public function title($title = '') {

        // If $title arg is given
        if ($title) {

            // If localization is turned On
            if (m('Entity')->fields('title')->l10n == 'y') {

                // Spoof title within json
                $_ = json_decode($this->_title);
                $_->{ini('lang')->admin} = $title;
                $this->_title = json_encode($_);

            // Else spoof existing title
            } else $this->_title = $title;
        }

        // If localization is turned On - pick title from json, or return as is
        return m('Entity')->fields('title')->l10n == 'y'
            ? json_decode($this->_title)->{ini('lang')->admin}
            : $this->_title;
    }

    /**
     * Return Field_Row object of a field, that is used as title-field
     *
     * @return Field_Row
     */
    public function titleField() {
        return $this->_fields->field($this->_titleFieldId);
    }

    /**
     * Return id or alias of a field, that is used for files to be grouped into subdirs
     *
     * @return string
     */
    public function filesGroupBy($alias = false) {
        return $alias ? $this->_fields->field($this->_filesGroupBy)->alias : $this->_filesGroupBy;
    }

    /**
     * Apply new values to some of properties of current model
     *
     * @param array $modified
     * @return Indi_Db_Table Fluent interface
     */
    public function apply(array $modified) {

        // Declare array of properties, that are allowed for change
        $allowedPropertyA = ['title', 'titleFieldId'];

        // Apply new values for these properties
        foreach ($modified as $property => $value)
            if (in_array($property, $allowedPropertyA))
                $this->{'_' . $property} = $value;

        // Return model itself
        return $this;
    }

    /**
     * Determine the upload directory name for current model. If $mode argument is not 'name' or 'exists', method will
     * try to create that upload directory, in case if it does not exist, and will return error message, if tries of
     * creation were failed. If $mode argument is 'name' - only directory name will be returned, without any checks
     * about is it exists or writable, and without any error messages. If $mode argument is 'writable' method will
     * perform the full scope of operations, and return error, if error will be met, or directory name, if directory
     * is exists and writable
     *
     * If $ckeditor argument is set to boolean `true`, then function will do the stuff with CKFinder uploads directory.
     * This feature is a part of a concept, that assumes that if database table (that current model is linked to)
     * is used as a place where cms special users are stored, these users should have access (via CKFinder) only to
     * their own files, stored in their own special directories, that are within main CKFinder uploads directory.
     *
     * Example:
     *
     * We have Teacher model, and, of course, `teacher` database table, where all details of all teachers are stored.
     * So, if we decided to give each teacher the ability to access the admin area, for them to be able to create their
     * own education documents/materials via WYSIWYG-editor (Indi Engine uses CKEditor as a WYSIWYG-editor). Any teacher
     * may need to upload some files (images, pdfs, etc) on a server, so Indi Engine provides that feature using CKFinder,
     * that, in it turn, deals with some certain folder, where all files uploaded by it (it - mean CKFinder) are stored.
     * So, this feature is a part of an access restriction policy, so any teacher won't be able to deal with files,
     * that were uploaded by other teachers.
     *
     *
     * @param string $mode
     * @param bool|int $ckfinder
     * @param string $subdir
     * @return string
     */
    public function dir($mode = '', $ckfinder = false, $subdir = '') {

        // Build the target directory name
        $dir = DOC . STD . '/' . ini()->upload->path
            . ($ckfinder ? '/' . ini()->ckeditor->uploadPath : '')
            . '/' . $this->_table . '/'
            . (!is_bool($ckfinder) && preg_match(Indi::rex('int11'), $ckfinder) ? $ckfinder . '/' : '')
            . $subdir;

        // If $mode argument is 'name'
        if ($mode == 'name') return $dir;

        // If all is ok - return directory name, as a proof
        return Indi::dir($dir, $mode);
    }

    /**
     * Return instance of Entity_Row, that represents current model
     *
     * @return Indi_Db_Table_Row|null
     */
    public function entity() {
        return m('Entity')->row($this->_id);
    }

    /**
     * Full model reload, mean same batch of operations that were done within Indi_Db::factory() call,
     * but, at this time, for current model only. Currently this function is used each time some system data changes,
     * for example new field added/changed/deleted within some entity, some field's params package changed, etc.
     */
    public function reload() {

        // Full model reload
        db((int) $this->_id);

        // Return reloaded model
        return m($this->_id);
    }

    /**
     * Return changelog config
     *
     * @param $arg
     * @return array
     */
    public function changeLog($arg = null) {
        return $arg ? $this->_changeLog[$arg] : $this->_changeLog;
    }

    /**
     * Getter function for $this->_notices prop
     *
     * @return Indi_Db_Table_Rowset
     */
    public function notices() {
        return $this->_notices;
    }

    /**
     * Get space scheme settings
     *
     * @param string $keyChain Example: 'fields', 'fields.owners'
     * @return string
     */
    public function space($keyChain = '') {

        // If no $keyChain arg given - return all settings
        if (!$keyChain) return $this->_space;

        // Foreach key pick value each time going to deeper level
        foreach(explode('.', $keyChain) as $key) $value = isset($value) ? $value[$key]: $this->_space[$key];

        // Return value
        return $value;
    }

    /**
     * Set/get for $this->_daily
     */
    public function daily($arg1 = false, $arg2 = false) {

        // If $arg1 is either 'since' or 'until'
        if (in($arg1, 'since,until')) {

            // If $arg2 is also given
            if (func_get_args() == 2) {

                // Set daily bound
                $this->_daily[$arg1] = $arg2;

                // Return model itself
                return $this;

                // Else return current value of a daily bound, specified by $arg1
            } else return $this->_daily[$arg1];

            // Else
        } else {

            // Set 'since' and 'until' either as time or false
            if (func_num_args() > 0) $this->_daily['since'] = Indi::rexm('time', $arg1) ? $arg1 : false;
            if (func_num_args() > 1) $this->_daily['until'] = Indi::rexm('time', $arg2) ? $arg2 : false;

            // Return $this->_daily
            return $this->_daily;
        }
    }

    /**
     * Fetch entries by $limit per once, and pass each entry into $fn function as an arguments.
     * This is a workaround to avoid problems, caused by PHP ini's `memory_limit` configuration option
     *
     * @param callable $fn
     * @param null $where
     * @param null $order
     * @param int $limit
     * @param bool $rowset
     * @throws Exception
     */
    public function batch($fn, $where = null, $order = null, $limit = 500, $rowset = false) {

        // Check that $fn arg is callable
        if (!is_callable($fn)) throw new Exception('$fn arg is not callable');

        // Turn off limitations
        ignore_user_abort(1); set_time_limit(0);

        // Build WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, [null, '']))) $where = implode(' AND ', $where);
        if (is_array($order) && count($order = un($order, [null, '']))) $order = implode(', ', $order);

        // Get total qty of entries to be processed
        $qty = db()->query('SELECT COUNT(*) FROM `' . $this->table() . '`' . ($where ? ' WHERE ' . $where : ''))->cell();

        // Fetch usages by $limit at a time
        for ($p = 1; $p <= ceil($qty/$limit); $p++) {

            // Count how many entries should be deducted from range.
            // However here we need this as a flag indicating whether
            // we should fetch next page as planned, or fetch first page again
            // with understanding that despite it's a again the first page
            // it will contain another results, because previous results are deleted or
            // are not matching $where clause anymore
            $deduct = 0;

            // Fetch usages
            $rs = $this->all($where, $order, $limit, $p);

            // If nothing found - return
            if (!$rs->count()) return;

            // Update usages
            if ($rowset) $fn($rs, $deduct); else foreach ($rs as $i => $r) $fn($r, $deduct, ($p - 1) * $limit + $i);

            // If now (e.g. after $fn() call completed) less entries match WHERE clause,
            // it means that we need to fetch same page again rather than fetching next page
            if ($deduct) $p -= (int) !($deduct = 0);
        }
    }

    /**
     * Shortcut to $this->fields($field)->nested('enumset')
     *
     * @param $field
     * @param $option
     * @return Indi_Db_Table_Rowset
     */
    public function enumset($field, $option = null) {

        // Get *_Rowset object containing `enumset` entries, nested under given field
        $_ = $this->fields($field)->nested('enumset');

        // If $option arg is given - return comma-separated titles, or return an *_Rowset object otherwise
        return $option ? $_->select($option, 'alias')->column('title', ', ') : $_;
    }

    /**
     * This method should return associative array, having field names as keys and array of their props as values.
     * Note that here should be only fields that relate to other entities' entries, that have their own schedules
     * For example, we have `lesson` entity. It's entries - are spaces in schedule. But, we also have `teacherId` field,
     * and `roomId` field within `lesson` entity's structure. So, those two fields should be used as keys in array
     * that this method returns, because each teacher has it's own schedule, and each room has it's own schedule.
     * Example:
     *  return array(
     *      'teacherId' => array('param1' => 'value1'),
     *      'roomId' => array('pre' => function($r){
     *          // Adjust entry's params here, if need
     *      })
     *  )
     *
     * Currently, only one param is available - 'pre'. It a function that can be used to adjust an entry prior
     * inserting it as a new busy space (lesson) within the schedule
     */
    protected function _spaceOwners() {
        return [];
    }

    /**
     * Get space-owner fields either as simple array of field names, or as an associative array,
     * having field names as keys and array of field rules as values. This is because in some cases
     * we need just field names only, but in some - we need each field's rules also
     *
     * @param bool $rules
     * @return array
     */
    public function spaceOwners($rules = false) {
        return $rules ? $this->_space['fields']['owners'] : array_keys($this->_space['fields']['owners']);
    }

    /**
     * Get validation rules for space-coord fields.
     *
     * @return array
     */
    protected function _spaceCoords() {

        // Get space description info, and if space's scheme is 'none' - return empty array
        $space = $this->space(); $ruleA = []; if ($space['scheme'] == 'none') return $ruleA;

        // Shortcut to coord types array
        $_ = explode('-', $space['scheme']);

        // For each duration-responsible space-field append 'required' validation rule, at first
        foreach ($_ as $coord) if (in($coord, 'dayQty,minuteQty,timespan'))
            $ruleA[$space['coords'][$coord]] = ['req' => true];

        // For each space-field (including duration-responsible fields) - set/append data-type validation rules
        foreach ($_ as $coord) switch ($coord) {
            case 'date':      $ruleA[$space['coords'][$coord]]  = ['rex' => 'date']; break;
            case 'datetime':  $ruleA[$space['coords'][$coord]]  = ['rex' => 'datetime']; break;
            case 'time':      $ruleA[$space['coords'][$coord]]  = ['rex' => 'time']; break;
            case 'timeId':    $ruleA[$space['coords'][$coord]]  = ['rex' => 'int11']; break;
            case 'dayQty':    $ruleA[$space['coords'][$coord]] += ['rex' => 'int11']; break;
            case 'minuteQty': $ruleA[$space['coords'][$coord]] += ['rex' => 'int11']; break;
            case 'timespan':  $ruleA[$space['coords'][$coord]] += ['rex' => 'timespan']; break;
        }

        // Return space-coord fields and their validation rules
        return $ruleA;
    }

    /**
     * Get space-coord fields either as simple array of field names, or as an associative array,
     * having field names as keys and array of field rules as values. This is because in some cases
     * we need just field names only, but in some - we need each field's rules also
     * If $strict arg is `true` - rules arrays for all fields will consist of 'required' - rule only
     *
     * @param bool $rules
     * @param bool $strict
     * @return array
     */
    public function spaceCoords($rules = false, $strict = false) {

        // Shortcut
        $coords = $this->_space['fields']['coords'];

        // If no rules should be returned - return coord-fields' names only
        if (!$rules) return array_keys($coords);

        // If $strict arg is given, this means that current call was made within (not directly within)
        // $this->validate() call, and this, in it's turn, means that all fields had already passed
        // data-type-validation, performed by $this->scratchy() call, because $this->scratchy() call
        // is being made prior to $this->validate() call, so we have no need to do data-type validation again
        // So the only validation rule that we should add - is a 'required' validation rule
        if ($strict) foreach ($coords as &$ruleA) $ruleA = ['req' => true];

        // Return
        return $coords;
    }

    /**
     * Collect and return consider-fields for all space-owner fields
     *
     * @return array
     */
    protected function _spaceOwnersRelyOn() {

        // Declare
        $ownerRelyOn = [];

        // Collect consider-fields for all space-owner fields
        foreach ($this->_space['fields']['owners'] as $owner => $ruleA) {

            // If current space-owner field has consider-fields - for each
            foreach ($this->_fields->field($owner)->nested('consider') as $considerR) {

                // Get consider-field
                $cFieldR = $this->_fields->field($considerR->consider);

                // Setup shortcut for consider-field's `storeRelationAbility` prop
                $cra = $cFieldR->storeRelationAbility;

                // Setup rules for space-owner field's consider-field
                $ownerRelyOn[$cFieldR->alias] = ['rex' => $cra == 'many' ? 'int11list' : 'int11'];
            }
        }

        // Return array of consider-fields and their rules
        return $ownerRelyOn;
    }

    /**
     * Collect and return fields that should be auto-filled with one of non-disabled
     * values in case if it will turn out that it's current value is inaccessible
     *
     * @return array
     */
    protected function _spaceOwnersAuto() {

        // Declare
        $ownerAuto = [];

        // Collect auto-fields aliases
        foreach ($this->_space['fields']['owners'] as $owner => $ruleA)
            if ($ruleA['auto']) $ownerAuto[] = $owner;

        // Return array of auto-fields aliases
        return $ownerAuto;
    }

    /**
     * Get consider-fields (for all space-owner fields) either as simple array of field names,
     * or as an associative array, having field names as keys and array of field rules as values.
     * This is because in some cases we need just field names only, but in some - we need each field's rules also
     *
     * @param bool $rules
     * @return array
     */
    public function spaceOwnersRelyOn($rules = false) {
        return $rules ? $this->_space['fields']['relyOn'] : array_keys($this->_space['fields']['relyOn']);
    }


    /**
     * Get fields, responsible for major color and point color
     *
     * @return array
     */
    protected function _spaceColors() {
        return ['major' => null, 'point' => null];
    }

    /**
     * Get preloaded row by a given $key (entry's ID)
     *
     * @param int $key
     * @return Indi_Db_Table_Row
     */
    public function preloadedRow($key) {
        return db()->preloadedRow($this->_table, $key);
    }

    /**
     * Get rowset of сontaining preloaded rows by a given $keys (entry's ID comma-separated list or array)
     *
     * @param int|string|array $keys
     * @return Indi_Db_Table_Rowset
     */
    public function preloadedAll($keys) {
        return db()->preloadedAll($this->_table, $keys);
    }

    /**
     * Get next insert id
     *
     * @return mixed
     */
    public function nid() {
        return db()->query('SHOW TABLE STATUS LIKE "' . $this->_table . '"')->fetch(PDO::FETCH_OBJ)->Auto_increment;
    }

    /**
     * Build and return path to the php-template, used to build the html-file,
     * that is autoattached to a fileupload field. So $field arg should be an alias
     * of a fileupload-field
     *
     * @param $field
     * @param bool $abs
     * @param string $lang
     * @return string
     */
    public function tpldoc($field, $abs = false, $lang = '') {

        // Append script path
        view()->addScriptPath($dir = DOC . STD . '/data/tpldoc');

        // If localization is turned On for this field - append language definition to file name
        if ($this->fields($field)->l10n == 'y' && !$lang && $lang !== false) $lang = ini('lang')->admin;

        // Build template file name
        $tpl = rif($abs, $dir . '/') . $this->_table . '-' . $field .  rif($lang, '-$1') . '.php';
        if (!file_exists($tpl) && ($lang = ini('lang')->admin))
            $tpl = rif($abs, $dir . '/') . $this->_table . '-' . $field .  rif($lang, '-$1') . '.php';

        // Build template file name
        return $tpl;
    }

    /**
     * Create and return new *_Row instance
     *
     * @param $data
     * @return Indi_Db_Table_Row
     */
    public function new($data = []) {
        return $this->createRow($data, true);
    }

    /**
     * Get info about qties and sums, that current entity entries are counted in
     *
     * @return mixed
     */
    public function inQtySum() {
        return $this->_inQtySum;
    }

    /**
     * Get instance of field, that can be used to check ownership by given $owner
     *
     * @param Indi_Db_Table_Row $owner
     * @return Field_Row|null
     */
    public function ownerField(Indi_Db_Table_Row $owner) {

        // Get owner field alias
        $ownerColumn = $this->ownerColumn($owner);

        // Get owner field instance
        return $this->fields($ownerColumn);
    }

    /**
     * Get column name, that can be used to check ownership by given $owner
     * NOTE: this column may not really exist
     *
     * @param Indi_Db_Table_Row $owner
     * @return string
     */
    public function ownerColumn($owner) {
        return $owner->table() . 'Id';
    }

    /**
     * Build WHERE clause so that records that are owned by given $owner are fetched
     *
     * @param Indi_Db_Table_Row $owner
     * @return string
     */
    public function ownerWHERE(Indi_Db_Table_Row $owner) {

        // If model have both ownerRole and ownerId columns
        if ($this->fields('ownerRole,ownerId')->count() == 2) {

            // Use clause based on two columns
            return sprintf('`ownerRole` = "%s" AND `ownerId` = "%s"', $owner->roleId, $owner->id);

        // Else if one of model's fields points to owner
        } else if ($ownerField = $this->ownerField($owner)) {

            // Here we use original value of field's storeRelationAbility prop
            // due to that value can be temporarily changed in certain circumstances
            $ownerWHERE = $ownerField->original('storeRelationAbility') == 'many'
                ? 'FIND_IN_SET("%s", `%s`)'
                : '"%s" = `%s`';

            // Fill with params
            return sprintf($ownerWHERE, $owner->id, $ownerField->alias);
        }
    }

    /**
     * Get/set values inside $this->_ibfk
     */
    public function ibfk() {
        if (func_num_args() == 0) return $this->_ibfk;
        else if (func_num_args() == 1) return $this->_ibfk[func_get_arg(0)];
        else if (func_get_arg(1) === null) unset($this->_ibfk[func_get_arg(0)]);
        else return $this->_ibfk[func_get_arg(0)] = func_get_arg(1);
    }

    /**
     * Check whether there is at least 1 mention of any of $values in $column
     * $entityId arg is applicable and required in case if entity's field,
     * identified by $column - is a multi-entity foreign key field
     *
     * @param string $column
     * @param int|string|array $values
     * @param int|null $entityId
     * @return bool
     */
    public function hasUsages($column, $values, $entityId = null) {

        // If no such field found - return false
        if (!$field = $this->fields($column)) return false;

        // If there is no underlying db table column anymore - return false
        if (!$field->hasColumn()) return false;

        // Prepare WHERE clause for finding usages
        $usagesWHERE = $field->usagesWHERE($values, $entityId);

        // Return true if there is at least 1 mention found
        return !!db()->query("SELECT `id` FROM `{$this->_table}` WHERE $usagesWHERE LIMIT 1")->cell();
    }

    /**
     * Get ids-array of all entries where any of $values are mentioned in $column
     * $entityId arg is applicable and required in case if entity's field,
     * identified by $column - is a multi-entity foreign key field
     *
     * @param string $column
     * @param int|string|array $values
     * @return array
     */
    public function getUsages($column, $values, $format = 'col', $entityId = null) {

        // If no such field found - return false
        if (!$field = $this->fields($column)) return false;

        // If there is no underlying db table column anymore - return false
        if (!$field->hasColumn()) return false;

        // Prepare WHERE clause for finding usages
        $usagesWHERE = $field->usagesWHERE($values, $entityId);

        // Prepare expr to be added to SELECT clause to fetch key => value pairs, if need
        $flag = rif($format == 'pairs', ', 1');

        // Return array of ids of records where any of $values mentioned in $column
        return db()->query("SELECT `id` $flag FROM `{$this->_table}` WHERE $usagesWHERE")->$format();
    }

    /**
     * Check whether deletion of rows identified by $rowIds - is restricted due to
     * onDelete=RESTRICT rule defined on direct or indirect references
     *
     * @param array|int|string $rowIds
     * @return bool|array
     * @throws Exception
     */
    public function isDeletionRESTRICTed($rowIds) {

        // Foreach single-entity reference-fields having onDelete=RESTRICT
        foreach ($this->_refs['RESTRICT'] ?? [] as $ref) {

            // If there is at least 1 usage found by that reference
            if (m($ref['table'])->hasUsages($ref['column'], $rowIds)) {

                // Return true
                return $ref;
            }
        }

        // Foreach of multi-entity reference-fields having onDelete=RESTRICT
        foreach (db()->multiRefs('RESTRICT') ?? [] as $ref) {

            // If current entity is expected to be mentioned in iterated multi-entity reference field
            if (in($this->id(), $ref['expect'])) {

                // If there is at least 1 usage found by that reference
                if (m($ref['table'])->hasUsages($ref['column'], $rowIds, $this->id())) {

                    // Return true
                    return $ref;
                }
            }
        }

        // If we reached this line, it means there are either no references configured
        // as restricted to delete, or there are, but no entries exist so far in referenced tables
        // But, we need to go deeper and check indirect references having onDelete=RESTRICT as we might
        // have those indirectly, e.g behind direct ones having onDelete=CASCADE
        //
        // Foreach single-entity reference-fields having onDelete=CASCADE
        foreach ($this->_refs['CASCADE'] ?? [] as $fieldId => $ref) {

            // If there are usages found by that reference
            if ($refIds = m($ref['table'])->getUsages($ref['column'], $rowIds, 'pairs')) {

                // If any of those usages have their own direct or deeper usages that are restricted to delete
                if ($info = m($ref['table'])->isDeletionRESTRICTed(array_keys($refIds))) {

                    // Return info
                    return $info;

                // Else
                } else {

                    // Declare ids to be array, if not declared so far
                    $this->_refs['CASCADE'][$fieldId]['ids'] = $this->_refs['CASCADE'][$fieldId]['ids'] ?? [];

                    // Append $refIds to the list
                    $this->_refs['CASCADE'][$fieldId]['ids'] += $refIds;
                }
            }
        }

        // Foreach of multi-entity reference-fields having onDelete=CASCADE
        foreach (db()->multiRefs('CASCADE') ?? [] as $fieldId => $ref) {

            // If current entity is expected to be mentioned in iterated multi-entity reference field
            if (in($this->id(), $ref['expect'])) {

                // If there are usages found by that reference
                if ($refIds = m($ref['table'])->getUsages($ref['column'], $rowIds, 'pairs', $this->id())) {

                    // If any of those usages have their own direct or deeper usages that are restricted to delete
                    if ($info = m($ref['table'])->isDeletionRESTRICTed(array_keys($refIds))) {

                        // Return info
                        return $info;

                        // Else
                    } else {

                        // Create temporary ref
                        $this->_refs['CASCADE'][$fieldId] = $this->_refs['CASCADE'][$fieldId] ?? $ref;

                        // Declare ids to be array, if not declared so far
                        $this->_refs['CASCADE'][$fieldId]['ids'] = $this->_refs['CASCADE'][$fieldId]['ids'] ?? [];

                        // Append $refIds to the list
                        $this->_refs['CASCADE'][$fieldId]['ids'] += $refIds;
                    }
                }
            }
        }

        // If we reached this line, it means there are no usages restricted to delete
        return false;
    }

    /**
     * This method is expected to be called after $this->isDeletionRESTRICTed(), and this
     * means execution should be at the step where we are sure that deletion is NOT restricted
     * and for each ref having CASCADE as onDelete-rule we do already have ids fetched, so we rely on that
     *
     * @param $rowIds
     */
    public function doDeletionCASCADEandSETNULL($rowIds) {

        // Foreach of multi-entity reference-fields having onDelete='SET NULL'
        foreach (db()->multiRefs('SET NULL') ?? [] as $fieldId => $ref) {

            // If current entity is expected to be mentioned in iterated multi-entity reference field
            if (in($this->id(), $ref['expect'])) {

                // Create temporary model-level ref
                $this->_refs['SET NULL'][$fieldId] = $ref;
            }
        }

        // Foreach ref group
        foreach ($this->_refs as $rule => &$refs) if (in($rule, ['CASCADE', 'SET NULL'])) {

            // Foreach ref
            foreach ($refs as $fieldId => &$ref) {

                // Get ref model
                $model = m($ref['table']);

                // If $rule is 'CASCADE'
                if ($rule == 'CASCADE') {

                    // If we have previously set skip-flag for this ref-field, it means
                    // we should not go deeper for deletion of same-table child-entries here,
                    // because we have already collected $ids of all-levels child-entries to be deleted
                    // and such a deletion will be done at most upper possible level of nesting
                    if ($ref['skip']) continue;

                    // If there are usages ids found by that ref
                    if ($ids = im(array_keys($ref['ids'] ?? []))) {

                        // If it's a tree-like entity, setup a skip-flag, which we'll use to prevent endless sequence of calls:
                        // 1. Indi_Db_Table_Row->delete()
                        // 2. Indi_Db_Table_Row->doDeletionCASCADEandSETNULL()
                        // 3. Indi_Db_Table->doDeletionCASCADEandSETNULL()
                        // 4. Indi_Db_Table->batch()
                        // 5. Indi_Db_Table->{closure}()
                        // 1. Indi_Db_Table_Row->delete()
                        if ($ref['table'] == $this->_table) $ref['skip'] = true;

                        // Fetch usages by those ids by 500 entries per once
                        $model->batch(function($child, &$deduct) {

                            // Delete child
                            $child->system('skipDeletionRESTRICTedCheck', true)->delete();

                            // Setup $deduct flag to true
                            $deduct = true;

                        //
                        },"`id` IN ($ids)");
                    }

                    // Unset ids and skip from ref
                    unset($ref['ids'], $ref['skip']);

                // Else if $rule is 'SET NULL' and ref-field is not a ENUM-field
                } else if ($model->fields($fieldId)->foreign('columnTypeId')->type != 'ENUM') {

                    // Fetch usages by 500 entries per once
                    $model->batch(function($child) use ($ref, $rowIds) {

                        // Remove usage from child entry
                        $ref['multi']
                            ? $child->drop($ref['column'], $rowIds)
                            : $child->set($ref['column'], 0);

                        // Save child entry
                        $child->save();

                    // Invoke WHERE clause for finding usages
                    }, $model->fields($ref['column'])->usagesWHERE($rowIds, $this->_id));
                }

                // If entity-prop is set, it means it's a multi-entity reference-field
                // and this means we should unset it, as it was set up temporary within $this model's context
                if ($ref['entity'] ?? 0) unset($this->_refs[$rule][$fieldId]);
            }

            // If rule's refs group is empty - unset it
            if (isset($this->_refs[$rule]) && !count($this->_refs[$rule])) unset($this->_refs[$rule]);
        }
    }

    /**
     * Get ref-fields, grouped by their onDelete-rule
     *
     * @return array|mixed
     */
    public function refs($arg1 = null) {

        // If $arg1 is an array - spoof $this->_refs with it
        if (is_array($arg1)) $this->_refs = $arg1;

        // Return refs
        return $this->_refs;
    }

    /**
     * Get value of nativeCascade-flag
     *
     * @return bool
     */
    public function nativeCascade() {
        return $this->_nativeCascade;
    }

    /**
     * Create new record if it does not exists so far
     *
     * @param array $need [propName => propValue] pairs to check whether record having those - already exists
     * @param array $ctor [propName => propValue] pairs to append to the data to be INSERTed, if record not exists
     * @return Indi_Db_Table_Row|null
     */
    public function newIfNeed(array $need, $ctor = []) {

        // Prepare WHERE clause
        $where = [];
        foreach ($need as $prop => $value)
            $where []= "`$prop` = " . db()->quote($value);

        // If row not exists
        if (!$row = $this->row($where)) {

            // Create new one
            $row = $this->new($need + $ctor);

            // Save it
            $row->save();
        }

        // Return row exsiting or newly created
        return $row;
    }

    /**
     * This method will be called after foreign-key field is created somewhere
     * pointing to this entity
     *
     * @param Field_Row $field
     */
    public function onAddedAsForeignKey(Field_Row $field) {

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
        return call_user_func_array([$this, get_parent_class($call['class']) . '::' .  $call['function']], func_num_args() ? func_get_args() : $call['args']);
    }
}