<?php
class SqlIndex_Row extends Indi_Db_Table_Row {

    /**
     * This method was redefined to provide ability for some sqlIndex
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName === 'entityId') $value = entity($value)->id;
            else if ($columnName == 'columns') {
                if ($value && !Indi::rexm('int11list', $value)) $value = m('field')
                    ->all("`entityId` = $this->entityId AND FIND_IN_SET(`alias`, '$value')")
                    ->fis();
            }

        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * @throws Exception
     */
    public function onBeforeInsert() {
        if (!$this->hasIdx()) $this->addIdx();
    }

    /**
     * If only visibility changed - run ALTER INDEX query, else recreate index
     *
     * @throws Exception
     */
    public function onBeforeUpdate() {

        // If nothing modified - return
        if (!$this->isModified()) return;

        // If only visibility is modified
        if ($this->isModified('visibility') && !$this->isModified('entityId,alias,columns,type')) {

            // Get table name
            $table = $this->foreign('entityId')->table;

            // Run ALTER INDEX query to change visibility
            db()->query("ALTER TABLE `$table` ALTER INDEX `$this->alias` $this->visibility");

        // Else drop old index and create new with updated definition
        } else {
            $this->dropIdx()->addIdx();
        }
    }

    /**
     *
     */
    public function onBeforeDelete() {
        $this->dropIdx();
    }

    /**
     * Add mysql index
     *
     * @return $this
     * @throws Exception
     */
    protected function addIdx() {

        // Get table name
        $table = $this->foreign('entityId')->table;

        // Get columns array
        $columnA = $this->foreign('columns')->col('alias');

        // Get columns list to be mentioned in sql query
        $columns = im($columnA, '`, `');

        // Setup index name
        $this->alias = im($columnA);

        // Setup visibility only if INVISIBLE as it's non-default
        $visible = rif($this->visibility === 'INVISIBLE', $this->visibility);

        // Run query
        db()->query("ALTER TABLE `$table` ADD $this->type `$this->alias` (`$columns`) $visible");

        // Fluent interface
        return $this;
    }

    /**
     * Drop mysql index
     *
     * @return $this
     * @throws Exception
     */
    protected function dropIdx() {

        // If we really have an index - drop it
        if ($this->hasIdx()) {

            // Get table name
            $table = $this->foreign('entityId', true, 'original')->table;

            // Run query
            db()->query("ALTER TABLE `$table` DROP INDEX `{$this->_original['alias']}`");
        }

        // Fluent interface
        return $this;
    }

    /**
     * Check whether mysql index really exists behind this entry
     *
     * @return false|int
     */
    public function hasIdx() {

        // Get SHOW CREATE TABLE
        $def = m($this->entityId)->def();

        // Prepare alias for use in regex
        $alias = preg_quote($this->_original['alias'] ?: $this->alias, '~');

        // Check for index mention within table definition
        return preg_match("~ KEY `$alias` \(~", $def);
    }

    /**
     * Build an expression for creating/getting the current `sqlIndex` entry in another project, running on Indi Engine
     *
     * Usage:
     * $index->export(): "sqlIndex('tablename', 'indexname', ['prop1' => 'value1', 'prop2' => 'value2', ...]);"
     * $index->export('indexprop1'): "field('tablename', 'indexname', ['prop1' => 'value1']);"
     * $index->export(false): "field('tablename', 'indexname')"
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Shortcuts
        $table = $this->foreign('entityId')->table;

        // Build `field` entry creation line
        $lineA[] = "sqlIndex('" . $table . "', '" . $this->alias . "'" . rif($certain !== false, ", " . $this->_ctor($certain)) . ");";

        // If $certain arg is given - export it only
        if ($certain || $certain === false) return $lineA[0];

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Build a string, that will be used in Field_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL
        unset($ctor['id']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('entityId,alias') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->field($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'columns') $value = $this->foreign('columns')->fis('alias');
            }
        }

        // If columns names are unchanged since the index was created - unset 'columns' key
        if ($this->_original['alias'] === $ctor['columns'])
            unset($ctor['columns']);

        // Stringify and return $ctor
        return _var_export($ctor);
    }
}