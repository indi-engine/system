<?php
class AlteredField extends Indi_Db_Table {

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     * @var array
     */
    protected $_evalFields = array('defaultValue');

    /**
     * Classname for row. Here we setup Indi_Db_Table_Row_Noeval as a row class name for current model
     * to prevent call of php eval for value of `defaultValue` property at the stage of row construction,
     * because there (within contents of `defaultValue`) can be expressions like 'Indi::trail(X)->...',
     * which will cause errors, because at that moment trail may not had been created. So, for getting the
     * `defaultValue` compilation result, ->compiled('defaultValue') call should be done, so compilation
     * would be performed at the stage of that call rather than at the stage of row construction
     *
     * @var string
     */
    protected $_rowClass = 'Indi_Db_Table_Row_Noeval';
}
