<?php
class Indi_View_Action_Admin_Rowset extends Indi_View_Action_Admin {
    public function render() {

        // Start output buffering
        ob_start();

        // Setup filters
        foreach (Indi::trail()->filters ?: [] as $filter)
            if ($field = $filter->foreign($filter->further ? 'further' : 'fieldId'))
                if ($field->storeRelationAbility != 'none' || $field->columnTypeId == 12)
                    Indi::view()->filterCombo($filter);

        // Prepare and assign raw response for rendering tab contents, if need
        $this->renderTab();

        // Return buffered contents with parent's return-value
        return ob_get_clean() . parent::render();
    }

    /**
     * Prepare and assign raw response for rendering tab contents, if need
     *
     * @return string JSON-response, got by separate call for an uri
     */
    public function renderTab() {

        // Disable this feature
        return;

        // Get the id
        $id = Indi::trail()->scope->actionrowset['south']['activeTab'];

        // If $id is null/empty
        if (!strlen($id)) return;

        // If last active tab was minimized - return
        if (Indi::trail()->scope->actionrowset['south']['height'] == 25) return;

        // Build url, depending on whether or not $id is non-zero
        $url = '/' . Indi::trail()->section->alias . '/form/';
        if ($id) $url .= 'id/' . $id . '/';
        $url .= 'ph/' . Indi::trail()->scope->hash . '/';
        if ($id) $url .= 'aix/' . Indi::trail()->model->detectOffset(
            Indi::trail()->scope->WHERE, Indi::trail()->scope->ORDER, $id
        ) . '/';

        // Get the response, and assign it into the scope' special place
        Indi::trail()->scope->actionrowset['south']['activeTabResponse'][$id] = Indi::lwget($url);
    }
}