<?php
class Role extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Role_Row';

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = [
        'field' => 'fraction',
        'value' => [
            'system' => 'adminSystemUi',
            'custom' => 'adminCustomUi',
        ]
    ];
}