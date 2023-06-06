<?php
class Indi_Controller_Admin_Exportable extends Indi_Controller_Admin {

    /**
     * @return string
     */
    protected function promptForCertainFields() : string {

        // Create phantom `certain` field and append to fields list
        m()->fields()->append([
            'alias' => 'certain',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'storeRelationAbility' => 'many',
            'relation' => 'field',
            'filter' => '`entityId` = "' . m()->id() . '"',
            'mode' => 'regular',
            'defaultValue' => ''
        ]);

        // Setup empty value
        t()->row->certain = '';

        // Build config for langId-combo
        $combo = ['width' => 400] + t()->row->combo('certain');

        // Prompt for source language
        $prompt = $this->prompt('Если нужно экспортировать только некоторые поля - выберите их', [$combo]);

        // Check prompt data
        $_ = jcheck(['certain' => ['rex' => 'int11list', 'key' => 'field*']], $prompt);

        // Get explicit list of certain props to be exported, or null if all props and and nested records should be exported
        return $_['certain'] ? $_['certain']->fis('alias') : '';
    }

    /**
     * Flush creation expression for selected entries, to be applied on another project running on Indi Engine
     */
    public function exportAction() {

        // Prompt for certain fields to be exported
        $certain = $this->promptForCertainFields();

        // For each row get export expression
        $php = []; foreach (t()->rows as $row) $php []= $row->export($certain);

        // Flush
        jtextarea(true, im($php, "\n"));
    }
}