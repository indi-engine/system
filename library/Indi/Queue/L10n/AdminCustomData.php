<?php
class Indi_Queue_L10n_AdminCustomData extends Indi_Queue_L10n_AdminUi {

    /**
     * Create queue chunks, or obtain WHERE clause for specific field
     *
     * @param array|int $params
     */
    public function chunk($params) {

        // If $params arg is an array
        if (is_array($params)) {

            // Create `queueTask` entry
            $queueTaskR = m('QueueTask')->new([
                'title' => 'L10n_' . array_pop(explode('_', get_class($this))),
                'params' => json_encode($params),
                'queueState' => $params['toggle'] == 'n' ? 'noneed' : 'waiting'
            ]);

            // Save `queueTask` entries
            $queueTaskR->save();

        // Else assume it's an ID of a field, that we need to detect WHERE clause for
        } else $this->fieldId = $params;

        // If $this->fieldId prop is set, it means that we're here
        // because of Indi_Queue_L10n_FieldToggleL10n->getFractionChunkWHERE() call
        // so our aim here to obtain WHERE clause for certain field's chunk,
        // and appendChunk() call will return WHERE clause rather than `queueChunk` instance
        if ($this->fieldId) {
            if ($fieldR_certain = m('Field')->row($this->fieldId))
                return $this->appendChunk($queueTaskR, $fieldR_certain->foreign('entityId'), $fieldR_certain);

        // Foreach `entity` entry, having `fraction` = "custom" (e.g. project's custom entities)
        // Foreach `field` entry, having `l10n` = "y" - append chunk
        } else {

            // Create chunks
            foreach (m('Entity')->all('`fraction` = "custom" OR `table` = "changeLog"', '`table` ASC') as $entityR) {

                /**
                 * WHERE clause for `changeLog` values
                 */
                if ($entityR->table == 'changeLog') {

                    // Get distinct `fieldId`-values
                    $fieldIds = im(db()->query('SELECT DISTINCT `fieldId` FROM `changeLog`')->col());

                    // Collect ids of applicable fields
                    $fieldIdA = [];
                    foreach (m('Field')->all(['`id` IN (0' . rif($fieldIds, ',$1') . ')',
                        '(`l10n` = "y" OR `storeRelationAbility` != "none")'
                    ]) as $fieldR)
                        if ($fieldR->l10n == 'y' || $fieldR->rel()->titleField()->l10n == 'y')
                            $fieldIdA []= $fieldR->id;

                    // Prepend `fieldId`-clause
                    $where = '`fieldId` IN (0' . rif(im($fieldIdA), ',$1') . ')';

                // Else no WHERE clause should be used
                } else {
                    $where = false;
                }

                // Foreach field - append chunk
                foreach ($entityR->nested('field', ['where' => '`l10n` = "y" AND IFNULL(`relation`, 0) != "6"']) as $fieldR)
                    $this->appendChunk($queueTaskR, $entityR, $fieldR, $where ? [$where] : []);
            }

            // Order chunks to be sure that all dependen fields will be processed after their dependencies
            $this->orderChunks($queueTaskR->id);
        }
    }
}