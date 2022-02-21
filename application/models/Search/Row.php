<?php
class Search_Row extends Indi_Db_Table_Row {

    /**
     * This method was redefined to provide ability for some filter
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some filter props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'sectionId') $value = section($value)->id;
            else if ($columnName == 'fieldId') $value = field(section($this->sectionId)->entityId, $value)->id;
            else if ($columnName == 'further') $value = field(field(section($this->sectionId)->entityId, $this->fieldId)->relation, $value)->id;
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * Detect whether or not combo-filter show have the ability to deal with multiple values
     *
     * @return bool
     */
    public function any() {
        return $this->any || $this->foreign('fieldId')->storeRelationAbility == 'many';
    }

    /**
     * Build a string, that will be used in Search_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `move` as they will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id'], $ctor['move']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('sectionId,fieldId,further') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $fieldR = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($fieldR->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Exclude `title` prop, if it was auto-created
            else if ($prop == 'title' && ($tf = $this->model()->titleField()) && $tf->storeRelationAbility != 'none'  && !in($prop, $certain))
                unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($fieldR->storeRelationAbility != 'none') {
                // Empty for now
            }
        }

        // Return stringified $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current `filter` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Return creation expression
        if ($this->further) return "filter('" .
            $this->foreign('sectionId')->alias . "', '" .
            $this->foreign('fieldId')->alias . "', '" .
            $this->foreign('fieldId')->rel()->fields($this->further)->alias . "', " .
            $this->_ctor($certain) . ");";

        // Build and return `filter` entry creation expression
        else return "filter('" .
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

    /**
     * Make sure `profileIds` will be empty if `access` is 'all'
     */
    public function onBeforeSave() {
        if ($this->access == 'all') $this->zero('profileIds', true);
    }
}