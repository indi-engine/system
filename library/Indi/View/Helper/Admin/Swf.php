<?php
class Indi_View_Helper_Admin_Swf extends Indi_View_Helper_Abstract
{
    public function swf($entity = null, $id = null, $name = null, $silence = true, $width = null, $height = null)
    {
        static $index = null;

        $entity = $entity ? $entity : Indi::trail()->model->name();
        $id = $id ? $id : $this->view->row->id;

        if ($name === null) {
            if ($index !== null) {
                $index++;
                $name = $index;
            } else {
                $index = 1;
            }
        }

        $xhtml = Indi::swf($entity, $id, $name, array('width' => $width, 'height' => $height));
        
        return $xhtml;
    }
}