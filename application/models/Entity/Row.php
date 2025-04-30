<?php
class Entity_Row extends Indi_Db_Table_Row {

    /**
     * @return array|void
     */
    public function validate() {

        // Check
        $this->vcheck([
            'title' => [
                'req' => true
            ],
            'table' => [
                'req' => true,
                'rex' => '/^[a-zA-Z0-9]+$/',
                'unq' => true
            ]
        ]);

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
        if (db()->query('SHOW TABLES LIKE "' . $this->table . '"')->cell()) {
            db()->query('SET `foreign_key_checks` = 0');
            db()->query('DROP TABLE `' . $this->table . '`');
            db()->query('SET `foreign_key_checks` = 1');
        }

        // Destroy model
        m($this->table, 'destroy');

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
        $dir = DOC . STD . '/' . ini()->upload->path . '/' . $this->table . '/';

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
        if (($dir = m($this->id)->dir('exists', true)) === false) return;

        // Delete recursively all the contents - folder and files
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        // Remove the directory itself
        rmdir($dir);
    }

    /**
     * Run CREATE TABLE query
     */
    public function onInsert() {

        // Run the CREATE TABLE sql query
        db()->query("CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB");

        // Append new model metadata into the Indi_Db's registry
        db((int) $this->id);

        // Load that new model
        m($this->id);
    }

    /**
     * Apply table name change, if any
     */
    protected function _rename() {

        // If db table name was not changed - do nothing
        if (!isset($this->_affected['table'])) return; else $was = $this->_affected['table']; $now = $this->table;

        // Run the RENAME TABLE sql query
        db()->query("RENAME TABLE  `$was` TO  `$now`");

        // Get the templates for ordinary and CKFinder upload directory names
        $tplA = [
            DOC . STD . '/' . ini()->upload->path . '/%s/',
            DOC . STD . '/' . ini()->upload->path . '/' . ini()->ckeditor->uploadPath . '/%s/'
        ];

        // Rename directories
        foreach ($tplA as $tpl)
            if (is_dir($old = sprintf($tpl, $was)))
                rename($old, sprintf($tpl, $now));
    }

    /**
     * Clear spaceFields if spaceScheme is now 'none'
     */
    public function onBeforeSave() {

        // If `spaceScheme` became non-'none' - clear spaceFields-prop
        if ($this->modified('spaceScheme') === 'none') $this->zero('spaceFields', true);
    }

    /**
     * Check changes and do things if need
     */
    public function onUpdate() {

        // Apply table name change, if any
        $this->_rename();

        // Reload model
        if (m($this->id, true)) m($this->id)->reload();

        // Apply spaceScheme changes, if any
        $this->_spaceScheme();

        // Apply fileGroupBy changes, if any
        $this->_filesGroupBy();

        // If monthFieldId was changed
        if ($this->affected('monthFieldId')) {

            // Call monthFieldId() method in a detached (background) process
            $this->detachProcess('monthFieldId');
        }

        // Reload the model, that current entity row is representing
        if (count($this->_affected)) {

            // If it was an existing entity - do a full model reload, else
            $model = m($this->id)->reload();

            // If `titleFieldId` property was modified
            if (array_key_exists('titleFieldId', $this->_affected)) {

                // If, after modification, value of `titleFieldId` property is pointing to a valid field
                if ($titleFieldR = $model->titleField()) {

                    // If that field is a foreign key
                    if ($titleFieldR->storeRelationAbility != 'none') {

                        // If current entity has no `title` field
                        if (!m($this->id)->fields('title')) {

                            // Create it
                            $fieldR = m('Field')->new();
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
                        $rs = $model->all();

                        // Setup foreign data, as it will be need in the process of rows titles updating
                        $rs->foreign($titleFieldR->alias);

                        // Update titles
                        foreach ($rs as $r) $r->titleUpdate($titleFieldR);

                    } else $model->all()->titleUsagesUpdate();
                } else $model->all()->titleUsagesUpdate();
            }
        }
    }

    /**
     * Apply spaceScheme changes, if any
     */
    protected function _spaceScheme() {

        // If neither `spaceScheme` nor `spaceFields` fields were changed - return
        if (!$this->affected('spaceScheme,spaceFields')) return;

        // If `spaceScheme` became non-'none' - create space fields within an entity, that current entry is representing
        if ($this->affected('spaceScheme', true) == 'none') {

            // Get key-value pairs of `element` and `columnType` entries
            $elementIdA = db()->query('SELECT `alias`, `id` FROM `element`')->pairs();
            $columnTypeIdA = db()->query('SELECT `type`, `id` FROM `columnType`')->pairs();

            // Prepare fields configs
            $fieldA = [
                'space' => [
                    'title' => 'Расписание',
                    'elementId' => $elementIdA['span'],
                    'mode' => 'hidden'
                ],
                'spaceSince' => [
                    'title' => 'Начало',
                    'elementId' => $elementIdA['datetime'],
                    'columnTypeId' => $columnTypeIdA['DATETIME'],
                    'mode' => 'hidden'
                ],
                'spaceUntil' => [
                    'title' => 'Конец',
                    'elementId' => $elementIdA['datetime'],
                    'columnTypeId' => $columnTypeIdA['DATETIME'],
                    'mode' => 'hidden'
                ],
                'spaceFrame' => [
                    'title' => 'Длительность',
                    'elementId' => $elementIdA['number'],
                    'columnTypeId' => $columnTypeIdA['INT(11)'],
                    'mode' => 'hidden'
                ]
            ];

            // Foreach fields configs
            foreach ($fieldA as $alias => $fieldI) {

                // If field already exists - skip
                if (m($this->id)->fields($alias)) continue;

                // Create field
                $fieldRA[$alias] = m('Field')->new();
                $fieldRA[$alias]->entityId = $this->id;
                $fieldRA[$alias]->alias = $alias;
                $fieldRA[$alias]->set($fieldI);
                $fieldRA[$alias]->save();
            }

        // Else if `spaceScheme` wa changed to 'none'
        } else if ($this->affected('spaceScheme') && $this->spaceScheme == 'none') {

            // Remove space* fields
            m($this->id)->fields('space,spaceSince,spaceUntil,spaceFrame')->delete();
        }

        // Get space settings
        $space = m($this->id)->space();

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
        if ($scheme == 'date') db()->query('
            UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " 00:00:00"),
              `spaceUntil` = CONCAT(`:p`, " 00:00:00"),
              `spaceFrame` = 0
        ', $this->table, $coords['date'], $coords['date']);

        // If scheme is 'datetime'
        if ($scheme == 'datetime') db()->query('
            UPDATE `:p` SET `spaceSince` = `:p`, `spaceUntil` = `:p`, `spaceFrame` = 0
        ', $this->table, $coords['datetime'], $coords['datetime']);

        // If scheme is 'date-time'
        if ($scheme == 'date-time') db()->query('
            UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " ", `:p`),
              `spaceUntil` = CONCAT(`:p`, " ", `:p`),
              `spaceFrame` = 0
        ', $this->table, $coords['date'], $coords['time'], $coords['date'], $coords['time']);

        // If scheme is 'date-timeId'
        if ($scheme == 'date-timeId') db()->query('
            UPDATE `:p` `e`, `time` `t` SET
              `e`.`spaceSince` = CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"),
              `e`.`spaceUntil` = CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"),
              `e`.`spaceFrame` = 0
            WHERE `e`.`:p` = `t`.`id`
        ', $this->table, $coords['date'], $coords['date'], $coords['timeId']);

        // If scheme is 'date-dayQty'
        if ($scheme == 'date-dayQty') db()->query('
             UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " 00:00:00"),
              `spaceUntil` = CONCAT(DATE_ADD(`:p`, INTERVAL `' . $coords['dayQty'] . '` DAY), " 00:00:00"),
              `spaceFrame` = `' . $coords['dayQty'] . '` * ' . _2sec('1d') . '
       ', $this->table, $coords['date'], $coords['date']);

        // If scheme is 'datetime-minuteQty'
        if ($scheme == 'datetime-minuteQty') db()->query('
            UPDATE `:p` SET
              `spaceSince` = `:p`,
              `spaceUntil` = DATE_ADD(`:p`, INTERVAL `' . $coords['minuteQty'] . '` MINUTE),
              `spaceFrame` = `' . $coords['minuteQty'] . '` * ' . _2sec('1m') . '
        ', $this->table, $coords['datetime'], $coords['datetime']);

        // If scheme is 'date-time-minuteQty'
        if ($scheme == 'date-time-minuteQty') db()->query('
            UPDATE `:p` SET
              `spaceSince` = CONCAT(`:p`, " ", `:p`),
              `spaceUntil` = DATE_ADD(CONCAT(`:p`, " ", `:p`), INTERVAL `' . $coords['minuteQty'] . '` MINUTE),
              `spaceFrame` = `' . $coords['minuteQty'] . '` * ' . _2sec('1m') . '
        ', $this->table, $coords['date'], $coords['time'], $coords['date'], $coords['time']);

        // If scheme is 'date-timeId-minuteQty'
        if ($scheme == 'date-timeId-minuteQty') db()->query('
            UPDATE `:p` `e`, `time` `t` SET
              `e`.`spaceSince` = CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"),
              `e`.`spaceUntil` = DATE_ADD(CONCAT(`e`.`:p`, " ", `t`.`title`, ":00"), INTERVAL `' . $coords['minuteQty'] . '` MINUTE),
              `spaceFrame` = `' . $coords['minuteQty'] . '` * ' . _2sec('1m') . '
            WHERE `e`.`:p` = `t`.`id`
        ', $this->table, $coords['date'], $coords['date'], $coords['timeId']);

        // If scheme is 'date-timespan'
        if ($scheme == 'date-timespan') {

            // Update `spaceSince` and `spaceUntil`
            db()->query('
                UPDATE `' . $this->table . '` SET
                  `spaceSince` = CONCAT(`' . $coords['date'] . '`, " ", SUBSTRING(`' . $coords['timespan'] . '`, 1, 5), ":00"),
                  `spaceUntil` = DATE_ADD(
                    CONCAT(`' . $coords['date'] . '`, " ", SUBSTRING(`' . $coords['timespan'] . '`, -5), ":00"),
                    INTERVAL IF(SUBSTRING(`' . $coords['timespan'] . '`, 1, 5) < SUBSTRING(`' . $coords['timespan'] . '`, -5), 0, 1) DAY
                  )
            ');

            // Update `spaceFrame`
            db()->query('
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

            //
            if (in($columnName, 'titleFieldId,filesGroupBy,monthFieldId,spaceFields,changeLogExcept')) {
                if (!field($this->table, $value)) {
                    return;
                }
            }

            if (in($columnName, 'titleFieldId,filesGroupBy,monthFieldId')) $value = field($this->table, $value)->id;
            else if (in($columnName,'spaceFields,changeLogExcept')) {
                if ($value && !Indi::rexm('int11list', $value)) {
                    $fieldIdA = [];
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

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value)

            // Exclude prop, if it has value equal to default value
            if (m('entity')->fields($prop)->defaultValue == $value && !in($prop, $certain)) {
                if (!isset($GLOBALS['export'])){
                    unset($ctor[$prop]);
                }
            }

            // Exclude prop, if $invert arg is given as `true` and prop is not mentioned in $deferred list
            else if ($invert && !in($prop, $deferred)) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else {

                // If it's titleFieldId or filesGroupBy
                if (in($prop,'titleFieldId,filesGroupBy,monthFieldId'))
                    $value = field($this->table, $value)->alias;

                // Else if it's spaceFields or changeLogExcept
                else if (in($prop,'spaceFields,changeLogExcept'))
                    $value = ($sf = $this->foreign($prop)) ? $sf->column('alias', true) : '';
            }

        // Stringify and return
        return $this->_var_export($ctor);
    }

    /**
     * Build an expression for creating the current entity in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // If $certain arg is given - export it only
        if ($certain) return "entity('" . $this->table . "', " . $this->_ctor('', false, $certain) . ");";


        // Build `entity` entry creation expression
        if (isset($GLOBALS['export'])) {
            $lineA[] = "\n# '$this->title'-entity";
            $deferred = '';
        } else {
            // Declare list of `entity` entry's props, that rely on fields,
            // that will be created AFTER `entity` entry's itself creation
            $deferred = 'titleFieldId,filesGroupBy,spaceScheme,spaceFields';
        }
        $lineA[] = "entity('" . $this->table . "', " . $this->_ctor($deferred) . ");";

        // Foreach `field` entry, nested within current `entity` entry
        foreach ($this->nested('field', ['order' => 'move']) as $fieldR)

            // Build `field` entry's creation expression
            $lineA[] = $fieldR->export();

        // Build expression, that will now apply $deferred props,
        // because underlying fields creation expressions are already prepared
        if (!isset($GLOBALS['export'])){
            if (($deferred = $this->_ctor($deferred, true)) != 'true')
                $lineA[] = "entity('" . $this->table . "', " . $deferred . ");";
        }


        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Apply filesGroupBy changes, if any
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
        $groupByIdA = db()->query('SELECT `id`, `' . $group . '` FROM `' . $m->table() . '`')->pairs();

        // Model dir
        $dir = $m->dir();

        // Created group-dirs
        $mkdirA = [];

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

    /**
     * Check if monthFieldId was set up, and if yes:
     *  - add 'Month'-field to that entity's data-structure
     *  - setup values for that field for already existing entries of that entity
     *  - new records in `month` table are created if necessary
     *  - add 'Month'-filter to all level-1 sections having this entity as data-source
     */
    public function monthFieldId() {

        // If monthFieldId was changed from 0 to non-0
        if ($this->fieldWasUnzeroed('monthFieldId')) {

            // Print status
            //msg("Creating 'Month'-field...");

            // Create monthId-field within current entity data-structure
            field($this->table, 'monthId', [
                'title' => 'Month',
                'mode' => 'readonly',
                'storeRelationAbility' => 'one',
                'relation' => 'month',
                'onDelete' => 'RESTRICT',
                'elementId' => 'combo',
                'columnTypeId' => 'INT(11)',
                'defaultValue' => '0',
                'move' => '',
            ]);
            param($this->table, 'monthId', 'groupBy', 'yearId');
            param($this->table, 'monthId', 'optionTemplate', '<?=$o->foreign(\'month\')->title?>');

            // Print status
            //msg("Creating filters for 'Month'-field...");

            // Get comma-separated ids of sections used as groups of left menu items
            $level0 = db()->query('
                SELECT `id`
                FROM `section`
                WHERE IFNULL(`sectionId`, 0) = "0" AND `toggle` = "y"
                ORDER BY `move`
            ')->in();

            // Get ids of sections used as left menu items
            $level1 = db()->query("
                SELECT `id`
                FROM `section`
                WHERE `entityId` = '$this->id'
                  AND `sectionId` IN ($level0)
                  AND `toggle` = 'y'
                ORDER BY `move`
            ")->col();

            // For each section - append filter for monthId-field
            foreach ($level1 as $sectionId) filter($sectionId, 'monthId', ['move' => '']);

            // Get alias of date field to calculate values for monthId-field
            $source = $this->foreign('monthFieldId')->alias;

            // Run batch, which might invoke Google Cloud Translate API call
            try {

                // Re-setup value of monthId-field for existing entries
                $qty = m($this->table)->batch(

                    // Do setup
                    fn($r) => $r->deriveMonthId($source)->basicUpdate(),

                    // Batch settings
                    null, '`id` ASC', 500, false,

                    // Progress title
                    "Setting up values for 'Month'-field"
                );

            // Catch exception
            } catch (Exception $e) {

                // Get error
                $error = $e->getMessage();
                if ($json = json_decode($error))
                    $error = $json->error->message;

                //
                msg($error, false);

                // Log error
                ehandler(1, $error, __FILE__, __LINE__);

                // Exit
                exit;
            }

            // Indicate done
            progress(true);

        // Else if monthFieldId was changed to 0
        } else if ($this->fieldWasZeroed('monthFieldId')) {

            // Print where we are
            msg("Deleting 'Month'-field...");

            // Delete monthId-field from current entity structure
            if ($monthId = field($this->table, 'monthId')) $monthId->delete();

            // Print where we are
            msg("Done");

        // Else if source field was changed from one to another
        } else if ($this->affected('monthFieldId')) {

            // Get alias of date field to calculate values for monthId-field
            $source = $this->foreign('monthFieldId')->alias;

            // Re-setup value of monthId-field for existing entries
            m($this->table)->batch(

                // Do re-setup
                fn($r) => $r->deriveMonthId($source)->basicUpdate(),

                // Batch settings
                null, '`id` ASC', 500, false,

                // Progress title
                "Setting up values for 'Month'-field"
            );

            // Indicate done
            progress(true);
        }
    }
}