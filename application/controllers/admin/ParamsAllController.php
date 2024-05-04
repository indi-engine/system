<?php
class Admin_ParamsAllController extends Indi_Controller_Admin_CfgValue {

    /**
     * Apply special behaviour for entityId and fieldId fields
     * as we're unable to pick their values from upper trail items
     */
    public function adjustCreatingRowAccess(Indi_Db_Table_Row $row) {

        // For those two fields
        foreach (ar('entityId,fieldId') as $prop) {

            // Setup importantDespiteDisabled-flag
            m()->fields($prop)->importantDespiteDisabled = true;

            // Apply values if they're zero but given in $_POST
            if (t()->row->zero($prop))
                if ($value = Indi::post()->$prop)
                    t()->row->$prop = $value;
        }

        // Call parent
        $this->callParent();
    }

    /**
     * Ask user which cfgField is going to be added for which field in which entity.
     * We need to know that in advance as we have to prepare relevant fields configs
     *
     * @param $row
     * @return array[]|mixed
     */
    public function promptCfgField(Indi_Db_Table_Row $row) {

        // Shared cfg
        $sharedCfg = ['width' => 400];

        // Prompt for 3 fields, as we can't pick those from parent trail items
        $data = $this->prompt(I_SELECT_CFGFIELD, [
            $row->combo('entityId') + $sharedCfg,
            $row->combo('fieldId')  + $sharedCfg,
            $row->combo('cfgField') + $sharedCfg
        ]);

        // Check and assign data if valid
        $row->mcheck([
            'entityId,fieldId,cfgField' => ['req' => true, 'rex' => 'int11'],
            'entityId'                  => ['key' => 'entity'],
            'fieldId,cfgField'          => ['key' => 'field']
        ], $data);

        // Return data to be further processed
        return $data;
    }
}