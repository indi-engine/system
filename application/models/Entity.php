<?php
class Entity extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Entity_Row';

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = [
        'field' => 'system',
        'value' => [
            'y' => 'adminSystemUi',
            'n' => 'adminCustomUi',
            'o' => 'adminPublicUi'
        ]
    ];
}