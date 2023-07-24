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
     * @param $cell
     * @param $value
     */
    public function onBeforeCellSave($cell, $value) {

        // If we're going to turn on one of those features
        if (in($cell, 'filter,allowZeroResult,denyClear,ignoreTemplate,multiSelect') && $value) {

            // If it's a not foreign-key field behind this filter
            if (t()->row->fieldBehind()->storeRelationAbility === 'none') {

                // Show side msg
                wflush(false, 'This feature is only applicable for filters that are having foreign-key fields behind');
            }
        }
    }
}