<?php
class Param_Row extends Indi_Db_Table_Row_Noeval {

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
        foreach (ar('fieldId,possibleParamId,cfgField') as $arg) unset($ctor[$arg]);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Stringify
        return _var_export($ctor);
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
            $this->foreign('fieldId')->foreign('entityId')->table . "', '" .
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
        if (preg_match('/^{"[a-z_A-Z]{2,5}":/', $data['cfgValue']))
            if ($this->_language['cfgValue'] = json_decode($data['cfgValue'], true))
                $data['cfgValue'] = $this->_language['cfgValue'][Indi::ini('lang')->admin];

        // Return data
        return $data;
    }
}