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
     * Here is the temporary solution for filters consistence
     */
    public function adjustTrail() {

        // Here we setup `filter` prop for `entityId` field, to ensure that filter-combo,
        // linked to `entityId` field won't use extra width
        if ($entityIdA = Indi::db()
            ->query('SELECT DISTINCT `entityId` FROM `changeLog`')
            ->fetchAll(PDO::FETCH_COLUMN, 0))
            Indi::trail()->model->fields('entityId')->filter = '`id` IN (' . im($entityIdA) . ') ';

        // Here we setup `filter` prop for `changerType` field, to ensure that filter-combo,
        // linked to `changerType` field won't use extra width
        if ($changerTypeA = Indi::db()
            ->query('SELECT DISTINCT `changerType` FROM `changeLog`')
            ->fetchAll(PDO::FETCH_COLUMN, 0))
            Indi::trail()->model->fields('changerType')->filter = '`id` IN (' . im($changerTypeA) . ') ';

        // Here we setup `filter` prop for `changerType` field, to ensure that filter-combo,
        // linked to `changerId` field won't use extra width
        if ($changerIdA = Indi::db()
            ->query('SELECT DISTINCT `changerId` FROM `changeLog`')
            ->fetchAll(PDO::FETCH_COLUMN, 0))
            Indi::trail()->model->fields('changerId')->filter = '`id` IN (' . im($changerIdA) . ') ';

        // Append `was` and `now` columns as they weren't added at the stage
        // of grid columns autocreation after current section entry was created
        $this->inclGridProp('was,now');

        // Exclude `changerType` and `monthId` grid columns
        $this->exclGridProp('changerType,monthId');

        // If current changeLog-section is for operating on changeLog-entries,
        // nested under some single entry - exclude `key` grid column
        if (Indi::trail(1)->section->entityId) $this->exclGridProp('key');

        // Else force `fieldId`-filter's combo-data to be grouped by `entityId`
        else Indi::trail()->model->fields('fieldId')->param('groupBy', 'entityId');

        // If Indi client app is used - make 'datetime' to be grouping field
        if (APP) t()->section->groupBy = 'datetime';

        // Set grid column titles
        t()->model->fields('fieldId')->title = I_CHANGELOG_FIELD;
        t()->model->fields('was')->title = I_CHANGELOG_WAS;
        t()->model->fields('now')->title = I_CHANGELOG_NOW;
    }

    /**
     * Implement special parentWHERE logic, that involves two db-columns instead of one
     *
     * @return null|string
     */
    public function parentWHERE() {

        // If current section does not have a parent section, or have, but is a root section - return
        if (!Indi::trail(1)->section->sectionId) return;

        // Setup connector alias, which is always is 'key'
        $connectorAlias = 'key';

        // Get the connector value
        $connectorValue = Indi::uri('action') == 'index'
            ? Indi::uri('id')
            : $_SESSION['indi']['admin']['trail']['parentId'][Indi::trail(1)->section->id];

        // Return clause
        return '`entityId` = "' . Indi::trail(1)->section->entityId . '" AND `' . $connectorAlias . '` = "' . $connectorValue . '"';
    }

    /**
     * Adjust values, for 'changerId' and 'key' props
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Collect shaded fields
        if (($shade = []) || Indi::demo(false))
            foreach(Indi::model(Indi::trail(1)->section->entityId)->fields() as $fieldR)
                if ($fieldR->param('shade'))
                    $shade[$fieldR->id] = true;

        // Set $key flag, indicating whether `key` column is used
        $key = $data && isset($data[0]['key']);

        // Adjust data
        for ($i = 0; $i < count($data); $i++) {

            // Build group title
            $data[$i]['_render']['datetime'] = $data[$i]['datetime']
                . rif($key, ' - ' . $data[$i]['entityId'] . ' » ' . $data[$i]['key'])
                . rif($data[$i]['changerId'], ' - ' . $data[$i]['changerId'] . ' [' . $data[$i]['profileId'] . ']');

            // Encode <iframe> tag descriptors into html entities
            $data[$i]['was'] = preg_replace('~(<)(/?iframe)([^>]*)>~', '&lt;$2$3&gt;', $data[$i]['was']);
            $data[$i]['now'] = preg_replace('~(<)(/?iframe)([^>]*)>~', '&lt;$2$3&gt;', $data[$i]['now']);

            // Unset props, that are not needed separately anymore
            unset($data[$i]['key'], $data[$i]['entityId'], $data[$i]['changerId'], $data[$i]['profileId']);

            // Shade private data
            if ($shade[$data[$i]['$keys']['fieldId']]) {
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
        $toBeRevertedRs = Indi::trail()->model->fetchAll([
            '`id` IN (' . im($toBeRevertedIdA) . ')', Indi::trail()->scope->WHERE
        ]);

        // For each row
        foreach ($toBeRevertedRs as $toBeRevertedR) $toBeRevertedR->revert();

        // Apply new index
        $this->setScopeRow(false, null, $toBeRevertedRs->column('id'));

        // Flush success
        jflush(true, 'OK');
    }
}