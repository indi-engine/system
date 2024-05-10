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
            if ($columnName == 'sectionId' || $columnName == 'jumpSectionId') $value = section($value)->id;
            else if ($columnName == 'jumpSectionActionId') $value = section2action(section($this->jumpSectionId)->id, $value)->id;
            else if ($columnName == 'fieldId') $value = field(section($this->sectionId)->entityId, $value)->id ?? null;
            else if ($columnName == 'elementId') $value = element($value)->id;
            else if ($columnName == 'accessExcept') {
                if ($value && !Indi::rexm('int11list', $value)) $value = m('role')
                    ->all('FIND_IN_SET(`alias`, "' . $value .'")')
                    ->col('id', true);
            }
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

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

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

                // If it's elementId-prop
                if ($fieldR->alias == 'elementId') $value = element($value)->alias;

                // Else if it's one of fields for jump destination definition
                else if ($prop == 'jumpSectionId')       $value = $this->foreign($prop)->alias;
                else if ($prop == 'jumpSectionActionId') $value = $this->foreign($prop)->foreign('actionId')->alias;

                // Export roles
                else if ($fieldR->rel()->table() == 'role') $value = $this->foreign($prop)->col('alias', true);
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

    /**
     * Assign zero-value to `summaryText` prop, if need
     */
    public function onBeforeSave() {

        // If jumpSectionId-prop was zeroed - do zero for jumpSectionActionId and jumpCreate fields
        if ($this->fieldIsZeroed('jumpSectionId')) $this->zero('jumpSectionActionId,jumpCreate', true);

        // Pick parent entry's group, if parent entry is going to be changed
        if ($this->modified('gridId')) $this->group = $this->foreign('gridId')->group;
    }
}