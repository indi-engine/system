<?php
class Indi_View_Helper_Admin_SiblingCombo extends Indi_View_Helper_Admin_FormCombo{
    public $type = 'sibling';
    public $context = 'top.window';
    public function siblingCombo(){

        $order = Indi::trail()->scope->ORDER;

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
        $pseudoFieldR = Indi::model('Field')->createRow();
        $pseudoFieldR->entityId = Indi::trail()->section->entityId;
        $pseudoFieldR->alias = $name;
        $pseudoFieldR->storeRelationAbility = 'one';

        if (($groupFieldId = Indi::trail()->section->groupBy)
            && ($groupFieldR = Indi::trail()->fields->gb($groupFieldId)))
            $pseudoFieldR->param('groupBy', $groupFieldR->alias);

        $pseudoFieldR->elementId = 23;
        $pseudoFieldR->columnTypeId = 3;
        //$pseudoFieldR->defaultValue = Indi::trail()->row->id;
        $pseudoFieldR->relation = Indi::trail()->section->entityId;
        $pseudoFieldR->filter = Indi::trail()->scope->WHERE;
        $pseudoFieldR->ignoreAlternate = true;

        return $pseudoFieldR;
    }

    public static function createPseudoFieldR($name, $entityId, $filter) {
        $pseudoFieldR = Indi::model('Field')->createRow();
        $pseudoFieldR->entityId = $entityId;
        $pseudoFieldR->alias = $name;
        $pseudoFieldR->storeRelationAbility = 'one';
        $pseudoFieldR->elementId = 23;
        $pseudoFieldR->columnTypeId = 3;
        //$pseudoFieldR->defaultValue = Indi::trail()->row->id;
        $pseudoFieldR->relation = $entityId;
        $pseudoFieldR->filter = $filter;
        return $pseudoFieldR;
    }

    public function getRow(){
        return Indi::trail()->row;
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