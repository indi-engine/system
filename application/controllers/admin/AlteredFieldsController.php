<?php
class Admin_AlteredFieldsController extends Indi_Controller_Admin_Multinew {

    /**
     * @var string
     */
    public $field = 'fieldId';

    /**
     * @var string
     */
    public $unset = 'rename,defaultValue';


    /**
     * Spoof icon used for jumpSectionActionId-column icon-overflow feature
     * from default column heading icon to actual icon of an action fetched by jumpSectionActionId->actionId
     *
     * @param $item
     * @param $r
     */
    public function renderGridDataItem(&$item, $r) {

        // If no icon-overflow html rendered (so no value there) - return
        if (!$render = &$item['_render']['jumpSectionActionId']) return;

        // Else spoof icon with actual action-icon
        $render = preg_replace('~src="[^"]+"~', 'src="' . $r->foreign('jumpSectionActionId')->foreign('actionId')->icon . '"', $render);
    }
}