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

        // If filter-prop is not empty - prepend with icon
        if ($_ = $item['filter']) $item['_render']['filter']
            = '<img src="resources/images/icons/btn-icon-filter.png" class="i-cell-img">' . $_;

        // If _render prop is not set - set it to empty object
        if (!$item['_render']) $item['_render'] = new stdClass();
    }
}