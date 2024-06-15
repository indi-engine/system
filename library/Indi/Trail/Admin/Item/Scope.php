<?php
class Indi_Trail_Admin_Item_Scope {

    /**
     * Steps-up index of a trail item, that current Scope object is related to
     *
     * @var int
     */
    protected $_level;

    /**
     * ORDER clause
     *
     * @var
     */
    public $ORDER;

    /**
     * WHERE clause
     *
     * @var
     */
    public $WHERE;

    /**
     * Index of last accessed row
     *
     * @var
     */
    public $aix;

    /**
     * Ids of other bulk-accessed rows
     *
     * @var
     */
    public $lastIds = [];

    /**
     * Json-encoded array of filters, that were used to setup scope bounds
     *
     * @var string
     */
    public $filters = '[]';

    /**
     * @var string
     */
    public $filterOwner = '';

    /**
     * Number of found rows within current scope
     *
     * @var
     */
    public $found;

    /**
     * Scope identifier
     *
     * @var
     */
    public $hash;

    /**
     * Keyword, that was used to setup scope bounds
     *
     * @var
     */
    public $keyword;

    /**
     * Json-encoded array of ORDER clauses. Each ORDER clause is represented by a 'property' and 'direction' params
     *
     * @var string
     */
    public $order = '[]';

    /**
     * Number of page within all results, related to current scope
     *
     * @var
     */
    public $page;

    /**
     * Primary WHERE clause. The difference from $this->WHERE is that $this->primary does not contain WHERE clauses,
     * related to filters and keyword search
     *
     * @var
     */
    public $primary;

    /**
     * Index of parent-level row within it's scope (mean parent-level scope)
     *
     * @var
     */
    public $upperAix;

    /**
     * Primary hash of parent-level scope. Used for jumping from current scope to parent scopes
     *
     * @var
     */
    public $upperHash;

    /**
     * Constructor
     *
     * @param $level
     */
    public function __construct($level) {
        $this->_level = $level;
    }

    /**
     * Convert current scope object to array
     *
     * @return array
     */
    public function toArray() {
        $array = (array) $this;
        array_shift($array);
        return $array;
    }

    /**
     * Get/set the value of current scope's filter, identified by $alias
     *
     * @param $alias
     * @param $value
     * @return mixed
     */
    public function filter($alias = null, $value = null) {

        // Try to decode json-string, stored in $this->filters
        $filterA =  json_decode($this->filters, true);

        // If decoding was successful, and decode result is an array
        if (is_array($filterA))

            // If $alias argument is given
            if ($alias) {

                // Set $found flag to be false by default
                $found = false;

                // Foreach filter
                foreach ($filterA as $idx => &$filterI)

                    // If filter's key is $alias, remember index
                    if (key($filterI) == $alias && (($found = $idx) || true))

                        // If we're going to get the value - do it, else break
                        if (func_num_args() == 1) return current($filterI); else break;

                // If we're going to set the value
                if (func_num_args() > 1) {

                    // If value is not null - create/overwrite filter's value
                    if ($value !== null) $filterA[$found === false ? count($filterA) : $found][$alias] = $value;

                    // Else unset that filter
                    else if ($found !== false) unset ($filterA[$found]);

                    // Encode back to json
                    $this->filters = json_encode($filterA);
                }

            // Else
            } else {

                // Declare $assoc array
                $assoc = [];

                // Build simple associative array
                foreach ($filterA as $filterI) $assoc[key($filterI)] = current($filterI);

                // Return
                return $assoc;
            }

        // Return
        return [];
    }

    /**
     * Apply new values for current object's properties and update current object's version, stored in $_SESSION
     *
     * @param array $data
     */
    public function apply(array $data) {

        // If key 'primary' exists within $data, and if it's value is an array
        // - we convert it to string by imploding by ' AND '
        if (array_key_exists('primary', $data) && is_array($data['primary']))
            $data['primary'] = implode(' AND ', $data['primary']);

        // Setup new values for internal properties
        foreach ($data as $prop => $value) $this->$prop = $value;

        // Update session
        $_SESSION['indi']['admin'][t($this->_level)->section->alias][$this->hash]
            = is_array($_SESSION['indi']['admin'][t($this->_level)->section->alias][$this->hash] ?? null)
                ? array_merge($_SESSION['indi']['admin'][t($this->_level)->section->alias][$this->hash], $data)
                : $this->toArray();

        // Update current object, for case if at the moment of apply() call there have already been some
        // related scope data in $_SESSION, so we replicate session scope data to current object
        foreach ($_SESSION['indi']['admin'][t($this->_level)->section->alias][$this->hash] as $prop => $value)
            $this->$prop = $value;

        if ($this->hash)t($this->_level)->section->primaryHash = $this->hash;
        if ($this->aix) t($this->_level)->section->rowIndex = $this->aix;

        if ($this->upperHash && $this->upperAix && t($this->_level+1)->scope)
            t($this->_level+1)->scope->apply(['hash' => $this->upperHash, 'aix' => $this->upperAix]);
    }
}
