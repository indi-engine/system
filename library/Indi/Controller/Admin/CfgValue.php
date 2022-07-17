<?php
class Indi_Controller_Admin_CfgValue extends Indi_Controller_Admin_Exportable {

    /**
     * Disable action/fields depending on non-yet existing classroom's props
     */
    public function adjustCreatingRowAccess(&$row) {

        //
        if (uri()->action != 'form') return;

        // Preliminary prompt for cfgField
        $data = $this->prompt(I_SELECT_CFGFIELD, [['fieldLabel' => ''] + $row->combo('cfgField')]);

        // Check date
        jcheck(['cfgField' => ['req' => true, 'rex' => 'int11', 'key' => 'field']], $data);

        // Setup `cfgField` prop
        $row->cfgField = $data['cfgField'];

        // Setup default value
        $row->cfgValue = $row->foreign('cfgField')->defaultValue;

        // Make sure
        t()->fields->field('cfgField')->filter = '`id` = "' . $row->cfgField . '"';

        // Prepare field for spoofing `cfgValue` field
        $gen = $row->foreign('cfgField')->set([
            'filter' => '`entityId` = "' . $row->foreign('fieldId')->relation . '"',
            'entityId' => t()->section->entityId,
            'alias' => 'cfgValue',
            'title' => t()->fields->field('cfgValue')->title
        ]);

        // Spoof `cfgValue` field
        t()->fields->exclude('cfgValue', 'alias')->append($gen);
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

        //
        if (!$row->cfgField) return;

        // Prepare field for spoofing `cfgValue` field
        $gen = $row->foreign('cfgField')->set([
            'filter' => '`entityId` = "' . $row->foreign('fieldId')->relation . '"',
            'entityId' => t()->section->entityId,
            'alias' => 'cfgValue'
        ]);

        //
        if (uri()->action == 'form') $gen->title = t()->fields->field('cfgValue')->title;

        // Spoof `cfgValue` field
        t()->fields->exclude('cfgValue', 'alias')->append($gen);

        //
        $this->appendDisabledField('cfgField', true);
    }
}