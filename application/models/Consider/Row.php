<?php
class Consider_Row extends Indi_Db_Table_Row {

    /**
     * Small hack, for spoofing name of column to be involved in sql query
     *
     * @param $where
     * @param Field_Row $field
     * @param Field_Row $cField
     * @param $cValue
     * @return mixed|void
     */
    protected function _comboDataConsiderWHERE(&$where, Field_Row $field, Field_Row $cField, $cValue) {

        // Spoof column name
        if ($cField->alias == 'relation') $cField->alias = 'entityId';

        // Call parent
        return $this->callParent();
    }

    /**
     * This method was redefined to provide ability for some `consider` entry's
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some `consider` entry's props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'entityId') $value = entity($value)->id;
            else if (in($columnName, 'fieldId,consider')) $value = field($this->entityId, $value)->id;
            else if ($columnName == 'foreign') $value = field($this->foreign('consider')->relation, $value)->id;
            else if ($columnName == 'connector')
                $value = $value == 'id' ? -1 : field($this->foreign('fieldId')->relation, $value)->id;
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * @inheritdocs
     */
    public function onBeforeSave() {

        // Set `entityId`
        $this->entityId = $this->foreign('fieldId')->entityId;

        // If dependent field's `relation` prop is not zero - return
        if (!$this->foreign('fieldId')->zero('relation')) return;

        // Else:
        // 1. Set `required` to be 'y',
        $this->required = 'y';

        // 2. Assign zero-values to `foreign` and `connector` props, as they're applicable
        // only in cases when dependent field's `relation` prop is not zero
        $this->zero('connector', true);
    }

    /**
     * Provide ability to use `id` column as connector
     *
     * @param $field
     * @return Indi_Db_Table_Rowset|mixed
     */
    public function getComboData($field) {

        // Call parent
        $dataRs = $this->callParent();

        // Prepend data rowset with 'ID' option
        if ($field == 'connector') $dataRs->append(array('id' => -1, 'title' => 'ID', 'alias' => 'id'), 'title');

        // Return
        return $dataRs;
    }

    /**
     * Build a string, that will be used in Consider_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `title` as those will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id']); if (!in('title', $certain)) unset($ctor['title']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('entityId,fieldId,consider') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Replace ids with aliases for `foreign` and `connector` fields
        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if (in($prop, 'foreign,connector')) $value = $this->foreign($prop)->alias;
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating the current `consider` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `field` entry creation line
        $lineA[] = "consider('"
            . $this->foreign('entityId')->table . "', '"
            . $this->foreign('fieldId')->alias  . "', '"
            . $this->foreign('consider')->alias . "', "
            . $this->_ctor($certain) . ");";

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Setter for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }
}