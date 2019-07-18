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
     * Flag, that figures out whether or not cache is used for that model
     *
     * @var boolean
     */
    protected $_useCache = false;

    /**
     * Flag, indicating that this model instances may be used as an access accounts
     *
     * @var boolean
     */
    protected $_hasRole = false;

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
     * Store array of fields, that current model consists from
     *
     * @var array
     */
    protected $_fields = array();

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
    protected $_evalFields = array();

    /**
     * Store array of aliases, related to fields, that are fileupload fields.
     *
     * @var array
     */
    protected $_fileFields = null;

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
    protected $_notices = array();

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
     * protected $_changeLog = array(
     *      'toggle' => true,
     *      'ignore' => 'ignoredField1,ignoredField2,etc'
     * );
     *
     * @var array
     */
    protected $_changeLog = array();

    /**
     * Daily time. This can be used to setup working hours, for example since '10:00:00 until '20:00:00'.
     * If daily times are set, schedule will auto-create busy spaces within each separate 24h-hour period,
     * so, if take the above example, periods from 00:00:00 till 10:00:00 and from 20:00:00 till 00:00:00
     * will be set as busy spaces
     *
     * @var array
     */
    protected $_daily = array(
        'since' => false,
        'until' => false
    );

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

        // Set notices
        if (isset($config['notices'])) $this->_notices = $config['notices'];

        // Set title
        $this->_title = $config['title'];

        // Detect tree column name
        $this->_treeColumn = $config['fields']->field($this->_table . 'Id') ? $this->_table . 'Id' : '';

        // Setup title field id
        $this->_titleFieldId = $config['titleFieldId'] ? $config['titleFieldId'] : 0;

        // Setup 'useCache' flag
        $this->_useCache = isset($config['useCache']) ? true : false;

        // Setup 'hasRole' flag
        $this->_hasRole = $config['hasRole'];

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
                $this->_space['fields']['events'] = array(
                    'change' => $change,
                    'afterrender' => $this->_space['coords'][$frameCoord],
                    'boundchange' => $this->_space['coords'][$dpickCoord],
                );
            }
        }
    }

    /**
     * Fetches all rows, according the given criteria
     *
     * @param null|string|array $where
     * @param null|string|array $order
     * @param null|int $count
     * @param null|int $page
     * @param null|int $offset
     * @return Indi_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $page = null, $offset = null) {
        // Build WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, array(null, '')))) $where = implode(' AND ', $where);
        if (is_array($order) && count($order = un($order, array(null, '')))) $order = implode(', ', $order);

        // Build LIMIT clause
        if ($count !== null || $page !== null) {
            $offset = (is_null($page) ? ($count ? 0 : $page) : $count * ($page - 1)) + ($offset ? $offset : 0);
            if ($offset < 0) {
                $count -= abs($offset);
                $offset = 0;
            }
            $limit = $offset . ($count ? ',' : '') . $count;

            // the SQL_CALC_FOUND_ROWS flag
            if (!is_null($page) || !is_null($count)) $calcFoundRows = 'SQL_CALC_FOUND_ROWS ';
        } else {
            $limit = false;
        }

        // Build the query
        $sql = 'SELECT ' . ($limit ? $calcFoundRows : '') . '* FROM `' . $this->_table . '`'
            . ($where ? ' WHERE ' . $where : '')
            . ($order ? ' ORDER BY ' . $order : '')
            . ($limit ? ' LIMIT ' . $limit : '');

        // Fetch data
        $data = Indi::db()->query($sql)->fetchAll();

        // Prepare data for Indi_Db_Table_Rowset object construction
        $data = array(
            'table'   => $this->_table,
            'data' => $data,
            'rowClass' => $this->_rowClass,
            'found'=> $limit ? $this->_found($where) : count($data),
            'page' => $page
        );

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
    protected function _found($where = '') {
        return Indi::db()->query('SELECT FOUND_ROWS()')->fetchColumn();
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
    public function fetchTree($where = null, $order = null, $count = null, $page = null, $parentId = 0, $selected = 0, $keyword = null, $offsetDetection = false) {

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
            $foundRs = $this->fetchAll($where, $order, $count, $page);
            $found = $foundRs->found();
            $foundA = $foundRs->toArray();

            // We replace indexes with actual ids in $foundA array
            $tmp = array(); foreach ($foundA as $foundI) {
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
            $ids = array();
            $disabledA = array();
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

            $ids = array();
            $i = 0;

            // Declare ids history
            $idsHistory = array();

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

        // Construct a WHERE and ORDER clauses for getting that particular
        // page of results, get it, and setup nesting level indents
        $wo = 'FIND_IN_SET(`id`, "' . implode(',', $ids) . '")';
        $data = $this->fetchAll($wo, $wo)->toArray();
        $assocDataA = array();
        for ($i = 0; $i < count($data); $i++) {
            $assocDataI = $data[$i];
            $assocDataI['_system']['level'] = $level[$data[$i]['id']];
            $assocDataI['_system']['indent'] = indent($level[$data[$i]['id']]);
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
        $data = array (
            'table' => $this->_table,
            'data' => array_values($data),
            'rowClass' => $this->_rowClass,
            'found' => $found,
            'page' => $page
        );

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
        $query = 'SELECT `id`, `' . $tc . '` FROM `' . $this->_table . '`';

        // If $where arg contains 'important' key - use it's value as WHERE clause to prevent
        // fetching whole tree. This can be useful when in case we need certain part of tree
        if (is_array($where) && isset($where['important'])) $query .= ' WHERE ' . $where['important'];

        // Use $order arg as ORDER clause, if given
        $query .= rif($order, ' ORDER BY $1');

        // Get general tree data for whole table, but only `id` and `treeColumn` columns
        $tree = Indi::db()->query($query)->fetchAll();
        $nested = array();
        foreach ($tree as $item) {
            $nested[$item[$tc]][] = $item;
            unset($item);
        }

        // Release memory
        unset($tree);

        // Re-setup tree
        $tree = $this->_append(0, array(), $nested, 0);

        // Release memory
        unset($nested);

        // Then we get an associative array, where keys are ids, and values are arrays containing from parent ids and levels
        $return = array(); for ($i = 0; $i < count($tree); $i++) $return[$tree[$i]['id']] = array($tree[$i][$tc], $tree[$i]['level']);

        // Release memory
        unset($tree);

        // If we have WHERE clause, we should filter tree so there should remain only needed branches
        if ($where) {

            // Needed branches can be two categories:
            // 1. Branches that directly match WHERE clause (primary results)
            // 2. Branches that do not, but that are parent to branches mentioned in point 1 (disabled results)

            // First we should find primary results
            $primary = array();
            if (is_array($where) && count($where = un($where, null))) $where = implode(' AND ', $where);
            $foundA = Indi::db()->query('SELECT `id` FROM `' . $this->_table . '` WHERE ' . $where)->fetchAll();
            foreach ($foundA as $foundI) {
                $primary[$foundI['id']] = true;
                unset($foundI);
            }

            // Get found rows
            $found = count($primary);

            // Then we should find disabled results
            $disabled = array();
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
            $tmp = array();
            foreach ($return as $id => $data) if ($primary[$id] || $disabled[$id]) {
                $tmp[$id] = $data;
                unset($id, $data);
            }

            // Release memory
            unset($return, $primary);

            // Return array(data, foundRows)
            return array('tree' => $tmp, 'found' => $found, 'disabledA' => $disabled);
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
        Indi::db()->query('SET @o=0;');

        // Random temporary table name. We should ensure that there will be no table with such name
        $tmpTableName = 'offset' . rand(2000, 8000);

        // We are using a temporary table to place data into it, and the get of offset
        Indi::db()->query($sql = '
            CREATE TEMPORARY TABLE `' . $tmpTableName . '`
            SELECT @o:=@o+1 AS `offset`, `id`="' . $id . '" AS `found`
            FROM `' . $this->_table .'`'
                . ($where ? ' WHERE ' . $where : '')
                . ($order ? ' ORDER BY ' . $order : '')
        );

        // Get the offset
        $offset = Indi::db()->query('
            SELECT `offset`
            FROM `' . $tmpTableName . '`
            WHERE `found` = "1"'
        )->fetchColumn(0);

        // Unset offset variable
        Indi::db()->query('SET @o=null;');

        // Truncate temporary table
        Indi::db()->query('DROP TABLE `' . $tmpTableName . '`');

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
    public function setEvalFields($evalFields = array()) {
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

        // Setup $this->_fileFields property, if it wasn't yet, and then do the job
        if ($this->_fileFields === null) $this->_fileFields = $this->fields()->select(14, 'elementId')->column('alias');
        return $fileField ? in_array($fileField, $this->_fileFields) : $this->_fileFields;
    }

    /**
     * Return incremented by 1 maximum value of `move` column within current database table
     *
     * @return int
     */
    public function getNextMove() {
        return $this->fetchRow('`move` != "0"', '`move` DESC')->move + 1;
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
     * @return Field_Row|Field_Rowset
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
                $columnA = array();

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
        return $object ? (object) $array : $array;
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
        return $data = Indi::db()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Return the name of database table, related to current model
     *
     * @return string
     */
    public function table() {
        return $this->_table;
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
        if (is_array($order) && count($order = un($order, null))) $order = implode(', ', $order);

        // If we are trying to get row by offset, and current model is a tree - use special approach
        if ($offset !== null && $this->treeColumn())
            return $this->fetchTree($where, $order, 1, $offset + 1)->current();

        // Else use usual approach
        else {
            $data = Indi::db()->query($sql =
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
            $constructData = array(
                'table'    => $this->_table,
                'original' => $data,
            );

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
     * Create empty row. If non-false $assign argument is given - we assume that $input arg should not be used
     * be used for construction, but should be used for $this->assign() call. This may me useful
     * in case when we need to create an instance of a row and assign a values into it - and all
     * this within a single call. So, without $assign arg usage, the desired effect would require:
     *
     *   Indi::model('SomeModel')->createRow()->assign(array('prop1' => 'value1', 'prop2' => 'value2'));
     *
     * But not, with $assign arg usage, same effect would require
     *
     *   Indi::model('SomeModel')->createRow(array('prop1' => 'value1', 'prop2' => 'value2'));
     *
     * So, with $assign arg usage, we can omit the additional 'assign(..)' cal
     *
     * @param array $input
     * @param bool $assign
     * @return mixed
     */
    public function createRow($input = array(), $assign = false) {

        // If non-false $assign argument is given - we assume that $input arg should not be used
        // be used for construction, but should be used for $this->assign() call
        if ($assign) { $assign = $input; $input = array(); }

        // Prepare data for construction
        $constructData = array(
            'table'   => $this->_table,
            'original'     => is_array($input['original']) ? $input['original'] : array(),
            'modified' => is_array($input['modified']) ? $input['modified'] : array(),
            'system' => is_array($input['system']) ? $input['system'] : array(),
            'temporary' => is_array($input['temporary']) ? $input['temporary'] : array(),
            'foreign' => is_array($input['foreign']) ? $input['foreign'] : array(),
            'nested' => is_array($input['nested']) ? $input['nested'] : array(),
        );

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
        if (!$row->id) $row->compileDefaults();

        // Construct and return Indi_Db_Table_Row object,
        // but, if $assign arg is given - preliminary assign data
        return is_array($assign) ? $row->assign($assign) : $row;
    }

    /**
     * Create Indi_Db_Table_Rowset object with some data, if passed
     *
     * @param array $input
     * @return Indi_Db_Table_Rowset
     */
    public function createRowset($input = array()) {

        // Get the type of construction
        $index = isset($input['rows']) ? 'rows' : 'data';

        // Prepare data for Indi_Db_Table_Rowset object construction
        $data = array(
            'table'   => $this->_table,
            $index     => is_array($input[$index]) ? $input[$index] : array(),
            'rowClass' => $this->_rowClass,
            'found'=> isset($input['found'])
                ? $input['found']
                : (is_array($input[$index]) ? count($input[$index]) : 0)
        );

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
            return Indi::db()->query($sql);

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
        $setA = array();

        // If value for `id` is explicitly set - prepend it explicitly,
        // because there is no such a Field_Row instance within $fieldRs
        if ($data['id']) $setA[] = Indi::db()->sql('`id` = :s', $data['id']);

        // Foreach field within existing fields
        foreach ($fieldRs as $fieldR) {

            // We will insert values for fields, that are actually exist in database table structure
            if ($fieldR->columnTypeId) {

                // If current field alias is one of keys within data to be inserted,
                // and if data's value for that field alias is not null
                if (array_key_exists($fieldR->alias, $data)) {

                    // We append value with related field alias to $set array
                    $setA[] = Indi::db()->sql('`' . $fieldR->alias . '` = :s', $data[$fieldR->alias]);

                // Else if column type is TEXT, we use field's default value as value for insertion
                } else if ($fieldR->foreign('columnTypeId')->type == 'TEXT')
                    $setA[] = Indi::db()->sql('`' . $fieldR->alias . '` = :s', $fieldR->compiled('defaultValue'));
            }
        }

        // Append imploded values from $set array to sql query, or append `id` = NULL expression, if no items in $set
        $sql .= count($setA) ? implode(', ', $setA) : '`id` = NULL';

        // Run the query
        Indi::db()->query($sql);

        // Return the id of inserted row
        return Indi::db()->getPDO()->lastInsertId();
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
        if (count($data)) {

            // Get existing fields
            $fieldRs = $this->fields();

            // Build the first part of sql expression
            $sql = 'UPDATE `' . $this->_table . '` SET ';

            // Declare array for sql SET statements
            $setA = array();

            // If value for `id` is explicitly set - prepend it explicitly,
            // because there is no such a Field_Row instance within $fieldRs
            if ($data['id']) $setA[] = Indi::db()->sql('`id` = :s', $data['id']);

            // Foreach field within existing fields
            foreach ($fieldRs as $fieldR) {

                // We will update values for fields, that are actually exist in database table structure
                if ($fieldR->columnTypeId) {

                    // If current field alias is one of keys within data to be updated,
                    if (array_key_exists($fieldR->alias, $data))

                        // We append value with related field alias to $set array
                        $setA[] = Indi::db()->sql('`' . $fieldR->alias . '` = :s', $data[$fieldR->alias]);
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
            return Indi::db()->query($sql);
        }
    }

    /**
     * Return the 'useCache' flag value
     *
     * @return bool
     */
    public function useCache() {
        return $this->_useCache;
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
        return $title ? $this->_title = $title : $this->_title;
    }

    /**
     * Return Field_Row object of a field, that is used as title-field
     *
     * @return Field_Row
     */
    public function titleField() {
        return $this->_fields->select($this->_titleFieldId)->rewind()->current();
    }

    /**
     * Apply new values to some of properties of current model
     *
     * @param array $modified
     * @return Indi_Db_Table Fluent interface
     */
    public function apply(array $modified) {

        // Declare array of properties, that are allowed for change
        $allowedPropertyA = array('title', 'titleFieldId');

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
     * @return string
     */
    public function dir($mode = '', $ckfinder = false) {

        // Build the target directory name
        $dir = DOC . STD . '/' . Indi::ini()->upload->path
            . ($ckfinder ? '/' . Indi::ini()->ckeditor->uploadPath : '')
            . '/' . $this->_table . '/'
            . (!is_bool($ckfinder) && preg_match(Indi::rex('int11'), $ckfinder) ? $ckfinder . '/' : '');

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
        return Indi::model('Entity')->fetchRow('`id` = "' . $this->_id . '"');
    }

    /**
     * Full model reload, mean same batch of operations that were done within Indi_Db::factory() call,
     * but, at this time, for current model only. Currently this function is used each time some system data changes,
     * for example new field added/changed/deleted within some entity, some field's params package changed, etc.
     */
    public function reload() {

        // Full model reload
        Indi::db((int) $this->_id);

        // Return reloaded model
        return Indi::model($this->_id);
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
     * Fetch entries by $limit per once, and pass each entry into $operation function as an arguments.
     * This is a workaround to avoid problems, caused by PHP ini's `memory_limit` configuration option
     *
     * @param callable $operation
     * @param null $where
     * @param null $order
     * @param int $limit
     * @throws Exception
     */
    public function batch($operation, $where = null, $order = null, $limit = 500) {

        // Check that $operation arg is callable
        if (!is_callable($operation)) throw new Exception('$operation arg is not callable');

        // Turn off limitations
        ignore_user_abort(1); set_time_limit(0);

        // Build WHERE and ORDER clauses
        if (is_array($where) && count($where = un($where, array(null, '')))) $where = implode(' AND ', $where);
        if (is_array($order) && count($order = un($order, array(null, '')))) $order = implode(', ', $order);

        // Get total qty of entries to be processed
        $qty = Indi::db()->query('SELECT COUNT(*) FROM `' . $this->table() . '`' . ($where ? ' WHERE ' . $where : ''))->fetchColumn();

        // Fetch usages by $limit at a time
        for ($p = 1; $p <= ceil($qty/$limit); $p++) {

            // Fetch usages
            $rs = $this->fetchAll($where, $order, $limit, $p);

            // Update usages
            foreach ($rs as $r) $operation($r);
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
        return array();
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
        $space = $this->space(); $ruleA = array(); if ($space['scheme'] == 'none') return $ruleA;

        // Shortcut to coord types array
        $_ = explode('-', $space['scheme']);

        // For each duration-responsible space-field append 'required' validation rule, at first
        foreach ($_ as $coord) if (in($coord, 'dayQty,minuteQty,timespan'))
            $ruleA[$space['coords'][$coord]] = array('req' => true);

        // For each space-field (including duration-responsible fields) - set/append data-type validation rules
        foreach ($_ as $coord) switch ($coord) {
            case 'date':      $ruleA[$space['coords'][$coord]]  = array('rex' => 'date'); break;
            case 'datetime':  $ruleA[$space['coords'][$coord]]  = array('rex' => 'datetime'); break;
            case 'time':      $ruleA[$space['coords'][$coord]]  = array('rex' => 'time'); break;
            case 'timeId':    $ruleA[$space['coords'][$coord]]  = array('rex' => 'int11'); break;
            case 'dayQty':    $ruleA[$space['coords'][$coord]] += array('rex' => 'int11'); break;
            case 'minuteQty': $ruleA[$space['coords'][$coord]] += array('rex' => 'int11'); break;
            case 'timespan':  $ruleA[$space['coords'][$coord]] += array('rex' => 'timespan'); break;
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
        if ($strict) foreach ($coords as &$ruleA) $ruleA = array('req' => true);

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
        $ownerRelyOn = array();

        // Collect consider-fields for all space-owner fields
        foreach ($this->_space['fields']['owners'] as $owner => $ruleA) {

            // If current space-owner field has consider-fields - for each
            foreach ($this->_fields->field($owner)->nested('consider') as $considerR) {

                // Get consider-field
                $cFieldR = $this->_fields->field($considerR->consider);

                // Setup shortcut for consider-field's `storeRelationAbility` prop
                $cra = $cFieldR->storeRelationAbility;

                // Setup rules for space-owner field's consider-field
                $ownerRelyOn[$cFieldR->alias] = array('rex' => $cra == 'many' ? 'int11list' : 'int11');
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
        $ownerAuto = array();

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
     * Get preloaded row by a given $key (entry's ID)
     *
     * @param int $key
     * @return Indi_Db_Table_Row
     */
    public function preloadedRow($key) {
        return Indi::db()->preloadedRow($this->_table, $key);
    }

    /**
     * Get rowset of сontaining preloaded rows by a given $keys (entry's ID comma-separated list or array)
     *
     * @param int|string|array $keys
     * @return Indi_Db_Table_Rowset
     */
    public function preloadedAll($keys) {
        return Indi::db()->preloadedAll($this->_table, $keys);
    }
}