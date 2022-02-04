<?php
class Indi_Controller_Admin_Myprofile extends Indi_Controller_Admin {

	/**
	 * Default action
	 */
    public $action = 'form';

    /**
     * Replace view type for 'index' action from 'grid' to 'changeLog'
     */
    public function adjustActionCfg() {
        $this->actionCfg['view']['index'] = 'myProfile';
    }

    /**
     * Force to perform formAction instead of indexAction
     */
    public function preDispatch() {

        // Force redirect to certain row
        if (uri()->action == 'index' || uri()->id != admin()->id) {

            // Set format to 'json', so scope things will be set up
            uri()->format = 'json';

            // Call parent
            parent::preDispatch();

            // Spoof uri params to force row-action
            uri()->action = $this->action;
            uri()->id = admin()->id;
            uri()->ph = t()->scope->hash;
            uri()->aix = t()->scope->aix;
        }

        // Call parent
        parent::preDispatch();
    }

    /**
     *
     *
     * @param $scope
     */
    public function createContextIfNeed($scope) {

        // Do nothing for index-action
        if (uri()->action == 'index' || uri()->id != admin()->id) return;

        // Call parent
        parent::createContextIfNeed($scope);
    }

    /**
     * Hardcode WHERE clause, to prevent user from accessing someone else's details
     *
     * @param $where
     * @return array|mixed
     */
    public function adjustPrimaryWHERE($where) {

        // Prevent user from accessing someone else's details
        $where['static'] = '`id` = "' . admin()->id . '"';

        // Return
        return $where;
    }
}