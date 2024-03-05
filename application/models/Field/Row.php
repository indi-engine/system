<?php
class Field_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = []) {

        // Explicitly set table name
        $config['table'] = 'field';

        // Call parent
        parent::__construct($config);

        // Pull params
        if (!array_key_exists('params', $this->_temporary)) $this->_temporary['params'] = array_merge(
            Indi_Db::$_cfgValue['default']['element'][$this->elementId] ?: [],
            Indi_Db::$_cfgValue['certain']['field'][$this->id] ?: []
        );
    }

    /**
     * Check if current field depends on fields having localization turned On
     */
    public function hasLocalizedDependency() {

        // This feature is applicable only for non-foreign-key fields
        if ($this->storeRelationAbility != 'none') return false;

        // Foreach nested `consider` entry
        foreach ($this->nested('consider') as $considerR) {
            $prop = $considerR->foreign ? 'foreign' : 'consider';
            if ($considerR->foreign($prop)->hasLocalizedDependency()) return true;
            if ($considerR->foreign($prop)->l10n == 'y') return true;
        }

        // Return false
        return false;
    }

    /**
     * Get fraction(s), that this field belongs to
     */
    public function l10nFraction() {

        // Get field's entity
        $entityR = $this->foreign('entityId');

        // If field's entity is a system-entity
        if ($entityR->fraction == 'system') {

            // If it's a enumset-field - set fraction as 'adminSystemUi'
            if ($this->relation == 6) $fraction = 'adminSystemUi';

            // Else set two fractions
            else $fraction = 'adminSystemUi,adminCustomUi';

        // Else it's a custom entity
        } else if ($entityR->fraction == 'custom') {

            // If it's a enumset-field - set fraction as 'adminCustomUi'
            if ($this->relation == 6) $fraction = 'adminCustomUi';

            // Else set fraction to be 'adminCustomData'
            else $fraction = 'adminCustomData';
        }

        // Return fraction
        return $fraction;
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

        // Check if value is a color in #RRGGBB format and prepend it with hue number
        if (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) $value = hrgb($value);

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'elementId') $value = element($value)->id;
            else if ($columnName == 'columnTypeId') $value = coltype($value)->id;
            else if (in($columnName, 'entityId,relation')) $value = entity($value)->id;
            else if ($columnName == 'move') return $this->_system['move'] = $value;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * @throws Exception
     */
    public function onBeforeSave() {

        // Get the column type row
        $columnTypeR = $this->foreign('columnTypeId');

        // If column type is SET or ENUM
        if (in($columnTypeR->type, 'ENUM,SET')) $this->relation = m('enumset')->id();

        // Setup `defaultValue` for current field, but only if it's type is not
        // BLOB or TEXT, as these types do not support default value definition
        if (!in($columnTypeR->type, 'BLOB,TEXT')) $this->_defaultValue($columnTypeR);

        // If there was a relation, but now there is no - we perform a number of 'reset' adjustments, that aim to
        // void values of all properties, that are certainly not used now, as field does not store foreign keys anymore
        $this->_resetRelation($columnTypeR);
    }

    /**
     * Setup skip-flag for enumset-ref to make sure nested
     * enumset-records won't be deleted via doDeletionCASCADEandSETNULL
     * as we do that in another way.
     *
     * Skip-flag removed after parent::delete() call
     *
     * @return int|void
     */
    public function delete() {

        // Get `enumset`.`fieldId` field
        $skip []= m('enumset')->fields('fieldId');

        // Get `sqlIndex`.`columns` field
        $skip []= m('sqlIndex')->fields('columns');

        // Get current refs
        $refs = $this->model()->refs();

        // Setup skip-flag on each ref we need to skip
        foreach ($skip as $ref) $refs[$ref->onDelete][$ref->id]['skip'] = true;

        // Spoof refs
        $this->model()->refs($refs);

        // Call parent
        $return = parent::delete();

        // Unset skip-flag
        foreach ($skip as $ref) unset($refs[$ref->onDelete][$ref->id]['skip']);

        // Restore refs back
        $this->model()->refs($refs);

        // Return
        return $return;
    }

    /**
     * After delete
     *
     * @return int|void
     */
    public function onDelete() {

        // Delete files that are values of this field, if uploaded
        $this->deleteFiles();

        // Delete related enumset rows
        db()->query("DELETE FROM `enumset` WHERE `fieldId` = '{$this->_original['id']}'");

        // Drop foreign key constraint
        $this->dropIbfk();

        // Delete db table associated column
        $this->deleteColumn();

        // Delete indexes
        m('sqlIndex')->all("FIND_IN_SET('{$this->_original['id']}', `columns`)")->delete();

        // Delete current field from model's fields
        m($this->entityId)->fields()->exclude($this->_original['id']);
    }

    /**
     * Drop the database table column, that current field is representing
     */
    public function deleteColumn() {

        // If current field does not have a column - return
		if (!$this->columnTypeId || $this->entry) return;

        // Drop column
        db()->query('ALTER TABLE `' . $this->foreign('entityId')->table . '` DROP `' . $this->alias . '`');
	}

    /**
     * Delete all files, that were created by usage of current field
     */
    public function deleteFiles() {

        // If field has column in db table it means it's not an upload-field, so return
        if ($this->columnTypeId) return;

        // Shortcuts
        $entityId = $this->_affected['entityId'] ?? $this->_original['entityId'];
        $alias    = $this->_affected['alias']    ?? $this->_original['alias'];

        // If it's not an upload-field - return
		if ($this->foreign('elementId')->alias != 'upload') return;

        // Get the table
        $table = m($entityId)->table();

        // Get the directory name
        $dir = DOC . STD . '/' . ini()->upload->path . '/' . $table . '/';

        // If directory does not exist - return
        if (!is_dir($dir)) return;

        // Get the array of uploaded files and their copies (if some of them are images)
        $fileA = glob($dir . '[0-9]*_' . $alias . '[,.]*');

        // Delete files
        foreach ($fileA as $fileI) @unlink($fileI);
	}

    /**
     * Rename uploaded files for case if current field's `alias` property
     * was changed, so this change should affect uploaded files names
     *
     * @return mixed
     */
    protected function _renameUploadedFiles() {

        // If `alias` property was not changed - return
        if (!$this->affected('alias')) return;

        // Get the table
        $table = $this->foreign('entityId')->table;

        // Get the directory name
        $dir = DOC . STD . '/' . ini()->upload->path . '/' . $table . '/';

        // If directory does not exist - return
        if (!is_dir($dir)) return;

        // Get the array of uploaded files and their copies (if some of them are images)
        $fileA = glob($dir . '[1-9]*_' . $this->_affected['alias'] . '[,.]*');

        // Delete files
        foreach ($fileA as $fileI) {

            // Determine a new name
            $new = preg_replace('~(/[1-9][0-9]*_)' . $this->_affected['alias'] . '([,\.])~', '$1' . $this->alias . '$2', $fileI);

            // Rename
            rename($fileI, $new);
        }
    }

    /**
     * Do schema changes and files maintenance, if need
     *
     * @return int
     */
    protected function _schema() {

        // Declare the array of properties, who's modification leads to necessity of sql ALTER query to be executed
        $affect = ['entityId', 'alias', 'columnTypeId', 'defaultValue', 'storeRelationAbility'];

        // If field's control element was 'upload', but now it is not - set $deleteUploadedFiles to true
        $uploadElementId = element('upload')->id;
        if ($this->affected('elementId', true) == $uploadElementId)
            $this->deleteFiles();

        // Detect if any of affected properties are within $affect array, and if no
        if (!array_intersect($affect, array_keys($this->_affected))) {

            // Reload the model, because field was deleted
            m($this->entityId)->reload();

            // Check if saving of current field should affect current entity's
            // `titleFieldId` property and affect all involved titles
            $this->_titleFieldUpdate($this->_affected + $this->_original);

            // Return
            return;
        }

        // If `entityId` property was modified, and current field was an existing field
        if (!$this->wasNew() && $this->affected('entityId', true)) {

            // Get names of tables, related to both original and modified entityIds
            list($wasTable, $table) = m('Entity')->all(
                '`id` IN (' . $this->_affected['entityId'] . ',' . $this->entityId . ')',
                'FIND_IN_SET(`id`, "' . $this->_affected['entityId'] . ',' . $this->entityId . '")'
            )->column('table');

            // Get real table, as $table may contain VIEW-name rather that TABLE-name
            $table = m($table)->table(true);

            // Drop column from old table, if that column exists
            if ($this->fieldWasNonZero('columnTypeId'))
                db()->query('ALTER TABLE `' . $wasTable . '` DROP COLUMN `' . ($this->_affected['alias'] ?: $this->alias) .'`');

            // If field's control element was and still is 'upload' - delete files
            if ($this->elementId == $uploadElementId && !$this->affected('elementId'))
                $this->deleteFiles();

            // Reload the model, because field was deleted
            m($this->_affected['entityId'])->fields()->exclude($this->id);

            // Get original entity row
            $entityR = m('Entity')->row($this->_affected['entityId']);

            // If current field was used as title-field within original entity
            if ($entityR->titleFieldId == $this->id) {

                // Reset `titleFieldId` property of original entity and save that entity
                $entityR->titleFieldId = 0;
                $entityR->save();
            }

        // Else if `entityId` property was not modified
        } else {

            // Get name of the table, related to `entityId` property
            $table = m($this->entityId)->table(true);
        }

        // If current field is a cfgField
        if ($this->entry) {

            // Get the column type row
            $columnTypeR = $this->foreign('columnTypeId');

            // If column type is SET or ENUM
            if ($columnTypeR->isEnumset())
                list($enumsetA, $enumsetAppendA) = $this->_setupEnumset($columnTypeR, $table);

            // Setup `defaultValue` for current field, but only if it's type is not
            // BLOB or TEXT, as these types do not support default value definition
            // if (!preg_match('/^BLOB|TEXT$/', $columnTypeR->type)) $this->_defaultValue($columnTypeR);

            // If field column type was ENUM or SET, but now it is not -
            // we should delete rows, related to current field, from `enumset` table
            $this->_clearEnumset($columnTypeR);

            // If store relation ability changed to  'many'
            // And if field had it's own column within database table, and still has
            // And if all these changes are performed within the same entity
            // - Remove 0-values from column, as 0-values are not allowed
            //   for fields, that have storeRelationAbility = 'many'
            $this->_clear0($table);

            // If earlier we detected some values, that should be inserted to `enumset` table - insert them
            $this->_enumsetAppend($enumsetAppendA);

            // Return
            return;
        }

        // We should add a new column in database table in 3 cases:
        // 1. Field was moved from one entity to another, and field now has non-zero
        //    columnTypeId property, and does not matter whether is had it before or not
        // 2. Field is new, and columnTypeId property is non-zero
        // 3. Field had no column, e.g columnTypeId property has zero-value, but now it is non-zero
        if ($this->columnTypeId
            && ($this->affected('entityId', true) || $this->wasNew() || $this->fieldWasZero('columnTypeId'))) {

            // Start building an ADD COLUMN query
            $sql[] = 'ALTER TABLE `' . $table . '` ADD COLUMN `' . $this->alias . '`';

        // Else if we are certainly not dealing with field throw from one
        // entity to another, and field had non-zero columnTypeId originally
        } else if (!$this->affected('entityId') && $this->fieldWasNonZero('columnTypeId')) {

            // Get original value of alias, or current if it wasn't affected
            $alias = $this->_affected['alias'] ?: $this->alias;

            // If columnTypeId was non-zero, but now it is
            if ($this->fieldWasZeroed('columnTypeId')) {

                // Run a DROP COLUMN query
                db()->query("ALTER TABLE `$table` DROP COLUMN `$alias`");

                // Delete rows from `enumset` table, that are related to current field
                $this->clearEnumset();

                // Reload the model, because field was deleted
                m($this->entityId)->reload();

                // Check if saving of current field should affect current entity's
                // `titleFieldId` property and affect all involved titles
                $this->_titleFieldUpdate($this->_affected + $this->_original);

                // Return
                return;

            // Else if columnType was non-zero, and now it is either not changed, or changed but to also non-zero value
            } else

                // Start building a CHANGE COLUMN query
                $sql[] = "ALTER TABLE `$table` CHANGE COLUMN `$alias` `{$this->alias}`";
        }

        // If no query built - do a standard save
        if (!$sql) {

            // If `alias` property was modified, and current field's control element was and still is 'upload'
            // Rename uploaded files, for their names to be affected by change of current field's `alias` property
            if ($this->affected('alias', true) && $this->elementId == $uploadElementId && !$this->affected('elementId'))
                $this->_renameUploadedFiles();

            // Reload the model, because field info was changed
            m($this->entityId)->reload();

            // Check if saving of current field should affect current entity's
            // `titleFieldId` property and affect all involved titles
            $this->_titleFieldUpdate($this->_affected + $this->_original);

            // Return
            return;
        }

        // Get the column type row
        $columnTypeR = $this->foreign('columnTypeId');

        // Add the primary type definition to a query
        $sql[] = $columnTypeR->type;

        // If column type is SET or ENUM
        if ($columnTypeR->isEnumset()) {

            // Set $this->relation to 6, and get two enumset lists, that will be used further
            list($enumsetA, $enumsetAppendA) = $this->_setupEnumset($columnTypeR, $table);

            // Append the list of possible values to sql column type definition
            $sql[] = '("' . implode('","', $enumsetA) . '")';
        }

        // Add the collation definition, if column type supports it
        foreach (['CHAR', 'VARCHAR', 'TEXT'] as $collatedType)
            if (preg_match('~^' . $collatedType . '~', $columnTypeR->type))
                $sql[] = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci';

        // Add the 'NOT NULL' expression
        $sql[] = 'NOT NULL';

        // Add the DEFAULT definition for column, but only if it's type is not
        // BLOB or TEXT, as these types do not support default value definition
        if (!preg_match('/^BLOB|TEXT$/', $columnTypeR->type)) {

            // Setup $defaultValue along with setting $this->defaultValue (not always the same)
            $defaultValue = $this->_defaultValue($columnTypeR);

            // Append sql DEFAULT expression to query
            $sql[] = 'DEFAULT "' . $defaultValue . '"';

            // Check if field's column datatype is going to be changed, and if so - check whether there is a need
            // to adjust existing values, to ensure that they will be compatiple with new datatype, and won't cause
            // mysql error like 'Incorrect integer value ...'  during execution of a change-column-datatype sql query
            $this->_enforceExistingValuesCompatibility($columnTypeR->type, $defaultValue, $enumsetA);
        }

        // If field's column should be moved within table structure
        if (array_key_exists('move', $this->_system)) {

            // If system's move-prop is non-falsy
            if ($after = $this->_system['move']) {

                // Get [alias => columnTypeId] pairs
                $columnTypeIdA = m($this->entityId)->fields()->col('columnTypeId', false, 'alias');

                // If field, that we are going to move current field after - really exists in the entity
                if (array_key_exists($after, $columnTypeIdA)) {

                    // If field, that we are going to move current field after - does NOT really exist in the table
                    if (!$columnTypeIdA[$after]) {

                        // Field aliases array
                        $aliasA = array_keys($columnTypeIdA);

                        // Find closest field that DOES really exist in the table
                        do $after = $aliasA[array_search($after, $aliasA) - 1] ?? false; while ($after && !$columnTypeIdA[$after]);
                    }

                // Else make sure no AFTER-expr is added to sql-query
                } else $after = false;

            // Else assume it should be added at the top
            } else $after = 'id';

            // If AFTER-expr should be added to sql query
            if ($after) {

                // Append AFTER-expr to sql query
                $sql []= "AFTER $after";

                // Setup system colAFTER-flag indicating that AFTER-expr was already executed within an ALTER TABLE query
                $this->_system['colAFTER'] = true;
            }
        }

        // Implode the parts of sql query
        $sql = implode(' ', $sql);

        // Run the query
        db()->query($sql);

        // If we are creating move-column, e.g. this column will be used for ordering rows
        // Force it's values to be same as values of `id` column, for it to be possible to
        // move entries up/down once such a column was created
        if ($this->wasNew() && $columnTypeR->type == 'INT(11)' && $this->foreign('elementId')->alias == 'move')
            db()->query("UPDATE `$table` SET `{$this->alias}` = `id`");

        // If field column type was ENUM or SET, but now it is not -
        // we should delete rows, related to current field, from `enumset` table
        $this->_clearEnumset($columnTypeR);

        // If store relation ability changed to  'many'
        // And if field had it's own column within database table, and still has
        // And if all these changes are performed within the same entity
        // - Remove 0-values from column, as 0-values are not allowed
        //   for fields, that have storeRelationAbility = 'many'
        $this->_clear0($table);

        // If earlier we detected some values, that should be inserted to `enumset` table - insert them
        $this->_enumsetAppend($enumsetAppendA);

        // Get original data before save() call, as this data
        // will be used bit later for proper column indexes adjustments
        $original = $this->_affected + $this->_original;

        // Setup MySQL indexes
        $this->_indexes($columnTypeR, $table, $original);

        // Reload the model, because field info was changed
        m($this->entityId)->reload();

        // Check if saving of current field should affect current entity's
        // `titleFieldId` property and affect all involved titles
        $this->_titleFieldUpdate($original);
    }

    /**
     * Do schema changes, if need
     */
    public function onSave() {

        // Flag indicating whether at least one of props that ibfk constraint rely on - is affected
        $_ibfkRelyOnAffected = $this->affected('entityId,entry,alias,storeRelationAbility,relation,onDelete,columnTypeId');

        // If it's an existing column - drop old ibfk constraint, if need
        if (!$this->wasNew() && $_ibfkRelyOnAffected) $this->dropIbfk();

        // Do schema changes and files maintenance, if need
        if ($this->entityId) $this->_schema();

        // Handle positioning if _system['move'] is set
        $this->_move();

        // If this is a newly created field, or exsiting but some ibfk-props are affected - (re)add ibfk, if need
        if ($this->wasNew() || $_ibfkRelyOnAffected) $this->addIbfk();
    }

    /**
     * @param $columnTypeR
     * @param $table
     * @return array
     */
    private function _setupEnumset($columnTypeR, $table) {

        // Get the column type row, representing field's column before type change,
        // or field's current column, if no change was there
        $wasTypeR = coltype($this->_affected['columnTypeId'] ?: $this->columnTypeId);

        // Get the existing enumset values
        $enumsetA = $this->wasNew() ? [] : $this->nested('enumset')->column('alias');

        // Get the array of default values
        $defaultValueA = preg_match(Indi::rex('php'), $this->defaultValue)
            ? ['']
            : explode(',', $this->defaultValue);

        // If there are some values mentioned in defaultValue that are
        // NOT in existing values get the values, that should be added to the list
        $enumsetAppendA = array_diff($defaultValueA, $enumsetA);

        // If we are converting BOOLEAN to ENUM|SET, ensure both 0 and 1 will be
        // 1. mentioned in ALTER TABLE query
        // 2. insterted into `enumset` table
        if ($wasTypeR->type == 'BOOLEAN') $enumsetAppendA = [I_NO => 0, I_YES => 1];

        // Else if it was non-ENUM/SET type but now it is ENUM
        else if ($wasTypeR->id && !$wasTypeR->isEnumset() && $columnTypeR->type == 'ENUM') {

            // Get distinct list of existing values
            $valueA = db()->query($this->entry
                ? 'SELECT DISTINCT `cfgValue` FROM `param` WHERE `cfgField` = "' . $this->id . '"'
                : 'SELECT DISTINCT `' . ($this->_affected['alias'] ?: $this->alias) . '` FROM `' . $table . '`'
            )->col();

            // Set default value to be the first from the list of distinct values
            if (!$this->defaultValue) $this->defaultValue = $valueA[0];

            // Build key-value pairs
            $enumsetAppendA = array_combine($valueA, $valueA);
        }

        // Get the final list of possible values
        $enumsetA = array_merge($enumsetA, $enumsetAppendA);

        // Force `relation` property to be '6' - id of enumset `entity`
        $this->relation = 6;

        // Return
        return [$enumsetA, $enumsetAppendA];
    }

    /**
     * If store relation ability changed to  'many'
     * And if field had it's own column within database table, and still has
     * And if all these changes are performed within the same entity
     * - Remove 0-values from column, as 0-values are not allowed
     *   for fields, that have storeRelationAbility = 'many'
     *
     * @param $table
     */
    private function _clear0($table) {
        if ($this->fieldWasChangedTo('storeRelationAbility', 'many')
            && $this->fieldWasChangedToStillNonZero('columnTypeId')
            && $table && !$this->affected('entityId')) db()->query('
            UPDATE `' . $table . '` SET `' . $this->alias . '` = SUBSTR(REPLACE(CONCAT(",", `' . $this->alias . '`), ",0", ""), 2)
        ');
    }

    /**
     *  If field column type was ENUM or SET, but now it is not -
     * we should delete rows, related to current field, from `enumset` table
     *
     * @param $columnTypeR
     */
    private function _clearEnumset($columnTypeR) {
        if ($this->affected('columnTypeId')
            && ($wasType = $this->affected('columnTypeId', true))
            && ($wasType = coltype($wasType))
            && $wasType->isEnumset()
            && !$columnTypeR->isEnumset())
            $this->clearEnumset();
    }

    /**
     * If there was a relation, but now there is no - we perform a number of 'reset' adjustments, that aim to
     * void values of all properties, that are certainly not used now, as field does not store foreign keys no more
     *
     * @param $columnTypeR
     * @throws Exception
     */
    private function _resetRelation($columnTypeR) {

        // If storeRelationAbility-prop was not modified - return
        if (!$storeRelationAbility = $this->_modified['storeRelationAbility']) {

            // If it's not a foreign key field, or is but a cfgField - set onDelete to be '-'
            if ($this->storeRelationAbility == 'none' || $this->entry) $this->onDelete = '-';

            // Else if it's a non-cfgField and is a foreign-key but onDelete-value is '-'
            else if ($this->onDelete == '-')

                // Set valid value
                $this->onDelete = $columnTypeR->isEnumset()
                    ? 'RESTRICT'
                    : ($storeRelationAbility == 'many'
                        ? 'SET NULL'
                        : 'CASCADE');

            return;
        }

        // If it was modified to 'none'
        if ($storeRelationAbility == 'none') {

            // Get element alias
            $element = $this->foreign('elementId')->alias;

            // Get string-element id
            $string = element('string')->id;

            // If control element was radio or multicheck - set it as string
            if (in($element, 'radio,multicheck')) $this->elementId = $string;

            // Else if control element was combo, and column type is not BOOLEAN - set control element as string as well
            else if ($element == 'combo' && $columnTypeR->type != 'BOOLEAN') $this->elementId = $string;

            // If column type was ENUM or SET - set it as VARCHAR(255)
            if ($columnTypeR->isEnumset()) $this->columnTypeId = coltype('VARCHAR(255)')->id;

            // Setup `relation` as 0
            $this->relation = 0;

            // Setup `filter` as an empty string
            $this->filter = '';

            // Setup 'not-applicable' value for onDelete-prop
            $this->onDelete = '-';

        // Else if it was modified to some other value, which means it's single- or multi-value foreign key
        } else if ($this->onDelete === '-') {

            // Setup onDelete-rule accordingly
            $this->onDelete = $columnTypeR->isEnumset()
                ? 'RESTRICT'
                : ($storeRelationAbility == 'many'
                    ? 'SET NULL'
                    : 'CASCADE');
        }
    }

    /**
     * Detect and return proper default value to be used in ALTER TABLE query,
     * Also setup proper value for $this->defaultValue
     *
     * @param $columnTypeR
     * @return string
     */
    private function _defaultValue($columnTypeR) {

        // Trim the whitespaces and replace double quotes from `defaultValue` property,
        // for proper check of defaultValue compability to mysql column type
        $this->defaultValue = trim(str_replace('"', '&quot;', $this->defaultValue));

        // Check if default value contains php expressions
        $php = preg_match(Indi::rex('php'), $this->defaultValue);

        // Initial setup the default value for use in sql query
        $defaultValue = $this->defaultValue;

        // If column type is VARCHAR(255)
        if ($columnTypeR->type == 'VARCHAR(255)') {

            // If $php is true - set $defaultValue as empty string
            if ($php) $defaultValue = '';

            // Else if store relation ability changed to 'many' and default value contains zeros
            else if ($this->_modified['storeRelationAbility'] == 'many' && preg_match('/,0/', ',' . $defaultValue))

                // Strip zeros from both $defaultValue and $this->defaultValue
                $this->defaultValue = $defaultValue = ltrim(preg_replace('/,0/', '', ',' . $defaultValue), ',');

        // Else if column type is INT(11)
        } else if ($columnTypeR->type == 'INT(11)') {

            // If $php is true, or $defaultValue is not a positive integer
            if ($php || !preg_match(Indi::rex('int11'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a positive integer - we set field's `defaultValue` as '0'
                if (!$php) $this->defaultValue = '0';

                // Set $defaultValue as '0'
                $defaultValue = '0';
            }

        // Else if column type is DOUBLE(7,2)
        } else if ($columnTypeR->type == 'DOUBLE(7,2)') {

            // If $php is true, or default value does not match the column type signature
            if ($php || !preg_match(Indi::rex('double72'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a positive integer - we set field's `defaultValue` as '0'
                if (!$php) $this->defaultValue = '0';

                // Set $defaultValue as '0'
                $defaultValue = '0';
            }

        // Else if column type is DECIMAL(11,2)
        } else if ($columnTypeR->type == 'DECIMAL(11,2)') {

            // If $php is true, or default value does not match the column type signature
            if ($php || !preg_match(Indi::rex('decimal112'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a positive integer - we set field's `defaultValue` as '0.00'
                if (!$php) $this->defaultValue = '0.00';

                // Set $defaultValue as '0'
                $defaultValue = '0.00';
            }

        // Else if column type is DECIMAL(14,3)
        } else if ($columnTypeR->type == 'DECIMAL(14,3)') {

            // If $php is true, or default value does not match the column type signature
            if ($php || !preg_match(Indi::rex('decimal143'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a positive integer - we set field's `defaultValue` as '0.000'
                if (!$php) $this->defaultValue = '0.000';

                // Set $defaultValue as '0.000'
                $defaultValue = '0.000';
            }

        // Else if column type is DATE
        } else if ($columnTypeR->type == 'DATE') {

            // If $php is true or default value is not a date in format YYYY-MM-DD
            if ($php || !preg_match(Indi::rex('date'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a date - we set field's `defaultValue` as '0000-00-00'
                if (!$php) $this->defaultValue = '0000-00-00';

                // Set $defaultValue as '0000-00-00'
                $defaultValue = '0000-00-00';

                // Else if $default value is not '0000-00-00'
            } else if ($defaultValue != '0000-00-00') {

                // Extract year, month and day from date
                list($year, $month, $day) = explode('-', $defaultValue);

                // If $defaultValue is not a valid date - set it and field's `defaultValue `as '0000-00-00'
                if (!checkdate($month, $day, $year)) $this->defaultValue = $defaultValue = '0000-00-00';
            }

        // Else if column type is YEAR
        } else if ($columnTypeR->type == 'YEAR') {

            // If $php is true or default value does not match the YEAR column type format - set it as '0000'
            if ($php || !preg_match(Indi::rex('year'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a year - we set field's `defaultValue` as '0000'
                if (!$php) $this->defaultValue = '0000';

                // Set $defaultValue as '0000'
                $defaultValue = '0000';
            }

        // Else if column type is TIME
        } else if ($columnTypeR->type == 'TIME') {

            // If $php is true or default value is not a time in format HH:MM:SS - set it as '00:00:00'. Otherwise
            if ($php || !preg_match(Indi::rex('time'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a time - we set field's `defaultValue` as '00:00:00'
                if (!$php) $this->defaultValue = '00:00:00';

                // Set $defaultValue as '00:00:00'
                $defaultValue = '00:00:00';

            } else {

                // Extract hours, minutes and seconds from $defaultValue
                list($time['hour'], $time['minute'], $time['second']) = explode(':', $defaultValue);

                // If any of hours, minutes or seconds values exceeds
                // their possible values - set $defaultValue and field's `defaultValue` as '00:00:00'
                if ($time['hour'] > 23 || $time['minute'] > 59 || $time['second'] > 59)
                    $this->defaultValue = $defaultValue = '00:00:00';
            }

        // Else if column type is DATETIME
        } else if ($columnTypeR->type == 'DATETIME') {

            // If $php is true or $defaultValue is not a datetime in format YYYY-MM-DD HH:MM:SS - set it as '0000-00-00 00:00:00'
            if ($php || !preg_match(Indi::rex('datetime'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a datetime - we set field's `defaultValue` as '0000-00-00 00:00:00'
                if (!$php) $this->defaultValue = '0000-00-00 00:00:00';

                // Set $defaultValue as '0000-00-00 00:00:00'
                $defaultValue = '0000-00-00 00:00:00';

            // Else if $defaultValue is not '0000-00-00 00:00:00'
            } else if ($defaultValue != '0000-00-00 00:00:00') {

                // Extract date and time from $defaultValue
                list($date, $time) = explode(' ', $defaultValue);

                // Extract year, month and day from $defaultValue's date
                list($year, $month, $day) = explode('-', $date);

                // If $defaultValue's date is not a valid date - set $defaultValue as '0000-00-00 00:00:00'. Else
                if (!checkdate($month, $day, $year)) $this->defaultValue = $defaultValue = '0000-00-00 00:00:00'; else {

                    // Extract hour, minute and second from $defaultValue's time
                    list($hour, $minute, $second) = explode(':', $time);

                    // If any of hour, minute or second values exceeds their
                    // possible - set $defaultValue and field's `defaultValue` as '0000-00-00 00:00:00'
                    if ($hour > 23 || $minute > 59 || $second > 59)
                        $this->defaultValue = $defaultValue = '0000-00-00 00:00:00';
                }
            }

        // Else if column type is ENUM
        } else if ($columnTypeR->type == 'ENUM') {

            // If $php is true, set $defaultValue as empty string
            if ($php) $defaultValue = '';

        // Else if column type is SET
        } else if ($columnTypeR->type == 'SET') {

            // If $php is true, set $defaultValue as empty string
            if ($php) $defaultValue = '';

        // Else if column type is BOOLEAN
        } else if ($columnTypeR->type == 'BOOLEAN') {

            // If $php is true or $devaultValue is not 0 or 1 - set it as 0
            if ($php || !preg_match(Indi::rex('bool'), $defaultValue)) {

                // If $defaultValue does not contain php expressions and
                // is not a boolean - we set field's `defaultValue` as '0'
                if (!$php) $this->defaultValue = '0';

                // Set $defaultValue as '0'
                $defaultValue = '0';
            }

        // Else if column type is VARCHAR(10) we assume that it should be a color in format 'hue#rrggbb'
        } else if ($columnTypeR->type == 'VARCHAR(10)') {

            // If $php is true, or $defaultValue is not a color in format either '#rrggbb' or 'hue#rrggbb'
            if ($php || (!preg_match(Indi::rex('rgb'), $defaultValue) && !preg_match(Indi::rex('hrgb'), $defaultValue))) {

                // If $defaultValue does not contain php expressions and
                // is not a color in format either '#rrggbb' or 'hue#rrggbb'
                // - set field's `defaultValue` as empty string
                if (!$php) $this->defaultValue = '';

                // Set $defaultValue as empty string
                $defaultValue = '';

            // Else if $defaultValue is a color in format '#rrggbb'
            } else if (preg_match(Indi::rex('rgb'), $defaultValue))

                // We prepend it with hue number
                $defaultValue = hrgb($defaultValue);
        }

        // Return $defaultValue
        return $defaultValue;
    }

    /**
     * If we detected some values, that should be inserted to `enumset` table - insert them
     *
     * @param $enumsetAppendA
     */
    private function _enumsetAppend($enumsetAppendA) {

        // Foreach title=>alias pair to be appended
        foreach ($enumsetAppendA ?: [] as $title => $alias) {

            // If empty alias - skip
            if (!strlen($alias)) continue;

            // If $title is an integer - setup more user readable title by default
            if (Indi::rexm('int11', $title)) $title = sprintf(I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE, $alias);

            // Get `move` as auto increment
            $move = db()->query('SHOW TABLE STATUS LIKE "enumset"')->fetch(PDO::FETCH_OBJ)->Auto_increment;

            // Do insert todo: refactor
            db()->query('
                INSERT INTO `enumset` SET
                `fieldId` = "' . $this->id . '",
                `title` = "' . $title . '",
                `alias` = "' . $alias . '",
                `move` = "' . $move . '"
            ');
        }
    }

    /**
     * Create/delete indexes
     *
     * @param $columnTypeR
     * @param $table
     * @param $original
     */
    private function _indexes($columnTypeR, $table, $original) {

        // Check if where was no relation and index, but now relation is exist, - we add an INDEX index
        if (preg_match('/INT|SET|ENUM|VARCHAR/', $columnTypeR->type))
            if (!db()->query("SHOW INDEXES FROM `$table` WHERE `Column_name` = '$this->alias'")->obj()->Key_name)
                if ($original['storeRelationAbility'] == 'none' && $this->storeRelationAbility != 'none')
                    $table === 'sqlIndex'
                        ? db()->query("ALTER TABLE  `$table` ADD INDEX (`$this->alias`)")
                        : sqlIndex($table, $this->alias, ['type' => 'KEY']);

        // Check if where was a relation, and these was an index, but now there is no relation, - we remove an INDEX index
        if ($original['storeRelationAbility'] != 'none' && $this->storeRelationAbility == 'none')
            if ($index = db()->query("SHOW INDEXES FROM `$table` WHERE `Column_name` = '$this->alias'")->obj()->Key_name)
                sqlIndex($table, $index)->delete();

        // Check if is was not a TEXT column, and it had no FULLTEXT index, but now it is a TEXT column, - we add a FULLTEXT index
        if (m('ColumnType')->row($original['columnTypeId'])->type != 'TEXT')
            if (!db()->query("SHOW INDEXES FROM `$table` WHERE `Column_name` = '$this->alias' AND `Index_type` = 'FULLTEXT'")->fetch())
                if ($columnTypeR->type == 'TEXT')
                    sqlIndex($table, $this->alias, ['type' => 'FULLTEXT']);

        // Check if is was a TEXT column, and it had a FULLTEXT index, but now it is not a TEXT column, - we remove a FULLTEXT index
        if (m('ColumnType')->row($original['columnTypeId'])->type == 'TEXT')
            if ($index = db()->query("SHOW INDEXES FROM `$table` WHERE `Column_name` = '$this->alias' AND `Index_type` = 'FULLTEXT'")->obj()->Key_name)
                if ($columnTypeR->type != 'TEXT')
                    sqlIndex($table, $index)->delete();
    }

    /**
     * Check if saving of current field should affect current entity's `titleFieldId` property
     * and affect all involved titles
     *
     * @param array $original
     */
    protected function _titleFieldUpdate(array $original) {

        // If current field's alias is 'title' and current entity's titleFieldId is not set
        // and this was a new field, or existing field, but it's alias was changed to 'title'
        if ($this->alias == 'title' && !$this->foreign('entityId')->titleFieldId
            && (!$original['id'] || $original['alias'] != 'title') && $this->columnTypeId) {

            // Set entity's titleFieldId property to $this->id and save the entity
            $this->foreign('entityId')->titleFieldId = $this->id;
            $this->foreign('entityId')->save();

        // Else if field was already existing, and it's used as title field for current entity
        } else if ($original['id'] && $this->foreign('entityId')->titleFieldId == $this->id) {

            // We need to know whether or not all involved titles should be updated.
            // If field's `storeRelationAbility` property was changed, or `relation`
            // property was changed - we certainly should update all involved titles
            if ($original['storeRelationAbility'] != $this->storeRelationAbility
                || $original['relation'] != $this->relation || $original['columnTypeId'] != $this->columnTypeId) {

                // We force `titleFieldId` property to be in the list of modified fields,
                // despite on actually it's value is not modified. We do that to ensure
                // that all operations for all involved titles update will be executed
                $this->foreign('entityId')->modified('titleFieldId', $this->id);
                $this->foreign('entityId')->save();
            }
        }
    }

    /**
     * Check if field's column datatype is going to be changed, and if so - check whether there is a need
     * to adjust existing values, to ensure that they will be compatiple with new datatype, and won't cause
     * mysql error like 'Incorrect integer value ...'  during execution of a change-column-datatype sql query
     *
     * @param $newType
     * @param $defaultValue
     * @return mixed
     */
    protected function _enforceExistingValuesCompatibility($newType, $defaultValue, $enumsetA) {

        // If field's entityId was not changed, and field had and still has it's
        // own database table column, but that column type is going to be changed
        if (!($this->fieldWasChangedToStillNonZero('columnTypeId') && !$this->affected('entityId'))) return;

        // Get the column type row, representing field's column before type change (original column)
        $curTypeR = m('ColumnType')->row($this->_affected['columnTypeId']);

        // Get the table name
        $tbl = $this->foreign('entityId')->table;

        // Get the field's column name
        $col = $this->_affected['alias'] ?: $this->alias;

        // Define array of rex-names, related to their mysql data types
        $rex = [
            'VARCHAR(255)' => 'varchar255', 'INT(11)' => 'int11', 'DECIMAL(11,2)' => 'decimal112',
            'DECIMAL(14,3)' => 'decimal143', 'DATE' => 'date', 'YEAR' => 'year', 'TIME' => 'time',
            'DATETIME' => 'datetime', 'ENUM' => 'enum', 'SET' => 'set', 'BOOLEAN' => 'bool', 'VARCHAR(10)' => 'hrgb'
        ];

        // Prepare regular expression for usage in WHERE clause in
        // UPDATE query, for detecting and fixing incompatible values
        if ($rex[$newType] == 'enum') {
            $regexp = ',(' . im($enumsetA, '|') . '),';
        } else {
            $regexp = preg_replace('/\$$/', ')$', preg_replace('/^\^/', '^(', trim(Indi::rex($rex[$newType]), '/')));
        }

        // Setup double-quote variable, and WHERE usage flag
        $q = '"'; $w = true; $incompatibleValuesReplacement = false; $wcol = '`' . $col . '`';

        if ($newType == 'VARCHAR(255)') {
            if (preg_match('/TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col . '`, 1, 255)'; $q = ''; $w = false;
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            }
        } else if ($newType == 'INT(11)') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/DOUBLE\(7,2\)|YEAR|BOOLEAN|DECIMAL\(11,2\)|DECIMAL\(14,3\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = false;
            } else if (preg_match('/^DATE|TIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $this->storeRelationAbility == 'none' ? false : $defaultValue;
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0'; $q = '';
            }
        } else if ($newType == 'DECIMAL(11,2)') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/YEAR|BOOLEAN/', $curTypeR->type)) {
                $incompatibleValuesReplacement = false;
            } else if (preg_match('/^DATE|TIME|DATETIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0'; $q = '';
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            }
        } else if ($newType == 'DECIMAL(14,3)') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/YEAR|BOOLEAN/', $curTypeR->type)) {
                $incompatibleValuesReplacement = false;
            } else if (preg_match('/^DATE|TIME|DATETIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0'; $q = '';
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = false;
            }
        } else if ($newType == 'DATE') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0000-00-00';
            } else if (preg_match('/ENUM/', $curTypeR->type)) {
                $maxLen = 10;
                foreach(m($tbl)->fields($this->id)->nested('enumset') as $enumsetR)
                    if (strlen($enumsetR->alias) > $maxLen) $maxLen = strlen($enumsetR->alias);
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR('. $maxLen . ') NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00';
            } else if (preg_match('/SET/', $curTypeR->type)) {
                $shortestValue = '';
                foreach(m($tbl)->fields($this->id)->nested('enumset') as $enumsetR)
                    if (strlen($enumsetR->alias) < strlen($shortestValue)) $shortestValue = $enumsetR->alias;
                db()->query('UPDATE TABLE `' . $tbl . '` SET `' . $col . '` = "' . $shortestValue . '"');
                $minLen = ($svl = strlen($shortestValue)) > 10 ? $svl : 10;
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(' . $minLen . ') NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00';
            } else if (preg_match('/YEAR/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = 'CONCAT(`' . $col .'`, IF(`' . $col . '` = "0000", "0000", "0101"))';
                $q = ''; $w = false;
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            } else if (preg_match('/^DATETIME|TIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = 'IF(DAYOFYEAR(CAST(`' . $col .'` AS UNSIGNED)),
                DATE_FORMAT(CAST(`' . $col .'` AS UNSIGNED), "%Y-%m-%d"), "0000-00-00")'; $q = ''; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(14) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            }
        } else if ($newType == 'YEAR') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0000';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col .'`, 1, 4)';
                $q = ''; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col .'`, 1, 4)';
                $q = ''; $w = false;
            } else if (preg_match('/^TIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(4) NOT NULL');
                $incompatibleValuesReplacement = '0000'; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` INT NOT NULL');
                $incompatibleValuesReplacement = '0'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` INT NOT NULL');
                $incompatibleValuesReplacement = '0'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` INT NOT NULL');
                $incompatibleValuesReplacement = '0'; $w = false;
            }
        } else if ($newType == 'TIME') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '00:00:00';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col .'`, 12)';
                $q = ''; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(12) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(15) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            }
        } else if ($newType == 'DATETIME') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0000-00-00 00:00:00';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {

            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'CONCAT(`' . $col . '`, "-", IF(`' . $col . '` = "0000", "00-00", "01-01"), " 00:00:00")';
                $w = false; $q = '';
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            }
        } else if ($newType == 'BOOLEAN') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0000-00-00 00:00:00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(4) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0000", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0000-00-00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "00:00:00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '1';
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0.00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(12) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0.00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(15) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0.000", "0", "1")'; $w = false; $q = '';
            }
        } else if ($newType == 'ENUM' || $newType == 'SET') {
            if (preg_match('/TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/VARCHAR\(255\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = true; $wcol = 'CONCAT(",", `' . $col . '`, ",")';
            } else if (preg_match('/SET/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $regexp = '^' . im($enumsetA, '|') . '$';
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $regexp = '^' . im($enumsetA, '|') . '$';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            }
        } else if ($newType == 'VARCHAR(10)') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(' . $this->maxLength() . ') NOT NULL');
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(11,2\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(12) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(14,3\)/', $curTypeR->type)) {
                db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(15) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            }
        }

        // Adjust existing values, for them to be compatible with type, that field's column will be
        // converted to. We should do it to aviod mysql error like 'Incorrect integer value ...' etc
        if ($incompatibleValuesReplacement !== false)
            db()->query('
                UPDATE `' . $tbl . '`
                SET `' . $col . '` = ' . $q . $incompatibleValuesReplacement . $q .
                    ($w ? ' WHERE ' . $wcol . ' NOT REGEXP "' . $regexp . '"' : '')
            );
    }

    /**
     * Deletes rows from `enumset` table, that are nested to current field
     */
    public function clearEnumset() {
        if ($this->id) db()->query('DELETE FROM `enumset` WHERE `fieldId` = "' . $this->id . '"');
    }

    /**
     * Implementation of toArray() function, special for Field_Row class.
     * Here we exclude values, stored in $this->_compiled, from the process of converting
     * current Field_Row object to array, to prevent possibility of any other values being
     * overwritten ones stored under same keys in $this->_compiled property.
     * After conversion is done, the initial state of $this->_compiled property is restored.
     *
     * @param string $type
     * @param bool $deep
     * @return array
     */
    public function toArray($type = 'current', $deep = true) {

        // If toArray conversion mode is 'current'
        if ($type == 'current') {

            // Backup values, stored in $this->_compiled property
            $compiled = $this->_compiled;

            // Reset $this->_compiled property
            $this->_compiled = [];
        }

        // Do regular conversion
        $return = parent::toArray($type, $deep);

        // If toArray conversion mode is 'current' - restore $this->_compiled property
        if ($type == 'current') $this->_compiled = $compiled;

        // Escape '}{' sequence here as we are using it to split json-responses in *_Admin
        if ($return['params']['placeholder'] ?? 0)
            $return['params']['placeholder']
                = preg_replace('~\}\{~', '&rcub;&lcub;', $return['params']['placeholder']);

        // Return conversion result
        return $return;
    }


    /**
     * Build ORDER clause for the field
     *
     * @param string $direction
     * @param null $where
     * @return string
     */
    public function order($direction = 'ASC', $where = null) {

        // Unallow bad values of $direction arg
        if (!in($direction, 'ASC,DESC')) $direction = 'ASC';

        // If this is a simple column
        if ($this->storeRelationAbility == 'none') {

            // If sorting column type is BOOLEAN (use for Checkbox control element only)
            if ($this->foreign('columnTypeId')->type == 'BOOLEAN') {

                // Provide an approriate SQL expression, that will handle different titles for 1 and 0 possible column
                // values, depending on current language
                $orderA[] = (ini('view')->lang == 'en'
                    ? 'IF(`' . $this->alias . '`, "' . I_YES .'", "' . I_NO . '") '
                    : 'IF(`' . $this->alias . '`, "' . I_NO .'", "' . I_YES . '") ') . $direction;

            // Build l10n-compatible version of $order for usage in sql queries
            } else if ($this->l10n == 'y') {
                $order = 'SUBSTRING_INDEX(`' . $this->alias . '`, \'"' . ini('lang')->admin . '":"\', -1) ' . $direction;

            // Else build the simplest ORDER clause
            } else $order = '`' . $this->alias . '` ' . $direction;

        // Else if column is storing single foreign keys
        } else if ($this->storeRelationAbility == 'one') {

            // If column is of type ENUM
            if ($this->foreign('columnTypeId')->type == 'ENUM') {

                // Get a list of comma-imploded aliases, ordered by their titles
                $set = db()->query($sql = '
                    SELECT GROUP_CONCAT(`alias` ORDER BY `move`)
                    FROM `enumset`
                    WHERE `fieldId` = "' . $this->id . '"
                ')->cell();

                // Build the order clause, using FIND_IN_SET function
                $order = 'FIND_IN_SET(`' . $this->alias . '`, "' . $set . '") ' . $direction;

            // If column is of type (BIG|SMALL|MEDIUM|)INT
            } else if (preg_match('/INT/', $this->foreign('columnTypeId')->type)) {

                // Do nothing for variable-entity fields. todo: make for variable entity
                if ($this->relation) {

                    // If we're going to sort entries by `monthId` column, or other-named
                    // column referencing to `month` entries - apply custom behaviour
                    if ($this->rel()->table() == 'month') {

                        // Build the order clause, using FIND_IN_SET function and comma-separated
                        // list of months' ids, as monthId('all') fn return them in right chronology
                        $order = 'FIND_IN_SET(`' . $this->alias . '`, "' . im(monthId('all')) . '")' . $direction;

                    // Else
                    } else {

                        // Table name shortcut
                        $table = m($this->entityId)->table();

                        // Start building WHERE clause
                        $where = is_array($where) ? implode(' AND ', $where) : $where;

                        // If this field is a foreign key having mysql constraint defined at mysql-level
                        if ($this->relation && m($this->entityId)->ibfk($this->alias)) {

                            // Append 'AND NOT ISNULL(...)' clause
                            $where = rif($where, '($1) AND ') . 'NOT ISNULL(`' . $this->alias . '`)';
                        }

                        // Prepend with WHERE keyword, if not empty
                        $where = rif($where, 'WHERE $1');

                        // Get the possible foreign keys
                        $setA = db()->query("SELECT DISTINCT `{$this->alias}` AS `id` FROM `$table` $where")->col();

                        // If at least one key was found
                        if (count($setA)) {

                            // Setup a proper order of elements in $setA array, depending on their titles
                            $setA = Indi::order($this->relation, $setA);

                            // Build the order clause, using FIND_IN_SET function
                            $order = 'FIND_IN_SET(`' . $this->alias . '`, "' . implode(',', $setA) . '") ' . $direction;
                        }
                    }
                }
            }
        }

        // Return ORDER clause
        return $order;
    }

    /**
     * Builds the part of WHERE clause, that will be involved in keyword-search, especially for current field
     *
     * @param $keyword
     * @return string
     */
    public function keywordWHERE($keyword) {

        // If current field does not have it's own column within database table - return
        if (!$this->columnTypeId) return;

        // If column does not store foreign keys
        if ($this->storeRelationAbility == 'none') {

            // If column store boolean values
            if (preg_match('/BOOLEAN/', $this->foreign('columnTypeId')->type)) {
                return db()->sql('IF(`' . $this->alias . '`, "' . I_YES . '", "' .
                    I_NO . '") LIKE :s', '%' . $keyword . '%');

            // Otherwise handle keyword search on other non-relation column types
            } else {

                // Setup an array with several column types and possible characters sets for each type.
                $reg = [
                    'YEAR' => '[0-9]', 'DATE' => '[0-9\-]', 'DATETIME' => '[0-9\- :]',
                    'TIME' => '[0-9:]', 'INT' => '[\-0-9]', 'DOUBLE' => '[0-9\.]', 'DECIMAL' => '[\-0-9\.]'
                ];

                // We check if db table column type is presented within a keys of $reg array, and if so, we check
                // if $keyword consists from characters, that are within a column's type's allowed character set.
                // If yes, we add a keyword clause for that column in a stack. We need to do these two checks
                // because otherwise, for example if we will be trying to find keyword 'Привет' in column that have
                // type DATE - it will cause a mysql collation error
                if (preg_match(
                    '/(' . implode('|', array_keys($reg)) . ')/',
                    $this->foreign('columnTypeId')->type, $matches
                )) {
                    if (preg_match('/^' . $reg[$matches[1]] . '+$/', $keyword)) {
                        return db()->sql('`' . $this->alias . '` LIKE :s', '%' . $keyword . '%');
                    } else {
                        return 'FALSE';
                    }

                // If column's type is CHAR|VARCHAR|TEXT - all is quite simple
                }/* else if ($this->foreign('columnTypeId')->type == 'TEXT') {
                    return 'MATCH(`' . $this->alias . '`) AGAINST("' . implode('* ', explode(' ', $keyword)) . '*' . '" IN BOOLEAN MODE)';
                }*/ else {
                    return db()->sql('`' . $this->alias . '` LIKE :s', '%' . $keyword . '%');
                }
            }

        // If column store foreign keys from `enumset` table
        } else if ($this->relation == 6) {

            // Find `enumset` keys (mean `alias`-es), that have `title`-s, that match keyword
            $idA = db()->query('
                SELECT `alias` FROM `enumset`
                WHERE `fieldId` = "' . $this->id . '" AND `title` LIKE :s
            ', '%' . $keyword . '%')->col();

            // Return clause
            return count($idA)
                ? ($this->storeRelationAbility == 'many'
                    ? 'CONCAT(",", `' . $this->alias . '`, ",") REGEXP ",(' . implode('|', $idA) . '),"'
                    : 'FIND_IN_SET(`' . $this->alias . '`, "' . implode(',', $idA) . '")')
                : 'FALSE';

        // If column store foreign keys, but those keys are from certain single table
        } else if ($this->relation) {

            // Get the related model
            $relatedM = m($this->relation);

            // Declare empty $idA array
            $idA = [];

            // If title column is `id` and
            if ($relatedM->titleColumn() == 'id') {

                // If keyword consists from only numeric characters
                if (preg_match('/^[0-9]+$/', $keyword))

                    // Get the ids
                    $idA = db()->query('
                        SELECT `id` FROM `' . $relatedM->table() . '` WHERE `id` LIKE :s
                    ', '%' . $keyword . '%')->col();

            // Else if WHERE clause, got for keyword search on related model title field - is not 'FALSE'
            } else if (($titleColumnWHERE = $relatedM->titleField()->keywordWHERE($keyword)) != 'FALSE') {

                // Find matched foreign rows, collect their ids, and add a clause
                $idA = db()->query($sql = '
                    SELECT `id` FROM `' . $relatedM->table() . '` WHERE ' . $titleColumnWHERE . '
                ')->col();
            }

            // Return clause
            return count($idA)
                ? ($this->storeRelationAbility == 'many'
                    ? 'CONCAT(",", `' . $this->alias . '`, ",") REGEXP ",(' . implode('|', $idA) . '),"'
                    : 'FIND_IN_SET(`' . $this->alias . '`, "' . implode(',', $idA) . '")')
                : 'FALSE';

        // Else if column store foreign keys, but those keys are from variable tables
        } else {

            // Will be implemented later
        }
    }

    /**
     * Get zero-value for a column type, linked to current field, or boolean `false`,
     * in case if current field has no related column within database table
     *
     * @return bool|string
     */
    public function zeroValue() {

        // If no column type - return
        if (!$this->columnTypeId) return;

        // If this is a enumset field - return it's default value, or return column type default value
        return $this->relation == 6 && $this->storeRelationAbility == 'one'
            ? $this->defaultValue
            : $this->foreign('columnTypeId')->zeroValue();
    }

    /**
     * Get field's maximum possible/allowed length, accroding to INFORMATION_SCHEMA metadata
     *
     * @return int|null
     */
    public function maxLength() {

        // If (for some reason) current field's `entityId` property is empty/zero - return null
        if (!$this->_original['entityId']) return null;

        // Get the name of the table, that current field's entity is assotiated with
        $table = m($this->_original['entityId'])->table();

        // If (for some reason) current field's `alias` property is empty - return null
        if (!$this->_original['alias']) return null;

        // If (for some reason) current field's `columnTypeId` property is empty/zero - return null
        if (!$this->_original['columnTypeId']) return null;

        // Return the maximum possible length, that current field's value are allowed to have
        // Such info is got from `INFORMATION_SCHEMA` pseudo-database
        return (int) db()->query('
            SELECT `CHARACTER_MAXIMUM_LENGTH`
            FROM `INFORMATION_SCHEMA`.`COLUMNS`
            WHERE `table_name` = "'. $table . '"
                AND `table_schema` = "' . ini()->db->name . '"
                AND `column_name` = "'. $this->_original['alias'] . '"
            LIMIT 0 , 1
        ')->cell();
    }

    /**
     * Set/Unset param, stored within $this->_temporary['params'] array
     *
     * @param $name
     * @param null $value
     * @return array
     */
    public function param($name = null, $value = null) {

        // If $value arg was explicitly given, and it was given as NULL,
        // and $name arg exists as a key within $this->_temporary['params'] array
        if (func_num_args() > 1 && $value === null && array_key_exists($name, $this->_temporary['params']))

            // Unset such a param
            unset($this->_temporary['params'][$name]);

        // Else set up a new param under given name with given value and return it
        else if (func_num_args() > 1) return $this->_temporary['params'][$name] = $value;

        // Else if no any args given - return whole $this->_temporary['params'] array
        // as an instance of sdClass, for all supprops to be easily available using
        // $fieldR->param()->optionHeight
        else if (func_num_args() == 0) return (object) $this->_temporary['params'];

        // Else just return it
        else return $this->_temporary['params'][$name];
    }

    /**
     * This method is redefined to setup default value for $within arg,
     * for current `field` entry to be moved within the `entity` it belongs to
     *
     * @param string $direction
     * @param string $within
     * @param bool $last If true - _colAFTER() is called
     * @return bool
     */
    public function move($direction = 'up', $within = '', $last = true) {

        // If $within arg is not given - move field within the entity it belongs to
        if (func_num_args() < 2) $within = '`entityId` = "' . $this->entityId . '"';

        // Call parent
        $return = parent::move($direction, $within);

        // If field was not moved as there nowhere to move - setup $last flag to true
        if (!$return) $last = true;

        // If that was the last move - adjust underlying db table column position as well
        if ($last) $this->_colAFTER();

        // Return
        return $return;
    }

    /**
     * Adjust underlying column position within table  structure
     * for it to match   field  position within entity structure
     */
    protected function _colAFTER() {

        // If current field is a config-field - return
        if ($this->entry) return;

        // If current field does not have underlying db table column - return
        if (!$this->columnTypeId) return;

        // If column have been already moved at the proper position within table structure - return
        if ($this->_system['colAFTER'] === true) return;

        // Get entity, where current field is in
        $model = m($this->entityId); $table = $model->table(); $column = $this->alias;

        // Get all column in the right order
        $colA = $model->fields(null, 'columns');

        // Get index if current field among all fields
        $idx = array_search($column, $colA);

        // Get table definition as raw SQL
        $def = db()->query("SHOW CREATE TABLE `$table`")->cell(1);

        // Parse column definitions from raw SQL
        preg_match_all('~^\s+`([^`]+)` .*?,$~m', $def, $m);

        // Trim leading whitespaces and trailing commas
        array_walk($m[0], fn (&$line) => $line = trim($line, ' ,'));

        // If no definition found - return
        if (!$definition = array_combine($m[1], $m[0])[$column]) return;

        // Get name of the column, after which current column should be moved
        $after = $colA[$idx - 1] ?: 'id';

        // Move the column
        db()->query("ALTER TABLE `$table` CHANGE `$column` $definition AFTER `$after`");
    }

    /**
     * Build a string, that will be used in Field_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = null) {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `move` as they will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id']);

        // Exclude for now `l10n`, as this thing will be sorted out later, once 'l10n' branch will be merged to 'master'
        unset($ctor['l10n']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('entityId,alias' . rif($this->entry, ',entry')) as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = m('Field')->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Else if $prop is 'move' - get alias of the field, that current field is after,
            // among fields with same value of `entityId` prop
            else if ($prop == 'move') $value = $this->position();

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'columnTypeId') $value = coltype($value)->type;
                else if ($prop == 'elementId') $value = element($value)->alias;
                else if ($prop == 'relation') $value = entity($value)->table;
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating/getting the current `field` entry in another project, running on Indi Engine
     *
     * Usage:
     * $field->export(): "field('tablename', 'fieldname', ['fieldprop1' => 'value1', 'fieldprop2' => 'value2', ...]);"
     * $field->export('fieldprop1'): "field('tablename', 'fieldname', ['fieldprop1' => 'value1']);"
     * $field->export(false): "field('tablename', 'fieldname')"
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Shortcuts
        $table = $this->foreign('entityId')->table;
        $entry = $this->entry ? $this->foreign('entry')->alias : $this->entry;

        // Build `field` entry creation line
        $lineA[] = $this->entry
            ? "cfgField('" . $table . "', '" . $entry . "', '" . $this->alias . "'" . rif($certain !== false, ", " . $this->_ctor($certain) . ");", ')')
            : "field('" . $table . "', '" . $this->alias . "'" . rif($certain !== false, ", " . $this->_ctor($certain) . ");", ')');

        // If $certain arg is given - export it only
        if ($certain || $certain === false) return $lineA[0];

        // Foreach `enumset` entry, nested within current `field` entry
        // - build `enumset` entry's creation expression
        foreach ($this->nested('enumset', ['order' => 'move']) as $enumsetR)
            $lineA[] = $enumsetR->export();

        // Foreach `resize` entry, nested within current `field` entry - do same
        foreach ($this->nested('resize') as $resizeR) $lineA[] = $resizeR->export();

        // Foreach `param` entry, nested within current `field` entry - do same
        foreach ($this->nested('param') as $paramR) $lineA[] = $paramR->export();

        // Foreach `param` entry, nested within current `field` entry - do same
        foreach ($this->nested('consider') as $considerR) $lineA[] = $considerR->export();

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Prevent some fields from being localized
     *
     * @return array|void
     */
    public function validate() {

        // If `l10n` prop is not modified - call parent
        if (!array_key_exists('l10n', $this->_modified)) return $this->callParent();

        // Shortcut to column type
        $columnType = $this->foreign('columnTypeId')->type;

        // If field is not intended to contain keys
        if ($this->storeRelationAbility == 'none') {

            // Shortcuts to element
            $element = $this->foreign('elementId')->alias;

            // Setup $allowed flag, inciating whether or not field has a type that is allowed for localization
            $allowed = in($element . ':' . $columnType, [
                'string:VARCHAR(255)',
                'string:TEXT',
                'textarea:TEXT',
                'textarea:VARCHAR(255)',
                'upload:',
                'html:TEXT'
            ]);

            // Setup array of fields, that should not be localized
            $_exclude = [
                'year' => 'title',
                'action' => 'alias',
                'enumset' => 'title,alias',
                'resize' => 'alias',
                'possibleElementParam' => 'alias,defaultValue',
                'field' => 'alias,defaultValue,filter',
                'alteredField' => 'defaultValue',
                'noticeGetter' => 'criteriaEvt,criteriaInc,criteriaDec',
                'admin' => 'email,password',
                'section' => 'alias,extendsPhp,extendsJs,filter',
                'role' => 'dashboard',
                'grid' => 'alias',
                'entity' => 'table,extends',
                'columnType' => 'type',
                'notice' => 'event,qtySql',
                'filter' => 'filter,defautValue',
                'element' => 'alias',
                'lang' => 'title,alias',
                'queueTask' => 'title',
                'queueItem' => 'target,value,result',
                'queueChunk' => 'where,location'
            ];

            // Setup $exclude flag, indicating whether or not field should not be localized despite it's type is ok
            $exclude = in($this->alias, $_exclude[$this->foreign('entityId')->table]);

            // If element and columnType combination is in the list of allowed combinations,
            // and field is not in the exclusions list - call parent
            if ($allowed && !$exclude) return $this->callParent();

        // Else if field contains enumset-keys - call parent
        } else if ($this->foreign('relation')->table == 'enumset') return $this->callParent();

        // Setup mismatch, saying that current field cannot be localized
        $this->_mismatch['l10n'] = sprintf(I_LANG_FIELD_L10N_DENY, $this->title);

        // Call parent
        return $this->callParent();
    }

    /**
     * Get the model, that value of current field's `relation` prop points to
     *
     * @return Indi_Db_Table
     */
    public function rel() {
        return m($this->relation, true);
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
    public function position($after = null, $withinFields = 'entityId,entry') {

        // Build within-fields WHERE clause
        $wfw = [];
        foreach (ar($withinFields) as $withinField)
            if (array_key_exists($withinField, $this->_original))
                $wfw []= '`' . $withinField . '` = "' . $this->$withinField . '"';

        // Get ordered fields aliases
        $fieldA_alias = db()->query(
            'SELECT `alias` FROM `:p` :p ORDER BY `move`', $this->_table, rif($within = im($wfw, ' AND '), 'WHERE $1')
        )->col();

        // Get current position
        $currentIdx = array_flip($fieldA_alias)[$this->alias];

        // Do positioning
        return $this->_position($after, $fieldA_alias, $currentIdx, $within);
    }

    /**
     * Do positioning, if $this->_system['move'] is set
     */
    protected function _move() {

        // If no _system['move'] defined - return
        if (!array_key_exists('move', $this->_system)) return;

        // Get field, that current field should be moved after
        $after = $this->_system['move']; unset($this->_system['move']);

        // Position field for it to be after field, specified by $this->_system['move']
        $this->position($after);
    }

    /**
     * Toggle l10n for a field
     *
     * @param $value
     * @param $lang
     * @param bool $async
     * @throws Exception
     */
    public function toggleL10n($value, $lang, $async = true) {

        // If we're going to start the queue for an aim,
        // that is already achieved, e.g. we're trying to turn on l10n
        // for a field that already has l10n = y - return
        if ($this->l10n == ltrim($value, 'q')) return;

        // Get fraction
        $fraction = ar($this->l10nFraction());

        // Build queue class name
        $queueClassName = 'Indi_Queue_L10n_FieldToggleL10n';

        // If field's element is file-upload - use special queue class
        if ($this->foreign('elementId')->alias == 'upload') $queueClassName .= 'Upload';

        // Create queue class instance
        $queue = new $queueClassName();

        // Get target langs
        $target = [];
        foreach ($fraction as $fractionI) $target[$fractionI] = m('Lang')->all([
            '`' . $fractionI . '` = "y"',
            '`alias` != "' . $lang . '"'
        ])->column('alias', true);

        // Prepare params
        $params = [
            'field' => m($this->entityId)->table() . ':' . $this->alias . rif($this->entry, ':$1'),
            'source' => $lang
        ];

        // If we're dealing with `action` entity's `title` field
        if ($params['field'] == 'action:title' && !$_ = []) {

            // Collect all target languages
            foreach ($target as $targets) $_ = array_unique(array_merge($_, ar($targets)));

            // Pass separately, to be used for root-level `queueChunk` entry ('action:title')
            $params['rootTarget'] = im($_);
        }

        // Prepare params
        $params['target'] = $target;

        // If we're going to turn l10n On for this field - specify target languages,
        // else setup 'toggle' param as 'n', indicating that l10n will be turned On for this field
        if ($value != 'qy') $params['toggle'] = 'n';

        // Run first stage
        $queueTaskR = $queue->chunk($params);

        // If $async arg is true - auto-start queue as a background process
        if ($async === true) Indi::cmd('queue', ['queueTaskId' => $queueTaskR->id]);

        // Else
        else {

            // Update value of `l10n` prop
            $this->set(['l10n' => $value])->basicUpdate();

            // Start queue in synchronous mode
            $queueTaskR->start();
        }
    }

    /**
     * Check whether current field has an underlying db column
     *
     * @return bool
     */
    public function hasColumn() {

        // If it's a cfgField - return false
        if ($this->entry) return false;

        // If field does not have columnTypeId - return false
        if (!$this->columnTypeId) return false;

        // Get model
        $model = m($this->entityId);

        // If no such field in the model anymore - return false
        if (!$model->fields($this->alias)) return false;

        // Get db table name
        $table = $model->table();

        // Check column exists
        return !!db()->query("SHOW COLUMNS FROM `$table` LIKE '{$this->alias}'")->cell();
    }

    /**
     * Get foreign key constraint name to be used on mysql-level
     *
     * @aoc bool If given as true - affected (e.g. previous) values are used where possible
     *           to build the name of foreign key constraint
     * @return string
     */
    protected function _ibfk($aoc = false) {

        // Get entityId
        $entityId = $this->aoc('entityId', $aoc);

        // Get foreign key column name
        $fkColumn = $this->aoc('alias', $aoc);

        // Build and return foreign key constraint name
        return m($entityId)->table() . '_ibfk_' . $fkColumn;
    }

    /**
     * Check whether foreign key constraint exists having given params
     *
     * @aoc bool If given as true - affected (e.g. previous) values are used where possible
     *           to build the name of foreign key constraint to be checked for existence
     * @return bool
     */
    public function hasIbfk($aoc = false) {

        // Build constraint name
        $ibfkName = $this->_ibfk($aoc);

        // Get delete rule
        $deleteRule = $this->aoc('onDelete', $aoc);

        // Get ref entityId
        $relation = $this->aoc('relation', $aoc);

        // Get ref table name
        $refTable = m($relation)->table();

        // Get constraint name, if exists
        // Here we can't append " AND `DELETE_RULE` = '$deleteRule'"
        // as otherwise it causes 'Call to a member function cell() on int' error
        // because query() returns 0 for some unknown reason
        return db()->query("
          SELECT *
          FROM `information_schema`.`REFERENTIAL_CONSTRAINTS`
          WHERE 1
            AND `CONSTRAINT_SCHEMA` = DATABASE() 
            AND `CONSTRAINT_NAME` = '$ibfkName' 
            AND `REFERENCED_TABLE_NAME` = '$refTable'
        ")->cell(8) == $deleteRule;
    }

    /**
     * Add foreign key constraint if need but not defined on mysql-level so far
     *
     * @return bool
     */
    public function addIbfk() {

        // If it's not a foreign-key field, or is, but multi-value foreign-key field - return false
        if ($this->storeRelationAbility != 'one') return false;

        // If it's a cfgField - return false
        if ($this->entry) return false;

        // If it's a variable- or enumset-entity foreign key field - return false
        if ($this->zero('relation') || $this->rel()->table() == 'enumset') return false;

        // If field has zero columnTypeId, or non INT(11) - return false
        if ($this->zero('columnTypeId') || coltype('INT(11)')->id != $this->columnTypeId) return false;

        // Get model
        $model = m($this->entityId);

        // If is VIEW - return
        if ($model->isVIEW()) return false;

        // Get table
        $table = $model->table();

        // Get foreign key column name
        $fkColumn = $this->alias;

        // Get constraint name
        $ibfkName = $this->_ibfk();

        // Get delete rule
        $deleteRule = $this->onDelete;

        // Get referenced table
        $refTable = $this->rel()->table();

        // If ref-table is VIEW - return false
        if (m($refTable)->isVIEW()) return false;

        // If desired foreign key constraint already exists - return it's name
        if ($this->hasIbfk()) return $ibfkName;

        // Make sure NULL is allowed and is a default value for that table column
        db()->query("ALTER TABLE `$table` MODIFY `$fkColumn` INT DEFAULT NULL NULL");

        // Convert 0-values to NULL if any
        db()->query("UPDATE `$table` SET `$fkColumn` = NULL WHERE `$fkColumn` = '0'");

        // Build query
        $sql = "ALTER TABLE `$table` 
          ADD CONSTRAINT `$ibfkName` 
          FOREIGN KEY (`$fkColumn`) REFERENCES `$refTable` (`id`) 
          ON UPDATE CASCADE ON DELETE $deleteRule";

        // Run query
        db()->query($sql);

        // Update model's ibfk-info
        $model->ibfk($fkColumn, $deleteRule);

        // Return
        return true;
    }

    /**
     * Drop foreign key constraint if defined on mysql-level
     *
     * @return bool
     */
    public function dropIbfk() {

        // If it was/is not a foreign-key field, or is, but multi-value foreign-key field - return false
        if ($this->aoc('storeRelationAbility') != 'one') return false;

        // If it was/is a cfgField- return false
        if ($this->aoc('entry')) return false;

        // If it was/is a variable- or enumset-entity foreign key field - return false
        if (!$this->aoc('relation') || m($this->aoc('relation'))->table() == 'enumset') return false;

        // If field had/has zero columnTypeId, or non INT(11) - return false
        if (!$this->aoc('columnTypeId') || coltype('INT(11)')->id != $this->aoc('columnTypeId')) return false;

        // Get model
        $model = m($this->aoc('entityId'));

        // If is VIEW - return
        if ($model->isVIEW()) return false;

        // Get table
        $table = $model->table();

        // Get foreign key column name
        $fkColumn = $this->aoc('alias');

        // Get constraint name
        $ibfkName = $this->_ibfk(true);

        // Get referenced table
        $refTable = m($this->aoc('relation'))->table();

        // If ref-table is VIEW - return false
        if (m($refTable)->isVIEW()) return false;

        // If foreign key constraint does not already exist - return true
        if (!$this->hasIbfk(true)) return true;

        // Run query
        db()->query("ALTER TABLE `$table` DROP FOREIGN KEY `$ibfkName`");

        // Convert NULL-values to 0 if any
        db()->query("UPDATE `$table` SET `$fkColumn` = 0 WHERE ISNULL(`$fkColumn`)");

        // Make sure NULL is not allowed anymore and 0 is a default value for that table column
        db()->query("ALTER TABLE `$table` MODIFY `$fkColumn` INT DEFAULT 0 NOT NULL");

        // Update model's ibfk-info
        $model->ibfk($fkColumn, null);

        // Return
        return true;
    }

    /**
     * Build WHERE clause to be used for finding mentions of all or any of $values in this field
     * $entityId arg is applicable and required in case if this field is a multi-entity foreign key field
     *
     * @param int|array $values
     * @param int|null $entityId
     * @return string
     */
    public function usagesWHERE($values, $entityId = null) {

        // If $ids is array
        if (is_array($values)) {

            // Prepare WHERE clause template
            $where = $this->storeRelationAbility == 'many'
                ? "CONCAT(',', `%s`, ',') REGEXP ',(%s),'"
                : "`%s` IN (%s)";

            // Concat ids by '|' or ',' depending on whether it's a multi- or single-value foreign key field
            $values = im($values, $this->storeRelationAbility == 'many' ? '|' : ',');

            // Build WHERE clause to find usages
            $where = sprintf($where, $this->alias, $values);

        // Else
        } else {

            // Prepare WHERE clause template
            $where = $this->storeRelationAbility == 'many'
                ? "FIND_IN_SET('%s', `%s`)"
                : "'%s' = `%s`";

            // Build WHERE clause to find usages
            $where = sprintf($where, $values, $this->alias);
        }

        // If this is a ENUM-column, the following may happen:
        // 1. We have `enumCol` ENUM('val1', 'val2')
        // 2. We have two rows in the table, having 'val1' for 1st row and 'val2' for 2nd
        // 3. We drop 'val2' from column definition, so it become `enumCol` ENUM('val1')
        // 4. We see that the value of `enumCol` in 2nd row became empty
        // 5. We do SELECT * FROM `thatTable` WHERE `enumCol` = "anyRandomValueIncludingEmptyStringExcept_val1"
        // 6. We get row2 in the result set, despite we should NOT
        // 7. `enumCol` != "" does hot help, but prepending with BINARY - does
        //    we have to use that workaround because otherwise usagesWHERE clause
        //    is matching row that should not be matched, so hasUsages() and getUsages() return wrong results
        if ($this->foreign('columnTypeId')->type == 'ENUM')

            // Append additional `BINARY <column>` != '' clause
            $where .= sprintf(' AND BINARY `%s` != ""', $this->alias);

        // If it's a multi-entity foreign key field
        // we should prepend additional column match clause
        // which is pointing to the right entity
        if ($this->storeRelationAbility != 'none' && $this->zero('relation')) {

            // Get ref
            $ref = db()->multiRefs($this->onDelete)[$this->id];

            // If column named as $ref['entity'] in $table does not really contain ids of `entity`-records
            if ($ref['foreign']) {

                // Get table and column which does
                list ($table, $column) = explode('.', $ref['foreign']);

                // Get ids applicable for use in WHERE clause
                $ids = db()->query("SELECT `id` FROM `$table` WHERE `$column` = '$entityId'")->in();

                // Build WHERE clause using those $ids instead of $entityId
                $where = "`{$ref['entity']}` IN ($ids) AND " . $where;

            // Else
            } else {

                // Build WHERE clause using $entityId
                $where = "`{$ref['entity']}` = $entityId AND " . $where;
            }
        }

        // Return WHERE clause for finding usages
        return $where;
    }

    /**
     * Call onAddedAsForeignKey() on the model, that foreign-key field is pointing to
     */
    public function onInsert() {

        // If it's not a foreign-key field - return
        if ($this->storeRelationAbility === 'none') return;

        // If it's a multi-entity foreign-key field - return
        if (!$this->relation) return;

        // If it's a single-entity foreign-key field - call hook method
        $this->rel()->onAddedAsForeignKey($this);
    }

    /**
     * Overridden to handle case when current field is a global-level config-field,
     * so in that case we assume such a field to belong to 'system' fraction
     *
     * @return string
     */
    public function fraction() {
        return $this->entityId ? parent::fraction() : 'system';
    }
}