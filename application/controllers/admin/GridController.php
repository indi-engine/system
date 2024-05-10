<?php
class Admin_GridController extends Indi_Controller_Admin_Multinew {

    /**
     * @var string
     */
    public $field = 'fieldId';

    /**
     * @var string
     */
    public $unset = 'rename,tooltip';

    /**
     * Spoof icon used for jumpSectionActionId-column icon-overflow feature
     * from default column heading icon to actual icon of an action fetched by jumpSectionActionId->actionId
     *
     * @param $item
     * @param $r
     */
    public function renderGridDataItem(&$item, $r) {

        // If there is some value rendered for icon-overflow html
        if ($render = &$item['_render']['jumpSectionActionId']) {

            // Get action icon
            $icon = $r->foreign('jumpSectionActionId')->foreign('actionId')->icon;

            // Spoof icon with actual action-icon
            $render = preg_replace('~src="[^"]+"~', "src=\"$icon\"", $render);
        }

        // Spoof rendered value with color-box having color picked from the external source
        if ($render = &$item['_render']['colorEntry']) {
            $field = $r->foreign('colorField')->alias;
            $color = $r->foreign('colorEntry')->rgb($field);
            $title = $r->foreign('colorEntry')->attr('title');
            $render = "<span class=\"i-color-box\" style=\"background: $color;\" title=\"$title\"></span>";
        }

        // Prepend entity title to colorField title
        if ($render = &$item['_render']['colorField']) {

            // Get entity title
            $entityTitle =  $r->foreign('colorField')->foreign('entityId')->title;

            // Prepend it to colorField's title
            $render = preg_replace('~<img.+?>~', '$0' . "$entityTitle &raquo; ", $render);
        }
    }

    /**
     * Apply colorField-param for variable-entity field `colorEntry`
     *
     * @param int $fieldId
     */
    protected function _color(int $fieldId) {

        // Get color field alias
        if ($prop = m('field')->row((int) $fieldId)->alias ?? null) {

            // Set it as colorField-param's value
            t()->fields->field('colorEntry')->param('colorField', $prop);
        }
    }

    /**
     * Apply colorField-param for variable-entity field `colorEntry` on form load
     */
    public function adjustTrailingRowAccess(Indi_Db_Table_Row $row) {
        $this->_color(t()->row->colorField);
    }

    /**
     * Apply colorField-param for variable-entity field `colorEntry`
     * on value-change of colorField-prop, which colorEntry-field consider on
     *
     * @param $consider
     */
    public function formActionOdataColorEntry($consider) {
        $this->_color($consider['colorField']);
    }
}