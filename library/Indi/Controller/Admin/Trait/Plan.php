<?php
trait Indi_Controller_Admin_Trait_Plan {

    /**
     * Calendar period
     *
     * @var string
     */
    public $type = 'month';

    /**
     * Color definition for calendar events
     *
     * @var bool/array
     */
    public $colors = false;

    /**
     * Override this method in child classes for defining custom colors, if need
     *
     * @param $info
     */
    public function adjustColors(&$info) {

        // Set empty info
        if (!$info) $info = [
            'major' => ['field' => '', 'colors' => []],
            'point' => ['field' => '', 'colors' => []]
        ];

        // Append one more color definition
        $info['major']['colors']['default'] = 'lime';
    }

    /**
     * Adjust certain color's css-definition
     *
     * @param $option
     * @param $color
     * @param $css
     */
    public function adjustColorsCss($option, $color, &$css) {

    }

    /**
     * Define colors
     *
     * @return mixed
     * @throws Exception
     */
    public function defineColors() {

        // Try to detected colors automatically
        $info = $this->detectColors();

        // Adjust colors, even if automatic colors detection had no success
        $this->adjustColors($info);

        // If still no colors - return
        if (!$info) return false;

        // Prepare colors
        foreach ($info['major']['colors'] as $option => $color) {

            // Get colors
            $colors = Indi::planItemColors($color);

            // Adjust it for custom needs
            $this->adjustColorsCss($option, $color, $colors);

            // Assign css
            $info['major']['colors'][$option] = $colors;
        }

        // Assign colors info into $this->colors, and return it
        return $this->colors = $info;
    }

    /**
     * Try to find enumset-field having most quantity of color-boxes,
     * and if found - prepare object containing colors info, to be used further
     *
     * @return mixed
     * @throws Exception
     */
    public function detectColors() {

        // Get id of ENUM column type
        $ENUM_columnTypeId = m('ColumnType')->row('`type` = "ENUM"')->id;

        // Get ENUM fields
        $ENUM_fieldRs = m()->fields()->select($ENUM_columnTypeId, 'columnTypeId');

        // Try to find color definitions
        $found = [];
        foreach ($ENUM_fieldRs as $ENUM_fieldR)
            foreach ($ENUM_fieldR->nested('enumset') as $enumsetR)
                if ($color = $enumsetR->rgb('boxColor') ?: $enumsetR->rgb('textColor'))
                    $found[$ENUM_fieldR->alias][$enumsetR->alias] = trim($color);

        // If nothing found - return
        if (!$found) return false;

        // If major-color field and/or point-color field are explicitly defined - setup info
        foreach (ar('major,point') as $kind)
            if ($$kind = m()->space('fields.colors.' . $kind))
                $info[$kind] = [
                    'field' => $$kind,
                    'colors' => $found[$$kind]
                ];

        // If $info was set up - return it
        if ($info) return $info;

        // Else auto-choose field having bigger qty of colored enum values
        $info['major'] = ['field' => '', 'colors' => []];
        foreach ($found as $field => $colors)
            if (count($colors) > count($info['major']['colors']) && $info['major']['field'] = $field)
                $info['major']['colors'] = $colors;

        // Return info about automatically-detected colors
        return $info;
    }

    /**
     * Apply colors
     */
    public function applyColors() {
        foreach ($this->rowset as $r) $r->system('color', $this->detectEventColor($r));
    }

    /**
     * Detect color for a certain event, given by $r arg.
     */
    public function detectEventColor($r) {

        // Detect major color and point color
        $major = $this->colors['major']['field'] ? $r->{$this->colors['major']['field']} : null;
        $point = $this->colors['point']['field'] ? $r->{$this->colors['point']['field']} : null;

        // Return both
        return compact('major', 'point');
    }

    /**
     * @param Indi_Db_Table_Row $r
     */
    public function adjustEventForMonth($r) {

    }

    /**
     * @param Indi_Db_Table_Row $r
     */
    public function adjustEventForWeek($r) {
        $this->adjustEventForMonth($r);
    }

    /**
     * @param Indi_Db_Table_Row $r
     */
    public function adjustEventForDay($r) {
        $this->adjustEventForWeek($r);
    }

    /**
     * @param array $data
     */
    public function adjustEventDataForMonth(&$data) {

    }

    /**
     * @param array $data
     */
    public function adjustEventDataForWeek(&$data) {
        $this->adjustEventDataForMonth($data);
    }

    /**
     * @param array $data
     */
    public function adjustEventDataForDay(&$data) {
        $this->adjustEventDataForWeek($data);
    }

    /**
     * Check whether 'since' uri-param is given, and if yes - prefill current entry's
     * certain space-fields with values according to clicked timestamp ('since' uri-param)
     * or according to selected datetime-range (both 'since' and 'until' uri-params)
     *
     * @return mixed
     */
    public function applySpace() {

        // If we're not dealing with a row, or we are, but with already existing row - return
        if (!t()->row || t()->row->id) return;

        // If clicked timestamp is not given as an uri-param - return
        if (!$since = uri('since')) return;

        // Get 'until' uri-param, if given
        $until = uri('until');

        // Setup `extraUri`, for 'since' and 'until' uri-params being kept even if entry's form will be reloaded
        t()->action->extraUri = '/since/' . $since . rif($until,'/until/$1');

        // Get space scheme and fields
        $space = m()->space();

        // Prepare array of values, that space-start fields should be prefilled with
        foreach (explode('-', $space['scheme']) as $coord) switch ($coord) {
            case 'date': $prefill[$space['coords'][$coord]] = date('Y-m-d', $since); break;
            case 'datetime': $prefill[$space['coords'][$coord]] = date('Y-m-d H:i:s', $since); break;
            case 'time': $prefill[$space['coords'][$coord]] = date('H:i:s', $since); break;
            case 'timeId': $prefill[$space['coords'][$coord]] = timeId(date('H:i', $since)) ?: '0'; break;
        }

        // Prepare array of values, that space-duration fields should be prefilled with
        if ($until) foreach (explode('-', $space['scheme']) as $coord) switch ($coord) {
            case 'dayQty': $prefill[$space['coords'][$coord]] = ($until - $since) / 86400; break;
            case 'minuteQty': $prefill[$space['coords'][$coord]] = ($until - $since) / 60; break;
            case 'timespan': $prefill[$space['coords'][$coord]] = date('H:i', $since) . '-' . date('H:i', $since); break;
        }

        // Append kanban value into $prefill array
        if (($k = t()->section->kanban) && ($v = uri()->kanban)) $prefill[$k['prop']] = $v;

        // Assign prepared values
        $this->row->set($prefill);
    }

    /**
     * Append special filter, linked to `spaceSince` column
     */
    public function plan_adjustTrail() {

        // If `spaceSince` field does not exists - return
        if (!$fieldR_spaceSince = m()->fields('spaceSince')) return;

        // For each of the below space-fields
        foreach (ar('spaceSince,spaceUntil') as $field) {

            // Set format of time to include seconds
            m()->fields($field)->param('displayTimeFormat', 'H:i:s');

            // Append to gridFields
            if (t()->gridFields) t()->gridFields->append(m()->fields($field));
        }

        // Append filter
        t()->filters->append([
            'sectionId' => t()->section->id,
            'fieldId' => $fieldR_spaceSince->id,
            'title' => $fieldR_spaceSince->title,
            'toolbar' => 'master'
        ]);

        // Define colors
        t()->section->colors = $this->defineColors();

        // If grouping is turned On - setup kanban cfg
        if ($fieldId_kanban = t()->section->groupBy) {

            // Get kanban field's dependencies
            $considerRs = m()->fields($fieldId_kanban)->nested('consider')->select('y', 'required');

            // For required of them
            foreach ($considerRs as $considerR)
                if ($cField = $considerR->foreign('consider'))
                    if ($rel = m($cField->relation))
                        if ($rel->table() == admin()->table())
                            t()->filtersSharedRow->{$cField->alias} = admin()->id;

            // Get combo data
            $combo = t()->filtersSharedRow->combo($fieldId_kanban);

            // If at least one possible kanban value exist
            if ($combo['store']['ids']) {

                // Setup kanban props
                t()->section->kanban = [
                    'prop' => $combo['name'],
                    'values' => $combo['store']['ids'],
                    'titles' => array_column($combo['store']['data'], 'title')
                ];
            }
        }

        // Check whether 'since' uri-param is given, and if yes - prefill current entry's
        // certain space-fields with values according to clicked timestamp ('since' uri-param)
        // or according to selected datetime-range (both 'since' and 'until' uri-params)
        $this->applySpace();

        // Increase limit to 1000
        t()->section->rowsOnPage = 1000;
    }

    /**
     * This method is redefined to append calendar type detection
     *
     * @return array|mixed
     */
    public function plan_filtersWHERE() {

        // Call parent
        $return = $this->callParent();

        // If _excelA-prop was set by above callParent call
        if ($this->_excelA) {

            // Pick bounds
            list ($since, $until) = ar(im($this->_excelA['spaceSince']['value']));

            // Detect type of calendar
            $diff = strtotime($until) - strtotime($since);
            if ($diff == 3600 * 24 * 7) $this->type = 'week';
            else if ($diff == 3600 * 24 * 1) $this->type = 'day';
        }

        // Return
        return $return;
    }

    /**
     * Make sure records will be fetched from db ordered by `spaceSince`-column
     *
     * @param string $finalWHERE
     * @param string $json
     * @return string
     */
    public function plan_finalORDER($finalWHERE = '', $json = '') {
        return 'spaceSince';
    }

    /**
     * Adjust events' *_Row instances depending on calendar type, and apply colors to events
     */
    public function plan_adjustGridDataRowset() {

        // Adjust events according to current calendar type
        if ($this->_excelA)
            foreach ($this->rowset as $r)
                $this->{'adjustEventFor' . ucfirst($this->type)}($r);

        // Apply colors
        $this->applyColors();
    }

    /**
     * Adjust events' props-arrays depending on calendar type
     */
    public function plan_adjustGridData(&$data) {
        if ($this->_excelA)
            foreach ($data as &$item)
                $this->{'adjustEventDataFor' . ucfirst($this->type)}($item);
    }

    /**
     * Do some plan-specific things prior event is saved
     */
    public function plan_preSaveAction() {

        // Check daily schedule
        $this->row->system('bounds', 'day');

        // Exclude `spaceSince` and `spaceUntil` fields from the list of disabled fields,
        // as those fields wil be got from $_POST as a result of event move or resize
        $this->excludeDisabledFields('spaceSince,spaceUntil');

        // Detect calendar type
        $this->filtersWHERE();
    }
}