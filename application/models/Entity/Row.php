<?php
class Entity_Row extends Indi_Db_Table_Row {

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
     *
     */
    public function onUpdate() {

        // If neither `spaceUsage` nor `spaceFieldIds` fields were changed - return
        if (!$this->affected('spaceUsing,spaceFieldIds')) return;

        // If `spaceUsing` became non-'none' - create space fields within an entity, that current entry is representing
        if ($this->affected('spaceUsing', true) == 'none') {

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

            // Create fields
            foreach ($fieldA as $alias => $fieldI) {
                $fieldRA[$alias] = Indi::model('Field')->createRow();
                $fieldRA[$alias]->entityId = $this->id;
                $fieldRA[$alias]->alias = $alias;
                $fieldRA[$alias]->assign($fieldI);
                $fieldRA[$alias]->save();
            }

            // If `spaceUsing` is 'date'
            if ($this->spaceUsing == 'date') {

                // Get date field's alias
                $date = $this->foreign('spaceFieldIds')->select($columnTypeIdA['DATE'])->alias;

                // Run SQL-query
                Indi::db()->query('
                    UPDATE `' . $this->table . '` SET
                      `spaceSince` = CONCAT(`'. $date . '`, " 00:00:00"),
                      `spaceUntil` = CONCAT(`'. $date . '`, " 00:00:00"),
                      `spaceFrame` = 0
                ');
            }

        // Else if `spaceUsing` wa changed to 'none'
        } else if ($this->affected('spaceUsing') && $this->spaceUsing == 'none') {

            // Remove space* fields
            Indi::model($this->id)->fields('space,spaceSince,spaceUntil,spaceFrame')->delete();
        }
    }
}