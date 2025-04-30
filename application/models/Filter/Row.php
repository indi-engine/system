<?php
class Filter_Row extends Indi_Db_Table_Row {

    /**
     * This method was redefined to provide ability for some
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'sectionId') $value = section($value)->id;
            else if ($columnName == 'fieldId') $value = $value ? field(section($this->sectionId)->entityId, ar($value)[0])->id : 0;
            else if ($columnName == 'move') return $this->_system['move'] = $value;
            else if ($columnName == 'further') $value = field(field(section($this->sectionId)->entityId, $this->fieldId)->relation, $value)->id;
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
     * Detect whether or not combo-filter show have the ability to deal with multiple values
     *
     * @return bool
     */
    public function multiSelect() {
        return $this->multiSelect;
    }

    /**
     * Build a string, that will be used in Filter_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `move` as they will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('sectionId,fieldId,further') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $fieldR = $this->field($prop);

            // Exclude prop, if it has value equal to default value
            if ($fieldR->defaultValue == $value && !in($prop, $certain)) {
                if (!isset($GLOBALS['export'])) unset($ctor[$prop]);
            }

            // Else if $prop is 'move' - get alias of the filter, that current filter is after,
            // among filters with same value of `sectionId` prop
            else if ($prop == 'move') $value = $this->position();

            // Exclude `title` prop, if it was auto-created
            else if ($prop == 'title' && ($tf = $this->model()->titleField()) && $tf->storeRelationAbility != 'none' && !in($prop, $certain))
                unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($fieldR->storeRelationAbility != 'none') {

                // Export roles
                if ($fieldR->rel()->table() == 'role') $value = $this->foreign($prop)->col('alias', true);
            }
        }

        // Return stringified $ctor
        return $this->_var_export($ctor);
    }

    /**
     * Get the the alias of the `section2action` entry,
     * that current `section2action` entry is positioned after
     * among all `section2action` entries having same `sectionId`
     * according to the values `move` prop
     *
     * @param null|string $after
     * @param string $withinFields
     * @return string|Indi_Db_Table_Row
     */
    public function position($after = null, $withinFields = 'sectionId') {

        // Build within-fields WHERE clause
        $wfw = [];
        foreach (ar($withinFields) as $withinField)
            $wfw []= "IFNULL(`$withinField`, 0) = '{$this->$withinField}'";

        // Get ordered aliases
        $among = db()->query('
            SELECT
              `fr`.`id`, 
              CONCAT(`fd`.`alias`, IFNULL(CONCAT("_", `fu`.`alias`), "")) AS `alias`
            FROM `field` `fd`, `filter` `fr`
              LEFT JOIN `field` `fu` ON (`fr`.`further` = `fu`.`id`)
            WHERE `fd`.`id` = `fr`.`fieldId`
              AND :p  
            ORDER BY `fr`.`move`
        ', $within = im($wfw, ' AND '))->pairs();

        // Get current position
        $currentIdx = array_flip(array_keys($among))[$this->id]; $among = array_values($among);

        // Do positioning
        return $this->_position($after, $among, $currentIdx, $within);
    }

    /**
     * Build an expression for creating the current `filter` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        //
        $sectionR = $this->foreign('sectionId');
        $fieldR = $this->foreign('fieldId');
        $further = $this->further ? $this->foreign('fieldId')->rel()->fields($this->further) : null;
        $ctor = $this->_ctor($certain);

        //
        $lineA []= "\n# '$fieldR->title'-filter in '$sectionR->title'-section";

        // Return creation expression
        $lineA []= $this->further
            ? "filter('$sectionR->alias', '$fieldR->alias', '$further->alias', $ctor);"
            : "filter('$sectionR->alias', '$fieldR->alias', $ctor);";

        //
        return join("\n", $lineA);
    }

    /**
     * Setter for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }

    /**
     * Get field behind this filter
     *
     * @return Field_Row|Indi_Db_Table_Rowset|void
     */
    public function fieldBehind() {
        return $this->foreign($this->further ? 'further' : 'fieldId');
    }

    /**
     * Clear some props if not applicable
     */
    public function onBeforeSave() {

        // If base field behind this filter - is not a foreign-key field
        if ($this->foreign('fieldId')->storeRelationAbility === 'none') {

            // Clear `further`-prop
            $this->zero('further', true);
        }

        // If real field behind this filter - is not a foreign-key field
        if ($this->fieldBehind()->storeRelationAbility === 'none') {

            // Make sure those features are disabled
            $this->zero('allowZeroResult,denyClear,ignoreTemplate,multiSelect,filter', true);
        }

        // Maintain values
        if ($this->fieldIsUnzeroed('allowZeroResultExceptPrefilteredWith')) {
            $this->allowZeroResult = '1';
        } else if (!$this->allowZeroResult && $this->field('allowZeroResultExceptPrefilteredWith')) {
            $this->zero('allowZeroResultExceptPrefilteredWith', true);
        }
    }

    /**
     *
     */
    public function onSave() {

        // Do positioning, if $this->_system['move'] is set
        if (array_key_exists('move', $this->_system)) {

            // Get entry, that current entry should be moved after
            $after = $this->_system['move']; unset($this->_system['move']);

            // Do position for current entry to be after entry, specified by $this->_system['move']
            $this->position($after);
        }
    }
}