<?php
class Param_Rowset extends Indi_Db_Table_Rowset {

    /**
     * Render title for cfgValue, if it's a reference
     *
     * @param string $fields Comma-separated list of field names
     * @param array $renderCfg
     * @return array
     * @throws Exception
     */
    public function toGridData($fields, $renderCfg = []) {

        // Get initial grid data
        $data = $this->callParent();

        // Foreach grid data item
        foreach ($data as $idx => $item) {

            // Get cfgField
            $cfgField = m('field')->row($item['cfgField']);

            // If it's not a foreign key cfgField - skip
            if ($cfgField->storeRelationAbility === 'none') continue;

            // If no relation found - skip
            if (!$rel = $cfgField->rel()) continue;

            // Prepare WHERE clause
            $where = $rel->table() === 'enumset'
                ? "`fieldId` = '{$cfgField->id}' AND FIND_IN_SET(`alias`, '{$item['cfgValue']}')"
                : $item['cfgValue']; // will be converted into '`id` IN ($item['cfgValue'])' if it is an int11list

            // Setup title
            $data[$idx]['_render']['cfgValue'] = $where ? $rel->all($where)->col('title', ', ') : '';
        }

        // Return grid data
        return $data;
    }
}