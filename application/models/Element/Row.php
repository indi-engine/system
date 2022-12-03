<?php
class Element_Row extends Indi_Db_Table_Row {

    /**
     * Build an expression for creating the current `element` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `element` entry creation expression
        $lineA[] = "element('" . $this->alias . "', " . $this->_ctor($certain) . ");";

        // If $certain arg is given - export it only
        if ($certain) return $lineA[0];

        // Foreach `field` entry, nested within current `element` entry
        // - build `field` entry's creation expression
        foreach (m('Field')->all('`entry` = "' . $this->id . '"', 'move') as $cfgFieldR)
            $lineA[] = $cfgFieldR->export();

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Build a string, that will be used in $this->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL and Indi Engine
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args
        foreach (ar('alias') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }
}