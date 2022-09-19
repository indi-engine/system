<?php
class ColumnType extends Indi_Db_Table {

    /**
     * Contains empty/zero values for column types
     *
     * @var array
     */
    protected $_zeroValue = [
        'VARCHAR(255)' => '',
        'INT(11)' => '0',
        'TEXT' => '',
        'DOUBLE(7,2)' => '0.00',
        'DECIMAL(11,2)' => '0.00',
        'DECIMAL(14,3)' => '0.000',
        'DATE' => '0000-00-00',
        'YEAR' => '0000',
        'TIME' => '00:00:00',
        'DATETIME' => '0000-00-00 00:00:00',
        'ENUM' => '0',
        'SET' => '',
        'BOOLEAN' => '0',
        'VARCHAR(10)' => ''
    ];

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'ColumnType_Row';

    /**
     * Get zero value for a given column type
     *
     * @param $type
     * @return string
     */
    public function zeroValue($type) {
        return $this->_zeroValue[$type];
    }
}