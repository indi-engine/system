<?php
class Indi_Db_Table_Row implements ArrayAccess
{
    /**
     * Table name of table, that current row is related to
     *
     * @var string
     */
    protected $_table = '';

    /**
     * Original data. If entity has localized fields,
     * their values will be only for current language
     *
     * @var array
     */
    protected $_original = [];

    /**
     * Modified data, used to construct correct sql-query for INSERT and UPDATE statements
     *
     * @var array
     */
    protected $_modified = [];

    /**
     * Full original data for localized fields. Looks like: array(
     *     'title' => array(
     *         'ru' => 'Здравствуйте',
     *         'en' => 'Hello'
     *     ),
     *     'description' => array(
     *         'ru' => 'Меня зовут Павел',
     *         'en' => 'My name is Pavel'
     *     )
     * )
     *
     * @var array
     */
    protected $_language = [];

    /**
     * Associative array of names of the fields (as keys),
     * that were affected by the last ->save() call,
     * and their previous values (as values)
     *
     * @var array
     */
    protected $_affected = [];

    /**
     * System data, used for internal needs
     *
     * @var array
     */
    protected $_system = [];

    /**
     * Compiled data, used for storing eval-ed values for properties, that are allowed to contain php-expressions
     *
     * @var array
     */
    protected $_compiled = [];

    /**
     * Temporary data, used for assigning some values to the current row object under some keys,
     * but these key => value pairs will be never involved at SQL INSERT or UPDATE query executions
     *
     * @var array
     */
    protected $_temporary = [];

    /**
     * Rows, pulled for current row's foreign keys
     *
     * @var array
     */
    protected $_foreign = [];

    /**
     * Rowsets containing children for current row, but related to other models
     *
     * @var array
     */
    protected $_nested = [];

    /**
     * Array containing meta information about uploaded files
     *
     * @var array
     */
    protected $_files = [];

    /**
     * Store info about errors, fired while a try to save current row
     *
     * @var array
     */
    protected $_mismatch = [];

    /**
     *
     * @var array
     */
    protected $_notices = [];

    /**
     * Count of options that will be fetched. It's 300 by default - hundred-rounded number of countries in the world
     *
     * @var int
     */
    public static $comboOptionsVisibleCount = 300;

    /**
     * Used to store data, required for rendering the UI for current row's properties.
     * Usage: $row->view('someRowProperty', array('someparam1' => 'somevalue1')), assuming that 'someRowProperty'
     * is a field, that need to have some additional params for being properly displayed in the UI
     *
     * @var array
     */
    protected $_view = [];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = []) {

        // Setup initial properties
        $this->_init($config);

        // Compile php expressions stored in allowed fields and assign results under separate keys in $this->_compiled
        foreach ($this->model()->getEvalFields() as $evalField) {
            if (strlen($this->_original[$evalField])) {
                Indi::$cmpTpl = $this->_original[$evalField]; eval(Indi::$cmpRun); $this->_compiled[$evalField] = Indi::cmpOut();
            }
        }
    }

    /**
     * Setup initial properties
     *
     * @param array $config
     */
    protected function _init(array $config = []) {
        $this->_table = $config['table'];
        $this->_original = $config['original'];
        $this->_modified = is_array($config['modified']) ? $config['modified'] : [];
        $this->_affected = is_array($config['affected']) ? $config['affected'] : [];
        $this->_system = is_array($config['system']) ? $config['system'] : [];
        $this->_temporary = is_array($config['temporary']) ? $config['temporary'] : [];
        $this->_foreign = is_array($config['foreign']) ? $config['foreign'] : [];
        $this->_nested = is_array($config['nested']) ? $config['nested'] : [];

        // Pick translation
        $this->_original = $this->l10n($this->_original);
    }

    /**
     * Force $data to contain only single translation (according to current language settings)
     * but only for localized fields
     *
     * @param $data
     * @return array
     */
    public function l10n($data) {

        // Get localized
        foreach (Indi_Db::l10n($this->_table) ?: [] as $field)
            if (array_key_exists($field, $data))
                if (preg_match('/^{"[a-z_A-Z]{2,5}":/', $data[$field]) || $data[$field] == '{}')
                    if (($this->_language[$field] = json_decode($data[$field], true)) || $data[$field] == '{}')
                        $data[$field] = $this->_language[$field][
                            array_key_exists(ini('lang')->admin, $this->_language[$field])
                                ? ini('lang')->admin
                                : key($this->_language[$field])
                        ];

        // Pull tarnslations from `queueItem` entries if need
        foreach (Indi_Queue_L10n_FieldToggleL10n::$l10n[$this->_table] ?: [] as $field => $l10n)
            if (array_key_exists($field, $data))
                if ($this->_language[$field] = json_decode($l10n[$this->id], true))
                    $data[$field] = $this->_language[$field][ini('lang')->admin];

        // Return data
        return $data;
    }

    /**
     * Fix types of data, got from PDO
     *
     * @param array $data
     * @param bool $stdClass
     * @return array
     */
    public function fixTypes(array $data, $stdClass = false) {

        // Foreach prop check
        foreach ($data as $k => $v) {

            // If prop's value is a string, containing integer value - force value type to be integer, not string
            if (preg_match(Indi::rex('int11'), $v)) $data[$k] = (int) $v;

            // Else if prop's value is a string, containing decimal value - force value type to be float, not string
            else if (preg_match(Indi::rex('decimal112'), $v)) $data[$k] = (float) $v;
            
            // Else if prop's value is a string, containing relative src - prepend STD
            else if ($m = Indi::rexm('~\burl\((/[^/]+)~', $v)) $data[$k] = preg_replace('~\burl\((/[^/]+)~', 'url(' . STD . '$1', $v);
        }

        // Return
        return $stdClass ? (object) $data : $data;
    }

    /**
     * Get the title of current row
     *
     * @return string
     */
    public function title() {

        return $this->{$this->model()->titleColumn()};
    }

    /**
     * Update current row title in case if title is dependent from some foreign key data.
     * After that, function also updates all titles, that are dependent on current row title
     *
     * @param Field_Row $titleFieldR
     */
    public function titleUpdate(Field_Row $titleFieldR, $original = []) {

    }

    /**
     * Set `title` prop for case when foreign-key titleFieldId defined for current entity
     */
    protected function _setTitle() {

        // If no titleField defined - return
        if ((!$titleFieldR = $this->model()->titleField()) || $titleFieldR->storeRelationAbility == 'none') return;

        // If field, used as title field - is storing single foreign key
        if ($titleFieldR->original('storeRelationAbility') == 'one') {

            // If foreign row can be successfully got by that foreign key
            if ($this->foreign($titleFieldR->alias))

                // Set current row's title as value, got by title() call on foreign row
                $title = $this->foreign($titleFieldR->alias)->title();

        // Else if field, that is used as title field - is storing multiple foreign keys
        } else if ($titleFieldR->storeRelationAbility == 'many') {

            // Declare $titleA array
            $titleA = [];

            // Foreach foreign row within foreign rowset, got by multiple foreign keys
            foreach ($this->foreign($titleFieldR->alias) as $foreignR)

                // Append the result of title() method call in foreign row to $titleA array
                $titleA[] = $foreignR->title();

            // Setup current row's title as values of $titleA array, imploded by comma
            $title = implode(', ', $titleA);
        }

        // Set `title`
        $this->title = mb_substr($title, 0, 255, 'utf-8');
    }

    /**
     * Flush a special-formatted json error message, in case if current row has mismatches
     *
     * @param bool $check If this param is omitted or `false` (by default) - function will look at existing mismatches.
     *                    Otherwise, it will run a distinct mismatch check-up, and then behave on results
     * @param string $message
     */
    public function mflush($check = false, $message = null) {

        // If second arg - $message - is given, we'll right now flush the mismatch, related to certain field
        if (func_num_args() == 2) $this->mismatch($check, $message);

        // Check conformance to all requirements / Ensure that there are no mismatches
        else if (!count($this->mismatch($check))) return;

        // Setup $mflush flag, indicating whether or not system should immediately flush detected mismatches
        $mflush = !isset($this->_system['mflush']) || $this->_system['mflush'];

        // If immediate mismatch flushing is turned Off - return
        if (!$mflush) return;

        // Rollback changes
        db()->rollback();

        // Build an array, containing mismatches explanations
        $mismatch = [
            'entity' => [
                'title' => $this->model()->title(),
                'table' => $this->model()->table(),
                'entry' => $this->id
            ],
            'errors' => $this->_mismatch,
            'trace' => array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1)
        ];

        // Log this error if logging of mflush-events is turned On
        if (Indi::logging('mflush')) Indi::log('mflush', $mismatch);

        // Flush mismatch
        jflush(false, ['mismatch' => $mismatch]);
    }

    /**
     * Set values for external space-coord fields, in case if internal space
     * fields - `spaceSince` and/or `spaceUntil` - were modified
     *
     * @param $scheme
     * @param $coords
     */
    protected function _spaceStep1($scheme, $coords) {

        // If space's start point is going to be moved
        if ($this->delta('spaceSince')) {

            // [+] date
            // [+] datetime
            // [+] date-time
            // [+] date-timeId
            // [+] date-dayQty
            // [+] datetime-minuteQty
            // [+] date-time-minuteQty
            // [+] date-timeId-minuteQty
            // [+] date-timespan

            // If scheme is 'date' or 'date-dayQty'
            if (in($scheme, 'date,date-dayQty')) $this->{$coords['date']} = $this->date('spaceSince');

            // If scheme is 'datetime' or 'datetime-minuteQty
            if (in($scheme, 'datetime,datetime-minuteQty')) $this->{$coords['datetime']} = $this->spaceSince;

            // If scheme is 'date-time' or 'date-time-minuteQty'
            if (in($scheme, 'date-time,date-time-minuteQty')) {

                // Set 'date' field
                $this->{$coords['date']} = $this->date('spaceSince');

                // Set 'time' field
                $this->{$coords['time']} = array_pop(explode(' ', $this->spaceSince));
            }

            // If scheme is 'date-timeId'
            if (in($scheme, 'date-timeId,date-timeId-minuteQty')) {

                // Set 'date' field
                $this->{$coords['date']} = $this->date('spaceSince');

                // Set 'timeId' field
                $this->{$coords['timeId']} = m('Time')->row(
                    '`title` = "' . $this->date('spaceSince', 'H:i') . '"'
                )->id;
            }

            // If scheme is 'date-timespan'
            if ($scheme == 'date-timespan') {

                // Set 'date' field
                $this->{$coords['date']} = $this->date('spaceSince');

                // Set 'timespan' field
                $this->{$coords['timespan']} = $this->date('spaceSince', 'H:i') . '-' . $this->date('spaceUntil', 'H:i');
            }

        // Else if event duration is going to be changed
        } else if ($this->delta('spaceUntil')) {

            // [+] date
            // [+] datetime
            // [+] date-time
            // [+] date-timeId
            // [+] date-dayQty
            // [+] datetime-minuteQty
            // [+] date-time-minuteQty
            // [+] date-timeId-minuteQty
            // [+] date-timespan

            // If space scheme is a non-duration scheme - revert modification of `spaceUntil`
            if (in($scheme, 'date,datetime,date-time,date-timeId')) unset($this->_modified['spaceUntil']);

            // If scheme is 'date-dayQty'
            if ($scheme == 'date-dayQty') {

                // Get days difference
                $diff = $this->delta('spaceUntil') / _2sec('1d');

                // Update 'dayQty', else revert modification of `spaceUntil`
                if (Indi::rexm('int11', $diff)) $this->{$coords['dayQty']} += $diff;
                else unset($this->_modified['spaceUntil']);
            }

            // If scheme's space duration is measured in minutes
            if (in($scheme, 'datetime-minuteQty,date-time-minuteQty,date-timeId-minuteQty')) {

                // Get minutes difference
                $diff = $this->delta('spaceUntil') / _2sec('1m');

                // Update 'minuteQty', else revert modification of `spaceUntil`
                if (Indi::rexm('int11', $diff)) $this->{$coords['minuteQty']} += $diff;
                else unset($this->_modified['spaceUntil']);
            }

            // If scheme is 'date-timespan' - set 'timespan' field
            if ($scheme == 'date-timespan')
                $this->{$coords['timespan']} = $this->date('spaceSince', 'H:i') . '-' . $this->date('spaceUntil', 'H:i');
        }
    }

    /**
     * Set values for internal space fields (`spaceSince`, `spaceUntil` and `spaceFrame`)
     * for them to comply the values of external space-fields
     *
     * @param $scheme
     * @param $coords
     */
    protected function _spaceStep2($scheme, $coords) {
        $this->setSpaceSince($scheme, $coords);
        $this->setSpaceUntil($scheme, $coords);
        $this->setSpaceFrame($scheme, $coords);
    }

    /**
     * Set value for `spaceSince` prop, depend on $scheme and using names of involved fields
     *
     * @param $scheme
     * @param $coords
     */
    public function setSpaceSince($scheme, $coords) {

        // [+] date
        // [+] datetime
        // [+] date-time
        // [+] date-timeId
        // [+] date-dayQty
        // [+] datetime-minuteQty
        // [+] date-time-minuteQty
        // [+] date-timeId-minuteQty
        // [+] date-timespan

        // If scheme assumes that `spaceSince` depends on a single field, and that field is a date-field
        if (in($scheme, 'date,date-dayQty'))
            $this->spaceSince = $this->date($coords['date'], 'Y-m-d H:i:s');

        // If scheme assumes that `spaceSince` depends on a single field, and that field is a datetime-field
        if (in($scheme, 'datetime,datetime-minuteQty'))
            $this->spaceSince = $this->date($coords['datetime'], 'Y-m-d H:i:s');

        // If scheme assumes that `spaceSince` depends both on date-field and time-field,
        if (in($scheme, 'date-time,date-time-minuteQty'))
            $this->spaceSince = $this->{$coords['date']} . ' ' . $this->{$coords['time']};

        // If scheme assumes that `spaceSince` depends both on date-field and time-field,
        // and time field is a foreign-key field
        if (in($scheme, 'date-timeId,date-timeId-minuteQty'))
            $this->spaceSince = $this->{$coords['date']} . ' ' . $this->foreign($coords['timeId'])->title . ':00';

        // If scheme assumes that `spaceSince` depends on date-field, and on start time within timespan-field
        if ($scheme == 'date-timespan')
            $this->spaceSince = $this->{$coords['date']}
                . ' ' . array_shift(explode('-', $this->{$coords['timespan']})) . ':00';
    }

    /**
     * Set value for `spaceUntil` prop, depend on $scheme and using names of involved fields
     *
     * @param $scheme
     * @param $coords
     */
    public function setSpaceUntil($scheme, $coords) {

        // [+] date
        // [+] datetime
        // [+] date-time
        // [+] date-timeId
        // [+] date-dayQty
        // [+] datetime-minuteQty
        // [+] date-time-minuteQty
        // [+] date-timeId-minuteQty
        // [+] date-timespan

        // If current scheme is a no-duration scheme
        if (in($scheme, 'date,datetime,date-time,date-timeId')) $this->spaceUntil = $this->spaceSince;

        // If current scheme assumes that duration is represented by a field, containing hours quantity
        if (in($scheme, 'date-dayQty'))
            $this->spaceUntil = date('Y-m-d H:i:s',
                strtotime($this->spaceSince) + _2sec($this->{$coords['dayQty']} . 'd'));

        // If current scheme assumes that duration is specified by a number-field, containing minutes quantity
        if (in($scheme, 'datetime-minuteQty,date-time-minuteQty,date-timeId-minuteQty'))
            $this->spaceUntil = date('Y-m-d H:i:s',
                strtotime($this->spaceSince) + _2sec($this->{$coords['minuteQty']} . 'm'));

        // If scheme assumes duration is the difference between from-time
        // and till-time, both specified by the value of timespan-field
        if ($scheme == 'date-timespan') {

            // Convert date to timestamp
            $_ = strtotime($this->{$coords['date']});

            // Get 'from' and 'till' times
            list($from, $till) = explode('-', $this->{$coords['timespan']});

            // Append number of seconds according to 'till' time
            $_ += _2sec($till . ':00');

            // If 'till' time is less than 'from' time, assume that it's the next day
            if ($till < $from) $_ += _2sec('1d');

            // Set `spaceSince`
            $this->spaceUntil = date('Y-m-d H:i:s', $_);
        }
    }

    /**
     * Calculate current entry's space size within the schedule, in seconds
     */
    public function setSpaceFrame($scheme, $coords) {
        $this->spaceFrame = strtotime($this->spaceUntil) - strtotime($this->spaceSince);
    }

    /**
     * Saves row into database table. But.
     * Preliminary checks if row has a `move` field in it's structure and if row is not an existing row yet
     * (but is going to be inserted), and if so - autoset value for `move` column after row save
     *
     * @return int affected rows|last_insert_id
     */
    public function save() {

        // Setup $mflush flag, indicating whether or not system should
        // immediately flush any mismatches detected using scratchy() and/or validate() methods
        $mflush = !isset($this->_system['mflush']) || $this->_system['mflush'];

        // Data types check, and if smth is not ok - flush mismatches
        $this->scratchy($mflush);

        // If immediate mismatch flushing is turned Off, but some mismatches detected - return false
        if (!$mflush && $this->_mismatch) return false;

        // If entity, that current entry is an instance of - has non-'none' value of `spaceScheme` prop
        if (($space = $this->model()->space()) && $space['scheme'] != 'none') {

            // Set values for external space-fields, in case if internal space
            // fields - `spaceSince` and/or `spaceUntil` - was modified
            $this->_spaceStep1($space['scheme'], $space['coords']);

            // Set space fields
            $this->_spaceStep2($space['scheme'], $space['coords']);
        }

        // Backup original data
        $original = $this->_original;

        // Setup $orderAutoSet flag if need
        if (!$this->_original['id'] && array_key_exists('move', $this->_original) && !$this->move) $orderAutoSet = true;

        // If current row is an existing row
        if ($this->_original['id']) {

            // Do some needed operations that are required to be done right before row update
            $this->onBeforeUpdate(); $this->onBeforeSave();

            // Do both data types check and custom validation, and if smth is not ok - flush mismatches
            // At first sight, we could just perform custom validation, without preliminary data types check,
            // because data types check had been already done, by $this->scratchy(true) call at the beginning
            // of save() method, that we are currently in. BUT. $this->onBeforeUpdate() call was made after that,
            // so some props might have been additionally changed by that call, so, to be sure that data types are
            // STILL OK, we do $this->mflush(true) call, as it will do (among other things) data types check again
            $this->mflush(true);

            // If immediate mismatch flushing is turned Off, but some mismatches detected - return false
            if (!$mflush && $this->_mismatch) return false;

            // If foreign-key-titleField defined - set `title`
            $this->_setTitle();

            // Backup modified data
            $modified = $update = $this->_modified;

            // Setup other-language versions
            $this->_localize($update);

            // Update it
            $affected = $this->model()->update($update, '`id` = "' . $this->_original['id'] . '"');

            // Setup $return variable as a number of affected rows, e.g 1 or 0
            $return = $affected;

        // Else current row is a new row
        } else {

            // Set up a $new flag, indicating that this will be a new row
            $new = true;

            // Do some needed operations that are required to be done right before row insertion into a database table
            $this->onBeforeInsert(); $this->onBeforeSave();

            // Check mismatches again, because some additional changes might have been done within $this->onBeforeInsert() call
            $this->mflush(true);

            // If immediate mismatch flushing is turned Off, but some mismatches detected - return false
            if (!$mflush && $this->_mismatch) return false;

            // If foreign-key-titleField defined - set `title`
            $this->_setTitle();

            // Backup modified data
            $modified = $insert = $this->_modified;

            // Setup other-language versions
            $this->_localize($insert);

            // Execute the INSERT sql query, get LAST_INSERT_ID and assign it as current row id
            $this->_modified['id'] = $this->model()->insert($insert);

            // Setup $return variable as id of current (a moment ago inserted) row
            $return = $this->_modified['id'];
        }

        // Check if row (in it's original state) matches each separate notification's criteria,
        // and remember the results separately for each notification, attached to current row's entity
        $this->_noticesStep1($new ? 'insert' : 'update');

        // Merge $this->_original and $this->_modified arrays into $this->_original array
        $this->_original = (array) array_merge($this->_original, $this->_modified);

        // Update $this->_language
        foreach ($this->_modified as $prop => $value)
            if ($this->_language[$prop]) $this->_language[$prop][ini('lang')->admin] = $value;

        // Empty $this->_modified and $this->_mismatch arrays
        $this->_modified = $this->_mismatch = [];

        // Set up `_affected` prop, so it to contain affected field names as keys, and their previous values
        $this->_affected = array_diff_assoc($original, $this->_original);

        // Provide a changelog recording, if configured
        $this->changeLog($original);

        // Auto set `move` if need
        if ($orderAutoSet) {

            // Set `move` property equal to current row id
            $this->move = $this->id;

            // Update row data
            $this->model()->update($this->_modified, '`id` = "' . $this->_original['id'] . '"');
            $this->_original = (array) array_merge($this->_original, $this->_modified);
            $this->_modified = [];
        }

        // Adjust file-upload fields contents according to meta info, existing in $this->_files for such fields
        $this->files(true);

        // Do some needed operations that are required to be done right after row was inserted/updated
        $new ? $this->onInsert() : $this->onUpdate($original); $this->onSave();

        // Check if row (in it's current/modified state) matches each separate notification's criteria,
        // and compare results with the results of previous check, that was made before any modifications
        $this->_noticesStep2($original);

        // Update usages
        if (!$new) $this->_updateUsages();

        // For now, only existing entries changes are supported
        $this->realtime($new ? 'insert' : 'update');

        // Trigger dependent counters / summaries to be updated
        $this->_inQtySum($new ? 'insert' : 'update');

        // Return current row id (in case if it was a new row) or number of affected rows (1 or 0)
        return $return;
    }

    /**
     * Method to trigger all things to be processed on data change event, captured by maxwell daemon
     *
     * @param $event
     * @param int $queryID Identifier of a query that caused data changes.
     *        For example all binlog events saying some record was deleted will have identical $queryID
     *        in case if they were affected by single query like DELETE FROM `someTable` WHERE `xx` = "yy"
     */
    public function maxwell($event, $queryID = 0) {

        // If event is 'delete'
        if ($event == 'delete') {

            // Do system things
            $this->_afterDelete($queryID);

        // Else
        } else {

            // Backup original
            $original = $this->_original;

            // Check if row (in it's original state) matches each separate notification's criteria,
            // and remember the results separately for each notification, attached to current row's entity
            $this->_noticesStep1($event);

            // Merge $this->_original and $this->_modified arrays into $this->_original array
            $this->_original = (array) array_merge($this->_original, $this->_modified);

            // Empty $this->_modified and $this->_mismatch arrays
            $this->_modified = $this->_mismatch = [];

            // Set up `_affected` prop, so it to contain affected field names as keys, and their previous values
            $this->_affected = array_diff_assoc($original, $this->_original);

            // Check if row (in it's current/modified state) matches each separate notification's criteria,
            // and compare results with the results of previous check, that was made before any modifications
            $this->_noticesStep2($original);

            // Trigger realtime updates to be rendered and delivered to subscribers
            $this->realtime($event);
        }
    }

    /**
     * Use $fieldIds and $scope args to prepare args and call $this->toGridData($dataColumns, $renderCfg)
     *
     * @param $fieldIds
     * @param $scope
     * @return mixed
     */
    public function toRealtimeGridData($fieldIds, $scope, $langId, $roleId, $adminId) {

        // Backup current language
        $backup = ini()->lang->admin;

        // Spoof current language
        ini()->lang->admin = lang($langId);

        // Convert scope to array, if need
        if (is_object($scope)) $scope = (array) $scope;

        // Get aliases of affected fields involved by context
        $dataColumns = []; $renderCfg = ['_system' => []];
        foreach (ar($fieldIds) as $fieldId)
            if ($field = $this->field($fieldId)->alias) {
                $dataColumns[] = $field;
                if ($icon = $scope['icon'][$fieldId]) $renderCfg[$field]['icon'] = $icon;
                if ($jump = $scope['jump'][$fieldId]) $renderCfg[$field]['jump'] = $jump;
                if ($head = $scope['head'][$fieldId]) $renderCfg[$field]['head'] = $head;
                if ($val  = $scope['composeVal'][$fieldId]) $renderCfg[$field]['composeVal'] = $val;
                if ($tip  = $scope['composeTip'][$fieldId]) $renderCfg[$field]['composeTip'] = $tip;
                if (null !== ($color = $scope['color'][$fieldId])) $renderCfg[$field]['color'] = $color;
            }

        // Prepare row color config
        foreach (ar('colorField,colorFurther') as $prop)
            if ($scope[$prop])
                $renderCfg['_system'][$prop]
                    = $scope[$prop];

        // If scope's filterOwner is non-false
        if ($scope['filterOwner']) {
            $renderCfg['_system']['owner'] = [
                'field' => $scope['filterOwner'],
                'role' => $roleId,
                'id' => $adminId,
            ];
        }

        // Add panel type
        $renderCfg['_system']['panel'] = $scope['panel'] ?: 'grid';

        // DEBUG
        if (!$scope['panel']) {
            Indi::log('no-scope-panel', ['scope' => $scope, 'renderCfg' => $renderCfg], false);
        }

        // Render
        $data = $this->toGridData($dataColumns, $renderCfg);

        // Restore current language
        ini('lang')->admin = $backup;

        // Return
        return $data;
    }

    /**
     * Get info about affected fields and send it to websocket-channels,
     * that are representing browser tabs having grids where current entry
     * and old values of it's affected fields are displayed
     */
    public function realtime($event = 'update', $queryID = 0) {

        // If rabbitmq is not enabled
        if (!ini()->rabbitmq->enabled) return;

        // If realtime is configured to listen for mysql binlog events captured by vendor/zendesk/maxwell lib,
        // proceed only if MAXWELL-constant is defined, so that it means we're inside realtime/maxwell process
        if (ini()->rabbitmq->maxwell && !defined('MAXWELL') && $event != 'reload') return;

        // Start building WHERE clause
        $where = [
            '`type` = "context"',
            '`entityId` = "' . $this->model()->id() . '"',
            '`scope` != ""'
        ];

        // If $event is 'update', 'reload' or 'delete'
        if ($event == 'update' || $event == 'reload' || $event === 'delete') {

            // Append clause for `entries` column
            $where []= 'CONCAT(",", `entries`, ",") REGEXP ",(' . $this->id . '),"';

            // If $event is 'update'
            if ($event == 'update') {

                // If no fields affected - return
                if (!$fieldIdA_affected = $this->field(array_keys($this->_affected))->column('id')) return;

                // Append clause for `fields` column
                $where []= 'CONCAT(",", `fields`, ",") REGEXP ",(' . im($fieldIdA_affected, '|') . '),"';
            }
        }

        // Fetch matching `realtime` entries
        $realtimeRs = m('Realtime')->all($where)->exclude(': != "context"', 'type');

        // Pull foreign data for `realtimeId` key
        $realtimeRs->foreign('realtimeId');

        // Foreach
        foreach ($realtimeRs as $realtimeR) {

            // Get channel
            $channel = $realtimeR->foreign('realtimeId')->token;

            // Get context
            $context = $realtimeR->token;

            // If $event is 'update'
            if ($event == 'update' || $event == 'reload') {

                // Prepare blank data and group it by channel and context
                $byChannel[$channel][$context] = [
                    'table' => $this->_table,
                    'entry' => $this->id
                ];

                // Json-decode scope data
                $scope = json_decode($realtimeR->scope, true);

                // If at least one of affected fields stand behind of a grid column, having `rowReqIfAffected` = 'y'
                if ($event === 'reload' || array_intersect($fieldIdA_affected, ar($scope['rowReqIfAffected']))) {

                    // It means that such column's cell content IS INVOLVED in the process
                    // of rendering content for at least one other column's cell within same grid,
                    // and in that case we give a signal for whole row to be reloaded rather than
                    // sending only changed cells data
                    $byChannel[$channel][$context]['affected'] = true;

                // Else
                } else {

                    // Get IDs of affected fields involved by context
                    $fieldIds = array_intersect($fieldIdA_affected, ar($realtimeR->fields));

                    // Prepare grid data, however with no adjustments that could be applied at section/controller-level
                    $data = [$this->toRealtimeGridData($fieldIds, $scope, $realtimeR->langId, $realtimeR->roleId, $realtimeR->adminId)];

                    // Prepare blank data and group it by channel and context
                    $byChannel[$channel][$context]['affected'] = array_shift($data);
                }

            // Else if $event is 'delete'
            } else if ($event == 'delete') {

                // Get scope
                $scope = json_decode($realtimeR->scope, true); $entries = false;

                // If $queryID is 0, or is changed - reset $scope['overpage']
                if (!$queryID || $scope['queryID'] != $queryID) {
                    $scope['queryID'] = $queryID;
                    unset($scope['overpage']);
                }

                // If scope's tree-flag is true
                if ($scope['tree']) {

                    // Get raw tree
                    $tree = $this->model()->fetchRawTree($scope['ORDER'], $scope['WHERE']);

                    // Pick tree if need
                    if ($scope['WHERE']) $tree = $tree['tree'];

                    // Build ORDER clause, respecting the tree
                    $order = 'FIND_IN_SET(`id`, "' . im(array_keys($tree)) . '")';

                // Else build ORDER clause using ordinary approach
                } else $order = is_array($scope['ORDER']) ? im($scope['ORDER'], ', ') : ($scope['ORDER'] ?: '');

                // Get offset
                $offset = $scope['rowsOnPage'] * $scope['page'];

                // Build usable WHERE clause
                $where = rif($scope['WHERE'], 'WHERE $1');

                // Build usable ORDER BY clause
                $order = rif($order, 'ORDER BY $1');

                // If deleted entry is on current page
                if (in($this->id, $realtimeR->entries)) {

                    // If there is at least 1 next page exists
                    if ($scope['found'] > $offset) {

                        // Get id of current page's last record
                        $entries = ar($realtimeR->entries); $last = $entries[count($entries) - 1];

                        // Prepare WHERE and OFFSET clauses for fetching next page's first record
                        $pgdn1stWHERE = [];
                        if ($scope['WHERE'])     $pgdn1stWHERE []= $scope['WHERE'];
                        if ($realtimeR->entries) $pgdn1stWHERE []= "`id` NOT IN ({$realtimeR->entries})";
                        $pgdn1stWHERE = rif(join(' AND ', $pgdn1stWHERE), 'WHERE $1');

                        // Nasty hack. todo: investigate how that might happend in /realtime
                        if ($scope['page'] < 1) $scope = 1;

                        // Prepare OFFSET clause for current page's first record
                        $pg1stOFFSET = $scope['rowsOnPage'] * ($scope['page'] - 1);

                        // Detect ID of entry that will be shifted from next page's first to current page's last:
                        //
                        // If current page's last record is still exist, it means that even in case of batch-deletion
                        // such a deletion was either not intended to reach that record, or was but has not yet reached that
                        // record so far, so that we can rely on that record while calculating, so to say, 'next' record
                        // which is the next page's first record to be added to the list of current page's records
                        if ($last
                            && db()->query("SELECT `id` FROM `{$this->_table}` WHERE `id` = '$last'")->cell()
                            && !in($last, $scope['overpage'])) {

                            // Get next record after $last
                            $next = db()
                                ->query("SELECT `id` FROM `{$this->_table}` $pgdn1stWHERE $order LIMIT $pg1stOFFSET, 1")
                                ->cell();

                            // Unset overpage ids
                            unset($scope['overpage']);

                        // Else it means deletion reached all records up to current page's ones inclusively,
                        // and maybe even further ones, so the only thing we can do is just to pick first among
                        // remaining records called overpage-records
                        } else {

                            // If count of overpage-records does not exceed so far the limit of to be shown on page
                            if (count($scope['overpage'] ?: []) < $scope['rowsOnPage']) {

                                // Make sure previously fetched overpage-records are skipped while fetching next one
                                if ($scope['overpage'])
                                    $where = rif($where, "$1 AND ", "WHERE ")
                                        . '`id` NOT IN (' . im($scope['overpage']) . ')';

                                // Fetch record
                                $next = db()->query("SELECT `id` FROM `{$this->_table}` $where $order LIMIT 1")->cell();

                                // Append it's id to scope's overpage-array
                                $scope['overpage'] []= (int) $next;

                            // If limit reached - do nothing
                            } else {
                                $next = 0;
                            }
                        }

                        // Detect ID of entry that will be shifted from next page's first to current page's last
                        $byChannel[$channel][$context]['deleted'] = (int) $next;

                        // If such entry exists - push it's id to context's entries ids list
                        if ($byChannel[$channel][$context]['deleted'])
                            $realtimeR->push('entries', $byChannel[$channel][$context]['deleted']);

                    // Else if it's the last page - do nothing
                    } else $byChannel[$channel][$context]['deleted'] = 'this';

                    // Prepare data and group it by channel and context
                    $byChannel[$channel][$context] += [
                        'table' => $this->_table,
                        'entry' => $this->id
                    ];

                    // Decrement found
                    $scope['found'] --;

                    // Adjust summary if need
                    foreach (ar($scope['sum']) as $sumCol)
                        $byChannel[$channel][$context]['sum'][$sumCol]
                            = -$this->$sumCol;

                    // Update scope
                    $realtimeR->scope = json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);

                    // Drop current entry ID from `entries` column of `realtime` entry
                    $realtimeR->drop('entries', $this->id);

                    // Update `realtime` entry
                    $realtimeR->basicUpdate();

                // Else if deleted entry is on prev or next page
                } else if (!$where || db()->query("
                    SELECT `id` FROM ({$this->phantom()}) AS `{$this->_table}` $where
                ")->cell()) {

                    // Push current entry ID to the beginning of `entries` column of `realtime` entry
                    $entries = ar($realtimeR->entries); $first = $entries[0]; $last = $entries[count($entries) - 1];

                    // Get comma-separated ids list for First And/Or Last entries/entry on the page
                    $faol = []; if ($first) $faol []= $first; if ($last) $faol []= $last; $faol = im($faol);

                    // If not empty - build SELECT
                    if ($faol) $faol = "SELECT * FROM `{$this->_table}` WHERE `id` IN ($faol)";

                    // Create UNION expr
                    $union = rif($faol, '$1 UNION ') . $this->phantom();

                    // Get the ordered IDs: deleted, first on current page, and last on current page
                    $idA = db()->query($sql = "SELECT `id` FROM ($union) AS `{$this->_table}` $order")->col();

                    // If deleted entry ID is above the others, it means it belongs to the one of prev pages
                    if ($this->id == array_shift($idA)) {

                        // Remove first from $entries to set it as new scope's pgupLast
                        $scope['pgupLast'] = $first;
                        $realtimeR->drop('entries', $first);

                        // If there is at least 1 next page exists
                        if ($scope['found'] > $offset) {

                            // Detect ID of entry that will be shifted from next page's first to current page's last:
                            //
                            // If current page's last record is still exist, it means that even in case of batch-deletion
                            // such a deletion was either not intended to reach that record, or was but has not yet reached that
                            // record so far, so that we can rely on that record while calculating, so to say, 'next' record
                            // which is the next page's first record to be added to the list of current page's records
                            if ($last
                                && db()->query("SELECT `id` FROM `{$this->_table}` WHERE `id` = '$last'")->cell()
                                && !in($last, $scope['overpage'])) {

                                // Set @found mysql variable
                                db()->query('SET @found := 0');

                                // Get next record after $last
                                $next = db()->query("
                                    SELECT `id` 
                                    FROM `{$this->_table}`
                                    HAVING (@found := IF (`id` = '$last', 1, IF(@found, @found + 1, @found))) = 2
                                    $order
                                ")->cell();

                                // Unset overpage ids
                                unset($scope['overpage']);

                            // Else it means deletion reached all records up to current page's ones inclusively,
                            // and maybe even further ones, so the only thing we can do is just to pick first among
                            // remaining records called overpage-records
                            } else {

                                // If count of overpage-records does not exceed so far the limit of to be shown on page
                                if (count($scope['overpage'] ?: []) < $scope['rowsOnPage'] * $scope['page']) {

                                    // Make sure previously fetched overpage-records are skipped while fetching next one
                                    if ($scope['overpage'])
                                        $where = rif($where, "$1 AND ", "WHERE ")
                                            . '`id` NOT IN (' . im($scope['overpage']) . ')';

                                    // Fetch record
                                    $next = db()->query("SELECT `id` FROM `{$this->_table}` $where $order LIMIT 1")->cell();

                                    // Append it's id to scope's overpage-array
                                    $scope['overpage'] []= (int) $next;

                                // If limit reached - do nothing
                                } else {
                                    $next = 0;
                                }
                            }

                            // Apply to ws-message
                            $byChannel[$channel][$context]['deleted'] = $next;

                            // If such entry exists - push it's id to context's entries ids list
                            if ($byChannel[$channel][$context]['deleted'])
                                $realtimeR->push('entries', $byChannel[$channel][$context]['deleted']);

                        // Else if it's the last page
                        } else $byChannel[$channel][$context]['deleted'] = 'prev';

                    // Else if deleted entry ID is below the others, it means it belongs to the one of next pages
                    } else if ($this->id == array_pop($idA)) $byChannel[$channel][$context]['deleted'] = 'next';

                    // If $byChannel[$channel][$context] is empty - this means that current entry was already deleted
                    if ($byChannel[$channel][$context]) {

                        // Prepare data and group it by channel and context
                        $byChannel[$channel][$context] += [
                            'table' => $this->_table,
                            'entry' => $this->id
                        ];

                        // Adjust summary if need
                        foreach (ar($scope['sum']) as $sumCol)
                            $byChannel[$channel][$context]['sum'][$sumCol]
                                = -$this->$sumCol;

                        // Decrement found
                        $scope['found'] --;

                        // Update scope
                        $realtimeR->scope = json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);

                        // Update `realtime` entry
                        $realtimeR->basicUpdate();
                    }
                }

                // If some entry should be appended to UI grid store instead of deleted entry and scope's rowReqIfAffected-prop is empty
                if (($entry = $byChannel[$channel][$context]['deleted']) && is_numeric($entry) && !$scope['rowReqIfAffected']) {

                    // Get it's actual *_Row instance
                    $entry = $entry == $this->id ? $this : $this->model()->row($entry);

                    // Render json-data equal to as if separate request would be made
                    $byChannel[$channel][$context]['rendered'] = $entry->toRealtimeGridData($realtimeR->fields, $scope, $realtimeR->langId, $realtimeR->roleId, $realtimeR->adminId);
                }

            // Else if $event is 'insert'
            } else if ($event == 'insert') {

                // Get scope
                $scope = json_decode($realtimeR->scope, true); $entries = false;

                // If scope's tree-flag is true
                if ($scope['tree']) {

                    // Get raw tree
                    $tree = $this->model()->fetchRawTree($scope['ORDER'], $scope['WHERE']);

                    // Pick tree if need
                    if ($scope['WHERE']) $tree = $tree['tree'];

                    // Build ORDER clause, respecting the tree
                    $order = 'FIND_IN_SET(`id`, "' . im(array_keys($tree)) . '")';

                // Else build ORDER clause using ordinary approach
                } else $order = is_array($scope['ORDER']) ? im($scope['ORDER'], ', ') : ($scope['ORDER'] ?: '');

                // Check whether inserted row match scope's WHERE clause
                if (!$scope['WHERE'] || db()->query($sql = '
                    SELECT `id` FROM `' . $this->_table . '` WHERE `id` = "' . $this->id . '" AND (' . $scope['WHERE'] . ')'
                )->cell()) {

                    // Prepare blank data and group it by channel and context
                    $byChannel[$channel][$context] = [
                        'table' => $this->_table,
                        'entry' => $this->id
                    ];

                    // Adjust summary if need
                    foreach (ar($scope['sum']) as $sumCol)
                        $byChannel[$channel][$context]['sum'][$sumCol]
                            = $this->$sumCol * 1;

                    // If there are at least 1 entry on current page
                    if ($realtimeR->entries) {

                        // Get the ordered IDs: pgupLast, current, and newly added
                        $idA = db()->query($sql = '
                            SELECT `id` FROM `' . $this->_table . '`
                            WHERE `id` IN (' . rif($scope['pgupLast'], '$1,') . rif($realtimeR->entries, '$1,') .  $this->id . ')
                            ' . rif($order, 'ORDER BY $1') . ' 
                        ')->col();

                        // If new entry belongs to prev page
                        if ($scope['pgupLast'] && $this->id == array_shift($idA)) {

                            // Make sure pgupLast-entry will be appended -
                            $byChannel[$channel][$context]['entry'] = $scope['pgupLast'];

                            // - Appended to the top of current page
                            $byChannel[$channel][$context]['inserted'] = 0;

                            // Push current entry ID to the beginning of `entries` column of `realtime` entry
                            $entries = ar($realtimeR->entries); array_unshift($entries, $scope['pgupLast']);

                            // Get offset
                            $offset = $scope['rowsOnPage'] * ($scope['page'] - 1) - 1;

                            // Get pgupLast
                            $scope['pgupLast'] = $offset == -1 ? 0 : db()->query('
                                SELECT `id` FROM `' . $this->_table . '` 
                                WHERE ' . ($scope['WHERE'] ?: 'TRUE') . ' 
                                ' . rif($order, 'ORDER BY $1') . ' 
                                LIMIT ' . $offset . ', 1
                            ')->cell();

                        // Else if total number of entries is more than rowsOnPage
                        } else if (count($idA) > $scope['rowsOnPage']) {

                            // If new entry is the last entry - it means that it is needless
                            // entry of current page and belongs to next page
                            if ($this->id == array_pop($idA)) $byChannel[$channel][$context]['inserted'] = 'next';

                            // Otherwise detect position index at that it should be inserted at current page, so that
                            // the record, that is currently last on current page will be pushed out to next page
                            else {

                                // Detect position
                                $byChannel[$channel][$context]['inserted'] = array_flip($idA)[$this->id];

                                // Push current entry ID to `entries` column of `realtime` entry
                                $entries = ar($realtimeR->entries);
                                array_splice($entries, $byChannel[$channel][$context]['inserted'], 0, $this->id);
                            }

                        // Else it total number of entries on current page is less or equal than the rowsOnPage
                        } else {

                            // Detect the insertion index
                            $byChannel[$channel][$context]['inserted'] = array_flip($idA)[$this->id];

                            // Push current entry ID to `entries` column of `realtime` entry
                            $entries = ar($realtimeR->entries); array_push($entries, $this->id);
                        }

                    // Else if there is not yet entries on current page
                    // it means that we're on 1st page and it's empty
                    } else {

                        // So set the insertion index for new entry to be at the top
                        $byChannel[$channel][$context]['inserted'] = 0;

                        // Push current entry ID to `entries` column of `realtime` entry
                        $entries = ar($realtimeR->entries); array_push($entries, $this->id);
                    }

                    // Increment scope's 'found' prop
                    $scope['found'] ++;

                    // Data to update `realtime` entry with
                    $data = ['scope' => json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT)];

                    // If $entries is modified
                    if ($entries !== false) {

                        // If count of IDs in $entries became greater than rowsOnPage after that - remove the last
                        if (count($entries) > $scope['rowsOnPage']) array_pop($entries);

                        // Setup 'entries' prop
                        $data['entries'] = im($entries);
                    }

                    // Update `realtime` entry
                    $realtimeR->set($data)->basicUpdate();

                    // If some entry should be appended to UI grid store and scope's rowReqIfAffected-prop is empty
                    if (is_numeric($byChannel[$channel][$context]['inserted']) && !$scope['rowReqIfAffected']) {

                        // Get shortcut to id of entry that should be appended to UI grid store
                        $entry = $byChannel[$channel][$context]['entry'];

                        // Get it's actual *_Row instance
                        $entry = $entry == $this->id ? $this : $this->model()->row($entry);

                        // Render json-data equal to as if separate request would be made
                        $byChannel[$channel][$context]['rendered'] = $entry->toRealtimeGridData($realtimeR->fields, $scope, $realtimeR->langId, $realtimeR->roleId, $realtimeR->adminId);
                    }
                }
            }
        }

        // Send data to each channel
        if ($byChannel) foreach ($byChannel as $channel => $byContext) Indi::ws([
            'type' => 'realtime',
            'to' => $channel,
            'data' => $byContext
        ]);
    }

    /**
     * Build an SQL expression applicable for use as phantom dataset compatible with WHERE and ORDER clauses
     * Currently this is used in cases when the db-entry, that this instance is representing - was already deleted
     * from the database, but we still need to invoke into realtime logic as if it would be still there
     *
     * @param string $version Can be 'original' (default), 'current', 'modified' (alias for 'current') and 'affected'
     * @param string $format Can be 'sql' (default) and 'php'
     * @return string
     */
    public function phantom($version = 'original', $format = 'sql') {

        // Original data is used
        $data = $this->_original;

        // If $version is 'affected' - spoof original values with affected ones, if any
        if ($version === 'affected') {
            $data = array_merge($data, $this->_affected);
        } else if ($version === 'current' || $version === 'modified') {
            $data = array_merge($data, $this->_modified);
        }

        // Make sure localized fields to have values for all languages
        foreach ($this->_language as $prop => $valueByLang) {
            $data[$prop] = json_encode($valueByLang, JSON_UNESCAPED_UNICODE);
        }

        // If format is 'php' - return phantom entry instance
        if ($format === 'php') return $this->model()->createRow(['original' => $data]);

        // Build cols values
        foreach ($data as $prop => $value) {
            $colA []= db()->quote($value) . " AS `$prop`";
        }

        // Return SQL SELECT expression usable as derived table definition
        return 'SELECT ' . join(', ', $colA);
    }

    /**
     * Basic update. Used for cases when, for example, there is a need to setup a value for some new prop
     * for all/many entries, but doing it by calling save() method - is not good, as it involves a lot of things
     * that make the whole process run slow. So, this method runs quicker as it omit the following things:
     *
     * 1. Custom validation
     * 2. onBefore(Insert|Update)()
     * 3. onBeforeSave()
     * 4. Change-logging
     * 5. Usages checking
     * 6. Notices (optional)
     * 7. Space-things
     *
     * Note: Use it carefully, and for cases when you're sure the things you want to do are separated
     *
     * @param bool $notices If `true - notices will not be omitted
     * @param bool $amerge If `true - previous value $this->_affected will be kept, but newly affected will have a priority
     * @param bool $realtime If `true - $this->realtime('update') call will be made
     * @param bool $inQtySum If `true - $this->_inQtySum('update') call will be made
     * @return int
     */
    public function basicUpdate($notices = false, $amerge = true, $realtime = true, $inQtySum = true) {

        // Data types check, and if smth is not ok - flush mismatches
        $this->scratchy(true);

        // Backup modified data
        $update = $this->_modified;

        // Setup other-language versions
        $this->_localize($update);

        // Update it
        $affected = $this->model()->update($update, '`id` = "' . $this->_original['id'] . '"');

        // Backup original data
        $original = $this->_original;

        // Check if row (in it's original state) matches each separate notification's criteria,
        // and remember the results separately for each notification, attached to current row's entity
        if ($notices) $this->_noticesStep1();

        // Merge $this->_original and $this->_modified arrays into $this->_original array
        $this->_original = (array) array_merge($this->_original, $this->_modified);

        // Update $this->_language
        foreach ($this->_modified as $prop => $value)
            if ($this->_language[$prop]) $this->_language[$prop][ini('lang')->admin] = $value;

        // Empty $this->_modified, $this->_mismatch and $this->_affected arrays
        $this->_modified = $this->_mismatch = [];

        // Adjust file-upload fields contents according to meta info, existing in $this->_files for such fields
        $this->files(true);

        // Set up `_affected` prop, so it to contain affected field names as keys, and their previous values
        $this->_affected = array_diff_assoc($original, $this->_original) + ($amerge ? $this->_affected : []);

        // Check if row (in it's current/modified state) matches each separate notification's criteria,
        // and compare results with the results of previous check, that was made before any modifications
        if ($notices) $this->_noticesStep2($original);

        // Involve realtime feature
        if ($realtime) $this->realtime('update');

        // Trigger dependent counters / summaries to be updated
        if ($inQtySum) $this->_inQtySum('update');

        // Return number of affected rows (1 or 0)
        return $affected;
    }

    /**
     * Detect L10n-fraction
     */
    public function fraction() {
        return $this->model()->fraction($this);
    }

    /**
     * Setup language-versions for values of localized fields
     *
     * @param array $data
     * @return mixed
     */
    protected function _localize(array &$data) {

        // If it's an enumset-entry, and it's field has l10n turned on
        if ($this->_table == 'enumset' && $this->foreign('fieldId')->l10n == 'y') $lfA = ['title'];

        // Else if it's a param-entry, and it's cfgField has l10n turned on
        else if ($this->_table == 'param' && $this->foreign('cfgField')->l10n == 'y') $lfA = ['cfgValue'];

        // Else
        else $lfA = Indi_Db::l10n($this->_table);

        // If current entity has no localized fields - return
        if (!$lfA) return;

        // If there are localized fields, but none of them were modified - return
        // Here we assume that $data arg contains modified values only, see '->_localize(' usages
        if (!$mlfA = array_intersect($lfA, array_keys($data))) return;

        // Get fraction
        $fraction = $this->fraction();

        // Get json-template containing {lang1:"",lang2:"",...} for each language, that is applicable to current fraction
        if (!$jtpl = Lang::$_jtpl[$fraction]) return;

        // Aux variables
        $setterA = $batch = $json = $keep = [];

        // For each localized modified field
        foreach ($mlfA as $mlfI) {

            // Prepare blank template and setup first value - for current language
            $json[$mlfI] = $jtpl; $json[$mlfI][ini('lang')->admin] = $data[$mlfI];

            // Check whether setter-method exists and if yes - remember that
            if (method_exists($this, $setter = 'set' . ucfirst($mlfI)) && !ini()->lang->migration) $setterA[$mlfI] = $setter;

            // Else if value should be translated - collect such values for further batch translate
            else if (!$this->id || $this->field($mlfI)->param('refreshL10nsOnUpdate')) $batch[$mlfI] = $data[$mlfI];

            // Else collect props, those translations (except the one for current language) should be kept as is,
            // so we will know what fields should be used for spoofing the $this->_modified
            else $keep []= $mlfI;
        }

        // Get target languages
        $targets = $jtpl; unset($targets[ini('lang')->admin]); $targets = array_keys($targets);

        // If both $batch and $targets are not empty
        if ($batch && $targets) {

            // Require and instantiate Google Cloud Translation PHP API and
            $gapi = new Google\Cloud\Translate\V2\TranslateClient(['key' => ini('lang')->gapi->key]);

            // Try to call Google Cloud Translate API
            try {

                // Foreach target language - make api call to google passing source values
                foreach (ar($targets) as $target) {

                    // If valid gapi key cannot be obtained at the moment
                    // Use source lang values for testing purposes
                    // Else get translations from google
                    $result = ini('lang')->gapi->off
                        ? array_values($batch)
                        : array_column($gapi->translateBatch(array_values($batch), [
                            'source' => ini('lang')->admin,
                            'target' => $target,
                        ]), 'text');

                    // Combine source and target translations
                    $resultByLang[$target] = array_combine(array_keys($batch), $result);
                }

                // For each of localized modified fields
                foreach ($mlfA as $mlfI) {

                    // If $mlfI-field has no setter method - call it
                    if (!$setterA[$mlfI]) {

                        // Build localized values
                        foreach ($json[$mlfI] as $lang => &$holder) {

                            // Else if $lang is same as current system lang - use as is
                            if ($lang == ini('lang')->admin) $holder = $data[$mlfI];

                            // Else if translation result defined - use it
                            else if (array_key_exists($mlfI, $resultByLang[$lang])) $holder = $resultByLang[$lang][$mlfI];

                            // Else use existing language version
                            else $holder = $this->_language[$mlfI][$lang] ?: '';
                        }

                        // Update $this->_language
                        $this->_language[$mlfI] = $json[$mlfI];
                    }
                }

            // Catch exception
            } catch (Exception $e) {

                // Re-throw it to be catched on controller-level
                throw $e;

                // Exit
                exit;
            }
        }

        // Backup modified state
        $modified = $this->_modified;

        // Here results built by setters will be collected
        $resultBySetter = [];

        // For each of localized modified fields
        foreach ($mlfA as $mlfI) {

            // If $mlfI-field has setter method - call it
            if ($setter = $setterA[$mlfI]) {

                // Build localized values
                foreach ($json[$mlfI] as $lang => &$holder) {

                    // Backup current language
                    $_lang = ini('lang')->admin;

                    // Temporary spoof values within $this->_modified for localized fields, as they may be involved by setters
                    $this->_modified = array_merge(
                        $modified,
                        $resultByLang[$lang] ?: [],
                        $resultBySetter[$lang] ?: []
                    );

                    // If current language is not the same we're going to call setter for - spoof modified with existing translation
                    // This is used in this way because of that setter for $mlfI-prop may involve getter for other props,
                    // having only current-language verion modified while other-languages versions are not modified,
                    // but we directly assign the into $this->_modified because of *_Row->__get($columnName) value pickup priority logic
                    if ($_lang != $lang) foreach ($keep as $keep_) $this->_modified[$keep_] = $this->_language['alias'][$lang];

                    // Temporary spoof current language with $lang
                    ini('lang')->admin = $lang;

                    // Call setter and get value according to required language
                    $this->{$setter}(); $holder =  $this->$mlfI;

                    // Collect values, built by setters, as they can be used by other setters
                    $resultBySetter[$lang][$mlfI] = $holder;

                    // Restore current language back
                    ini('lang')->admin = $_lang;

                    // Restore $this->_modified
                    $this->_modified = $modified;
                }

                // Revert value back to current language
                $this->$mlfI = $json[$mlfI][ini('lang')->admin];

            // Else
            } else if (!array_key_exists($mlfI, $batch))
                foreach ($json[$mlfI] as $lang => &$holder)
                    $holder = $lang == ini('lang')->admin ? $data[$mlfI] : ($this->_language[$mlfI][$lang] ?? '');

            // Get JSON
            $data[$mlfI] = json_encode($json[$mlfI], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
        }
    }

    /**
     * Update usages, according to mappings defined in `consider` table
     *
     * @return mixed
     */
    private function _updateUsages() {

        // If 'Consider'-model exists, then for each dependent field
        if (!m('Consider', true)) return;

        // If no affected fields - return
        if (!$affected = $this->model()->fields(array_keys($this->_affected))->column('id', '|')) return;

        // Build query to fetch appropriate `consider` entries
        $sql = 'SELECT `id` FROM `consider` WHERE CONCAT(",", IF(`foreign` > 0, `foreign`, `consider`), ",") REGEXP ",(' . $affected . '),"';
        if ($this->_table == 'enumset') $sql .= ' AND `consider` = "' . $this->fieldId . '"';

        // Fetch usage map entries ids, and if ono found - return
        if (!$considerIdA = db()->query($sql)->col()) return;

        // Prepare params
        $params = [
            'source' => ini('lang')->admin,
            'fraction' => $this->fraction(),
            'table' => $this->_table,
            'entry' => $this->id,
            'affected' => [],
            'considerIdA' => $considerIdA
        ];

        // Collect current values of affected props
        foreach ($this->_affected as $prop => $orig)
            $params['affected'][$prop] = $this->$prop;

        // Build queue class name
        $queueClassName = 'Indi_Queue_UsagesUpdate';

        // Check that class exists
        if (!class_exists($queueClassName)) jflush(false, sprintf('Не найден класс %s', $queueClassName));

        // Create queue class instance
        $queue = new $queueClassName();

        // Start queue as a background process
        if ($queueTaskR = $queue->chunk($params)) Indi::cmd('queue', ['queueTaskId' => $queueTaskR->id]);
    }

    /**
     * Check if row (in it's original state) matches each separate notification's criteria,
     * and remember the results separately for each notification, attached to current row's entity
     *
     * @param string $event
     * @return mixed
     */
    private function _noticesStep1($event = 'update') {

        // If realtime is configured to listen for mysql binlog events captured by vendor/zendesk/maxwell lib,
        // proceed only if MAXWELL-constant is defined, so that it means we're inside realtime/maxwell process
        if (ini()->rabbitmq->maxwell && !defined('MAXWELL')) return;

        // If no modifications made - return
        if (!$this->_modified && $event != 'delete') return;

        // If no notices attached - return
        if (!$this->model()->notices()) return;

        // Backup $this->_modified
        $modified = $this->_modified;

        // Reset modifications
        $this->_modified = [];

        // Foreach notice
        foreach ($this->model()->notices() as $noticeR) {

            // No match by default
            $match = false;

            // If this was an existing row - check if it (in it's original state) matched the criteria
            if ($this->_original['id']) {
                if (strlen($noticeR->event)) eval('$match = (' . $noticeR->event . ') ? true : false;');
                else $match = false;
            }

            // Save result
            $this->_notices[$noticeR->id]['was'] = $match;
        }

        // Restore $this->_modified
        $this->_modified = $modified;
    }


    /**
     * Check if row (in it's current/modified state) matches each separate notification's criteria,
     * and compare results with the results of previous check, that was made before any modifications
     *
     * If results are NOT equal, function calls Notice_Row->trigger() method, passing the direction
     * that counter should be changed (e.g. incremented or decremented)
     *
     * @param $original
     * @return mixed
     */
    private function _noticesStep2($original = null) {

        // If realtime is configured to listen for mysql binlog events captured by vendor/zendesk/maxwell lib,
        // proceed only if MAXWELL-constant is defined, so that it means we're inside realtime/maxwell process
        if (ini()->rabbitmq->maxwell && !defined('MAXWELL')) return;

        // If $original arg is given but no modifications made - return
        if ($original && !$affected = array_diff_assoc($original, $this->_original)) return;

        // If no notices attached - return
        if (!$this->model()->notices()) return;

        // Foreach notice
        foreach ($this->model()->notices() as $noticeR) {

            // No match by default
            $match = false;

            // If $original arg is given - check if row (in it's current/modified state) matches the criteria
            // Note: if $original arg is NOT given, assume that we'd deleted this row from database,
            // so all matches results are false
            if ($original) {
                if (strlen($noticeR->event)) eval('$match = (' . $noticeR->event . ') ? true : false;');
                else $match = $this->_notices[$noticeR->id]['was'];
            }

            // Save result
            $this->_notices[$noticeR->id]['now'] = $match;

            // If row was deleted then the only possible change is decrementation
            // Else if row was just inserted then the only possible change is incrementation
            // Else set $diff to be 0
            if (!func_num_args() && $this->_notices[$noticeR->id]['was']) $event = 'delete';
            else if (array_key_exists('id', $this->_affected) && !$this->_affected['id'] && $this->id) $event = 'insert';
            else $event = 'update';

            // If match value changed
            if ($this->_notices[$noticeR->id]['now'] != $this->_notices[$noticeR->id]['was']) {

                // Notice's qtyDiffRelyOn's 'getter' value - is used for cases when counter itself should not
                // be changed, or should, but not for all getters/recipients, defined for this notice.
                // Example: we have tasks, stored in `task` db table. Each task has workers, who are
                // assigned to do the task, and manager, who is controlling which workers are assigned for which tasks,
                // and both workers and manager are represented by `workerIds` and `managerId` columns within `task` db table.
                // So, if we have task, and it has worker assigned, but there was a decision to assign this task to
                // another worker - tasks counter within old worker's UI should decrement, tasks counter within
                // new worker's UI should increment, and manager should be notified about that change, e.g manager
                // should not even have tasks counter in this case, because this certain tasks counter is for workers only.
                if ($noticeR->qtyDiffRelyOn == 'getter') $diff = 0;
                else if ($this->_notices[$noticeR->id]['now'] > $this->_notices[$noticeR->id]['was']) $diff = 1;
                else $diff = -1;

                // Call the trigger
                $noticeR->trigger($this, $diff, $event);
            }

            // Unset results
            unset($this->_notices[$noticeR->id]);
        }
    }

    /**
     * This method will be called after onInsert/onUpdate methods calls,
     * It can be useful in cases when we need to do something once where was an entry inserted/updated in database table
     */
    public function onSave() {

    }

    /**
     * This method will be called after onBeforeInsert/onBeforeUpdate methods calls,
     * but before insert/update sql-queries actual execution
     */
    public function onBeforeSave() {

    }

    /**
     * This function is called right before 'return ...' statement within Indi_Db_Table_Row::save() body.
     * It can be useful in cases when we need to do something once where was an entry inserted in database table
     */
    public function onInsert() {

    }

    /**
     * This function is called right before '$this->model()->insert(..)' statement within Indi_Db_Table_Row::save() body.
     * It can be useful in cases when we need to do something before where will be an entry inserted in database table
     */
    public function onBeforeInsert() {

    }

    /**
     * This function is called right before 'return ...' statement within Indi_Db_Table_Row::save() body.
     * It can be useful in cases when we need to do something once where was an entry updated in database table
     */
    public function onUpdate() {

    }

    /**
     * This function is called right before '$this->model()->update(..)' statement within Indi_Db_Table_Row::save() body.
     * It can be useful in cases when we need to do something before where will be an entry updated in database table
     */
    public function onBeforeUpdate() {

    }

    /**
     * This function is called right before entry's actual deletion within Indi_Db_Table_Row::delete() body.
     * It can be useful in cases when we need to do something before an entry deletion from database table
     */
    public function onBeforeDelete() {

    }

    /**
     * This function is called right before 'return ...' statement within Indi_Db_Table_Row::delete() body.
     * It can be useful in cases when we need to do something once where was an entry deleted from database table
     */
    public function onDelete() {

    }

    /**
     * Update titles of all rows, that use current row for building title
     */
    public function titleUsagesUpdate() {

        // Get the model-usages info as entityId and titleFieldAlias
        $usageA = db()->query('
            SELECT `e`.`id` AS `entityId`, `e`.`table`, `f`.`alias` AS `titleFieldAlias`
            FROM `entity` `e`, `field` `f`
            WHERE `f`.`relation` = "' . $this->model()->id() . '" AND `e`.`titleFieldId` = `f`.`id`
        ')->fetchAll();

        // Foreach model usage
        foreach ($usageA as $usageI) {

            // Get the model
            $model = m($usageI['entityId']);

            // Get the field
            $titleFieldR = $model->fields($usageI['titleFieldAlias']);

            // Build WHERE clause
            $where = $titleFieldR->storeRelationAbility == 'one'
                ? '`' . $titleFieldR->alias .'` = "' . $this->id . '"'
                : 'FIND_IN_SET("' . $this->id . '", `' . $titleFieldR->alias . '`)';

            // Get the rows, that use current row for building their titles
            $rs = $model->all($where);

            // Setup foreign data, for it to be fetched within a single request
            // to database server, instead of multiple request for each row within rowset
            $rs->foreign($titleFieldR->alias);

            // Foreach row -  update it's title
            foreach ($rs as $r) $r->titleUpdate($titleFieldR);
        }
    }

    /**
     * Provide Move up/Move down actions for row within the needed area of rows
     * If $direction arg is given as integer value - function can move current entry multiple times,
     * until it's possible. In this case, movement direction will be detected as 'up' for positive
     * values of $direction arg, and as 'down' for negative
     *
     * @param string $direction (up|down)
     * @param string $within
     * @return bool
     */
    public function move($direction = 'up', $within = '') {

        // If $direction arg is either 'up' or 'down'
        if (in_array($direction, ['up', 'down'])) {

            // Setup initial WHERE clause, for being able to detect the scope of rows, that order should be changed within
            $where = is_array($within) ? $within : (strlen($within) ? [$within] : []);

            // Apend additional part to WHERE clause, in case if current entity - is a tree-like entity
            if ($this->model()->treeColumn())
                $where['treeColumn'] = 'IFNULL(`' . $this->model()->treeColumn() . '`, 0) = "' . $this->{$this->model()->treeColumn()} . '"';

            // Append nearest-neighbour WHERE clause part, for finding the row,
            // that current row should exchange value of `move` property with
            $where[] = '`move` ' . ($direction == 'up' ? '<' : '>') . ' "' . $this->move . '"';

            // Setup ORDER clause
            $order = 'move ' . ($direction == 'up' ? 'DE' : 'A') . 'SC';

            // Find row, that will be used for `move` property value exchange
            if ($changeRow = $this->model()->row($where, $order)) {

                // Backup `move` of current row
                $backup = $this->move;

                // We exchange values of `move` fields
                $this->move = $changeRow->move;
                $this->save();
                $changeRow->move = $backup;
                $changeRow->save();

                // If `move` property of current row an $changeRow row was successfully exchanged -
                // return boolean true as an indicator of success
                if (!$this->mismatch() && !$changeRow->mismatch()) return true;
            }

        // Else if $direction arg is an integer
        } else if ($direction && Indi::rexm('int11', $direction)) {

            // Do move as many times as specified by $direction arg's integer value
            for ($i = 0; $i < abs($direction); $i++)
                if (!$this->move($direction > 0 ? 'up' : 'down', $within))
                    break;

            // Return
            return $this;
        }
    }

    /**
     * Check whether deletion of this entry is restricted due to
     * onDelete=RESTRICT rule defined on direct or indirect references
     *
     * @return bool
     * @throws Exception
     */
    public function isDeletionRESTRICTed() {
        return $this->model()->isDeletionRESTRICTed($this->id);
    }

    /**
     * This method is expected to be called after $this->isDeletionRESTRICTed(), and this
     * means execution should be at the step where we are sure that deletion is NOT restricted
     * and for each ref having CASCADE as onDelete-rule we do already have ids fetched, so we rely on that
     *
     * @return mixed
     * @throws Exception
     */
    public function doDeletionCASCADEandSETNULL() {
        return $this->model()->doDeletionCASCADEandSETNULL($this->id);
    }

    /**
     * Fully deletion - including attached files and foreign key usages, if will be found
     *
     * @return int Number of deleted rows (1|0)
     */
    public function delete() {

        // If deletion of this entry is restricted - throw Indi_Db_DeleteException
        if (!$this->_system['skipDeletionRESTRICTedCheck']
            && $ref = $this->isDeletionRESTRICTed()) {

            // Prepare msg
            $msg = sprintf(I_ONDELETE_RESTRICT,
                m($ref['table'])->fields($ref['column'])->title,
                m($ref['table'])->title(),
                $ref['table'],
                $ref['column']
            );

            // Throw exception
            throw new Indi_Db_DeleteException($msg);
        }

        // Do some custom things before deletion
        $this->onBeforeDelete();

        // If native cascade deletion is turned on for this entity
        if ($this->model()->nativeCascade()) {

            // Just run DELETE query
            $return = $this->model()->delete('`id` = "' . $this->_original['id'] . '"');

        // Else
        } else {

            // Apply CASCASE and SET NULL rules for usages
            $this->doDeletionCASCADEandSETNULL();

            // Standard deletion with temporary turn off foreign keys on mysql-level
            // We do that way because CASCADE-deletion events are handled by MySQL
            // InnoDB storage engine internally and not logged into binlog, so cannot be captured by Maxwell
            db()->query('SET `foreign_key_checks` = 0');
            $return = $this->model()->delete('`id` = "' . $this->_original['id'] . '"');
            db()->query('SET `foreign_key_checks` = 1');
        }

        // Do some custom things after deletion
        $this->onDelete();

        // Do some system things after deletion, if maxwell is turned off,
        // as otherwise this method will be called inside $this->maxwell()
        if (!ini()->rabbitmq->maxwell) $this->_afterDelete();

        // Trigger dependent counters / summaries to be updated
        $this->_inQtySum('delete');

        // Unset `id` prop
        $this->id = null;

        // Return
        return $return;
    }

    /**
     * System things to be triggered after entry was deleted from table,
     * which can be done asyncronously
     *
     * @param int $queryID Is greater than 0 only in case if maxwell daemon is On,
     *                     so we're inside realtime/render process and processing binlog-events
     * @throws Exception
     */
    protected function _afterDelete($queryID = 0) {

        // Check if row (in it's current state) matches each separate notification's criteria,
        // and remember the results separately for each notification, attached to current row's entity
        $this->_noticesStep1('delete');

        // Notify UI-users
        $this->realtime('delete', $queryID);

        // Delete all files (images, etc) that have been attached to row
        $this->deleteFiles();

        // Delete all files/folder uploaded/created while using CKFinder
        $this->deleteCKFinderFiles();

        // Force false to be the result of all matches each separate notification's criteria,
        // and compare results with the results of previous check, that was made before any modifications
        $this->_noticesStep2();
    }

    /**
     * Get the data for use in all control element, that deal with foreign keys
     *
     * @param $field
     * @param null $page
     * @param null $selected
     * @param bool $selectedTypeIsKeyword
     * @param null $where
     * @param null $fieldR
     * @param null $order
     * @param string $dir
     * @param null $offset
     * @param null $consistence
     * @param null $multiSelect
     * @return Indi_Db_Table_Rowset
     */
    public function getComboData($field, $page = null, $selected = null, $selectedTypeIsKeyword = false,
        $where = null, $fieldR = null, $order = null, $dir = 'ASC', $offset = null, $consistence = null, $multiSelect = null) {

        // Basic info
        $fieldM = m('Field');
        $fieldR = $fieldR ? $fieldR : m($this->_table)->fields($field);
        $fieldColumnTypeR = $fieldR->foreign('columnTypeId');
        if ($fieldR->relation) $relatedM = m($fieldR->relation);

        // Array for WHERE clauses
        $where = $where ? (is_array($where) ? $where : [$where]): [];

        // Setup filter, as one of possible parts of WHERE clause
        if ($fieldR->filter) $where[] = '(' . $fieldR->filter . ')';

        // Compile filters if they contain php-expressions
        for($i = 0; $i < count($where); $i++) {
            Indi::$cmpTpl = $where[$i]; eval(Indi::$cmpRun); $where[$i] = Indi::cmpOut();
        }

        // If $multiSelect argument is not given - detect it automatically
        if ($multiSelect === null) $multiSelect = $fieldR->storeRelationAbility == 'many';

        // If current field column type is ENUM or SET
        if (preg_match('/ENUM|SET/', $fieldColumnTypeR->type)) {

            // Use existing enumset data, already nested for current field, instead of additional db fetch
            $dataRs = $fieldR->nested('enumset');

            // If $consistence argument is given, and it's an array, we assume it's an explicit definition of
            // a number of combo data items, that should ONLY be displayed. ONLY here mean combo items will be
            // exact as in $consistence array, not less and not greater. This feature is used for rowset filters,
            // and is a part of a number of tricks, that provide the availability of filter-combo data-options only
            // for data-options, that will have at least one matching row within rowset, in case of their selection
            // as a part of a rowset search criteria.
            $dataRs = is_array($consistence) ? $dataRs->select($consistence, 'alias') : clone $dataRs;

            // We should mark rowset as related to field, that has a ENUM or SET column type
            // because values of property `alias` should be used as options keys, instead of values of property `id`
            $dataRs->enumset = true;

            // If current field store relation ability is 'many' - we setup selected as rowset object
            if ($multiSelect) $dataRs->selected = $dataRs->select($selected, 'alias');

            // Exclude values
            if ($exclude = $fieldR->param('exclude')) $dataRs->exclude($exclude, 'alias');

            // Return combo data
            return $dataRs;

        // Else if it's a icon-field
        } else if ($fieldR->foreign('elementId')->alias == 'icon') {

            // Aux variables
            $data = []; $groups = [];

            // Foreach directory
            foreach (ar($fieldR->params['dir']) as $dir) {

                // Get absolute directory path with no trailing slash
                $pre = DOC . STD . rif(!preg_match('~^/~', $dir), VDR . '/client/');

                // Absolute path
                $abs = $pre . rtrim($dir, '\\/');

                // Add optgroup
                $groups []= ['id' => $dir, 'title' => $dir];

                // Glob files in that dir and foreach file
                foreach (rglob($abs . '/*.{png,ico,gif,svg}', GLOB_BRACE) as $icon) {

                    // Get icon
                    $icon = preg_replace('~^' . preg_quote($pre, '~') . '~', '', $icon);

                    // Boild color box
                    $box = '<span class="i-color-box" style="background: url(' . $icon . ');"></span>';

                    // If keyword is given but icon path does not match it - skip
                    if ($selectedTypeIsKeyword)
                        if (!preg_match('~' . preg_quote($keyword = $selected) . '~', $icon))
                            continue;

                    // Setup option data
                    $data []= [
                        'alias' => $icon,
                        'dir' => $dir,
                        'title' => $box . str_replace($dir . '/', '', $icon),
                        'system' => [
                            'group' => $dir
                        ]
                    ];
                }
            }

            // Prepare the data
            $dataRs = m('Enumset')->createRowset(['data' => $data]);

            // Setup `enumset` prop as `true`
            $dataRs->enumset = true;

            // Setup optgroup data
            $dataRs->optgroup = ['by' => 'dir', 'groups' => $groups];

            // Return
            return $dataRs;

        // Else if current field column type is BOOLEAN - combo is used as an alternative for checkbox control
        } else if ($fieldColumnTypeR->type == 'BOOLEAN') {

            // Prepare the data
            $dataRs = m('Enumset')->createRowset(
                [
                    'data' => [
                        ['alias' => '0', 'title' => I_NO],
                        ['alias' => '1', 'title' => I_YES]
                    ]
                ]
            );

            // If $consistence argument is given, and it's an array, we assume it's an explicit definition of
            // a number of combo data items, that should ONLY be displayed. ONLY here mean combo items will be
            // exact as in $consistence array, not less and not greater. This feature is used for rowset filters,
            // and is a part of a number of tricks, that provide the availability of filter-combo data-options only
            // for data-options, that will have at least one matching row within rowset, in case of their selection
            // as a part of a rowset search criteria.
            if (is_array($consistence)) $dataRs = $dataRs->select($consistence, 'alias');

            // Setup `enumset` prop as `true`
            $dataRs->enumset = true;

            // Return
            return $dataRs;

        // Else if combo data is being prepared for an usual (non-boolean and non-enumset) combo
        } else

            // If $consistence argument is given, and it's an array, we assume it's an explicit definition of
            // a number of combo data items, that should ONLY be displayed. ONLY here mean combo items will be
            // exact as in $consistence array, not less and not greater. This feature is used for rowset filters,
            // and is a part of a number of tricks, that provide the availability of filter-combo data-options only
            // for data-options, that will have at least one matching row within rowset, in case of their selection
            // as a part of a rowset search criteria. The $consistence array is being taken into consideration even
            // if it constains no elements ( - zero-length array), in this case
            if (is_string($consistence) && strlen($consistence)) $where[] = $consistence;

        // Foreach field, defined as a consider-field for current field
        foreach ($fieldR->nested('consider') as $considerR) {

            // Get consider-field
            $cField = $considerR->foreign('consider');

            // If it's `storeRelationAbility` prop is 'none' - skip
            if ($cField->storeRelationAbility == 'none') continue;

            // Get consider-field value
            $cValue = $this->_cValue($fieldR->alias, $cField);

            // Remember consider-field alias, because $cField may below start pointing
            // to consider-field's foreign field rather than on consider-field itself
            $cField_alias = $cField->alias;

            // If consider-field's foreign-key field should be used instead of consider-field itself
            if ($considerR->foreign) {

                // Get that foreign-key field
                $cField_foreign = $considerR->foreign('foreign');

                // Get entry, identified by current value of consider-field
                $cEntryR = m($cField->relation)->row($cValue);

                // Get it's value
                $cValueForeign = $cEntryR->{$cField_foreign->alias};

                // If current field itself is not linked to any entity, it mean that this entity would be identified by
                // the value of consider-entry's foreign field's value, and in this case there may be a need to anyway
                // involve consider-field's name and value in combo data WHERE clause. First faced case is to to this
                // for consider-fields linked to `role`-entity, as different `role` entries may have same `entityId`
                if (!$fieldR->relation && $cValueForeign)
                    $cEntryR->_comboDataConsiderWHERE($where, $fieldR, $cField, $cValue, $considerR->required, $cValueForeign);

                // Spoof variables before usage
                $cField = $cField_foreign; $cValue = $cValueForeign;
            }

            // If current field is linked to some certain entity
            if ($fieldR->relation) {

                // Use a custom connector, if defined
                if ($considerR->connector) $cField->system('connector',
                    $considerR->connector == -1 ? 'id' : $considerR->foreign('connector')->alias);

                // Build WHERE clause
                if ($this->_comboDataConsiderWHERE($where, $fieldR, $cField, $cValue, $considerR->required)
                    && array_key_exists($cField_alias, $this->_modified)
                    && $this->_modified[$cField_alias] != $this->_original[$cField_alias]
                    && ($this->id || (!$this->_system['consider'] && $this->_system['consider'][$cField_alias])))
                    $hasModifiedConsiderWHERE = true;

            // Else it mean that current-field is linked to variable entity, and that entity is identified by $cValue, so
            } else {

                // Setup model, that combo data will be fetched from
                $relatedM = $cValue ? m($cValue) : false;
            }
        }

        // If we have no related model - this happen if we have 'varibale entity'
        // consider type but that entity is not defined - we return empty rowset
        if (!$relatedM) return new Indi_Db_Table_Rowset(['titleColumn' => 'title', 'rowClass' => __CLASS__]);

        // Get title column
        $titleColumn = $fieldR->params['titleColumn'] ?: $relatedM->titleColumn();

        // Get tree column
        $treeColumn = $relatedM->treeColumn();

        // Get options limit. If field is readonly - prepare only 1 option to improve performance both
        // server side (data preeparation) and client-side (width calculation)
        // todo: implement proper logic for tree-like entities
        $limit = $fieldR->mode === 'readonly' && !$treeColumn
            ? (count(ar($this->{$fieldR->alias})) ?: 1)
            : self::$comboOptionsVisibleCount;

        // Set ORDER clause for combo data
        if (is_null($order)) {
            if ($relatedM->comboDataOrder) {
                $order = $relatedM->comboDataOrder;
                if (!@func_get_arg(7) && $relatedM->comboDataOrderDirection) $dir = $relatedM->comboDataOrderDirection;
                if (!preg_match('~^[a-zA-Z0-9]+$~', $order)) $order = str_replace('$dir', $dir, $order);
            } else if ($relatedM->fields('move')) {
                $order = 'move';
            } else {

                // Order by title column
                $order = $orderColumn = $titleColumn;

                // Build l10n-compatible version of $order for usage in sql queries
                if ($orderColumn != 'id' && $relatedM->fields($orderColumn)->l10n == 'y' && $l10n = true)
                    $order = 'SUBSTRING_INDEX(`' . $orderColumn . '`, \'"' . ini('lang')->admin . '":"\', -1)';
            }

            // If $order is not null, but is an empty string, we set is as 'id' for results being fetched in the order of
            // their physical appearance in database table, however, regarding $dir (ASC or DESC) param.
        } else if (!is_array($order) && !strlen($order)) {
            $order = 'id';
        }

        // Here and below we will be always checking if $order is not empty
        // because we can have situations, there order is not set at all and if so, we won't use ORDER clause
        // So, if order is empty, the results will be retrieved in the order of their physical placement in
        // their database table
        if (!is_array($order) && preg_match('~^[a-zA-Z0-9_]+$~', $order)) $order = '`' . $order . '`';

        // If fetch-mode is 'keyword'
        if ($selectedTypeIsKeyword) {
            $keyword = $selected;

        // Else if fetch-mode is 'no-keyword'
        } else {

            // Get selected row
            // todo: implement proper handling of $selected
            $selectedR = $relatedM->row('`id` IN (' . ($selected ?: 0) . ')');

            // Setup current value of a sorting field as start point
            if (!is_array($order) && $order && !preg_match('/[\(,]/', $orderColumn ?: $order))
                $keyword = $selectedR->{trim($orderColumn ?: $order, '`')};
        }

        // Owner WHERE
        if (admin() && admin()->table() != 'admin' && ($fieldR->filterOwner || $fieldR->params['filterOwner'])
            && ($ownerField = $relatedM->ownerField(admin()))
            && !array_key_exists('consider:' . $ownerField->alias, $where))
            $where['owner'] = $relatedM->ownerWHERE(admin());

        // If related entity has tree-structure
        if ($treeColumn) {

            // If we go lower, page number should be incremented, so if passed page number
            // is 1, it will be 2, because actually results of page 1 were already fetched
            // and displayed at the stage of combo first initialization
            if ($page != null) {
                if (!$selected || $selectedTypeIsKeyword || $hasModifiedConsiderWHERE) $page++;

                // Page number is not null when we are paging, and this means that we are trying to fetch
                // more results that are upper or lower and start point for paging ($selected) was not changed.
                // So we mark that foundRows property of rowset should be unset, as in indi.formcombo.js 'page-top-reached'
                // attribute is set depending on 'found' property existence in response json
                $unsetFoundRows = true;
            }

            // If $selected arg is a keyword
            if ($selectedTypeIsKeyword) {

                // Append additional ORDER clause, for grouping
                if ($groupByField = $relatedM->fields()->gb($fieldR->params['groupBy'], 'alias'))
                    if ($groupByFieldOrder = $groupByField->order($dir, $where))
                        $order = [$groupByFieldOrder, $order];

                // Fetch results
                $dataRs = $relatedM->fetchTree($where, $order, $limit, $page, 0, null, $keyword);

            // Else
            } else {

                // Append order direction
                if (!is_array($order)) $order .= ' ' . ($dir == 'DESC' ? 'DESC' : 'ASC');

                // Append additional ORDER clause, for grouping
                if ($groupByField = $relatedM->fields()->gb($fieldR->params['groupBy'], 'alias'))
                    if ($groupByFieldOrder = $groupByField->order($dir, $where))
                        $order = [$groupByFieldOrder, $order];

                if (!$hasModifiedConsiderWHERE) {
                    $dataRs = $relatedM->fetchTree($where, $order, $limit, $page, 0, $selected);
                } else {
                    $dataRs = $relatedM->fetchTree($where, $order, $limit, $page, 0, null, null);
                }
            }

            // Setup indents
            foreach ($dataRs as $dataR) $dataR->system('indent', indent($dataR->system('level')));

            // Unset found rows to prevent disabling of paging up
            if ($unsetFoundRows) $dataRs->found('unset');

        // Otherwise
        } else {

            // If we selected option is set, or if we have keyword that results should match, special logic will run
            if ($selected && (($fieldR->storeRelationAbility == 'one' && !$multiSelect) || $selectedTypeIsKeyword)) {

                // We do a backup for WHERE clause, because it's backup version
                // will be used to calc foundRows property in case if $selectedTypeIsKeyword = false
                $whereBackup = $where;

                // Get WHERE clause for options fetch
                if ($selectedTypeIsKeyword) {

                    // Check if keyword is a part of color value in format #rrggbb, and if so, we use RLIKE instead
                    // of LIKE, and prepare a special regular expression
                    if (preg_match('/^#[0-9a-fA-F]{0,6}$/', $keyword)) {
                        $rlike = '^[0-9]{3}' . $keyword . '[0-9a-fA-F]{' . (7 - mb_strlen($keyword, 'utf-8')) . '}$';
                        $where['lookup'] = '`' . $titleColumn . '` RLIKE "' . $rlike . '"';

                    // Else
                    } else $where['lookup'] = ($keyword2 = str_replace('"', '\"', Indi::kl($keyword)))
                        ? '(`' . $titleColumn . '` LIKE "%' . str_replace('"', '\"', $keyword) . '%" OR `' . $titleColumn . '` LIKE "%' . $keyword2 . '%")'
                        : '`' . $titleColumn . '` LIKE "%' . str_replace('"', '\"', $keyword) . '%"';

                // Else we should get results started from selected value only if consider-fields were not modified
                } else if (!$hasModifiedConsiderWHERE) {

                    // If $order is a name of a column, and not an SQL expression (except l10n-expression)
                    // we setup results start point as current row's column's value
                    if (!preg_match('/\(/', $order) || $l10n) {
                        if (preg_match('~/span>~', $keyword)) {
                            $order = 'SUBSTRING_INDEX(' . $order . ', "/span>", -1)';
                            $keyword = strip_tags($keyword);
                        }
                        $where['lookup'] = $order . ' '. (is_null($page) || $page > 0 ? ($dir == 'DESC' ? '<=' : '>=') : ($dir == 'DESC' ? '>' : '<')).' "' . str_replace('"', '\"', $keyword) . '"';
                    }

                    // We set this flag to true, because the fact that we are in the body of current 'else if' operator
                    // mean that:
                    // 1. we have selected value,
                    // 2. selected value is not a keyword,
                    // 3. none of consider-fields (if they exist) are changed
                    // 4. first option of final results, fetched by current function (getComboData) - wil be option
                    //    related to selected value
                    // So, we remember this fact, because if $found will be not greater than $limit
                    // there will be no need for results set to be started from selected value, and what is why this
                    $resultsShouldBeStartedFromSelectedValue = true;
                }

                // Get foundRows WHERE clause
                $foundRowsWhere = im($selectedTypeIsKeyword ? $where : $whereBackup, ' AND ');

                // Adjust WHERE clause so it surely match existing value
                if (!$hasModifiedConsiderWHERE) $this->comboDataExistingValueWHERE($foundRowsWhere, $fieldR, $consistence);

                // Use default WHERE if need - to improve performance as we're using InnoDB tables
                if (!strlen($foundRowsWhere)) $foundRowsWhere = '`id` > 0';

                // Get number of total found rows
                $found = db()->query(
                    "SELECT COUNT(`id`) FROM `{$relatedM->table()}` WHERE $foundRowsWhere"
                )->cell();

                // If results should be started from selected value but total found rows number if not too great
                // we will not use selected value as start point for results, because there will be a sutiation
                // that PgUp or PgDn should be pressed to view all available options in combo, instead of being
                // available all initially
                if ($resultsShouldBeStartedFromSelectedValue && $found <= $limit) {
                    unset($where['lookup']);
                }

                // Get results
                if (!is_null($page)) {
                    // If we go lower, page number should be incremented, so if passed page number
                    // is 1, it will be 2, because actually results of page 1 were already fetched
                    // and displayed at the stage of combo first initialization
                    if ($page > 0) {
                        $page++;

                        $order .= ' ' . ($dir == 'DESC' ? 'DESC' : 'ASC');

                        // Else if we go upper, but
                    } else if ($offset) {
                        $page++;
                        $order .= ' ' . ($dir == 'DESC' ? 'DESC' : 'ASC');

                        // Otherwise, if we go upper, we should make page number positive.
                        // Also we should adjust ORDER clause to make it DESC
                    } else {
                        $page = abs($page);
                        $order .= ' ' . ($dir == 'DESC' ? 'ASC' : 'DESC');

                        // We remember the fact of getting upper page results, because after results is fetched,
                        // we will revert them
                        $upper = true;
                    }

                // Else
                } else {

                    // Append order direction
                    $order .= ' ' . ($dir == 'DESC' ? 'DESC' : 'ASC');

                    // Adjust WHERE clause so it surely match existing value
                    if (!$selectedTypeIsKeyword && !$hasModifiedConsiderWHERE) $this->comboDataExistingValueWHERE($where, $fieldR, $consistence);
                }

                // Append additional ORDER clause, for grouping
                if ($groupByField = $relatedM->fields()->gb($fieldR->params['groupBy'], 'alias'))
                    if ($groupByFieldOrder = $groupByField->order($dir, $where))
                        $order = [$groupByFieldOrder, $order];

                // Fetch raw combo data
                $dataRs = $relatedM->all($where, $order, $limit, $page, $offset);

                // We set number of total found rows only if passed page number is null, so that means that
                // we are doing a search of first page of results by a keyword, that just has been recently changed
                // so at this time we need to get total number of results that match given keyword
                if (is_null($page)) {
                    $dataRs->found($found);
                } else {
                    $dataRs->found('unset');
                }

                // Reverse results if we were getting upper page results
                if ($upper) $dataRs->reverse();

            // If we don't have neither initially selected options, nor keyword
            } else {

                // If user try to get results of upper page, empty result set should be returned
                if ($page < 0) {
                    $dataRs = $this->model()->createRowset([]);

                // Increment page, as at stage of combo initialization passed page number was 0,
                // and after first try to get lower page results passed page number is 1, that actually
                // means that if we don't increment such page number, returned results for lower page
                // will be same as initial results got at combo initialization and that is a not correct
                // way.
                } else {

                    $order .= ' ' . ($dir == 'DESC' ? 'DESC' : 'ASC');

                    // Adjust WHERE clause so it surely match consistence values
                    if (is_null($page) && !$selectedTypeIsKeyword && !$hasModifiedConsiderWHERE)
                        $this->comboDataExistingValueWHERE($where, $fieldR, $consistence);

                    // Append additional ORDER clause, for grouping
                    if ($groupByField = $relatedM->fields()->gb($fieldR->params['groupBy'], 'alias'))
                        if ($groupByFieldOrder = $groupByField->order($dir, $where))
                            $order = [$groupByFieldOrder, $order];

                    // Fetch raw combo data
                    $dataRs = $relatedM->all($where, $order, $limit, $page + 1);
                }
            }
        }

        // If results should be grouped (similar way as <optgroup></optgroup> do)
        if ($fieldR->params['groupBy']) {

            // Get distinct values
            $distinctGroupByFieldValues = [];
            foreach ($dataRs as $dataR)
                if (!$distinctGroupByFieldValues[$dataR->{$fieldR->params['groupBy']}])
                    $distinctGroupByFieldValues[$dataR->{$fieldR->params['groupBy']}] = true;

            // Get group field
            $groupByFieldR = $fieldM->row([
                '`entityId` = "' . $fieldR->relation . '"',
                '`alias` = "' . $fieldR->params['groupBy'] . '"'
            ]);

            // If groupBy-field is not a foreign-key field
            if ($groupByFieldR->storeRelationAbility === 'none') {

                // Model is the same model where groupBy-field belongs
                $groupByFieldEntityM = m($fieldR->entityId);

                // Title column if groupBy-field's alias
                $groupBy_titleColumn = $groupByFieldR->alias;

                // Key property is the same
                $keyProperty = $groupBy_titleColumn;

                //
                $groupByRs = $dataRs;

            // Else
            } else {

                // If groupBy-field is a single-entity foreign-key field
                if ($groupByFieldEntityM = $groupByFieldR->rel()) {

                    // If groupBy-field is an enumset-field
                    if ($groupByFieldEntityM->table() == 'enumset') {

                        // Get groups by aliases
                        $groupByRs = $groupByFieldEntityM->all([
                            '`fieldId` = "' . $groupByFieldR->id . '"',
                            'FIND_IN_SET(`alias`, "' . implode(',', array_keys($distinctGroupByFieldValues)) . '")'
                        ]);

                        // Set key prop
                        $keyProperty = 'alias';

                    // Else
                    } else {

                        // Get groups by ids
                        $groupByRs = $groupByFieldEntityM->all(
                            'FIND_IN_SET(`id`, "' . implode(',', array_keys($distinctGroupByFieldValues)) . '")',
                            'FIND_IN_SET(`id`, "' . implode(',', array_keys($distinctGroupByFieldValues)) . '") ASC'
                        );

                        // Set key prop
                        $keyProperty = 'id';
                    }

                    // Get groupBy title-column
                    $groupBy_titleColumn = $groupByFieldEntityM->titleColumn();

                // Else if groupBy-field is a multi-entity foreign-key field
                } else {

                    // Model is entity-model
                    $groupByFieldEntityM = m('entity');

                    // Title column is title
                    $groupBy_titleColumn = 'title';

                    // Get groups by ids
                    $groupByRs = $groupByFieldEntityM->all(
                        'FIND_IN_SET(`id`, "' . implode(',', array_keys($distinctGroupByFieldValues)) . '")',
                        'FIND_IN_SET(`id`, "' . implode(',', array_keys($distinctGroupByFieldValues)) . '") ASC'
                    );

                    // Set key prop
                    $keyProperty = 'id';
                }
            }

            // Get titles for optgroups
            $groupByOptions = [];

            // If some of combo data entries are not within any group
            if ($groupByRs->count() < count($distinctGroupByFieldValues) && array_key_exists('0', $distinctGroupByFieldValues)) {
                $groupByOptions['0'] = ['title' => I_COMBO_GROUPBY_NOGROUP, 'system' => []];
            }

            // Foreach group
            foreach ($groupByRs as $groupByR) {

                // Here we are trying to detect, does $o->title have tag with color definition, for example
                // <span style="color: red">Some option title</span> or <font color=lime>Some option title</font>, etc.
                // We should do that because such tags existance may cause a dom errors while performing usubstr()
                $info = Indi_View_Helper_Admin_FormCombo::detectColor([
                    'title' => $groupByR->$groupBy_titleColumn,
                    'value' => $groupByR->$keyProperty
                ]);

                // Reset $system array
                $system = [];

                // If color was detected as a box, append $system['boxColor'] property
                if ($info['box']) $system['boxColor'] = $info['color'];

                // If non-box color was detected - setup a 'color' property
                if ($info['style']) $system['color'] = $info['color'];

                // Setup primary option data
                $groupByOptions [$groupByR->$keyProperty]= [
                    'id' => $groupByR->$keyProperty,
                    'title' => usubstr($info['title'], 50),
                    'system' => $system
                ];
            }

            $dataRs->optgroup = ['by' => $groupByFieldR->alias, 'groups' => array_values($groupByOptions)];
        }

        // If additional params should be passed as each option attributes, setup list of such params
        if ($fieldR->params['optionAttrs']) {
            $dataRs->optionAttrs = explode(',', $fieldR->params['optionAttrs']);
        }

        // Set `enumset` property as false, because without definition it will have null value while passing
        // to indi.formcombo.js and and after Indi.copy there - will have typeof == object, which is not actually boolean
        // and will cause problems in indi.formcombo.js
        $dataRs->enumset = false;

        if ($fieldR->storeRelationAbility == 'many' || $multiSelect) {
            if ($selected) {
                // Convert list of selected ids into array
                $selected = explode(',', $selected);

                // Get array of ids of already fetched rows
                $allFetchedIds = []; foreach ($dataRs as $dataR) $allFetchedIds[] = $dataR->id;

                // Check if some of selected rows are already presented in $dataRs
                $selectedThatArePresentedInCurrentDataRs = array_intersect($selected, $allFetchedIds);

                // Array for selected rows
                $data = [];

                // If some of selected rows are already presented in $dataRs, we pick them into $data array
                if (count($selectedThatArePresentedInCurrentDataRs))
                    foreach ($dataRs as $dataR)
                        if (in_array($dataR->id, $selectedThatArePresentedInCurrentDataRs))
                            $data[] = $dataR;

                // If some of selected rows are not presented in $dataRs, we do additional fetch to retrieve
                // them from database and append these rows to $data array
                if (count($selectedThatShouldBeAdditionallyFetched = array_diff($selected, $allFetchedIds))) {
                    $data = array_merge($data, $relatedM->all('
                        FIND_IN_SET(`id`, "' . implode(',', $selectedThatShouldBeAdditionallyFetched) . '")
                    ')->rows());
                }

                // Create unsorted rowset
                $unsortedRs = $relatedM->createRowset(['rows' => $data]);

                // Build array containing rows, that are ordered within that array
                // in the same order as their ids in $selected comma-separated string
                $sorted = []; foreach (ar($selected) as $id) if ($row = $unsortedRs->select($id)->at(0)) $sorted[] = $row;

                // Unset $unsorted
                unset($unsortedRs);

                // Setup `selected` property as a *_Rowset object, containing properly ordered rows
                $dataRs->selected = $relatedM->createRowset(['rows' => $sorted]);

                // Unset $sorted
                unset($sorted);

            // Else
            } else {
                $dataRs->selected = $relatedM->createRowset(['rows' => []]);
            }
        }

        // Setup combo data rowset title column
        $dataRs->titleColumn = $titleColumn;

        // If foreign data should be fetched
        if ($fieldR->params['foreign']) $dataRs->foreign($fieldR->params['foreign']);

        // Return combo data rowset
        return $dataRs;
    }


    protected function _comboDataConsiderWHERE(&$where, Field_Row $fieldR, Field_Row $cField, $cValue, $required, $cValueForeign = null) {

        // In case of variable-entity we don't build WHERE clause
        // unless this behaviour is overridden in child classes,
        // See Role_Row->_comboDataConsiderWHERE() as an example
        if (!$fieldR->relation && $cValueForeign) return false;

        // Setup a key, that current consider's WHERE clause will be set up within $where arg under,
        $_wkey = 'consider:' . $cField->alias;

        // If consider-value is zero
        if (!$cValue) {

            // But it's not required - set WHERE clause to be FALSE
            if ($required == 'y') return $where[$_wkey] = 'FALSE';

            // Return
            return false;
        }

        // Get column name
        $column = $cField->system('connector') ?: $cField->alias;

        // Set $multi flag, indicating single- or multi-key mode
        $multi = $cField->storeRelationAbility == 'many' && preg_match('~,~', $cValue);

        // Build WHERE clause
        return $where[$_wkey] = $multi
            ? 'CONCAT(",", `' . $column . '`, ",") REGEXP ",(' . implode('|', explode(',', $cValue)) . '),"'
            : 'FIND_IN_SET("' . $cValue . '", `' . $column . '`)';
    }

    /**
     * Get value for consider field, to be involved in building WHERE clause
     *
     * @param $for
     * @param $cField
     * @param $cValue
     * @return mixed
     */
    protected function _cValue($for, $cField, $cValue = null) {

        // Get consider-field's alias
        $prop = $cField->alias;

        // If we have no consider field's value passed as a param, we get it
        // from related row property or from consider-field zero value
        if (is_null($cValue)) $cValue = strlen($this->$prop) ? $this->$prop : $cValue = $cField->zeroValue();

        // Spoof consider value, if need
        return $this->_spoofConsider($for, $prop, $cValue);
    }

    /**
     * Build and return a <span/> element with css class and styles definitions, that will represent a color value
     * for each combo option, in case if combo options have color specification. This function was created for using in
     * optionTemplate param within combos because if combo is simultaneously dealing with color and with optionTemplate
     * param, javascript in indi.formcombo.js file will not create a color boxes to represent color-options,
     * because optionTemplate param assumes, that height of each combo option may be different with default height,
     * so default color box size may not match look and feel of options, builded with optionTemplate param usage.
     * So this function provides a possibility to define custom size for color box
     *
     * @param $colorField
     * @param string $size
     * @return string
     */
    public function colorBox($colorField, $size = '14x9') {
        list($width, $height) = explode('x', $size);
        if (preg_match('/^[0-9]{3}#([0-9a-fA-F]{6})$/', $this->$colorField, $matches)) {
            $style = ['background: #' . $matches[1]];
            if (strlen($width)) $style[] = 'width: ' . $width . 'px';
            if (strlen($height)) $style[] = 'height: ' . $height . 'px';
            return '<span class="i-combo-color-box" style="' . implode('; ', $style) . '"></span> ';
        } else {
            return '';
        }
    }

    /**
     * Strips hue value from color in format 'xxx#rrggbb', where xxx - is hue value
     *
     * @param $colorField
     * @return string
     */
    public function colorHex($colorField) {
        if (preg_match('/^[0-9]{3}#([0-9a-fA-F]{6})$/', $this->$colorField, $matches)) {
            return '#' . $matches[1];
        } else {
            return $this->$colorField;
        }
    }

    /**
     * Gets the foreign row by foreign key name, using it's current value
     *
     * @param string $key The  name of foreign key
     * @param bool $refresh If specified, cached foreign row will be refreshed
     * @param string $version Can be 'original', 'modified', 'current' or 'affected'. Default is 'current'
     *  original: $this->_original[$key]
     *  modified: $this->_modified[$key]
     *  current: array_key_exists($key, $this->_modified) ? $this->_modified[$key] : $this->_original[$key]
     *  affected: $this->_affected[$key]
     * @return Indi_Db_Table_Row|Indi_Db_Table_Rowset|null
     * @throws Exception
     */
    public function foreign($key = '', $refresh = false, $version = 'current') {

        // If $key argument is an array, it mean that key argument contains info about not only multiple foreign rows
        // to be fetched, but also info about sub-nested rowsets and sub-foreign rows that should be fetched too
        if (is_array($key)) {

            // Create a rowset with current row as a single row within that rowset
            $rowset = $this->model()->createRowset(['rows' => [$this]]);

            // Fetch all required data using rowset's foreign() method instead of row's foreign() method
            $rowset->foreign($key);

            // Pick the one existing row from rowset and unset rowset to release RAM
            $row = $rowset->rewind()->current(); unset($rowset);

            // Get the _foreign property of picked row and use it as value for $this's _foreign property
            $this->_foreign = $row->foreign();

            // Unset picked row to release RAM
            unset($row);

            // Return current row itself, but now with properly updated _foreign property
            return $this;
        }

        // If $key argument contains more than one key name - we setup rows for all keys
        if (preg_match('/,/',$key)) {

            // Explode keys by comma
            $keyA = explode(',', $key);

            // Fetch foreign data for each key separately
            foreach ($keyA as $keyI) {

                // If $refresh arg is boolean true, or if value, stored under $keyI was modified
                // set up $refresh_ flag as boolean true
                $refresh_ = $refresh ?: array_key_exists(trim($keyI), $this->_modified);

                // Fetch foreign data
                $this->foreign(trim($keyI), $refresh_);
            }

            // Return current row
            return $this;

        } else if (!$key) {
            return $this->_foreign;
        }

        // If $refresh argument is an object, we interpret it as a foreign row, and assign it directly
        if (is_string($key) && is_object($refresh)) return $this->_foreign[$key] = $refresh;

        // If $refresh arg is boolean true, or if value, stored under $key was modified
        // set up $refresh_ flag as boolean true
        $refresh_ = $refresh ?: array_key_exists(trim($key), $this->_modified);

        // If foreign row, got by foreign key, was got already got earlier, and no refresh should be done - return it
        if (array_key_exists($key, $this->_foreign) && !$refresh_ && func_num_args() < 3) {
            return $this->_foreign[$key];

        // Else
        } else {

            // If field, representing foreign key - is exist within current entity
            if ($fieldR = $this->model()->fields($key)) {

                // If field do not store foreign keys - throw exception
                if ($fieldR->storeRelationAbility == 'none'
                    || ($fieldR->relation == 0 && ($fieldR->dependency != 'e' && !$fieldR->nested('consider')->count())))
                    throw new Exception('Field with alias `' . $key . '` (`' . $fieldR->alias . '`) within entity with table name `' . $this->_table .'` is not a foreign key');

                // Get foreign key value
                if ($version === 'current') $val = $this->$key;
                else $val = $this->{"_$version"}[$key];

                // If $refresh arg is 'ins' or 'del'
                if (in($refresh, 'ins,del,was')) {

                    // If foreign key field is a multi-value field and $refresh arg is 'ins' or 'del'
                    if ($fieldR->original('storeRelationAbility') == 'many' && in($refresh, 'ins,del')) {

                        // We need to fetch foreign data for keys, inserted or deleted from the field's value,
                        // so we replace existing keys list with list of inserted or removed keys, to be used for fetching
                        $val = ($diff = $this->adelta($key, $refresh)) ? im($diff) : '';

                    // Else if foreign key field is a single-value field, or $refresh arg is 'was',
                    // we need to fetch foreign data for previous key,
                    // so we replace existing keys list with previous keys list
                    } else if ($refresh == 'was') $val = $this->_affected[$key] ?: '';

                    // Setup $aux flag as `true` to indicate that current call of foreign() method
                    // - is an auxiliary call, and therefore, fethed foreign data that shouldn't be
                    // saved within $this->_foreign array
                    $aux = true;
                }

                // If field is able to store single key, or able to store multiple, but current key's value isn't empty
                if ($fieldR->original('storeRelationAbility') == 'one' || strlen($val) || $aux) {

                    // Determine a model, for foreign row to be got from. If consider type is 'variable entity',
                    // then model is a value of consider-field, or it's foreign field. Otherwise model is field's `relation` property
                    if ($fieldR->relation) $model = $fieldR->relation; else {

                        // Get first `consider` entry
                        $considerR = $fieldR->nested('consider')->at(0);

                        // Get `field` entry that `consider` entry is representing
                        $cField = $considerR->foreign('consider');

                        // Get model
                        $model = $cValue = $this->{$cField->alias};

                        // If `consider` entry has non-zero `foreign` prop
                        if ($considerR->foreign) {

                            // Get that foreign-key field
                            $cField_foreign = $considerR->foreign('foreign');

                            // Get entry, identified by current value of consider-field
                            $cEntryR = m($cField->relation)->row($cValue);

                            // Spoof model
                            $model = $cValue = $cEntryR->{$cField_foreign->alias};
                        }
                    }

                    // Determine a fetch method
                    $methodType = $fieldR->original('storeRelationAbility') == 'many' ? 'All' : 'Row';

                    // If current field is an imitated-field, and is an enumset-field
                    if (!array_key_exists($fieldR->alias, $this->_original) && $model == 6) {

                        // Get foreign data rowset, even iа such rowset contains only one row
                        $foreign = $fieldR->nested('enumset')->select($val, 'alias');

                        // If field's storeRelationAbility is 'one' - pick first
                        if ($methodType == 'Row') $foreign = $foreign->at(0);

                    // Else if model is non-zero
                    } else if ($model) {

                        // Declare array for WHERE clause
                        $where = [];

                        // If field is related to enumset entity, we should append an additional WHERE clause,
                        // that will outline the `fieldId` value, because in this case current row store aliases
                        // of rows from `enumset` table instead of ids, and aliases are not unique within that table.
                        if (m($model)->table() == 'enumset') {
                            $where[] = '`fieldId` = "' . $fieldR->id . '"';
                            $col = 'alias';
                        } else {
                            $col = 'id';
                        }

                        // If foreign model's `preload` flag was turned On
                        if (m($model)->preload()) {

                            // Use preloaded data as foreign data rather than
                            // obtaining foreign data by separate sql-query
                            $foreign = m($model)->{'preloaded' . $methodType}($val);

                        // Else fetch foreign from db
                        } else {

                            // Finish building WHERE clause
                            $where[] = '`' . $col . '` ' .
                                ($fieldR->original('storeRelationAbility') == 'many'
                                    ? 'IN(' . (strlen($val) ? (Indi::rexm('int11list', $val) ? $val : '"' . im(ar($val), '","') . '"') : 0) . ')'
                                    : '= "' . $val . '"');

                            // Fetch foreign row/rows
                            $foreign = m($model)->{'fetch' . $methodType}(
                                $where,
                                $fieldR->original('storeRelationAbility') == 'many'
                                    ? 'FIND_IN_SET(`' . $col . '`, "' . $val . '")'
                                    : null
                            );
                        }
                    } else {
                        $foreign = null;
                    }
                }

            // Else there is no such a field within current entity - throw an exception
            } else {
                throw new Exception('Field with alias `' . $key . '` does not exists within entity with table name `' . $this->_table .'`');
            }

            // Save foreign row within a current row under key name, and return it
            return $aux ? $foreign : $this->_foreign[$key] = $foreign;
        }
    }

    /**
     * Return a model, that current row is related to
     *
     * @return Indi_Db_Table
     */
    public function model() {
        return m($this->_table);
    }

    /**
     * Return a database table name, that current row is dealing with
     *
     * @return string
     */
    public function table() {
        return $this->_table;
    }

    /**
     * Provide Toggle On/Off action
     *
     * @throws Exception
     */
    public function toggle() {

        // If `toggle` column exists
        if ($field = $this->model()->fields('toggle')) {

            // Get values
            $values = $field->nested('enumset')->column('alias');

            // Append first item to be the last item
            $values []= $values[0];

            // Get index of current value
            $index = array_search($this->toggle, $values);

            // Setup next value
            $this->toggle = $values[$index + 1];
            $this->save();

            // Else throw exception
        } else throw new Exception('Column `toggle` does not exist');
    }

    /**
     * Mark for deletion
     */
    public function m4d() {

        // If `m4d` field does not exist - flush an error message
        if (!$this->model()->fields('m4d')) jflush(false, sprintf(I_ROWM4D_NO_SUCH_FIELD, $this->model()->title()));

        // Do mark
        $this->m4d = 1;

        // Save
        $this->save();
    }

    /**
     * Delete all files (images, etc) that have been attached to row. Names of all uploaded files,
     * are constructed by the following pattern
     * <upload path>/<entity|table|model name>/<row id>_<file upload field alias>.<file extension>
     * or
     * <upload path>/<entity|table|model name>/<row id>_<file upload field alias>,<resized copy name>.<file extension>
     *  (in case if uploaded file is an image, and resized copy autocreation was set up)
     *
     * This function get all parts of the pattern, build it, and finds all files that match this pattern
     * with glob() php function usage, so uploaded files names and/or paths and/or extensions are not stored in db,
     *
     * @param string $field The alias of field, that is File Upload field (Aliases of such fields are used in the
     *                     process of filename construction for uploaded files to saved under)
     * @throws Exception
     */
    public function deleteFiles($field = '') {

        // If upload dir does not exist - return
        if (($dir = $this->dir('exists')) === false) return;

        // If $field argument is not given
        if (!$field) {

            // We assume that all files, uploaded using all (not certain) file upload fields should be deleted,
            // so we get the array of aliases of file upload fields within entity, that current row is related to
            $alias = [];
            foreach ($this->model()->fields() as $fieldR)
                if ($fieldR->foreign('elementId')->alias == 'upload')
                    $alias[] = $fieldR->alias;

            // Spoof $field arg
            $field = $alias;
        }

        // If value of $field variable is still empty - return
        if (!$field) return;

        // Use file upload fields aliases list to build a part of a pattern for use in php glob() function
        $field = '{' . im(ar($field)) . '}';

        // Get all of the possible files, uploaded using that field, and all their versions
        $fileA = glob($dir . $this->id . '_' . $field . '[.,]*', GLOB_BRACE);

        // Delete them
        foreach ($fileA as $fileI) @unlink($fileI);
    }


    /**
     * Delete all of the files/folders uploaded/created as a result of CKFinder usage. Actually,
     * this function can do a deletion only in one case - if entity, that current row is related to
     * - is involved in 'alternate-cms-users' feature. This feature assumes, that any row, related to
     * such an entity/model - is representing a separate user account, that have ability to sign in into the
     * Indi Engine system interface, and therefore may have access to CKFinder
     *
     * @return mixed
     */
    public function deleteCKFinderFiles () {

        // If current entry belongs to entity, that is not a role - return
        if (!$this->model()->hasRole()) return;

        // If CKFinder upload dir (special dir for current row instance) does not exist - return
        if (($dir = $this->model()->dir('exists', $this->id)) === false) return;

        // Delete recursively all the contents - folder and files
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        // Remove the directory itself
        rmdir($dir);
    }

    /**
     * Get the absolute path to a file, that was attached to current row by uploading within field with $alias alias.
     * If $copy argument is given, function will return a path to a resized copy of file - of course if uploaded file
     * was an image. If file was not found - return false;
     *
     * @param string $alias
     * @param string $copy
     * @return bool|string
     */
    public function abs($alias, $copy = '') {

        // Here were omit STD's one or more dir levels at the ending, in case if
        // ini('upload')->path is having one or more '../' at the beginning
        $std = STD; $uph = ini('upload')->path;
        if (preg_match(':^(\.\./)+:', $uph, $m)) {
            $uph = preg_replace(':^(\.\./)+:', '', $uph);
            $lup = count(explode('/', rtrim($m[0], '/')));
            for ($i = 0; $i < $lup; $i++) $std = preg_replace(':/[a-zA-Z0-9_\-]+$:', '', $std);
        }

        // Get the name of the directory, relative to document root,
        // and where all files related model of current row are located
        $src =  $std . '/' . $uph . '/' . $this->_table . '/';

        // If field have l10n turned on and tpldoc exists for that field prepare language-part of glob() pattern
        if ($this->id && $this->field($alias)->l10n == 'y')
            if ($this->tpldoc($alias, true, $_ = ini('lang')->admin))
                $lang = '-' . $_;

        // Build the filename pattern for using in glob() php function
        $pat = DOC . $src . $this->filesGroup() . $this->id . ($alias ? '_' . $alias : '') . ($copy ? ',' . $copy : '') . $lang . '.' ;

        // Get the files, matching $pat pattern
        $files = glob($pat . '*');

        // If no files found, return false
        if (count($files) == 0) {

            // If it's an existing entry and setter method for creating the document from template - is defined
            if ($this->id && method_exists($this, $method = '_' . $alias)) {

                // Call setter method
                $this->{$method}();

                // Get the files, matching $pat pattern
                $files = glob($pat . '*');

                // Return found file or false
                return $files ? $files[0] : false;
            }

            // Return false
            return false;
        }

        // Else return absolute path to first found file
        return $files[0];
    }

    /**
     * Check whether `fileGroupBy` prop defined for current entity,
     * and if yes - build and return group-subdir name with trailing slash
     */
    public function filesGroup() {

        // If files grouping is not defined - return
        if (!$groupBy = $this->model()->filesGroupBy(true)) return;

        // Return group subdir with trailing slash
        return $this->$groupBy . '/';
    }

    /**
     * Get the relative ( - relative to document root ) filename of the uploaded file, that was attached to current row
     * by uploading within field with $alias alias. If $copy argument is given, function will return a path
     * to a resized copy of file - of course if uploaded file was an image. If file was not found - will return false.
     *
     * @param $alias
     * @param string $copy
     * @param bool $dc Whether or not to append modification timestamp, for disabling browser cache
     * @param bool $std Whether or not to prepend returned value with STD
     * @return string|null
     */
    public function src($alias, $copy = '', $dc = true, $std = false) {

        // Get the filename with absolute path
        if ($abs = preg_match('/^([A-Z]:|\/)/', $alias) ? $alias : $this->abs($alias, $copy)) {

            // Here were omit STD's one or more dir levels at the ending, in case if
            // ini('upload')->path is having one or more '../' at the beginning
            $std_ = STD;
            if (preg_match(':^(\.\./)+:', ini('upload')->path, $m)) {
                $lup = count(explode('/', rtrim($m[0], '/')));
                for ($i = 0; $i < $lup; $i++) $std_ = preg_replace(':/[a-zA-Z0-9_\-]+$:', '', $std_);
            }

            // Return path, relative to document root
            return str_replace(DOC . ($std ? '' : $std_), '', $abs) . ($dc ? '?' . filemtime($abs) : '');
        }
    }

    /**
     * Create an return an <embed> tag with 'src' attribute, pointing to uploaded file
     * of type application/x-shockwave-flash, if it exists, or return false otherwise
     *
     * @param $alias Alias of field of 'File' type
     * @param string $attr Additional attributes list for <embed> tag
     * @return bool|string
     */
    public function swf($alias, $attr = '') {

        // If uploaded file exists, get it src
        if ($src = $this->src($alias))

            // Return <embed> tag with found src
            return '<embed src="' . $src .'" border="0"' . ($attr ? $attr : '') . '/>';

        // Return false otherwise
        return false;
    }

    /**
     * Build and return an <img> tag, representing an uploaded image
     *
     * @param null $alias Alias of field, image was uploaded using by
     * @param null $copy Name of image resized copy, if resized copy should be displayed instead of original image
     * @param string $attr Attributes list for <img> tag
     * @param bool $noCache Append image last modification time to 'src' attribute
     * @param bool $size Include real dimensions info as 'real-width' and 'real-height' attributes within <img> tag
     * @return bool|string Built <img> tag, of false, if image file does not exists
     */
    public function img($alias = null, $copy = null, $attr = '', $noCache = true, $size = true) {

        // If image file exists
        if ($abs = $this->abs($alias, $copy)) {

            // Start building <img> tag
            $img = '<img';

            // Get image filename, relative to $_SERVER['DOCUMENT_ROOT']
            $src = str_replace(DOC . STD, '', $abs);

            // If $noCache argument is true, we append file modification time to 'src' attribute
            if ($noCache) $src .= '?' . filemtime($abs);

            // Append 'src' attribute to <img> tag
            $img .= ' src="' . $src . '"';

            // If $size argument is true, we should mention real image dimensions as additional attributes
            if ($size) {
                list($w, $h) = getimagesize($abs);
                $img .= ' real-width="' . $w . '" real-height="' . $h . '"';
            }

            // If $attr argument is specified, we append it to <img> tag
            if ($attr) $img .= ' ' .$attr;

            // If $attr argument do not contain 'alt' attribute, we append it, but with empty value
            if (!preg_match('/alt="/', $attr)) $img .= ' alt=""';

            // Close <img> tag and return it
            return $img . '/>';
        }

        // Return false
        return false;
    }

    /**
     * Get qty of nested entries by simple sql-query
     *
     * @param $table
     * @param $where
     * @return int
     */
    public function qty($table, $where = null, $connector = null) {
        return $this->nestedSummary($table, 'COUNT', 'id', $where, $connector);
    }

    /**
     * Get sum for given column for nested entries using simple sql-query
     *
     * @param $table
     * @param $where
     * @return int
     */
    public function sum($table, $column, $where = null, $connector = null) {
        return $this->nestedSummary($table, 'SUM', $column, $where, $connector);
    }

    /**
     * Get certain type of summary among certain nested entries
     *
     * @param $table
     * @param string $summaryType
     * @param string $column
     * @param null $where
     * @param null $connector
     * @return int|mixed
     */
    public function nestedSummary($table, $summaryType = 'COUNT', $column = 'id', $where = null, $connector = null) {

        // Connector
        if (!$connector) $connector = $this->_table . 'Id';

        // Build part of WHERE clause, responsible for nesting
        $nested = ['`' . $connector . '` = "' . $this->id . '"'];

        // If $where arg is an array
        if (is_array($where)) $nested = array_merge($nested, count($where = un($where, [null, ''])) ? $where : []);

        // Else if where arg is a non-empty string
        else if (strlen($where)) $nested []= "($where)";

        // Prepare WHERE clause
        $where = im($nested, ' AND ');

        // Build the query
        $sql = "SELECT $summaryType(`$column`) FROM `$table` WHERE $where";

        // Get qty
        return db()->query($sql)->cell() ?: 0;
    }

    /**
     * Fetch the rowset, nested to current row, assing that rowset within $this->_nested array under certain key,
     * and return that rowset
     *
     * @param string $table A table, where rowset will be fetched from
     * @param array $fetch Array of fetch params, that are same as Indi_Db_Table::fetchAll() possible arguments
     * @param null $alias The key, that fetched rowset will be stored in $this->_nested array under
     * @param null $field Connector field, in case if it is different from $this->_table . 'Id'
     * @param bool $fresh Flag for rowset refresh
     * @return Indi_Db_Table_Rowset object
     * @throws Exception
     */
    public function nested($table, $fetch = [], $alias = null, $field = null, $fresh = false) {

        // Id $fetch argument is object, we interpret it as nested data, so we assign it directly
        if (is_object($fetch)) return $this->_nested[$table] = $fetch;

        // Else if it's a non-associative array - assume it's a data to be
        // used for creating a rowset and putting it under $table key within $this->_nested prop
        else if (is_array($fetch) && preg_match('~^[0-9,]+$~', im(array_keys($fetch)))) {

            // Get rowset class
            $rowsetClass = m($table)->rowsetClass();

            // Create the rowset and assign it directly
            return $this->_nested[$table] = new $rowsetClass([
                'table' => $table,
                'data' => $fetch,
                'rowClass' => m($table)->rowClass(),
                'found' => count($fetch)
            ]);
        }

        // Determine the nested rowset identifier. If $alias argument is not null, we will assume that needed rowset
        // is or should be stored under $alias key within $this->_nested array, or under $table key otherwise.
        // This is useful in cases when we need to deal with nested rowsets, got from same database table, but
        // with different fetch params, such as WHERE, ORDER, LIMIT clauses, etc.
        $key = $alias ?: $table;

        // If needed nested rowset is already exists within $this->_nested array, and it shouldn't be refreshed
        if (array_key_exists($key, $this->_nested) && !$fresh) {

            // If $fetch argument is 'unset', we do unset nested data, stored under $key key within $this->_nested
            // Here we use $fetch argument, instead of $fresh agrument, for more friendly unsetting usage, e.g
            // $rs->nested('table', 'unset') instead of $rs->nested('table', null, null, null, 'unset')
            if ($fetch == 'unset') {

                // Unset nested data
                unset($this->_nested[$key]);

                // Return row itself
                return $this;

            // Else we return it
            } else return $this->_nested[$key];

        // Otherwise we fetch it, assign it under $key key within $this->_nested array and return it
        } else {

            // Determine the field, that is a connector between current row and nested rowset
            $connector = $field ? $field : $this->_table . 'Id';

            // If $fetch argument is array
            if (is_array($fetch)) {

                // Define the allowed keys within $fetch array
                $params = ['where', 'order', 'count', 'page', 'offset', 'foreign', 'nested'];

                // Unset all keys within $fetch array, that are not allowed
                foreach ($fetch as $k => $v) if (!in_array($k, $params)) unset($fetch[$k]);

                // Extract allowed keys with their values from $fetch array
                extract($fetch);
            }

            // Convert $where to array
            $where = isset($where) && is_array($where) ? $where : (strlen($where) ? [$where] : []);

            // If connector field store relation ability is multiple
            if (m($table)->fields($connector)->storeRelationAbility == 'many')

                // We use FIND_IN_SET sql expression for prepending $where array
                array_unshift($where, 'FIND_IN_SET("' . $this->id . '", `' . $connector . '`)');

            // Else if connector field store relation ability is single
            else if (m($table)->fields($connector)->storeRelationAbility == 'one')

                // We use '=' sql expression for prepending $where array
                array_unshift($where, '`' . $connector . '` = "' . $this->id . '"');

            // Else an Exception will be thrown, as connector field do not exists or don't have store relation ability
            else throw new Exception();

            // Fetch nested rowset, assign it under $key key within $this->_nested array
            $method = m($table)->treeColumn() ? 'fetchTree' : 'fetchAll';
            $this->_nested[$key] = m($table)->$method($where, $order, $count, $page, $offset);

            // Setup foreign data for nested rowset, if need
            if ($foreign) $this->_nested[$key]->foreign($foreign);

            // Setup nested data for nested rowset, if need
            if (is_string($nested)) $this->_nested[$key]->nested($nested);
            else if (is_array($nested))
                foreach ($nested as $args)
                    $this->_nested[$key]->nested($args[0], $args[1], $args[2], $args[3], $args[4]);

            // Return nested rowset
            return $this->_nested[$key];
        }
    }

    /**
     * Returns the column/value data as an array.
     * If $type param is set to current (by default), the returned array will contain original data
     * with overrided values for keys of $this->_modified array
     *
     * @param string $type current|original|modified|temporary
     * @param bool $deep
     * @param null $purp
     * @return array
     */
    public function toArray($type = 'current', $deep = true, $purp = null) {
        if ($type == 'current') {

            // Merge _original, _modified, _compiled and _temporary array of properties
            $array = (array) array_merge($this->fixTypes($this->_original), $this->_modified, $purp == 'form' ? [] : $this->_compiled, $this->_temporary);

            // Setup filefields values
            foreach ($this->model()->getFileFields() as $fileField) $array[$fileField] = $this->$fileField;

            // Append _system array as separate array within returning array, under '_system' key
            if (count($this->_system)) $array['_system'] = $this->_system;

            // Append _view array as separate array within returning array, under '_view' key
            if (count($this->_view)) $array['_view'] = $this->_view;

        } else if ($type == 'original') {
            $array = (array) $this->fixTypes($this->_original);
        } else if ($type == 'modified') {
            $array = (array) $this->_modified;
        } else if ($type == 'temporary') {
            $array = (array) $this->_temporary;
        }

        if ($deep) {
            if (count($this->_foreign))
                foreach ($this->_foreign as $alias => $row)
                    if (is_object($row) && $row instanceof Indi_Db_Table_Row)
                        $array['_foreign'][$alias] = $row->toArray($type, $deep);

            if (count($this->_nested))
                foreach ($this->_nested as $alias => $rowset)
                    if (is_object($rowset) && $rowset instanceof Indi_Db_Table_Rowset)
                        $array['_nested'][$alias] = $rowset->toArray($deep);
        }

        // Return
        return $array;
    }

    /**
     * Test existence of row field
     *
     * @param  string  $columnName   The column key.
     * @return boolean
     */
    public function __isset($columnName) {
        return array_key_exists($columnName, $this->_original);
    }

    /**
     * Retrieve row field value
     *
     * @param  string $columnName The user-specified column name.
     * @return string             The corresponding column value.
     */
    public function __get($columnName) {

        // We trying to find the key's ($columnName) value at first in $this->_modified array,
        // then in $this->_original array, and then in $this->_temporary array, and return
        // once value was found
        if (array_key_exists($columnName, $this->_modified)) return $this->_modified[$columnName];
        else if (array_key_exists($columnName, $this->_language) && is_array($this->_language[$columnName]))
            return $this->_language[$columnName][array_key_exists(ini('lang')->admin, $this->_language[$columnName])
                ? ini('lang')->admin
                : key(Lang::$_jtpl[$this->fraction()])
        ];
        else if (array_key_exists($columnName, $this->_original)) return $this->_original[$columnName];
        else if (array_key_exists($columnName, $this->_temporary)) return $this->_temporary[$columnName];
        else if ($fieldR = $this->model()->fields($columnName)) if ($fieldR->foreign('elementId')->alias == 'upload')
            return $this->src($columnName, '', false);
    }

    /**
     * Set row field value, by creating an item of $this->_modified array, in case if
     * value is different from value of $this->_original at same key ($columnName)
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // If $columnName is exists as one of keys within $this->_original array
        if (array_key_exists($columnName, $this->_original)) {

            // If this value is not equal to value, already existing in $this->_original array under same
            // key ($columnName), we put this value in a $this->_modified array
            if ($this->_original[$columnName] !== $value)
                $this->_modified[$columnName] = $value;

            // Else we unset value, stored in $this->_modified array under $columnName key, because the fact
            // that we are here mean value is now the exact same as it was originally, so we need to unset
            // info about it's modification, as it is actually no more modified
            else unset($this->_modified[$columnName]);

            // Else we put this value in $this->_temporary array
        } else $this->_temporary[$columnName] = $value;
    }

    /**
     * Strips all tags from $html argument, except tags mentioned in $tags argument as a comma-separated list,
     * then strip event attributes from these tags, and after that return result
     *
     * @static
     * @param $html
     * @param string $allowedTags
     * @return string
     */
    public static function safeHtml($html, $allowedTags = '') {

        // If $allowedTags arg is '*' - return as is. This may be useful
        // in case if there is a need to save raw (for example: parsed) html code
        // and view it within a textarea rather than some WYSIWYG editor
        if ($allowedTags == '*') return $html;

        // Build list of allowed tags, using tags, passed with $allowedTags arg and default tags
        $allowedS = im(array_unique(array_merge(ar('font,span,br'), ar(strtolower($allowedTags)))));

        // Prepare placeholder for '<=', as it's incorrectly evaluated by strip_tags()
        $placeholder = 'less_or_equal' . grs();

        // Replace '<=' with placeholders
        $html = str_replace('<=', $placeholder, $html);

        // Strip all tags, except tags, mentioned in $tags argument
        $html = strip_tags($html, '<' . preg_replace('/,/', '><', $allowedS) . '>');

        // Replace placeholders back to '<='
        $html = str_replace($placeholder, '<=', $html);

        // Strip event attributes, and return the result
        return self::safeAttrs($html);
    }

    /**
     * Strip event attributes from tags, that are exist in $html argument, and return the result
     *
     * @static
     * @param $html
     * @return mixed
     */
    public static function safeAttrs($html) {

        // Declare a callback function for usage within preg_replace_callback() php function
        if (!function_exists('safeAttrsCallback')) {
            function safeAttrsCallback($m) {
                $m[2] = preg_replace('/\s+on[a-zA-Z0-9]+\s*=\s*"[^">]+"/', '', $m[2]);
                $m[2] = preg_replace("/\s+on[a-zA-Z0-9]+\s*=\s*'[^'>]+'/", '', $m[2]);
                return $m[1] . $m[2] . $m[5];
            }
        }

        // Replace double and single quotes that are prepended with a backslash, with their special charachers
        $html = preg_replace('/\\\\"/', '&quot;', $html); $html = preg_replace("/\\\\'/", '&#039;', $html);

        // Strip event attributes, using a callback function
        $html = preg_replace_callback('/(<[a-zA-Z0-9]+)((\s+[a-zA-Z0-9]+\s*=\s*("|\')[^\4>]+\4)*)\s*(\/?>)/', 'safeAttrsCallback', $html);

        // Restore double and single quotes
        $html = preg_replace('/&quot;/', '"', $html); $html = preg_replace('/&#039;/', "'", $html);

        // Return result
        return $html;
    }

    /**
     * If $check argument is set to false or not given - return the stack of errors,
     * appeared while try to save current row. Otherwise, if $check argument is set to true,
     * do a check for $this->_modifed values conformance to all possible requirements, e.g.
     * control element requirements, mysql column type requirements and additional/user-defined requirements
     *
     * @param bool $check
     * @param null|string $message
     * @return array
     */
    public function mismatch($check = false, $message = null) {

        // If $check argument is boolean
        if (is_bool($check)) {

            // If $check argument is set to false, return $this->_mismatch stack, else reset $this->_mismatch array
            if ($check == false) return $this->_mismatch; else $this->_mismatch = [];

        // Else if $check argument is not boolean, and, additionally, $message argument was given
        } else if (func_num_args() == 2) {

            // If $message argument was given, and it is strict null
            if ($message === null) {

                // Delete the item, stored under $check key from $this->_mismatch array
                unset($this->_mismatch[$check]);

                // Return array of all remaining mismatches
                return $this->_mismatch;

            // Else we explicitly setup $message as an item within $this->_mismatch array, under $check key
            } else return $this->_mismatch[$check] = $message;

        // Else we assume that $check argument is field name, so the mismatch for especially that field will be returned
        } else return $this->_mismatch[$check];

        // Return array of errors
        return $this->scratchy() ?: $this->validate() ?: $this->_mismatch;
    }

    /**
     * Custom validation function, to be overridden in child classes if need
     *
     * @return array
     */
    public function validate() {
        return $this->_mismatch;
    }

    /**
     * Validate all modified fields to ensure all of them have values, compatible with their data-types,
     * collect their errors in $this->_mismatch array, with field names as keys and return it
     *
     * If there is a need to immediately flush data-types error (if detected) - pass `true` as $mflush arg
     *
     * @param bool $mflush
     * @return array
     */
    public function scratchy($mflush = false) {

        // Declare an array, containing aliases of control elements, that can deal with array values
        $arrayAllowed = ['multicheck', 'time', 'datetime'];

        // For each $modified field
        foreach ($this->_modified as $column => $value) {

            // If $column is 'id', so no Field_Row instance can be found
            if ($column == 'id') {

                // If $value is not a decimal - push a error to errors stack
                if (!preg_match(Indi::rex('int11'), $value))
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11, $value, 'ID');

                // Jump to checking the next column's value
                continue;
            }

            // Get the field
            $fieldR = $this->model()->fields($column);

            // Get the control element
            $elementR = $fieldR->foreign('elementId');

            // Get the control element
            $entityR = $fieldR->foreign('relation');

            // If $value is an object - push a message to $this->_mismatch stack,
            // stop dealing with the current column's value and continue with the next
            if (is_object($value)) {

                // Push a error to errors stack
                $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT, $fieldR->title);

                // Jump to checking the next column's value
                continue;
            }

            // If $value is an array, but current field's control element do not deal with arrays
            // - push a message to $this->_mismatch stack, stop dealing with the current column's
            // value and continue with the next
            if (is_array($value) && !in_array($elementR->alias, $arrayAllowed)) {

                // Push a error to errors stack
                $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY, $fieldR->title);

                // Jump to checking the next column's value
                continue;
            }

            // If element is 'string' or 'text'
            if (preg_match('/^string|textarea$/', $elementR->alias)) {

                // If field is in list of eval fields, and current field's value contains php expressions
                if (in_array($fieldR->alias, $this->model()->getEvalFields()) && preg_match(Indi::rex('phpsplit'), $value)) {

                    // Split value by php expressions
                    $chunk = preg_split(Indi::rex('phpsplit'), $value, -1, PREG_SPLIT_DELIM_CAPTURE);

                    // Declare a variable for filtered value
                    $value = '';

                    // For each chunk
                    for ($i = 0; $i < count($chunk); $i++) {

                        // If chunk is a part of php expression - append that php expression to filtered value
                        if ($chunk[$i] == '<?') {
                            $php = $chunk[$i] . $chunk[$i+1] . $chunk[$i+2];
                            $value .= $php;
                            $i += 2;

                        // Else if chunk is not a php expression - make it safe and append to filtered value
                        } else  $value .= self::safeHtml($chunk[$i], $fieldR->params['allowedTags']);
                    }

                // Else field is not in list of eval fields, make it's value safe by stripping restricted html tags,
                // and by stripping event attributes from allowed tags
                } else $value = self::safeHtml($value, $fieldR->params['allowedTags']);

            // If element is 'move'
            } else if ($elementR->alias == 'move') {

                // If $value is not a decimal
                if (!preg_match(Indi::rex('int11'), $value)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11, $value, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

            // If element is 'price'
            } else if ($elementR->alias == 'price') {

                // Round the value to 2 digits after floating point
                if (is_numeric($value)) $value = price($value);

                // If $value is not a decimal
                if (!preg_match(Indi::rex('decimal112'), $value)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112, $value, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

                // If element is 'decimal143'
            } else if ($elementR->alias == 'decimal143') {

                // Round the value to 2 digits after floating point
                if (is_numeric($value)) $value = decimal($value, 3);

                // If $value is not a decimal
                if (!preg_match(Indi::rex('decimal143'), $value)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143, $value, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

            // If element is 'radio', or element is 'combo' and field store relation ability is 'one'
            } else if ($elementR->alias == 'radio' || ($elementR->alias == 'combo' && $fieldR->storeRelationAbility == 'one')) {

                // If field deals with values from 'enumset' table
                if ($entityR->table == 'enumset') {

                    // Get the possible field values
                    $possible = $fieldR->nested('enumset')->column('alias');

                    // If $value is not a one of possible values
                    if (!in_array($value, $possible)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // Else if field deals with foreign keys of other tables
                } else {

                    // If $value is not a decimal
                    if (!preg_match(Indi::rex('int11'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }
                }

            // If element is 'multicheck' or element is 'combo' and field relation ability is 'many'
            } else if ($elementR->alias == 'multicheck' || ($elementR->alias == 'combo' && $fieldR->storeRelationAbility == 'many')) {

                // Implode the values list by comma
                if (is_array($value)) $value = implode(',', $value);

                // Trim the ',' from value
                $value = trim($value, ',');

                // If value is not empty
                if (strlen($value)) {

                    // Get the values array
                    $valueA = explode(',', $value);

                    // If field deals with values from 'enumset' table
                    if ($entityR->table == 'enumset') {

                        // Get the possible field values
                        $possible = $fieldR->nested('enumset')->column('alias');

                        // If at least one of values is not one of possible values
                        if (count($impossible = array_diff($valueA, $possible))) {

                            // Push a error to errors stack
                            $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS,
                                $fieldR->title, implode('","', $impossible)
                            );

                            // Jump to checking the next column's value
                            continue;
                        }

                    // Else if field deals with foreign keys of other tables
                    } else {

                        // If $value is not a list of non-zero decimals
                        if (!preg_match(Indi::rex('int11list'), $value)) {

                            // Push a error to errors stack
                            $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS,
                                $value, $fieldR->title);

                            // Jump to checking the next column's value
                            continue;
                        }
                    }
                }

            // If element is 'check'
            } else if ($elementR->alias == 'check') {

                // If $value is not '1' or '0'
                if (!preg_match(Indi::rex('bool'), $value)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN, $value, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

            // If element is 'color'
            } else if ($elementR->alias == 'color') {

                // If value is an empty string - skip
                if (!strlen($value)) continue;

                // If $value is not a color in format #rrggbb or in format hue#rrggbb
                if (!preg_match(Indi::rex('rgb'), $value) && !preg_match(Indi::rex('hrgb'), $value)) {

                    // If $value is not a color name
                    if (!$rgb = Indi::$colorNameA[$value]) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;

                    // Else get that color name's rgb
                    } else {
                        $value = hrgb($rgb);
                    }

                // Else if $value is a color in format #rrggbb, e.g without hue
                } else if (preg_match(Indi::rex('rgb'), $value)) {

                    // Prepend color with it's hue number
                    $value = hrgb($value);
                }

            // If element is 'calendar'
            } else if ($elementR->alias == 'calendar') {

                // If $value is not a date in format YYYY-MM-DD
                if (!preg_match(Indi::rex('date'), $value)) {

                    // Set $mismatch flag to true
                    $mismatch = true;

                    // If $value is a zero-date, e.g '0000-00-00', '00/00/0000', etc
                    if (preg_match(Indi::rex('zerodate'), $value)) {

                        // Set $mismatch flag to false and set value as '0000-00-00'
                        $mismatch = false; $value = '0000-00-00';

                    // Else if $value is a non-zero date, and field has a 'displayFormat' param
                    } else if ($fieldR->params['displayFormat']) {

                        // Try to get a unix-timestamp of a date stored in $value variable
                        $utime = strtotime($value);

                        // If date, built from $utime and formatted according to 'displayFormat' param
                        // is equal to initial value of $value variable - this will mean that date, stored
                        // in $value is a valid date, so we
                        if (date($fieldR->params['displayFormat'], $utime) == $value) {

                            // Set $mismatch flag to false
                            $mismatch = false;

                            // And setup $value as date got from $utime timestamp and formatted by 'Y-m-d' format
                            $value = date('Y-m-d', $utime);
                        }
                    }

                    // If $mismatch flag is set to true
                    if ($mismatch) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }
                }

                // If $value is not '0000-00-00'
                if ($value != '0000-00-00') {

                    // Extract year, month and day from value
                    list($year, $month, $day) = explode('-', $value);

                    // If date is not a valid date
                    if (!checkdate($month, $day, $year)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }
                }

            // If element is 'html' - no checks
            } else if ($elementR->alias == 'html') {

            // If element is 'upload' - no checks
            } else if ($elementR->alias == 'upload') {

            // If element is 'time'
            } else if ($elementR->alias == 'time') {

                // If $value is not an array, we convert it to array, containing hours, minutes and seconds
                // values under corresponding keys, by splitting $value by ':' sign
                if (!is_array($value)) {

                    // Make a copy of $value and redefine $value as array
                    $time = $value; $value = [];

                    // Extract hours, minutes and seconds
                    list($value['hours'], $value['minutes'], $value['seconds']) = explode(':', $time);
                }

                // If $value is an array - get the imploded value, assuming that array version of values contains
                // hours, minutes and seconds under corresponding keys within that array
                $time = implode(':', [
                    str_pad($value['hours'], 2, '0', STR_PAD_LEFT),
                    str_pad($value['minutes'], 2, '0', STR_PAD_LEFT),
                    str_pad($value['seconds'], 2, '0', STR_PAD_LEFT)
                ]);

                // If $value is not a time in format HH:MM:SS
                if (!preg_match(Indi::rex('time'), $time)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME, $time, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

                // If any of hours, minutes or seconds values exceeds their possible
                if ($value['hours'] > 23 || $value['minutes'] > 59 || $value['seconds'] > 59) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME, $time, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

                // Assign a value
                $value = $time;

            // If element is 'number'
            } else if ($elementR->alias == 'number') {

                // If $value is not a decimal
                if (!preg_match(Indi::rex('int11lz'), $value)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11, $value, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

            // If element is 'datetime'
            } else if ($elementR->alias == 'datetime') {

                // If $value is not an array, we convert it to array, containing date, time, year, month, day,
                // hours, minutes and seconds values under corresponding keys in $value array,
                if (!is_array($value)) {

                    // Make a copy of $value and redefine $value as array
                    $datetime = $value; $value = [];

                    list($value['date'], $value['time']) = explode(' ', $datetime);
                    list($value['year'], $value['month'], $value['day']) = explode('-', $value['date']);
                    list($value['hours'], $value['minutes'], $value['seconds']) = explode(':', $value['time']);

                // Else if $value is already an array, we assume that it already have 'date', 'hours', 'minutes'
                // and 'seconds' keys, so we only explode value under 'date' key to setup values for keys 'year',
                // 'month' and 'day' separately
                } else {

                    // Extract year, month and day from date
                    list($value['year'], $value['month'], $value['day']) = explode('-', $value['date']);
                }

                // If $value is not a date in format YYYY-MM-DD
                if (!preg_match(Indi::rex('date'), $value['date'])) {

                    // Set $mismatch flag to true
                    $mismatch = true;

                    // If $value is a zero-date, e.g '0000-00-00', '00/00/0000', etc
                    if (preg_match(Indi::rex('zerodate'), $value['date'])) {

                        // Set $mismatch flag to false and set value as '0000-00-00'
                        $mismatch = false; $value['date'] = '0000-00-00';

                        // Else if $value is a non-zero date, and field has a 'displayFormat' param
                    } else if ($fieldR->params['displayDateFormat']) {

                        // Try to get a unix-timestamp of a date stored in $value variable
                        $utime = strtotime($value['date']);

                        // If date, builded from $utime and formatted according to 'displayFormat' param
                        // is equal to initial value of $value variable - this will mean that date, stored
                        // in $value is a valid date, so we
                        if (date($fieldR->params['displayDateFormat'], $utime) == $value['date']) {

                            // Set $mismatch flag to false
                            $mismatch = false;

                            // And setup $value as date got from $utime timestamp and formatted by 'Y-m-d' format
                            $value['date'] = date('Y-m-d', $utime);

                            // Renew year, month and day values
                            list($value['year'], $value['month'], $value['day']) = explode('-', $value['date']);
                        }
                    }

                    // If $mismatch flag is set to true
                    if ($mismatch) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE, $value['date'], $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }
                }

                // If $value['date'] is not '0000-00-00'
                if ($value['date'] != '0000-00-00') {

                    // If date is not a valid date
                    if (!checkdate($value['month'], $value['day'], $value['year'])) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE,
                            $value['date'], $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }
                }

                // If $value is an array - get the imploded value, assuming that array version of values contains
                // hours, minutes and seconds under corresponding keys within that array
                $time = implode(':', [
                    str_pad($value['hours'], 2, '0', STR_PAD_LEFT),
                    str_pad($value['minutes'], 2, '0', STR_PAD_LEFT),
                    str_pad($value['seconds'], 2, '0', STR_PAD_LEFT)
                ]);

                // If $value is not a time in format HH:MM:SS
                if (!preg_match(Indi::rex('time'), $time)) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME, $time, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

                // If any of hours, minutes or seconds values exceeds their possible
                if ($value['hours'] > 23 || $value['minutes'] > 59 || $value['seconds'] > 59) {

                    // Push a error to errors stack
                    $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME, $time, $fieldR->title);

                    // Jump to checking the next column's value
                    continue;
                }

                // Assign a value
                $value = $value['date'] . ' ' . $time;

            // If element is 'hidden'
            } else if ($elementR->alias == 'hidden') {

                // Get the column type
                $columnTypeR = $fieldR->foreign('columnTypeId');

                // If column type is 'VARCHAR(255)'
                if ($columnTypeR->type == 'VARCHAR(255)') {

                    // Make the value safer
                    $value = self::safeHtml($value);

                // If column type is 'INT(11)'
                } else if ($columnTypeR->type == 'INT(11)') {

                    // If $value is not a decimal, or more than 11-digit decimal
                    if (!preg_match(Indi::rex('int11'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'DECIMAL(11,2)'
                } else if ($columnTypeR->type == 'DECIMAL(11,2)') {

                    // If $value is not a decimal, or more than 11-digit decimal
                    if (!preg_match(Indi::rex('decimal112'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'DECIMAL(14,3)'
                } else if ($columnTypeR->type == 'DECIMAL(14,3)') {

                    // If $value is not a decimal, or more than 11-digit decimal
                    if (!preg_match(Indi::rex('decimal143'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'TEXT' - no checks
                } else if ($columnTypeR->type == 'TEXT') {


                // If column type is 'DOUBLE(7,2)'
                } else if ($columnTypeR->type == 'DOUBLE(7,2)') {

                    // If $value is not a DOUBLE(7,2)
                    if (!preg_match(Indi::rex('double72'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'DATE'
                } else if ($columnTypeR->type == 'DATE') {

                    // If $value is not a date in format YYYY-MM-DD
                    if (!preg_match(Indi::rex('date'), $value)) {

                        // Set $mismatch flag to true
                        $mismatch = true;

                        // If $value is a zero-date, e.g '0000-00-00', '00/00/0000', etc
                        if (preg_match(Indi::rex('zerodate'), $value)) {

                            // Set $mismatch flag to false and set value as '0000-00-00'
                            $mismatch = false; $value = '0000-00-00';
                        }

                        // If $mismatch flag is set to true
                        if ($mismatch) {

                            // Push a error to errors stack
                            $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE, $value, $fieldR->title);

                            // Jump to checking the next column's value
                            continue;
                        }
                    }

                    // Extract year, month and day from date
                    list($year, $month, $day) = explode('-', $value);

                    // If $value is not a valid date
                    if ($value != '0000-00-00' && !checkdate($month, $day, $year)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'YEAR'
                } else if ($columnTypeR->type == 'YEAR') {

                    // If $value is not a YEAR
                    if (!preg_match(Indi::rex('year'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'TIME'
                } else if ($columnTypeR->type == 'TIME') {

                    // If $value is not an array, we convert it to array, containing hours, minutes and seconds
                    // values under corresponding keys, by splitting $value by ':' sign
                    if (!is_array($value)) {

                        // Make a copy of $value and redefine $value as array
                        $time = $value; $value = [];

                        // Extract hours, minutes and seconds
                        list($value['hours'], $value['minutes'], $value['seconds']) = explode(':', $time);
                    }

                    // If $value is an array - get the imploded value, assuming that array version of values contains
                    // hours, minutes and seconds under corresponding keys within that array
                    $time = implode(':', [
                        str_pad($value['hours'], 2, '0', STR_PAD_LEFT),
                        str_pad($value['minutes'], 2, '0', STR_PAD_LEFT),
                        str_pad($value['seconds'], 2, '0', STR_PAD_LEFT)
                    ]);

                    // If $value is not a time in format HH:MM:SS
                    if (!preg_match(Indi::rex('time'), $time)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME, $time, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                    // If any of hours, minutes or seconds values exceeds their possible
                    if ($value['hours'] > 23 || $value['minutes'] > 59 || $value['seconds'] > 59) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME, $time, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                    // Assign the value
                    $value = $time;

                // If column type is 'DATETIME'
                } else if ($columnTypeR->type == 'DATETIME') {

                    // Make a copy of $value and redefine $value as array
                    $datetime = $value; $value = [];

                    // Convert $value to array, containing date, time, year, month, day,
                    // hours, minutes and seconds values under corresponding keys in $value array,
                    list($value['date'], $value['time']) = explode(' ', $datetime);
                    list($value['year'], $value['month'], $value['day']) = explode('-', $value['date']);
                    list($value['hours'], $value['minutes'], $value['seconds']) = explode(':', $value['time']);

                    // If $value is not a date in format YYYY-MM-DD
                    if (!preg_match(Indi::rex('date'), $value['date'])) {

                        // Set $mismatch flag to true
                        $mismatch = true;

                        // If $value is a zero-date, e.g '0000-00-00', '00/00/0000', etc
                        if (preg_match(Indi::rex('zerodate'), $value['date'])) {

                            // Set $mismatch flag to false and set value as '0000-00-00'
                            $mismatch = false; $value['date'] = '0000-00-00';
                        }

                        // If $mismatch flag is set to true
                        if ($mismatch) {

                            // Push a error to errors stack
                            $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE,
                                $value['date'], $fieldR->title);

                            // Jump to checking the next column's value
                            continue;
                        }
                    }

                    // If $value['date'] is not '0000-00-00'
                    if ($value['date'] != '0000-00-00') {

                        // If date is not a valid date
                        if (!checkdate($value['month'], $value['day'], $value['year'])) {

                            // Push a error to errors stack
                            $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE,
                                $value['date'], $fieldR->title);

                            // Jump to checking the next column's value
                            continue;
                        }
                    }

                    // If $value is an array - get the imploded value, assuming that array version of values contains
                    // hours, minutes and seconds under corresponding keys within that array
                    $time = implode(':', [
                        str_pad($value['hours'], 2, '0', STR_PAD_LEFT),
                        str_pad($value['minutes'], 2, '0', STR_PAD_LEFT),
                        str_pad($value['seconds'], 2, '0', STR_PAD_LEFT)
                    ]);

                    // If $value is not a time in format HH:MM:SS
                    if (!preg_match(Indi::rex('time'), $time)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME, $time, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                    // If any of hours, minutes or seconds values exceeds their possible
                    if ($value['hours'] > 23 || $value['minutes'] > 59 || $value['seconds'] > 59) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME, $time, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                    // Assign a value
                    $value = $value['date'] . ' ' . $time;

                // If column type is 'ENUM'
                } else if ($columnTypeR->type == 'ENUM') {

                    // Get the possible field values
                    $possible = $fieldR->nested('enumset')->column('alias');

                    // If $value is not a one of possible values
                    if (!in_array($value, $possible)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'SET'
                } else if ($columnTypeR->type == 'SET') {

                    // Trim the ',' from value
                    $value = trim($value, ',');

                    // If value is not empty
                    if (strlen($value)) {

                        // Get the values array
                        $valueA = explode(',', $value);

                        // If field deals with values from 'enumset' table
                        if ($entityR->table == 'enumset') {

                            // Get the possible field values
                            $possible = $fieldR->nested('enumset')->column('alias');

                            // If at least one of values is not one of possible values
                            if (count($impossible = array_diff($valueA, $possible))) {

                                // Push a error to errors stack
                                $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS,
                                    $fieldR->title, implode('","', $impossible)
                                );

                                // Jump to checking the next column's value
                                continue;
                            }
                        }
                    }

                // If column type is 'BOOLEAN'
                } else if ($columnTypeR->type == 'BOOLEAN') {

                    // If $value is not '1' or '0'
                    if (!preg_match(Indi::rex('bool'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;
                    }

                // If column type is 'VARCHAR(10)'
                } else if ($columnTypeR->type == 'VARCHAR(10)') {

                    // If $value is not a color in format #rrggbb or in format hue#rrggbb
                    if (!preg_match(Indi::rex('rgb'), $value) && !preg_match(Indi::rex('hrgb'), $value)) {

                        // Push a error to errors stack
                        $this->_mismatch[$column] = sprintf(I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR, $value, $fieldR->title);

                        // Jump to checking the next column's value
                        continue;

                    // Else if $value is a color in format #rrggbb, e.g without hue
                    } else if (preg_match(Indi::rex('rgb'), $value)) {

                        // Prepend color with it's hue number
                        $value = hrgb($value);
                    }
                }
            }

            // Re-assign the value to column
            $this->$column = $value;
        }

        // Get tree-column name
        $tc = $this->model()->treeColumn();

        // If current model has a tree-column, and current row is an existing row and tree column value was modified
        if ($tc && $this->id && ($parentId_new = $this->_modified[$tc])) {

            // Get the tree column field row object
            $fieldR = $this->model()->fields($tc);

            // If tree-column's value it is going to be same as current row id
            if ($parentId_new == $this->id) {

                // Push a error to errors stack
                $this->_mismatch[$tc] = sprintf(I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF, $fieldR->title);

            // Else if there is actually no parent row got by such a parent id
            } else if (!$parentR = $this->foreign($tc)) {

                // Push a error to errors stack
                $this->_mismatch[$tc] = sprintf(I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404, $parentId_new, $fieldR->title);

            // Else if parent row, got by given parent id, has a non-zero parent row id (mean non-zero grandparent row id for current row)
            } else if ($parentR->$tc) {

                // Backup $parentR
                $_parentR = $parentR;

                // Here we ensure that id, that we gonna set up as parent-row id for a current row - is not equal
                // to current row id, and, even more, ensure that ids of all parent-row's ancestor rows are not
                // equal to current row id too
                do {

                    // If ancestor row id is equal to current row id
                    if ($parentR->$tc == $this->id) {

                        // Push a error to errors stack
                        $this->_mismatch[$tc] = sprintf(I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD, $_parentR->title(), $fieldR->title, $this->title());

                        // Break the loop
                        break;

                    // Else get the upper-level ancestor
                    } else $parentR = $parentR->foreign($tc);

                } while ($parentR->$tc);
            }
        }

        // If current row relates to an account-model - do additional validation
        $this->_ifRole();

        // If there is UNIQUE index defined for the current table - do additional validation
        $this->_ifUnique();

        // Check each file's type and size
        foreach ($this->_files as $field => $meta) {
            $errorA = [];

            // Here we assume that $meta is an array
            if (!is_array($meta)) continue;

            // Check type
            if ($allowTypes = $this->field($field)->param('allowTypes'))
                if ($type = $this->_fileShouldBeOfType($meta['name'], $allowTypes)) $errorA[] = $type;

            // Check max size
            if ($maxSize = $this->field($field)->param('maxSize'))
                if ($maxSize = $this->_fileShouldBeOfMaxSize($meta['size'], $maxSize)) $errorA[] = $maxSize;

            // Check min size
            if ($minSize = $this->field($field)->param('minSize'))
                if ($minSize = $this->_fileShouldBeOfMinSize($meta['size'], $minSize)) $errorA[] = $minSize;

            // Build error message
            if ($errorA) $this->_mismatch[$field] = I_FILE . ' ' . I_SHOULD . ' ' . im($errorA, ', ' . I_AND . ' ');
        }

        // If $mflush arg is `true` - flush mismatches right now
        if ($mflush) $this->mflush(false);

        // Return found mismatches
        return $this->_mismatch;
    }

    /**
     * Check if given $size is greater than $maxSize, and if so - builds an error message
     *
     * @param $size
     * @param $maxSize
     * @return string
     */
    protected function _fileShouldBeOfMaxSize($size, $maxSize) {

        // Size types
        $sizeTypeO = ['K' => 1, 'M' => 2, 'G' =>  3];

        // If no $maxSize given - return
        if (!$d = floatval($maxSize)) return;

        // Get max size type
        $sizeType = strtoupper(preg_replace('/[^KMG]/i', '', $maxSize));

        // Check size
        if ($maxSize = $d * pow(1024, $sizeTypeO[$sizeType] ?: 0)) if ($size > $maxSize)
            return I_FORM_UPLOAD_HSIZE . ' ' . I_FORM_UPLOAD_NOTGT . ' ' . strtoupper(func_get_arg(1));
    }

    /**
     * Check if given $size is less than $minSize, and if so - builds an error message
     *
     * @param $size
     * @param $minSize
     * @return string
     */
    protected function _fileShouldBeOfMinSize($size, $minSize) {

        // Size types
        $sizeTypeO = ['K' => 1, 'M' => 2, 'G' =>  3];

        // If no $minSize given - return
        if (!$d = floatval($minSize)) return;

        // Get min size type
        $sizeType = strtoupper(preg_replace('/[^KMG]/i', '', $minSize));

        // Check size
        if ($minSize = $d * pow(1024, $sizeTypeO[$sizeType] ?: 0)) if ($size < $minSize)
            return I_FORM_UPLOAD_HSIZE . ' ' . I_FORM_UPLOAD_NOTLT . ' ' . strtoupper(func_get_arg(1));
    }

    /**
     * Check if given $file is of type, or has extension within $allowTypes, and if no
     * - builds an error message
     *
     * @param $file
     * @param $allowTypes
     * @return string
     */
    protected function _fileShouldBeOfType($file, $allowTypes) {

        // Declare aux variables
        $aTypeAExt = $customExtA = $msgTypeA = []; $msg = '';

        // Predefined filetype-groups
        $typeA = [
            'image'     => ['txt' => I_FORM_UPLOAD_ASIMG, 'ext' => 'gif,png,jpeg,jpg'],
            'office'    => ['txt' => I_FORM_UPLOAD_ASOFF, 'ext' => 'doc,pdf,docx,xls,xlsx,txt,odt,ppt,pptx'],
            'draw'      => ['txt' => I_FORM_UPLOAD_ASDRW, 'ext' => 'psd,ai,cdr'],
            'archive'   => ['txt' => I_FORM_UPLOAD_ASARC, 'ext' => 'zip,rar,7z,gz,tar'],
            'audio'     => ['txt' => I_FORM_UPLOAD_ASAUD, 'ext' => 'wav,mp3,m4a,flac,wma,aac,aiff,ogg,pcm,cda,mp4,m4'],
        ];

        // Get the array of type-groups
        $aTypeA = ar($allowTypes);

        // Get the whole list of allowed extensions
        for ($i = 0; $i < count($aTypeA); $i++)
            if (!is_array($aTypeI = $typeA[$aTypeA[$i]])) $customExtA[] = $aTypeA[$i];
            else if (is_string($aTypeIExt = $aTypeI['ext'])) $aTypeAExt = array_merge($aTypeAExt, ar($aTypeIExt));

        // Setup regular expression for file extension check
        $rex = '/\.(' . im(explode(';', preg_quote(im(array_merge($aTypeAExt, $customExtA), ';'), '/')), '|') . ')$/i';

        // Check the file extension
        if (!preg_match($rex, $file)) {

            // Build array, containing parts of error message, each mentioning a certain allowed type group
            for ($i = 0; $i < count($aTypeA); $i++)
                if (is_array($dTypeI = $typeA[$aTypeA[$i]]))
                    $msgTypeA[] = $dTypeI['txt'];

            // Prepare the part of the error message, containing abstract list of allowed extenstions
            if (count($customExtA)) {
                $msg .= I_FORM_UPLOAD_OFEXT . ' ';
                if ($msgTypeA) $msg .= strtoupper(im($customExtA, ', ')) . ' ' . I_OR . ' '; else {
                    $customExtILast = array_pop($customExtA);
                    $msg .= $customExtA ? strtoupper(im($customExtA, ', ')) . ' ' . I_OR . ' ' : '';
                    $msg .= strtoupper($customExtILast);
                }
            }

            // Prepare the part of the error message, containing human-friendly file-type groups mentions
            if ($msgTypeA) {
                $msg .= I_BE . ' ';
                $msgTypeILast = array_pop($msgTypeA);
                $msg .= $msgTypeA ? im($msgTypeA, ', ') . ' ' . I_OR . ' ' : '';
                $msg .= $msgTypeILast;

                $msg .= ' ' . I_FORM_UPLOAD_INFMT . ' ';

                // Prepare the part of the error message, containing merged extension list for
                // all human-friendly file-type groups mentions
                $aTypeAExtLast = array_pop($aTypeAExt);
                $msg .= $aTypeAExt ? strtoupper(im($aTypeAExt, ', ')) . ' ' . I_OR . ' ' : '';
                $msg .= strtoupper($aTypeAExtLast);
            }

            // Return
            return $msg;
        }
    }

    /**
     * Check if current entry's model is attached to a role, and if so - check that username (`email` prop) is unique
     *
     * @return mixed
     */
    protected function _ifRole() {

        // If current model is not used within any access role - return
        if (!$this->model()->hasRole()) return;

        // If current entry already has a mismatch-message for 'email' field - return
        if ($this->_mismatch['email']) return;

        // If `email` prop became empty
        if (!$this->email) {

            // Setup mismatch message
            $this->_mismatch['email'] = sprintf(I_ADMIN_ROWSAVE_LOGIN_REQUIRED, $this->field('email')->title);

            // Return
            return;
        }

        // Get the list of entities, that should be skipped while checking username unicity
        $exclude = $this->model()->_roleFrom;

        // For each account model
        foreach (Indi_Db::role() as $entityId) {

            // Model shortcut
            $m = m($entityId);

            // Exclude some entities from username unicity check
            if ($exclude && in($m->table(), ar($exclude))) continue;

            // Try to find an account with such a username, and if found
            if ($m->row([
                '`email` = "' . $this->email . '"',
                $m->id() == $this->model()->id() ? '`id` != "' . $this->id . '"' : 'TRUE'
            ])) {

                // Setup a mismatch message
                $this->_mismatch['email'] = sprintf(
                    I_ADMIN_ROWSAVE_LOGIN_OCCUPIED, $this->email, $this->field('email')->title);

                // Stop searching
                break;
            }
        }
    }

    /**
     * Check whether UNIQUE index is set up, and if so - check uniqueness and setup mismatch message
     * if need here on PHP level, so the step at which that error would occur on MySQL level - is not reached
     */
    protected function _ifUnique() {

        // If no index of type UNIQUE defined for the current table - return
        if (!$unique = $this->model()->unique()) return;

        // If duplication detected - we attach error message at the last modified column among covered by UNIQUE index
        $msgTarget = '';

        // Foreach column covered by UNIQUE index
        foreach ($unique as $column) {

            // Prepare WHERE clause
            $where []= $this->model()->ibfk($column) && !$this->$column
                ? "`$column` IS NULL"
                : "`$column` = " . db()->quote($this->$column);

            // If this is a modified column - use it to attach error message for
            if ($this->isModified($column)) {
                $msgTarget = $column;
            }
        }

        // If no columns (covered by current UNIQUE index) are modified - return
        if (!$msgTarget) return;

        // If current entry is an existing entry - make sure it will be skipped by duplicates-search
        if ($this->id) $where []= "`id` != '$this->id'";

        // Stringify WHERE clause for searching duplicates
        $where = im($where, ' AND ');

        // If same already existing values detected - setup mismatch messages
        if ($this->model()->row($where))
            $this->_mismatch[$msgTarget] = sprintf(I_MCHECK_UNQ, $this->$msgTarget, $this->field($msgTarget)->title);
    }

    /**
     * This function sets of gets a value of $this->_temporary array by a given key (argument #1)
     * using a given value (argument # 2)

     * @return mixed
     */
    public function original() {

        // If no args given - return $this->_original;
        if (func_num_args() == 0) return $this->_original;

        // Else if single arg given
        else if (func_num_args() == 1) {

            // If it's and array
            if (is_array(func_get_arg(0))) {

                // Apply faced keys to $this->_language where possible
                foreach (func_get_arg(0) as $prop => $value)
                    if ($this->_language[$prop])
                        $this->_language[$prop][ini('lang')->admin] = $value;

                // Assign original data and return it
                return $this->_original = func_get_arg(0);

            // Else return original data stored under a given key
            } else return $this->_original[func_get_arg(0)];

        // Else two args re given
        } else {

            // Apply language version, if key is an alias of a localized fields
            if ($this->_language[func_get_arg(0)])
                $this->_language[func_get_arg(0)][ini('lang')->admin] = func_get_arg(1);

            // Assign original value for a given key and return it
            return $this->_original[func_get_arg(0)] = func_get_arg(1);
        }
    }

    /**
     * This function sets of gets a value of $this->_temporary array by a given key (argument #1)
     * using a given value (argument # 2)
     *
     * @return mixed
     */
    public function temporary() {
        if (func_num_args() == 0) return $this->_temporary;
        else if (func_num_args() == 1) return $this->_temporary[func_get_arg(0)];
        else if (func_get_arg(1) === null) unset($this->_temporary[func_get_arg(0)]);
        else return $this->_temporary[func_get_arg(0)] = func_get_arg(1);
    }

    /**
     * Forces value setting for a given key at $this->_modified array,
     * without 'same-value' check. Actually this function was created
     * to deal with cases, when we need to skip prepending a hue number
     * to #RRGGBB color, because we need to display color value without hue number in forms.
     *
     * @return mixed
     */
    public function modified() {
        if (func_num_args() == 0) return $this->_modified;
        else if (func_num_args() == 1) return is_array(func_get_arg(0)) ? $this->_modified = func_get_arg(0) : $this->_modified[func_get_arg(0)];
        else return $this->_modified[func_get_arg(0)] = func_get_arg(1);
    }

    /**
     * This function sets of gets a value of $this->_system array by a given key (argument #1)
     * using a given value (argument # 2)
     *
     * @return mixed
     */
    public function system() {
        if (func_num_args() >= 1) $prop  = func_get_arg(0);
        if (func_num_args() == 2) $value = func_get_arg(1);


        if (func_num_args() == 0) return $this->_system;
        else if (func_num_args() == 1) return $this->_system[func_get_arg(0)];
        else {
            if (func_get_arg(1) === null) unset($this->_system[func_get_arg(0)]);
            else $this->_system[func_get_arg(0)] = func_get_arg(1);
            return $this;
        }
    }

    /**
     * This function sets of gets a value of $this->_system array by a given key (argument #1)
     * using a given value (argument # 2)
     *
     * @return mixed
     */
    public function view() {
        if (func_num_args() == 1) {
            return $this->_view[func_get_arg(0)];
        } else if (func_num_args() == 2) {
            $this->_view[func_get_arg(0)] = func_get_arg(1);
            return $this;
        } else {
            return $this->_view;
        }
    }

    /**
     * Return results of certain field value compilation
     *
     * @return mixed
     */
    public function compiled() {

        // If no arguments passed - the whole array, containing compiled values will be returned
        if (func_num_args() == 0) return $this->_compiled;

        // Else if one argument is given
        else if (func_num_args() == 1) {

            // Assume it is a alias of a field, that is having value that should be compiled
            $evalField = func_get_arg(0);

            // If there is already exist a value for that field within $this->_compiled array
            if (array_key_exists($evalField, $this->_compiled)) {

                // Return that compiled value
                return $this->_compiled[$evalField] ?: '';

            // Else if field original value is not empty, and field is within
            // list of fields that are allowed for being compiled
            } else if (strlen($this->_original[$evalField]) && $this->model()->getEvalFields($evalField)) {

                // Check if field original value contains php expressions, and if so
                if (preg_match(Indi::rex('php'), $this->_original[$evalField])) {

                    // Compile that value
                    Indi::$cmpTpl = $this->_original[$evalField]; eval(Indi::$cmpRun);

                    // Save compilation result under $evalField key within $this->_compiled array, and return that result
                    return $this->_compiled[$evalField] = Indi::cmpOut();

                // Else return already existing value
                } else return $this->_compiled[$evalField] = $this->_original[$evalField];

            // Else return empty string
            } else return '';

        // Else if two arguments passed, and second is not null - we assume they
        // are key and value, and there should be explicit setup performed, so we do it
        } else if (func_get_arg(1) !== null) return $this->_compiled[func_get_arg(0)] = func_get_arg(1);

        // Else if two args passed, but second is null
        else {

            // Unset a value in _compiled under key, specified by first arg
            unset($this->_compiled[func_get_arg(0)]);

            // Return null
            return null;
        }
    }

    /**
     * Proxy to __isset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    /**
     * Proxy to __get
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return mixed|string
     */
    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    /**
     * Proxy to __set
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    /**
     * Proxy to __unset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     */
    public function offsetUnset($offset) {
        return $this->__unset($offset);
    }

    /**
     * Do all maintenance, related to file-uploads, e.g upload/replace/delete files and make copies if need
     *
     * @param array|bool $fields
     * @return Indi_Db_Table_Row
     */
    public function files($fields = []) {

        // If $fields argument is a string - convert it to array by exploding by comma
        if (is_string($fields)) $fields = explode(',', $fields);

        // If there is no file upload fields that should be taken into attention - exit
        if (is_array($fields) && !count($fields)) return $this;

        // If value, got by $this->model()->dir() call, is not a directory name
        if ((is_array($fields) ?: $this->_files) && !Indi::rexm('dir', $dir = $this->dir())) {

            // Assume it is a error message, and put it under '#model' key within $this->_mismatch property
            $this->_mismatch['#model'] = $dir;

            // Exit
            $this->mflush(false);
        }
        
        // If $fields arguments is a boolean and is true we assume that there is already exists file-fields
        // content modification info, that was set up earlier, so now we should apply file-upload fields contents
        // modifications, according to that info
        if ($fields === true) foreach ($this->_files as $field => $meta) {

            // If $meta is an array, we assume it contains values (`name`, `tmp_name`, etc),
            // picked from $_FILES variable, especially for a certain file-upload field
            if (is_array($meta)) {

                // Get the extension of the uploaded file
                $ext = preg_replace('/.*\.([^\.]+)$/', '$1', $meta['name']);

                // Delete all of the possible files, uploaded using that field, and all their versions
                $this->deleteFiles($field);

                // Build the full filename into $dst variable
                $dst = $dir . $this->id . '_' . $field . '.' . strtolower($ext);

                // Move uploaded file to $dst destination, or copy, if move_uploaded_file() call failed
                if (!move_uploaded_file($meta['tmp_name'], $dst)) copy($meta['tmp_name'], $dst);

                // Catch the moment after file was uploaded
                $this->onUpload($field, $dst);

                // If uploaded file is an image in formats gif, jpg or png
                if (preg_match('/^gif|jpe?g|png$/i', $ext)) {

                    // Check if there should be copies created for that image
                    $resizeRs = m('Resize')->all('`fieldId` = "' . $this->model()->fields($field)->id . '"');

                    // If should - create thmem
                    foreach ($resizeRs as $resizeR) $this->resize($field, $resizeR, $dst);
                }

                // Remove meta info for current file-upload field
                unset ($this->_files[$field]);

            // If file, uploaded using $field field, should be deleted
            } else if ($meta == 'd') {

                // Get the file, and all it's versions
                $fileA = glob($dir . $this->id . '_' . $field . '[.,]*');

                // Delete them
                foreach ($fileA as $fileI) @unlink($fileI);

                // Remove meta info for current file-upload field
                unset ($this->_files[$field]);

                // Call hook
                $this->onUpload($field, false);

            // If url was detected in $_POST data under key, assotiated with file-upload field
            } else if (preg_match(Indi::rex('url'), $meta)) {

                // Load that file by a given url
                $this->wget($meta, $field);

                // Remove meta info for current file-upload field
                unset ($this->_files[$field]);
            }

        // For each file upload field alias within $fields list
        } else foreach ($fields as $field) {

            // If there was a file uploaded a moment ago, we should move it to certain place
            if (Indi::post($field) == 'm' || Indi::files($field)) {

                // Get the meta information
                $meta = Indi::files($field);

                // If meta information contains a error
                if ($meta['error']) {

                    // Setup an appropriate error message
                    if ($meta['error'] === UPLOAD_ERR_INI_SIZE) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_INI_SIZE, $field);
                    else if ($meta['error'] === UPLOAD_ERR_FORM_SIZE) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_FORM_SIZE, $field);
                    else if ($meta['error'] === UPLOAD_ERR_PARTIAL) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_PARTIAL, $field);
                    else if ($meta['error'] === UPLOAD_ERR_NO_FILE) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_NO_FILE, $field);
                    else if ($meta['error'] === UPLOAD_ERR_NO_TMP_DIR) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_NO_TMP_DIR, $field);
                    else if ($meta['error'] === UPLOAD_ERR_CANT_WRITE) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_CANT_WRITE, $field);
                    else if ($meta['error'] === UPLOAD_ERR_EXTENSION) $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_EXTENSION, $field);
                    else $this->_mismatch[$field] = sprintf(I_UPLOAD_ERR_UNKNOWN, $field);

                    // Stop current iteration and goto next, if current was not the last
                    continue;
                }

                // Assign $meta under $field key within $this->_files array
                $this->_files[$field] = $meta;

            // If file, uploaded using $field field, should be deleted
            } else if (Indi::post($field) == 'd') {

                // Assign $meta under $field key within $this->_files array
                $this->_files[$field] = Indi::post($field);

            // If url was detected in $_POST data under key, assotiated with file-upload field
            } else if (preg_match(Indi::rex('url'), Indi::post($field))) {

                // Assign $meta under $field key within $this->_files array
                $this->_files[$field] = Indi::post($field);
            }
        }
        
        // Flush existing/collected/current mismatches
        $this->mflush(false);

        // Return *_Row instance itself
        return $this;
    }

    /**
     * Create the resized copy of an image, uploaded using $field field, according to info stored in $resizeR argument
     *
     * @param string $field Alias of field, that image was uploaded using by
     * @param Resize_Row $resizeR
     * @param null $src Original image full path. If this argument is not set - it will be calculated.
     * @return mixed
     */
    public function resize($field, Resize_Row $resizeR, $src = null) {

        // If no $src argument given
        if (!$src) {

            // Get dir
            $dir = $this->dir('name');

            // If directory does not exist - return
            if (!is_dir($dir)) return;

            // Get the original uploaded file full filename
            list ($src) = glob($dir . $this->id . '_' . $field . '.*');

            // If filename was not found - return
            if (!$src) return;
        }

        // Get the extension of the original uploaded file
        $ext = preg_replace('/.*\.([^\.]+)$/', '$1', $src);

        // If original uploaded file is not an image in format gif, jpeg or png - return
        if (!preg_match('/^gif|jpe?g|png$/i', $ext)) return;

        // Get the absolute filename of image's copy
        $dst = preg_replace('~(\.' . $ext . ')$~', ',' . $resizeR->alias . '$1', $src);

        // Try to create a new Imagick object, and stop function execution, if imagick object creation failed
        try { $imagick = new Imagick($src); } catch (Exception $e) { return; }

        // If mode is 'both'
        if ($resizeR->mode == 'both') {

            // This is a specialization of the cropImage method. At a high level, this method will
            // create a thumbnail of a given image, with the thumbnail sized at ($width, $height).
            // If the thumbnail does not match the aspect ratio of the source image, this is the
            // method to use. The thumbnail will capture the entire image on the shorter edge of
            // the source image (ie, vertical size on a landscape image). Then the thumbnail will
            // be scaled down to meet your target height, while preserving the aspect ratio.
            // Extra horizontal space that does not fit within the target $width will be cropped
            // off evenly left and right. As a result, the thumbnail is usually a good representation
            // of the source image.
            $imagick->cropThumbnailImage($resizeR->width, $resizeR->height);

        // Else if mode is 'auto'
        } else if ($resizeR->mode == 'auto') {

            // Create a thumbnail with $bestfit = true as 3rd arg (see https://www.php.net/manual/en/imagick.thumbnailimage.php)
            $imagick->thumbnailImage($resizeR->width, $resizeR->height, $bestfit = true);

        // Else if mode is 'width'
        } else if ($resizeR->mode == 'width') {

            // Create a thumbnail with exact width and auto-height
            $imagick->thumbnailImage($resizeR->width, 0, false);

        // Else if mode is 'height'
        } else if ($resizeR->mode == 'height') {

            // Create a thumbnail with exact height and auto-width
            $imagick->thumbnailImage(0, $resizeR->height, false);
        }

        // Remove the canvas
        if ($ext == 'gif') $imagick->setImagePage(0, 0, 0, 0);

        // Save the copy
        $imagick->writeImage($dst);

        // Free memory
        $imagick->destroy();
    }

    /**
     * Get a remote file by it's url, and assign it to a certain $field field within current row,
     * as if it was manually uploaded by user. If any error occured - return boolean false, or boolean true otherwise
     *
     * @param $url
     * @param $field
     * @return mixed
     */
    public function wget($url, $field) {

        // If value, got by $this->model()->dir() call, is not a directory name
        if (!Indi::rexm('dir', $dir = $this->dir())) {

            // Assume it is a error message, and put it under $field key within $this->_mismatch property
            $this->_mismatch[$field] = $dir;

            // Exit
            return false;
        }

        // Delete all of the possible files, previously uploaded using that field, and all their versions
        $this->deleteFiles($field);

        // Get the extension of the uploaded file
        preg_match('/[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-]+\/[^?#]*\.([a-zA-Z0-9+]+)$/', $url, $m); $uext = $m[1];

        // Try to detect remote file props using cURL request
        $p = Indi::probe($url); $size = $p['size']; $mime = $p['mime']; $cext = $p['ext'];

        // If simple extension detection failed - use one that curl detected
        $ext = $uext ? $uext : $cext;

        // If no size, or zero-size detected
        if (!$size) {

            // Setup an error to $this->_mismatch array, under $field key
            $this->_mismatch[$field] = sprintf(I_WGET_ERR_ZEROSIZE, $field);

            // Exit
            return false;
        }

        // Check is $url's host name is same as $_SERVER['SERVER_NAME']
        $purl = parse_url($url); $isOwnUrl = $purl['host'] == $_SERVER['SERVER_NAME'] || !$purl['host'];

        // If no extension was got from the given url
        if (!$ext || ($isOwnUrl && !$uext)) {

            // If $url's hostname is same as $_SERVER['SERVER_NAME']
            if ($isOwnUrl) {

                // If hostname is not specified within $url, prepend $url with self hostname and PRE constant
                if (!$purl['host']) $url = 'http://' . $_SERVER['SERVER_NAME'] . STD . $url;

                // Get request headers, and declare $hrdS variable for collecting strigified headers list
                $hdrA = apache_request_headers(); $hdrS = '';

                // Unset headers, that may (for some unknown-by-me reasons) cause freeze execution
                unset($hdrA['Connection'], $hdrA['Content-Length'], $hdrA['Content-length'], $hdrA['Accept-Encoding']);

                // Build headers list
                foreach ($hdrA as $n => $v) $hdrS .= $n . ': ' . $v . "\r\n";

                // Prepare context options
                $opt = ['http' => ['method'=> 'GET', 'header' => $hdrS]];

                // Create context, for passing as a third argument within file_get_contents() call
                $ctx = stream_context_create($opt);

                // Write session data and suspend session, so session file, containing serialized session data
                // will be temporarily unlocked, to prevent caused-by-lockness execution freeze
                session_write_close();
            }

            // Get the contents from url, and if some error occured then
            ob_start(); $raw = file_get_contents($url, false, $ctx); if ($error = ob_get_clean()) {

                // Resume session
                if ($isOwnUrl) session_start();

                // Save that error to $this->_mismatch array, under $field key
                $this->_mismatch[$field] = $error;

                // Exit
                return false;
            }

            // Resume session
            if ($isOwnUrl) session_start();

            // Create the temporary file, and place the url contents to it
            $tmp = tempnam(sys_get_temp_dir(), "indi-wget");
            $fp = fopen($tmp, 'wb'); fwrite($fp, $raw); fclose($fp);

            // If no extension yet detected
            if (!$ext) {

                // Get the mime type
                $fi = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($fi, $tmp);
                finfo_close($fi);

                // Get the extension
                $ext = Indi::ext($mime);
            }
        }

        // Build the full filename into $dst variable
        $dst = $dir . $this->id . '_' . $field . '.' . $ext;

        // 
        if ($url) {
            
            // Prepare context options
            $opt = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];

            // Create context, for passing as a third argument within file_get_contents() call
            $ctx = stream_context_create($opt);
        }

        // Copy the remote file
        $return = copy($tmp ? $tmp : $url, $dst, $ctx);

        // Change access rights
        chmod($dst, 0666);

        // Delete the temporary file
        if ($tmp) @unlink($tmp);

        // If uploaded file is an image in formats gif, jpg or png
        if (preg_match('/^gif|jpe?g|png$/i', $ext)) {

            // Check if there should be copies created for that image
            $resizeRs = m('Resize')->all('`fieldId` = "' . $this->model()->fields($field)->id . '"');

            // If should - create thmem
            foreach ($resizeRs as $resizeR) $this->resize($field, $resizeR, $dst);
        }

        // Return boolean value
        return $return;
    }

    /**
     * Get all direct descedants (incapsulated in Indi_Db_Table_Rowset object), found in $source rowset especially for
     * current row, and attach these to $this->_nested property, under the '$this->model()->treeColumn()' key, so they
     * will be accessible by the same way as per $this->nested() ordinary usage. After that, function calls itself for
     * each item within nested items, so function is acting recursively
     *
     *
     * @param Indi_Db_Table_Rowset $source
     */
    public function nestDescedants(Indi_Db_Table_Rowset $source, $collectProp = 'id') {

        // Collect [idValue => propValue] pair for current record
        $collected[$this->id] = $this->$collectProp;

        // Get nested records
        $nested = $source->select($this->id, $this->model()->treeColumn());

        // Find and attach direct descedants of current row to $this->_nested property
        $this->nested($this->_table, $nested);

        // Do quite the same for each direct descedant
        foreach ($this->nested($this->_table) as $nestedR) {

            // Collect [idValue => propValue] for nested records
            $collected[$nestedR->id] = $nestedR->$collectProp;

            // Merge collected
            $collected += $nestedR->nestDescedants($source, $collectProp);
        }

        // Return colected pairs
        return $collected;
    }

    /**
     * Usages: 
     *   ->file($field) - get some basic info about file, uploaded within certain field
     *   ->file($field, '/path/to/new.file') - Replace current file with any another one. 
     *    Consider that path to new file should start with '/'. 
     *    If you want to pass an URL as a 2nd arg, you should use ->wget($field, $url) method instead
     *    If field's settings asume resized copies should be created - they will be created if new file is an image
     *  ->file($field, $copy) - get info about one of image's copies
     *
     * @param $field
     * @param $src
     * @param $raw
     * @return array
     */
    public function file($field, $src = false, $raw = null) {

        // If given field alias - is not an alias of one of filefields - return
        if (!$this->model()->getFileFields($field)) return;

        // If $src arg is given, and $src value starts from '/'
        if ($src && (preg_match('~^/~', $src) || func_num_args() == 3)) {

            // If value, got by $this->model()->dir() call, is not a directory name
            if (!Indi::rexm('dir', $dir = $this->model()->dir())) {

                // Assume it is a error message, and put it under $field key within $this->_mismatch property
                $this->_mismatch[$field] = $dir;

                // Exit
                return false;
            }

            // Fix for cases when $src arg was created using *_Row->src() call, with 
            // it's $dc (disable cache) arg being explicitly (or by default) set to `true`
            $src = array_shift(explode('?', $src));
            
            // If $raw arg is given, we assume that $src arg is an extension, else
            if (func_num_args() == 3) $ext = $src; else {

                // Get absolute pathto file
                $abs = DOC . STD . $src;

                // If $src is not an existing file
                if (!is_file($abs)) {

                    // Assign an error message
                    $this->_mismatch[$field] = 'Filename "' . $src . '" is not a file';
                }

                // Get extension
                $ext = pathinfo($src, PATHINFO_EXTENSION);
            }

            // Build the full filename into $dst variable
            $dst = $dir . $this->id . '_' . $field . '.' . $ext;

            // Copy the remote file or create new file with contents, specified by $raw arg
            func_num_args() == 3 ? file_put_contents($dst, $raw) : copy($abs, $dst);

            // Change access rights
            chmod($dst, 0666);

            // If uploaded file is an image in formats gif, jpg or png
            if (preg_match('/^gif|jpe?g|png$/i', $ext)) {

                // Check if there should be copies created for that image
                $resizeRs = m('Resize')->all('`fieldId` = "' . $this->model()->fields($field)->id . '"');

                // If should - create thmem
                foreach ($resizeRs as $resizeR) $this->resize($field, $resizeR, $dst);
            }
        }

        // If there is currently no file uploaded - return
        if (!($abs = $this->abs($field, preg_match('~^/~', $src) ? '' : $src))) return;

        // Get the extension
        $ext = array_pop(explode('.', $abs));

        // Setup basic file info
        $file = [
            'mtime' => filemtime($abs),
            'size' => $size = filesize($abs),
            'src' => $this->src($abs, '', false),
            'ext' => $ext,
            'mime' => $mime = Indi::mime($abs),
            'type' => array_shift(explode('/', $mime)),
            'text' => $text = strtoupper($ext) . ' » ' . size2str($size),
            'href' => $href = PRE . "/auxiliary/download/id/{$this->id}/field/{$this->model()->fields($field)->id}/",
            'link' => "<a href=\"$href\">$text</a>"
        ];

        // Get more info, using getimagesize/getflashsize functions
        if ($file['type'] == 'image') $more = getimagesize($abs);
        else if ($ext == 'swf') $more = getflashsize($abs);

        // If more info was successfully got, append some of it to main info
        if ($more) {
            $file['width'] = $more[0];
            $file['height'] = $more[1];
        }

        // Here were omit STD's one or more dir levels at the ending, in case if
        // ini('upload')->path is having one or more '../' at the beginning
        $std_ = STD;
        if (preg_match(':^(\.\./)+:', ini('upload')->path, $m)) {
            $lup = count(explode('/', rtrim($m[0], '/')));
            for ($i = 0; $i < $lup; $i++) $std_ = preg_replace(':/[a-zA-Z0-9_\-]+$:', '', $std_);
            $file['std'] = $std_;
        }

        // Return
        return (object) $file;
    }

    /**
     * Assign values to row properties in batch mode, but only for properties,
     * which names are not starting with underscope ('_') sign,
     * as this properties starting with that sign is used for internal features,
     * so they are not allowed for assign and will be ignored if faced.
     *
     * Example: $row->set(array('prop1' => 'val1', 'prop2' => 'val2'));
     * is equal to: $row->prop1 = 'val1'; $row->prop2 = 'val2';
     *
     * @param array $assign
     * @return Indi_Db_Table_Row
     */
    public function assign(array $assign) {

        // Assign props in batch mode, but ignore ones starting with underscope
        foreach ($assign as $k => $v) if (!preg_match('/^_/', trim($k))) $this->{trim($k)} = $v;

        // Return row itself
        return $this;
    }

    /**
     * Alias for assign(), if $value is not given
     *
     * @param $assign
     */
    public function set($assign, $value = null) {

        // If $value arg is given, assumes the first arg is the property name
        if (func_num_args() > 1) $assign = [$assign => $value];

        // Return
        return $this->assign($assign);
    }

    /**
     * Return Field_Row object for a given field/property within current row
     *
     * @param $alias
     * @return Field_Row|Indi_Db_Table_Rowset
     */
    public function field($alias) {
        return $this->model()->fields($alias);
    }

    /**
     * Provide the ability for changelog
     *
     * @param $original
     * @throws Exception
     */
    public function changeLog($original) {

        // Get changelog config
        $cfg = $this->model()->changeLog();

        // Get the state of modified fields, that they were in at the moment before current row was saved
        $affected = $this->_affected;

        // If no changes should be logged with no exceptions - return
        if ($cfg['toggle'] != 'all' && !$cfg['except']) return;

        // If except-fields are defined
        if ($except = ar($cfg['except'])) {

            // If toggle is 'none'
            if ($cfg['toggle'] == 'none') {

                // Make sure only except-fields are kept in $affected
                $affected = []; foreach ($except as $exceptI)
                    if (array_key_exists($exceptI, $this->_affected))
                        $affected[$exceptI] = $this->_affected[$exceptI];

            // Else make sure except-fields are removed from $affected
            } else foreach($except as $exceptI) unset($affected[$exceptI]);
        }

        // If current row was a new row, or wasn't, but had no modified properties to be logged - return
        if (!$original['id'] || !count($affected)) return;

        // Get the id of current entity/model
        $entityId = $this->model()->id();

        // Get month id
        $monthId = Month::o()->id;

        // Get the foreign key names
        $foreignA = $this->model()->fields()->select('one,many', 'storeRelationAbility')->column('alias');

        // Get the list of foreign keys, that had modified values
        $affectedForeignA = array_intersect($foreignA, array_keys($affected));

        // Setup $was object as a clone of $this object, but at a state
        // that it had before it was saved, and even before it was modified
        $was = clone $this; $was->original($original);

        // Setup foreign data for $was object
        $was->foreign(im($affectedForeignA), true);

        // Setup $now object as a clone of $this object, at it's current state
        $now = clone $this; $now->foreign(im($affectedForeignA));

        // Get the storage model
        $storageM = m('changeLog');

        // Get the rowset of modified fields
        $affectedFieldRs = m($entityId)->fields()->select(array_keys($affected), 'alias');

        // Foreach modified field within the modified fields rowset
        foreach ($affectedFieldRs as $affectedFieldR) {

            // Create the changelog entry object
            $storageR = $storageM->new();

            // Setup a link to current row
            $storageR->entityId = $entityId;
            $storageR->entryId = $this->id;

            // Setup a field, that was modified
            $storageR->fieldId = $affectedFieldR->id;

            // If modified field is a foreign key
            if (array_key_exists($affectedFieldR->alias, $was->foreign())) {

                // If modified field's foreign data was a rowset object
                if ($was->foreign($affectedFieldR->alias) instanceof Indi_Db_Table_Rowset) {

                    // Declare the array that will contain comma-imploded titles of all rows
                    // within modified field's foreign data rowset
                    $implodedWas = [];

                    // Fulfil that array
                    foreach ($was->foreign($affectedFieldR->alias) as $r) $implodedWas[] = $r->title();

                    // Convert that array to comma-separated string
                    $storageR->was = im($implodedWas);

                // Else if modified field's foreign data was a row object
                } else if ($was->foreign($affectedFieldR->alias) instanceof Indi_Db_Table_Row) {

                    // Get that row's title
                    $storageR->was = $was->foreign($affectedFieldR->alias)->title();
                }

            // Else if modified field is not a foreign key
            } else {

                // Get it's value as is
                $storageR->was = $was->{$affectedFieldR->alias};
            }

            // If modified field is a foreign key
            if (array_key_exists($affectedFieldR->alias, $now->foreign())) {

                // If modified field's foreign data was a rowset object
                if ($now->foreign($affectedFieldR->alias) instanceof Indi_Db_Table_Rowset) {

                    // Declare the array that will contain comma-imploded titles of all rows
                    // within modified field's foreign data rowset
                    $implodedNow = [];

                    // Fulfil that array
                    foreach ($now->foreign($affectedFieldR->alias) as $r) $implodedNow[] = $r->title();

                    // Convert that array to comma-separated string
                    $storageR->now = im($implodedNow);

                // Else if modified field's foreign data was a row object
                } else if ($now->foreign($affectedFieldR->alias) instanceof Indi_Db_Table_Row) {

                    // Get that row's title
                    $storageR->now = $now->foreign($affectedFieldR->alias)->title();
                }

            // Else if modified field is not a foreign key
            } else {

                // Get it's value as is
                $storageR->now = $now->{$affectedFieldR->alias};
            }

            // Setup other properties
            $storageR->datetime = date('Y-m-d H:i:s');
            $storageR->monthId = $monthId;
            $storageR->roleId = admin()->roleId ?: 0;
            $storageR->adminId = admin()->id ?: 0;
            $storageR->save();
        }
    }

    /**
     * Determine whether field's value was changed from zero-value to non-zero-value
     *
     * @param $field
     * @return bool
     */
    public function fieldIsUnzeroed($field) {
        return array_key_exists($field, $this->_modified)
            && (
                $this->_original[$field] == $this->field($field)->zeroValue()
                || (
                    in_array($field, $this->model()->getEvalFields())
                    && preg_match(Indi::rex('phpsplit'), $this->_original[$field])
                )
            ) && $this->_modified[$field] != $this->field($field)->zeroValue();
    }

    /**
     * Determine whether field's value was changed from non-zero-value to zero-value
     *
     * @param $field
     * @return bool
     */
    public function fieldIsZeroed($field) {
        return array_key_exists($field, $this->_modified)
            && $this->_original[$field] != $this->field($field)->zeroValue()
            && $this->_modified[$field] == $this->field($field)->zeroValue();
    }

    /**
     * Check whether field had zero value
     * Note: 'had' is only applicable if value was affected, so if not - current value is checked
     *
     * @param $field
     * @return bool
     */
    public function fieldWasZero($field) {

        // Get zero value
        $zeroValue = $this->field($field)->zeroValue();

        // If field was changed - get previous value, else get current value
        $value = $this->affected($field) ? $this->affected($field, true) : $this->$field;

        // Return
        return $value == $zeroValue;
    }

    /**
     * Check whether field had non-zero value
     * Note: 'had' is only applicable if value was affected, so if not - current value is checked
     *
     * @param $field
     * @return bool
     */
    public function fieldWasNonZero($field) {

        // Get zero value
        $zeroValue = $this->field($field)->zeroValue();

        // If field was changed - get previous value, else get current value
        $value = $this->affected($field) ? $this->affected($field, true) : $this->$field;

        // Return
        return $value != $zeroValue;
    }

    /**
     * Check whether field has non-zero value, but was changed to zero value
     *
     * @param $field
     * @return bool
     */
    public function fieldWasZeroed($field) {

        // Get zero value
        $zeroValue = $this->field($field)->zeroValue();

        // Return
        return $this->affected($field)
            && $zeroValue != $this->affected($field, true)
            && $zeroValue == $this->$field;
    }

    /**
     * Check whether field has zero value, but was changed to non-zero value
     *
     * @param $field
     * @return bool
     */
    public function fieldWasUnzeroed($field) {

        // Get zero value
        $zeroValue = $this->field($field)->zeroValue();

        // Return
        return $this->affected($field)
            && $zeroValue == $this->affected($field, true)
            && $zeroValue != $this->$field;
    }

    /**
     * Check whether this entry was a new entry, e.g. it had no `id`-prop before the last save() call
     */
    public function wasNew() {
        return $this->affected('id') && !$this->affected('id', true) && $this->id;
    }

    /**
     * Check whether $field was changed to given $value
     *
     * @param $field
     * @param $value
     * @return bool
     */
    public function fieldWasChangedTo($field, $value) {
        return $this->affected($field) && $this->$field == $value;
    }

    /**
     * Check whether $field was changed from one non-zero value to another non-zero value
     *
     * @param $field
     * @return bool
     */
    public function fieldWasChangedToStillNonZero($field) {

        // Get zero value
        $zeroValue = $this->field($field)->zeroValue();

        // Do check
        return $this->affected($field)
            && $zeroValue != $this->affected($field, true)
            && $zeroValue != $this->$field;
    }

    /**
     * Detect whether or not row's field currently has a zero-value
     *
     * @param $field
     * @param $version
     * @return bool
     */
    public function fieldIsZero($field, $version = null) {

        // If field is a file-upload field, we implement special logic of zeroValue detection
        if ($this->field($field)->foreign('elementId')->alias == 'upload') {

            // Pick info from $_FILES
            $this->files($field);

            // If file was uploaded, but it is a temporary - return false
            if (is_array($this->_files[$field])) return false;

            // Else if file is going to be retrieved by an url - return false
            if (Indi::rexm('url', $this->_files[$field])) return false;

            // Else if file was uploaded/retrieved earlier (existing file), and it's not going to be deleted - return false
            if ($this->abs($field) && $this->_files[$field] != 'd') return false;

            // Return true, as there is no existing file, and it's not going to be uploaded/retrieved
            // Or there is an already existing file, but it's going to be deleted
            return true;

        // Else use simple logic
        } else {
            if ($version == 'original') return $this->_original[$field] == $this->field($field)->zeroValue();
            else if ($version == 'modified') return $this->_modified[$field] == $this->field($field)->zeroValue();
            else return $this->$field == $this->field($field)->zeroValue();
        }
    }

    /**
     * Detect whether or not row's field currently has a non-zero-value
     *
     * @param $field
     * @param $version
     * @return bool
     */
    public function fieldIsNonZero($field, $version = null) {
        if ($version == 'original') return $this->_original[$field] != $this->field($field)->zeroValue();
        else if ($version == 'modified') return $this->_modified[$field] != $this->field($field)->zeroValue();
        else return $this->$field != $this->field($field)->zeroValue();
    }

    /**
     * If some of the row's prop values are CKEditor-field values, we shoudl check whether they contain '<img>'
     * and other tags having STD injections at the beginning of 'src' or other same-aim html attributes,
     * and if found - trim it, for avoid problems while possible move from STD to non-STD, or other-STD directories
     *
     * @return Indi_Db_Table_Row
     */
    public function trimSTDfromCKEvalues() {

        // Collect aliases of all CKEditor-fields
        $ckeFieldA = [];
        foreach ($this->model()->fields() as $fieldR)
            if ($fieldR->foreign('elementId')->alias == 'html')
                $ckeFieldA[] = $fieldR->alias;

        // Get the aliases of fields, that are CKEditor-fields
        $ckePropA = array_intersect(array_keys($this->_original), $ckeFieldA);

        // Left-trim the {STD} from the values of 'href' and 'src' attributes
        foreach ($ckePropA as $ckePropI)
            $this->$ckePropI = preg_replace(':(\s*(src|href)\s*=\s*[\'"])' . STD . '/:', '$1/', $this->$ckePropI);

        // Return
        return $this;
    }

    /**
     * Alias for $this->delta() method
     *
     * @param $prop
     * @return mixed
     */
    public function moDelta($prop) {
        return $this->delta($prop);
    }

    /**
     * Get the difference between modified (but not yet saved) value and original value for a given property.
     * This method is for use with only properties, that have numeric values or
     * properties, containing comma-separated list of foreign keys
     *
     * @param $prop string
     * @param $diff null|string If given, valid values are 'ins' or 'del' (mean 'inserted keys' or 'deleted keys')
     * @return mixed
     */
    public function delta($prop, $diff = null) {

        // Shortcut to if clause, detecting whether we should operate on keys rather than on number-values
        $csl = $diff && $this->field($prop)->storeRelationAbility == 'many' && in($diff, 'ins,del');

        // If at the time of this call, call of parent::save() was not yet made (e.g. $this->_modified is not yet emptied)
        if (array_key_exists($prop, $this->_modified)) {

            // If field definition assumes containing comma-separated list of keys
            if ($csl) return array_diff(
                ar($this->{$diff == 'ins' ? '_modified' : '_original'}[$prop]),
                ar($this->{$diff == 'del' ? '_modified' : '_original'}[$prop]));

            // Else if we deal with dates - return difference in seconds
            else if (Indi::rexm('datetime', $this->_original[$prop]))
                return strtotime($this->_modified[$prop]) - strtotime($this->_original[$prop]);

            // Else return result of deduction of previous value from modified value
            else return $this->_modified[$prop] - $this->_original[$prop];

        // Return empty array or 0, depend on whether field definition
        // assumes containing comma-separated list of keys
        } else return $csl ? [] : 0;
    }

    /**
     * Get the difference between modified (and already saved) value and original value for a given property
     * This method is for use with only properties, that have numeric values or
     * properties, containing comma-separated list of foreign keys
     *
     * @param $prop
     * @param $diff null|string If given, valid values are 'ins' or 'del' (mean 'inserted keys' or 'deleted keys')
     * @return mixed
     */
    public function adelta($prop, $diff = null) {

        // Shortcut to if clause, detecting whether we should operate on keys rather than on number-values
        $csl = $diff && $this->field($prop)->storeRelationAbility == 'many' && in($diff, 'ins,del');

        // Else if this call is made after parent::save(), and $prop is in the list of affected props
        if ($this->affected($prop)) {

            // If field definition assumes containing comma-separated list of keys
            if ($csl) return array_diff(
                ar($diff == 'ins' ? $this->_original[$prop] : $this->_affected[$prop]),
                ar($diff == 'del' ? $this->_original[$prop] : $this->_affected[$prop]));

            // Else return result of deduction of previous value from current value
            else return $this->_original[$prop] - $this->affected($prop, true);

        // Return empty array or 0, depend on whether field definition
        // assumes containing comma-separated list of keys
        } else return $csl ? [] : 0;
    }

    /**
     * This function assumes that $prop - is the name of the property, that contains date in a some format,
     * so function convert it into timestamp and then convert it back to date, but in a custom format, provided
     * by $format argument. Output format is 'Y-m-d' by default
     *
     * @param $prop
     * @param string $format
     * @param string $ldate
     * @return string
     */
    public function date($prop, $format = 'Y-m-d', $ldate = '') {

        // If zero-value - return empty string
        if ($this->zero($prop)) return '';

        // If $ldate arg is given
        if ($ldate) {

            // Get localized date
            $date = ldate(Indi::date2strftime($format), $this->$prop);

            // Force Russian-style month name endings
            foreach (['ь' => 'я', 'т' => 'та', 'й' => 'я'] as $s => $r) {
                $date = preg_replace('/' . $s . '\b/u', $r, $date);
                $date = preg_replace('/' . $s . '(\s)/u', $r . '$1', $date);
                $date = preg_replace('/' . $s . '$/u', $r, $date);
            }

            // Force Russian-style weekday name endings, suitable for version, spelling-compatible for question 'When?'
            if (is_string($ldate) && in('weekday', ar($ldate)))
                foreach (['а' => 'у'] as $s => $r) {
                    $date = preg_replace('/' . $s . '\b/u', $r, $date);
                    $date = preg_replace('/' . $s . '(\s)/u', $r . '$1', $date);
                    $date = preg_replace('/' . $s . '$/u', $r, $date);
                }

        // Else use ordinary approach
        } else $date = date($format, strtotime($this->$prop));

        // Return
        return $date;
    }

    /**
     * Format localized date, according to current locale, set by setlocale() call
     * The key thing is that date()-compatible format can be used, rather than strftime()-compatible format
     *
     * @param $prop
     * @param $format
     * @return string
     */
    public function ldate($prop, $format) {
        return $this->date($prop, $format, true);
    }

    /**
     * Return number-formatted value of $this->prop
     *
     * @param $prop
     * @param null|int $precision
     * @param bool $color
     * @return bool|string
     */
    public function number($prop, $precision = null, $color = false) {

        // If $prop arg is an alias of an existing field
        if ($fieldR = $this->field($prop)) {

            // If $precision arg is not given, or given incorrect
            if (func_num_args() == 1 || !Indi::rexm('int11', $precision)) {

                // If existing field's column type is DECIMAL(XXX,Y)
                if (preg_match('/^DECIMAL\([0-9]+,([0-9]+)\)$/', $fieldR->foreign('columnTypeId')->type, $mColumnType)) {

                    // Set $precision as Y
                    $precision = (int) $mColumnType[1];

                // Else set $precision as 0
                } else $precision = 0;

            // Else set $precision as 0
            } else $precision = 0;

        // Else if $prop is a temporary prop
        } else if (array_key_exists($prop, $this->_temporary)) {

            // If $precision arg is not given, or given incorrect
            if (func_num_args() == 1 || !Indi::rexm('int11', $precision)) $precision = 0;

        // Else if $prop can't be used as an identifier of any prop
        } else return false;

        // Return formatted value of $this->$prop
        $formatted = decimal($this->$prop, $precision, true);

        // If $color flag is `true`
        if ($color) {

            // Possible colors
            $colorA = [-1 => 'red', 0 => 'black', 1 => 'green'];

            // Wrap formatted number into a <SPAN> with color definition
            return '<span style="color: ' . $colorA[sign($this->$prop)] . '">' . $formatted . '</span>';

        // Else just return formatted value
        } else return $formatted;
    }

    /**
     * Calls the parent class's same function, passing same arguments.
     * This is similar to ExtJs's callParent() function, except that agruments are
     * FORCED to be passed (in extjs, if you call this.callParent() - no arguments would be passed,
     * unless you use this.callParent(arguments) expression instead)
     */
    public function callParent() {

        // Get call info from backtrace
        $call = array_pop(array_slice(debug_backtrace(), 1, 1));

        // Make the call
        return call_user_func_array([$this, get_parent_class($call['class']) . '::' .  $call['function']], func_num_args() ? func_get_args() : $call['args']);
    }
    
    /**
     * Retrieve width and height from the getimagesize/getflashsize call, for an image or swf file
     * linked to a curent row's $alias field, incapsulated within an instance of stdClass object
     *
     * @param $alias
     * @param string $copy
     * @return stdClass
     */
    public function dim($alias, $copy = '') {

        // If image file exists
        if ($abs = $this->abs($alias, $copy)) {

            // Get the native result of getimagesize/getflashsize call
            $dim = (preg_match('/\.swf$/', $abs) ? getflashsize($abs) : getimagesize($abs));
            
            // Return 
            return (object) ['width' => $dim[0], 'height' => $dim[1]];
        }
    }

    /**
     * Reset row props modifications. If row had any foreign data, that data will be re-fetched to ensure it rely
     * on original values of foreign keys rather than modified values of foreign keys
     *
     * @param bool $clone
     * @return bool|Indi_Db_Table_Row
     */
    public function reset($clone = false) {

        // Backup modifications
        $modified = $this->_modified;

        // Backup foreign data
        $foreign = $this->_foreign;

        // Get modified foreign key names
        $mfkeyA = array_intersect(array_keys($modified), array_keys($foreign));

        // Reset modifications
        $this->_modified = [];

        // Remove foreign data for modified foreign keys
        foreach ($mfkeyA as $mfkeyI) unset($this->_foreign[$mfkeyI]);

        // If $clone arg is `true`
        if ($clone) {

            // Create the clone
            $clone = clone $this;

            // Set up clone's own foreign data, but only for certain foreign keys
            if ($mfkeyA) $clone->foreign(im($mfkeyA));

            // Get modifications back
            $this->_modified = $modified;

            // Get foreign data
            $this->_foreign = $foreign;

        // Else
        } else {

            // Renew own foreign data, for it to rely on original values rather than modified values
            if ($mfkeyA) $this->foreign(im($mfkeyA));
        }

        // Return
        return $clone ? $clone : $this;
    }

    /**
     * Getter function for `_affected` prop. If $prop arg is given, then function
     * will indicate whether or not prop having $prop as it alias is in the list
     * of affected props
     *
     * @param null|string $prop
     * @param null|bool $prev
     * @return array|bool
     */
    public function affected($prop = null, $prev = false) {

        // If $prop arg is given
        if (func_num_args()) {

            // If $prop arg is an array, or contains comma, we assume that $prop arg is a list of prop names
            if (is_array($prop) || preg_match('/,/', $prop)) {

                // So we try to detect if any of props within that list was affected
                foreach (ar($prop) as $propI)
                    if (array_key_exists($propI, $this->_affected))
                        return $prev ? $this->_affected[$propI] : true;

                // If detection failed - return false
                return false;

            // Else if single prop name is given as $prop arg - detect whether or not it is in the list of affected props
            } else if (is_string($prop) && $prop) {
                return $prev ? $this->_affected[$prop] : array_key_exists($prop, $this->_affected);

            } else if ($prop === true) {
                $prev = true;
            }
        }

        // Return array of affected props
        return $prev ? $this->_affected : array_keys($this->_affected);
    }

    /**
     *
     *
     * @param $fields
     * @return mixed
     */
    public function toGridData($fields, $renderCfg = []) {

        // Render grid data
        $data = $this->model()->createRowset(['rows' => [$this]])->toGridData($fields, $renderCfg);

        // Return
        return array_shift($data);
    }

    /**
     * Assing values for props, responsible for storing info about
     * the user who initially created current entry
     *
     * @param string $prefix
     */
    public function author($prefix = 'author') {
        if (admin()) {
            $this->{$prefix . 'Type'} = admin()->model()->id();
            $this->{$prefix . 'Id'} = admin()->id;
        } else {
            $this->{$prefix . 'Type'} = Indi::me('aid');
            $this->{$prefix . 'Id'} = Indi::me('id');
        }
    }

    /**
     * Adjust given $where arg so it surely match existing value
     *
     * @param $where
     * @param $fieldR
     * @return mixed
     */
    protected function comboDataExistingValueWHERE(&$where, $fieldR, $consistence = null) {

        // If current entry is a filters shared row and no consistence is set - return
        if ($this->{$fieldR->alias} == $fieldR->zeroValue() && !$consistence) return;

        // If $where arg is an empty array - return
        if (is_array($where) && !count($where)) return;

        // If $where arg is an empty string - return
        if (is_string($where) && !strlen($where)) return;

        // Build alternative WHERE clauses,
        // that will surely provide current value presence within fetched combo data
        $or = [
            'one' => '`id` = "' . $this->{$fieldR->alias} . '"',
            'many' => '`id` IN (' . $this->{$fieldR->alias} . ')'
        ];

        // If $fieldR's `storeRelationAbility` prop's value is not one oth the keys within $or array - return
        if ((!$this->{$fieldR->alias} || !$or[$fieldR->storeRelationAbility]) && !$consistence) return;

        // Append consustence clause
        if ($consistence) {
            
            // Convert to array
            if (!is_array($where)) $where = [$where];
            
            // Append $consistence
            $where []= $consistence;
        }

        // Implode $where
        if (is_array($where)) $where = im($where, ' AND ');

        // Append alternative
        if (!$consistence)
            $where = $fieldR->mode === 'readonly'
                ? $or[$fieldR->storeRelationAbility]
                : im(['(' . $where . ')', $or[$fieldR->storeRelationAbility]], ' OR ');

        // Append alternative
        //$where = im(['(' . $where . ')', /*$consistence ? '(' . $consistence . ')' : $or[$fieldR->storeRelationAbility]*/], ' OR ');
    }

    /**
     * Append $value to the list of comma-separated values, stored as a string value in $this->$prop
     *
     * @param $prop
     * @param $value
     * @param bool $unique
     * @return mixed
     */
    public function push($prop, $value, $unique = true) {

        // Convert $value to string
        $value .= '';

        // Convert $this->$prop to string
        $this->$prop .= '';

        // If $value is not an empty string
        if (strlen($value)) {

            // If $this->$prop is currently not an empty string, append $value followed by comma
            if (strlen($this->$prop)) {

                // If $unique is `true`, make sure $this->$prop will contain only distinct values
                if (!$unique || !in($value, $this->$prop)) $this->$prop .= ',' . $value;
            }

            // Else setup $this->$prop with $value
            else $this->$prop = $value;
        }

        // Return
        return $this->$prop;
    }

    /**
     * Drop $value from the comma-separated list, stored in $this->$prop
     * NOTE: $value can also be comma-separated list too
     *
     * @param $prop
     * @param $value
     * @return mixed
     */
    public function drop($prop, $value) {

        // Convert $value to string
        $value .= '';

        // Convert $this->$prop to string
        $this->$prop .= '';

        // If $value and $this->$prop are not empty strings
        if (strlen($value) && strlen($this->$prop)) {

            // If $unique is `true`, make sure $this->$prop will contain only distinct values
            $this->$prop = im(un($this->$prop, $value));
        }

        // Return
        return $this->$prop;
    }

    /**
     * This function is for compiling prop default values within *_Row instance context
     *
     * @param string $prop
     * @param string $level DEFAULT 'model'
     */
    public function compileDefaultValue($prop, $level = 'model') {

        // If compile expr is empty - return
        if (!strlen($expr = $this->{$level == 'trail' ? '_modified' : '_original'}[$prop])) return;

        // Compile and assign
        Indi::$cmpTpl = $expr; eval(Indi::$cmpRun); $this->$prop = Indi::cmpOut();
    }

    /**
     * Detect whether or not
     * 1. Entry has at least one modified prop ($prop arg is not given)
     * 2. ANY of entry's props, mentioned in given $prop arg (the comma-separated list) was modified
     *
     * @param $propS
     * @return bool|int
     */
    public function isModified($propS = null) {

        // If $propS arg is notgiven/null/zero/false/empty - return count of modified props
        if (func_num_args() == 0 || !$propS) return count($this->_modified);

        // Detect if at least one prop in the $propS list is modified
        foreach (ar($propS) as $propI) if (array_key_exists($propI, $this->_modified)) return true;

        // Return false
        return false;
    }

    /**
     * @param $field
     * @param $nested
     * @param array $ctor
     * @return array
     */
    public function keys2nested($field, $nested, $ctor = []) {

        // If $field field's value was not modified
        if (!$this->affected($field)) return ['del' => [], 'kpt' => ar($this->$field), 'new' => []];

        // Get previous and current values of $field prop
        $was = strlen($was = $this->affected($field, true)) ? ar($was) : [];
        $now = strlen($now = $this->$field) ? ar($now) : [];

        // Get values that were kept
        $kpt = array_intersect($was, $now);

        // Compare previous and current values and get arrays of deleted and inserted keys
        $del = array_diff($was, $now);
        $new = array_diff($now, $was);

        // Get field name within nested model, responsible for logical connection between
        $connector = m($this->field($field)->relation)->table() . 'Id';

        // Remove nested rows, having keys that were removed from comma-separated list within $field-prop value
        if ($del) {

            // Remove certain nested entries from database
            $this->nested($nested)->select($del, $connector)->delete();

            // Remove certain nested entries from $this->_nested[..]
            $this->nested($nested)->exclude($del, $connector);
        }

        // Create and append nested rows, having keys
        foreach ($new as $id) {

            // Create row
            $r = m($nested)->new();

            // Setup main props
            $r->set([$this->table() . 'Id' => $this->id, $connector => $id]);

            // Setup prop, presented by $ctor arg.
            $_ = is_callable($ctor) ? $ctor($r, $id, $connector) : $ctor;

            // If callback return value is boolean false - skip
            if ($_ === false) continue;

            // Assign additional props
            if (is_array($_)) $r->set($_);

            // Save
            $r->save();
        }

        // Return info about values that were kept, deleted and added
        return ['del' => $del, 'kpt' => $kpt, 'new' => $new];
    }

    /**
     * Create a new foreign-entry and use it's id to overwrite the current value or append to the current value
     *
     * @param $prop
     * @param array $ctor
     * @return mixed
     */
    public function wand($prop, $ctor = []) {

        // If $prop is not a field - return
        if (!$field = $this->field($prop)) return;

        // If field relates to `enumset` table/entity - return
        if ($field->relation == 6) return;

        // If field can store only one foreign key's value and value is an integer - return
        if ($field->storeRelationAbility == 'one' && Indi::rexm('int11', $this->$prop)) return;

        // If field can store multiple foreign key's values and value is a comma-separated list of integer values - return
        if ($field->storeRelationAbility == 'many' && Indi::rexm('int11list', $this->$prop)) return;

        // If $prop prop does not deal with foreign-keys
        if (!$entityId = $this->field($prop)->relation) return;

        // If $ctor arg is 'check' - return true
        if ($ctor == 'check') return true;

        // Create new entry
        $entry = m($entityId)->new(array_merge([
            'title' => $this->$prop
        ], $ctor));

        // Build WHERE clause to detect whether such entry is already exist
        $where = [];
        foreach ($entry->modified() as $key => $value)
            $where[] = db()->sql('`' . $key . '` = :s', $value);

        // Check whether such entry is already exist, and if yes - use existing, or save new otherwise
        if ($already = m($entityId)->row($where)) $entry = $already; else $entry->save();

        // Assign or append new entry's id, depend of field's storeRelationAbility
        if ($field->storeRelationAbility == 'one') $this->$prop = $entry->id;
        else if ($field->storeRelationAbility == 'many') $this->push($prop, $entry->id, true);
    }

    /**
     * Check whether current value of some prop - is a zero-value, or set it to be zero
     *
     * @param $prop
     * @param null $mode
     * @return bool
     */
    public function zero($prop, $mode = null) {

        // If mode is null/false/empty/not-given, or is a string
        if (!$mode || is_string($mode))

            // Check if current value of $this->$prop is a zero-value
            return $this->fieldIsZero($prop, $mode);

        // Else
        else {

            // Set zero-value for $this->$prop
            foreach (ar($prop) as $alias) $this->$alias = $this->field($alias)->zeroValue();

            // Return *_Row instance itself
            return $this;
        }
    }

    /**
     * Build a string representation of a date and time in special format
     *
     * @param $dateField
     * @param string $timeIdField
     * @return string
     */
    public function when($dateField, $timeIdField = '') {
        if (func_num_args() == 1) return when($this->$dateField);
        return when($this->$dateField, $this->foreign($timeIdField)->title);
    }

    /**
     * Get a substring of a $prop prop's current value
     *
     * @param $prop
     * @param int $length
     * @param bool $hellip
     * @return string|bool
     */
    public function substr($prop, $length = 100, $hellip = true) {

        // Check that current value of $this->$prop is either a string or a number
        if (!is_string($this->$prop) && !is_numeric($this->$prop)) return false;

        // Return substring
        return usubstr($this->$prop, $length, $hellip);
    }

    /**
     * Build the string that will be used as entry's title,
     * involved in the process of building the filename, that downloaded file will have.
     * Here it is equal to entry's actual title, but this can be altered in child classes
     *
     * @param null $fileProp
     * @return string
     */
    public function dftitle($fileProp = null) {
        return $this->title();
    }

    /**
     * Check props against validation rules.
     * Example:
     *
     * // Data
     * $data = array('myBoolProp' => 1, 'myObjectId' => 'somevalue')
     *
     * // Validation rules
     * $ruleA = array(
     *     'countryId' => array(
     *         'req' => true,
     *         'reg' => 'int11',
     *         'key' => true
     *     ),
     *     'myBoolProp' => array(
     *         'reg' => '/^(0|1)$/'
     *     ),
     *     'myObjectId' => array(
     *         'reg' => 'int11',
     *         'key' => 'City'
     *     )
     * )
     *
     * // Do check
     * $this->mcheck($ruleA, $data);
     *
     * Currently, only 3 types of checks are supported:
     *
     * 1.'req' - mean 'required'. In the example above, 'countryId' is required. But if $data arg contains
     *           no 'countryId' key, method will try to check self same-named prop, e.g. $this->countryId
     *           So if $data['countryId'] is unset/null/false/empty/zero and $this->countryId is too - error
     *           msg will be immediately flushed
     *
     * 2.'reg' - mean 'regular expression'. Can be an alias of one of predefined regular expressions,
     *           stored in Indi::$_rex, or can be a raw regular expression
     *
     * 3.'key' - mean given value is a key of a existing entry, representing instance of certain model.
     *           If prop name is an alias of a field, then there is no need to specify model name, as field
     *           metadata will be used for detecting the model name and checking entry existence. In the above
     *           example it's about 'countryId' prop, so the value of 'key' rule is just boolean `true` instead
     *           of model name 'Country'. But, for the third prop - 'myObjectId' - we explicitly provide the model
     *           name: 'City'. This approach can be used in cases when there is some prop that is NOT a one of model's
     *           fields, but there is anyway need to check if there is an 'City' entry having `id` = 'somevalue'
     *
     * Once ANY error is detected - this method is not acting like scratchy(), so method won't go further,
     * and will flush an error message, related to the only one certain check of the only once certain prop, immediately.
     *
     * But if all is ok - method will assign the value of each prop, mentioned in rules, to self same-named props,
     * e.g. $this->$prop = $data[$prop]
     *
     * Also, once 'key' checks are passed, the picked foreign data will be assigned to $this->_foreign array
     * under $prop name, for ability to be used further. In the example above, this foreign data can be accessible by
     * $this->foreign('countryId') and $this->foreign('myObjectId'). Note that the second one will work despite
     * 'myObjectId' is not an alias of one of existing fields
     *
     * @param array $ruleA
     * @param array $data
     * @param bool $flush
     */
    public function mcheck($ruleA, $data = [], $flush = true) {

        // If $flush arg is not explicitly given, override it's default value `true` - to `false`,
        // for cases when immediate flushing is turned off for current *_Row instance
        if (func_num_args() < 3 && $this->_system['mflush'] === false) $flush = false;

        // Foreach prop having mismatch rules
        foreach ($ruleA as $props => $rule) foreach (ar($props) as $prop) {

            // If $prop exists as a key in $data arg - assign in
            if (array_key_exists($prop, (array) $data)) $this->$prop = $data[$prop];

            // If $prop is an alias of thу existing field
            if ($fieldR = $this->field($prop)) {

                // If prop is required, but has empty/null/zero value - flush error
                if ($rule['req'] && ($this->zero($prop) || !$this->$prop)) $flush
                    ? mflush($prop, sprintf(I_MCHECK_REQ, $fieldR->title))
                    : $this->_mismatch[$prop] = sprintf(I_MCHECK_REQ, $fieldR->title);

                // If prop's value should match certain regular expression, but it does not - flush error
                else if ($rule['rex'] && !$this->zero($prop) && !Indi::rexm($rule['rex'], $this->$prop)) $flush
                    ? mflush($prop, sprintf(I_MCHECK_REG, $this->$prop, $fieldR->title))
                    : $this->_mismatch[$prop] = sprintf(I_MCHECK_REG, $this->$prop, $fieldR->title);

                // If prop's value should be an identifier of an existing object, but such object not found - flush error
                else if ($rule['key'] && !$this->zero($prop) && !$this->foreign($prop)) $flush
                    ? mflush($prop, sprintf(I_MCHECK_KEY, ucfirst(m($fieldR->relation)->table()), $this->$prop))
                    : $this->_mismatch[$prop] = sprintf(I_MCHECK_KEY, ucfirst(m($fieldR->relation)->table()), $this->$prop);

                // If prop's value should be unique within the whole database table, but it's not - flush error
                else if ($rule['unq'] && !$this->zero($prop) && $this->model()->row([
                    '`' . $prop . '` = "' . $this->$prop . '"', '`id` != "' . $this->id . '"'
                    ])) $flush
                    ? mflush($prop, sprintf(I_MCHECK_UNQ, $this->$prop, $fieldR->title))
                    : $this->_mismatch[$prop] = sprintf(I_MCHECK_UNQ, $this->$prop, $fieldR->title);

                // Else if $prop is a just some prop, assigned as temporary prop
            } else {

                // If prop is required, but has empty/null/zero value - flush error
                if ($rule['req'] && !$this->$prop) jflush(false, sprintf(I_JCHECK_REQ, $prop));

                // If prop's value should match certain regular expression, but it does not - flush error
                if ($rule['rex'] && $this->$prop && !Indi::rexm($rule['rex'], $this->$prop))
                    jflush(false, sprintf(I_JCHECK_REG, $this->$prop, $prop));

                // If prop's value should be an identifier of an existing object, but such object not found - flush error
                if ($rule['key'] && $this->$prop) {
                    if ($fgn = m($rule['key'])->row($this->$prop)) $this->foreign($prop, $fgn);
                    else jflush(false, sprintf(I_JCHECK_KEY, $rule['key'], $this->$prop));
                }
                
                // If prop's value should be equal to some certain value, but it's not equal - flush error
                if (array_key_exists('eql', $rule) && $this->$prop != $rule['eql'])
                    jflush(false, sprintf(I_JCHECK_EQL, $rule['eql'], $this->$prop));                
            }
        }
    }

    /**
     * Get daily working hours to be involved in certain entry validation.
     * This function was implemented as a workaround for cases when we have
     * some events, that are not fully/partially fit within the daily working hours.
     * This may happen for example, then there was old settings for daily working hours,
     * and certian entry was in those hours, but then those hours were changed, so now
     * that entry is already NOT within new daily working hours, and this will prevent
     * any modifications, that user/system will be trying to apply, as validation will flush
     * failure. That is why this function will disable daily working hours settings for events
     * that are not match that settings
     *
     * @return array
     */
    public function daily() {

        // Get daily working hours
        $daily = $this->model()->daily();

        // If this is a not-yet-existing entry - return daily working hours, declared for the model
        if (!$this->id) return $daily;

        // Disable any daily bound in case if current event is overlapping that bound
        foreach ($daily as $type => $time)
            if ($time && $_ = Indi::schedule($this->_original['spaceSince'], $this->_original['spaceUntil']))
                if ($_ = $_->{$type == 'since' ? 'early' : 'late'}($time)->spaces())
                    if (count($_) > 1 || $_[0]->avail != 'free') $daily[$type] = false;

        // Return
        return $daily;
    }

    /**
     * Alias for mcheck, but without immediate flush
     *
     * @param $ruleA
     */
    public function vcheck($ruleA) {
        $this->mcheck($ruleA, [], false);
    }

    /**
     * Return formatted price
     *
     * @param $prop
     * @return float|string
     */
    public function price($prop) {
        return price($this->$prop, true);
    }

    /**
     * Alias for _spoofConsider(). Temporary kept for backwards compatibility
     */
    protected function _spoofSatellite($for, $cField, $cValue) {
        return $this->_spoofConsider($for, $cField, $cValue);
    }

    /**
     * Spoof consider value, before it will be involved in combo data fetch sql query.
     * No actual spoof by default, but another logic my be implemented in child classes
     *
     * @param $for
     * @param $cField
     * @param $cValue
     * @return mixed
     */
    protected function _spoofConsider($for, $cField, $cValue) {
        return $cValue;
    }

    /**
     * Prepare an array containing props and values.
     * Note: return values in the below examples are json-encoded for lines usage minimization,
     * but the actual return values won't be json-encoded
     *
     * Example calls:
     *
     * 1. Simple example
     *    $cityR->attr('id,title,countryId')
     *    will return
     *    {"id":123,"title":"London","countryId":345}
     *
     * 2. Prop (title), fetched by foreign key (countryId)
     *    $cityR->attr('id,title,countryId.title')
     *    will return
     *    {"id":123,"title":"London","countryId.title":"United Kingdom"}
     *
     * 3. Both simple props and foreign key props
     *    $cityR->attr('id,title,countryId,countryId.title')
     *    will return
     *    {"id":123,"title":"London","countryId":345,"countryId.title":"United Kingdom"}
     *
     * 4. Deeper foreign key usage
     *    $cityR->attr('id,title,countryId.continentId.title')
     *    will return
     *    {"id":123,"title":"London","countryId":345,"countryId.countinentId.title":"Europe"}
     *
     * 5. Image field
     *    $cityR->attr('id,title,image')
     *    will return
     *    {"id":123,"title":"London","image":"/data/upload/city/123_image.jpg}
     *
     * 6. Certain copy of image, foreign-entry image
     *    $cityR->attr('id,title,image.small,countryId.flagPic')
     *    will return
     *    {"id":123,"title":"London","image.small":"/data/upload/city/123_image,small.jpg","countryId.flagPic":"/data/upload/country/345_flagPic.jpg"}
     */
    public function props($list, $pref = '') {

        // Attributes array
        $dataA = [];

        // Fulfil that array
        foreach (ar($list) as $prop)

            // If $prop is NOT an multipart path - use value as is, else
            if (count($path = explode('.', $prop)) == 1) $dataA[($pref ? $pref . '.' : '') . $prop] = $this->$prop; else {

                // If first part of path is a name of a foreign-key field
                if ($this->field($path[0])->relation) {

                    // If it's a single-value foreign key field
                    if ($this->field($path[0])->storeRelationAbility == 'one') {

                        // Merge props, related to foreign-key entry into $dataA array
                        $dataA += $this->foreign($_pref = array_shift($path))
                            ? $this->foreign($_pref)->props(im($path, '.'), ($pref ? $pref . '.' : '') . $_pref)
                            : [];

                    // Else if it's a multiple-values foreign key field - skip, as no support yet implemented
                    } else continue;

                // Else if first part of path is a name of a file-upload field
                } else if ($this->field($path[0])->foreign('elementId')->alias == 'upload') {

                    //
                    $dataA[($pref ? $pref . '.' : '') . $prop] = $this->src($path[0], $path[1], false);
                }
            }

        // Return a json-string, that can be used as a value for an html-attribute
        return $dataA;
    }

    /**
     * Json-encode props, mentioned in $propS arg
     * If $attr arg is non-false, json-encoded string will processed by htmlentities() fn prior to returning
     *
     * @see props()
     * @param string|array $propS
     * @param bool $attr
     * @return string
     */
    public function json($propS, $attr = false) {

        // Get json-encoded props
        $json = json_encode($this->props($propS));

        // Return a json-string, that can be used as a value for an html-attribute
        return $attr ? htmlspecialchars($json) : $json;
    }

    /**
     * Replace '"' with '&quot;', for usage as a value of a html-attribute
     *
     * @param $prop
     * @return mixed
     */
    public function attr($prop) {
        return str_replace('"', '&quot;', $this->$prop);
    }

    /**
     * Shortcut to $this->model()->enumset($field, $option)
     *
     * @param $field
     * @param $option
     * @return Indi_Db_Table_Rowset
     */
    public function enumset($field, $option = null) {
        return $this->model()->enumset($field, $option);
    }

    /**
     * This function is called right after 'move_uploaded_file() / copy()' call within Indi_Db_Table_Row::file(true) body.
     * It can be useful in cases when we need to do something once where was a file uploaded into a field
     */
    public function onUpload($field, $dst) {

    }

    /**
     * Get space frame to be used in validation, according to duration-field, specified in space-scheme
     *
     * @return int|null|string
     */
    protected function _spaceFrame() {

        // Get space description info, and if space's scheme is 'none' - return empty array
        $space = $this->model()->space(); if ($space['scheme'] == 'none') return null;

        // For each space-field (including duration-responsible fields) - set/append data-type validation rules
        foreach (explode('-', $space['scheme']) as $coord) switch ($coord) {
            case 'dayQty':    return $this->{$space['coords'][$coord]} . 'd';
            case 'minuteQty': return $this->{$space['coords'][$coord]} . 'm';
            case 'timespan':

                // Calculate frame as the difference between span bounds, in seconds
                list($span['since'], $span['until']) = explode('-', $this->{$space['coords'][$coord]});
                return _2sec($span['until'] . ':00') - _2sec($span['since'] . ':00');
        }
    }

    /**
     * Get values (for each space-field), that are not allowed to be chosen,
     * because they will lead to overlapping events within the schedule
     *
     * @param array $data
     * @return array
     */
    public function spaceDisabledValues($data = []) {

        // Setup $strict flag indicating whether or not 'req'-rule
        // should be added for each space-coord field's validation rules array.
        $strict = !(is_array($data) || $data instanceof ArrayObject); if ($strict) $data = [];

        // Get space-coord fields with their validation rules
        $spaceCoords = $this->model()->spaceCoords(true, $strict);

        // Get space-owners fields  with their validation rules
        $spaceOwners = $this->model()->spaceOwners(true);

        // Get consider-fields (e.g. fields that space-owner fields rely on) with their validation rules
        $ownerRelyOn = $this->model()->spaceOwnersRelyOn(true);

        // Setup validation rules for $data['since'] and $data['until']
        $schedBounds = $strict ? [] : ['since,until' => ['rex' => 'date']];

        // Validate all involved fields
        $this->mcheck($spaceFields = $spaceCoords + $schedBounds + $spaceOwners + $ownerRelyOn, $data);

        // If $strict flag is `true`, it means that current spaceDisabledValues()
        // call was made from validate() call, and in that case we won't validate
        // the schedule if space fields were not modified, and current entry is an existing entry
        if ($this->id && $strict && !$this->isModified(array_keys($spaceFields))) return [];

        // If $data arg contains 'purpose' param - pass into current entry's system data
        // For now, there is only one situation where it's useful: for cases when event
        // is being dragged within calendar UI, so user is planning to shift event to
        // another date, so we need to detect dates, that are disabled, and highlight
        // such dates within calendar UI, to prevent user from dropping event at disabled date
        // So, in this cases, $data['purpose'] == 'drag', and it is used within $schedule->distinct()
        // call, to prevent processing space owners, that are not same as current entry's space owners
        if ($data['purpose']) $this->_system['purpose'] = $data['purpose'];

        // If $data arg contains 'uixtype' param - pass into current entry's system data
        // It will be used to calc disabled values for other weekdays' dates within week,
        // that current event's date is in
        if ($data['uixtype']) $this->_system['uixtype'] = $data['uixtype'];

        // If $data arg contains 'kanban' param - it means that uixtype is 'dayview'
        // and kanban param contains kanban columns info (possible values of kanban-prop)
        if ($data['kanban']) $this->_system['kanban'] = $data['kanban'];

        // If $data arg contains 'fromHour' param - it means that uixtype is 'dayview' or 'weekiew'
        // So we need to know what time those view-types start with
        if ($data['fromHour']) $this->_system['fromHour'] = $data['fromHour'];

        // If system 'fromHour' param is set - set system 'bounds' param to 'day'
        if ($this->_system['fromHour']) $this->_system['bounds'] = 'day';

        // Create schedule
        $schedule = !$strict && array_key_exists('since', $this->_temporary)
            ? Indi::schedule($this->since, strtotime($this->until . ' +1 day'))
            : Indi::schedule($this->_system['bounds'] ?: 'month', $this->zero('date') ? null : $this->date);

        // Expand schedule's right bound
        $schedule->frame($frame = $this->_spaceFrame());

        // Preload existing events, but do not fill schedule with them
        $schedule->preload($this->_table, array_merge(
            ['`id` != "' . $this->id . '"'],
            $this->spacePreloadWHERE()
        ));

        // Collect distinct values for each prop
        $schedule->distinct($spaceOwners, $this, $strict);

        // Get daily working hours
        $daily = $this->daily(); $disabled = ['date' => [], 'timeId' => []];

        // Get time in 'H:i' format
        $time = $this->foreign('timeId')->title;

        // Setup 'early' and 'late' spaces and backup
        $schedule->daily($daily['since'], $daily['until'])->backup();

        // Collect dates
        $schedule->dates();

        // Foreach prop, representing event-participant
        foreach ($spaceOwners as $prop => $ruleA) {

            // Declare $prop-key within $disabled array, and initially set it to be an empty array
            $disabled[$prop] = $busy['date'] = $busy['time'] = $psblA = [];

            // Get distinct values of current $prop from schedule's rowset,
            // as they are values that do have a probability to be disabled
            // So, for each $prop's distinct value
            foreach ($schedule->distinct($prop) as $id => $info) {

                // Reset $both array
                $both = [];

                // Refill schedule
                $schedule->refill($info['idxA'], null, null, $ruleA['pre']);

                // Prepare $hours arg for busyDates call
                if (!$ruleA['hours']) $hours = false; else $hours = [
                    'idsFn' => $ruleA['hours']['time'],
                    'only' => $ruleA['hours']['only'],
                    'owner' => $info['entry'],
                    'event' => $this
                ];

                // Setup daily working hours per each date separately
                if ($hours && !$hours['only']) $schedule->ownerDaily($hours);

                // Collect info about disabled values per each busy date
                // So for each busy date we will have the exact reasons of why it is busy
                // Also, fulfil $both array with partially busy dates
                foreach ($dates = $schedule->busyDates($frame, $both, $hours['only'] ? $hours : false) as $date)
                    $busy['date'][$date][] = $id;

                // Get given date's busy hours for current prop's value
                // If current space-owner has no custom logic of per-day availability hours - use dates,
                // containing in $both array for walking through and detect busy hours, else - use dates,
                // containing in $schedule->dates array
                if ($dates = $hours ? $schedule->dates : $both)
                    foreach ($dates as $date)
                        foreach ($schedule->busyHours($date, '15m', true, $hours['only'] ? $hours : false) as $Hi)
                            $busy['time'][$date][$Hi][] = $id;
            }

            // If we have values, fully busy at at least one day
            if ($busy['date']) {

                // Get array of possible values
                $psblA = $this->getComboData($prop)->column('id');

                // For each date, that busy for some values,
                foreach ($busy['date'] as $date => $busyA) {

                    // Reset $d flag to `false`
                    $d = false;

                    // If there are no possible values remaining after
                    // deduction of busy values - set $d flag to `true`
                    if (!array_diff($psblA, $busyA)) $d = true;

                    // Else if current value of $prop is given, but it's
                    // in the list of busy values - also set $d flag to `true`
                    else if (preg_match('~,(' . im($busyA, '|') . '),~', ',' . $this->$prop . ',')) $d = true;

                    // If $d flag is `true` - append disabled date
                    if ($d) $disabled['date'][$date] = true;

                    // If iterated date is same as current date - append disabled value for $prop prop
                    if ($date == $this->date) $disabled[$prop] = $busyA;
                }
            }

            // If we have values, fully busy at at least one day
            if ($busy['time']) {

                // Get array of possible values, keeping in mind
                // that some values might have already been excluded by date
                if (!$psblA) $psblA = $this->getComboData($prop)->column('id');

                // For each date, that busy for some values,
                foreach ($busy['time'] as $date => $HiA) {

                    // If there are non-empty array of disabled values for entry's time
                    if ($busyA = $HiA[$time]) {

                        // Reset $d flag to `false`
                        $d = false;

                        // If there are no possible values remaining after
                        // deduction of busy values - set $d flag to `true`
                        if (!array_diff($psblA, array_merge($busy['date'][$date] ?: [], $busyA))) {
                            if ($psblA) $d = true;
                        }

                        // Else if current value of $prop is given, but it's
                        // in the list of busy values - also set $d flag to `true`
                        else if (preg_match('~,(' . im($busyA, '|') . '),~', ',' . $this->$prop . ',')) $d = true;

                        // If $d flag is `true` - append disabled date
                        if ($d) $disabled['date'][$date] = true;

                        // If iterated date is same as current date - append disabled value for $prop prop
                        if ($date == $this->date) $disabled[$prop] = array_merge($disabled[$prop] ?: [], $busyA);
                    }

                    // If iterated date is same as current date - append disabled value for `timeId` prop
                    if ($date == $this->date) foreach ($HiA as $Hi => $busyA) {

                        // Reset $d flag to `false`
                        $d = false;

                        // If there are no possible values remaining after
                        // deduction of busy values - set $d flag to `true`
                        if (!array_diff($psblA, array_merge($busy['date'][$date] ?: [], $busyA))) {
                            if ($psblA) $d = true;
                        }

                        // Else if current value of $prop is given, but it's
                        // in the list of busy values - also set $d flag to `true`
                        else if (preg_match('~,(' . im($busyA, '|') . '),~', ',' . $this->$prop . ',')) $d = true;

                        // If $d flag is `true` - append disabled `timeId`
                        if ($d && $timeId = timeId($Hi)) $disabled['timeId'][$timeId] = true;
                    }
                }
            }

            // If current space field is auto-field -  setup list of possible values
            if ($ruleA['auto']) $this->_system['possible'][$prop] = array_diff($psblA, $disabled[$prop]);
        }

        // Append disabled timeIds, for time that is before opening and after closing
        if ($daily['since'] || $daily['until']) foreach (timeId() as $Hi => $timeId) {
            $His = $Hi . ':00';
            if ($daily['since'] && $His <  $daily['since']) $disabled['timeId'][$timeId] = true;
            if ($daily['until'] && $His >= $daily['until']) $disabled['timeId'][$timeId] = true;
        }

        // Use keys as values for date and timeId
        foreach ($disabled as $prop => $data) { // $data variable overridden here

            // Get actual dates
            if ($prop == 'date') $disabled[$prop] = array_keys($data);

            // If prop is 'timeId'
            if ($prop == 'timeId') {

                // Get timeHi
                $disabled['timeHi'] = [];
                foreach ($disabled[$prop] = array_keys($data) as $timeId)
                    $disabled['timeHi'][] = timeHi($timeId);

                // Sort
                sort($disabled['timeHi']);
            }
        }

        // Declare busy chunks array
        $chunkA = [];

        // Foreach disabled time
        foreach ($disabled['timeHi'] as $idx => $Hi) {

            // Append busy chunk
            $chunkA []= [strtotime($this->date . ' ' . $Hi . ':00'), 15];

            // If it was first timeHi item - skip
            if (!$idx) continue;

            // Prev and next busy chunks shortcuts
            $prev = &$chunkA[count($chunkA) - 2];
            $curr = &$chunkA[count($chunkA) - 1];

            // If current busy chunk is start at the exact same timestamp where previous busy chunk ends
            if ($curr[0] <= $prev[0] + $prev[1] * 60) {

                // Extend previous chunk's duration
                $prev[1] += 15;

                // Instead of adding new chunk
                array_pop($chunkA);
            }
        }

        // Get unixtime of a time, that day/week view starts with
        $fromHour = strtotime($this->date) + $this->_system['fromHour'] * 3600;

        // Foreach chunk
        foreach ($chunkA as $idx => &$curr) {

            // If it's not first chunk - setup prev busy chunks shortcut
            if ($idx) $prev = &$chunkA[$idx - 1];

            // If busy chunk's time match 'fromHour' param - set no diff, else
            if ($curr[0] == $fromHour) $diff = 0; else {

                // Get right bound of free chunk's, located before curr busy chunk
                $free = $this->duration * 60 + ($idx ? $prev[0] + $prev[1] * 60 : $curr[0] - 15 * 60);

                // Get diff
                $diff = $free > $curr[0] ? $free - $curr[0] : ($this->duration - 15) * 60;
            }

            // Shift current busy chunk's left bound
            $curr[0] += $diff;

            // Decrease current busy chunk's duration
            $curr[1] -= $diff / 60;
        }

        // Get kanban value
        $kanbanKey = $this->_system['kanban'] ? $this->_system['kanban']['prop'] : 'date';
        $kanbanVal = $this->$kanbanKey;

        // Pass busy chunks, grouped by kanban values - to the return value
        $disabled['busy'][$kanbanVal]['chunks'] = $chunkA ?: [];
        $disabled['busy'][$kanbanVal]['timeHi'] = array_flip($disabled['timeHi']) ?: [];

        // Adjust disabled values
        $this->adjustSpaceDisabledValues($disabled, $schedule);

        // If we're trying to drag event within week-calendar
        if ($this->_system['uixtype'] == 'weekview') {

            // Backup date and
            $date = $this->date;

            // Unset system uixtype param, to prevent unneeded recursion
            unset($this->_system['uixtype']);

            // Get since/until for given week
            $bounds = $schedule->bounds('week', $date); $wd = $bounds['since'];

            // Foreach date within week, that dragged event's date is in
            while ($wd < $bounds['until']) {

                // If weekday's date is same as dragged event's date - skip
                if ($date != $wd) {

                    // Spoof date
                    $data['date'] = $wd;

                    // Get disabled values for weekday's date
                    $_ = $this->spaceDisabledValues($data);

                    // Append into $disabled['busy']
                    $disabled['busy'][$wd] = $_['busy'][$wd];
                }

                // Jump to next
                $wd = date('Y-m-d', strtotime($wd . ' +1 day'));
            }

            // Restore date and system uixtype param
            $this->date = $date; $this->_system['uixtype'] = 'weekview';

        // Else if uixtype is 'dayview' and kanban cfg is set
        } else if ($this->_system['uixtype'] == 'dayview' && ($k = $this->_system['kanban'])) {

            // Backup kanban-prop's value and
            $kanban = $this->{$k['prop']};

            // Unset system uixtype param, to prevent unneeded recursion
            unset($this->_system['uixtype']);

            // Foreach kanban column
            foreach ($k['values'] as $value) {

                // If kanban value is same as dragged event's kanban-prop's value - skip
                if ($kanban != $value) {

                    // Spoof kanban-prop's value
                    $data[$k['prop']] = $value;

                    // Get disabled values for kanban value
                    $_ = $this->spaceDisabledValues($data);

                    // Append into $disabled['busy']
                    $disabled['busy'][$value] = $_['busy'][$value];
                }
            }

            // Restore kanban-prop's value and system uixtype param
            $this->{$k['prop']} = $kanban; $this->_system['uixtype'] = 'dayview';
        }

        // Return info about disabled values
        return $disabled;
    }

    /**
     * Empty function, to be overridden in child classes in cases when there is a need to, for example,
     * do not preload cancelled events into schedule, to prevent their presence them from being
     * the reason of why some new/existing event can't be scheduled/re-scheduled at some date/time.
     *
     * Example of return value: array('`status` != "canceled"');
     *
     * @return null
     */
    public function spacePreloadWHERE() {
        return [];
    }

    /**
     * Empty function. TO be redefined in child classes in cases when some some
     * new value was assigned and it require custom validation to be performed again
     *
     * @param $field
     */
    public function spaceValidateAutoField($field) {

    }

    /**
     * Check whether there are some space-fields having values that are in the list of disables values,
     * and if found - try to find non-disabled values and assign found or build mismatch messages, if
     * initially/newly assigned values are empty or did not met the requirements of custom validation
     *
     * @param $auto
     */
    public function spaceMismatches($auto) {

        // Check space-fields, and collect problem-values
        foreach ($this->spaceDisabledValues(false) as $prop => $disabledA)
            foreach ($disabledA as $disabledI)
                if (in($disabledI, $this->$prop))
                    $this->_mismatch[$prop][] = $disabledI;

        // Foreach space auto-field
        foreach ($auto as $prop) {

            // Get field shortcut
            $field = $this->field($prop);

            // While there are possible values remaining
            // Note: $this->_system['possible'][$prop] is being set up within
            //       $this->spaceDisabledValues() for field having ['auto' => true] rule
            while ($this->_system['possible'][$prop]) {

                // If it currently is having zero-value, or non-zero, but it's in the list of disabled values
                if ($this->zero($prop) || $this->_mismatch[$prop]) {

                    // Pick one of possible values, or zero-value if there are no remaining possible values
                    // Note: this logic is not ok for multi-value fields
                    $this->$prop = array_shift($this->_system['possible'][$prop]) ?: $field->zeroValue();

                    // Unset prop's mismatch, as here at this stage mismatch can only be in case if
                    // current value was in the list of disabled values, but now we assigned a value
                    // got from $this->_system['possible'][$prop], so now value is surely not in the
                    // list of disabled values, so we need to unset the mismatch
                    unset($this->_mismatch[$prop]);

                    // Validate newly-assigned value
                    if ($this->$prop) $this->spaceValidateAutoField($prop);
                    else $this->_mismatch[$prop] = sprintf(I_MCHECK_REQ, $field->title);
                }

                // If still no mismatch - break
                if (!$this->_mismatch[$prop]) break;
            }
        }

        // Walk through fields and their problem-values
        foreach ($this->_mismatch as $prop => $disabledValueA) if (is_array($disabledValueA)) {

            // Get field shortcut
            $field = $this->field($prop);

            // Get title of value, that caused the problem
            if ($field->storeRelationAbility == 'none') $value = $disabledValueA[0];
            else if ($field->storeRelationAbility == 'one') $value = $this->foreign($prop)->title;
            else if ($field->storeRelationAbility == 'many') $value =
                $this->foreign($prop)->select($disabledValueA)->column('title', ', ');

            // Setup mismatch message
            $this->_mismatch[$prop] = sprintf(I_COMBO_MISMATCH_DISABLED_VALUE, $value, $field->title);
        }
    }

    /**
     * Get parent entry
     *
     * @return Indi_Db_Table_Row|Indi_Db_Table_Rowset|null
     * @throws Exception
     */
    public function parent() {

        // If current entity has no tree-column - return
        if (!$tc = $this->model()->treeColumn()) return;

        // Return parent entry
        return $this->$tc ? $this->foreign($tc) : null;
    }

    /**
     * @param string $level DEFAULT 'model'
     * @return mixed
     * @throws Exception
     */
    public function compileDefaults($level = 'model') {

        // If it's an existing entry - return
        if ($this->id) return;

        // Foreach field
        foreach ($this->model()->fields() as $fieldR) {

            // If field does not have underlying db table column - return
            if (!$fieldR->columnTypeId) continue;

            // If default value should be set up as a result of php-expression's execution - do it
            if (preg_match(Indi::rex('php'), $fieldR->{$level == 'trail' ? 'modified' : 'original'}('defaultValue')))
                $this->compileDefaultValue($fieldR->alias, $level);

            // Else if underlying column's datatype is TEXT - set up default value, stored in Indi
            // Engine field's settings as MySQL does not suppoer native default values for TEXT column
            else if ($fieldR->foreign('columnTypeId')->type == 'TEXT')
                $this->compileDefaultValue($fieldR->alias, $level);
        }
    }

    /**
     * Adjust space disabled values. To be overridden in child classed
     *
     * @param array $disabled
     * @param Indi_Schedule $schedule
     */
    public function adjustSpaceDisabledValues(array &$disabled, Indi_Schedule $schedule) {

    }

    /**
     * Build config for combo
     *
     * todo: refactor
     *
     * @param int|string $field
     * @param bool $store Get store only instead of full config
     * @return array
     */
    public function combo($field, $store = false) {

        // If $field arg is an array - assume pseudo-field should be created
        if (is_array($field)) {

            // Create
            $fieldR = m('Field')->new();

            // Assign config
            $fieldR->set($field);

            // Setup `entityId`, if not given within config
            if (!$fieldR->entityId) $fieldR->entityId = $this->model()->id;

        // Else get existing field
        } else $fieldR = $this->field($field);

        // Get name
        $name = $field = $fieldR->alias;

        // Get params
        $params = $fieldR->params;

        // Get default value
        $defaultValue = $this->compileDefaultValue($field);

        // If current row is an existing row, or current value is not
        // empty - use current value as selected value, else use default value
        $selectedValue = $this->id || strlen($this->$field) ? $this->$field : $defaultValue;

        // Get initial combo options rowset
        $comboDataRs = $this->getComboData($name, null, $selectedValue, null, null, $fieldR);

        // Prepare combo options data
        $comboDataA = $comboDataRs->toComboData($params, $fieldR->param('ignoreTemplate'));


        $options = $comboDataA['options'];
        $keyProperty = $comboDataA['keyProperty'];

        // If combo is boolean
        if ($fieldR->storeRelationAbility == 'none' && $fieldR->columnTypeId == 12) {

            // Setup a key
            if ($this->$name) {
                $key = $this->$name;
            } else if ($comboDataRs->enumset) {
                $key = key($options);
            } else {
                $key = $defaultValue;
            }

            // Setup an info about selected value
            if (strlen($key)) {
                $selected = [
                    'title' => $options[$key]['title'],
                    'value' => $key
                ];
            } else {
                $selected = [
                    'title' => null,
                    'value' => null
                ];
            }

        // Else if current field column type is ENUM or SET, and current row have no selected value, we use first
        // option to get default info about what title should be displayed in input keyword field and what value
        // should have hidden field
        } else if ($fieldR->storeRelationAbility == 'one') {

            // Setup a key
            if (($this->id && !$comboDataRs->enumset) || !is_null($this->$name)) {
                $key = $this->$name;
            } else if ($comboDataRs->enumset) {
                $key = key($options);
            } else {
                $key = $defaultValue;
            }

            // Setup an info about selected value
            $selected = [
                'title' => $options[$key]['title'],
                'value' => $key
            ];

            // Add box color
            if ($options[$key]['system']['boxColor']) $selected['boxColor'] = $options[$key]['system']['boxColor'];

            // Setup css color property for input, if original title of selected value contained a color definition
            if ($options[$selected['value']]['system']['color'])
                $selected['style'] =  ' style="color: ' . $options[$selected['value']]['system']['color'] . ';"';

            // Set up html attributes for hidden input, if optionAttrs param was used
            if ($options[$selected['value']]['attrs']) {
                $attrs = [];
                foreach ($options[$selected['value']]['attrs'] as $k => $v) {
                    $attrs[] = $k . '="' . $v . '"';
                }
                $attrs = ' ' . implode(' ', $attrs);
            }

        // Else if combo is multiple
        } else if ($fieldR->storeRelationAbility == 'many') {

            // Set value for hidden input
            $selected = ['value' => $selectedValue];

            // Set up html attributes for hidden input, if optionAttrs param was used
            $exploded = explode(',', $selected['value']);
            $attrs = [];
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
        $options = [
            'ids' => array_keys($options),
            'data' => array_values($options),
            'found' => $comboDataRs->found(),
            'page' => $comboDataRs->page(),
            'enumset' => $comboDataRs->enumset
        ];

        // Setup tree flag in entity has a tree structure
        if ($comboDataRs->table() && $comboDataRs->model()->treeColumn()) $options['tree'] = true;

        // Setup groups for options
        if ($comboDataRs->optgroup) $options['optgroup'] = $comboDataRs->optgroup;

        // Setup option height. Current context does not have a $this->ignoreTemplate member,but inherited class *_FilterCombo
        // does, so option height that is applied to form combo will not be applied to filter combo, unless $this->ignoreTemplate
        // in *_FilterCombo is set to false
        $options['optionHeight'] = $params['optionHeight'] && !$fieldR->param('ignoreTemplate') ? $params['optionHeight'] : 14;

        // Setup groups for options
        if ($comboDataRs->optionAttrs) $options['attrs'] = $comboDataRs->optionAttrs;

        // If store arg is given - return only store data
        if ($store) return $options;

        // Prepare view params
        $view = [
            'subTplData' => [
                'attrs' => $attrs,
                'pageUpDisabled' => $this->$name ? 'false' : 'true',
            ],
            'store' => $options
        ];

        // Setup view data,related to currenty selected value(s)
        if ($fieldR->storeRelationAbility == 'many') {
            $view['subTplData']['selected'] = $selected;
            foreach($comboDataRs->selected as $selectedR) {
                $item = Indi_View_Helper_Admin_FormCombo::detectColor([
                    'title' => ($_tc = $fieldR->param('titleColumn'))
                        ? $selectedR->$_tc
                        : $selectedR->title(),
                    'boxColor'  => $selectedR->boxColor,
                    'boxIcon'   => $selectedR->boxIcon,
                    'textColor' => $selectedR->textColor,
                ]);
                $item['id'] = $selectedR->$keyProperty;
                $view['subTplData']['selected']['items'][] = $item;
            }
        } else {
            $view['subTplData']['selected'] = Indi_View_Helper_Admin_FormCombo::detectColor($selected);
        }

        // Build full config
        $view = [
            'xtype' =>'formcombo',
            'fieldLabel' => $fieldR->title,
            'name' => $fieldR->alias,
            'value' => $selectedValue,
            'field' => [
                'id' => $fieldR->id,
                'storeRelationAbility' => $fieldR->storeRelationAbility,
                'alias' => $fieldR->alias,
                'relation' => $fieldR->relation,
                'params' => $params,
                '_nested' => [
                    'consider' => $fieldR->nested('consider')->toArray(true)
                ]
            ],
            'allowBlank' => $fieldR->mode == 'regular',
            ] + $view + [
                'fetchUrl' => str_replace('/save/', '/form/', explode('?', URI)[0])
            ];

        // Return it
        return $view;
    }

    /**
     * Build extjs config object for {'xtype': 'radios'}, based on given field
     *
     * @param $field
     * @param bool $store
     * @return array
     */
    public function radio($field, $store = false) {
        return ['xtype' => 'radios', 'cls' => 'i-field-radio'] + $this->combo($field, $store);
    }

    /**
     * Build extjs config object for {'xtype': 'radios'}, based on given field
     *
     * @param $field
     * @param bool $store
     * @return array
     */
    public function multicheck($field, $store = false) {
        return ['xtype' => 'multicheck', 'cls' => 'i-field-multicheck'] + $this->combo($field, $store);
    }

    /**
     * Get nesting-level, if applicable
     *
     * @return int
     */
    public function level() {
        $level = 0; $up = $this;

        // Get level
        while ($up = $up->parent()) $level ++;

        // Assign into system prop
        $this->system('level', $level);

        // Return
        return $this->system('level');
    }

    /**
     * Get all translations for all props, or all translations for certain prop, or certain translation for certain prop
     *
     * @param $prop
     * @param $lang
     * @param $value
     * @return array|mixed
     */
    public function language($prop = null, $lang = null, $value = null) {

        // If no args given - return $this->_language
        if (func_num_args() == 0) return $this->_language;

        // If only $prop arg given - return existing translations for certain prop
        if (func_num_args() == 1) return $this->_language[$prop];

        // If $prop and $lang args given - return certain translation for certain prop
        if (func_num_args() == 2) {

            // If 2nd arg is an array - assume it's a [lang => value] pairs
            if (is_array($lang)) {

                // So set it directly
                $this->_language[$prop] = $lang;

                // Return
                return $this;

            // Else get value of a given $prop for a given $lang
            } else return $this->_language[$prop][$lang];
        }

        // If $prop, $lang and $value args given
        if (func_num_args() == 3) {

            // Set certain translation for certain prop
            $this->_language[$prop][$lang] = $value;

            // Return
            return $this;
        }
    }

    /**
     * Build dirname where current entry's files should be stored
     *
     * @param string $mode
     * @return string
     */
    public function dir($mode = '') {
        return $this->model()->dir($mode, false, $this->filesGroup());
    }

    /**
     * Return I_PRIVATE_DATA instead of actual field value if demo-mode is currently On and field has shade param
     *
     * @param $prop
     * @return string
     */
    public function shade($field) {
        return Indi::demo(false) && $this->field($field)->param('shade') && $this->$field ? I_PRIVATE_DATA : $this->$field;
    }

    /**
     * Build and return path to the php-template, used to build the html-file,
     * that is autoattached to a fileupload field. So $field arg should be an alias
     * of a fileupload-field
     *
     * @param $field
     * @param bool $abs
     * @param string $lang
     * @return string
     */
    public function tpldoc($field, $abs = false, $lang = null) {
        return $this->model()->tpldoc($field, $abs, $lang);
    }

    /**
     * Helper function, used for system purposes, should not be called directly
     *
     * @param $after
     * @param $among
     * @param $currentIdx
     * @param $within
     * @return $this|string
     */
    protected function _position($after, $among, $currentIdx, $within = '') {

        // If $after arg is null or not given
        if ($after === null) {

            // If position of current entry is non-zero, e.g. is not first
            // return alias of entry, that current entry is positioned after,
            // else return empty string, indicating that current entry is on top
            return $currentIdx ? $among[$currentIdx - 1] : '';

        // Else do positioning
        } else {

            // If current entry should moved to top
            if ($after === '') {

                // If current entry is already on top - return
                if (!$currentIdx) return $this;

                // Else set direction to 'up', and qty of $this->move() calls
                $direction = 'up'; $count = $currentIdx;

            // Else
            } else {

                // Get required position of current entry
                $mustbeIdx = array_flip($among)[$after] + 1;

                // If it's already at required position - do nothing
                if ($mustbeIdx == $currentIdx) return $this;

                // Set direction
                $direction = $mustbeIdx > $currentIdx ? 'down' : 'up';

                // Set count of $this->move() calls
                $count = abs($currentIdx - $mustbeIdx);

                // If $durection is  'down' - decrement $count
                if ($direction == 'down') $count --;
            }

            // Do positioning
            for ($i = 0; $i < $count; $i++) $this->move($direction, $within, $i === $count - 1);

            // Return this
            return $this;
        }
    }

    /**
     * Increment/decrement all qties and increase/decrease all sums that current entry is counted in
     * todo: fix casdade double-decrement/decrease problem
     *
     * @param string $event
     * @throws Exception
     */
    protected function _inQtySum($event = 'insert') {

        // If we don't need to count/summarize this entry anywhere - return
        if (!$inQtySum = $this->model()->inQtySum()) return;

        // Foreach location that we should count/summarize that in
        foreach ($inQtySum as $info) {

            // Shortcuts
            $type  = $info['type'];
            $fgn   = $info['sourceTarget'];
            $trg   = $info['targetField'];
            $src   = $info['sourceField'];
            $where = $info['sourceWherePHP'] ?: $info['sourceWhereSQL'];
            $isPHPWhere = $info['sourceWherePHP'] !== false;
            $method = 'save';

            // If new entry was inserted
            if ($event == 'insert') {

                // If current target-record ID is non-zero
                if ($this->$fgn) {

                    // If current target-record is externally fetched from db and fed via
                    // $this->_system["inQtySumTarget-$fgn"] or can it be still fetched right here
                    if ($foreign = $this->_system["inQtySumTarget-$fgn"]
                        ?? $this->foreign($fgn, true, 'original')) {

                        // If source-record match WHERE clause
                        if ($match['now'] = $this->match($where, 'original', $isPHPWhere)) {

                            // Increment/increase value of target-field on current target-record
                            $foreign->$trg += $type == 'qty' ? 1 : $this->original($src);

                            // Apply changes to current target-record
                            $foreign->$method();
                        }
                    }
                }

            // Else if existing entry was deleted
            } else if ($event == 'delete') {

                // If previous target-record ID was non-zero
                if ($this->$fgn) {

                    // If previous target-record is externally fetched from db and fed via
                    // $this->_system["inQtySumTarget-$fgn"] or can it be still fetched right here
                    if ($foreign = $this->_system["inQtySumTarget-$fgn"]
                        ?? $this->foreign($fgn, true, 'original')) {

                        // If source-record match WHERE clause
                        if ($match['was'] = $this->match($where, 'original', $isPHPWhere)) {

                            // Decrement/decrease value of target-field on previous target-record
                            $foreign->$trg -= $type == 'qty' ? 1 : $this->original($src);

                            // Apply changes to previous target-record
                            $foreign->$method();
                        }
                    }
                }

            // Else if existing entry was updated
            } else if ($event == 'update') {

                // If target-record ID was changed
                if ($this->affected($fgn)) {

                    // If previous target-record ID was non-zero
                    if ($this->affected($fgn, true)) {

                        // If previous target-record still exists in DB
                        if ($foreign = $this->foreign($fgn, true, 'affected')) {

                            // If source-record match WHERE clause
                            if ($match['was'] = $this->match($where, 'affected', $isPHPWhere)) {

                                // Decrement/decrease value of target-field on previous target-record
                                $foreign->$trg -= $type == 'qty' ? 1 : $this->aoo($src);

                                // Apply changes to previous target-record
                                $foreign->$method();
                            }
                        }
                    }

                    // If current target-record ID is non-zero
                    if ($this->$fgn) {

                        // If current target-record is externally fetched from db and fed via
                        // $this->_system["inQtySumTarget-$fgn"] or can it be still fetched right here
                        if ($foreign = $this->_system["inQtySumTarget-$fgn"]
                            ?? $this->foreign($fgn, true, 'original')) {

                            // If source-record match WHERE clause
                            if ($match['now'] = $this->match($where, 'original', $isPHPWhere)) {

                                // Increment/increase value of target-field on current target-record
                                $foreign->$trg += $type == 'qty' ? 1 : $this->original($src);

                                // Apply changes to current target-record
                                $foreign->$method();
                            }
                        }
                    }

                // Else if target-record ID was NOT changed
                } else {

                    // If target-record ID is non-zero
                    if ($this->$fgn) {

                        // If target-record is externally fetched from db and fed via
                        // $this->_system["inQtySumTarget-$fgn"] or can it be still fetched right here
                        if ($foreign = $foreign = $this->_system["inQtySumTarget-$fgn"]
                            ?? $this->foreign($fgn)) {

                            // Get flags indicating whether source-record match WHERE clause before and after change
                            $match['was'] = $this->match($where, 'affected', $isPHPWhere);
                            $match['now'] = $this->match($where, 'original', $isPHPWhere);

                            // If source-record was and is still matching WHERE clause
                            if ($match['was'] && $match['now']) {

                                // Increment/increase value of target-field on target-record
                                $foreign->$trg += $type == 'qty' ? 0 : $this->adelta($src);

                            // Else if source-record does not match WHERE clause anymore
                            } else if ($match['was'] && !$match['now']) {

                                // Decrement/decrease value of target-field on target-record
                                $foreign->$trg -= $type == 'qty' ? 1 : $this->aoo($src);

                            // Else if source-record did not match WHERE clause but not it does
                            } else if (!$match['was'] && $match['now']) {

                                // Increment/increase value of target-field on target-record
                                $foreign->$trg += $type == 'qty' ? 1 : $this->$src;
                            }

                            // Apply changes to current target-record
                            if ($foreign->isModified($trg)) $foreign->$method();
                        }
                    }
                }
            }
        }
    }

    /**
     * Check whether certain version of current entry does match given SQL WHERE clause or php-expression
     *
     * @param $where
     * @param $version
     * @param bool $isPHPWhere
     * @return mixed
     */
    public function match($where, $version, $isPHPWhere = false) {

        // If where clause is empty - return true
        if (!$where) return true;

        // If $where arg contains a php-expression
        if ($isPHPWhere) {

            // Get plain entry instance
            $phantom = $this->phantom($version,'php');

            // Eval the php-expression and assign the result into $match variable
            eval("\$match = ($where);");

            // Return boolean flag
            return $match;

        // Else if it's an SQL-expression
        } else {

            // Get sql expression to be used as derived table
            $phantom = $this->phantom($version,'sql');

            // Return
            return db()->query("SELECT `id` FROM ($phantom) AS `phantom` WHERE $where")->cell();
        }
    }

    /**
     * Trim hue from the beginning of color-field value, which itinially is in format 'hue#rrggbb',
     * where hue is a number below 360
     *
     * @param $field
     * @return string|string[]|null
     */
    public function rgb($field) {
        return preg_replace('~^[0-9]{3}~', '', $this->$field);
    }

    /**
     * Pick $certain props from $ctor
     *
     * @param $certain
     * @param array $ctor
     */
    protected function _certain($certain, array $ctor) {

        // If $certain arg is empty - return
        if (!$certain) return $ctor;

        // Spoof ctor
        $spoof = [];

        // Pick certain props
        foreach(ar($certain) as $prop) $spoof[$prop] = $ctor[$prop];

        // Return spoof ctor, containing only certain fields
        return $spoof;
    }

    /**
     * 'aoc' - means 'affected or current'
     *
     * If $prop was affected by the last insert/update - get affected (e.g. previous) value , else get current value
     * If $aoc arg is given as false - current value is returned in any case
     *
     * @param $prop
     * @param bool $aoc
     * @return mixed
     */
    public function aoc($prop, $aoc = true) {
        return $aoc
            ? ($this->affected($prop)
                ? $this->affected($prop, true)
                : $this->$prop)
            : $this->$prop;
    }

    /**
     * 'aoo' - means 'affected or original'
     *
     * If $prop was affected by the last insert/update - get affected (e.g. previous) value , else get original value
     * If $aoo arg is given as false - original value is returned in any case
     *
     * @param $prop
     * @param bool $aoo
     * @return mixed
     */
    public function aoo($prop, $aoo = true) {
        return $aoo
            ? ($this->affected($prop)
                ? $this->affected($prop, true)
                : $this->original($prop))
            : $this->original($prop);
    }

    /**
     * Get previous value of a $prop
     *
     * @param $prop
     * @return array|bool
     */
    public function was($prop) {
        return $this->affected($prop, true);
    }

    /**
     * Increment prop
     *
     * @param $prop
     * @return $this
     */
    public function inc($prop) {
        return $this->set($prop, $this->$prop + 1);
    }

    /**
     * Decrement prop
     *
     * @param $prop
     * @return $this
     */
    public function dec($prop) {
        return $this->set($prop, $this->$prop - 1);
    }

    /**
     * Prepare new entry based on current on, except that 'id' and 'move' props are unset
     * Note: it's now saved into db yet
     *
     * @return Indi_Db_Table_Row
     */
    public function new($ctor = null) {

        // Use original data as ctor
        if (!$ctor) $ctor = $this->_original;

        // Unset 'id' and 'move'
        unset ($ctor['id'], $ctor['move']);

        // Use others to init new entry
        return $this->model()->new($ctor);
    }
}