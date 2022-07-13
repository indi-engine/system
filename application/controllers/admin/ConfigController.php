<?php
class Admin_ConfigController extends Indi_Controller_Admin {

    /**
     * Adjust wording for entries having 'check' as their control element
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {
        for ($i = 0; $i < count($data); $i++) {

            // Make 'defaultValue' and 'currenValue' grid columns have 'Yes' or 'No' wordings
            // instead of '1' and '0' in case if config control element's type is 'check'
            if ($data[$i]['$keys']['elementId'] == 9) {
                $data[$i]['currentValue'] = $data[$i]['currentValue'] ? I_YES : I_NO;
                $data[$i]['defaultValue'] = $data[$i]['defaultValue'] ? I_YES : I_NO;
            }
        }
    }

    /**
     * Ensure that full access will be provided only for admin users having Configurator role
     */
    public function adjustAccess() {

        // If current admin is not a Configurator
        if (admin()->roleId != 1) {

            // Deny config entries creation
            $this->deny('create');

            // Disable but make visible system fields
            $this->appendDisabledField('title,alias,elementId', true);
        }

        // Totally disable `expiryDurationStr` field
        $this->appendDisabledField('expiryDurationStr');
    }

    /**
     * Check that once `expiryType` is changed to 'temporary', `currentValue` should not be equal to `defaultValue`
     */
    public function preSave() {

        // If `expiryType` was not modified, or was, but new value for it is not 'temporary' - return
        if ($this->row->modified('expiryType') != 'temporary') return;

        // If `currentValue` is not equal to `defaultValue` - return
        if (!($this->row->currentValue == $this->row->defaultValue)) return;

        // If 'answer' is not a key within $_GET params
        if (!Indi::get('answer'))
            jconfirm('Вы выбрали временный срок действия для текущего значения параметра настройки,<br>но само текущее значение оставили '
                . ' соответствующим значению по умолчанию,<br>что по сути нивелирует данное действие. Продолжить?');

        // Else if $_GET's 'answer' param is 'cancel' - stop and do nothing
        else if (Indi::get('answer') == 'cancel') jflush(true);
    }
}