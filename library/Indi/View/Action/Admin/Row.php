<?php
class Indi_View_Action_Admin_Row extends Indi_View_Action_Admin {
    public function render() {

        // Start output buffering
        ob_start();

        // Setup sibling combo
        view()->siblingCombo();

        // Create `realtime` entry having `type` = "context"
        Realtime::context();

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

        // Get last active tab or use first section alias instead
        $nested = t()->scope->actionrow['south']['activeTab'] ?? t()->sections->at(0)->alias;

        // If last active tab was minimized - return
        if ((int) (t()->scope->actionrow['south']['height'] ?? 0) === 25) return;

        // Build url, even if parent entry is non yet existing entry
        $url = '/' . $nested . '/index/id/' . (t()->row->id ?: 0)
            . '/ph/' . uri('ph') . '/aix/' . (uri('aix') ?: 0) . '/tab/1/';

        // Get the response
        $out = uri()->response($url);

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