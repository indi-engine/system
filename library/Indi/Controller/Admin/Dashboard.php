<?php
class Indi_Controller_Admin_Dashboard extends Indi_Controller_Admin_Myprofile {
	
	/**
	 * Default action
	 */
    public $action = 'dashboard';
	
	/**
     * Replace view type for 'index' action from 'grid' to 'changeLog'
     */
    public function adjustActionCfg() {
        $this->actionCfg['view']['dashboard'] = 'form';
    }
}