<?php
class Admin_FilterController extends Indi_Controller_Admin_Multinew {

    /**
     * @var string
     */
    public $field = 'fieldId';

    /**
     * @var string
     */
    public $unset = 'rename,filter,defaultValue';

    /**
     * Add icon for `filter` prop
     *
     * @param $item
     * @param Indi_Db_Table_Row $r
     */
    public function adjustGridDataItem(&$item, $r) {

        // If _render prop is not set - set it to empty object
        if (!$item['_render']) $item['_render'] = new stdClass();
    }
}