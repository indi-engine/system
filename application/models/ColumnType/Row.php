<?php
class ColumnType_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Get zero value for a current column type
     *
     * @return mixed
     */
    public function zeroValue() {
        return $this->model()->zeroValue($this->type);
    }

    /**
     * This method was redefined to provide ability for some field
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'elementId') {
                if ($value && !Indi::rexm('int11list', $value)) $value = m('element')
                    ->all('FIND_IN_SET(`alias`, "' . $value .'")')
                    ->col('id', true);
            }
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Build an expression for creating the current `columnType` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `element` entry creation expression
        $lineA[] = "coltype('" . $this->type . "', " . $this->_ctor($certain) . ");";

        // If $certain arg is given - export it only
        if ($certain) return $lineA[0];

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Build a string, that will be used in $this->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL and Indi Engine
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('type') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'elementId') $value = $this->foreign($prop)->col('alias', true);
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Check whether it's an ENUM or SET type
     *
     * @return bool
     */
    public function isEnumset() {
        return preg_match('/^ENUM|SET$/', $this->type);
    }
}