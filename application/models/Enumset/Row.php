<?php
class Enumset_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Here we override parent's l10n() method, as enumset-model has it's special way of handling translations
     *
     * @param $data
     * @return array
     */
    public function l10n($data) {

        // Pick localized value of `title` prop, if detected that raw value contain localized values
        if (preg_match('/^{"[a-z_A-Z]{2,5}":/', $data['title']))
            if ($this->_language['title'] = json_decode($data['title'], true))
                $data['title'] = $this->_language['title'][Indi::ini('lang')->admin];

        // Get localized
        foreach (Indi_Queue_L10n_FieldToggleL10n::$l10n[$this->_table] ?: array()  as $field => $l10n)
            if (array_key_exists($field, $data))
                if ($this->_language[$field] = json_decode($l10n[$this->id], true))
                    $data[$field] = $this->_language[$field][Indi::ini('lang')->admin];

        // Return data
        return $data;
    }

    /**
     * Check the unicity of value of `alias` prop, within certain ENUM|SET field
     *
     * @return array
     */
    public function validate() {

        // Get the field row
        $fieldR = $this->foreign('fieldId');

        // Get the existing possible values
        $enumsetA = $fieldR->nested('enumset', array('order' => 'move'))->column('alias');

        // If modified version of value (or value that's going to be appended) is already exists within list of possible value - set mismatch
        if (array_key_exists('alias', $this->_modified))
            if (in_array($this->alias, ar(im($enumsetA))))
                $this->_mismatch['alias'] = sprintf(I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS, $this->alias);

        // Return
        return $this->callParent();
    }

    /**
     * Save possible value
     *
     * @return int
     */
    public function save() {

        // Convert `alias` to proper datatype
        if (array_key_exists('alias', $this->_modified))
            $this->alias = $this->fixTypes($this->_modified, true)->alias;

        // If `alias` property was not modified - do a standard save
        if (!array_key_exists('alias', $this->_modified)) return parent::save();

        // Run validation
        $this->mflush(true);

        // Get the field row
        $fieldR = $this->foreign('fieldId');

        // Get the existing possible values
        $enumsetA = $fieldR->nested('enumset', array('order' => 'move'))->column('alias');

        // Get the database table name
        $table = $fieldR->foreign('entityId')->table;

        // Get the current default value
        $defaultValue = $fieldR->entry
            ? $fieldR->defaultValue
            : Indi::db()->query('SHOW COLUMNS FROM `' . $table . '` LIKE "' . $fieldR->alias . '"')
                ->fetch(PDO::FETCH_OBJ)->Default;

        // If this is an existing enumset row
        if ($this->id) {

            // Convert $defaultValue to an array, for handling case if column type is SET
            $defaultValue = explode(',', $defaultValue);

            // If original version of value - is a default value
            if (in_array($this->_original['alias'], $defaultValue)) {

                // Setup sql default value as modified version of value
                $defaultValue[array_search($this->_original['alias'], $defaultValue)] = $this->alias;

                // If field's default value does not contain php expressions, setup $updateFieldDefaultValue flag
                // to true, because in this case we need to update field default value bit later, too
                if (!preg_match(Indi::rex('php'), $fieldR->defaultValue)) $updateFieldDefaultValue = true;
            }

            // Convert $defaultValue back from array to string
            $defaultValue = implode(',', $defaultValue);

            // Replace original value with modified value within list of possible values
            $enumsetA[array_search($this->_original['alias'], $enumsetA)] = $this->alias;

            // Temporarily append original value (to avoid 'Data truncated' mysql errors)
            $enumsetA[] = $this->_original['alias'];

        // Else if it is a new enumset row
        } else {

            // Append a new value to the list of allowed values
            $enumsetA[] = $this->alias;
        }

        // If it's not a cfgField - re-run ALTER query
        if (!$fieldR->entry) {

            // Build the ALTER query template
            $tpl = 'ALTER TABLE `%s` MODIFY COLUMN `%s` %s %s CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT "%s"';

            // Run that query
            Indi::db()->query(sprintf($tpl, $table, $fieldR->alias, $fieldR->foreign('columnTypeId')->type,
                '("' . im($enumsetA, '","') . '")', $defaultValue));
        }

        // Deal with existing values
        if ($this->id) {

            // If it's not a cfgField
            if (!$fieldR->entry) {

                // Replace mentions of original value with modified value
                Indi::db()->query('
                    UPDATE `' . $table . '`
                    SET `' . $fieldR->alias . '` = TRIM(BOTH "," FROM REPLACE(
                        CONCAT(",", `' . $fieldR->alias . '`, ","),
                        ",' . $this->_original['alias'] . ',",
                        ",' . $this->_modified['alias'] . ',"
                    ))
                ');

            // Else
            } else {

                // Replace mentions of original value with modified value
                Indi::db()->query('
                    UPDATE `param`
                    SET `cfgValue` = TRIM(BOTH "," FROM REPLACE(
                        CONCAT(",", `cfgValue`, ","),
                        ",' . $this->_original['alias'] . ',",
                        ",' . $this->_modified['alias'] . ',"
                    ))
                    WHERE `cfgField` = "' . $fieldR->id . '"
                ');
            }

            // Remove original value that was temporarily added to $enumsetA
            array_pop($enumsetA);

            // If it's not a cfgField - re-run ALTER query
            if (!$fieldR->entry)
                Indi::db()->query(sprintf($tpl, $table, $fieldR->alias, $fieldR->foreign('columnTypeId')->type,
                '("' . im($enumsetA, '","') . '")', $defaultValue));
        }

        // If $updateFieldDefaultValue flag is set to true
        if ($updateFieldDefaultValue)
            Indi::db()->query('
                UPDATE `field`
                SET `defaultValue` = "' . $defaultValue . '"
                WHERE `id` = "' . $fieldR->id . '"
                LIMIT 1
            ');

        // Standard save
        return parent::save();
    }

    /**
     * Delete
     *
     * @return int
     */
    public function delete() {

        // Get the field row
        $fieldR = $this->foreign('fieldId');

        // Get the existing possible values
        $enumsetA = $fieldR->nested('enumset', array('order' => 'move'))->column('alias');

        // Get the database table name
        $table = $fieldR->foreign('entityId')->table;

        // Get the current default value
        $defaultValue = $fieldR->entry
            ? $fieldR->defaultValue
            : Indi::db()->query('SHOW COLUMNS FROM `' . $table . '` LIKE "' . $fieldR->alias . '"')
                ->fetch(PDO::FETCH_OBJ)->Default;

        // If current row is the last enumset row, related to current field - throw an error message
        if (count($enumsetA) == 1) iexit(sprintf(I_ENUMSET_ERROR_VALUE_LAST, $this->alias));

        // Remove current item from the list of possible values
        unset($enumsetA[array_search($this->alias, $enumsetA)]);

        // Convert $defaultValue to an array, for handling case if column type is SET
        $defaultValue = explode(',', $defaultValue);

        // If original version of value - is a default value
        if (in_array($this->alias, $defaultValue)) {

            // Unset current item from the list of default values
            unset($defaultValue[array_search($this->alias, $defaultValue)]);

            // If field's default value does not contain php expressions, setup $updateFieldDefaultValue flag
            // to true, because in this case we need to update field default value bit later, too
            if (!preg_match(Indi::rex('php'), $fieldR->defaultValue)) $updateFieldDefaultValue = true;

            // If after unset there is no more sql default values left, we should set at least one, so we pick first
            // item from $enumsetA and set it as $defaultValue
            if (count($defaultValue) == 0) $defaultValue = array(current($enumsetA));
        }

        // Convert $defaultValue back from array to string
        $defaultValue = implode(',', $defaultValue);

        // If it's not a cfgField
        if (!$fieldR->entry) {

            // Build the ALTER query
            $sql[] = 'ALTER TABLE `' . $table . '` CHANGE COLUMN `' . $fieldR->alias . '` `' . $fieldR->alias . '`';
            $sql[] = $fieldR->foreign('columnTypeId')->type . '("' . implode('","', $enumsetA) . '")';
            $sql[] = 'CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
            $sql[] = 'DEFAULT "' . $defaultValue . '"';

            // Run that query
            Indi::db()->query(implode(' ', $sql));
        }

        // If $updateFieldDefaultValue flag is set to true
        if ($updateFieldDefaultValue)
            Indi::db()->query('
                UPDATE `field`
                SET `defaultValue` = "' . $defaultValue . '"
                WHERE `id` = "' . $fieldR->id . '"
                LIMIT 1
            ');

        // Standard save
        return parent::delete();
    }

    /**
     * This method is redefined here to prevent parent's method from being called,
     * because `enumset` entries have their own special usage behaviour
     */
    public function deleteForeignKeysUsages() {

    }

    /**
     * This method is redefined to setup default value for $within arg,
     * for current `enumset` entry to be moved within the `field` it belongs to
     *
     * @param string $direction
     * @param string $within
     * @return bool
     */
    public function move($direction = 'up', $within = '') {

        // If $within arg is not given - move `enumset` within the `field` it belongs to
        if (func_num_args() < 2) $within = '`fieldId` = "' . $this->fieldId . '"';

        // Call parent
        return parent::move($direction, $within);
    }

    /**
     * Build a string, that will be used in Enumset_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL and Indi Engine
        unset($ctor['id']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('fieldId,alias') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            // $field = Indi::model('Enumset')->fields($prop);

            // Else if $prop is 'move' - get alias of the enumset, that current enumset is after,
            // among enumsets with same value of `fieldId` prop
            if ($prop == 'move') $value = $this->position();
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current `enumset` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Shortcut
        $fieldR = $this->foreign('fieldId');

        // Return
        return $fieldR->entry
            ? "cfgEnumset('" .
                $fieldR->foreign('entityId')->table . "', '" .
                ($fieldR->foreign('entry')->alias ?: $fieldR->entry) . "', '" .
                $fieldR->alias . "', '" .
                $this->alias . "', " . $this->_ctor($certain) . ");"
            : "enumset('" .
                $fieldR->foreign('entityId')->table . "', '" .
                $fieldR->alias . "', '" .
                $this->alias . "', " . $this->_ctor($certain) . ");";
    }

    /**
     * Get color box
     *
     * @return string
     */
    public function box() {
        return preg_match('~<span.*?class=".*?i-color-box.*?".*?></span>~', $this->title, $m) ? $m[0] : '';
    }

    /**
     * Get the the alias of the `enumset` entry,
     * that current `enumset` entry is positioned after
     * among all `enumset` entries having same `fieldId`
     * according to the values `move` prop
     *
     * @param null|string $after
     * @param string $withinFields
     * @return string|Indi_Db_Table_Row
     */
    public function position($after = null, $withinFields = 'fieldId') {

        // Build within-fields WHERE clause
        $wfw = [];
        foreach (ar($withinFields) as $withinField)
            $wfw []= '`' . $withinField . '` = "' . $this->$withinField . '"';

        // Get ordered enumset aliases
        $enumsetA_alias = Indi::db()->query(
            'SELECT `alias` FROM `:p` :p ORDER BY `move`', $this->_table, rif($within = im($wfw, ' AND '), 'WHERE $1')
        )->fetchAll(PDO::FETCH_COLUMN);

        // Get current position
        $currentIdx = array_flip($enumsetA_alias)[$this->alias];

        // Do positioning
        return $this->_position($after, $enumsetA_alias, $currentIdx, $within);
    }

    /**
     * Do positioning, if $this->_system['move'] is set
     */
    public function onSave() {

        // If no _system['move'] defined - return
        if (!array_key_exists('move', $this->_system)) return;

        // Get field, that current enumset should be moved after
        $after = $this->_system['move']; unset($this->_system['move']);

        // Position field for it to be after field, specified by $this->_system['move']
        $this->position($after);
    }

    /**
     * This method was redefined to provide ability for some enumset
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'move') return $this->_system['move'] = $value;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }
}