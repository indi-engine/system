<?php
/**
 * Special controller for deal with changelog entries
 */
class Indi_Controller_Admin_ChangeLog extends Indi_Controller_Admin {

    /**
     * Replace view type for 'index' action from 'grid' to 'changeLog'
     */
    public function adjustActionCfg() {
        $this->actionCfg['view']['index'] = 'changeLog';
    }

    /**
     * Make sure filters will be initially consistent (e.g before store is loaded)
     * to that their widths from jumping where it is not needed
     */
    public function adjustTrail() {

        // Here we setup `filter` prop for several fields to ensure that
        // filters linked to those fields won't use extra width
        foreach (ar('entityId,adminId,entryId') as $field)
            if ($values = db()->query("SELECT DISTINCT `$field` FROM `changeLog`")->col())
                m()->fields($field)->filter = '`id` IN (' . im($values) . ') ';

        // Append `was` and `now` columns as they weren't added at the stage
        // of grid columns autocreation after current section entry was created
        $this->inclGridProp('was,now');

        // Exclude `monthId` grid columns
        t()->grid('monthId')->toggle('n');

        // If current changeLog-section is for operating on changeLog-entries,
        // nested under some single entry - exclude `entryId` grid column
        if (t(1)->section->entityId) t()->grid('entryId')->toggle('n');

        // Else force `fieldId`-filter's combo-data to be grouped by `entityId`
        else m()->fields('fieldId')->param('groupBy', 'entityId');

        // If Indi client app is used - make 'datetime' to be grouping field
        if (APP) t()->section->groupBy = 'datetime';

        // Make adminId and roleId columns hidden
        t()->grid('adminId,roleId')->toggle('h');

        // Make sure whole record will be reloaded if adminId, roleId or datetime is changed
        t()->grid('adminId,roleId,datetime')->rowReqIfAffected();

        // Set grid column titles
        m()->fields('fieldId')->title = I_CHANGELOG_FIELD;
        m()->fields('was')->title = I_CHANGELOG_WAS;
        m()->fields('now')->title = I_CHANGELOG_NOW;
    }

    /**
     * Implement special parentWHERE logic, that involves two db-columns instead of one
     *
     * @return null|string
     */
    public function parentWHERE() {

        // If current section does not have a parent section, or have, but is a root section - return
        if (!t(1)->section->sectionId) return;

        // Setup connector alias, which is always is 'entryId'
        $connectorAlias = 'entryId';

        // Get the connector value
        $connectorValue = uri('action') == 'index' ? uri('id') : Indi::parentId(t(1)->section->id);

        // Return clause
        return '`entityId` = "' . t(1)->section->entityId . '" AND `' . $connectorAlias . '` = "' . $connectorValue . '"';
    }

    /**
     * Adjust values, for 'adminId' and 'entryId' props
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Collect shaded fields
        if (($shade = []) || Indi::demo(false))
            foreach(m(t(1)->section->entityId)->fields() as $fieldR)
                if ($fieldR->param('shade'))
                    $shade[$fieldR->id] = true;

        // Set $entryId flag, indicating whether `entryId` column is used
        $entryId = $data && isset($data[0]['entryId']);

        // Adjust data
        for ($i = 0; $i < count($data); $i++) {

            // Build group title
            $data[$i]['_render']['datetime'] = $data[$i]['datetime']
                . rif($entryId, ' - ' . $data[$i]['entityId'] . ' » ' . $data[$i]['entryId'])
                . rif($data[$i]['adminId'], ' - ' . $data[$i]['adminId'] . ' [' . $data[$i]['roleId'] . ']');

            // Encode <iframe> tag descriptors into html entities
            $data[$i]['was'] = preg_replace('~(<)(/?iframe)([^>]*)>~', '&lt;$2$3&gt;', $data[$i]['was']);
            $data[$i]['now'] = preg_replace('~(<)(/?iframe)([^>]*)>~', '&lt;$2$3&gt;', $data[$i]['now']);

            // Unset props, that are not needed separately anymore
            unset($data[$i]['entryId'], $data[$i]['entityId'], $data[$i]['adminId'], $data[$i]['roleId']);

            // Shade private data
            if ($shade[$data[$i]['fieldId']]) {
                if ($data[$i]['was']) $data[$i]['was'] = I_PRIVATE_DATA;
                if ($data[$i]['now']) $data[$i]['now'] = I_PRIVATE_DATA;
            }
        }
    }

    /**
     * Revert back target entry's prop, that current `changeLog` entry is related to
     */
    public function revertAction() {

        // Demo mode
        Indi::demo();

        // Declare array of ids of entries, that should be moved, and push main entry's id as first item
        $toBeRevertedIdA[] = $this->row->id;

        // If 'others' param exists in $_POST, and it's not empty
        if ($otherIdA = ar(Indi::post()->others)) {

            // Unset unallowed values
            foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

            // If $otherIdA array is still not empty append it's item into $toBeRevertedIdA array
            if ($otherIdA) $toBeRevertedIdA = array_merge($toBeRevertedIdA, $otherIdA);
        }

        // Fetch rows that should be moved
        $toBeRevertedRs = m()->all([
            '`id` IN (' . im($toBeRevertedIdA) . ')', t()->scope->WHERE
        ]);

        // For each row
        foreach ($toBeRevertedRs as $toBeRevertedR) $toBeRevertedR->revert();

        // Apply new index
        $this->setScopeRow(false, null, $toBeRevertedRs->column('id'));

        // Flush success
        jflush(true, 'OK');
    }
}