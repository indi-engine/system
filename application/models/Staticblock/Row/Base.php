<?php
class Staticblock_Row_Base extends Indi_Db_Table_Row {
    /**
     * Get the appropriate value, depending on block type
     */
    public function value() {
        $valueField = 'details' . ucfirst($this->type);
        $value = $this->$valueField;
        if ($this->type == 'textarea') $value = nl2br($value);
        return $value;
    }

    /**
     * Prevent tags-stripping from `detailsTextarea` contents
     */
    public function mismatch($check = false, $message = null) {
        $textarea = $this->detailsTextarea;
        $return = parent::mismatch($check);
        $this->detailsTextarea = $textarea;
        return $return;
    }
}