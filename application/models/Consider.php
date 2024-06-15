<?php
class Consider extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Consider_Row';

    /**
     * Classname for rowset
     *
     * @var string
     */
    public $_rowsetClass = 'Consider_Rowset';

    /**
     * Temporary disable foreign key checks if connector is -1, which is used to point to `id`-column,
     * because `id`-columns does not have corresponding entries in `field`-table
     *
     * @param array $data
     * @param string $where
     * @return int|void
     * @throws Exception
     */
    public function update(array $data, $where = '') {

        // Temporarily disable foreign keys
        if (($data['connector'] ?? null) == -1) db()->query('SET `foreign_key_checks` = 0');

        // Call parent
        $return = parent::update($data, $where);

        // Enable foreign keys back
        if (($data['connector'] ?? null) == -1) db()->query('SET `foreign_key_checks` = 1');

        // Return
        return $return;
    }

    /**
     * Temporary disable foreign key checks if connector is -1, which is used to point to `id`-column,
     * because `id`-columns does not have corresponding entries in `field`-table
     *
     * @param array $data
     * @return int|void
     * @throws Exception
     */
    public function insert(array $data) {

        // Temporarily disable foreign keys
        if (($data['connector'] ?? null) == -1) db()->query('SET `foreign_key_checks` = 0');

        // Call parent
        $return = parent::insert($data);

        // Enable foreign keys back
        if (($data['connector'] ?? null) == -1) db()->query('SET `foreign_key_checks` = 1');

        // Return
        return $return;
    }
}