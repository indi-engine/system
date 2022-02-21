<?php
class Action extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Action_Row';

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = [
        'field' => 'type',
        'value' => [
            's' => 'adminSystemUi,adminCustomUi',
            'p' => 'adminCustomUi',
            'o' => 'adminPublicUi'
        ]
    ];
}