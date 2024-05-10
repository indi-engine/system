<?php
class Field_Rowset_Base extends Indi_Db_Table_Rowset {

    /**
     * Table name of table, that current rowset is related to
     *
     * @var string
     */
    protected $_table = 'field';

    /**
     * Contains an array with fields aliases as keys, and their indexes within $this->_rows array as values.
     * Is need for ability to direct fetch a needed row from rowset, by it's alias.
     *
     * @var array
     */
    protected $_aliases = [];

    /**
     * Contains an array with fields ids as keys, and their indexes within $this->_rows array as values.
     * Is need for ability to direct fetch a needed row from rowset, by it's id.
     *
     * @var array
     */
    protected $_ids = [];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config) {

        // Call parent constructor
        parent::__construct($config);

        // Foreach indexed prop - setup indexes
        foreach (['aliases' => 'alias', 'ids' => 'id'] as $index => $prop) {
            $this->{'_' . $index} = array_flip(
                $config[$index] ?? parent::column($prop)
            );
        }
    }

    /**
     * Empty the current rowset. Method is redeclared to keep $this->_aliases and
     * $this->_ids arrays matched with the contents if $this->_rows array
     *
     * @return Indi_Db_Table_Rowset Fluent interface
     */
    public function truncate() {

        // Reset $this->_aliases property
        $this->_aliases = [];

        // Reset $this->_ids property
        $this->_ids = [];

        // Call the truncate() method of a parent class and return it's result - rowset itself
        return parent::truncate();
    }

    /**
     * Reverse the current rowset. Method is redeclared to keep $this->_aliases and
     * $this->_ids arrays matched with the contents if $this->_rows array
     */
    public function reverse() {

        // Call the reverse() method of a parent class
        parent::reverse();

        // Reverse $this->_aliases array
        $this->_aliases = array_reverse($this->_aliases, true);

        // Reverse $this->_ids array
        $this->_ids = array_reverse($this->_ids, true);
    }

    /**
     * Get the values of a single column within rowset. If $column argument is 'alias', function will return keys of
     * $this->_aliases array, as they are aliases, so wen do not need to iterate through $this->_rows array.
     * Otherwise column() method of a parent class will be called
     *
     * @param $column
     * @param bool|string $imploded
     * @param bool $unique
     * @return array
     */
    public function column($column, $imploded = false, $unique = false) {

        // If $column argument is 'alias'
        if ($column == 'alias') {

            // Get the values array
            $valueA = array_keys($this->_aliases);

            // Return column data
            return $imploded ? implode(is_string($imploded) ? $imploded : ',', $valueA) : $valueA;

        // Else call ordinary function
        } else return parent::column($column, $imploded, $unique);
    }

    /**
     * Provide direct access to a field row, stored within $this->_rows array,
     * not by index, but by it's alias or id, instead.
     *
     * @param $alias
     * @return mixed
     */
    public function field($aliasOrId) {
        return $this->_rows[$this->_aliases[$aliasOrId] ?? $this->_ids[$aliasOrId] ?? null] ?? null;
    }

    /**
     * Exclude items from current rowset, that have keys, mentioned in $keys argument.
     * If $inverse argument is set to true, then function will return only rows,
     * which keys are mentioned in $keys argument. This method was redeclared to provide
     * an approriate adjstments for $this->_aliases and $this->_ids arrays, as we have here bit extented logic.
     *
     * @param string|array $keys Can be array or comma-separated list of ids
     * @param string $type Name of key, which value will be tested for existence in keys list
     * @param boolean $inverse
     * @return Indi_Db_Table_Rowset Fluent interface
     */
    public function exclude($keys, $type = 'id', $inverse = false) {

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

                // Unset row and it's alias
                unset($this->_aliases[$this->_rows[$index]->alias]);
                unset($this->_ids[$this->_rows[$index]->id]);
                unset($this->_rows[$index]);

                // Decrement count of items in current rowset
                $this->_count --;
            }
        }

        // Force zero-indexing
        $this->_rows = array_values($this->_rows);
        $this->_aliases = array_flip(array_values(array_flip($this->_aliases)));
        $this->_ids = array_flip(array_values(array_flip($this->_ids)));

        // Force $this->_pointer to be not out from the bounds of current rowset
        if ($this->_pointer > $this->_count) $this->_pointer = $this->_count;

        // Return rowset itself
        return $this;
    }

    /**
     * Builds an SQL string from an array of clauses, imploded with OR. String will be enclosed by round brackets, e.g.
     * '(`column1` LIKE "%keyword%" OR `column2` LIKE "%keyword%" OR `columnN` LIKE "%keyword%")'. Result string will
     * not contain WHERE clauses for columns, that are involved in building of set of another kind of WHERE clauses -
     * related to grid filters
     *
     * @param $keyword
     * @param $exclude
     * @param $nested
     * @return string
     */
    public function keywordWHERE($keyword, $exclude = [], array $nested = []) {

        // Convert quotes and perform an urldecode
        $keyword = strip_tags(urldecode($keyword));

        // Explode the keyword by spaces
        $keywordA = explode(' ', $keyword);

        // Remove unfriendly characters from each word within the keyword
        foreach ($keywordA as $i => $keywordI) {
            //$keywordI = preg_replace('/[^a-zA-Z0-9а-яА-Я]/u', '', $keywordI);
            $keywordI = trim($keywordI);
            if (!mb_strlen($keywordI, 'utf-8')) unset($keywordA[$i]);
        }

        // Update the keyword, so now we are sure that any word within the keyword doesn't contain unfriendly characters
        $keyword = implode(' ', $keywordA);

        // If keyword is empty, nothing to do here
        if (mb_strlen($keyword, 'utf-8') == 0) return;

        // Clauses stack
        $where = [];

        // Set up info about column types to be available within each grid field
        $this->foreign('columnTypeId');

        // Check each grid field's alias (same as db tabe column name) to ensure it's not in is not in exclusions,
        // and build WHERE clause for each db table column, that is presented in section's grid
        foreach ($this as $fieldR)
            if (!in_array($fieldR->alias, $exclude))
                if ($keywordFieldWHERE = $fieldR->keywordWHERE($keyword))
                    $where[] = $keywordFieldWHERE;

        // Append clauses, for deeper/nested keyword-filter, if $nested argument is given
        if ($this[0]) {
            $connector = m($this[0]->entityId)->table() . 'Id';
            foreach ($nested as $table => $columns) {
                if ($nestedWHERE = m($table)->fields($columns, 'rowset')->keywordWHERE($keyword)) {
                    $idA = db()->query('
                        SELECT `' . $connector . '` FROM `block` WHERE ' . $nestedWHERE
                    )->col();
                    $where[] = count($idA) ? '`id` IN (' . implode(',', $idA) . ')' : 'FALSE';
                }
            }
        }

        // Setup $nonFalseClauseA array
        $nonFALSE = []; for ($i = 0; $i < count($where); $i++) if ($where[$i] != 'FALSE') $nonFALSE[] = $where[$i];

        // If we have at least one non-FALSE clause - return them all, imploded by 'OR', else if
        // we have only FALSE clauses - return single 'FALSE', otherwise, if we have no clauses at all - return null
        return count($nonFALSE) ? '(' . implode(' OR ', $nonFALSE) . ')' : (count($where) ? 'FALSE' : null);
    }

    /**
     * Merge with another instance of Field_Rowset_Base
     *
     * @param Field_Rowset_Base $rowset
     * @return Field_Rowset_Base|Indi_Db_Table_Rowset
     */
    public function merge(Indi_Db_Table_Rowset $rowset) {

        // Call parent
        $this->callParent();

        // Merge aliases, and merge ids
        foreach ($rowset as $r) {
            $this->_aliases[$r->alias] = count($this->_aliases);
            $this->_ids[$r->id] = count($this->_ids);
        }

        // Return itself
        return $this;
    }

    /**
     * Append row to current rowset, using $original argument as the base data for
     * construction of a row, that will be appended to the end, or injected before certain row
     *
     * @param array|Field_Row $original
     * @param string $before
     * @return Field_Rowset_Base|Indi_Db_Table_Rowset|Field_Rowset
     */
    public function append($original, $before = null) {

        $id = $original instanceof Field_Row ? $original->id : ($original['id'] ?? null);
        $alias = $original instanceof Field_Row ? $original->alias : $original['alias'];

        // Prevent duplicates
        if (array_key_exists($alias, $this->_aliases)) return $this;

        // If $before arg is not given, or is, but ot found among the keys of $this->_aliases
        if ($before !== true && (!$before || !array_key_exists($before, $this->_aliases))) {

            // Push alias into aliases and id into ids
            $this->_aliases[$alias] = $this->_count;
            $this->_ids[$id] = $this->_count;

            // Call parent
            parent::append($original, $this->_count);

        // Else
        } else {

            // Get index
            if ($before === true) $before = key($this->_aliases);
            $idx = $this->_aliases[$before] ?? null;

            // Inject new value in $this->_aliases array
            $this->_aliases = array_flip($this->_aliases);
            array_splice($this->_aliases, $idx, 0, [$alias]);
            $this->_aliases = array_flip($this->_aliases);

            // Inject new value in $this->_ids array
            $this->_ids = array_flip($this->_ids);
            array_splice($this->_ids, $idx, 0, [$id]);
            $this->_ids = array_flip($this->_ids);

            // Call parent
            parent::append($original, $idx);
        }

        // Return
        return $this;
    }

    /**
     * Create pseudo-field
     *
     * @param $name
     * @param $table
     * @param bool $multiple
     * @return mixed
     */
    public function combo($name, $table, $multiple = false) {

        // Append
        $this->append([
            'alias' => $name,
            'columnTypeId' => $multiple ? 1 : 3,
            'storeRelationAbility' => $multiple ? 'many' : 'one',
            'elementId' => 23,
            'defaultValue' => $multiple ? '' : 0,
            'relation' => m($table)->id()
        ]);

        // Return field itself
        return $this->field($name);
    }

    /**
     * Force rowset to contain only rows, that have keys, mentioned in $keys argument
     * If $clone argument is set to true (this is it's default value), a clone of current rowset
     * will be filtered and returned. Otherwise - method will operate with current rowset instead of it's clone
     *
     * Further-foreign keys are supported. Syntax: 'title,price,categoryId,categoryId_info,category_discountPerCategoryId'
     * This is used in *_Rowset->toGridData(), for grid data ability to contain both plain/foreign data for those of grid's
     * fields, what are non-plain fields, e.g. - are foreign-key fields themselves.
     * Note that in the above example 'categoryId' is equal to 'categoryId_title' in most cases, because in most cases
     * 'title'-field is the title-field (see `entity`.`titleFieldId`).
     * If entity does not have `title`, `id`-column is used as title-column by default
     *
     * @param $keys
     * @param string $type
     * @param bool $clone
     * @return Indi_Db_Table_Rowset Fluent interface
     */
    public function select($keys, $type = 'id', $clone = true) {

        // Get initial result
        $rowset = $this->callParent();

        // Process keys
        list($keys, $expr) = $this->_selector($keys);

        // If regexp detected - return
        if ($expr || $type != 'alias') return $rowset;

        // Foreach key
        foreach ($keys as $key) {

            // If already selected - skip, else
            if (isset($this->_aliases[$key])) continue;

            // Check whether key name can contain <foreign field>_<further-foreign field> definition, and if no - skip
            if (!preg_match('~^([0-9a-zA-Z_]+)_([0-9a-zA-Z]+)$~', $key, $m)) continue;

            // If no field can by found by foreign field name definition, parsed from $key - skip
            if (!$fieldR = $this->field($m[1])) continue;

            // If found, but it's non-foreign field - skip
            if ($fieldR->storeRelationAbility == 'none') continue;

            // Get further-foreign field
            if (!$further = m($fieldR->relation)->fields($m[2])) continue;

            // Use cloned field
            $further = clone $further;

            // Prepend foreign field alias to further-foreign field alias
            $further->alias = $fieldR->alias . '_' . $further->alias;

            // Append further-foreign field to $rowset
            $rowset->append($further);

            // Append further-foreign field to $this
            $this->append($further);
        }

        // Return $rowset
        return $rowset;
    }
}