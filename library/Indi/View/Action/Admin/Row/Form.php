<?php
class Indi_View_Action_Admin_Row_Form extends Indi_View_Action_Admin_Row {

    /**
     * @return string
     */
    public function render() {

        // Prepare file-data and combo-data only for visible fields
        foreach (t()->fields as $fieldR) {

            // Skip hidden fields
            if ($fieldR->mode == 'hidden') continue;

            // Skip fields, that are using hidden elements
            if ($fieldR->foreign('elementId')->hidden) continue;

            // Element's alias shortcut
            $element = $fieldR->foreign('elementId')->alias;

            // Prepare combo-data for 'combo', 'radio' and 'multicheck' elements
            if (in($element, 'combo,radio,multicheck')) Indi::view()->formCombo($fieldR->alias);

            // Prepare file-data for 'upload' element
            else if ($element == 'upload' && t()->row->abs($fieldR->alias))
                t()->row->view($fieldR->alias, t()->row->file($fieldR->alias));
        }

        // Return parent's return-value
        return parent::render();
    }
}