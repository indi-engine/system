<?php
class Param_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * This method was redefined to provide ability for some
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Do that  for cfgValue-prop
        if ($columnName === 'cfgValue') {
            if ($value && !Indi::rexm('int11list', $value))
                if (m('field')->row($this->cfgField)->relation === m('field')->id())
                    if ($field = m('field')->row($this->fieldId))
                        $value = m($field->relation)->fields($value, 'rowset')->fis();
        }

        // Call parent
        parent::__set($columnName, $value);
    }

    /**
     * Build a string, that will be used in Param_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `title` as those will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id'], $ctor['title']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('entityId,fieldId,possibleParamId,cfgField') as $arg) unset($ctor[$arg]);

        // Get fcgField
        $cfgField = $this->foreign('cfgField');

        // If it's a foreign-key value, that is referencing to some field(s)
        if ($cfgField->relation === m('field')->id()) {
            $value = m('field')->all($this->cfgValue)->fis('alias');

        // Else
        } else {
            $value = $this->cfgValue;
        }

        // Return exported
        return var_export($value, true);
    }

    /**
     * Build an expression for creating the current `param` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Return
        return "param('" .
            $this->foreign('entityId')->table . "', '" .
            $this->foreign('fieldId')->alias . "', '" .
            $this->foreign('cfgField')->alias . "', " . $this->_ctor($certain) . ");";
    }

    /**
     * Setter method for `title` prop
     */
    public function setTitle() {
        $this->_setTitle();
    }

    /**
     * Here we override parent's l10n() method, as param-model has it's special way of handling translations for 'cfgValue' field
     *
     * @param $data
     * @return array
     */
    public function l10n($data) {

        // Call parent
        $data = $this->callParent();

        // Pick localized value of `cfgValue` prop, if detected that raw value contain localized values
        if (isset($data['cfgValue']))
            if (preg_match('/^{"[a-z_A-Z]{2,5}":/', $data['cfgValue']))
                if ($this->_language['cfgValue'] = json_decode($data['cfgValue'], true))
                    $data['cfgValue'] = $this->_language['cfgValue'][ini('lang')->admin];

        // Return data
        return $data;
    }

    /**
     * Pick entityId from fieldId
     *
     * @throws Exception
     */
    public function onBeforeSave() {
        if ($this->fieldId) $this->entityId = $this->foreign('fieldId')->entityId;
    }

    /**
     * Overridden to handle case when current param is a value of global-level config-field,
     * so in that case we assume such a field to belong to 'system' fraction
     *
     * @return string
     */
    public function fraction() {
        return $this->fieldId ? parent::fraction() : 'system';
    }

    /**
     * Overridden to make sure cfgField-field is always present in $fields arg
     *
     * @param $fields
     * @return mixed
     */
    public function toGridData($fields, $renderCfg = []) {

        // Make sure 'cfgField' is always present, as cfgValue rely on that
        if (!in('cfgField', $fields)) $fields []= 'cfgField';

        // Render grid data
        $data = $this->model()->createRowset(['rows' => [$this]])->toGridData($fields, $renderCfg);

        // Return
        return array_shift($data);
    }
}