<?php
class AlteredField_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * This method was redefined to provide ability for some altered field
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some grid col props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'sectionId') $value = section($value)->id;
            else if ($columnName == 'fieldId') $value = field(section($this->sectionId)->entityId, $value)->id;
            else if ($columnName == 'elementId') $value = element($value)->id;
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * Build a string, that will be used in AlteredField_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('sectionId,fieldId') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $fieldR = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($fieldR->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Exclude `title` prop, if it was auto-created
            else if ($prop == 'title' && ($tf = $this->model()->titleField()) && $tf->storeRelationAbility != 'none' && !in($prop, $certain))
                unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($fieldR->storeRelationAbility != 'none') {

                //
                if ($fieldR->alias == 'elementId') {
                    $value = element($value)->alias;
                }
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current `alteredField` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build and return `alteredField` entry creation expression
        return "alteredField('" .
            $this->foreign('sectionId')->alias . "', '" .
            $this->foreign('fieldId')->alias . "', " .
            $this->_ctor($certain) . ");";
    }

    /**
     * Setter for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }
}