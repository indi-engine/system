<?php
class Indi_Controller_Admin_CfgField extends Indi_Controller_Admin_Field {

    /**
     * Show entry-field but keep disabled
     * Disable onDelete-field
     */
    public function formAction() {

        // Default form action
        parent::formAction();

        // Disable `entry` field, but keep it shown
        $this->appendDisabledField('entry', true);

        // Disable `onDelete` field, as it's not supported for cfgFields so far
        $this->appendDisabledField('onDelete');
    }
}