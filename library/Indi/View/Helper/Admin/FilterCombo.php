<?php
class Indi_View_Helper_Admin_FilterCombo extends Indi_View_Helper_FilterCombo{

    /**
     * @return Indi_Controller_Admin|null
     */
    public function getController() {
        return Indi_Trail_Admin::$controller;
    }

    /**
     *
     */
    public function primaryWHERE() {
        return t()->scope->primary;
    }
}
