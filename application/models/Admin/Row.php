<?php
class Admin_Row extends Indi_Db_Table_Row {

    /**
     * Function is redeclared for passwords encryption
     *
     * @return int
     */
    public function save(){

        // If password was changed
        if ($this->_modified['password']) {

            // Encrypt the password
            $this->_modified['password'] = db()->query('
                SELECT CONCAT("*", UPPER(SHA1(UNHEX(SHA1("' . $this->_modified['password'] . '")))))
            ')->fetchColumn(0);
        }

        // Standard save
        return parent::save();
    }

    /**
     * @return array|mixed
     */
    public function validate() {

        // Check 'vk' prop
        if (strlen($this->vk) && $this->isModified('vk')) {

            if (!preg_match('~^https://vk.com/([a-zA-Z0-9_\.]{3,})~', $this->vk, $m))
                $this->_mismatch['vk'] = 'Адрес страницы должен начинаться с https://vk.com/';

            else if (ini('vk')->enabled) {

                // Try to detect object type
                $response = Vk::type($m[1]);

                // If request was successful
                if ($response['success']) {

                    // Get result
                    $result = $response['json']['response'];

                    // If no result
                    if (!$result) $this->_mismatch['vk'] = 'Этой страницы ВКонтакте не существует';

                    // If result's type is not 'user'
                    else if ($result['type'] != 'user') $this->_mismatch['vk'] = 'Эта страница ВКонтакте не является страницей пользователя';

                    // Else setup custom mismatch message
                } else $this->_mismatch['vk'] = $response['msg'];
            }
        }

        // Call parent
        return $this->callParent();
    }

    /**
     * Build an expression for creating the current entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build entry creation expression
        $lineA[] = "admin('" . $this->email . "', " . $this->_ctor($certain) . ");";

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
        foreach (ar('email') as $arg) unset($ctor[$arg]);

        // Unset password
        unset($ctor['password']);

        // If certain field should be exported - keep it only
        if ($certain) $ctor = [$certain => $ctor[$certain]];

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // If field is 'roleId' - spoof value with role alias
            else if ($field->alias == 'roleId') $value = role($value)->alias;
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * This method was redefined to provide ability for some field
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'roleId') $value = role($value)->id;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }
}