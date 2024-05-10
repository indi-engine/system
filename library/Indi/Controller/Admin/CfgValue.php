<?php
class Indi_Controller_Admin_CfgValue extends Indi_Controller_Admin_Exportable {

    /**
     * Disable action/fields depending on non-yet existing classroom's props
     */
    public function adjustCreatingRowAccess(Indi_Db_Table_Row $row) {

        // Make sure all further code is reached for form-action only
        if (uri()->action !== 'form') return;

        // Prompt for necessary data
        $data = $this->promptCfgField($row);

        // Setup `cfgField` prop
        $row->cfgField = $data['cfgField'];

        // Setup default value
        $row->cfgValue = $row->foreign('cfgField')->defaultValue;

        // Make sure
        t()->fields->field('cfgField')->filter = '`id` = "' . $row->cfgField . '"';

        // Prepare field for spoofing `cfgValue` field
        $gen = $row->foreign('cfgField')->set([
            'entityId' => t()->section->entityId,
            'alias' => 'cfgValue',
            'title' => t()->fields->field('cfgValue')->title
        ]);

        // If combo data will contain fields list - make sure certain fields to be there only
        if ($gen->relation == m('field')->id())
            $gen->set('filter', '`entityId` = "' . $row->foreign('fieldId')->relation . '"');

        // Spoof `cfgValue` field
        t()->fields->exclude('cfgValue', 'alias')->append($gen);
    }

    /**
     * Ask user which cfgField is going to be added for the chosen field.
     * We need to know that in advance as we have to prepare relevant field config
     *
     * @param $row
     * @return array[]|mixed
     */
    public function promptCfgField(Indi_Db_Table_Row $row) {

        // Preliminary prompt for cfgField
        $data = $this->prompt(I_SELECT_CFGFIELD, [['fieldLabel' => ''] + $row->combo('cfgField')]);

        // Check date
        jcheck(['cfgField' => ['req' => true, 'rex' => 'int11', 'key' => 'field']], $data);

        // Return prompt data
        return $data;
    }


    /**
     * @param Indi_Db_Table_Row $row
     */
    public function adjustTrailingRowAccess(Indi_Db_Table_Row $row) {

        // Make `fieldId` field to be disabled but visible
        $this->appendDisabledField('fieldId', true);
    }

    /**
     * @param Indi_Db_Table_Row $row
     * @throws Exception
     */
    public function adjustExistingRowAccess(Indi_Db_Table_Row $row) {

        // Prepare field for spoofing `cfgValue` field
        $gen = $row->foreign('cfgField')->set([
            'entityId' => t()->section->entityId,
            'alias' => 'cfgValue'
        ]);

        // If combo data will contain fields list - make sure certain fields to be there only
        if ($gen->relation == m('field')->id())
            $gen->set('filter', '`entityId` = "' . $row->foreign('fieldId')->relation . '"');

        //
        if (uri()->action == 'form') $gen->title = t()->fields->field('cfgValue')->title;

        // Spoof `cfgValue` field
        t()->fields->exclude('cfgValue', 'alias')->append($gen);

        // Disable cfgField-field, but keep displayed in form
        $this->appendDisabledField('cfgField', true);
    }

    /**
     * Flush creation expression for selected entries, to be applied on another project running on Indi Engine
     */
    public function exportAction() {

        // For each row get export expression
        $php = []; foreach (t()->rows as $row) $php []= $row->export();

        // Flush
        jtextarea(true, im($php, "\n"));
    }
}