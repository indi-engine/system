<?php
class Grid extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Grid_Row';

    /**
     * Classname for rowset
     *
     * @var string
     */
    public $_rowsetClass = 'Grid_Rowset';

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = ['field' => 'sectionId'];
}