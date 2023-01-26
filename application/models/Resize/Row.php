<?php
class Resize_Row extends Indi_Db_Table_Row {

    /**
     * Delete all of resized copies and clear usages from `section`.`tileThumb`
     */
    public function onDelete() {

		// Delete copies of images, that have copy-name equal to $this->alias
		$this->deleteCopies();
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
     * Create resized copies according to this newly created `resize` entry
     */
    public function onInsert() {

        // Todo: make it to run in a queue
        set_time_limit(0); ignore_user_abort(1);

        // Get db table name, and field alias
        $table = $this->foreign('fieldId')->foreign('entityId')->table;
        $field = $this->foreign('fieldId')->alias;

        // Get all rows within entity, that current row's field is in structure of
        $rs = m($table)->all();

        // Create a new resized copy of an image, uploaded using $field, for all rows
        foreach ($rs as $r) $r->resize($field, $this);
    }

    /**
     * Do all required operations of creation/altering copies of images,
     * accordingly to related settings, stored in current `resize` entry
     */
    public function onUpdate() {

        // Todo: make it to run in a queue
        set_time_limit(0); ignore_user_abort(1);

        // Get db table name, and field alias
        $table = $this->foreign('fieldId')->foreign('entityId')->table;
        $field = $this->foreign('fieldId')->alias;

        // If no props were affected - return
        if (!$this->_affected) return;

        // If `alias` property is the only modified - we just rename copies
        if (count($this->_affected) == 1 && isset($this->_affected['alias'])) {

            // Get the directory name
            $dir = DOC . STD . '/' . ini()->upload->path . '/' . $table . '/';

            // If directory does not exist - return
            if (!is_dir($dir)) return;

            // Get the array of images certain copies
            $copyA = glob($dir . '*_' . $field . ',' . $this->_affected['alias'] . '.{gif,jpeg,jpg,png}', GLOB_BRACE);

            // Foreach copy
            foreach ($copyA as $copyI) {

                // Get the new filename, by replacing original copy alias with modified copy alias
                $renameTo = preg_replace(
                    '~(/[0-9]+_' . $field . ',)' . $this->_affected['alias'] . '\.(gif|jpe?g|png)$~',
                    '$1' . $this->alias . '.$2', $copyI
                );

                // Rename
                rename($copyI, $renameTo);
            }

        // Else there were more changes
        } else {

            // If these changes include change of 'alias'
            if (isset($this->_affected['alias'])) {

                // Get the directory name
                $dir = DOC . STD . '/' . ini()->upload->path . '/' . $table . '/';

                // If directory does not exist - return
                if (!is_dir($dir)) return;

                // Get the array of images certain copies
                $copyA = glob($dir . '*_' . $field . ',' . $this->_affected['alias'] . '.{gif,jpeg,jpg,png}', GLOB_BRACE);

                // Unlink original-named copies
                foreach ($copyA as $copyI) @unlink($copyI);
            }

            // Get all rows within entity, that current row's field is in structure of
            $rs = m($table)->all();

            // Create a new resized copies
            foreach ($rs as $r) $r->resize($field, $this);
        }
    }
    /**
     * Build an expression for creating/getting the current `resize` entry in another project, running on Indi Engine
     *
     * Usage:
     * $resize->export(): "resize('tablename', 'fieldname', 'resizename', ['resizeprop1' => 'value1', 'resizeprop2' => 'value2', ...]);"
     * $resize->export('resizeprop1'): "resize('tablename', 'fieldname', 'resizename', ['resizeprop1' => 'value1']);"
     * $resize->export(false): "resize('tablename', 'fieldname', 'resizename')"
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Shortcuts
        $fieldR = $this->foreign('fieldId');
        $table = $fieldR->foreign('entityId')->table;
        $entry = $fieldR->foreign('entry')->alias ?: $fieldR->entry;

        // Build and return `resize` entry creation line
        return $fieldR->entry
            ? "cfgResize('" . $table . "', '" . $entry . "', '" . $fieldR->alias . "', '" . $this->alias . "'" . rif($certain !== false, ", " . $this->_ctor($certain) . ");", ')')
            : "resize('" . $table . "', '" . $fieldR->alias . "', '" . $this->alias . "'" . rif($certain !== false, ", " . $this->_ctor($certain) . ");", ')');
    }

    /**
     * Build a string, that will be used in Resize_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = null) {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL
        unset($ctor['id']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('fieldId,alias') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = m('Resize')->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }
}