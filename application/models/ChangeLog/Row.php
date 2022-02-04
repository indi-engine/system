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
            jflush(false, 'Восстановление недоступно для полей, заполняемых автоматически');

        // If field, that we're trying to revert is using element not from allowed list - prevent reverting
        if (!in($this->foreign('fieldId')->foreign('elementId')->alias, $types)) {

            // Get titles of allowed field types
            $titles = Indi::model('Element')->fetchAll('FIND_IN_SET(`alias`, "' . $types . '")')->column('title');

            // Get title of last allowed element
            $last = array_pop($titles);

            // Flush failure msg
            jflush(false, 'Восстановление доступно только для полей типа ' . im($titles, ', ') . ' и ' . $last);
        }

        // If field , that we're trying to revert is a foreign key field - prevent reverting
        if (in($this->foreign('fieldId')->mode, 'hidden,readonly'))
            jflush(false, 'Восстановление недоступно для полей, являющихся внешними ключами');

        // Get field alias
        $field = Indi::model($this->entityId)->fields($this->fieldId)->alias;

        // Revert value
        $this->foreign('key')->assign([$field => $this->was])->save();
    }
}