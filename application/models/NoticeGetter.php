<?php
class NoticeGetter extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'NoticeGetter_Row';

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     * @var array
     */
    protected $_evalFields = ['criteriaEvt', 'criteriaInc', 'criteriaDec'];
}