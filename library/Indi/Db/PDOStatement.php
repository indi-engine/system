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
     * Get value handy for use in 'IN (...)' expression
     *
     * @return array
     */
    public function in() {

        // Get array
        $array = $this->fetchAll(PDO::FETCH_COLUMN);

        // Return value
        return $array ? join(',', $array) : 0;
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

    /**
     * Shortcut to fetchAll(PDO::FETCH_GROUP)
     *
     * @return array
     */
    public function groups() {
        return $this->fetchAll(PDO::FETCH_GROUP);
    }

    /**
     * Shortcut to fetchAll(PDO::FETCH_UNIQUE)
     *
     * @return array
     */
    public function byid() {
        return $this->fetchAll(PDO::FETCH_UNIQUE);
    }
}