<?php
class Indi_Db {

    /**
     * Singleton instance
     *
     * @var Indi_Db
     */
    protected static $_instance = null;

    /**
     * PDO object
     *
     * @var PDO
     */
    protected static $_pdo = null;

    /**
     * Array of loaded models, with model class names as keys
     *
     * @var array
     */
    protected static $_modelA = [];

    /**
     * Array of existing entities, with capitalized entity table names as keys
     *
     * @var array
     */
    protected static $_entityA = [];

    /**
     * Array of table names of existing entities, which have `alternate` flag turned on
     *
     * @var array
     */
    protected static $_roleA = [];

    /**
     * Localized fields, grouped by table names
     *
     * @var array
     */
    protected static $_l10nA = [];

    /**
     * Array of *_Row instances preloaded by m('ModelName')->preload(true) call
     * Those instances are grouped by entity table name and are used as a return value for
     * $row->foreign('foreignKeyName'), $rowset->foreign('foreignKeyName') and $schedule->distinct() calls
     *
     * @var array
     */
    protected static $_preloadedA = [];

    /**
     * Arrays of config values. Default - are `field`.`alias` => `field`.`defaultFalue` pairs,
     * grouped by entity's table name and entity entry id, e.g.
     *     'default' => [
     *         'element' => [
     *             23 => [                            // 23 is the ID of `element` entry having `alias` = 'combo'
     *                 'optionHeight' => 14,              // e.g 14px by default
     *                 'groupBy' => '',                   // No options grouping by default
     *                 ...
     *             ]
     *         ]
     *     ]
     * Certain - are `param`.`cfgField`->`field`.`alias` => `param`.`cfgValue` pairs
     *     'certain' => [
     *         'field' => [
     *             19 => [                           // 19 is the ID of `field` entry having `alias` = 'entityId' and `elementId` = "23"
     *                 'groupBy' => 'fraction'           // entity titles will be grouped by `entity`.`fraction`
     *             ]
     *         ]
     *     ]
     *
     * So, default values are values specified by config fields definitions,
     * and certain values are values that are explicitly defined, so they have the priority
     * @var array
     */
    public static $_cfgValue = [
        'default' => [],
        'certain' => []
    ];

    /**
     * Store queries count
     *
     * @var Indi_Db
     */
    public static $queryCount = 0;

    /**
     * @var array
     */
    public static $DELETEQueryA = [];

    /**
     * Flag
     *
     * @var bool
     */
    protected static $_transactionLevel = 0;

    /**
     * Initial database setup, if $config argument is provided, or just return the singleton instance otherwise
     *
     * @static
     * @param array $arg
     * @return null|Indi_Db
     */
    public static function factory($arg = [])
    {
        // Setup $refresh flag, indicating that the purpose of why we're here is to globally refresh entities meta
        $refresh = $arg === true;

        // If singleton instance is not yet created or 'reload' key exists within $config array argument
        if (null === self::$_instance || is_int($arg) || $refresh) {

            // If we're in refresh-mode, we don't need to re-instantiate self::$_instance and self::$_pdo
            if (!$refresh) {

                // If singleton instance is not yet created
                if (null === self::$_instance) {

                    // Create it
                    self::$_instance = new self();

                    // Maximum attempts qty
                    // --
                    // Basically we need this dirty hack for case when Indi Engine is running inside a docker container,
                    // and docker-entrypoint.sh has commands that require mysql to be completely initialized, so we wait
                    $tryQty = 5;

                    // Do attempts
                    for ($i = 1; $i <= $tryQty; $i++) try {

                        // Setup a PDO object
                        self::$_pdo = new PDO($arg->adapter . ':dbname=' . $arg->name . ';host=' . $arg->host, $arg->user, $arg->pass);

                        // Set attribute for useing custom statement class
                        self::$_pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [Indi_Db_PDOStatement::class]);

                        // Stop attempts if reached this line
                        break;

                        // If something goes wrong
                    } catch (PDOException $e) {

                        // If max attempt qty is not yet reached - wait a bit
                        if ($i < $tryQty) sleep(1);

                        // Else pass caught exception to the own handler
                        else self::$_instance->jerror($e);
                    }

                    // Setup encoding
                    self::$_instance->query('SET NAMES utf8');
                    self::$_instance->query('SET CHARACTER SET utf8');

                // Else if singleton instance was already created, but $arg agument is an entity id - setup $entityId variable
                } else if (is_int($arg)) {

                    $entityId = $arg;
                }

            // Else
            } else {

                // Reset static props storing entities meta,
                // except self::$_roleA and Lang::$_jtpl which will be reset further
                self::$_modelA     = [];
                self::$_entityA    = [];
                self::$_l10nA      = [];
                self::$_preloadedA = [];
                self::$_cfgValue   = ['default' => [], 'certain' => []];
            }

            // Prepare ibfk data to be attached to model data
            foreach (self::$_instance->query("
                SELECT `c`.`TABLE_NAME`, SUBSTRING_INDEX(`c`.`CONSTRAINT_NAME`, 'ibfk_', -1) AS `field`, c.*
                FROM `information_schema`.`REFERENTIAL_CONSTRAINTS` `c`
                WHERE `CONSTRAINT_SCHEMA` = DATABASE()
            ")->fetchAll(PDO::FETCH_GROUP) as $table => $fields)
                $ibfk[$table] = array_column($fields, 'DELETE_RULE', 'field');

            // Get info about existing entities, or one certain entity, identified by id,
            // passed within value of 'model' key of $config argument
            $entityA = self::$_instance->query(
                'SELECT * FROM `entity`' . ($entityId ? ' WHERE `id` = "' . $entityId . '"' : '')
            )->fetchAll();

            // If we're not reloading some certain entity
            if (!$entityId) {

                // Get db table containing roles. This is temporary solution to handle
                // Indi Engine instances where `profile`-table is not yet renamed to `role`
                $roleTable = array_column($entityA, 'table','table')['profile'] ?? 'role';

                // Get ids of entities, linked to access roles
                self::$_roleA = self::$_instance->query('
                    SELECT
                      GROUP_CONCAT(`id`) AS `roleIds`,
                      IF(`entityId`,`entityId`,11) AS `entityId`
                    FROM `' . $roleTable . '`
                    GROUP BY `entityId`
                ')->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            // Fix tablename case, if need
            if (!$entityId && !preg_match('/^WIN/i', PHP_OS) && self::$_instance->query('SHOW TABLES LIKE "columntype"')->cell()) {
            
                // Build an sql-query, that will construct sql-queries that will 
                // fix tablename confusion for each database table affected
                $needQ = 'SELECT CONCAT("RENAME TABLE `", LOWER(`table`), "` TO `", `table`, "`") '
                        .'FROM `entity` WHERE LOWER(`table`) COLLATE utf8_bin != `table` COLLATE utf8_bin';
                
                // Get RENAME queries
                $renameQA = self::$_instance->query($needQ)->fetchAll(PDO::FETCH_COLUMN);
                
                // Execute RENAME queries
                foreach ($renameQA as $renameQI) self::$_instance->query($renameQI);
            }

            // Get info about fields, existing within all entities, or one certain entity
            $fieldA = self::$_instance->query(
                'SELECT * FROM `field`'  .
                ($entityId ? ' WHERE `entityId` = "' . $entityId . '" ' : '') .
                'ORDER BY `move`'
            )->fetchAll();

            // Get temporary table names array
            foreach($entityA as $entityI) $_[$entityI['id']] = $entityI['table'];

            // Make fields ids to be used as the keys
            $fieldA = array_combine(array_column($fieldA, 'id'), $fieldA);

            // Walk through fields, and
            foreach ($fieldA as $fieldI) {

                // Collect config-fields alias=>defaultValue pairs, grouped by entity and entry
                if ($fieldI['entry'])
                    self::$_cfgValue['default']
                        [$_[$fieldI['entityId']]]
                            [$fieldI['entry']]
                                [$fieldI['alias']] = $fieldI['defaultValue'];

                // Collect localized fields
                if ($fieldI['storeRelationAbility'] == 'none' && in($fieldI['l10n'], 'y,qn'))
                    self::$_l10nA[$_[$fieldI['entityId']]][$fieldI['id']] = $fieldI['alias'];

                // Collect reference fields
                if ($fieldI['relation'] && $_[$fieldI['relation']] != 'enumset' && !$fieldI['entry'] && $fieldI['onDelete'] != '-')
                    $refs[ $_[$fieldI['relation']] ][ $fieldI['onDelete'] ][ $fieldI['id'] ] = [
                        'table' => $_[$fieldI['entityId']],
                        'column' => $fieldI['alias'],
                        'multi' => $fieldI['storeRelationAbility'] == 'many'
                    ];
            }

            // Unset tmp variable
            unset($_);

            // Overwrite ini('lang')->admin for it to be same as $_COOKIE['i-language'], if possible
            // We do it here because this should be done BEFORE any *_Row (and *_Noeval) instance creation
            if (($lang = $_COOKIE['i-language']) && ini('lang')->admin != $lang
                && in($lang, db()->query('SELECT `alias` FROM `lang` WHERE `toggle` = "y"')->fetchAll(PDO::FETCH_COLUMN)))
                ini('lang')->admin = $_COOKIE['i-language'];

            // Setup json-templates for each possible fractions
            if (db()->query('
                SELECT COUNT(`column_name`) 
                FROM `INFORMATION_SCHEMA`.`COLUMNS` 
                WHERE 1
                  AND `table_schema` = DATABASE() 
                  AND `table_name` = "lang" 
                  AND `column_name` IN ("adminSystemUi", "move")
            ')->cell() == 2)
                foreach (['adminSystemUi', 'adminCustomUi', 'adminCustomData', 'adminSystemUi,adminCustomUi'] as $fraction)
                    Lang::$_jtpl[$fraction] = db()->query('
                    SELECT `alias`, "" AS `holder` 
                    FROM `lang` 
                    WHERE "y" IN (`' . im(ar($fraction), '`,`') . '`)
                    ORDER BY `move`
                ')->fetchAll(PDO::FETCH_KEY_PAIR);

            // Get info about existing control elements
            $elementA = self::$_instance->query('SELECT * FROM `element`')->fetchAll();
            $iElementA = []; foreach ($elementA as $elementI)
                $iElementA[$elementI['id']] = new Indi_Db_Table_Row_Noeval([
                    'table' => 'element',
                    'original' => $elementI
                ]);
            unset($elementA);

            // Get info about existing column types
            $columnTypeA = self::$_instance->query('SELECT * FROM `columnType`')->fetchAll();
            $iColumnTypeA = []; foreach ($columnTypeA as $columnTypeI)
                $iColumnTypeA[$columnTypeI['id']] = new ColumnType_Row([
                    'table' => 'columnType',
                    'original' => $columnTypeI
                ]);
            unset($columnTypeA);

            // If certain model should be reloaded, collect ids of it's fields, for use them as a part of WHERE clause
            // in fetch from `enumset`
            if ($entityId) {

                // Declare array for collecting fields ids
                $fieldIdA = [];

                // Fulfil that array
                foreach ($fieldA as $fieldI) $fieldIdA[] = $fieldI['id'];
            }

            // Get info about existing enumset values
            $enumsetA = self::$_instance->query(
                'SELECT * FROM `enumset`' . (is_array($fieldIdA) ? ' WHERE FIND_IN_SET(`fieldId`, "' .
                implode(',', $fieldIdA) . '") ' : '') . 'ORDER BY `move`'
            )->fetchAll();

            // Group them by `fieldId`
            $fEnumsetA = []; foreach ($enumsetA as $enumsetI)
                $fEnumsetA[$enumsetI['fieldId']][] = new Enumset_Row([
                    'table' => 'enumset',
                    'original' => $enumsetI
                ]);
            unset($enumsetA);

            // Get info about existing consider-fields
            $considerA = self::$_instance->query(
                'SELECT * FROM `consider`' . (is_array($fieldIdA) ? ' WHERE FIND_IN_SET(`fieldId`, "' .
                implode(',', $fieldIdA) . '") ' : '')
            )->fetchAll();

            // Group them by `fieldId`
            $fConsiderA = []; foreach ($considerA as $considerI)
                $fConsiderA[$considerI['fieldId']][] = new Indi_Db_Table_Row_Noeval([
                    'table' => 'consider',
                    'original' => $considerI
                ]);
            unset($considerA);

            // Temporary flag indicating whether or not we have already removed legacy cfgFields implementation
            if ($pep = self::$_instance->query('SHOW TABLES LIKE "possibleElementParam"')->cell()) {

                // Get info about existing field params
                // 1. Get info about possible field element params
                $possibleElementParamA = self::$_instance->query('SELECT * FROM `possibleElementParam`')->fetchAll();

                // 2. Declare two arrays, where:
                //   a. possible params as array of arrays, each having params aliases as keys, and devault values as
                //      values, grouped by elementId
                //   b. possible params as array, having params ids as keys, and aliases as values
                // - respectively
                $ePossibleElementParamA = []; $possibleElementParamAliasA = [];

                // 3. Fulfil these two arrays
                foreach (l10n($possibleElementParamA, 'defaultValue') as $possibleElementParamI) {
                    $ePossibleElementParamA[$possibleElementParamI['elementId']]
                    [$possibleElementParamI['alias']] = $possibleElementParamI['defaultValue'];
                    $possibleElementParamAliasA[$possibleElementParamI['id']] = $possibleElementParamI['alias'];
                }
                unset($possibleElementParamA);
            }

            // 4. Get info about explicit set (e.g. non-default) config-fields' values
            $paramA = self::$_instance->query('SELECT * FROM `param`' . (is_array($fieldIdA)
                ? ' WHERE FIND_IN_SET(`fieldId`, "' . implode(',', $fieldIdA) . '") ' : ''))->fetchAll();
            $fParamA = []; foreach (l10n($paramA, 'value') as $paramI) {
                if ($pep) $fParamA[$paramI['fieldId']][$possibleElementParamAliasA[$paramI['possibleParamId']]] = $paramI['value'];
                if (array_key_exists('cfgField', $paramI)) {
                    if ($fieldA[$paramI['cfgField']]['relation'] == 5)  $paramI['cfgValue'] = $paramI['cfgValue']
                        ? im(array_column(array_intersect_key($fieldA, array_flip(explode(',', $paramI['cfgValue']))), 'alias'))
                        : '';
                    self::$_cfgValue['certain']['field'][$paramI['fieldId']][$fieldA[$paramI['cfgField']]['alias']]
                        = preg_match('~^{"[a-zA-Z]{2,5}":~', $paramI['cfgValue'])
                            ? json_decode($paramI['cfgValue'])->{ini('lang')->admin}
                            : $paramI['cfgValue'];
                }
            }
            unset($paramA);

            // Group fields by their entity ids, and append system info
            $eFieldA = [];
            foreach ($fieldA as $fieldI) {

                // Setup original data
                $fieldI = ['original' => $fieldI];

                // Setup foreign data for 'elementId' foreign key
                $fieldI['foreign']['elementId'] = $iElementA[$fieldI['original']['elementId']];

                // Setup foreign data for 'columnTypeId' foreign key, if field has a non-zero columnTypeId
                if ($iColumnTypeA[$fieldI['original']['columnTypeId']])
                    $fieldI['foreign']['columnTypeId'] = $iColumnTypeA[$fieldI['original']['columnTypeId']];

                // Setup nested rowset with 'enumset' rows, if field contains foreign keys from 'enumset' table
                if ($fieldI['original']['relation'] == '6') {
                    $fieldI['nested']['enumset'] = new Indi_Db_Table_Rowset([
                        'table' => 'enumset',
                        'rows' => $fEnumsetA[$fieldI['original']['id']],
                        'rowClass' => 'Enumset_Row',
                        'found' => count($fEnumsetA[$fieldI['original']['id']] ?: [])
                    ]);
                    unset($fEnumsetA[$fieldI['id']]);
                }

                // Setup nested rowset with 'consider' rows, if there are consider-fields defined for current field
                if ($fConsiderA[$fieldI['original']['id']]) {
                    $fieldI['nested']['consider'] = new Indi_Db_Table_Rowset([
                        'table' => 'consider',
                        'rows' => $fConsiderA[$fieldI['original']['id']],
                        'rowClass' => 'Indi_Db_Table_Row_Noeval',
                        'found' => count($fConsiderA[$fieldI['original']['id']])
                    ]);
                    unset($fConsiderA[$fieldI['id']]);
                }

                // Shortcuts
                $elementId = $fieldI['original']['elementId']; $fieldId = $fieldI['original']['id'];

                // Setup params, as array, containing default values, and actual values arrays merged to single array
                if ($ePossibleElementParamA[$elementId]
                    || $fParamA[$fieldId]
                    || self::$_cfgValue['default']['element'][$elementId]
                    || self::$_cfgValue['certain']['field'][$fieldId]) {

                    if ($pep) $fieldI['temporary']['params'] = array_merge(
                        $ePossibleElementParamA[$elementId] ?: [],
                        $fParamA[$fieldId] ?: []
                    );

                    // For now, config-fields is a new untested update, so it will should be turnable on/off
                    if (ini('db')->cfgField || !$pep)
                    $fieldI['temporary']['params'] = array_merge(
                        self::$_cfgValue['default']['element'][$elementId] ?: [],
                        self::$_cfgValue['certain']['field'][$fieldId] ?: []
                    );
                }

                // Append current field data to $eFieldA array
                if (!$fieldI['original']['entry']) {
                    $eFieldA[$fieldI['original']['entityId']]['rows'][] = new Field_Row($fieldI);
                    $eFieldA[$fieldI['original']['entityId']]['aliases'][$fieldI['original']['id']] = $fieldI['original']['alias'];
                    $eFieldA[$fieldI['original']['entityId']]['ids'][$fieldI['original']['id']] = $fieldI['original']['id'];
                }
            }

            // Release memory
            unset($fieldA, $iElementA, $iColumnTypeA, $fEnumsetA, $ePossibleElementParamA, $fParamA);

            // If we are here for model reload - drop all metadata for that model
            if ($entityId) {

                // Try to find the model class name, as the class name is the key
                foreach (self::$_entityA as $className => $entityI)
                    if ($entityI['id'] == $entityId) {
                        $class = $className;
                        break;
                    }

                // If $entityId was found, so it mean that we are reloading existing model
                // Unset metadata storage under that key from self::$_entityA and self::$_modelA
                if ($class) unset(self::$_entityA[$class], self::$_modelA[$class]);
            }

            // Array for collecting "entityId => modelName" pairs
            $modelNameA = [];

            // Foreach existing entity
            foreach ($entityA as $entityI) {

                // Collect "entityId => modelName" pairs
                $modelNameA[$entityI['id']] = ucfirst($entityI['table']);

                // Prepare comma-separated list of aliases of fields, specified in $entityI['changeLogExcept']
                $changeLogExcept = [];
                $_ids = array_values($eFieldA[$entityI['id']]['ids'] ?: []);
                $_aliases = array_values($eFieldA[$entityI['id']]['aliases'] ?: []);
                $_map = array_combine($_ids, $_aliases);
                foreach (ar($entityI['changeLogExcept']) as $exceptFieldId) $changeLogExcept []= $_map[$exceptFieldId];
                $entityI['changeLogExcept'] = im($changeLogExcept);

                // Create an item within self::$_entityA array, containing some basic info
                self::$_entityA[$modelNameA[$entityI['id']]] = [
                    'id' => $entityI['id'],
                    'title' => $entityI['title'],
                    'extends' => $entityI['extends'],
                    'titleFieldId' => $entityI['titleFieldId'],
                    'filesGroupBy' => $entityI['filesGroupBy'],
                    'hasRole' => in_array($entityI['id'], self::$_roleA),
                    'fraction' => $entityI['fraction'],
                    'changeLog' => [
                        'toggle' => $entityI['changeLogToggle'],
                        'except' => $entityI['changeLogExcept'],
                    ],
                    'ibfk' => $ibfk[$entityI['table']] ?: [],
                    'refs' => $refs[$entityI['table']] ?: [],
                    'fields' => new Field_Rowset_Base([
                        'table' => 'field',
                        'rows' => $eFieldA[$entityI['id']]['rows'],
                        'aliases' => $_aliases,
                        'ids' => $_ids,
                        'rowClass' => 'Field_Row',
                        'found' => count($eFieldA[$entityI['id']]['rows'] ?: [])
                    ])
                ];

                // Default value
                if (!$entityI['spaceScheme']) $entityI['spaceScheme'] = 'none';

                // Set space scheme settings
                self::$_entityA[$modelNameA[$entityI['id']]]['space'] = [
                    'scheme' => $entityI['spaceScheme'],
                    'coords' => $entityI['spaceScheme'] != 'none'
                        ? array_combine(
                            explode('-', $entityI['spaceScheme']),
                            array_flip(array_intersect(
                                array_flip($eFieldA[$entityI['id']]['aliases']),
                                ar($entityI['spaceFields'])
                            ))
                        ) : []
                ];

                // Free memory, used by fields array for current entity
                unset($eFieldA[$entityI['id']]);
            }

            // Setup notices
            if (self::$_entityA['Notice']
                && self::$_entityA['Notice']['fields'] instanceof Field_Rowset_Base
                && self::$_entityA['Notice']['fields']->count() >= 13) {

                // Get info about notices, attached to entities
                $noticeA = self::$_instance->query('
                    SELECT * FROM `notice` WHERE `toggle` = "y"' . ($entityId ? ' AND `entityId` = "' . $entityId . '"' : '') . '
                ')->fetchAll();

                // Group notices by their entity ids, preliminary converting
                // each notice into an instance of Indi_Db_Table_Row
                $eNoticeA = [];
                foreach ($noticeA as $noticeI)
                    $eNoticeA[$modelNameA[$noticeI['entityId']]][]
                        = new Notice_Row(['original' => $noticeI]);

                // Free memory
                unset($noticeA);

                // Convert array of notices into an instance of Indi_Db_Table_Rowset object,
                // and inject into entity specs array, under 'notices' key
                foreach ($eNoticeA as $modelName => $eNoticeRa)
                    self::$_entityA[$modelName]['notices'] = new Indi_Db_Table_Rowset([
                        'table' => 'notice',
                        'rows' => $eNoticeRa
                    ]);

                // Free memory
                unset($eNoticeA);
            }
        }

        // Return instance
        return self::$_instance;
    }

    /**
     * Getter for self::$_roleA
     *
     * @return array
     */
    public static function role() {
        return self::$_roleA;
    }

    /**
     * Loads and returns the model by model entity id, or model class name, or entity table name.
     *
     * @static
     * @param int|string $identifier
     * @param bool $check
     * @return Indi_Db_Table
     * @throws Exception
     */
    public static function model($identifier, $check = false) {

        // If $identifier argument is an entity id
        if (preg_match('/^[0-9]+$/', $identifier)) {

            // Try to find that id within ids of existing entities
            foreach (self::$_entityA as $className => $info) {
                if ($info['id'] == $identifier) {
                    $identifier = $className;
                    break;
                }
            }

            // If was not found, throw exception
            if ($identifier != $className)
                if ($check && $check !== 'destroy') return null;
                else throw new Exception('Entity with id ' . $identifier . ' does not exist');
        }

        // Uppercase the first char, as keys in self::$_modelA and self::$_entityA arrays are capitalized
        if (is_object($identifier)) throw new Exception();
        $identifier = ucfirst($identifier);

        // Else if found, but $check arg is 'destroy' - destroy model
        if ($check === 'destroy') {

            // Destroy model
            unset(self::$_modelA[$identifier], self::$_entityA[$identifier]);

            // Return null
            return null;
        }

        // If model is already loaded, we return it
        if (array_key_exists($identifier, self::$_modelA) == true) {
            return self::$_modelA[$identifier];

        // Else if model not loaded, but it's entity exists within self::$_entityA array
        } else if (array_key_exists($identifier, self::$_entityA)) {

            // If model class does not exist
            if (!class_exists($identifier)) {

                // Get model's parent class name from self::$_entityA array. If not parent class name there, set
                // it as 'Indi_Db_Table' by default
                if (!($extends = self::$_entityA[$identifier]['extends'])) $extends = 'Indi_Db_Table';

                // Declare model class, using php eval()
                eval('class ' . $identifier . ' extends ' . $extends . '{}');
            }

            // Create a model, push it to self::$_modelA array as a next item
            self::$_modelA[$identifier] = new $identifier(self::$_entityA[$identifier]);

            // Free memory
            unset(self::$_entityA[$identifier]['fields']);

            // Return model
            return self::$_modelA[$identifier];
        }

        // Throw exception
        if ($check) return null; else throw new Exception('Model "' . $identifier . '" does not exists');
    }

    /**
     * Execute a sql-query. :s, :i, and :p placeholders are supported to be within $sql arg,
     * but require additional args to be passed. See Indi_Db::sql() method description
     * for more details and usage examples. The difference betweeb query() and sql() methods is
     * that sql() only builds the sql query's string, unlike query(), that is not only building,
     * but executing too
     *
     * @uses Indi_Db::sql()
     * @param $sql
     * @return int|Indi_Db_PDOStatement
     */
    public function query($sql) {

        // If more than 1 arg is given, assume that other args are values to be injected into a sql query
        if (func_num_args() > 1) $sql = call_user_func_array([$this, 'sql'], func_get_args());

        // Trim the query
        $sql = trim($sql);

        // Here we separate all queries by their type, and and this time we deal with queries, that provide affected
        // rows count as return value of execution
        if (preg_match('/^UPDATE|DELETE|INSERT/', $sql)) {

            // Execute query and get affected rows count
            $affected = self::$_pdo->exec($sql);

            // Increment queries count
            self::$queryCount++;

            // Collect DELETE queries
            if (preg_match('/^DELETE/', $sql))
                self::$DELETEQueryA[] = [
                    'sql' => $sql,
                    'affected' => $affected
                ];

            // If no rows were affected and error reporting ($silence argument) is turned on
            // Display error message, backtrace info and make the global stop
            if ($affected === false) $this->jerror($sql);

            // Return affected rows count as a result of query execution
            return $affected;

        // Else if query was not UPDATE|DELETE|INSERT
        } else {

            // Exectute query by PDO->query() method
            $stmt = self::$_pdo->query($sql);

            // Increment queries count
            self::$queryCount++;

            // If query execition was not successful and mysql error reporting is on
            // Display error message, backtrace info and make the global stop
            if (!$stmt) $this->jerror($sql);

            // Else if all was ok, setup fetch mode as PDO::FETCH_ASSOC
            else if ($stmt) $stmt->setFetchMode(PDO::FETCH_ASSOC);

            // Return PDO statement
            return $stmt;
        }
    }

    /**
     * Flush the special-formatted error in case if mysql query execution failed
     *
     * @param string $sql An SQL query, that coused an error
     */
    public function jerror($sql) {

        // If $sql arg is an instance of PDOException class
        if ($sql instanceof PDOException) {

            // Get error message
            $errstr = preg_match('/WIN/', PHP_OS)
                ? iconv('windows-1251', 'utf-8', $sql->getMessage())
                : $sql->getMessage();

            $file = $sql->getFile();
            $line = $sql->getLine();

            // Set error code
            $errcode = 3;

        // Else
        } else {

            // Get error info
            list ($SQLSTATE, $driverCode, $errstr) = self::$_pdo->errorInfo();

            // If it's a foreign key constraint error saying "Cannot delete or update a parent row: ..."
            if (preg_match('~^DELETE FROM~', trim($sql)) && $driverCode === 1451) {

                // Cannot delete or update a parent row: a foreign key constraint fails (`custom`.`queueitem`, CONSTRAINT `queueItem_ibfk_queueChunkId` FOREIGN KEY (`queueChunkId`) REFERENCES `queuechunk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE)
                preg_match('~CONSTRAINT `(?<table>.*?)_ibfk_(?<column>.*?)`~', $errstr, $ref);

                // Prepare msg
                $msg = sprintf(I_ONDELETE_RESTRICT,
                    m($ref['table'])->fields($ref['column'])->title,
                    m($ref['table'])->title(),
                    $ref['table'],
                    $ref['column']
                );

                // Throw DeleteException
                throw new Indi_Db_DeleteException($msg);

            // Else
            } else {

                // Prepend the sql query
                $errstr = $sql . ' - ' . $errstr;

                // Remove the useless shit
                $errstr = str_replace('; check the manual that corresponds to your MySQL server version for the right syntax to use', '', $errstr);
                $errstr = preg_replace('/at line [0-9]+/', '', $errstr);

                // Set error code
                $errcode = 0;

                // Get line and file
                extract(array_pop(array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1, 1)));
            }
        }

        // Flush an error
        iexit(jerror($errcode, $errstr, $file, $line));
    }

    /**
     * Return PDO object. Currently, the only purpose of this method is to provide an ability to call lastInsertId()
     * method on it, in insert() method in Indi_Db_Table class
     *
     * @return null|PDO
     */
    public function getPDO() {
        return self::$_pdo;
    }

    /**
     * Begin the transaction, if it not had yet begun
     */
    public function begin() {

        // Begin the transaction, if it not had yet begun
        if (self::$_transactionLevel == 0) self::$_instance->query('START TRANSACTION');

        // Increment the transaction level
        self::$_transactionLevel ++;
    }

    /**
     * Rollback the transaction
     */
    public function rollback() {

        // Rollback
        self::$_instance->query('ROLLBACK');

        // Return `false`. Here we do it because we will be using 'return db()->rollback()' statements
        return false;
    }

    /**
     * Commit the transaction
     */
    public function commit() {

        // Decrease the transaction level
        self::$_transactionLevel --;

        // if we a at the most top transaction level - commit the transaction,
        if (self::$_transactionLevel == 0) self::$_instance->query('COMMIT');

        // Return `false`. Here we do it because we will be using 'return db()->rollback()' statements
        return true;
    }

    /**
     * Replace placeholders with values - either in the full query or a query part
     * unlike native prepared statements, allows any query part to be parsed
     *
     * Supported placeholders
     *  :s - strings
     *  :i - integers
     *  :p - already parsed query parts
     *
     * Example:
     * $qpart = $someBool ? db()->sql(' AND `bar` = :s', $bar) : '';
     * $sql = db()->sql('SELECT * FROM `table` WHERE `foo` = :s :p LIMIT :i', $foo, $qpart, $qty);
     * echo $sql;
     *
     * @param string $tpl - whatever expression that contains placeholders
     * @param mixed  $arg1,... unlimited number of arguments to match placeholders in the expression
     * @return string - initial expression with placeholders substituted with data.
     */
    public function sql($tpl, $arg1 = null) {

        // Get arguments
        $args = func_get_args();

        // Get the sql-query template
        $tpl = array_shift($args);

        // If no args remaining after shifting - return
        if (!$args) return $tpl;

        // Final query, empty yet
        $sql = '';

        // Split given sql-query template by variable-expressions
        $rawA = preg_split('~(:[spi])~u', $tpl, null, PREG_SPLIT_DELIM_CAPTURE);

        // Get quantity of given arguments, excluding first, as we assume it's a sql query
        $aQty  = count($args);

        // Get quantity of placeholder-expressions, mentioned within sql query
        $pQty  = floor(count($rawA) / 2);

        // Check that both quantities are equal
        if ($pQty != $aQty) jflush(false, 'Number of args ('. $aQty
            . ') doesn\'t match number of placeholders ('. $pQty . ') in [' . $tpl . ']');

        // Walk through sql-template parts
        foreach ($rawA as $i => $rawI) {

            // Concat non-placeholder part and jump to next iteration
            if (($i % 2) == 0) {
                $sql .= $rawI;
                continue;
            }

            // Pick arg
            $value = array_shift($args);

            // Apply different behaviour depend on placeholder type
            switch ($rawI) {
                case ':s':
                    $rawI = $value === null ? '""' : self::$_pdo->quote($value, PDO::PARAM_STR);
                    break;
                case ':i':
                    $rawI = $value === null ? '0' : (is_numeric($value) ? decimal($value, 0) : self::$_pdo->quote($value, PDO::PARAM_STR));
                    break;
                case ':p':
                    $rawI = $value;
                    break;
            }

            // Concat
            $sql .= $rawI;
        }

        // Return
        return $sql;
    }

    /**
     * Get all localized field, grouped by by table name,
     * or get localized fields for a given table,
     * or check if some field is localized within given table
     *
     * @static
     * @param null $table
     * @param null $field
     * @return array|bool
     */
    public static function l10n($table = null, $field = null) {
        if (func_num_args() == 0) return self::$_l10nA;
        else if (func_num_args() == 1) return self::$_l10nA[$table];
        else if (func_num_args() > 1) return in_array($field, self::$_l10nA[$table]);
    }

    /**
     * Return *_Row instance from $entity's preloaded instances storage by given $key
     *
     * @param $entity
     * @param $key
     * @return mixed
     */
    public function preloadedRow($entity, $key) {

        // Preload if not yet preloaded
        $this->_preload($entity);

        // Return preloaded *_Row instance
        return self::$_preloadedA[$entity][$key];
    }

    /**
     * Pick *_Row instances from preloaded instances storage by given $keys, wrap into a *_Rowset instance and return
     *
     * @param $entity
     * @param $keys
     * @return Indi_Db_Table_Rowset
     */
    public function preloadedAll($entity, $keys) {

        // Preload if not yet preloaded
        $this->_preload($entity);

        // Pick *_Row instances from self::$_preloadedA[$entity]
        $rows = []; foreach(ar($keys) as $key) array_push($rows, self::$_preloadedA[$entity][$key]);

        // Wrap picked rows into a rowset and return it
        return m($entity)->createRowset(['rows' => $rows]);
    }

    /**
     * Preload *_Row instances of given $entity, and store them into self::$_preloadedA[$entity] array,
     * having instances' ids as keys
     *
     * @param $entity
     */
    protected function _preload($entity) {

        // If already preloaded - return
        if (array_key_exists($entity, self::$_preloadedA)) return;

        // Else preload
        foreach (m($entity)->all() as $row) self::$_preloadedA[$entity][$row->id] = $row;
    }

    /**
     * Proxy for PDO::quote()
     *
     * @param $value
     * @return string
     */
    public function quote($value) {
        return $this->getPDO()->quote($value);
    }
}