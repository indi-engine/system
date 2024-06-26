<?php
class Notice_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Entry, that triggered the notice.
     * Used for building notification message's body, in NoticeGetter_Row::_notify()
     *
     * @var Indi_Db_Table_Row
     */
    public $row = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = []) {

        // Explicitly set table name
        $config['table'] = 'notice';

        // Call parent
        parent::__construct($config);
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
            if ($columnName == 'entityId') $value = entity($value)->id;
            else if ($columnName == 'sectionId') {
                if ($value && !Indi::rexm('int11list', $value))
                    $value = m('section')->all('FIND_IN_SET(`alias`, "' . $value .'")')->fis();
            }
            else if ($columnName == 'roleId') {
                if ($value && !Indi::rexm('int11list', $value))
                    $value = m('role')->all('FIND_IN_SET(`alias`, "' . $value .'")')->fis();
            }
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Sync nested `noticeGetter` entries with keys, mentioned in `roleId` field
     *
     * @return int
     */
    public function onSave() {
        
        // Sync keys, mentioned as comma-separated values in `roleId` prop, with entries, nested in `noticeGetter` table
        $this->keys2nested('roleId', 'noticeGetter');
    }

    /**
     * Trigger the notice
     *
     * @param Indi_Db_Table_Row $row
     * @param int $diff
     * @param string $event
     */
    public function trigger(Indi_Db_Table_Row $row, $diff, $event = 'update') {

        // Assign `row` prop, that will be visible in compiling context
        $this->row = $row;

        // Foreach getter, defined for current notice
        foreach ($this->nested('noticeGetter') as $noticeGetterR) {

            // Directly setup foreign data for `noticeId` key, to prevent it
            // from being pulled, as there is no need to do that
            $noticeGetterR->foreign('noticeId', $this);

            // Notify
            $noticeGetterR->notify($row, $diff, $event);
        }
    }

    /**
     * Build an expression for creating the current `notice` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `notice` entry creation expression
        $lineA[] = "notice('" . $this->foreign('entityId')->table . "', '" . $this->alias . "', " . $this->_ctor($certain) . ");";

        // If $certain arg is given - export it only
        if ($certain) return $lineA[0];

        // Foreach `noticeGetter` entry, nested within current `notice` entry
        // - build `noticeGetter` entry's creation expression
        foreach ($this->nested('noticeGetter') as $noticeGetterR)
            $lineA[] = $noticeGetterR->export();

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Build a string, that will be used in Notice_Row->export()
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
        foreach (ar('entityId,alias') as $arg) unset($ctor[$arg]);

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
                if ($prop == 'entityId') $value = entity($value)->table;
                else if ($prop == 'sectionId') $value = section($value)->alias;
                else if ($field->rel()->table() == 'role') $value = $this->foreign($prop)->col('alias', true);
            }
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }
}