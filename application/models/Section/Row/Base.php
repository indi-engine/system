<?php
class Section_Row_Base extends Indi_Db_Table_Row {

    /**
     * Function is redeclared to provide an ability for `href` pseudo property to be got
     *
     * @param string $property
     * @return string
     */
    public function __get($property) {
        return $property == 'href' ? PRE . '/' . $this->alias : parent::__get($property);
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
            if (in($columnName, 'parentSectionConnector,groupBy,defaultSortField,tileField')) $value = field($this->entityId, $value)->id;
            else if ($columnName == 'entityId') $value = entity($value)->id;
            else if ($columnName == 'sectionId') $value = section($value)->id;
            else if ($columnName == 'tileThumb') $value = thumb($this->entityId, $this->tileField, $value)->id;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Convert values of `defaultSortField` and `defaultSortDirection`
     * into a special kind of json-encoded array, suitable for usage
     * with ExtJS
     *
     * Also, before json-encoding, prepend additional item into that array,
     * responsible for grouping, if `groupBy` prop is set
     *
     * @return string
     */
    public function jsonSort() {

        // Blank array
        $json = array();

        // Mind grouping, as it also should be involved while building ORDER clause
        if ($this->groupBy && $this->foreign('groupBy'))
            $json[] = array(
                'property' => $this->foreign('groupBy')->alias,
                'direction' => 'ASC'
            );

        // Ming sorting
        if ($this->foreign('defaultSortField'))
            $json[] = array(
                'property' => $this->foreign('defaultSortField')->alias,
                'direction' => $this->defaultSortDirection,
            );

        // Return json-encoded sort params
        return json_encode($json);
    }

    /**
     * Build a string, that will be used in Section_Row_Base->export()
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
        foreach (ar('alias') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = Indi::model('Section')->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'sectionId') $value = section($value)->alias;
                else if ($prop == 'entityId') $value = entity($value)->table;
                else if (in($prop, 'parentSectionConnector,groupBy,defaultSortField,tileField')) $value = field($this->entityId, $value)->alias;
                else if ($prop == 'tileThumb') $value = m('Resize')->fetchRow($value)->alias;
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current `section` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `section` entry creation expression
        $lineA[] = "section('" . $this->alias . "', " . $this->_ctor($certain) . ");";

        // If $certain arg is given - export it only
        if ($certain) return $lineA[0];

        // Foreach `section2action` entry, nested within current `section` entry
        // - build `section2action` entry's creation expression
        foreach ($this->nested('section2action', array('order' => 'move')) as $section2actionR)
            $lineA[] = $section2actionR->export();

        // Foreach `grid` entry, nested within current `section` entry
        // - build `grid` entry's creation expression
        foreach ($this->nested('grid', array('order' => 'move')) as $gridR)
            $lineA[] = $gridR->export();

        // Foreach `alteredField` entry, nested within current `section` entry
        // - build `alteredField` entry's creation expression
        foreach ($this->nested('alteredField') as $alteredFieldR)
            $lineA[] = $alteredFieldR->export();

        // Foreach `filter` entry, nested within current `section` entry
        // - build `filter` entry's creation expression
        foreach ($this->nested('search', array('order' => 'move')) as $filterR)
            $lineA[] = $filterR->export();

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Prevent `extendsPhp` and `extendsJs` props from being empty
     */
    public function onBeforeSave() {

        // Clear value for `expandRoles` prop, if need
        if (in($this->expand, 'all,none')) $this->expandRoles = '';

        // Setup default value instead of empty value
        foreach (ar('extendsPhp,extendsJs') as $prop)
            if ($this->isModified($prop) && !$this->$prop)
                $this->$prop = $this->field($prop)->defaultValue;
    }

    /**
     *
     */
    public function onSave() {
        Indi::ws(['type' => 'menu', 'to' => true]);
    }

    /**
     *
     */
    public function onDelete() {
        Indi::ws(['type' => 'menu', 'to' => true]);
    }
}