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
            $queueTaskR = Indi::model('QueueTask')->createRow(array(
                'title' => 'L10n_' . array_pop(explode('_', get_class($this))),
                'params' => json_encode($params),
                'queueState' => $params['toggle'] == 'n' ? 'noneed' : 'waiting'
            ), true);

            // Save `queueTask` entries
            $queueTaskR->save();

        // Else assume it's an ID of a field, that we need to detect WHERE clause for
        } else $this->fieldId = $params;

        // If $this->fieldId prop is set, it means that we're here
        // because of Indi_Queue_L10n_FieldToggleL10n->getFractionChunkWHERE() call
        // so our aim here to obtain WHERE clause for certain field's chunk,
        // and appendChunk() call will return WHERE clause rather than `queueChunk` instance
        if ($this->fieldId) {
            if ($fieldR_certain = m('Field')->fetchRow($this->fieldId))
                return $this->appendChunk($queueTaskR, $fieldR_certain->foreign('entityId'), $fieldR_certain);

        // Foreach `entity` entry, having `system` = "n" (e.g. project's custom entities)
        // Foreach `field` entry, having `l10n` = "y" - append chunk
        } else foreach (Indi::model('Entity')->fetchAll('`system` = "n"') as $entityR)
            foreach ($entityR->nested('field', ['where' => '`l10n` = "y"']) as $fieldR)
                $this->appendChunk($queueTaskR, $entityR, $fieldR);
    }
}