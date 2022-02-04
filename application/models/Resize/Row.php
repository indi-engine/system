<?php
class Resize_Row extends Indi_Db_Table_Row {

    /**
     * Standard deletion, prepended with deletion of all resized copies
     *
     * @return int Number of deleted rows (1|0)
     */
    public function delete() {

		// Delete copies of images, that have copy-name equal to $this->alias
		$this->deleteCopies();

		// Standard Db_Table_Row deletion
		return parent::delete();
	}

    /**
     * Delete all images copies, that were created in respect to current row
     */
    public function deleteCopies() {

        // Build the target directory
        $dir = DOC . STD . '/' . ini()->upload->path . '/' . $this->foreign('fieldId')->foreign('entityId')->table . '/';

        // Get all copies ( of all images, that were uploaded using current field), created in respect of current row
        $fileA = glob($dir . '*_' . $this->foreign('fieldId')->alias . ','  . $this->alias . '.*');

        // Delete them
        foreach ($fileA as $fileI) @unlink($fileI);
	}

    /**
     * Do all required operations of creation/altering copies of images, accordingly to related settings, stored in
     * current row
     *
     * @return int
     */
    public function save() {

        set_time_limit(0); ignore_user_abort(1);

        // Get db table name, and field alias
        $table = $this->foreign('fieldId')->foreign('entityId')->table;
        $field = $this->foreign('fieldId')->alias;

        // If this is a new row
        if (!$this->id) {

            // Get all rows within entity, that current row's field is in structure of
            $rs = m($table)->all();

            // Create a new resized copy of an image, uploaded using $field, for all rows
            foreach ($rs as $r) $r->resize($field, $this);

        // Else
        } else if (count($this->_modified)) {

            // If `alias` property is the only modified - we just rename copies
            if (count($this->_modified) == 1 && isset($this->_modified['alias'])) {

                // Get the directory name
                $dir = DOC . STD . '/' . ini()->upload->path . '/' . $table . '/';

                // If directory does not exist - return
                if (!is_dir($dir)) return;

                // Get the array of images certain copies
                $copyA = glob($dir . '*_' . $field . ',' . $this->_original['alias'] . '.{gif,jpeg,jpg,png}', GLOB_BRACE);

                // Foreach copy
                foreach ($copyA as $copyI) {

                    // Get the new filename, by replacing original copy alias with modified copy alias
                    $renameTo = preg_replace(
                        '~(/[0-9]+_' . $field . ',)' . $this->_original['alias'] . '\.(gif|jpe?g|png)$~',
                        '$1' . $this->_modified['alias'] . '.$2', $copyI
                    );

                    // Rename
                    rename($copyI, $renameTo);
                }

            // Else there were more changes
            } else {

                // If these changes include change of 'alias'
                if (isset($this->_modified['alias'])) {

                    // Get the directory name
                    $dir = DOC . STD . '/' . ini()->upload->path . '/' . $table . '/';

                    // If directory does not exist - return
                    if (!is_dir($dir)) return;

                    // Get the array of images certain copies
                    $copyA = glob($dir . '*_' . $field . ',' . $this->_original['alias'] . '.{gif,jpeg,jpg,png}', GLOB_BRACE);

                    // Unlink original-named copies
                    foreach ($copyA as $copyI) @unlink($copyI);
                }

                // Get all rows within entity, that current row's field is in structure of
                $rs = m($table)->all();

                // Create a new resized copies
                foreach ($rs as $r) $r->resize($field, $this);
            }
        }

        // Standard save
        return parent::save();
    }
}