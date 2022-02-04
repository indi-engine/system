<?php
class Indi_View_Helper_Admin_SiblingCombo extends Indi_View_Helper_Admin_FormCombo{
    public $type = 'sibling';
    public $context = 'top.window';
    public function siblingCombo(){

        $order = t()->scope->ORDER;

        if (is_array($order) && count($order) > 1) {
            //$this->comboDataOrderColumn = $order;
        } else {
            if (is_array($order)) $order = array_pop($order);
            $order = array_shift(explode(', `', $order));
            $this->comboDataOrderDirection = array_pop(explode(' ', $order));
            $this->comboDataOrderColumn = trim(preg_replace('/ASC|DESC/', '', $order), ' `');
            if (preg_match('/\(/', $order)) $this->comboDataOffset = Indi::uri('aix') - 1;
        }

        return parent::formCombo('sibling');
    }

    public function getSelected() {

        // If current row does not exist, combo will use field's default value as selected value
        if ($this->getRow()->id) $selected = $this->getRow()->id;

        // Return
        return $selected;
    }

    public function getField($name) {
        $pseudoFieldR = m('Field')->new();
        $pseudoFieldR->entityId = t()->section->entityId;
        $pseudoFieldR->alias = $name;
        $pseudoFieldR->storeRelationAbility = 'one';

        if (($groupFieldId = t()->section->groupBy)
            && ($groupFieldR = t()->fields->gb($groupFieldId)))
            $pseudoFieldR->param('groupBy', $groupFieldR->alias);

        $pseudoFieldR->elementId = 23;
        $pseudoFieldR->columnTypeId = 3;
        //$pseudoFieldR->defaultValue = t()->row->id;
        $pseudoFieldR->relation = t()->section->entityId;
        $pseudoFieldR->filter = t()->scope->WHERE;
        $pseudoFieldR->ignoreAlternate = true;

        return $pseudoFieldR;
    }

    public static function createPseudoFieldR($name, $entityId, $filter) {
        $pseudoFieldR = m('Field')->new();
        $pseudoFieldR->entityId = $entityId;
        $pseudoFieldR->alias = $name;
        $pseudoFieldR->storeRelationAbility = 'one';
        $pseudoFieldR->elementId = 23;
        $pseudoFieldR->columnTypeId = 3;
        //$pseudoFieldR->defaultValue = t()->row->id;
        $pseudoFieldR->relation = $entityId;
        $pseudoFieldR->filter = $filter;
        return $pseudoFieldR;
    }

    public function getRow(){
        return t()->row;
    }

    public function extjs($options) {
        $this->getRow()->view($this->field->alias, [
            'subTplData' => [
                'attrs' => $this->attrs,
                'pageUpDisabled' => $this->getRow()->id ? 'false' : 'true',
                'selected' => self::detectColor($this->selected)
            ],
            'store' => $options,
            'field' => $this->field->toArray()
        ]);
    }
}