<?php
class Indi_Db_Table_Rowset implements SeekableIterator, Countable, ArrayAccess {

    /**
     * Array of rows, that are stored within current rowset
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * Table name of table, that current rowset is related to
     *
     * @var string
     */
    protected $_table = '';

    /**
     * Contain keys, that current rowset have nested rowsets under
     *
     * @var array
     */
    public $_nested = array();

    /**
     * Iterator pointer.
     *
     * @var int
     */
    protected $_pointer = 0;

    /**
     * How many data rows there are.
     *
     * @var integer
     */
    protected $_count = 0;

    /**
     * Store number of rows, which would be found
     * if the LIMIT clause was disregarded
     *
     * @var mixed (null,int)
     */
    protected $_found = null;

    /**
     * Store current page number
     *
     * @var mixed (null,int)
     */
    protected $_page = null;

    /**
     * Indi_Db_Table_Row class name.
     *
     * @var string
     */
    protected $_rowClass = 'Indi_Db_Table_Row';

    /**
     * Sql query used to fetch this rowset
     *
     * @var mixed (null,int)
     */
    protected $_query = null;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config) {

        // Setup properties from $config argument
        if (isset($config['table'])) $this->_table = $config['table'];

        // Setup row class, and the count of rows within current rowset
        $this->_rowClass = isset($config['rowClass']) ? $config['rowClass'] : $this->model()->rowClass();

        // Setup title column
        $this->titleColumn = isset($config['titleColumn']) ? $config['titleColumn'] : 'title';

        // If 'data' key exists within $config array
        if (isset($config['data'])) {

            // Declare an array of special properties, that will be picked up from $config argument
            $specialA = array('modified', 'system', 'temporary', 'compiled', 'foreign', 'nested');

            // Foreach data item create a $rowConfig variable
            foreach ($config['data'] as $item) {

                // Assign values for special properties within $rowConfig variable
                foreach ($specialA as $specialI) {
                    $rowConfig[$specialI] = $item['_' . $specialI];
                    unset($item['_' . $specialI]);
                }

                // Assing 'original' and 'table' properties
                $rowConfig['original'] = $item; unset($item);
                $rowConfig['table'] = $this->_table;

                // Use $rowConfig as an argument for row construction
                $this->_rows[] = new $this->_rowClass($rowConfig);
            }

        // Else if 'data' key does not exist within $config array, but 'rows' key do, setup $this->_rows property directly
        } else if (isset($config['rows'])) $this->_rows = $config['rows'];

        // Setup page and total found results number
        if (isset($config['page'])) $this->_page = $config['page'];
        if (isset($config['found'])) $this->_found = $config['found'];
        if (isset($config['query'])) $this->_query = $config['query'];

        $this->_count = count($this->_rows);
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Indi_Db_Table_Row|mixed Current element from the current rowset
     */
    public function current() {
        // If $this->_pointer is out of bounds - return null
        if ($this->valid() === false) return null;

        // Return current row
        return $this->_rows[$this->_pointer];
    }

    /**
     * Delete rowset using row by row scheme. If $parentDelete param is set to true,
     * then there only standard Indi_Db_Table_Row deletion will take effect, with no
     * additional deletion commands, specified in delete() methods of all classes that
     * are extended from Indi_Db_Table_Row class, if they exist and have this method overrided
     *
     * @param bool $parentDelete
     * @return int Number of rows, that were deleted from current rowset
     */
    public function delete($parentDelete = false) {

        // Define a variable for counting how many rows were deleted
        $deleted = 0;

        // Row-by-row deletion
        foreach ($this as $row) $deleted += $row->delete($parentDelete);

        // Return number of deleted rows.
        return $deleted;
    }

    /**
     * Returns all data as an array. If $deep argument is specified then return value will represent an array of
     * values, got by toArray() function usage, called for each row in current rowset, instead of simply returning
     * $this->_original rowset property, so if any of rows within rowset have some additional properties, such as foreign
     * rows, nested rowsets, etc - these props will be included in the return value
     *
     * @param bool $deep
     * @return array
     */
    public function toArray($deep = false){

        // Declare an array for return values, got by deep mode
        $array = array();

        // If $deep argument is a boolean
        if (is_bool($deep)) {

            // Fulfil that array
            foreach ($this as $row) $array[] = $row->toArray('current', $deep);

        // Else if $deep argument is a string, we assume it's a comma-separated
        // columns/properties list that each item of result array should consist of
        } else if (is_string($deep)) {

            // Get the column names
            $columnA = explode(',', $deep);

            // Fulfil that array
            foreach ($this as $i => $row) foreach ($columnA as $columnI) $array[$i][$columnI] = $row->$columnI;
        }

        // Return result
        return $array;
    }

    /**
     * Return a model, that current rowset is related to
     *
     * @return Indi_Db_Table
     */
    public function model() {
        return Indi::model($this->_table);
    }

    /**
     * Return a database table name, that current rowset is dealing with
     *
     * @return mixed
     */
    public function table() {
        return $this->_table;
    }

    /**
     * Reverse order of items in $this->_original array
     */
    public function reverse() {
    
        // Reverse rows
        $this->_rows = array_reverse($this->_rows);
        
        // Return rowset itself
        return $this;
    }

    /**
     * Returns the number of row in the rowset.
     * Required by interface Countable
     *
     * @return int
     */
    public function count() {
        return $this->_count;
    }

    /**
     * Check if an offset exists
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($this->_rows[(int) $offset]);
    }

    /**
     * Get the row for the given offset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @throws Exception
     * @return Indi_Db_Table_Row|mixed
     */
    public function offsetGet($offset) {
        $offset = (int) $offset;
        if ($offset < 0 || $offset >= $this->_count) {
            return null;
        }
        $this->_pointer = $offset;

        return $this->current();
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {

    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     */
    public function offsetUnset($offset) {

    }

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return Indi_Db_Table_Rowset|void Fluent interface.
     */
    public function rewind() {
        // Set the internal pointer to 0
        $this->_pointer = 0;

        // Return current rowset itself
        return $this;
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return scalar
     */
    public function key() {
        return $this->_pointer;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return Indi_Db_Table_Rowset|void Fluent interface
     */
    public function next() {

        // Increment internal pointer
        $this->_pointer++;

        // Return current rowset itself
        return $this;
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     * Used to check if we've iterated to the end of the collection.
     * Required by interface Iterator.
     *
     * @return bool False if there's nothing more to iterate over
     */
    public function valid() {
        return $this->_pointer >= 0 && $this->_pointer < $this->_count;
    }

    /**
     * Take the Iterator to position $position
     * Required by interface SeekableIterator.
     *
     * @param int $position the position to seek to
     * @throws Exception
     * @return Indi_Db_Table_Rowset_Abstract|void Fluent interface
     */
    public function seek($position) {
        // Force $position argument to int
        $position = (int) $position;

        // If $position is out of rowset bounds, throw an exception
        if ($position < 0 || $position >= $this->_count)  throw new Exception("Illegal index $position");

        // Update an internal pointer
        $this->_pointer = $position;

        // Return current rowset itself
        return $this;
    }

    /**
     * Get the last row from current rowset
     *
     * @param int $stepsBack If this argument is set, function will return row, located at index, that is back shifted
     *                       by $stepsBack times from the index of last row
     * @return Indi_Db_Table_Row|null
     */
    public function last($stepsBack = 0) {

        // Backup internal pointer current value
        $savePointer = $this->_pointer;

        // Set internal pointer current value as last row index, decremented by $stepsBack argument
        $this->_pointer = $this->_count - 1 - (int) $stepsBack;

        // Get the row
        $last = $this->current();

        // Restore internal pointer value from backup
        $this->_pointer = $savePointer;

        // Return row
        return $last;
    }

    /**
     * Exclude items from current rowset, that have keys, mentioned in $keys argument.
     * If $inverse argument is set to true, then function will return only rows,
     * which keys are mentioned in $keys argument
     *
     * @param string|array $keys Can be array or comma-separated list of ids
     * @param string $type Name of key, which value will be tested for existence in keys list
     * @param boolean $inverse
     * @return Indi_Db_Table_Rowset Fluent interface
     */
    public function exclude($keys, $type = 'id', $inverse = false){

        // Process keys
        list($keys, $expr) = $this->_selector($keys);

        // Flip $keys array
        if (is_array($keys)) $keys = array_flip($keys);

        // For each item in $this->_original array
        foreach ($this->_rows as $index => $row) {

            // If we deal with an expression
            if ($expr) {

                // Temporary value
                $m_ = $row->$type; $match = false;

                // Detect match
                if (preg_match('/^(\/|#|\+|%)[^\1]*\1[imsxeu]*$/', $expr))
                    eval('$match = preg_match($expr, $m_);'); else eval('$match = $m_ ' . $expr . ';');

                // Set $cond flag
                $cond = $inverse ? !$match : $match;

                // Else
            } else {

                // Check key
                $ake = array_key_exists($row->$type, $keys);

                // Set $cond flag
                $cond = $inverse ? !$ake : $ake;
            }

            // Finally, if row should be unset
            if ($cond) {

                // Unset row
                unset($this->_rows[$index]);

                // Decrement count of items in current rowset
                $this->_count --;
            }
        }

        // Force zero-indexing
        $this->_rows = array_values($this->_rows);

        // Force $this->_pointer to be not out from the bounds of current rowset
        if ($this->_pointer > $this->_count) $this->_pointer = $this->_count;

        // Return rowset itself
        return $this;
    }

    /**
     * Remove $count items from the beginning of rowset
     *
     * @param int $count
     */
    public function shift($count = 1) {

        // Remove rows
        for ($i = 0; $i < $count; $i++) {

            // Remove item from $this->_rows array
            array_shift($this->_rows);

            // Decrement $this->_count prop
            $this->_count --;

            // Force $this->_pointer to be not out from the bounds of current rowset
            if ($this->_pointer > $this->_count) $this->_pointer = $this->_count;
        }
    }

    /**
     * Remove $count items from the ending of rowset
     *
     * @param int $count
     */
    public function pop($count = 1) {

        // Remove rows
        for ($i = 0; $i < $count; $i++) {

            // Remove item from $this->_rows array
            array_pop($this->_rows);

            // Decrement $this->_count prop
            $this->_count --;

            // Force $this->_pointer to be not out from the bounds of current rowset
            if ($this->_pointer > $this->_count) $this->_pointer = $this->_count;
        }
    }

    /**
     * Empty rowset
     *
     * @return Indi_Db_Table_Rowset
     */
    public function truncate(){
        $this->_found = 0;
        $this->_count = 0;
        $this->_rows = array();
        return $this;
    }

    /**
     * Force rowset to contain only rows, that have keys, mentioned in $keys argument
     * If $clone argument is set to true (this is it's default value), a clone of current rowset
     * will be filtered and returned. Otherwise - method will operate with current rowset instead of it's clone
     *
     * @param $keys
     * @param string $type
     * @param bool $clone
     * @return Indi_Db_Table_Rowset Fluent interface
     */
    public function select($keys, $type = 'id', $clone = true) {

        // If $clone argument is set to true
        if ($clone) {

            // Clone current rowset
            $clone = clone $this;

            // Make a selection (inverted exclusion)
            return $clone->exclude($keys, $type, true);

        // Else of a selection on current rowset
        } else return $this->exclude($keys, $type, true);
    }

    /**
     * Return the number of page of results, that current rowset is representing
     *
     * @return int|null
     */
    public function page(){
        return $this->_page;
    }

    /**
     * Return the query, that was used to fetch current rowset
     *
     * @return string
     */
    public function query(){
        return $this->_query;
    }

    /**
     * If $found argument is null or not given, function will return the total count of rows, that can be fetched
     * in case if LIMIT clause would not be used in sql query. Else if $found argument is 'unset' function will
     * unset $this->_found property from current rowset, otherwise, if $found argument is numeric - function will
     * set $this->_found property equal to $found argument, and return that property
     *
     * @param string|int|null $found
     * @return int|null
     */
    public function found($found = null) {

        // If $found argument is 'unset' - unset the $this->_found property
        if ($found == 'unset') unset($this->_found);

        // Else if $found argument is numeric - set $this->_found property equal to $found argument, and return it
        else if (preg_match('/^[0-9]+$/', $found)) return $this->_found = $found;

        // Else just return $this->_found property
        else return $this->_found;
    }

    /**
     * Converts a rowset to grid data array, using current trail item details, such as columns, filters, etc
     *
     * @param string $fields Comma-separated list of field names
     * @return array
     */
    public function toGridData($fields) {

        // If there are no rows in $this argument - return
        if ($this->_count == 0) return array();

        // Declare an array for aliases of grid fields
        $columnA = array('id');

        // Build the array, containing all possible types of fields, that grid columns are linked to. We need to do that
        // because there will be different transformations performed on data, for ability to human-friendly displaying
        $typeA = array(
            'foreign' => array(
                'single' => array(),
                'multiple' => array()
            ),
            'boolean' => array(),
            'price' => array(),
            'date' => array(),
            'datetime' => array(),
            'upload' => array(),
            'other' => array('id' => true),
            'shade' => array(),
            'further' => array()
        );

        // Get fields
        $fieldRs = $this->model()->fields(im(ar($fields)), 'rowset');

        // Setup actual info about types of columns, that we will have to deal with
        foreach ($fieldRs as $gridFieldR) {

            // If grid column have further-foreign field defined
            if ($this->model()->id() != $gridFieldR->entityId
                && preg_match('~^([0-9a-zA-Z_]+)_([0-9a-zA-Z]+)$~', $gridFieldR->alias, $m)
                && ($foreign = $this->model()->fields($m[1]))
                && ($foreign->storeRelationAbility != 'none')) {

                // Mode shortcut
                $mode = $foreign->storeRelationAbility == 'one' ? 'single' : 'multiple';

                // Collect further-foreign fields info in a format, compatible with $this->foreign() usage
                if ($further = Indi::model($foreign->relation)->fields($m[2]))
                    if ($further->storeRelationAbility != 'none')
                        $typeA['foreign'][$mode][$foreign->alias]['foreign'][0] []= $m[2];

                // Collect further-foreign fields info in a format, easy for detecting
                // whether some field is a further-foreign field at a later stage
                $typeA['further'][$gridFieldR->alias] = true;
            }

            // Foreign keys (single and multiple)
            if ($gridFieldR->original('storeRelationAbility') == 'one')
                $typeA['foreign']['single'][$gridFieldR->alias]['title'] = $gridFieldR->relation
                    ? ($gridFieldR->params['titleColumn'] ?: Indi::model($gridFieldR->relation)->titleColumn())
                    : true;

            else if ($gridFieldR->original('storeRelationAbility') == 'many')
                $typeA['foreign']['multiple'][$gridFieldR->alias]['title'] = $gridFieldR->relation
                    ? ($gridFieldR->params['titleColumn'] ?: Indi::model($gridFieldR->relation)->titleColumn())
                    : true;

            // Boolean values
            else if ($gridFieldR->foreign('columnTypeId')->type == 'BOOLEAN') $typeA['boolean'][$gridFieldR->alias] = true;

            // Decimal values (prices, etc)
            else if (preg_match('/^DECIMAL\(\d+,(\d+)\)$/', $gridFieldR->foreign('columnTypeId')->type, $m))
                $typeA['price'][$gridFieldR->alias] = $m[1];

            // Date and datetime values. Also we're getting additional params - display format at least
            else if ($gridFieldR->foreign('columnTypeId')->type == 'DATE')
                $typeA['date'][$gridFieldR->alias] = $gridFieldR->params;
            else if ($gridFieldR->foreign('columnTypeId')->type == 'DATETIME')
                $typeA['datetime'][$gridFieldR->alias] = $gridFieldR->params;

            // File-uploads
            else if ($gridFieldR->foreign('elementId')->alias == 'upload')
                $typeA['upload'][$gridFieldR->alias] = true;

            // All other types
            else $typeA['other'][$gridFieldR->alias] = true;

            // Shaded fields
            if (Indi::demo(false) && ($gridFieldR->param('shade')  || (
                    ($_ = $gridFieldR->relation) && ($_ = Indi::model($_)) && ($_ = $_->titleField()) && $_->param('shade')
                ))) $typeA['shade'][$gridFieldR->alias] = $gridFieldR->param();                

            // Append current grid field alias to $columnA array
            $columnA[] = $gridFieldR->alias;
        }

        // Set up $titleProp variable as an indicator of that titleColumn is within grid fields
        if (in($titleColumn = $this->model()->titleColumn(), $columnA)) $titleProp = $titleColumn;

        // Setup foreign rows, fetched by foreign keys, mentioned in fields, that are set up as grid columns
        if ($foreign = $typeA['foreign']['single'] + $typeA['foreign']['multiple']) $this->foreign($foreign);

        // Declare an array for grid data
        $data = array();

        // Get tree column
        $treeColumn = $this->model()->treeColumn();

        // Foreach row within $this rowset
        foreach ($this as $pointer => $r) {

            // Append system data
            $data[$pointer]['_system'] = $r->system();

            // Merge with temporary props
            $data[$pointer] = array_merge($data[$pointer], $r->toArray('temporary', false));

            // Foreach field column within each row we check if we should perform any transformation
            foreach ($columnA as $columnI) {

                // Shortcuts
                $entry = $r; $value = $r->$columnI; $further = false;

                // If grid column have further-foreign field defined
                if ($typeA['further'][$columnI] && preg_match('~^([0-9a-zA-Z_]+)_([0-9a-zA-Z]+)$~', $columnI, $m)) {

                    // Spoof entry
                    $entry = $r->foreign($m[1]);

                    // Spoof value
                    $value = $entry->{$further = $m[2]};
                }

                // If field column type is regular, e.g no foreign keys, no prices, no dates, etc. - we do no changes
                if (isset($typeA['other'][$columnI])) $data[$pointer][$columnI] = $value;

                // If field column type is 'decimal', we right pad column value by certain precision length
                // so if current row's price is '30.5' - we convert it to '30.50'
                if (isset($typeA['price'][$columnI]))
                    $data[$pointer][$columnI] = count($parts = explode('.', $value))
                        ? $parts[0] . '.' . str_pad($parts[1], $typeA['price'][$columnI], '0', STR_PAD_RIGHT)
                        : $data[$pointer][$columnI] . str_pad('.', $typeA['price'][$columnI] + 1, '0', STR_PAD_RIGHT);

                // If field column type is 'boolean', we replace actual value with localized 'Yes' or 'No' strings
                if (isset($typeA['boolean'][$columnI])) $data[$pointer][$columnI] = $value ? I_YES : I_NO;

                // If field column type is a single foreign key, we use title of related foreign row
                if (isset($typeA['foreign']['single'][$columnI]['title']) && $entry) $data[$pointer][$columnI] = $entry->foreign($further ?: $columnI)
                    ->{is_string($title = $typeA['foreign']['single'][$columnI]['title']) ? $title : 'title'};

                // If field column type is a multiple foreign key, we use comma-separated titles of related foreign rows
                if (isset($typeA['foreign']['multiple'][$columnI]['title']) && $entry)
                    foreach ($entry->foreign($further ?: $columnI) as $m)
                        $data[$pointer][$columnI] .= $m
                            ->{is_string($title = $typeA['foreign']['multiple'][$columnI]['title']) ? $title : 'title'} .
                            ($entry->foreign($further ?: $columnI)->key() < $entry->foreign($further ?: $columnI)->count() - 1 ? ', ' : '');

                // If field column type is 'date' we adjust it's format if need. If date is '0000-00-00' we set it
                // to empty string
                if (isset($typeA['date'][$columnI])
                    && $typeA['date'][$columnI]['displayFormat']
                    && preg_match(Indi::rex('date'), $value))

                    $data[$pointer][$columnI] = $value == '0000-00-00'
                        ? ''
                        : date($typeA['date'][$columnI]['displayFormat'], strtotime($value));

                // If field column type is datetime, we adjust it's format if need. If datetime is '0000-00-00 00:00:00'
                // we set it to empty string
                if (isset($typeA['datetime'][$columnI])
                    && ($typeA['datetime'][$columnI]['displayDateFormat'] || $typeA['datetime'][$columnI]['displayTimeFormat'])) {

                    if (!$typeA['datetime'][$columnI]['displayDateFormat'])
                        $typeA['datetime'][$columnI]['displayDateFormat'] = 'Y-m-d';
                    if (!$typeA['datetime'][$columnI]['displayTimeFormat'])
                        $typeA['datetime'][$columnI]['displayTimeFormat'] = 'H:i:s';

                    $data[$pointer][$columnI] = $value == '0000-00-00 00:00:00'
                        ? '' : ldate($typeA['datetime'][$columnI]['displayDateFormat'] . ' ' .
                            $typeA['datetime'][$columnI]['displayTimeFormat'], strtotime($value),
                            $typeA['datetime'][$columnI]['when']);
                }

                // If field type is fileupload, we build something like
                // '<a href="/url/for/file/download/">DOCX » 1.25mb</a>'
                if (isset($typeA['upload'][$columnI])) {
                    $file = $entry->file($columnI);
                    $data[$pointer][$columnI] = $file->link;
                    $data[$pointer]['_upload'][$columnI]['type'] = $file->type;
                }

                // If there the color-value in format 'hue#rrgbb' can probably be found in field value
                // we do a try, and if found - inject a '.i-color-box' element
                if (   isset($typeA['other'][$columnI])
                    || isset($typeA['foreign']['single'][$columnI]['title'])
                    || isset($typeA['foreign']['multiple'][$columnI]['title'])) {

                    // Process color boxes
                    if (preg_match(Indi::rex('hrgb'), $data[$pointer][$columnI], $color)) {
                        $data[$pointer][$columnI] = '<span class="i-color-box" style="background: #'
                            . $color[1] . ';"></span>#'. $color[1];
                    } else if (preg_match(Indi::rex('hrgb'), $value, $color)) {
                        $data[$pointer][$columnI] = '<span class="i-color-box" style="background: #'
                            . $color[1] . ';"></span>';
                    } else if (preg_match('/box/', $data[$pointer][$columnI]) && !in($this->table(), 'enumset,changeLog')) {
                        if (preg_match('/background:\s*url\(/', $data[$pointer][$columnI])) {
                            if ($this->model()->fields($columnI)->relation == 6) {
                                $data[$pointer][$columnI] = preg_replace('/(><\/span>)(.*)$/', ' title="$2"$1', $data[$pointer][$columnI]);
                            }
                        } else {
                            $data[$pointer][$columnI] = preg_replace('/(><\/span>)(.*)$/', ' title="$2"$1', $data[$pointer][$columnI]);
                        }
                    }
                }

                // If field should be shaded - prevent actual value from being assigned
                if (isset($typeA['shade'][$columnI])) if ($value) $data[$pointer][$columnI] = I_PRIVATE_DATA;

                // Include the original foreign keys data
                if (isset($typeA['foreign']['single'][$columnI]['title'])
                    || isset($typeA['foreign']['multiple'][$columnI]['title'])
                    || isset($typeA['boolean'][$columnI]))
                    $data[$pointer]['$keys'][$columnI] = $value;
            }

            // Setup special 'title' property within '_system' property. This is for having proper title
            // for each grid data row, event if grid does not have `title` property at all, or have, but
            // affected by indents or some other manipulations
            $data[$pointer]['_system']['title'] = $titleProp ? $data[$pointer][$titleProp] : $r->title();
            
            // Implement indents if need
            if ($data[$pointer][$_ = $titleProp ?: 'title'] && $treeColumn)
                if ($r->system('level') !== null || $r->system('level', $r->level()))
                    $data[$pointer]['_render'][$_]
                        = str_repeat('&nbsp;', 5 * $r->system('level')) . $data[$pointer][$_];

            // Unset '_foreign'
            unset($data[$pointer]['_foreign']);
        }

        // Return grid data
        return $data;
    }

    /**
     * Fetch the rowset, nested to current rowset, assing that rowset within $this->_nested array under certain key,
     * and return that rowset
     *
     * @param string $table A table, where rowset will be fetched from
     * @param array $fetch Array of fetch params, that are same as Indi_Db_Table::fetchAll() possible arguments
     * @param null $alias The key, that fetched rowset will be stored in $this->_nested array under
     * @param null $field Connector field, in case if it is different from $this->_table . 'Id'
     * @param bool $fresh Flag for rowset refresh
     * @return Indi_Db_Table_Rowset object
     * @throws Exception
     */
    public function nested($table, $fetch = array(), $alias = null, $field = null, $fresh = false) {

        // Determine the nested rowset identifier. If $alias argument is not null, we will assume that needed rowset
        // is or should be stored under $alias key within $this->_nested array, or under $table key otherwise.
        // This is useful in cases when we need to deal with nested rowsets, got from same database table, but
        // with different fetch params, such as WHERE, ORDER, LIMIT clauses, etc.
        $key = $alias ? $alias : $table;

        // If needed nested rowset is already exists within $this->_nested array, and it shouldn't be refreshed
        if (array_key_exists($key, $this->_nested) && !$fresh) {

            // If $fetch argument is 'unset', we do unset nested data, stored under $key key within $this->_nested
            // Here we use $fetch argument, instead of $fresh agrument, for more friendly unsetting usage, e.g
            // $rs->nested('table', 'unset') instead of $rs->nested('table', null, null, null, 'unset')
            if ($fetch == 'unset') {

                foreach ($this as $r) $r->nested($key, 'unset');

                // Unset nested data
                unset($this->_nested[$key]);

                // Return row itself
                return $this;

            // Else we return it
            } else {

                foreach ($this as $r) $nested[$r->id] = $r->nested($key);

                return $nested;
            }

        // Otherwise we fetch it, assign it under $key key within $this->_nested array and return it
        } else {

            // Determine the field, that is a connector between current row and nested rowset
            $connector = $field ? $field : $this->_table . 'Id';

            // If $fetch argument is array
            if (is_array($fetch)) {

                // Define the allowed keys within $fetch array
                $params = array('where', 'order', 'count', 'page', 'offset', 'foreign', 'nested');

                // Unset all keys within $fetch array, that are not allowed
                foreach ($fetch as $k => $v) if (!in_array($k, $params)) unset($fetch[$k]);

                // Extract allowed keys with their values from $fetch array
                extract($fetch);
            }

            // Convert $where to array
            $where = isset($where) && is_array($where) ? $where : (strlen($where) ? array($where) : array());

            // Get all the ids of rows within current rowset
            $idA = array(); foreach ($this as $i) $idA[] = $i->id;

            // If current rowset is not empty
            if ($this->_count) {

                // If connector field store relation ability is multiple
                if (Indi::model($table)->fields($connector)->storeRelationAbility == 'many')

                    // We use REGEXP sql expression for prepending $where array
                    array_unshift($where,
                        'CONCAT(",", `' . $connector . '`, ",") REGEXP ",(' . implode('|', $idA) . '),"');

                // Else if connector field store relation ability is single
                else if (Indi::model($table)->fields($connector)->storeRelationAbility == 'one')

                    // We use IN sql expression for prepending $where array. If
                    array_unshift($where,
                        count($idA)
                            ? '`' . $connector . '` IN (' . implode(',', $idA) . ')'
                            : '0'
                    );

                // Else an Exception will be thrown, as connector field do not exists or don't have store relation ability
                else throw new Exception('Connector field `' . $connector . '` do not exists or don\'t have store relation ability');

            // Else prepend $where array with '0', so no nested rows will be found
            } else array_unshift($where, '0');

            // Fetch rowset containing rows, that are nested to at least one row of current rowset
            $nestedRs = Indi::model($table)->fetchAll($where, $order, $count, $page, $offset);

            // Setup foreign data for nested rowset, if need
            if ($foreign) $nestedRs->foreign($foreign);

            // Setup nested data for nested rowset, if need
            if ($nested) {
                if (is_array($nested)) $nestedRs->nested($nested[0], $nested[1], $nested[2], $nested[3], $nested[4]);
                else $nestedRs->nested($nested);
            }

            // Declare an array for nested rows distribution by connector values
            $cNested = array();

            // Find matches, and collected keys or rows, that won't be excluded from clone of $allNestedRs rowset
            foreach ($nestedRs as $nestedR)

                // If connector field is multiple, foreach unique value within that multiple - append a row
                if (Indi::model($table)->fields($connector)->storeRelationAbility == 'many') {
                    foreach (explode(',', $nestedR->$connector) as $i)
                        if (strlen($i))
                            $cNested[$i][] = $nestedR;

                // Else we use usual approach
                } else if (Indi::model($table)->fields($connector)->storeRelationAbility == 'one') {
                    $cNested[$nestedR->$connector][] = $nestedR;
                }

            // Now we should assign appropriate nested data to each row within current rowset
            foreach ($this as $r) {

                // Assign
                $r->nested($key, Indi::model($table)->createRowset(
                    $cNested[$r->id] && count($cNested[$r->id]) ? array('rows' => $cNested[$r->id]) : array()
                ));

                // Setup a flag indicating that there is a nested data for $key key within rows in current rowset
                $this->_nested[$key] = true;
            }

            // Return rowset itself
            return $this;
        }
    }

    /**
     * Gets the foreign rows/rowsets by foreign key name, depending on foreign key field store relation ability,
     * with usage current values of these keys, mentioned in all rows in current rowset
     *
     * @param $key Single foreign key name, or comma-separated list of foreign key names, or array of foreign key names
     *             Usage examples:
     *             1. $myRowsetObject->foreign('foreignKeyName');
     *             2. $myRowsetObject->foreign('foreignKey1Name,foreignKey2Name');
     *             3. $myRowsetObject->foreign(array(
     *                  'foreignKey1Name' => 'subForeignKeyXName',
     *                  'foreignKey2Name
     *                ));
     *             4. $myRowsetObject->foreign(array(
     *                  'foreignKey1Name' => 'subForeignKeyAName,subForeignKeyBName'
     *                ));
     *             5. $myRowsetObject->foreign(array(
     *                  'foreignKey1Name' => 'subForeignKeyAName,subForeignKeyBName',
     *                  'foreignKey2Name' => 'subForeignKeyCName,subForeignKeyDName'
     *                ));
     *             6. $myRowsetObject->foreign(array(
     *                  'foreignKey1Name' => array(
     *                      'subForeignKeyAName' => 'subSubForeignKeyCName'
     *                      'subForeignKeyBName'
     *                  ),
     *                  'foreignKey2Name' => 'subForeignKeyCName,subForeignKeyDName'
     *                ));
     *
     *             So, function allow to fetch
     *             1. foreign data,
     *             2. foreign data for foreign data,
     *             3. foreign data for foreign data for foreign data,
     *             4. etc
     *
     * @param null $subs Keyword, that affects format of return value and whether or not foreign data should be
     *                   refreshed. Mode can be: null, 'column', 'columns', 'refresh', 'refresh column', 'refresh columns'.
     *                   If neither 'columns' nor 'column' keywords if not exists within $mode argument, then current
     *                   rowset will be returned, else if $mode argument contains 'columns' keyword, the associative
     *                   array of foreign data, grouped by foreign key names will be returned, else if $mode argument
     *                   contains 'column' keyword, then associative array of foreign data will be returned, with ids
     *                   of rows of current rowset (let's call them 'original rows') as keys, and foreign rows/rowsets,
     *                   got for 'original rows' as values
     * @return array|Indi_Db_Table_Rowset
     * @throws Exception
     */
    public function foreign($key, $subs = null) {

        // If no rows within this rowset - return
        if (!$this->count()) return $this;

        // If $key argument is string
        if (is_string($key)) {

            // If $key argument contains more than one key name - we setup rows for all keys
            if (preg_match('/,/', $key)) {

                // Prevent unsupported usage
                if (!is_string($subs)) $subs = null;

                // Convert keys list to array by exploding by comma
                $keyA = explode(',', $key);

                // If $subs argument contains 'columns' keyword
                if (preg_match('/columns/', $subs)) {

                    // If there actually was only one key name in $key argument, we replace 'columns' with 'column' in
                    // $subs argument, to avoid infinite recursion and to provide an ability for results to be returned
                    // as if there were multipler keys passed within $keys argument
                    $subs = str_replace('columns', 'column', $subs);

                    // We collect all foreign data as associative array, where keys are keys from $keys argument
                    $columnA = array(); foreach ($keyA as $keyI) $columnA[trim($keyI)] = $this->foreign(trim($keyI), $subs);

                    // Return collected data
                    return $columnA;

                    // Otherwise we do a ordinary foreign data setup
                } else foreach ($keyA as $keyI) $this->foreign(trim($keyI), $subs);

                // Return current
                return $this;
            }

        // Else if $key argument is an array
        } else if (is_array($key)) {

            // Foreach item in that array
            foreach ($key as $keyI => $subs) {

                // If $key arg is an integer, this means that $key arg looks like this:
                // array(
                //     'foreignKey1Name' => 'subForeignKeyXName',
                //     'foreignKey2Name      // equal to '0' => 'foreignKey2Name'
                // )
                // So, for 'foreignKey2Name', we need to spoof args for below $this->foreign() call
                if (Indi::rexm('int11', $keyI)) {
                    $keyI = $subs;
                    $subs = array();

                // Else is $subs is string
                } else if (is_string($subs) && !preg_match('~^columns?$~', $subs)) {
                    $subs = array('foreign' => $subs);
                }

                // Setup foreign data
                $this->foreign(trim($keyI), $subs);
            }

            // Return rowset itself
            return $this;
        }

        // Detect if $key argument contains a directive to call some function on a foreign data rowset
        if (preg_match('/:/', $key)) list($key, $call) = explode(':', $key);

        // If field, representing foreign key - is exist within current entity
        if ($fieldR = $this->model()->fields($key)) {

            // If $subs argument contains no 'refresh' keyword, and foreign data for $key key is already exists within
            // $this->_foreign array, we return existing data, and if $subs argument contains 'column' keyword - we
            // return existing foreign data separately, without returning current rowset itself, otherwise, if $subs
            // argument does not contain 'column' keyword - we return rowset itself
            if (is_string($subs) && !preg_match('/refresh/', $subs))
                if (is_array($this->_foreign) && array_key_exists($key, $this->_foreign))
                    return preg_match('/column/', $subs)
                        ? (preg_match('/columns/', $subs)
                            ? array($key => $this->_foreign[$key])
                            : $this->_foreign[$key])
                        : $this;

            // If field do not store foreign keys - throw exception
            if ($fieldR->storeRelationAbility == 'none'
                || ($fieldR->relation == 0 && ($fieldR->dependency != 'e' && !$fieldR->nested('consider')->count())))
                throw new Exception('Field with alias `' . $key . '` within entity with table name `' . $this->_table .'` is not a foreign key');

            // Declare array for distinct values of foreign keys
            $distinctA = array();

            // If field dependency is 'Variable entity'
            if ($fieldR->relation == 0 && $fieldR->nested('consider')->count()) {

                // Get consider-field, e.g. field, that current field depends on
                $consider = $fieldR->nested('consider')->at(0)->foreign('consider')->alias;

                // Foreach row within current rowset
                foreach ($this as $r) {

                    // Get the id of entity, that current foreign key is related to
                    $entityId = $r->$consider;

                    // Collect foreign key values, grouped by entity id
                    $distinctA[$entityId] = array_merge(

                        // If there are already items exist within group, representing keys that are related
                        // to certain entity id (entity id is a consider as per 'Variable entity' concept),
                        // - we use this group as array which will be a first array in list of merged arrays
                        is_array($distinctA[$entityId]) ? $distinctA[$entityId] : array(),

                        // If foreign key field store relation ability is 'many', and foreign key value of
                        // current row is not empty, we convert it to array by exploding by comma, and append
                        // to $distinctA array under $entityId key, otherwise we create and array that contains
                        // only one item and also append it to $distinctA array under $entityId key
                        strlen($r->$key)
                            ? ($fieldR->original('storeRelationAbility') == 'many'
                                ? explode(',', $r->$key)
                                : array($r->$key))
                            : array()
                    );
                }

            // Else if foreign field dependency is not 'Variable entity', that mean that we deal with single entity
            // - entity, that current foreign key is related to
            } else

                // Foreach row within current rowset
                foreach ($this as $r)

                    // Collect foreign key values, grouped by id of entity, that current foreign key field is related to
                    $distinctA[$fieldR->relation] = array_merge(

                        // If there are already items exist within group, representing keys that are related
                        // to related entity id - we use this group as array which will be a first array in list
                        // of merged arrays, otherwise we use empty array to aviod php warning messages
                        is_array($distinctA[$fieldR->relation]) ? $distinctA[$fieldR->relation] : array(),

                        // If foreign key field store relation ability is 'many', and foreign key value of
                        // current row is not empty, we convert it to array by exploding by comma, and append
                        // to $distinctA array under $entityId key, otherwise we create and array that contains
                        // only one item and also append it to $distinctA array under $entityId key
                        strlen($r->$key)
                            ? ($fieldR->original('storeRelationAbility') == 'many'
                                ? explode(',', $r->$key)
                                : array($r->$key))
                            : array()
                    );

            // Strip foreign key values, that appear twice or more within $distinctA array under
            // their entity ids, so values in $distinctA array will now be truly distinct
            foreach ($distinctA as $entityId => $keys) $distinctA[$entityId] = array_unique($distinctA[$entityId]);

            // Check whether or not current field has a column within database table
            $imitated = !array_key_exists($fieldR->alias, $this->at(0)->original());

            // For each $entityId => $key pair within $distinctA array we fetch rowsets, that contain all rows that
            // are 'mentioned' in all rows within current rowset
            foreach ($distinctA as $entityId => $keys) {

                // If current field is an imitated-field, and is a enumset-filed
                if ($imitated && Indi::model($entityId)->table() == 'enumset') {

                    // Fetch foreign data with no db-request
                    $foreignRs[$entityId] = $fieldR->nested('enumset')->select($keys, 'alias');

                // Else
                } else {

                    // Declare array for WHERE clause
                    $where = array();

                    // If current $entityId is id of enumset entity, we should append an additional WHERE clause,
                    // that will outline the `fieldId` value, because in this case rows in current rowset store
                    // aliases of rows from `enumset` table instead of ids, and aliases are not unique within that table.
                    if (Indi::model($entityId)->table() == 'enumset') {

                        // Set the first part of WHERE clause
                        $where[] = '`fieldId` = "' . $fieldR->id . '"';

                        // Set the name of column, that will be involved in sql ' IN() ' statement
                        $col = 'alias';

                        // Set the quotation, as enumset keys are strings in most cases
                        $q = '"';

                    } else {

                        // Set the name of column, that will be involved in sql ' IN() ' statement
                        $col = 'id';
                    }

                    // If foreign model's `preload` flag was turned On
                    if (Indi::model($entityId)->preload()) {

                        // Use preloaded data as foreign data rather than
                        // obtaining foreign data by separate sql-query
                        $foreignRs[$entityId] = Indi::model($entityId)->preloadedAll($distinctA[$entityId]);

                    // Else fetch foreign from db
                    } else {

                        // Finish building WHERE clause
                        $where[] = count($distinctA[$entityId])
                            ? '`' . $col . '` IN (' . $q . implode($q . ',' . $q, $distinctA[$entityId]) . $q . ')'
                            : 'FALSE';

                        // Fetch foreign data
                        $foreignRs[$entityId] = Indi::model($entityId)->fetchAll($where);
                    }
                }

                // Adjust foreign rowset
                $this->_adjustForeignRowset($key, $foreignRs[$entityId]);

                // Call a user-defined method for foreign data rowset, if need
                if ($call) eval('$foreignRs[$entityId]->' . $call . ';');

                // Setup a foreign and nested data for just fetched foreign data, by a recursive logic
                if (is_array($subs)) {

                    // Setup foreign data
                    if ($subs['foreign']) {
                        if (is_string($subs['foreign'])) {
                            $foreignRs[$entityId]->foreign($subs['foreign']);
                        } else if (is_array($subs['foreign']) && (key($subs['foreign']) == '0')) {
                            $foreignRs[$entityId]->foreign($subs['foreign'][0], $subs['foreign'][1]);
                        } else {
                            $foreignRs[$entityId]->foreign($subs['foreign']);
                        }
                    }

                    // Setup nested data
                    if ($subs['nested']) {
                        if (is_array($subs['nested'])) {
                            if (key($subs['nested']) == '0') {
                                $foreignRs[$entityId]->nested($subs['nested'][0], $subs['nested'][1], $subs['nested'][2],
                                    $subs['nested'][3], $subs['nested'][4]);

                            } else {
                                foreach ($subs['nested'] as $table => $args)
                                    $foreignRs[$entityId]->nested($table, array(
                                            'where' => $args['where'], 'order' => $args['order'],
                                            'count' => $args['count'], 'page' => $args['page'],
                                            'offset' => $args['offset'], 'foreign' => $args['foreign'],
                                            'nested' => $args['nested']
                                        ), $args['alias'], $args['field'], $args['fresh']
                                    );
                            }
                        } else {
                            $foreignRs[$entityId]->nested($subs['nested']);
                        }
                    }
                }
            }

            // For each row within current rowset
            foreach ($this as $r) {

                // Get the id of entity, that current row's foreign key is related to. If foreign key field
                // dependency is 'Variable entity' - entity id is dynamic, that mean is may differ for each row
                $foreignKeyEntityId = $fieldR->relation ?: $r->{$fieldR->nested('consider')->at(0)->foreign('consider')->alias};

                // Get the column name, which value will be used for match
                $col = $foreignKeyEntityId == 6 ? 'alias' : 'id';

                // If current foreign key field is able to store only one key
                if ($fieldR->original('storeRelationAbility') == 'one') {

                    // For each foreign row, fetched for entity, that have same id as $foreignKeyEntityId
                    foreach ($foreignRs[$foreignKeyEntityId] as $foreignR) {

                        // If foreign key value of current row is equal to foreign row id
                        if (($col == 'alias' ? $foreignR->fieldId == $fieldR->id : true) && '' . $r->$key == '' . $foreignR->$col) {

                            // Assign foreign row directly
                            $r->foreign($key, $foreignR);

                            // Stop searching for matches, as there could be only one foreign row found,
                            // and it was already found and assigned
                            break;
                        }
                    }

                // Else if current foreign key field is able to store more that one key
                } else if ($fieldR->original('storeRelationAbility') == 'many') {

                    // Declare/reset array of rows, related to multiple-foreign-key, for current row within current rowset
                    $rows = array();

                    // Setup an array, containing keys, that will be used to find match in
                    $set = explode(',', $r->$key);

                    // For each foreign row, fetched for entity, that have same id as $foreignKeyEntityId
                    foreach ($foreignRs[$foreignKeyEntityId] as $foreignR)

                        // If foreign row identifier (`id` or `alias`) is exists within list of keys,
                        // represented by foreign key value of current row, we get the data of that foreign row
                        // and append it to $original array. Also, before doing that, we should check
                        // if foreign key entity id is 'enumset', and if so, we do an additional check for
                        // foreign row field id to be equal to foreign key field id, because values of `alias`
                        // column/property within `enumset` table are unique only within their fields, but are not
                        // within global scope
                        if (($col == 'alias' ? $foreignR->fieldId == $fieldR->id : true) && in_array($foreignR->$col, $set))
                            $rows[] = $foreignR;

                    // Ensure foreign rows will appear in the same order as their keys within $r->$key
                    $rows_ = array(); foreach ($rows as $row) $rows_[$row->$col] = $row;
                    $rows  = array(); foreach (ar($r->$key) as $j) $rows[] = $rows_[$j]; unset($rows_);

                    // Create a rowset object, with usage of data, collected in $rows array, and assing that rowset
                    // as a value within $this->_foreign property under current foreign key field name and current row
                    $r->foreign($key, Indi::model($foreignKeyEntityId)->createRowset(array('rows' => $rows)));

                    // Release the memory
                    unset($rows, $set);
                }
            }

            // If $subs argument contains 'column' keyword - we return that foreign data separately, without returning
            // current rowset itself, otherwise, if $subs argument does not contain 'column' keyword - we return rowset
            // itself
            return is_string($subs) && preg_match('/column/', $subs)
                ? (preg_match('/columns/', $subs)
                    ? array($key => $this->_foreign[$key])
                    : $this->_foreign[$key])
                : $this;

        // Else there is no such a field within current entity - throw an exception
        } else {
            throw new Exception('Field with alias `' . $key . '` does not exists within entity with table name `' . $this->_table .'`');
        }
    }

    /**
     * Get value of a single column within curent rowset, as array
     *
     * @param string $column
     * @param bool|string $imploded
     * @param bool $unique
     * @return array
     */
    public function column($column, $imploded = false, $unique = false) {

        // Check if $column arg contains multiple column names
        if (count(ar($column)) > 1) $multi = ar($column); else $multi = false;

        // Declare array for single column
        $valueA = array();

        // If multiple column names was passed within $column arg - force
        // $implode and $unique arg to be `false`, as their support is not yet
        // implemented for multi-columns mode, currently
        if ($multi) $imploded = $unique = false;

        // Strip duplicate values from $valueA array, if $unique argument is `true`
        if ($unique) {
            foreach ($this as $r) $valueA[$r->$column] = true;
            $valueA = array_keys($valueA);

        // Else simply collect column data
        } else foreach ($this as $r) {
            if ($multi) {
                $valueI = array(); foreach ($multi as $c) $valueI[$c] = $r->$c; $valueA[] = $valueI;
            } else {
                $valueA[] = $r->$column;
            }
        }

        // Return column data
        return $imploded ? implode(is_string($imploded) ? $imploded : ',', $valueA) : $valueA;
    }

    /**
     * Get the $this->_rows array
     *
     * @return array
     */
    public function rows() {
        return $this->_rows;
    }

    /**
     * Update usages of all rows's titles within current rowset
     */
    public function titleUsagesUpdate() {
        foreach ($this as $row) $row->titleUsagesUpdate();
    }

    /**
     * Convert current rowset to combo data array
     *
     * @param array $params
     * @param bool $ignoreTemplate
     * @return array
     */
    public function toComboData($params = array(), $ignoreTemplate = false) {

        // Declare $options array
        $options = array();

        // If 'optgroup' param is used - set $by variable's value as 'by' property of $this->optgroup property
        if ($this->optgroup) $by = $this->optgroup['by'];

        // Detect key property for options
        $keyProperty = $this->enumset ? 'alias' : 'id';

        // Option title maximum length
        $titleMaxLength = 0;

        // Option title maximum indent
        $titleMaxIndent = 0;

        // Get title-column field. If it's a foreig-key field - pull foreign data
        if ($tc = $this->model()->fields($this->titleColumn))
            if ($tc->storeRelationAbility != 'none')
                $this->foreign($foreign = $tc->alias);

        // Set column
        $column = $foreign ? $tc->rel()->titleColumn() : 'title';

        // Setup primary data for options. Here we use '$o' name instead of '$comboDataR', because
        // it is much more convenient to use such name to deal with option row object while creating
        // a template in $params['template'] contents, if it is set, because php expressions are executed
        // in current context
        foreach ($this as $o) {

            // Get initial array of system properties of an option
            $system = $o->system();

            // Set group identifier for an option
            if ($by) $system = array_merge($system, array('group' => $o->$by));

            // If title column's field is a foreign-key field - use it to obtain the actual title
            $title = $foreign ? $o->foreign($foreign)->$column : $o->{$this->titleColumn};

            // Here we are trying to detect, does $o->title have tag with color definition, for example
            // <span style="color: red">Some option title</span> or <font color=lime>Some option title</font>, etc.
            // We should do that because such tags existance may cause a dom errors while performing usubstr()
            $info = Indi_View_Helper_Admin_FormCombo::detectColor(array(
                'title' => $title, 'value' => $o->$keyProperty
            ));

            // If color was detected as a box, append $system['boxColor'] property
            if ($info['box']) $system['boxColor'] = $info['color'];

            // Get max length
            $substr = $params['substr'] ?: 50;

            // Setup primary option data
            $options[$o->$keyProperty] = array('title' => usubstr($info['title'], $substr), 'system' => $system);

            // Put trimmed part of option title into tooltip
            if (preg_match('/\.\.$/', $options[$o->$keyProperty]['title']))
                $options[$o->$keyProperty]['system']['tooltip'] = '..' . mb_substr($info['title'], $substr, 1024, 'utf-8');

            $options[$o->$keyProperty]['raw'] = $o->{$this->titleColumn};

            // Setup foreign entries titles
            if ($params['foreign'])
                foreach (ar($params['foreign']) as $fk)
                    if ($fr = $o->foreign($fk))
                        $options[$o->$keyProperty]['_foreign'][$fk] =  $fr->title();

            // If color box was detected, and it has box-type, we remember this fact
            if ($info['box']) $hasColorBox = true;

            // Update maximum option title length, if it exceeds previous maximum
            $noHtmlSpecialChars = preg_replace('/&[a-z]*;/', ' ',$options[$o->$keyProperty]['title']);
            if (mb_strlen($noHtmlSpecialChars, 'utf-8') > $titleMaxLength)
                $titleMaxLength = mb_strlen($noHtmlSpecialChars, 'utf-8');

            // Update maximum option title indent, if it exceeds previous maximum
            if ($this->model()->treeColumn()) {
                $indent = mb_strlen(preg_replace('/&nbsp;/', ' ', $options[$o->$keyProperty]['system']['indent']), 'utf-8');
                if ($indent > $titleMaxIndent) $titleMaxIndent = $indent;
            }

            // If color was found, we remember it for that option
            if ($info['style']) $options[$o->$keyProperty]['system']['color'] = $info['color'];

            // If 'optionTemplate' is not empty, and $ignoreTemplate argument is not boolean true
            if ($params['optionTemplate'] && !$ignoreTemplate)

                // Compile the template and put the result of the compilation into the 'option'
                // property within array of current option properties
                Indi::$cmpTpl = $params['optionTemplate']; eval(Indi::$cmpRun); $options[$o->$keyProperty]['option'] = Indi::cmpOut();

            // Deal with optionAttrs, if specified.
            if ($this->optionAttrs) {
                for ($i = 0; $i < count($this->optionAttrs); $i++) {
                    $options[$o->$keyProperty]['attrs'][$this->optionAttrs[$i]] = $o->{$this->optionAttrs[$i]};
                }
            }
        }

        // Return combo data
        return array(
            'options' => $options,
            'titleMaxLength' => $titleMaxLength,
            'titleMaxIndent' => $titleMaxIndent,
            'hasColorBox' => $hasColorBox,
            'keyProperty' => $keyProperty
        );
    }

    /**
     * Append row to current rowset, using $original argument as the base data for
     * construction of a row, that will be appended to the end, or injected at desired index
     * 
     * @param array|Indi_Db_Table_Row $original
     * @param int $index
     * @return Indi_Db_Table_Rowset
     */
    public function append($original, $index = null) {

        // Convert $original into *_Row instance, if need
        if ($original instanceof Indi_Db_Table_Row) $append = $original; else {
            $append = Indi::model($this->_table)->createRow();
            foreach($original as $prop => $value) $append->original($prop, $value);
        }

        // If $before arg is not given - append to th ending
        if ($index === null) $this->_rows[] = $append;

        // Else inject at desired index
        else array_splice($this->_rows, $index, 0, array($append));

        // Increase counters
        $this->_count++;
        $this->_found++;
        
        // Return rowset itself
        return $this;
    }

    /**
     * Convert current plain-tree rowset to a nesting-tree rowset. Actually, this function does not
     * modify current rowset, it just returns it's clone, that contains only root-level tree items,
     * and all other (non-root-level) items are accessible as nested items
     *
     * @return Indi_Db_Table_Rowset
     */
    public function toNestingTree() {

        // If current rowset is owned by a non-tree model - return rowset with no changes
        if (!($treeColumn = $this->model()->treeColumn())) return $this;

        // Get root-level items
        $rs = $this->select(0, $treeColumn);

        // Attach child items to each root item, recursively
        foreach ($rs as $r) $r->nestDescedants($this);

        // Return nesting-tree rowset
        return $rs;
    }

    /**
     * Same as offsetGet(), except that internal pointer value will remain same
     * 
     * @param $idx
     * @return Indi_Db_Table_Row|null
     */
    public function at($idx) {
        return $this->_rows[$idx];
    }

    /**
     * Get a row from rowset, by the value of some property, which is 'id' by default
     *
     * @param $value
     * @param string $key
     * @return Indi_Db_Table_Row
     */
    public function gb($value, $key = 'id') {
        foreach ($this->_rows as $r) if ($r->$key == $value) return $r;
    }

    /**
     * Merge current rowset with same-type rowset
     * 
     * @param Indi_Db_Table_Rowset $rowset
     * @return Indi_Db_Table_Rowset
     */
    public function merge(Indi_Db_Table_Rowset $rowset) {
        
        // Append
        foreach ($rowset as $row) {
            $this->_rows[] = $row;
            $this->_count++;
            $this->_found++;
        }
        
        // Return rowset itself
        return $this;
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
        return call_user_func_array(array($this, get_parent_class($call['class']) . '::' .  $call['function']), func_num_args() ? func_get_args() : $call['args']);
    }

    /**
     * Get sum of values, stored in all rows under $prop prop
     *
     * @param $prop
     * @return number
     */
    public function sum($prop) {
        return array_sum($this->column($prop));
    }

    /**
     * Adjust rowset, fetched to be used as foreign data
     *
     * @see Consider_Rowset for usage example
     * @param $key
     * @param $rowset
     */
    public function _adjustForeignRowset($key, &$rowset) {

    }

    /**
     * Process keys, and extract regexp if $key arg contain regexp to be used instead of just list of keys
     */
    protected function _selector($keys) {

        // If $keys argument is a string
        if (is_string($keys) || is_integer($keys) || is_null($keys)) {

            // Check if it contains a match expression
            if (preg_match('/^: (.*)/', $keys, $expr)) $expr = $expr[1];

            // If $keys argument is not an array, we convert it to it by exploding by comma
            else if (!is_array($keys)) $keys = explode(',', $keys);
        }

        // Return
        return array($keys, $expr);
    }

    /**
     * Call *_Row->assign($data) for each *_Row instance within current rowset
     *
     * @param array $data
     * @return $this
     */
    public function assign(array $data) {

        // Assign $data into each row within current rowset
        foreach ($this as $row) $row->assign($data);

        // Return rowset itself
        return $this;
    }

    /**
     * Call *_Row->basicUpdate($notices, $amerge) for each *_Row instance within current rowset
     *
     * @param bool $notices
     * @param bool $amerge
     * @return $this
     */
    public function basicUpdate($notices = false, $amerge = true) {

        // Call basicUpdate() on each row within current rowset
        foreach ($this as $row) $row->basicUpdate($notices, $amerge);

        // Return rowset itself
        return $this;
    }
}
