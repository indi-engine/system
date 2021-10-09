<?php
class Grid_Row extends Indi_Db_Table_Row {

    /**
     * This method was redefined to provide ability for some grid col
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
            else if ($columnName == 'further') $value = field(field(section($this->sectionId)->entityId, $this->fieldId)->relation, $value)->id;
            else if ($columnName == 'gridId') $value = grid($this->sectionId, $value)->id;
            else if ($columnName == 'move') return $this->_system['move'] = $value;
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * This method is redefined to setup default value for $within arg,
     * for current `grid` entry to be moved within the `section` it belongs to
     *
     * @param string $direction
     * @param string $within
     * @return bool
     */
    public function move($direction = 'up', $within = '') {

        // If $within arg is not given - move grid column within the section it belongs to
        $within = im(array(
            '`sectionId` = "' . $this->sectionId . '"',
            '`gridId` = "' . $this->gridId . '"',
            '`group` = "' . $this->group . '"'
        ), ' AND ');

        // Call parent
        return parent::move($direction, $within);
    }

    /**
     * Build a string, that will be used in Grid_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = null) {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `move` as they will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('sectionId,fieldId,alias,further') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = Indi::model('Grid')->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Exclude `title` prop, if it was auto-created
            else if ($prop == 'title' && ($tf = $this->model()->titleField()) && $tf->storeRelationAbility != 'none' && !in($prop, $certain))
                unset($ctor[$prop]);

            // Else if $prop is 'move' - get alias of the field, that current field is after,
            // among fields with same value of `entityId` prop
            else if ($prop == 'move') $value = $this->position();

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'gridId') {
                    $value = ($_ = $this->foreign('gridId')) && $_->fieldId ? $_->foreign('fieldId')->alias : $_->alias;
                }
            }
        }

        // Unset `width` if current `grid` entry has nested entries
        if ($this->nested('grid')->count()) unset($ctor['width']);

        // Return stringified $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current grid column in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Return creation expression
        if ($this->further) return "grid('" .
            $this->foreign('sectionId')->alias . "', '" .
            $this->foreign('fieldId')->alias . "', '" .
            $this->foreign('fieldId')->rel()->fields($this->further)->alias . "', " .
            $this->_ctor($certain) . ");";

        // Return creation expression
        else return "grid('" .
            $this->foreign('sectionId')->alias . "', '" .
            ($this->foreign('fieldId')->alias ?: $this->alias) . "', " .
            $this->_ctor($certain) . ");";
    }

    /**
     * If `group` prop was changed, for example, to 'locked' - apply such value to all nested `grid` entries
     */
    public function onUpdate() {

        // If `group` prop was not affected - return
        if (!$this->affected('group')) return;

        // Apply same value of `group` prop to all nested `grid` entries
        foreach ($this->nested('grid') as $gridR) {
            $gridR->group = $this->group;
            $gridR->save();
        }
    }

    /**
     * Prevent `grid` entry's `group` prop from being changed for cases when
     * current `grid` entry is not a top-level entry, and one of parent
     * entries has another value of `group` prop
     *
     * @return array|mixed
     */
    public function validate() {

        // If `group` prop is modified
        if ($this->isModified('group'))

            // Check parent entries
            while ($parent = ($parent ? $parent->parent() : $this->parent()))
                if ($parent->group != $this->group)
                    $this->_mismatch['group'] = sprintf('One of parent entries has non-same value');

        // Call parent
        return $this->callParent();
    }

    /**
     * Assign zero-value to `summaryText` prop, if need
     */
    public function onBeforeSave() {

        // If summaryType is not 'text' - set `summaryText` to be empty
        if ($this->summaryType != 'text') $this->zero('summaryText', true);

        // Make sure `profileIds` will be empty if `access` is 'all'
        if ($this->access == 'all') $this->zero('profileIds', true);
    }

    /**
     * Check whether current grid column should be accessible by current user
     *
     * @return bool
     */
    public function accessible() {
        if (!$this->access || $this->access == 'all') return true;
        if ($this->access == 'only' && in(Indi::admin()->profileId, $this->profileIds)) return true;
        if ($this->access == 'except' && !in(Indi::admin()->profileId, $this->profileIds)) return true;
    }

    /**
     * Get the the alias of the `field` entry,
     * that current `field` entry is positioned after
     * among all `field` entries having same `entityId`
     * according to the values `move` prop
     *
     * @param null|string $after
     * @param string $withinFields
     * @return string|Indi_Db_Table_Row
     */
    public function position($after = null, $withinFields = 'sectionId,gridId,group') {

        // Build within-fields WHERE clause
        $wfw = [];
        foreach (ar($withinFields) as $withinField)
            $wfw []= '`' . $withinField . '` = "' . $this->$withinField . '"';

        // Get ordered fields aliases
        $fieldA_alias = Indi::db()->query('
            SELECT `g`.`id`, `f`.`alias` 
            FROM `field` `f`, `grid` `g`
            WHERE 1 
                AND `f`.`id` = IF(`g`.`further` != "0", `g`.`further`, `g`.`fieldId`)
                AND :p  
            ORDER BY `g`.`move`
        ', $within = im($wfw, ' AND '))->fetchAll(PDO::FETCH_KEY_PAIR);

        // Get current position
        $currentIdx = array_flip(array_keys($fieldA_alias))[$this->id]; $fieldA_alias = array_values($fieldA_alias);

        // Do positioning
        return $this->_position($after, $fieldA_alias, $currentIdx, $within);
    }

    /**
     * Make sure parent gridcol's width will be adjusted if need
     */
    public function onSave() {

        // If `width` was not affected, or this gridcol is a top-level gridcol
        if ($this->affected('width') && $this->gridId) {

            // Affect parent gridcol's width
            $this->foreign('gridId')->width += $this->adelta('width');
            $this->foreign('gridId')->save();
        }

        // Do positioning, if $this->_system['move'] is set
        if (array_key_exists('move', $this->_system)) {

            // Get field, that current field should be moved after
            $after = $this->_system['move']; unset($this->_system['move']);

            // Position field for it to be after field, specified by $this->_system['move']
            $this->position($after);
        }
    }

    /**
     * Setter for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }
}