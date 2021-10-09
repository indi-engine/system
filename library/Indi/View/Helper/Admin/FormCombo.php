<?php
class Indi_View_Helper_Admin_FormCombo {

    /**
     * This var is used for html elements css class names building
     * @var string
     */
    public $type = 'form';

    /**
     * Additional WHERE clause, that will be used while options data retrieving
     *
     * @var null
     */
    public $where = null;

    /**
     * Field_Row instance, that current combo is linked to
     *
     * @var
     */
    public $field;

    /**
     * Context of combo initialization javascript execution
     *
     * @var string
     */
    public $context = 'window';

    /**
     * Whether or not at least one option have color box. This property is used in *_FilterCombo helper
     *
     * @var bool
     */
    public $hasColorBox = false;

    /**
     * Number of characters, that longest option title consists from. Value of this property will be set while
     * iteration over combo data rowset
     *
     * @var int
     */
    public $titleMaxLength = 0;

    /**
     * Maximun level level of depth, within combo data rowset, of course in case if combo data rowset was fetched from
     * tree-like entity
     *
     * @var int
     */
    public $titleMaxIndent = 0;

    /**
     * Declared for availablity in child classes
     *
     * @var bool
     */
    public $pageUpDisabled = false;

    /**
     * Setup row object for combo
     *
     * @return Indi_Db_Table_Row
     */
    public function getRow(){
        return Indi::view()->row;
    }

    /**
     * Default value determining is extracted from raw code to seperate function for inherited classes
     * being able to setup a different logic for dealing with default values
     *
     * @return mixed
     */
    public function getDefaultValue() {
        return $this->getRow()->compileDefaultValue($this->name);
    }

    /**
     * Setup field
     *
     * @param $name
     * @return Field_Row
     */
    public function getField($name) {
        return Indi::trail()->model->fields($name);
    }

    /**
     * Get selected value
     *
     * @return mixed
     */
    public function getSelected() {

        // If current row does not exist, combo will use field's default value as selected value
        if ($this->getRow()->id || strlen($this->getRow()->{$this->name})) {
            $selected = $this->getRow()->{$this->name};
        } else {
            $selected = $this->getDefaultValue();
        }

        return $selected;
    }

    /**
     * Builds the combo
     *
     * @param $name
     * @return string
     */
    public function formCombo($name) {

        // Set name
        $this->name = $name;

        // Get field
        $this->field = $this->getField($name);

        // Get params
        $params = $this->field->params;

        // Get selected
        $selected = $this->getSelected();

        // Get initial combo options rowset
        $comboDataRs = $this->getRow()->getComboData($name, null, $selected, null,
            $this->where, $this->field, $this->comboDataOrderColumn,
            $this->comboDataOrderDirection, $this->comboDataOffset, $this->getConsistence(), $this->isMultiSelect());

        // Prepare combo options data
        $comboDataA = $comboDataRs->toComboData($params, $this->ignoreTemplate);

        $options = $comboDataA['options'];
        $this->titleMaxLength = $comboDataA['titleMaxLength'];
        $this->titleMaxIndent = $comboDataA['titleMaxIndent'];
        $this->hasColorBox = $comboDataA['hasColorBox'];
        $keyProperty = $comboDataA['keyProperty'];

        // If combo is boolean
        if ($this->field->storeRelationAbility == 'none' && $this->field->columnTypeId == 12) {

            // Setup a key
            if ($this->getRow()->$name) {
                $key = $this->getRow()->$name;
            } else if ($comboDataRs->enumset && $this->type == 'form') {
                $key = key($options);
            } else {
                $key = $this->getDefaultValue();
            }

            // Setup an info about selected value
            if (strlen($key)) {
                $selected = array(
                    'title' => $options[$key]['title'],
                    'value' => $key
                ); 
            } else {
                $selected = array(
                    'title' => null,
                    'value' => null
                );            
            }

        // Else if current field column type is ENUM or SET, and current row have no selected value, we use first
        // option to get default info about what title should be displayed in input keyword field and what value
        // should have hidden field
        } else if ($this->field->storeRelationAbility == 'one' && ($this->filter ? !$this->filter->any() : true)) {

            // Setup a key
            if (($this->getRow()->id && !$comboDataRs->enumset) || !is_null($this->getRow()->$name)) {

                if (preg_match('/Sibling/', get_class($this))) {
                    $key = $this->getRow()->id;
                } else {
                    $key = $this->getRow()->$name;
                }
            } else if ($comboDataRs->enumset && $this->type == 'form') {
                $key = key($options);
            } else {
                $key = $this->getDefaultValue();
            }

            // Setup an info about selected value
            $selected = array(
                'title' => $options[$key]['title'],
                'value' => $key
            );

            if ($options[$key]['system']['boxColor']) $selected['boxColor'] = $options[$key]['system']['boxColor'];

            // Setup css color property for input, if original title of selected value contained a color definition
            if ($options[$selected['value']]['system']['color'])
                $selected['style'] =  ' style="color: ' . $options[$selected['value']]['system']['color'] . ';"';

            // Set up html attributes for hidden input, if optionAttrs param was used
            if ($options[$selected['value']]['attrs']) {
                $attrs = array();
                foreach ($options[$selected['value']]['attrs'] as $k => $v) {
                    $attrs[] = $k . '="' . $v . '"';
                }
                $attrs = ' ' . implode(' ', $attrs);
            }

        // Else if combo is mulptiple
        } else if ($this->field->storeRelationAbility == 'many' || ($this->filter && $this->filter->any())) {

            // Set value for hidden input
            $selected = array('value' => $selected);

            // Set up html attributes for hidden input, if optionAttrs param was used
            $exploded = explode(',', $selected['value']);
            $attrs = array();
            for ($i = 0; $i < count($exploded); $i++) {
                if ($options[$exploded[$i]]['attrs']) {
                    foreach ($options[$exploded[$i]]['attrs'] as $k => $v) {
                        $attrs[] = $k . '-' . $exploded[$i] . '="' . $v . '"';
                    }
                }
            }
            $attrs = ' ' . implode(' ', $attrs);
        }

        // Prepare options data
        $options = array(
            'ids' => array_keys($options),
            'data' => array_values($options),
            'found' => $comboDataRs->found(),
            'page' => $comboDataRs->page(),
            'enumset' => $comboDataRs->enumset,
            'titleMaxLength' => $this->titleMaxLength
        );

        // Setup tree flag in entity has a tree structure
        if ($comboDataRs->table() && $comboDataRs->model()->treeColumn()) $options['tree'] = true;

        // Setup groups for options
        if ($comboDataRs->optgroup) $options['optgroup'] = $comboDataRs->optgroup;

        // Setup option height. Current context does not have a $this->ignoreTemplate member,but inherited class *_FilterCombo
        // does, so option height that is applied to form combo will not be applied to filter combo, unless $this->ignoreTemplate
        // in *_FilterCombo is set to false
        $options['optionHeight'] = $params['optionHeight'] && !$this->ignoreTemplate ? $params['optionHeight'] : 14;

        // Setup groups for options
        if ($comboDataRs->optionAttrs) $options['attrs'] = $comboDataRs->optionAttrs;

        // If combo-field of current row currently does not have a value, we should disable paging up
        $this->pageUpDisabled = $this->getRow()->$name ? 'false' : 'true';

        // Assign local variables to public class variables
        foreach (array('name', 'selected', 'params', 'attrs', 'comboDataRs', 'keyProperty') as $var) $this->$var = $$var;

        // Prepare a data object containing all involved info
        $this->extjs($options);

        // Return itself
        return $this;
    }

    /**
     * Empty function here, but non-empty in child class Indi_View_Helper_Admin_FilterCombo.
     * Look for description/purposes in that child class
     */
    public function getConsistence() {

    }

    /**
     * @return bool
     */
    public function isMultiSelect() {
        return $this->field->storeRelationAbility == 'many';
    }

    /**
     * Build a config-array for combo, and assign that array into combo's view prop
     *
     * @param $options
     */
    public function extjs($options) {

        // Prepare view params
        $view = array(
            'subTplData' => array(
                'attrs' => $this->attrs,
                'pageUpDisabled' => $this->pageUpDisabled,
            ),
            'store' => $options
        );

        // Setup view data,related to currenty selected value(s)
        if ($this->isMultiSelect()) {
            $view['subTplData']['selected'] = $this->selected;
            foreach($this->comboDataRs->selected as $selectedR) {
                $item = self::detectColor(array('title' => ($_tc = $this->field->param('titleColumn')) ? $selectedR->$_tc : $selectedR->title()));
                $item['id'] = $selectedR->{$this->keyProperty};
                $view['subTplData']['selected']['items'][] = $item;
            }
        } else {
            $view['subTplData']['selected'] = self::detectColor($this->selected);
        }

        // Assign view params
        $this->getRow()->view($this->field->alias, $view);
    }

    /**
     * Check if option contain a color definition, and if so, extract that color, build color box (in some cases) and
     * prepare some auxiliary data
     *
     * @param $option
     * @return array
     */
    public static function detectColor($option) {

        // Color detection in different places within one certain option
        ($v = preg_match('/^[0-9]{3}(#[0-9a-fA-F]{6})$/', is_string($option['value']) ? $option['value'] : '', $color)) ||
        ($t = preg_match('/^[0-9]{3}(#[0-9a-fA-F]{6})$/', $option['title'], $color)) ||
        ($s = preg_match('/color[:=][ ]*[\'"]{0,1}([#a-zA-Z0-9]+)/i', $option['title'], $color)) ||
        ($b = preg_match('/<span[^>]*\sclass="[^"]*i-color-box[^"]*"[^>]*\sstyle="background: ([#0-9a-zA-Z]{3,20});[^"]*"[^>]*>/', $option['title'], $color)) ||
        ($b = preg_match('/<span[^>]*\sclass="[^"]*i-color-box[^"]*"[^>]*\sstyle="background: (url\(.*\).*?);[^"]*"[^>]*>/', $option['title'], $color));

        // If color was detected somewhere
        if ($v || $t || $s || $b || $option['boxColor']) {

            // Setup color
            $option['color'] = $color[1] ? $color[1] : $option['boxColor'];

            // If color was detected in 'title' property, and found as 'color' or 'background' css property
            // - strip tags from title
            if ($s || $b) $option['title'] = strip_tags($option['title']);

            // If color was detected in 'title' property as 'color' css property
            if ($s) {

                // If there is no 'style' property within $option variable -
                // set it as 'color' and 'font' css properties specification
                if (!$option['style']) $option['style'] = ' style="color: ' . $option['color'] . '; ' . $option['font'] . '"';

            // Else if color was not detected in 'title' property as 'color' css property - we assume that color should
            // be represented as a color-box
            } else {

                // If color was detected in 'value' property of $option variable - set 'input' property as a purified color
                if ($t) $option['input'] = $option['color'];

                // Setup color box
                $option['box'] = '<span class="i-combo-color-box" style="background: ' . $option['color'] . ';"></span>';
            }
        }

        // Append font specification, as there might be case when fonts specifications, mentioned in css files - do not
        // applied at the moment of indi.combo.js maintenance, because indi.combo.js may run earlier than css files
        // loaded and this may cause wrong calculation of widths of selected options in multiple-combos, as by default
        // options's text is in 'Times New Roman' font, which have letter widths, differerent from other fonts
        //$option['font'] = ' font-family: tahoma, arial, verdana, sans-serif;';

        // Return
        return $option;
    }
}