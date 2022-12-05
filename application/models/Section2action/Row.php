<?php
class Section2action_Row extends Indi_Db_Table_Row {

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
            else if ($columnName == 'actionId') $value = action($value)->id;
            else if ($columnName == 'move') return $this->_system['move'] = $value;
            else if ($columnName == 'roleIds') {
                if ($value && !Indi::rexm('int11list', $value)) $value = m('role')
                    ->all('FIND_IN_SET(`alias`, "' . $value .'")')
                    ->col('id', true);
            }
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Build a string, that will be used in Section2action_Row->export()
     *
     * @param string $certain
     * @return string
     * @throws Exception
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `move` as they will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('sectionId,actionId') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $fieldR = m('Section2action')->fields($prop);

            // Exclude prop, if it has value equal to default value (unless it's `roleIds`)
            if ($fieldR->defaultValue == $value && $prop != 'roleIds' && !in($prop, $certain)) unset($ctor[$prop]);

            // Else if $prop is 'move' - get alias of the field, that current field is after,
            // among fields with same value of `entityId` prop
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

        // Stringify
        return _var_export($ctor);
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
    public function position($after = null, $withinFields = 'sectionId') {

        // Build within-fields WHERE clause
        $wfw = [];
        foreach (ar($withinFields) as $withinField)
            $wfw []= '`' . $withinField . '` = "' . $this->$withinField . '"';

        // Get ordered action aliases
        $actionA_alias = db()->query('
            SELECT `sa`.`id`, `a`.`alias` 
            FROM `action` `a`, `section2action` `sa`
            WHERE 1 
                AND `a`.`id` = `sa`.`actionId`
                AND :p  
            ORDER BY `sa`.`move`
        ', $within = im($wfw, ' AND '))->pairs();

        // Get current position
        $currentIdx = array_flip(array_keys($actionA_alias))[$this->id]; $actionA_alias = array_values($actionA_alias);

        // Do positioning
        return $this->_position($after, $actionA_alias, $currentIdx, $within);
    }

    /**
     * Build an expression for creating the current `section2action` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     * @throws Exception
     */
    public function export($certain = '') {

        // Return creation expression
        return "section2action('" .
            $this->foreign('sectionId')->alias . "','" .
            $this->foreign('actionId')->alias . "', " .
            $this->_ctor($certain) . ");";
    }

    /**
     * Add roles into linked `section` entry's `roleIds` prop, if need
     */
    public function onInsert() {

        // Foreach added role
        foreach ($this->adelta('roleIds', 'ins') as $ins)

            // Mention that role in section's `roleIds` prop
            $this->foreign('sectionId')->push('roleIds', $ins);

        // Save section
        $this->foreign('sectionId')->save();
    }

    /**
     * Add/remove roles from linked `section` entry, if need
     */
    public function onUpdate() {

        // Foreach added role
        foreach ($this->adelta('roleIds', 'ins') as $ins)

            // Mention that role in section's `roleIds` prop
            $this->foreign('sectionId')->push('roleIds', $ins);

        // Foreach removed role
        foreach ($this->adelta('roleIds', 'del') as $del)

            // If section have no more actions accessible for removed role
            if (!db()->query('
                SELECT COUNT(*) FROM `section2action`
                WHERE 1
                  AND `sectionId` = "'. $this->sectionId . '"
                  AND FIND_IN_SET("'. $del.'", `roleIds`)
            ')->cell())

                // Remove that role from section entry's `roleIds` prop
                $this->foreign('sectionId')->drop('roleIds', $del);

        // Save section
        $this->foreign('sectionId')->save();
    }

    /**
     * Setter for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }


    /**
     *
     * @throws Exception
     */
    public function onSave() {

        // Trigger menu reload
        Indi::ws(['type' => 'menu', 'to' => true]);

        // Do positioning, if $this->_system['move'] is set
        if (array_key_exists('move', $this->_system)) {

            // Get field, that current field should be moved after
            $after = $this->_system['move']; unset($this->_system['move']);

            // Position field for it to be after field, specified by $this->_system['move']
            $this->position($after);
        }

        // If `filterOwner` prop was changed - replicate that on section2action-level
        if ($this->affected('filterOwner') && !$this->system('replication')) {

            // If `filterOwner` prop was changed to 'certain'
            if ($this->filterOwner == 'certain') {

                // Set up $filterOwner to be further applied to section-entry
                $filterOwner = 'certain';

            // Else if `filterOwner` prop was changed to 'no' or 'yes'
            } else {

                // Get list of distinct values of filterOwner-prop across all actions (including current one) in this section
                $distinct = db()->query('
                    SELECT GROUP_CONCAT(DISTINCT `filterOwner`) 
                    FROM `section2action` 
                    WHERE `sectionId` = "' . $this->sectionId . '"
                ')->cell();

                // If list consists of just one value, use that value else, else use 'certain'
                $filterOwner = count(ar($distinct)) == 1 ? $distinct : 'certain';
            }

            // Apply new value of filterOwner-prop for section-record
            $this
                ->foreign('sectionId')
                ->system('replication', true)
                ->set('filterOwner', $filterOwner)
                ->save();
        }
    }

    /**
     *
     */
    public function onDelete() {
        Indi::ws(['type' => 'menu', 'to' => true]);
    }

    /**
     * Adjust value of filterOwner-prop in case if filterOwnerRoleIds-prop is changed
     */
    public function onBeforeSave() {

        // If roles list in filterOwnerRoleIds-prop was changed from empty to non-empty
        if ($this->fieldIsUnzeroed('filterOwnerRoleIds')) {

            // Setup filterOwner-prop as 'certain'
            $this->filterOwner = 'certain';

        // Else if roles list was emptied
        } else if ($this->fieldIsZeroed('filterOwnerRoleIds')) {

            // Setup filterOwner-prop as 'certain'
            $this->filterOwner = 'no';
        }
    }
}