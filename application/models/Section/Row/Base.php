<?php
class Section_Row_Base extends Indi_Db_Table_Row {

    /**
     * Function is redeclared to provide an ability for `href` pseudo property to be got
     *
     * @param string $property
     * @return string
     */
    public function __get($property) {
        return $property == 'href' ? PRE . '/' . $this->alias : parent::__get($property);
    }

    /**
     * Provide ability to use `id` column as defaultSortField
     *
     * @param $field
     * @return Indi_Db_Table_Rowset|mixed
     */
    public function getComboData($field, $page = null, $selected = null, $selectedTypeIsKeyword = false, $where = null, $fieldR = null, $order = null, $dir = 'ASC', $offset = null, $consistence = null, $multiSelect = null) {

        // Call parent
        $dataRs = $this->callParent();

        // Prepend data rowset with 'ID' option
        if ($field == 'defaultSortField') $dataRs->append(['id' => -1, 'title' => 'ID', 'alias' => 'id'], true);

        // Return
        return $dataRs;
    }

    /**
     * This method was redefined to provide ability for some field
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if (in($columnName, 'parentSectionConnector,groupBy,defaultSortField,tileField')) {
                $value = $value === 'id' && $columnName === 'defaultSortField'
                    ? -1
                    : field($this->entityId, $value)->id;
            }
            else if ($columnName == 'colorField') $value = field($this->entityId, $value)->id;
            else if ($columnName == 'colorFurther') $value = field(field($this->entityId, $this->colorField)->relation, $value)->id;
            else if ($columnName == 'entityId') $value = entity($value)->id;
            else if ($columnName == 'sectionId') $value = section($value)->id;
            else if ($columnName == 'tileThumb') $value = thumb($this->entityId, $this->tileField, $value)->id;
            else if ($columnName == 'move') return $this->_system['move'] = $value;
            else if ($columnName == 'roleIds' || $columnName == 'expandRoles') {
                if ($value && !Indi::rexm('int11list', $value)) $value = m('role')
                    ->all('FIND_IN_SET(`alias`, "' . $value .'")')
                    ->col('id', true);
            }
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Convert values of `defaultSortField` and `defaultSortDirection`
     * into a special kind of json-encoded array, suitable for usage
     * with ExtJS
     *
     * Also, before json-encoding, prepend additional item into that array,
     * responsible for grouping, if `groupBy` prop is set
     *
     * @return string
     */
    public function jsonSort() {

        // Blank array
        $json = [];

        // Mind grouping, as it also should be involved while building ORDER clause
        if ($this->groupBy && $this->foreign('groupBy')) {
            $json[$property = $this->foreign('groupBy')->alias] = [
                'property' => $property,
                'direction' => 'ASC'
            ];
        }

        // Ming sorting
        if ($this->foreign('defaultSortField'))
            $json[$property = $this->foreign('defaultSortField')->alias] = [
                'property' => $property,
                'direction' => $this->defaultSortDirection,
            ];

        // Return json-encoded sort params
        return json_encode(array_values($json));
    }

    /**
     * Build a string, that will be used in Section_Row_Base->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = '') {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` and `move` as they will be set automatically by MySQL and Indi Engine, respectively
        unset($ctor['id']);

        // Exclude props that are already represented by one of shorthand-fn args or are set automatically
        foreach (ar('alias,roleIds,expand,expandRoles') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = m('Section')->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) {
                if (!isset($GLOBALS['export']) || !$this->sectionId) unset($ctor[$prop]);
            }

            // Else if $prop is 'move' - get alias of the section, that current section is after,
            // among sections with same parent sectionId
            else if ($prop == 'move') $value = $this->position();

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'sectionId') $value = section($value)->alias;
                else if ($prop == 'entityId') $value = entity($value)->table;
                else if (in($prop, 'parentSectionConnector,groupBy,tileField')) $value = field($this->entityId, $value)->alias;
                else if ($prop === 'defaultSortField') $value = $value == -1 ? 'id' : field($this->entityId, $value)->alias;
                else if ($prop == 'tileThumb') $value = m('Resize')->row($value)->alias;
                else if ($field->rel()->table() == 'role') $value = $this->$prop ? $this->foreign($prop)->col('alias', true) : '';
            }
        }

        // Stringify and return $ctor
        return $this->_var_export($ctor);
    }

    /**
     * Build an expression for creating the current `section` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        if (isset($GLOBALS['export'])) {
            $lineA []= "\n# '$this->title'-section" . rif(!$this->sectionId, " (only used as a group in left menu)");
        }

        // Build `section` entry creation expression
        $lineA[] = "section('" . $this->alias . "', " . $this->_ctor($certain) . ");";

        // If $certain arg is given - export it only
        if ($certain) return $lineA[0];

        // Foreach `section2action` entry, nested within current `section` entry
        // - build `section2action` entry's creation expression
        foreach ($this->nested('section2action', ['order' => 'move']) as $section2actionR)
            $lineA[] = $section2actionR->export();

        // Foreach `grid` entry, nested within current `section` entry
        // - build `grid` entry's creation expression
        foreach ($this->nested('grid', ['order' => 'move']) as $gridR)
            $lineA[] = $gridR->export();

        // Foreach `alteredField` entry, nested within current `section` entry
        // - build `alteredField` entry's creation expression
        foreach ($this->nested('alteredField') as $alteredFieldR)
            $lineA[] = $alteredFieldR->export();

        // Foreach `filter` entry, nested within current `section` entry
        // - build `filter` entry's creation expression
        foreach ($this->nested('filter', ['order' => 'move']) as $filterR)
            $lineA[] = $filterR->export();

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Get the the alias of the `section` entry,
     * that current `section` entry is positioned after
     * among all `section` entries having same `sectionId`
     * according to the values `move` prop
     *
     * @param null|string $after
     * @param string $withinFields
     * @return string|Indi_Db_Table_Row
     */
    public function position($after = null, $withinFields = 'sectionId') {

        // Build within-fields WHERE clause
        $wfw = [];
        foreach (ar($withinFields) as $withinField)
            if (array_key_exists($withinField, $this->_original))
                $wfw []= '`' . $withinField . '` = "' . $this->$withinField . '"';

        // Get ordered fields aliases
        $sectionA_alias = db()->query(
            'SELECT `alias` FROM `:p` :p ORDER BY `move`', $this->_table, rif($within = im($wfw, ' AND '), 'WHERE $1')
        )->col();

        // Get current position
        $currentIdx = array_flip($sectionA_alias)[$this->alias];

        // Do positioning
        return $this->_position($after, $sectionA_alias, $currentIdx, $within);
    }

    /**
     * Prevent `extendsPhp` and `extendsJs` props from being empty
     */
    public function onBeforeSave() {

        // Clear colorFurther-field, which depends on colorField-field
        if ($this->fieldIsZeroed('colorField')) $this->zero('colorFurther', true);

        // If we're going to turn tile-panel but no tileField is given - abort
        if ($this->tileToggle === 'y' && !$this->tileField) $this->zero('tileToggle', true);

        // Clear tileThumb-field, which depends on tileField-field
        if ($this->fieldIsZeroed('tileField')) $this->zero('tileThumb', true);

        // Clear value for `expandRoles` prop, if need
        if (in($this->expand, 'all,none')) $this->expandRoles = '';

        // Setup default value instead of empty value
        foreach (ar('extendsPhp,extendsJs') as $prop)
            if ($this->isModified($prop) && !$this->$prop)
                $this->$prop = $this->field($prop)->defaultValue;
    }

    /**
     *
     * @throws Exception
     */
    public function onSave() {

        // If _system['move'] is defined
        if (array_key_exists('move', $this->_system)) {

            // Get field, that current field should be moved after
            $after = $this->_system['move']; unset($this->_system['move']);

            // Position field for it to be after field, specified by $this->_system['move']
            $this->position($after);
        }

        // If `filterOwner` prop was changed to 'no' or 'yes' - replicate that on section2action-level
        if ($this->affected('filterOwner')
            && $this->filterOwner != 'certain'
            && !$this->system('replication')) $this

            // Get nested section2action-entries
            ->nested('section2action')

            // Setup system replication flags to prevent endless loops
            ->system('replication', true)

            // Spread new value of filterOwner-prop
            ->set('filterOwner', $this->filterOwner)

            // Save
            ->save();

        // Send signal for menu to be reloaded
        Indi::ws(['type' => 'menu', 'to' => true]);
    }

    /**
     *
     */
    public function onDelete() {
        Indi::ws(['type' => 'menu', 'to' => true]);
    }

    /**
     * This method is redefined to setup default value for $within arg,
     * for current `section` entry to be moved within it's parent `section`
     *
     * @param string $direction
     * @param string $within
     * @return bool
     */
    public function move($direction = 'up', $within = '') {

        // If $within arg is not given - move grid column within the section it belongs to
        $within = '`sectionId` = "' . $this->sectionId . '"';

        // Call parent
        return parent::move($direction, $within);
    }

    /**
     * Overridden from parent to update menu afterwards
     *
     * @param false $notices
     * @param bool $amerge
     * @param bool $realtime
     * @param bool $inQtySum
     * @return int|void
     */
    public function basicUpdate($notices = false, $amerge = true, $realtime = true, $inQtySum = true) {

        // Call parent
        $this->callParent();

        // Update menu
        if ($this->affected('move,title,sectionId,expanded'))
            Indi::ws(['type' => 'menu', 'to' => true]);
    }
}