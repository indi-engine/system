<?php
class Profile_Row extends Indi_Db_Table_Row {

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
            if ($columnName == 'entityId') $value = entity($value)->id;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Build an expression for creating the current `role` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `role` entry creation expression
        $lineA[] = "role('" . $this->alias . "', " . $this->_ctor($certain) . ");";

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
        foreach (ar('alias') as $arg) unset($ctor[$arg]);

        // Unset props that are currently not important from the import/export perspective
        unset($ctor['move']);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // If field is 'entityId' - spoof entityId with table name
            else if ($field->alias == 'entityId') $value = entity($value)->table;
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Append WHERE clause for filtering users within combo-data by their profileId
     *
     * @return bool|string|void
     */
    public function _comboDataConsiderWHERE(&$where, Field_Row $fieldR, Field_Row $cField, $cValue, $required, $cValueForeign = 0) {

        // If dependent-field is not a variable-entity field, or is, but variable entity is not determined - return
        if ($fieldR->relation || !$cValueForeign) return;

        // If variable entity is determined, and it's `admin` - append filtering by `profileId`
        if (m($cValueForeign)->table() == 'admin') $where []= '`' . $cField->alias . '` = "' . $cValue . '"';
    }
}