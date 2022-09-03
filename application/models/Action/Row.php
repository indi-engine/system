<?php
class Action_Row extends Indi_Db_Table_Row {

    /**
     * Build an expression for creating the current `action` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `section` entry creation expression
        $lineA[] = "action('" . $this->alias . "', " . $this->_ctor($certain) . ");";

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Build a string, that will be used in Action_Row->export()
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

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = m('Action')->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Check whether <iconFileName>-jump.<iconFileExtension> exists and if yes return current value of icon-prop but
     * with '-jump' postfixed into icon filename, or return just the value of icon-prop as is
     *
     * @param bool $jump
     * @return string
     */
    public function icon($jump = true) {

        // If $jump arg is true and icon-prop is not empty
        if ($jump && $this->icon) {

            // Foreach directory
            foreach (ar($this->field('icon')->params['dir']) as $dir) {

                // Get absolute directory path with no trailing slash
                $pre = DOC . STD . rif(!preg_match('~^/~', $dir), VDR . '/client/');

                // Build icon filename for icon version having small right-arrow
                $jump = preg_replace('~^(.+?)(\.[^.]+)$~', '$1-jump$2', $this->icon);

                // If original icon file found - it means we're in the right directory
                if (is_file($pre . '/' . $this->icon)) return is_file($pre . '/' . $jump) ? $jump : $this->icon;
            }
        }

        // Return icon-prop as is
        return $this->icon;
    }
}