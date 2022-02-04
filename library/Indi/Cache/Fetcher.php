<?php
class Indi_Cache_Fetcher {

    /**
     * Store info about database table name, columns that should be used for fetching values from, search criteria,
     * sorting options, and number of returning results limitation. Also store info about type of result, e.g row
     * or rowset
     *
     * @var array
     */
    public $config = [];

    /**
     * Array of data, that met all the requirements
     *
     * @var array
     */
    public $data = [];

    /**
     * Constructor. Set $this->config only.
     *
     * @param array $config
     */
    public function __construct($config) {

        // Setup a config array
		$this->config = $config;
	}

    /**
     * Get the array of indexes, that will be used for direct properties picking for each data item, that satisfy the
     * whole scope of search criteria, mentioned in $this->params['where']
     *
     * @return array
     */
    protected function _where(){

        // If search criteria was given within the sql query
        if ($this->config['where']) {

            // Declare $sieved variable as null for this time, but later it will be converted to array
            $sievedA = null;

            // For each search criteria
            foreach ($this->config['where'] as $whereI) {

                // If type of current search criteria is sql '=' clause
                if (preg_match('/^`([a-zA-Z0-9_]+)`\s*=\s*"([^"]+)"$/', $whereI, $criteria)) {
                    $indexA = $GLOBALS['cache'][$this->config['table']]['myi'][$criteria[1]][$criteria[2]];

                    // If there is a single index found, and it is 0, convert it to array
                    if ($indexA == 0) $indexA = [0];

                // Else if type of current search criteria is sql `IN` or 'FIND_IN_SET(`column`, "valueslist")' clause
                } else if (preg_match('/^`([a-zA-Z0-9_]+)`\s+IN\s*\(([a-zA-ZА-Яа-я0-9_"\',]+)\)$/', $whereI, $criteria)
                    || preg_match('/^FIND_IN_SET\s*\(`([a-zA-Z0-9_]+)`,\s*"([a-zA-Zа-яА-Я0-9,_]+)"\)$/', $whereI, $criteria)) {

                    // Declare $indexA array
                    $indexA = [];

                    // For each value within values list
                    foreach(explode(',', $criteria[2]) as $value) {

                        // Trim quotes from current value
                        $value = trim($value, '"');

                        // Merge arrays of indexes of data items, where current value was found
                        $indexA = array_merge($indexA,
                            is_array($GLOBALS['cache'][$this->config['table']]['myi'][$criteria[1]][$value])
                                ? $GLOBALS['cache'][$this->config['table']]['myi'][$criteria[1]][$value]
                                : [$GLOBALS['cache'][$this->config['table']]['myi'][$criteria[1]][$value]]);

                    }

                    // Unset non-unique values within $indexA array
                    $indexA = array_unique($indexA);

                // If search criteria is 'FIND_IN_SET("value", `columnThatCanStoreCommaSeparatedListOfValues`)' clause
                } else if (preg_match('/^FIND_IN_SET\s*\("([a-zA-Zа-яА-Я0-9,_]+)",\s*`([a-zA-Z0-9_]+)`\s*\)/', $whereI, $criteria)) {

                    // Declare $indexA array
                    $indexA = [];

                    // For each possible values of within certain column usage
                    foreach ($GLOBALS['cache'][$this->config['table']]['myi'][$criteria[2]] as $value => $usage)

                        // If searched value is within current possible column value as one of comma-searated items
                        if (preg_match('/,' . $criteria[1] . ',/', ',' . $value . ','))

                            // Merge arrays if indexes of data items, where current value was found
                            $indexA = array_merge($indexA, is_array($usage) ? $usage : [$usage]);


                    // Unset non-unique values within $indexA array
                    $indexA = array_unique($indexA);
                }

                // If at any step of chech no results were found - stop
                if (!$indexA) return [];

                // If only one index was found - convert it into an array
                if (!is_array($indexA)) $indexA = [$indexA];

                // Find the interception between indexes, found for all search criteria items
                $sievedA = is_null($sievedA) ? $indexA : array_intersect($sievedA, $indexA);

                // If no indexes found, return empty array
                if (!count($sievedA)) return [];
            }

            //d($this->config);
            //d($sievedA);

            // Return indexes of needed data location
            return $sievedA;

            // Else there was no search criteria given, return all the possible indexes
        } else return array_keys($GLOBALS['cache'][$this->config['table']]['myd']['id']);
    }

    /**
     * Sort the data, stored in $this->data, by a certain property an direction, mentioned in $this->config['order'],
     * using php's array_multisort() function
     */
    protected function _order(){

        // If sorting should be performed
        if ($this->config['order']) {

            // If $this->config['order']['column'] contains sql 'FIND_IN_SET' expression, run bit different logic
            if (preg_match('/FIND_IN_SET\s*\(`([a-zA-Z0-9_]+)`,\s*"([^"]+)"\)/', $this->config['order']['column'], $fis)) {

                // Extract column name, mentioned in FIND_IN_SET expression and update ['order']['column'] with that
                $this->config['order']['column'] = $fis[1];

                // Pick certain column values separately, as this way usage is required for reach the aim using
                // php's array_multisort() function
                foreach ($this->data as $index => $data)
                    $column[$index] = strpos(',' . $fis[2] . ',', ',' . $data[$this->config['order']['column']] . ',');

            // Else
            } else {

                // Pick certain column values separately, as this way usage is required for reach the aim using
                // php's array_multisort() function
                foreach ($this->data as $index => $data) $column[$index] = $data[$this->config['order']['column']];
            }

            // Do an array_multisort(), but only if we have non-empty data array
            if (count($this->data))
                array_multisort($column,
                    $this->config['order']['direction'] == 'DESC' ? SORT_DESC : SORT_ASC, $this->data);
        }
    }

    /**
     * Check if sql query have a format that is supported by fetcher, and return info about it as values within
     * associative array. The whole set of params look like this:
     * Array(
     *   ['columns'] => Array(           or        ['columns'] => Array(
     *      [0] => 'column1',                          [0] => *
     *      [1] => 'column2',                      )
     *      ...
     *      [n] => 'columnN'
     *   ),
     *   ['table'] => 'table1',
     *   ['where] => Array(
     *      [0] => '`column1` = "123"',
     *      [1] => '`column2` IN (123,234,345)',
     *      [2] => 'FIND_IN_SET(`column3`, "123,234,345")',
     *      [3] => 'FIND_IN_SET("123", `column4`)'
     *   ),
     *   ['order'] => Array(
     *      ['column'] => '`column1`',
     *      ['direction'] => 'DESC'
     *   ),
     *   ['limit'] => 10
     * )
     *
     * Note:
     * 1. In WHERE clause, only expressions, mentioned in above example, are supported
     * 2. In ORDER clause, sorting by only one column is supported
     * 3. In LIMIT clause, only results count limitation is supported (OFFSET clause is not supported)
     * 4. In Order clause, if sorting direction was not mentioned in clause, ['order']['direction'] will be 'ASC' by
     *    default
     *
     * @param $sql
     * @return array
     */
    public static function support($sql) {

        // Replace newlines with spaces within a $sql argument, to make the query string single-line, for proper check
        // of match to Indi_Cache requirements
        $sql = preg_replace("/\n/", " ", trim($sql));

        // Declare regex for first step of sql SELECT query compatibility check
        $f = '/^SELECT\s+(\*|`[a-zA-Z0-9_]+`(,\s*`[a-zA-Z0-9_]+`)*)\s+FROM\s+`([^`]+)`/';

        // Declare regex for WHERE clause compatibility check
        $w = '(`[a-zA-Z0-9_]+`\s*(=\s*"[^"]+"|IN\s*\([a-zA-ZА-Яа-я0-9_"\',]+\))|FIND_IN_SET\s*\((`[a-zA-Z0-9_]+`,\s*"[a-zA-Zа-яА-Я0-9,_]+"|"[a-zA-Zа-яА-Я0-9,_]+",\s*`[a-zA-Z0-9_]+`)\))';

        // Declare regex for ORDER BY clause compatibility check
        //$o = '/^\s+ORDER BY\s*(`?[a-zA-Z0-9_]+`?|FIND_IN_SET\s*\(`[a-zA-Z0-9_]+`,\s*"[a-zA-Zа-яА-Я0-9,_]+"\))\s*(DESC|ASC)?/';
        $o = '/^\s+ORDER BY\s*(FIND_IN_SET\s*\(`[a-zA-Z0-9_]+`,\s*"[a-zA-Zа-яА-Я0-9,_]+"\)|`?[a-zA-Z0-9_]+`?)\s*(DESC|ASC)?/';

        // Declare regex for LIMIT clause compatibility check
        $l = '/^\s*LIMIT\s+([0-9]+)/';

        // If query is compatible with supported format of SELECT clause
        if (preg_match($f, $sql, $fieldtable)) {

            // If there is something left except columns and table name mention in query
            if ($tail = preg_replace($f, '', $sql)) {

                // If query is compatible with supported format of WHERE clause
                if (preg_match('/^\s+WHERE\s*(' . $w . '(\s+AND\s+' . $w . ')*)*/', $tail, $where)) {

                    // If there is something left except columns, table name and WHERE clause mentioned in query
                    if ($tail = preg_replace('/^\s+WHERE\s*(' . $w . '(\s+AND\s+' . $w . ')*)*/', '', $tail)) {

                        // If query is compatible with supported format of ORDER clause
                        if (preg_match($o, $tail, $order)) {

                            // If there is something left except columns, table name, WHERE and ORDER clauses
                            if ($tail = preg_replace($o, '', $tail)) {

                                // If query is compatible with supported format of LIMIT clause
                                if (preg_match($l, $tail, $limit)) {

                                    // If there is something left except columns, table name, WHERE, ORDER and LIMIT
                                    // clauses
                                    if (!preg_replace($l, '', $tail)) {

                                        // Prepare and return the whole set of params
                                        $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                                        $config['where'] = preg_split('/ AND /i', $where[1]);
                                        $config['order']['column'] = trim($order[1], '`');
                                        $config['order']['direction'] = $order[2] ? $order[2] : 'ASC';
                                        $config['limit'] = $limit[1];
                                        return $config;
                                    }
                                }

                            // Else if there is nothing left
                            } else {

                                // Prepare and return partial params set - all but without LIMIT mention
                                $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                                $config['where'] = preg_split('/ AND /i', $where[1]);
                                $config['order']['column'] = trim($order[1], '`');
                                $config['order']['direction'] = $order[2] ? $order[2] : 'ASC';
                                return $config;
                            }

                        // If query is compatible with supported format of LIMIT clause
                        } else if (preg_match($l, $tail, $limit)) {

                            // If there is nothing left except columns, table name, WHERE and LIMIT clauses
                            if (!preg_replace($l, '', $tail)) {

                                // Prepare and return the whole set of params
                                $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                                $config['where'] = preg_split('/ AND /i', $where[1]);
                                $config['limit'] = $limit[1];
                                return $config;
                            }
                        }

                    // Else if there is nothing left
                    } else {

                        // Prepare and return partial params set - only column names, table name and WHERE clause
                        $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                        $config['where'] = preg_split('/ AND /i', $where[1]);
                        return $config;
                    }

                // Else if supported WHERE clause was not detected in query, check for ORDER clause mention
                } else if (preg_match($o, $tail, $order)) {

                    // If there is something left in query except ORDER clause
                    if ($tail = preg_replace($o, '', $tail)) {

                        // Check if there is supported LIMIT clause mentioned in query
                        if (preg_match($l, $tail, $limit)) {

                            // If there is nothing left in query except ORDER and LIMIT clause
                            if (!preg_replace($l, '', $tail)) {

                                // Prepare return and partial params set, containing info about columns, table,
                                // ORDER and LIMIT clauses
                                $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                                $config['order']['column'] = trim($order[1], '`');
                                $config['order']['direction'] = $order[2] ? $order[2] : 'ASC';
                                $config['limit'] = $limit[1];
                                return $config;
                            }
                        }

                    // Else if there is nothing left except ORDER clause
                    } else {

                        // Prepare and return partial params set, containing info about columns, table and ORDER clause
                        $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                        $config['order']['column'] = trim($order[1], '`');
                        $config['order']['direction'] = $order[2] ? $order[2] : 'ASC';
                        return $config;
                    }

                // Else if supported WHERE and ORDER clauses was not detected in query, check for LIMIT clause mention
                } else if (preg_match($l, $tail, $limit)) {

                    // If there is nothing left except LIMIT clause
                    if (!preg_replace($l, '', $tail)) {

                        // Prepare and return partial params set, containing info about columns, table and LIMIT clause
                        $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                        $config['limit'] = $limit[1];
                        return $config;
                    }
                }

            // Else if there is nothing left in query except mentions of columns and table names - return it
            } else {
                $config = ['field' => preg_split('/`, *`/', trim($fieldtable[1], '`')), 'table' => $fieldtable[3]];
                return $config;
            }
        }
    }

    /**
     * Fetch and return an array of data items. Partially simulate PDO::fetchAll() method
     *
     * @param null $mode Only PDO::FETCH_COLUMN flag is supported
     * @param int $column Index or name of a column
     * @return array|int|string
     */
    public function fetchAll($mode = null, $column = 0) {
        if ($mode == PDO::FETCH_COLUMN) $this->config['column'] = $column;
        $this->config['result'] = 'rowset';
        return $this->_search();
    }

    /**
     * Set up a result type as 'row', results count limitation as 1, and fetch and return single data item as an array
     *
     * @return array
     */
    public function fetch() {
        $this->config['result'] = 'row';
        $this->config['limit'] = 1;
        return $this->_search();
    }

    /**
     * Fetch and return an value of a single column of a single data item. Partially simulate PDO::fetchColumn() method
     *
     * @param int $column Index or name of a column, which value should be returned
     * @return array|int|string
     */
    public function fetchColumn($column = 0) {
        $this->config['column'] = $column;
        $this->config['result'] = 'row';
        return $this->_search();
    }

    /**
     * Prepare and return fetched data in a certain format
     *
     * @return array|int|string
     */
    protected function _result() {

        // If config 'column' param is set
        if (isset($this->config['column']))

            // If it is an index
            if (preg_match('/^[0-9]+$/', $this->config['column'])) {

                if (!is_array($this->data[0])) {
                    d(debug_print_backtrace());
                    die();
                }

                // Determine a column name by that index
                $column = array_keys($this->data[0]);
                $column = $column[$this->config['column']];

            // Else we assume that it is a column name, so we assign it to $column variable
            } else $column = $this->config['column'];



        // If result should contain data related to single row
        if ($this->config['result'] == 'row') {

            // Return whole data for single row, or only row data stored under $column property
            return isset($column) ? $this->data[0][$column] : $this->data[0];

        // Else if results should contain data for multiple rows
        } else {

            // If $column variable is set, the array of only values within that column will be returned
            if (isset($column)) {
                $data = []; foreach ($this->data as $item) $data[] = $item[$column]; return $data;

            // Else required data for all found rows will be returned
            } else return $this->data;
        }
    }

    /**
     * Get the array of indexes, that needed data items properties are located under,
     * and pick all existing data located under these indexes
     */
    public function _matched() {

        // Get the array of indexes, that needed data items properties are located under
        $indexA = $this->_where();

        // Get the full list of possible properties within any data item
        $fieldA = array_keys($GLOBALS['cache'][$this->config['table']]['myd']);

        if (!is_array($indexA) && strlen($indexA)) $indexA = [$indexA];

        // Foreach index
        foreach ($indexA as $indexI) {

            // Declare data item properties array
            $dataI = [];

            // Fulfil data item properties array with values, picked from certain keys at certain indexes
            foreach ($fieldA as $fieldI)
                $dataI[$fieldI] = $GLOBALS['cache'][$this->config['table']]['myd'][$fieldI][$indexI];

            // Append data item to $this->data array
            $this->data[] = $dataI;
        }
    }

    /**
     * Slice the first N elements from $this->data array, and replace $this->data array with that elements
     */
    protected function _limit() {
        if ($this->config['limit']) $this->data = array_slice($this->data, 0, $this->config['limit']);
    }

    /**
     * Unset properties, that was not mentioned as needed within results, for each data item
     */
    protected function _columns() {

        // If first mentioned property is not an '*'
        if ($this->config['field'][0] != '*')

            // Get the array of properties names, that should not be included in final results
            if (count($unset = array_diff(array_keys($GLOBALS['cache'][$this->config['table']]['myd']), $this->config['field'])))

                // For each data item unset all non-needed properties
                for ($i = 0; $i < count($this->data); $i++)
                    foreach ($unset as $u)
                        unset($this->data[$i][$u]);

    }

    /**
     * Main function that accumulate call of all required operations
     *
     * @return array|int|string
     */
    protected function _search() {
        $this->_matched();
        $this->_order();
        $this->_limit();
        $this->_columns();
        return $this->_result();
    }
}
