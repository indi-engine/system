<?php
class Indi_Db_PDOStatement extends PDOStatement {

    /**
     * Shortcut-method to invoke PDO::FETCH_KEY_PAIR fetch style
     *
     * @return array
     */
    public function pairs() {
        return $this->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Shortcut-method to invoke PDO::FETCH_COLUMN fetch style
     *
     * @return array
     */
    public function col() {
        return $this->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Alias for fetchColumn() method
     *
     * @return mixed
     */
    public function cell($column_number = 0) {
        return $this->fetchColumn($column_number);
    }

    /**
     * Alias for fetchAll() method
     */
    public function all($style = null) {
        return $this->fetchAll($style);
    }
}