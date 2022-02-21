<?php
class Search extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Search_Row';

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     *
     * @var array
     */
    protected $_evalFields = ['defaultValue', 'filter'];

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = ['field' => 'sectionId'];
}