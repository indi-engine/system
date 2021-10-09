<?php
class Param extends Indi_Db_Table {

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     * @var array
     */
    protected $_evalFields = array('value', 'cfgValue');

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Param_Row';

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = ['field' => 'fieldId'];
}
