<?php
class Grid_Rowset extends Indi_Db_Table_Rowset {

    /**
     * Set up toggle-prop for each Grid_Row instance
     */
    public function toggle($value = '') {
        return $value
            ? $this->set('toggle', $value)->save()
            : $this->callParent();
    }

    /**
     * Set up rowReqIfAffected-prop for each Grid_Row instance
     */
    public function rowReqIfAffected($value = 'y') {
        $this->set('rowReqIfAffected', $value)->save();
    }
}