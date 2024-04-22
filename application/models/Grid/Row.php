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
            if ($columnName == 'sectionId' || $columnName == 'jumpSectionId') $value = $value ? section($value)->id : 0;
            else if ($columnName == 'jumpSectionActionId') $value = section2action(section($this->jumpSectionId)->id, $value)->id;
            else if ($columnName == 'fieldId') $value = field(section($this->sectionId)->entityId, $value)->id;
            else if ($columnName == 'further') $value = field(field(section($this->sectionId)->entityId, $this->fieldId)->relation, $value)->id;
            else if ($columnName == 'colorField') $value = $value ? field(explode('.', $value)[0], explode('.', $value)[1])->id : 0;
            else if ($columnName == 'colorEntry') $value = $value ? m(
                m('field')->row($this->colorField)->entityId
            )->row('`alias` = "' . $value . '"')->id : 0;
            else if ($columnName == 'gridId') $value = grid($this->sectionId, $value)->id;
            else if ($columnName == 'move') return $this->_system['move'] = $value;
            else if ($columnName == 'accessExcept') {
                if ($value && !Indi::rexm('int11list', $value)) $value = m('role')
                    ->all('FIND_IN_SET(`alias`, "' . $value .'")')
                    ->col('id', true);
            }
            else if ($columnName == 'formMoreGridIds') {
                if ($value && !Indi::rexm('int11list', $value)) {

                    // Get comma-separated list of fields ids
                    $fieldIds = m(section($this->sectionId)->entityId)->fields($value, 'rowset')->fis();

                    // Get comma-separated list of corresponding grid-cols ids
                    $value = $this->model()->all([
                        "`sectionId` = '$this->sectionId'",
                        "FIND_IN_SET(IFNULL(`further`,`fieldId`), '$fieldIds')"
                    ])->fis();
                }
            }
            else if ($columnName == 'formNotHideFieldIds') {
                if ($value && !Indi::rexm('int11list', $value)) {
                    $value = m(section($this->sectionId)->entityId)->fields($value, 'rowset')->fis();
                }
            }
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * Overridden to given ability to get syntetic value of alias-prop
     *
     * @param string $columnName
     * @return string|null
     * @throws Exception
     */
    public function __get($columnName) {

        // If $columnName is 'alias' - return syntetic value
        if ($columnName === 'alias')
            return $this->foreign('fieldId')->alias
                . rif($this->foreign('further')->alias, '_$1');

        // Else call parent
        return parent::__get($columnName);
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
        $within = im([
            '`sectionId` = "' . $this->sectionId . '"',
            'IFNULL(`gridId`, 0) = "' . $this->gridId . '"',
            '`group` = "' . $this->group . '"'
        ], ' AND ');

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

        // If certain fields should be exported - keep them only
                $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = m('Grid')->fields($prop);

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

                // If prop store parent `grid` entry ID - get it's alias
                if ($prop == 'gridId') {
                    $value = ($_ = $this->foreign('gridId')) && $_->fieldId ? $_->foreign('fieldId')->alias : $_->alias;
                }

                // Else if it's one of fields for jump destination definition
                else if ($prop == 'jumpSectionId')       $value = $this->foreign($prop)->alias;
                else if ($prop == 'jumpSectionActionId') $value = $this->$prop
                    ? $this->foreign($prop)->foreign('actionId')->alias
                    : '';

                // Else if it's one of fields for color definition
                else if ($prop == 'colorField') {
                    $value = $this->foreign($prop) ? join('.', [
                        $this->foreign($prop)->foreign('entityId')->table,
                        $this->foreign($prop)->alias
                    ]) : '';
                }
                else if ($prop == 'colorEntry') $value = $this->foreign($prop)->alias ?: $this->foreign($prop)->id;

                // Export roles
                else if ($field->rel()->table() == 'role') $value = $this->foreign($prop)->col('alias', true);

                // Export other things
                else if (in($prop, 'formMoreGridIds,formNotHideFieldIds')) {
                    $foreign = $value = $this->foreign($prop);
                    $value = $foreign ? $foreign->col('alias', true) : '';
                }
            }
        }

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
                    $this->_mismatch['group'] = I_MDL_GRID_PARENT_GROUP_DIFFERS;

        // If colorBreak-prop was modified to 'y',
        // and underlying data source field's element is non-numeric
        // setup mismatch as colorBreak=y is applicable for numeric-columns only
        if ($this->isModified('colorBreak') && $this->colorBreak == 'y')
            if ($element = $this->foreign($this->further ? 'further' : 'fieldId')->foreign('elementId')->alias)
                if (!in($element, 'number,price,decimal143')) $this->_mismatch['colorBreak'] = I_GRID_COLOR_BREAK_INCOMPAT;

        // Call parent
        return $this->callParent();
    }

    /**
     * Assign zero-value to `summaryText` prop, if need
     */
    public function onBeforeSave() {

        // If summaryType is not 'text' - set `summaryText` to be empty
        if ($this->summaryType != 'text') $this->zero('summaryText', true);

        // Make sure only one way of setting color is used at a time
             if ($this->modified('colorBreak') == 'y') $this->zero('colorDirect,colorField,colorEntry', true);
        else if ($this->modified('colorDirect'))       $this->zero('colorBreak,colorField,colorEntry', true);
        else if ($this->modified('colorField') || $this->modified('colorEntry'))
            $this->zero('colorBreak,colorDirect', true);

        // If variable-entity field was zeroed - do zero for colorEntry-field
        if ($this->fieldIsZeroed('colorField')) $this->zero('colorEntry', true);

        // If variable-entity field was zeroed - do zero for jumpSectionActionId-field
        if ($this->fieldIsZeroed('jumpSectionId')) $this->zero('jumpSectionActionId', true);
    }

    /**
     * Check whether current grid column should be accessible by current user
     *
     * @return bool
     */
    public function accessible() {

        // If accessRoles-prop is empty, it means that there is no such prop, and this,
        // in it's turn, means that current Indi Engine instance is not updated
        if (!$this->accessRoles) return true;

        // If current grid column should be accessible for all roles
        if ($this->accessRoles == 'all') return $this->accessExcept
            ? !in(admin()->roleId, $this->accessExcept)
            : true;

        // Else if should be accessible for no role (but maybe with some exceptions)
        else return $this->accessExcept
            ? in(admin()->roleId, $this->accessExcept)
            : false;
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
            $wfw []= "IFNULL(`gr`.`$withinField`, 0) = '{$this->$withinField}'";

        // Get ordered fields aliases
        $among = db()->query('
            SELECT 
              `gr`.`id`,
              CONCAT(`fd`.`alias`, IFNULL(CONCAT("_", `fu`.`alias`), "")) AS `alias`            
            FROM `field` `fd`, `grid` `gr`
              LEFT JOIN `field` `fu` ON (`gr`.`further` = `fu`.`id`)
            WHERE `fd`.`id` = `gr`.`fieldId`
              AND :p  
            ORDER BY `gr`.`move`
        ', $within = im($wfw, ' AND '))->pairs();

        // Get current position
        $currentIdx = array_flip(array_keys($among))[$this->id]; $among = array_values($among);

        // Do positioning
        return $this->_position($after, $among, $currentIdx, $within);
    }

    /**
     * Apply order change
     */
    public function onSave() {

        // Do positioning, if $this->_system['move'] is set
        if (array_key_exists('move', $this->_system)) {

            // Get field, that current field should be moved after
            $after = $this->_system['move']; unset($this->_system['move']);

            // Position field for it to be after field, specified by $this->_system['move']
            $this->position($after);
        }

        // If skipExcel-prop was changed
        if ($this->affected('skipExcel')) {

            // Apply that change to child entries, recursively
            foreach ($this->nested('grid') as $nested)
                if (!$nested->system('disabled'))
                    $nested->set('skipExcel', $this->skipExcel)->save();
        }
    }

    /**
     * Setter for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }

    /**
     * Replace '}{' with '&rcub;&lcub;' for composeVal and composeTip
     *
     * @return array
     */
    public function toArray() {

        // Call parent
        $array = parent::toArray();

        // Replace curly brackets with their html entities
        foreach (['composeVal', 'composeTip'] as $prop)
            $array[$prop] = preg_replace('~\}\{~', '&rcub;&lcub;', $array[$prop]);

        // Return
        return $array;
    }
}