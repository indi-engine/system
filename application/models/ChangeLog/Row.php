<?php
class ChangeLog_Row extends Indi_Db_Table_Row {

    /**
     * @return int
     */
    public function revert(){

        // Field types, allowed for reverting
        $types = 'string,textarea,html,number';

        // If field , that we're trying to revert is hidden or readonly (e.g disabled) - prevent reverting
        if (in($this->foreign('fieldId')->mode, 'hidden,readonly'))
            jflush(false, I_MDL_CHLOG_NO_REVERT_1);

        // If field, that we're trying to revert is using element not from allowed list - prevent reverting
        if (!in($this->foreign('fieldId')->foreign('elementId')->alias, $types)) {

            // Get titles of allowed field types
            $titles = m('Element')->all('FIND_IN_SET(`alias`, "' . $types . '")')->column('title');

            // Get title of last allowed element
            $last = array_pop($titles);

            // Flush failure msg
            jflush(false, __(I_MDL_CHLOG_NO_REVERT_2, im($titles, ', '), $last));
        }

        // If field , that we're trying to revert is a foreign key field - prevent reverting
        if (in($this->foreign('fieldId')->mode, 'hidden,readonly'))
            jflush(false, I_MDL_CHLOG_NO_REVERT_3);

        // Get field alias
        $field = m($this->entityId)->fields($this->fieldId)->alias;

        // Revert value
        $this->foreign('key')->set([$field => $this->was])->save();
    }
}