<?php
class Consider_Rowset extends Indi_Db_Table_Rowset {

    /**
     * Append 'ID' pseudo entry
     *
     * @param $key
     * @param $rowset
     */
    public function _adjustForeignRowset($key, &$rowset) {

        // If $key arg is not 'connector' - keep $rowset as is
        if ($key != 'connector') return;

        // Append 'ID' pseudo entry
        $rowset->append(['id' => -1, 'title' => 'ID', 'alias' => 'id'], 0);
    }
}