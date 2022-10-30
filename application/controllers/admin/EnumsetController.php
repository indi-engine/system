<?php
class Admin_EnumsetController extends Indi_Controller_Admin_Exportable {

    /**
     * Action function is redeclared to provide a strip hue part from $this->row->alias
     */
    public function formAction() {

        // If $this->row->defaultValue is a color in format 'hue#rrggbb'
        if (preg_match(Indi::rex('hrgb'), $this->row->alias))

            // Strip hue part from that color, for it to be displayed in form without hue
            $this->row->modified('alias', substr($this->row->alias, 3));

        // Default form action
        parent::formAction();
    }

    /**
     * Apply style to titles
     *
     * @param $item
     * @param $r
     */
    public function adjustGridDataItem(&$item, $r) {
        $item['_render']['title'] = $r->styled(true);
    }
}