<?php
class Indi_Controller_Admin_Chart extends Indi_Controller_Admin {

    /**
     * Alias of field, that will be used for X-axis
     *
     * @var null
     */
    public $xAxisField = null;

    /**
     * Set up default view for 'index' action to be 'chart'
     */
    public function adjustActionCfg() {
        $this->actionCfg['view']['index'] = 'chart';
    }

    /**
     * Detect field, that will be used for Y-axis
     */
    public function detectXAxis() {

        // If y-axis field is implicitly set up - return it
        if ($this->xAxisField) return $this->xAxisField;

        // Here we assume that yAxisField-field has DATETIME or DATE
        // as it's mysql column type so we search for it and return first found
        return $this->xAxisField = t()->fields->select('9,6', 'columnTypeId')->at(0)->alias;
    }

    /**
     * Prepare rowset data for being used by chart: set up additional `xAxis` prop within `_system`
     * 2. Convert all numeric values into (int) and (float)
     */
    public function adjustGridDataRowset() {

        // Detect x-axis
        $xAxis = $this->detectXAxis();

        // Foreach row within rowset - setup up additional `xAxis` system prop
        foreach ($this->rowset as $r) $r->system('xAxis', strtotime(($r->system($xAxis) ?: $r->$xAxis)) * 1000);

        // Reverse rowset, as HighStock becomes angry otherwise
        $this->rowset->reverse();
    }

    /**
     * Prepare data for being used by chart: convert all numeric values into (int) and (float)
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Detect numeric fields
        $fieldRs_numeric = m()->fields()->select('18,24,25', 'elementId');

        // Foreach data item - cast numeric values
        for ($i = 0; $i < count($data); $i++)
            foreach ($fieldRs_numeric as $fieldR_numeric)
                switch ($fieldR_numeric->elementId) {
                    case 18: $data[$i][$fieldR_numeric->alias] = (int) $data[$i][$fieldR_numeric->alias]; break;
                    case 24: $data[$i][$fieldR_numeric->alias] = price($data[$i][$fieldR_numeric->alias]); break;
                    case 25: $data[$i][$fieldR_numeric->alias] = decimal($data[$i][$fieldR_numeric->alias], 3); break;
                }
    }
}