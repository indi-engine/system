<?php
class PossibleElementParam_Row extends Indi_Db_Table_Row {
    public function mismatch($check = false, $message = null) {
        if ($this->alias == 'optionTemplate') $this->model()->setEvalFields(['defaultValue']);
        $defaultValue = $this->defaultValue;
        $return = parent::mismatch($check, $message);
        if ($this->alias == 'optionTemplate') $this->model()->setEvalFields([]);
        $this->defaultValue = $defaultValue;
        return $return;
    }

    public function save() {
        if ($this->alias == 'optionTemplate') $this->model()->setEvalFields(['defaultValue']);
        $return = parent::save();
        if ($this->alias == 'optionTemplate') $this->model()->setEvalFields([]);
        return $return;
    }
}