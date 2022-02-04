<?php
class Indi_View_Action_Admin_Row extends Indi_View_Action_Admin {
    public function render() {

        // Start output buffering
        ob_start();

        // Setup sibling combo
        Indi::view()->siblingCombo();

        // Create `realtime` entry having `type` = "context"
        if (!m('realtime')->row([
            '`type` = "context"',
            '`token` = "' . t()->bid() . '"',
            '`realtimeId` = "' . m('realtime')->row('`token` = "' . CID . '"')->id . '"'
        ])) t()->context();

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

        // If no subsections - return
        if (!t()->sections->count()) return;

        // If `southSeparate` flag is `true` - return
        if (t()->section->southSeparate) return;

        // If presence of south panel should be automatically detected,
        // Check whether there are 10 or less visible fields,
        // and if yes - mark that south panel can be shown
        if (t()->action->south == 'auto')
            if (t()->fields->select(': != "hidden"', 'mode')->count() <= 10)
                t()->action->south = 'yes';

        // If there should be no south panel - return
        if (t()->action->south != 'yes') return;

        // Get last active tab
        $nested = t()->scope->actionrow['south']['activeTab'];

        // If last active tab was minimized - return
        if (t()->scope->actionrow['south']['height'] == 25) return;

        // If no last active tab, use first section alias instead
        if (!$nested) $nested = t()->sections->at(0)->alias;

        // Build url, even if parent entry is non yet existing entry
        $url = '/' . $nested . '/index/id/' . (t()->row->id ?: 0)
            . '/ph/' . Indi::uri('ph') . '/aix/' . Indi::uri('aix') . '/';

        // Get the response
        $out = Indi::lwget($url);

        // Delimiter for error detection within $out
        $split = '</error>';

        // If error delimiter detected
        if (preg_match('~'. $split .'~', $out)) {

            // Split content by delimiter
            $raw = explode('</error>', $out);

            // Pick raw contents, not related to errors
            $out = array_pop($raw);

            // Send HTTP 500 code
            header('HTTP/1.1 500 Internal Server Error');

            // Echo errors
            echo implode('</error>', $raw) . '</error>';
        }

        // Assign response text
        foreach (t()->sections as $sectionR) if ($sectionR->alias == $nested) $sectionR->responseText = $out;
    }
}