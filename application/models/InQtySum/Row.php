<?php
class InQtySum_Row extends Indi_Db_Table_Row {

    /**
     * This method was redefined to provide ability for some props
     * to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'sourceEntity') $value = m($value)->id;
            else if (in($columnName, 'sourceTarget,sourceField')) $value = field($this->sourceEntity, $value)->id;
            else if ($columnName == 'targetField') $value = field(field($this->sourceEntity, $this->sourceTarget)->relation, $value)->id;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Make sure non-zero value of sourceField is applicable only if count type is 'sum'
     */
    public function onBeforeSave() {
        if ($this->type !== 'sum') $this->zero('sourceField', true);
    }

    /**
     * @return int
     */
    public function onSave() {
        $this->recount('save');
    }

    /**
     * @throws Exception
     */
    public function onDelete()
    {
        $this->recount('delete');
    }

    /**
     * @param string $event
     * @throws Exception
     */
    protected function recount($event = 'save') {

        // Get source table name
        $sourceTable = m($this->sourceEntity)->table();

        // Get target model instance
        $targetModel = $this->foreign('sourceTarget')->rel();

        // Get an alias of the field that is a connector between source entries and target entry
        $connector = $this->foreign('sourceTarget')->alias;

        // Get target field
        $targetField = $this->foreign('targetField')->alias;

        // Get source field
        $sourceField = $this->type === 'sum' ? $this->foreign('sourceField')->alias : '';

        // Shortcut for count type
        $type = $this->type;

        // Foreach target entry
        $targetModel->batch(function(Indi_Db_Table_Row $targetEntry) use ($sourceTable, $sourceField, $targetField, $event) {

            // (Re)calc target value
            $targetValue = $event === 'delete'
                ? 0
                : ($this->type === 'qty'
                    ? $targetEntry->qty($sourceTable, $this->sourceWhere, $connector)
                    : $targetEntry->sum($sourceTable, $sourceField, $this->sourceWhere, $connector));

            // Calc and apply value into target field
            $targetEntry->set($targetField, $targetValue)->save();
        });
    }

    /**
     * Build a string, that will be used in InQtySum_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = null) {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL
        unset($ctor['id']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('sourceEntity,sourceTarget,targetField') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Else if prop contains keys - use aliases instead
            else if ($field->storeRelationAbility != 'none') {
                if ($prop == 'sourceField') $value = m('field')->row($value)->alias;
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }

    /**
     * Build an expression for creating/getting the current `inQtySum` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Shortcuts
        $sourceTable = $this->foreign('sourceEntity')->table;
        $sourceTarget = $this->foreign('sourceTarget')->alias;
        $targetField = $this->foreign('targetField')->alias;
        $ctor = rif($certain !== false, ', ' . $this->_ctor($certain));

        // Build entry creation line
        return "inQtySum('$sourceTable', '$sourceTarget', '$targetField'$ctor);";
    }
}