<?php
class Field_Base extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Field_Row';

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Field_Rowset';

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     * @var array
     */
    protected $_evalFields = array('defaultValue', 'filter');

    /**
     * Default order for combo data
     *
     * @var string
     */
    public $comboDataOrder = 'move';

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = array('field' => 'entityId');

    /**
     * Create Indi_Db_Table_Rowset object with some data, if passed
     *
     * @param array $input
     * @return mixed
     */
    public function createRowset($input = []) {

        // If no $input arg given - assume empty rowset
        if (!$input) $input['rows'] = [];

        // Get the type of construction
        $index = isset($input['rows']) ? 'rows' : 'data';

        // Prepare data for Indi_Db_Table_Rowset object construction
        $data = [
            'table'   => $this->_table,
            $index     => is_array($input[$index]) ? $input[$index] : [],
            'rowClass' => $this->_rowClass,
            'found'=> isset($input['found'])
                ? $input['found']
                : (is_array($input[$index]) ? count($input[$index]) : 0)
        ];

        // Provide special 'aliases' and 'ids' construction property to be set up
        foreach (['aliases' => 'alias', 'ids' => 'id'] as $ctor => $prop) {
            if ($input[$ctor] && is_array($input[$ctor])) $data[$ctor] = $input[$ctor];
            else foreach($input[$index] as $item) {
                $data[$ctor][$index == 'rows' ? $item->$prop : $item[$prop]] = count($data[$ctor] ?: []);
            }
        }

        // Construct and return Indi_Db_Table_Rowset object
        return new $this->_rowsetClass($data);
    }
}