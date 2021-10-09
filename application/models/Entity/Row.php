<?php
class Entity_Row extends Indi_Db_Table_Row {

    /**
     * @return array|void
     */
    public function validate() {

        // Check
        $this->vcheck(array(
            'title' => array(
                'req' => true
            ),
            'table' => array(
                'req' => true,
                'rex' => '/^[a-zA-Z0-9]+$/',
                'unq' => true
            )
        ));

        // Return
        return $this->callParent();
    }

    /**
     * Delete current entity
     *
     * @return int|void
     */
    public function delete(){

		// Delete all uploaded files of entity rows and folder they were stored in
		$this->deleteAllUploadedFilesAndUploadFolder();

        // Standard deletion
        $return = parent::delete();

        // Delete database table if that table exists
        if (Indi::db()->query('SHOW TABLES LIKE "' . $this->table . '"')->fetchColumn())
            Indi::db()->query('DROP TABLE `' . $this->table . '`');

        // Destroy model
        Indi::model($this->table, 'destroy');

        // Return
        return $return;
	}

    /**
     * Delete the whole directory, containing files, related to current entity. If all was successful - return true,
     * else if all files were deleted, but directory was not - return false, else if some of files were not deleted
     *  - return their count
     *
     * @return bool|int
     */
    public function deleteAllUploadedFilesAndUploadFolder() {

        // Get the directory name
        $dir = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $this->table . '/';

        // If directory does not exist - return
        if (!is_dir($dir)) return;

        // Get all files
        $fileA = glob($dir . '*');

        // Delete them
        $deleted = 0; foreach ($fileA as $fileI) $deleted += @unlink($fileI);

        // If all files were deleted - try to delete empty directory and return it's success,
        // else return count of files that were not deleted for some reason
        $return = $deleted == count($fileA) ? rmdir($dir) : count($fileA) - $deleted;

        // Delete all files/folder uploaded/created while using CKFinder
        $this->deleteCKFinderFiles();

        // Return
        return $return;
	}

    /**
     * Delete all of the files/folders uploaded/created as a result of CKFinder usage. Actually,
     * this function can do a deletion only in one case - if entity/model, that current row is representing
     * - is involved in 'alternate-cms-users' feature. That feature assumes, that any row, related to
     * such an entity/model - is representing a separate user account, that have ability to sign in into the
     * Indi Engine system interface, and users might have been signing into the interface and using CKFinder,
     * so this function provides the removing such usage results
     *
     * @return mixed
     */
    public function deleteCKFinderFiles () {

        // If CKFinder upload dir (special dir for entity/model,
        // that current row instance represents) does not exist - return
        if (($dir = Indi::model($this->id)->dir('exists', true)) === false) return;

        // Delete recursively all the contents - folder and files
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        // Remove the directory itself
        rmdir($dir);
    }

    /**
     * Create/rename database table, refresh/remove cache file, rename upload folder if need
     *
     * @return int
     */
    public function save() {

        // Run checks
        $this->mflush(true);

        // If this is a new entity
        if (!$this->id) {

            // Run the CREATE TABLE sql query
            Indi::db()->query('CREATE TABLE IF NOT EXISTS `' . $this->table . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM');

        // Else if it is an existing entity, but it's database table name is to be modified
        } else if ($this->_modified['table']) {

            // Run the RENAME TABLE sql query
            Indi::db()->query('RENAME TABLE  `' . $this->_original['table'] . '` TO  `' . $this->_modified['table'] . '`');

            // Get the original ordinary upload directory name
            $old = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $this->_original['table'] . '/';

            // If directory exists - rename it
            if (is_dir($old)) {

                // Get the new directory name
                $new = DOC . STD . '/' . Indi::ini()->upload->path . '/' . $this->_modified['table'] . '/';

                // Do rename
                rename($old, $new);
            }

            // Get the original CKFinder upload directory name
            $old = Indi::model($this->id)->dir('name', true);

            // If directory exists - rename it
            if (is_dir($old)) {

                // Get the new directory name
                $new = DOC . STD . '/' . Indi::ini()->upload->path . '/' . Indi::ini()->ckeditor->uploadPath
                    . '/' . $this->_modified['table'] . '/';

                // Do rename
                rename($old, $new);
            }
        }

        // Backup modified data, because it will be emptied after call parent::save()
        $modified = $this->_modified; $original = $this->_original;

        // Standard save
        $return = parent::save();

        // Reload the model, that current entity row is representing
        if (count($modified)) {

            // If it was an existing entity - do a full model reload, else
            if ($original['id']) $model = Indi::model($this->id)->reload(); else {

                // Append new model metadata into the Indi_Db's registry
                Indi::db((int) $this->id);

                // Load that new model
                $model = Indi::model($this->id);
            }
        }

        // If `titleFieldId` property was modified
        if (array_key_exists('titleFieldId', $modified)) {

            // If, after modification, value `titleFieldId` property is pointing to a valid field
            if ($titleFieldR = $model->titleField()) {

                // If that field is a foreign key
                if ($titleFieldR->storeRelationAbility != 'none') {

                    // If current entity has no `title` field
                    if (!Indi::model($this->id)->fields('title')) {

                        // Create it
                        $fieldR = Indi::model('Field')->createRow();
                        $fieldR->entityId = $this->id;
                        $fieldR->title = 'Auto title';
                        $fieldR->alias = 'title';
                        $fieldR->storeRelationAbility = 'none';
                        $fieldR->columnTypeId = 1;
                        $fieldR->elementId = 1;
                        $fieldR->mode = 'hidden';
                        $fieldR->save();
                    }

                    // Fetch all rows
                    $rs = $model->fetchAll();

                    // Setup foreign data, as it will be need in the process of rows titles updating
                    $rs->foreign($titleFieldR->alias);

                    // Update titles
                    foreach ($rs as $r) $r->titleUpdate($titleFieldR);

                } else $model->fetchAll()->titleUsagesUpdate();
            } else $model->fetchAll()->titleUsagesUpdate();
        }

        // If useCache property was changed
        if (isset($modified['useCache'])) {

            // If it was switched on
            if ($modified['useCache']) {

                // Refresh the cache file
                Indi_Cache::update($this->table);

            // Else if it was switched off
            } else {

                // Remove the cache file
                Indi_Cache::remove($this->table);
            }
        }

        return $return;
    }

    /**
     * Check changes and do things if need
     */
    public function onUpdate() {

        // Reload model
        if (Indi::model($this->id, true)) Indi::model($this->id)->reload();

        // Apply spaceScheme changes, if need
        $this->_spaceScheme();

        // Apply fileGroupBy changes, if need
        $this->_filesGroupBy();
    }

    /**
     * Apply spaceScheme changes, if need
     */
    protected function _spaceScheme() {

        // If neither `spaceScheme` nor `spaceFields` fields were changed - return
        if (!$this->affected('spaceScheme,spaceFields')) return;

        // If `spaceScheme` became non-'none' - create space fields within an entity, that current entry is representing
        if ($this->affected('spaceScheme', true) == 'none') {

            // Get key-value pairs of `element` and `columnType` entries
            $elementIdA = Indi::db()->query('SELECT `alias`, `id` FROM `element`')->fetchAll(PDO::FETCH_KEY_PAIR);
            $columnTypeIdA = Indi::db()->query('SELECT `type`, `id` FROM `columnType`')->fetchAll(PDO::FETCH_KEY_PAIR);

            // Prepare fields configs
            $fieldA = array(
                'space' => array(
                    'title' => 'Расписание',
                    'elementId' => $elementIdA['span'],
                    'mode' => 'hidden'
                ),
                'spaceSince' => array(
                    'title' => 'Начало',
                    'elementId' => $elementIdA['datetime'],
                    'columnTypeId' => $columnTypeIdA['DATETIME'],
                    'mode' => 'hidden'
                ),
                'spaceUntil' => array(
                    'title' => 'Конец',
                    'elementId' => $elementIdA['datetime'],
                    'columnTypeId' => $columnTypeIdA['DATETIME'],
                    'mode' => 'hidden'
                ),
                'spaceFrame' => array(
                    'title' => 'Длительность',
                    'elementId' => $elementIdA['number'],
                    'columnTypeId' => $columnTypeIdA['INT(11)'],
                    'mode' => 'hidden'
                )
            );

            // Foreach fields configs
            foreach ($fieldA as $alias => $fieldI) {

                // If field already exists - skip
                if (Indi::model($this->id)->fields($alias)) continue;

                // Create field
                $fieldRA[$alias] = Indi::model('Field')->createRow();
                $fieldRA[$alias]->entityId = $this->id;
                $fieldRA[$alias]->alias = $alias;
                $fieldRA[$alias]->assign($fieldI);
                $fieldRA[$alias]->save();
            }

        // Else if `spaceScheme` wa changed to 'none'
        } else if ($this->affected('spaceScheme') && $this->spaceScheme == 'none') {

            // Remove space* fields
            Indi::model($this->id)->fields('space,spaceSince,spaceUntil,spaceFrame')->delete();
        }

        // Get space settings
        $space = Indi::model($this->id)->space();

        // Space's scheme and coords shortcuts
        $scheme = $space['scheme']; $coords = $space['coords'];

        // If space's scheme is 'none' - return
        if ($scheme == 'none') return;

        // [+] date
        // [+] datetime
        // [+] date-time
        // [+] date-timeId
        // [+] date-dayQty
        // [+] datetime-minuteQty
        // [+] date-time-minuteQty
        // [+] date-timeId-minuteQty
        // [+] date-timespan

        // If scheme is 'date'
        if ($scheme == 'date') Indi::db()->query('
            UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " 00:00:00"),
              `spaceUntil` = CONCAT(`:p`, " 00:00:00"),
              `spaceFrame` = 0
        ', $this->table, $coords['date'], $coords['date']);

        // If scheme is 'datetime'
        if ($scheme == 'datetime') Indi::db()->query('
            UPDATE `:p` SET `spaceSince` = `:p`, `spaceUntil` = `:p`, `spaceFrame` = 0
        ', $this->table, $coords['datetime'], $coords['datetime']);

        // If scheme is 'date-time'
        if ($scheme == 'date-time') Indi::db()->query('
            UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " ", `:p`),
              `spaceUntil` = CONCAT(`:p`, " ", `:p`),
              `spaceFrame` = 0
        ', $this->table, $coords['date'], $coords['time'], $coords['date'], $coords['time']);

        // If scheme is 'date-timeId'
        if ($scheme == 'date-timeId') Indi::db()->query('
            UPDATE `:p` `e`, `time` `t` SET
              `e`.`spaceSince` = CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"),
              `e`.`spaceUntil` = CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"),
              `e`.`spaceFrame` = 0
            WHERE `e`.`:p` = `t`.`id`
        ', $this->table, $coords['date'], $coords['date'], $coords['timeId']);

        // If scheme is 'date-dayQty'
        if ($scheme == 'date-dayQty') Indi::db()->query('
             UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " 00:00:00"),
              `spaceUntil` = CONCAT(DATE_ADD(`:p`, INTERVAL `' . $coords['dayQty'] . '` DAY), " 00:00:00"),
              `spaceFrame` = `' . $coords['dayQty'] . '` * ' . _2sec('1d') . '
       ', $this->table, $coords['date'], $coords['date']);

        // If scheme is 'datetime-minuteQty'
        if ($scheme == 'datetime-minuteQty') Indi::db()->query('
            UPDATE `:p` SET
              `spaceSince` = `:p`,
              `spaceUntil` = DATE_ADD(`:p`, INTERVAL `' . $coords['minuteQty'] . '` MINUTE),
              `spaceFrame` = `' . $coords['minuteQty'] . '` * ' . _2sec('1m') . '
        ', $this->table, $coords['datetime'], $coords['datetime']);

        // If scheme is 'date-time-minuteQty'
        if ($scheme == 'date-time-minuteQty') Indi::db()->query('
            UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " ", `:p`),
              `spaceUntil` = DATE_ADD(CONCAT(`:p`, " ", `:p`), INTERVAL `' . $coords['minuteQty'] . '` MINUTE),
              `spaceFrame` = `' . $coords['minuteQty'] . '` * ' . _2sec('1m') . '
        ', $this->table, $coords['date'], $coords['time'], $coords['date'], $coords['time']);

        // If scheme is 'date-timeId-minuteQty'
        if ($scheme == 'date-timeId-minuteQty') Indi::db()->query('
            UPDATE `:p` `e`, `time` `t` SET
              `e`.`spaceSince` = CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"),
              `e`.`spaceUntil` = DATE_ADD(CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"), INTERVAL `' . $coords['minuteQty'] . '` MINUTE),
              `spaceFrame` = `' . $coords['minuteQty'] . '` * ' . _2sec('1m') . '
            WHERE `e`.`:p` = `t`.`id`
        ', $this->table, $coords['date'], $coords['date'], $coords['timeId']);

        // If scheme is 'date-timespan'
        if ($scheme == 'date-timespan') {

            // Update `spaceSince` and `spaceUntil`
            Indi::db()->query('
                UPDATE `' . $this->table . '` SET
                  `spaceSince` = CONCAT(`' . $coords['date'] . '`, " ", SUBSTRING(`' . $coords['timespan'] . '`, 1, 5), ":00"),
                  `spaceUntil` = DATE_ADD(
                    CONCAT(`' . $coords['date'] . '`, " ", SUBSTRING(`' . $coords['timespan'] . '`, -5), ":00"),
                    INTERVAL IF(SUBSTRING(`' . $coords['timespan'] . '`, 1, 5) < SUBSTRING(`' . $coords['timespan'] . '`, -5), 0, 1) DAY
                  )
            ');

            // Update `spaceFrame`
            Indi::db()->query('
                UPDATE `' . $this->table . '`
                SET `spaceFrame` = UNIX_TIMESTAMP(`spaceUntil`) - UNIX_TIMESTAMP(`spaceSince`)
            ');
        }
    }

    /**
     * This method was redefined to provide ability for some entity
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some entity props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'titleFieldId' || $columnName == 'filesGroupBy') $value = field($this->table, $value)->id;
            else if ($columnName == 'spaceFields') {
                if ($value && !Indi::rexm('int11list', $value)) {
                    $fieldIdA = array();
                    foreach(ar($value) as $field) $fieldIdA[] = field($this->id, $field)->id;
                    $value = im($fieldIdA);
                }
            }
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * Build a string, that will be used in Entity_Row->export()
     *
     * @param string $deferred
     * @param bool $invert
     * @param string $certain
     * @return string
     */
    protected function _ctor($deferred = '', $invert = false, $certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id`, as it will be set automatically by MySQL
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('table') as $arg) unset($ctor[$arg]);

        // Exclude props that rely on entity's inner fields as they not exist at the moment of ctor usage
        if (!$invert) foreach (ar($deferred) as $defer) unset($ctor[$defer]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value)

            // Exclude prop, if it has value equal to default value
            if (Indi::model('Entity')->fields($prop)->defaultValue == $value) unset($ctor[$prop]);

            // Exclude prop, if $invert arg is given as `true` and prop is not mentioned in $deferred list
            else if ($invert && !in($prop, $deferred)) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else {
                if ($prop == 'titleFieldId' || $prop == 'filesGroupBy') $value = field($this->table, $value)->alias;
                else if ($prop == 'spaceFields') {
                    $value = ''; if ($sf = $this->foreign('spaceFields')) $value = $sf->column('alias', true);
                }
            }

        // Stringify and return
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current entity in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = null) {

        // If $certain arg is given - export it only
        if ($certain) return "entity('" . $this->table . "', " . $this->_ctor('', false, $certain) . ");";

        // Declare list of `entity` entry's props, that rely on fields,
        // that will be created AFTER `entity` entry's itself creation
        $deferred = 'titleFieldId,filesGroupBy,spaceScheme,spaceFields';

        // Build `entity` entry creation expression
        $lineA[] = "entity('" . $this->table . "', " . $this->_ctor($deferred) . ");";

        // Foreach `field` entry, nested within current `entity` entry
        foreach ($this->nested('field', array('order' => 'move')) as $fieldR)

            // Build `field` entry's creation expression
            $lineA[] = $fieldR->export();

        // Build expression, that will now apply $deferred props,
        // because underlying fields creation expressions are already prepared
        if (($deferred = $this->_ctor($deferred, true)) != 'true')
            $lineA[] = "entity('" . $this->table . "', " . $deferred . ");";

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Apply filesGroupBy changes, if need
     */
    protected function _filesGroupBy() {

        // If `filesGroupBy` prop was not affected - return
        if (!$this->affected('filesGroupBy')) return;

        // If `filesGroupBy` prop was unzeroed
        if ($this->filesGroupBy && !$this->_affected['filesGroupBy']) {

            // Do grouping
            $this->_filesGroupBy_group();

        // Else if `filesGroupBy` prop was zeroed
        } else if (!$this->filesGroupBy && $this->_affected['filesGroupBy']) {

            // Do ungrouping
            $this->_filesGroupBy_ungroup();

        // Else if `filesGroupBy` prop changed from one non-zero value to another non-zero value
        } else {

            // Do ungrouping
            $this->_filesGroupBy_ungroup();

            // Do grouping
            $this->_filesGroupBy_group();
        }
    }

    /**
     * Do grouping
     */
    protected function _filesGroupBy_group() {

        // Shortcut to model
        $m = m($this->id);

        // Get group field alias
        $group = $m->fields($this->filesGroupBy)->alias;

        // Get id => group array
        $groupByIdA = Indi::db()->query('SELECT `id`, `' . $group . '` FROM `' . $m->table() . '`')->fetchAll(PDO::FETCH_KEY_PAIR);

        // Model dir
        $dir = $m->dir();

        // Created group-dirs
        $mkdirA = array();

        // Foreach file
        foreach (glob($dir . '*.*', GLOB_NOSORT) as $abs) {

            // Get file base name
            $bn = pathinfo($abs, PATHINFO_BASENAME);

            // Get id
            $id = array_shift(explode('_', $bn));

            // If it's not possible to detect group for a file - skip
            if (!isset($groupByIdA[$id])) continue;

            // Get group
            $group = $groupByIdA[$id];

            // Create group-dir, if not exists
            if (!$mkdirA[$group] && !is_dir($mkdirI = $dir . $group . '/'))
                if (mkdir($mkdirI))
                    $mkdirA[$group] = $mkdirI;

            // Move file to group-dir
            rename($abs, $mkdirA[$group] . $bn);
        }
    }

    /**
     * Do ungroup
     */
    protected function _filesGroupBy_ungroup() {

        // Shortcut to model
        $m = m($this->id);

        // Model dir
        $dir = $m->dir();

        // Foreach group-dir
        foreach (glob($dir . '*', GLOB_ONLYDIR) as $group) {

            // Foreach file within group-gir
            foreach (glob($group . '/*.*') as $abs) {

                // Get file base name
                $bn = pathinfo($abs, PATHINFO_BASENAME);

                // Move file to upper dir
                rename($abs, $dir . $bn);
            }

            // Remove group-dir
            rmdir($group);
        }
    }
}