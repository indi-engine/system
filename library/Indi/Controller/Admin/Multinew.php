<?php
class Indi_Controller_Admin_Multinew extends Indi_Controller_Admin_Exportable {

    /**
     * Name of field, that has `storeRelationAbility` = 'one', but we
     * simulate it as 'many' to provide ability to specify multiple values
     * within new entry creation screen, so multiple entries will be created
     *
     * @var null
     */
    public $field = null;

    /**
     * Array or comma-separated list of $_POST props, that should be unset
     *
     * @var null
     */
    public $unset = null;

    /**
     * Append ability to choose multiple fields for being altered
     */
    public function adjustTrail() {

        // If we're not dealing with row, or we are, but it's an existing row - return
        if (!$this->field || !$this->row || $this->row->id) return;

        // Change fieldId-field's `storeRelationAbility` prop to 'many'
        t()->fields->field($this->field)->storeRelationAbility = 'many';

        // Change value from '0' to ''
        t()->row->{$this->field} = '';
    }

    /**
     * Handle cases when comma-separated list is given within $_POST['fieldId']
     */
    public function saveAction($redirect = true, $return = false) {

        // If we operate on existing entry - call parent
        if (!$this->field || $this->row->id) return $this->callParent();

        // Shortcut to $_POST[$this->field]
        $_ = Indi::post($this->field);

        // If $_POST['fieldId'] is not a comma-separated list having at least 2 values - call parent
        if (!Indi::rexm('int11list', $_) || count($_idA = ar($_)) < 2) return $this->callParent();

        // Unset certain $_POST variables
        if ($this->unset) foreach (ar($this->unset) as $u) unset(Indi::post()->$u);

        // Foreach fieldId make clone of $this->row
        foreach ($_idA as $_id) $cloneA[$_id] = clone $this->row;

        // Foreach clone
        foreach ($cloneA as $_id => $cloned) {

            // Set $_POST[$this->field]
            Indi::post($this->field, $_id);

            // Spoof $this->row with clone
            $this->row = $cloned;

            // Call parent
            $response = parent::saveAction(true, true);
        }

        // Flush response
        jflush($response);
    }
}