<?php
class Field_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = array()) {

        // Explicitly set table name
        $config['table'] = 'field';

        // Call parent
        parent::__construct($config);
    }

    /**
     * Set row field value, by creating an item of $this->_modified array, in case if
     * value is different from value of $this->_original at same key ($columnName)
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Check if value is a color in #RRGGBB format and prepend it with hue number
        if (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) $value = hrgb($value);

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Delete field
     *
     * @return int|void
     */
    public function delete() {

		// Delete uploaded images or files as they were uploaded as values
		// of this field if they were uploaded
		$this->deleteFiles();

        // Delete related enumset rows
        Indi::db()->query('DELETE FROM `enumset` WHERE `fieldId` = "' . $this->id . '"');

        // If current field is used as a title-field for entity, it's relating too
        if ($this->id == $this->foreign('entityId')->titleFieldId) {

            // Set titleFieldId to 0 and save the entity, to prevent full
            // entity deletion in the process of usages deletion
            $this->foreign('entityId')->titleFieldId = 0;
            $this->foreign('entityId')->save();
        }

        // Prevent deletion of `section` entries, having current `field` entry as `defaultSortField`
        if ($sectionRs = Indi::model('Section')->fetchAll('`defaultSortField` = "' . $this->id . '"'))
            foreach ($sectionRs as $sectionR) {
                $sectionR->defaultSortField = 0;
                $sectionR->save();
            }

        // Prevent deletion of `section` entries, having current `field` entry as `parentSectionConnector`
        if ($sectionRs = Indi::model('Section')->fetchAll('`parentSectionConnector` = "' . $this->id . '"'))
            foreach ($sectionRs as $sectionR) {
                $sectionR->parentSectionConnector = 0;
                $sectionR->save();
            }

        // Standard deletion
        $return = parent::delete();

        // Delete db table associated column
        $this->deleteColumn();

        // Delete current field from model's fields
        Indi::model($this->entityId)->fields()->exclude($this->id);

        // Return
        return $return;
    }

    /**
     * Drop the database table column, that current field is representing
     */
    public function deleteColumn() {

        // If current field does have a column
		if ($this->columnTypeId)

            // If that column is still exist within table structure. Here we do this check, because that column might
            // have already been deleted, for example in case if we were deleting the whole entity, and one field was
            // a satellite for another field within that entity, so the satellited-field will be deleted in the process
            // of deleting satellite-field usages, as satellited-field is using satellite-field, so satellited-field
            // will be deleted BEFORE satellite-field deletion. And here we do this chech because in that case (whole
            // entity) deletion system will try to delete satellited-field twice - first at the stage of other field's
            // usage deletion, and second at the stage of ordinary deletion.
            if (Indi::db()->query(
                'SHOW COLUMNS FROM `' . $this->foreign('entityId')->table. '` LIKE "' . $this->alias . '"'
            )->fetchColumn())

            // Drop that column
			Indi::db()->query('ALTER TABLE `' . $this->foreign('entityId')->table . '` DROP `' . $this->alias . '`');
	}

    /**
     * Delete all files, that were created by usage of current field
     */
    public function deleteFiles() {

        // If current field has no column type, and field's control element is 'upload'
		if (!$this->columnTypeId && Indi::model($this->_original['entityId'])
            ->fields($this->_original['alias'])->foreign('elementId')->alias == 'upload') {

            // Get the table
            $table = Indi::model($this->_original['entityId'])->table();

            // Get the directory name
            $dir = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $table . '/';

            // If directory does not exist - return
            if (!is_dir($dir)) return;

            // Get the array of uploaded files and their copies (if some of them are images)
            $fileA = glob($dir . '[0-9]*_' . $this->_original['alias'] . '[,.]*');

            // Delete files
            foreach ($fileA as $fileI) @unlink($fileI);
		}
	}

    /**
     * Rename uploaded files for case if current field's `alias` property
     * was changed, so this change should affect uploaded files names
     *
     * @return mixed
     */
    protected function _renameUploadedFiles() {

        // If `alias` property was not changed - return
        if (!$this->_modified['alias']) return;

        // Get the table
        $table = $this->foreign('entityId')->table;

        // Get the directory name
        $dir = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $table . '/';

        // If directory does not exist - return
        if (!is_dir($dir)) return;

        // Get the array of uploaded files and their copies (if some of them are images)
        $fileA = glob($dir . '[1-9]*_' . $this->_original['alias'] . '[,.]*');

        // Delete files
        foreach ($fileA as $fileI) {

            // Determine a new name
            $new = preg_replace('~(/[1-9][0-9]*_)' . $this->_original['alias'] . '([,\.])~', '$1' . $this->alias . '$2', $fileI);

            // Rename
            rename($fileI, $new);
        }
    }

    /**
     * Save field
     *
     * @return int
     */
    public function save() {

        // Declare the array of properties, who's modification leads to necessity of sql ALTER query to be executed
        $affect = array('entityId', 'alias', 'columnTypeId', 'defaultValue', 'storeRelationAbility');

        // If field's control element was 'upload', but now it is not - set $deleteUploadedFiles to true
        $uploadElementId = Indi::model('Element')->fetchRow('`alias` = "upload"')->id;
        if ($this->_original['elementId'] == $uploadElementId && array_key_exists('elementId', $this->_modified))
            $deleteUploadedFiles = true;

        // Detect if any of modified properties is/are within $affect array, and if no
        if (!array_intersect($affect, array_keys($this->_modified))) {

            // If $deleteUploadedFiles flag is set to true - call deleteUploadedFiles() method
            if ($deleteUploadedFiles) $this->deleteUploadedFiles();

            // Backup original data
            $original = $this->_original;

            // Standard save
            $return = parent::save();

            // Reload the model, because field was deleted
            Indi::model($this->entityId)->reload();

            // Check if saving of current field should affect current entity's
            // `titleFieldId` property and affect all involved titles
            $this->_titleFieldUpdate($original);

            // Return
            return $return;
        }

        // If `entityId` property was modified, and current field is an existing field
        if ($this->id && $this->_modified['entityId']) {

            // Get names of tables, related to both original and modified entityIds
            list($wasTable, $table) = Indi::model('Entity')->fetchAll(
                '`id` IN (' . $this->_original['entityId'] . ',' . $this->_modified['entityId'] . ')',
                'FIND_IN_SET(`id`, "' . $this->_original['entityId'] . ',' . $this->_modified['entityId'] . '")'
            )->column('table');

            // Drop column from old table, if that column exists
            if ($this->_original['columnTypeId'])
                Indi::db()->query('ALTER TABLE `' . $wasTable . '` DROP COLUMN `' . $this->_original['alias'] .'`');

            // If field's control element was and still is 'upload' - set $deleteUploadedFiles flag to true.
            if ($this->elementId == $uploadElementId && $this->_original['elementId'] == $uploadElementId)
                $deleteUploadedFiles = true;

            // Reload the model, because field was deleted
            Indi::model($this->_original['entityId'])->fields()->exclude($this->id);

            // Get original entity row
            $entityR = Indi::model('Entity')->fetchRow('`id` = "' . $this->_original['entityId'] . '"');

            // If current field was used as title-field within original entity
            if ($entityR->titleFieldId == $this->id) {

                // Reset `titleFieldId` property of original entity and save that entity
                $entityR->titleFieldId = 0;
                $entityR->save();
            }

        // Else if `entityId` property was not modified
        } else {

            // Get name of the table, related to `entityId` property
            $table = Indi::model($this->entityId)->table();
        }

        // We should add a new column in database table in 3 cases:
        // 1. Field was moved from one entity to another, and field now has non-zero
        //    columnTypeId property, and does not matter whether is had it before or not
        // 2. Field is new, and columnTypeId property is non-zero
        // 3. Field had no column, e.g columnTypeId property has zero-value, but now it is non-zero
        if ($this->columnTypeId && ($this->_modified['entityId'] || !$this->id || !$this->_original['columnTypeId'])) {

            // Start building an ADD COLUMN query
            $sql[] = 'ALTER TABLE `' . $table . '` ADD COLUMN `' . $this->alias . '`';

        // Else if we are certainly not dealing with field throw from one
        // entity to another, and field had non-zero columnTypeId originally
        } else if (!array_key_exists('entityId', $this->_modified) && $this->_original['columnTypeId']) {

            // If columnTypeId was non-zero, but now it is
            if (array_key_exists('columnTypeId', $this->_modified) && !$this->_modified['columnTypeId']) {

                // Run a DROP COLUMN query
                Indi::db()->query('ALTER TABLE `' . $table . '` DROP COLUMN `' . $this->_original['alias'] . '`');

                // Delete rows from `enumset` table, that are related to current field
                $this->clearEnumset();

                // If $deleteUploadedFiles flag is set to true - call deleteUploadedFiles() method
                if ($deleteUploadedFiles) $this->deleteUploadedFiles();

                // Backup original data
                $original = $this->_original;

                // Standard save
                $return = parent::save();

                // Reload the model, because field was deleted
                Indi::model($this->entityId)->reload();

                // Check if saving of current field should affect current entity's
                // `titleFieldId` property and affect all involved titles
                $this->_titleFieldUpdate($original);

                // Return
                return $return;

            // Else if columnType was non-zero, and now it is either not changed, or changed but to also non-zero value
            } else

                // Start building a CHANGE COLUMN query
                $sql[] = 'ALTER TABLE `' . $table . '` CHANGE COLUMN `' . $this->_original['alias'] . '` `' . $this->alias . '`';
        }

        // If no query builded - do a standard save
        if (!$sql) {

            // If $deleteUploadedFiles flag is set to true - call deleteUploadedFiles() method
            if ($deleteUploadedFiles) $this->deleteUploadedFiles();

            // Else if `alias` property is modified, and current field's control element was and still is 'upload'
            else if ($this->_modified['alias'] && $this->elementId == $uploadElementId && $this->_original['elementId'] == $uploadElementId)

                // Rename uploaded files, for their names to be affected by change of current field's `alias` property
                $this->_renameUploadedFiles();

            // Backup original data
            $original = $this->_original;

            // Standard save
            $return = parent::save();

            // Reload the model, because field info was changed
            Indi::model($this->entityId)->reload();

            // Check if saving of current field should affect current entity's
            // `titleFieldId` property and affect all involved titles
            $this->_titleFieldUpdate($original);

            // Return
            return $return;
        }

        // Get the column type row
        $columnTypeR = $this->foreign('columnTypeId');

        // Add the primary type definition to a query
        $sql[] = $columnTypeR->type;

        // If column type is SET or ENUM - add the secondary type definition
        if (preg_match('/^ENUM|SET$/', $columnTypeR->type)) {

            // Get the existing enumset values
            $enumsetA = $this->id ? $this->nested('enumset')->column('alias'): array();

            // Get the array of default values
            $defaultValueA = preg_match(Indi::rex('php'), $this->defaultValue)
                ? array('')
                : explode(',', $this->defaultValue);

            // Get the values, that should be added to the list of possible values
            $enumsetAppendA = array_diff($defaultValueA, $enumsetA);

            // Get the final list of possible values
            $enumsetA = array_merge($enumsetA, $enumsetAppendA);

            // Append the list of possible values to sql column type definition
            $sql[] = '("' . implode('","', $enumsetA) . '")';

            // Force `relation` property to be '6' - id of enumset `entity`
            $this->relation = 6;
        }

        // Add the collation definition, if column type supports it
        $collatedColumnTypeA = array('CHAR', 'VARCHAR', 'TEXT', 'ENUM', 'SET');
        foreach ($collatedColumnTypeA as $collatedColumnTypeI)
            if (preg_match('/^' . $collatedColumnTypeI . '/', $columnTypeR->type))
                $sql[] = 'CHARACTER SET utf8 COLLATE utf8_general_ci';

        // Add the 'NOT NULL' expression
        $sql[] = 'NOT NULL';

        // Add the DEFAULT definition for column, but only if it's type is not
        // BLOB or TEXT, as these types do not support default value definition
        if (!preg_match('/^BLOB|TEXT$/', $columnTypeR->type)) {

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

            // Append sql DEFAULT expression to query
            $sql[] = 'DEFAULT "' . $defaultValue . '"';

            // Check if field's column datatype is going to be changed, and if so - check whether there is a need
            // to adjust existing values, to ensure that they will be compatiple with new datatype, and won't cause
            // mysql error like 'Incorrect integer value ...'  during execution of a change-column-datatype sql query
            $this->_enforceExistingValuesCompatibility($columnTypeR->type, $defaultValue, $enumsetA);
        }

        // Implode the parts of sql query
        $sql = implode(' ', $sql);

        // Run the query
        Indi::db()->query($sql);

        // If we are creating move-column, e.g. this column will be used for ordering rows
        // Force it's values to be same as values of `id` column, for it to be possible to
        // move entries up/down once such a column was created
        if (!$this->id && $columnTypeR->type == 'INT(11)' && $this->foreign('elementId')->alias == 'move')
            Indi::db()->query('UPDATE `' . $table . '` SET `' . $this->alias . '` = `id`');

        // If field column type was ENUM or SET, but now it is not -
        // we should delete rows, related to current field, from `enumset` table
        if (!preg_match('/^ENUM|SET$/', $columnTypeR->type)
            && preg_match('/^ENUM|SET$/', Indi::model('ColumnType')
                ->fetchRow('`id` = "' . $this->_original['columnTypeId'] . '"')->type))
                    $this->clearEnumset();

        // Remember original data before call parent::save(), as this data
        // will be used bit later for proper column indexes adjustments
        $original = $this->_original;

        // If there was a relation, but now there is no - we perform a number of 'reset' adjustments, that aim to
        // void values of all properties, that are certainly not used now, as field does not store foreign keys no more
        if (array_key_exists('storeRelationAbility', $this->_modified) && $this->_modified['storeRelationAbility'] == 'none') {

            // If control element was radio or multicheck - set it as string
            if (preg_match('/^radio|multicheck$/', $this->foreign('elementId')->alias))
                $this->elementId = Indi::model('Element')->fetchRow('`alias` = "string"')->id;

            // Else if control element was combo, and column type is not BOOLEAN - set control element as string also
            else if ($this->foreign('elementId')->alias == 'combo' && $columnTypeR->type != 'BOOLEAN')
                $this->elementId = Indi::model('Element')->fetchRow('`alias` = "string"')->id;

            // If column type was ENUM or SET - set it as VARCHAR(255)
            if (preg_match('/^ENUM|SET$/', $columnTypeR->type))
                $this->columnTypeId = Indi::model('ColumnType')->fetchRow('`type` = "VARCHAR(255)"')->id;

            // Setup `relation` as 0
            $this->relation = 0;

            // Setup `filter` as an empty string
            $this->filter = '';
        }

        // If store relation ability changed to  'many'
        if ($this->_modified['storeRelationAbility'] == 'many') {

            // If field had it's own column within database table, and still has
            if ($this->_original['columnTypeId'] && $this->_modified['columnTypeId']) {

                // If all these changes are performed within the same entity
                if ($table && !array_key_exists('entityId', $this->_modified)) {

                    // Remove zero-values from column, as zero-values are not allowed
                    // for fields, that have storeRelationAbility = 'many'
                    Indi::db()->query($sql = '
                        UPDATE `' . $table . '` SET `' . $this->alias . '`
                            = SUBSTR(REPLACE(CONCAT(",", `' . $this->alias . '`), ",0", ""), 2)
                    ');
                }
            }
        }

        // If $deleteUploadedFiles flag is set to true - call deleteUploadedFiles()
        if ($deleteUploadedFiles) $this->deleteUploadedFiles();

        // Standard save
        $return = parent::save();

        // If earlier we detected some values, that should be inserted to `enumset` table - insert them
        if ($enumsetAppendA)
            foreach ($enumsetAppendA as $enumsetAppendI)
                Indi::db()->query('
                    INSERT INTO `enumset` SET
                    `fieldId` = "' . $this->id . '",
                    `title` = "' . sprintf(I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE, $enumsetAppendI) . '",
                    `alias` = "' . $enumsetAppendI. '",
                    `javascript` = "",
                    `move` = "' . Indi::db()->query('SHOW TABLE STATUS LIKE "enumset"')->fetch(PDO::FETCH_OBJ)->Auto_increment . '"
                ');

        // Check if where was no relation and index, but now relation is exist, - we add an INDEX index
        if (preg_match('/INT|SET|ENUM|VARCHAR/', $columnTypeR->type))
            if (!Indi::db()->query('SHOW INDEXES FROM `' . $table .'` WHERE `Column_name` = "' . $this->alias . '"')
                ->fetch(PDO::FETCH_OBJ)->Key_name)
                    if ($original['storeRelationAbility'] == 'none' && $this->storeRelationAbility != 'none')
                        Indi::db()->query('ALTER TABLE  `' . $table .'` ADD INDEX (`' . $this->alias . '`)');

        // Check if where was a relation, and these was an index, but now there is no relation, - we remove an INDEX index
        if ($original['storeRelationAbility'] != 'none' && $this->storeRelationAbility == 'none')
            if ($index = Indi::db()->query('SHOW INDEXES FROM `' . $table .'` WHERE `Column_name` = "' . $this->alias . '"')
                ->fetch(PDO::FETCH_OBJ)->Key_name)
                    Indi::db()->query('ALTER TABLE  `' . $table .'` DROP INDEX `' . $index . '`');

        // Check if is was not a TEXT column, and it had no FULLTEXT index, but now it is a TEXT column, - we add a FULLTEXT index
        if (Indi::model('ColumnType')->fetchRow('`id` = "' . $original['columnTypeId'] . '"')->type != 'TEXT')
            if (!Indi::db()->query('SHOW INDEXES FROM `' . $table .'` WHERE `Column_name` = "' . $this->alias . '"
                AND `Index_type` = "FULLTEXT"')->fetch())
                    if ($columnTypeR->type == 'TEXT')
                        Indi::db()->query('ALTER TABLE  `' . $table .'` ADD FULLTEXT (`' . $this->alias . '`)');

        // Check if is was a TEXT column, and it had a FULLTEXT index, but now it is not a TEXT column, - we remove a FULLTEXT index
        if (Indi::model('ColumnType')->fetchRow('`id` = "' . $original['columnTypeId'] . '"')->type == 'TEXT')
            if ($index = Indi::db()->query('SHOW INDEXES FROM `' . $table .'` WHERE `Column_name` = "' . $this->alias . '"
                AND `Index_type` = "FULLTEXT"')->fetch(PDO::FETCH_OBJ)->Key_name)
                    if ($columnTypeR->type != 'TEXT')
                        Indi::db()->query('ALTER TABLE  `' . $table .'` DROP INDEX `' . $index . '`');


        // Reload the model, because field info was changed
        Indi::model($this->entityId)->reload();

        // Check if saving of current field should affect current entity's
        // `titleFieldId` property and affect all involved titles
        $this->_titleFieldUpdate($original);

        // Return
        return $return;
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
        if (!($this->_original['columnTypeId'] && $this->_modified['columnTypeId'] && !$this->_modified['entityId'])) return;

        // Get the column type row, representing field's column before type change (original column)
        $curTypeR = Indi::model('ColumnType')->fetchRow('`id` = "' . $this->_original['columnTypeId'] . '"');

        // Get the table name
        $tbl = $this->foreign('entityId')->table;

        // Get the field's column name
        $col = $this->_original['alias'];

        // Define array of rex-names, related to their mysql data types
        $rex = array(
            'VARCHAR(255)' => 'varchar255', 'INT(11)' => 'int11', 'DECIMAL(11,2)' => 'decimal112',
            'DECIMAL(14,3)' => 'decimal143', 'DATE' => 'date', 'YEAR' => 'year', 'TIME' => 'time',
            'DATETIME' => 'datetime', 'ENUM' => 'enum', 'SET' => 'set', 'BOOLEAN' => 'bool', 'VARCHAR(10)' => 'hrgb'
        );

        // Prepare regular expression for usage in WHERE clause in
        // UPDATE query, for detecting and fixing incompatible values
        $regexp = preg_replace('/\$$/', ')$', preg_replace('/^\^/', '^(', trim(Indi::rex($rex[$newType]), '/')));

        // Setup double-quote variable, and WHERE usage flag
        $q = '"'; $w = true; $incompatibleValuesReplacement = false;

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
                foreach(Indi::model($tbl)->fields($this->id)->nested('enumset') as $enumsetR)
                    if (strlen($enumsetR->alias) > $maxLen) $maxLen = strlen($enumsetR->alias);
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR('. $maxLen . ') NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00';
            } else if (preg_match('/SET/', $curTypeR->type)) {
                $shortestValue = '';
                foreach(Indi::model($tbl)->fields($this->id)->nested('enumset') as $enumsetR)
                    if (strlen($enumsetR->alias) < strlen($shortestValue)) $shortestValue = $enumsetR->alias;
                Indi::db()->query('UPDATE TABLE `' . $tbl . '` SET `' . $col . '` = "' . $shortestValue . '"');
                $minLen = ($svl = strlen($shortestValue)) > 10 ? $svl : 10;
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(' . $minLen . ') NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00';
            } else if (preg_match('/YEAR/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = 'CONCAT(`' . $col .'`, IF(`' . $col . '` = "0000", "0000", "0101"))';
                $q = ''; $w = false;
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            } else if (preg_match('/^DATETIME|TIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = 'IF(DAYOFYEAR(CAST(`' . $col .'` AS UNSIGNED)),
                DATE_FORMAT(CAST(`' . $col .'` AS UNSIGNED), "%Y-%m-%d"), "0000-00-00")'; $q = ''; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(14) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00'; $w = false;
            }
        } else if ($newType == 'YEAR') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0000';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col .'`, 1, 4)';
                $q = ''; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col .'`, 1, 4)';
                $q = ''; $w = false;
            } else if (preg_match('/^TIME$/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(4) NOT NULL');
                $incompatibleValuesReplacement = '0000'; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` INT NOT NULL');
                $incompatibleValuesReplacement = '0'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` INT NOT NULL');
                $incompatibleValuesReplacement = '0'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` INT NOT NULL');
                $incompatibleValuesReplacement = '0'; $w = false;
            }
        } else if ($newType == 'TIME') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '00:00:00';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'SUBSTR(`' . $col .'`, 12)';
                $q = ''; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(12) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(15) NOT NULL');
                $incompatibleValuesReplacement = '00:00:00'; $w = false;
            }
        } else if ($newType == 'DATETIME') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0000-00-00 00:00:00';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {

            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'CONCAT(`' . $col . '`, "-", IF(`' . $col . '` = "0000", "00-00", "01-01"), " 00:00:00")';
                $w = false; $q = '';
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = '0000-00-00 00:00:00'; $w = false;
            }
        } else if ($newType == 'BOOLEAN') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '0';
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0000-00-00 00:00:00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(4) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0000", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0000-00-00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(8) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "00:00:00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                $incompatibleValuesReplacement = '1';
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0.00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/DECIMAL\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(12) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0.00", "0", "1")'; $w = false; $q = '';
            } else if (preg_match('/DECIMAL\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(15) NOT NULL');
                $incompatibleValuesReplacement = 'IF(`' . $col . '` = "0.000", "0", "1")'; $w = false; $q = '';
            }
        } else if ($newType == 'ENUM' || $newType == 'SET') {
            if (preg_match('/TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/VARCHAR\(255\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/SET/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $regexp = '^' . implode('|', $enumsetA) . '$';
            } else if (preg_match('/BOOLEAN/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` TEXT NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            }
        } else if ($newType == 'VARCHAR(10)') {
            if (preg_match('/VARCHAR|TEXT/', $curTypeR->type)) {
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/ENUM|SET/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(' . $this->maxLength() . ') NOT NULL');
                $incompatibleValuesReplacement = $defaultValue;
            } else if (preg_match('/DATETIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(19) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/^YEAR$/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DATE/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/TIME/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(10) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/INT\(11\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(7,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(11) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(11,2\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(12) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            } else if (preg_match('/DOUBLE\(14,3\)/', $curTypeR->type)) {
                Indi::db()->query('ALTER TABLE `' . $tbl . '` MODIFY `' . $col . '` VARCHAR(15) NOT NULL');
                $incompatibleValuesReplacement = $defaultValue; $w = false;
            }
        }

        // Adjust existing values, for them to be compatible with type, that field's column will be
        // converted to. We should do it to aviod mysql error like 'Incorrect integer value ...' etc
        if ($incompatibleValuesReplacement !== false)
            Indi::db()->query('
                UPDATE `' . $tbl . '`
                SET `' . $col . '` = ' . $q . $incompatibleValuesReplacement . $q .
                ($w ? ' WHERE `' . $col . '` NOT REGEXP "' . $regexp . '"' : '')
            );
    }

    /**
     * Deletes rows from `enumset` table, that are nested to current field
     */
    public function clearEnumset() {
        if ($this->id) Indi::db()->query('DELETE FROM `enumset` WHERE `fieldId` = "' . $this->id . '"');
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
            $this->_compiled = array();
        }

        // Do regular conversion
        $return = parent::toArray($type, $deep);

        // If toArray conversion mode is 'current' - restore $this->_compiled property
        if ($type == 'current') $this->_compiled = $compiled;

        // Return conversion result
        return $return;
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
        if ($this->relation == 0) {

            // If column store boolean values
            if (preg_match('/BOOLEAN/', $this->foreign('columnTypeId')->type)) {
                return 'IF(`' . $this->alias . '`, "' . I_YES . '", "' .
                    I_NO . '") LIKE "%' . $keyword . '%"';

            // Otherwise handle keyword search on other non-relation column types
            } else {

                // Setup an array with several column types and possible characters sets for each type.
                $reg = array(
                    'YEAR' => '[0-9]', 'DATE' => '[0-9\-]', 'DATETIME' => '[0-9\- :]',
                    'TIME' => '[0-9:]', 'INT' => '[\-0-9]', 'DOUBLE' => '[0-9\.]', 'DECIMAL' => '[\-0-9\.]'
                );

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
                        return '`' . $this->alias . '` LIKE "%' . $keyword . '%"';
                    } else {
                        return 'FALSE';
                    }

                // If column's type is CHAR|VARCHAR|TEXT - all is quite simple
                }/* else if ($this->foreign('columnTypeId')->type == 'TEXT') {
                    return 'MATCH(`' . $this->alias . '`) AGAINST("' . implode('* ', explode(' ', $keyword)) . '*' . '" IN BOOLEAN MODE)';
                }*/ else {
                    return '`' . $this->alias . '` LIKE "%' . $keyword . '%"';
                }
            }

        // If column store foreign keys from `enumset` table
        } else if ($this->relation == 6) {

            // Find `enumset` keys (mean `alias`-es), that have `title`-s, that match keyword
            $idA = Indi::db()->query('
                SELECT `alias` FROM `enumset`
                WHERE `fieldId` = "' . $this->id . '" AND `title` LIKE "%' . $keyword . '%"
            ')->fetchAll(PDO::FETCH_COLUMN);

            // Return clause
            return count($idA)
                ? ($this->storeRelationAbility == 'many'
                    ? 'CONCAT(",", `' . $this->alias . '`, ",") REGEXP ",(' . implode('|', $idA) . '),"'
                    : 'FIND_IN_SET(`' . $this->alias . '`, "' . implode(',', $idA) . '")')
                : 'FALSE';

            // If column store foreign keys, but not from `enumset` table
        } else {

            // If column does not have a satellite (dependency='u'), or have but dependency type is set to 'c'
            // (- mean childs-by-parent logic)
            if (preg_match('/c|u/', $this->dependency)) {

                // Get the related model
                $relatedM = Indi::model($this->relation);

                // Declare empty $idA array
                $idA = array();

                // If title column is `id` and
                if ($relatedM->titleColumn() == 'id') {

                    // If keyword consists from only numeric characters
                    if (preg_match('/^[0-9]+$/', $keyword))

                        // Get the ids
                        $idA = Indi::db()->query('
                            SELECT `id` FROM `' . $relatedM->table() . '`
                            WHERE `id` LIKE "%' . $keyword . '%"
                        ')->fetchAll(PDO::FETCH_COLUMN);

                // Else if WHERE clause, got for keyword search on related model title field - is not 'FALSE'
                } else if (($titleColumnWHERE = $relatedM->titleField()->keywordWHERE($keyword)) != 'FALSE') {

                    // Find matched foreign rows, collect their ids, and add a clause
                    $idA = Indi::db()->query($sql = '
                        SELECT `id` FROM `' . $relatedM->table() . '`
                        WHERE ' . $titleColumnWHERE . '
                    ')->fetchAll(PDO::FETCH_COLUMN);
                }

                // Return clause
                return count($idA)
                    ? ($this->storeRelationAbility == 'many'
                        ? 'CONCAT(",", `' . $this->alias . '`, ",") REGEXP ",(' . implode('|', $idA) . '),"'
                        : 'FIND_IN_SET(`' . $this->alias . '`, "' . implode(',', $idA) . '")')
                    : 'FALSE';

            // Else if dependency=e - mean 'Variable entity'. Will be implemented later
            } else {

            }
        }
    }

    /**
     * Get zero-value for a column type, linked to current field, or boolean `false`,
     * in case if current field has no related column within database table
     *
     * @return bool|string
     */
    public function zeroValue() {
        return $this->columnTypeId ? $this->foreign('columnTypeId')->zeroValue() : false;
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
        $table = Indi::model($this->_original['entityId'])->table();

        // If (for some reason) current field's `alias` property is empty - return null
        if (!$this->_original['alias']) return null;

        // If (for some reason) current field's `columnTypeId` property is empty/zero - return null
        if (!$this->_original['columnTypeId']) return null;

        // Return the maximum possible length, that current field's value are allowed to have
        // Such info is got from `INFORMATION_SCHEMA` pseudo-database
        return (int) Indi::db()->query('
            SELECT `CHARACTER_MAXIMUM_LENGTH`
            FROM `INFORMATION_SCHEMA`.`COLUMNS`
            WHERE `table_name` = "'. $table . '"
                AND `table_schema` = "' . Indi::ini()->db->dbname . '"
                AND `column_name` = "'. $this->_original['alias'] . '"
            LIMIT 0 , 1
        ')->fetchColumn();
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
}