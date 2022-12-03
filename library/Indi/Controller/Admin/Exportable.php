<?php
class Indi_Controller_Admin_Exportable extends Indi_Controller_Admin {

    /**
     * Flush creation expression for selected entries, to be applied on another project running on Indi Engine
     */
    public function exportAction() {

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
        $certain = $_['certain'] ? $_['certain']->col('alias', ',') : '';

        // Declare array of ids of entries, that should be exported, and push main entry's id as first item
        $toBeExportedIdA[] = $this->row->id;

        // If 'others' param exists in $_POST, and it's not empty
        if ($otherIdA = ar(Indi::post()->others)) {

            // Unset invalid values
            foreach ($otherIdA as $i => $otherIdI) if (!(int) $otherIdI) unset($otherIdA[$i]);

            // If $otherIdA array is still not empty append it's item into $toBeExportedIdA array
            if ($otherIdA) $toBeExportedIdA = array_merge($toBeExportedIdA, $otherIdA);
        }

        // If scope's tree-flag is true
        if (t()->scope->tree) {

            // Get raw tree
            $tree = m()->fetchRawTree(t()->scope->ORDER, t()->scope->WHERE);

            // Pick tree if need
            if (t()->scope->WHERE) $tree = $tree['tree'];

            // Build ORDER clause, respecting the tree
            $order = 'FIND_IN_SET(`id`, "' . im(array_keys($tree)) . '")';

        // Else build ORDER clause using ordinary approach
        } else $order = is_array(t()->scope->ORDER) ? im(t()->scope->ORDER, ', ') : (t()->scope->ORDER ?: '');

        // Fetch rows that should be moved
        $toBeExportedRs = m()->all(['`id` IN (' . im($toBeExportedIdA) . ')', t()->scope->WHERE], $order);

        // For each row get export expression
        $php = []; foreach ($toBeExportedRs as $toBeExportedR) $php []= $toBeExportedR->export($certain);

        // Apply new index
        $this->setScopeRow(false, null, $toBeExportedRs->column('id'));

        // Flush
        jtextarea(true, im($php, "\n"));
    }
}