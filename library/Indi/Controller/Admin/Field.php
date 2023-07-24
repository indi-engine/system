<?php
class Indi_Controller_Admin_Field extends Indi_Controller_Admin_Exportable {

    /**
     * Action function is redeclared to provide a strip hue part from $this->row->defaultValue
     */
    public function formAction() {

        // If $this->row->defaultValue is a color in format 'hue#rrggbb'
        if (preg_match(Indi::rex('hrgb'), $this->row->defaultValue))

            // Strip hue part from that color, for it to be displayed in form without hue
            $this->row->modified('defaultValue', substr($this->row->defaultValue, 3));

        // Disable `entry` field, as it's not applicable here
        $this->appendDisabledField('entry');

        // Default form action
        parent::formAction();
    }

    /**
     * Change mode for selected fields
     */
    public function activateAction() {

        // Build combo config for that field
        $combo = ['fieldLabel' => '', 'allowBlank' => 0] + t()->row->combo('mode');

        // Get field title
        $title = mb_strtolower($this->row->field('mode')->title);

        // Show prompt and obtain data
        $prompt = $this->prompt(I_SELECT_PLEASE . ' ' . $title, [$combo]);

        // Save new mode
        foreach (t()->rows as $selected) $selected->set(['mode' => $prompt['mode']])->save();

        // Flush success
        jflush(true);
    }

    /**
     * Create `queueTask` entry
     *
     * @param $cell
     * @param $value
     */
    public function onBeforeCellSave($cell, $value) {

        // If $cell is not 'l10n' - skip
        if ($cell != 'l10n') return;

        // If current field depends on other fields - deny
        if (t()->row->nested('consider')->count()) jflush(false, I_L10N_TOOGLE_FIELD_DENIED);

        // Check if [lang]->gapi.key is given in application/config.ini
        if ($value == 'qy' && !ini('lang')->gapi->key) jflush(false, I_GAPI_KEY_REQUIRED);

        // If we're going to create queue task for turning selected language either On or Off
        if (in($value, 'qy,qn')) {

            // Ask whether we want to turn l10n On/Off,
            // or want to arrange value of `l10n` for it to match real situation.
            if ('no' == $this->confirm(__(
                I_L10N_TOGGLE_FIELD_Y . ' ' . I_L10N_TOGGLE_MATCH,
                mb_strtolower($value == 'qy' ? I_TOGGLE_Y : I_TOGGLE_N), t()->row->title, I_YES, I_NO), 'YESNOCANCEL'))
                return;

            // Else if we're going to setup fraction-status directly
        } else if ('ok' == $this->confirm(__(I_L10N_TOGGLE_FIELD_EXPL, t()->row->title, t()->row->enumset($cell, $value)), 'OKCANCEL'))
            return;

        // Applicable languages WHERE clause
        $langId_filter = '"y" IN (`' . im($fraction = ar(t()->row->l10nFraction()), '`, `') . '`)';

        // Create phantom `langId` field
        $langId_combo = m('Field')->new([
            'alias' => 'langId',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'storeRelationAbility' => 'one',
            'relation' => 'lang',
            'filter' => $langId_filter,
            'mode' => 'hidden',
            'defaultValue' => 0
        ]);

        // Append to fields list
        m()->fields()->append($langId_combo);

        // Set active value
        t()->row->langId = m('lang')->row($langId_filter, '`move`')->id;

        // Build config for langId-combo
        $combo = ['fieldLabel' => '', 'allowBlank' => 0] + t()->row->combo('langId');

        // Prompt for source language
        $prompt = $this->prompt(__($value == 'qy' ? I_L10N_TOGGLE_FIELD_LANG_CURR : I_L10N_TOGGLE_FIELD_LANG_KEPT, t()->row->title), [$combo]);

        // Check prompt data
        $_ = jcheck(['langId' => ['req' => true, 'rex' => 'int11', 'key' => 'lang']], $prompt);

        // Call toggleL10n method on Field_Row instance
        t()->row->toggleL10n($value, $_['langId']->alias, true);
    }

    /**
     * Disable `l10n` field
     *
     * @param Indi_Db_Table_Row $row
     */
    public function adjustCreatingRowAccess(Indi_Db_Table_Row $row) {
        $this->appendDisabledField('l10n');
    }

    /**
     * Disable `l10n` field, but keep hidden
     *
     * @param Indi_Db_Table_Row $row
     */
    public function adjustEditingRowAccess(Indi_Db_Table_Row $row) {
        $this->appendDisabledField('l10n', true);
    }
}