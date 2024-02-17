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
            if (in($element, 'combo,radio,multicheck,icon')) view()->formCombo($fieldR->alias);

            // Prepare file-data for 'upload' element
            else if ($element == 'upload' && t()->row->abs($fieldR->alias)) {

                // Get file meta
                $file = t()->row->file($fieldR->alias);

                // If file exists, but shading is enabled for this field in demo-mode
                if ($file->src && $fieldR->param('shade') && Indi::demo(false)) {

                    // Spoof file src with I_PRIVATE_DATA
                    t()->row->{$fieldR->alias} = $file->src = I_PRIVATE_DATA;
                }

                // Assign into view. todo: rename 'view' to 'render'
                t()->row->view($fieldR->alias, $file);
            }
        }

        // Return parent's return-value
        return parent::render();
    }
}