<?php
class Profile_Row extends Indi_Db_Table_Row {

    /**
     * Append WHERE clause for filtering users within combo-data by their profileId
     *
     * @return bool|string|void
     */
    public function _comboDataConsiderWHERE(&$where, Field_Row $fieldR, Field_Row $cField, $cValue, $required, $cValueForeign = 0) {

        // If dependent-field is not a variable-entity field, or is, but variable entity is not determined - return
        if ($fieldR->relation || !$cValueForeign) return;

        // If variable entity is determined, and it's `admin` - append filtering by `profileId`
        if (m($cValueForeign)->table() == 'admin') $where []= '`' . $cField->alias . '` = "' . $cValue . '"';
    }
}