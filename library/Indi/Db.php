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
    protected static $_modelA = array();

    /**
     * Array of existing entities, with capitalized entity table names as keys
     *
     * @var array
     */
    protected static $_entityA = array();

    /**
     * Array of table names of existing entities, which have `useCache` flag turned on
     *
     * @var array
     */
    protected static $_cacheA = array();

    /**
     * Array of table names of existing entities, which have `alternate` flag turned on
     *
     * @var array
     */
    protected static $_roleA = array();

    /**
     * Localized fields, grouped by table names
     *
     * @var array
     */
    protected static $_l10nA = array();

    /**
     * Store queries count
     *
     * @var Indi_Db
     */
    public static $queryCount = 0;

    /**
     * @var array
     */
    public static $DELETEQueryA = array();

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
    public static function factory($arg = array())
    {
        // If singleton instance is not yet created or 'reload' key exists within $config array argument
        if (null === self::$_instance || is_int($arg)) {

            // If singleton instance is not yet created
            if (null === self::$_instance) {

                // Create it
                self::$_instance = new self();

                // Try to create PDO instance
                try {

                    // Setup a PDO object
                    @self::$_pdo = new PDO($arg->adapter . ':dbname=' . $arg->dbname . ';host=' . $arg->host,
                        $arg->username, $arg->password);

                // If something goes wrong
                } catch (PDOException $e) {

                    // Pass caught exception to the own handler
                    self::$_instance->jerror($e);
                }

                // Setup encoding
                self::$_instance->query('SET NAMES utf8');
                self::$_instance->query('SET CHARACTER SET utf8');

            // Else if singleton instance was already created, but $arg agument is an entity id - setup $entityId variable
            } else if (is_int($arg)) {

                $entityId = $arg;
            }

            // Get info about existing entities, or one certain entity, identified by id,
            // passed within value of 'model' key of $config argument
            $entityA = self::$_instance->query(
                'SELECT * FROM `entity`' . ($entityId ? ' WHERE `id` = "' . $entityId . '"' : '')
            )->fetchAll();

            // Get ids of entities, linked to access roles
            self::$_roleA = self::$_instance->query('
                SELECT
                  GROUP_CONCAT(`id`) AS `profileIds`,
                  IF(`entityId`,`entityId`,11) AS `entityId`
                FROM `profile`
                GROUP BY `entityId`
            ')->fetchAll(PDO::FETCH_KEY_PAIR);

            // Fix tablename case, if need
            if (!$entityId && !preg_match('/^WIN/i', PHP_OS) && self::$_instance->query('SHOW TABLES LIKE "columntype"')->fetchColumn()) {
            
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

            // Collect localized fields
            foreach ($fieldA as $fieldI)
                if ($fieldI['l10n'] == 'y')
                    self::$_l10nA[$_[$fieldI['entityId']]][] = $fieldI['alias'];

            // Unset tmp variable
            unset($_);

            // Get info about existing control elements
            $elementA = self::$_instance->query('SELECT * FROM `element`')->fetchAll();
            $iElementA = array(); foreach ($elementA as $elementI)
                $iElementA[$elementI['id']] = new Indi_Db_Table_Row_Noeval(array(
                    'table' => 'element',
                    'original' => $elementI
                ));
            unset($elementA);

            // Get info about existing column types
            $columnTypeA = self::$_instance->query('SELECT * FROM `columnType`')->fetchAll();
            $iColumnTypeA = array(); foreach ($columnTypeA as $columnTypeI)
                $iColumnTypeA[$columnTypeI['id']] = new ColumnType_Row(array(
                    'table' => 'columnType',
                    'original' => $columnTypeI
                ));
            unset($columnTypeA);

            // If certain model should be reloaded, collect ids of it's fields, for use them as a part of WHERE clause
            // in fetch from `enumset`
            if ($entityId) {

                // Declare array for collecting fields ids
                $fieldIdA = array();

                // Fulfil that array
                foreach ($fieldA as $fieldI) $fieldIdA[] = $fieldI['id'];
            }

            // Get info about existing enumset values
            $enumsetA = self::$_instance->query(
                'SELECT * FROM `enumset`' . (is_array($fieldIdA) ? ' WHERE FIND_IN_SET(`fieldId`, "' .
                implode(',', $fieldIdA) . '") ' : '') . 'ORDER BY `move`'
            )->fetchAll();

            $fEnumsetA = array(); foreach ($enumsetA as $enumsetI)
                $fEnumsetA[$enumsetI['fieldId']][] = new Enumset_Row(array(
                    'table' => 'enumset',
                    'original' => $enumsetI
                ));
            unset($enumsetA);

            // Get info about existing field params
            // 1. Get info about possible field element params
            $possibleElementParamA = self::$_instance->query('SELECT * FROM `possibleElementParam`')->fetchAll();

            // 2. Declare two arrays, where:
            //   a. possible params as array of arrays, each having params aliases as keys, and devault values as
            //      values, grouped by elementId
            //   b. possible params as array, having params ids as keys, and aliases as values
            // - respectively
            $ePossibleElementParamA = array(); $possibleElementParamAliasA = array();

            // 3. Fulfil these two arrays
            foreach ($possibleElementParamA as $possibleElementParamI) {
                $ePossibleElementParamA[$possibleElementParamI['elementId']]
                    [$possibleElementParamI['alias']] = $possibleElementParamI['defaultValue'];
                $possibleElementParamAliasA[$possibleElementParamI['id']] = $possibleElementParamI['alias'];
            }
            unset($possibleElementParamA);

            // 4. Get info about existing field parameters
            $paramA = self::$_instance->query('SELECT * FROM `param`' . (is_array($fieldIdA)
                ? ' WHERE FIND_IN_SET(`fieldId`, "' . implode(',', $fieldIdA) . '") ' : ''))->fetchAll();
            $fParamA = array(); foreach ($paramA as $paramI) $fParamA[$paramI['fieldId']]
            [$possibleElementParamAliasA[$paramI['possibleParamId']]] = $paramI['value']; unset($paramA);
            unset($paramA);

            // Group fields by their entity ids, and append system info
            $eFieldA = array();
            foreach ($fieldA as $fieldI) {

                // Setup original data
                $fieldI = array('original' => $fieldI);

                // Setup foreign data for 'elementId' foreign key
                $fieldI['foreign']['elementId'] = $iElementA[$fieldI['original']['elementId']];

                // Setup foreign data for 'columnTypeId' foreign key, if field has a non-zero columnTypeId
                if ($iColumnTypeA[$fieldI['original']['columnTypeId']])
                    $fieldI['foreign']['columnTypeId'] = $iColumnTypeA[$fieldI['original']['columnTypeId']];

                // Setup nested rowset with 'enumset' rows, if field contains foreign keys from 'enumset' table
                if ($fieldI['original']['relation'] == '6') {
                    $fieldI['nested']['enumset'] = new Indi_Db_Table_Rowset(array(
                        'table' => 'enumset',
                        'rows' => $fEnumsetA[$fieldI['original']['id']],
                        'rowClass' => 'Enumset_Row',
                        'found' => count($fEnumsetA[$fieldI['original']['id']])
                    ));
                    unset($fEnumsetA[$fieldI['id']]);
                }

                // Setup params, as array, containing default values, and actual values arrays merged to single array
                if ($ePossibleElementParamA[$fieldI['original']['elementId']] || $fParamA[$fieldI['original']['id']]) {
                    $fieldI['temporary']['params'] = array_merge(
                        is_array($ePossibleElementParamA[$fieldI['original']['elementId']])
                            ? $ePossibleElementParamA[$fieldI['original']['elementId']]
                            : array(),
                        is_array($fParamA[$fieldI['original']['id']])
                            ? $fParamA[$fieldI['original']['id']]
                            : array()
                    );
                }

                // Append current field data to $eFieldA array
                $eFieldA[$fieldI['original']['entityId']]['rows'][] = new Field_Row($fieldI);
                $eFieldA[$fieldI['original']['entityId']]['aliases'][$fieldI['original']['id']] = $fieldI['original']['alias'];
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
            $modelNameA = array();

            // Foreach existing entity
            foreach ($entityA as $entityI) {

                // Collect "entityId => modelName" pairs
                $modelNameA[$entityI['id']] = ucfirst($entityI['table']);

                // Create an item within self::$_entityA array, containing some basic info
                self::$_entityA[$modelNameA[$entityI['id']]] = array(
                    'id' => $entityI['id'],
                    'title' => $entityI['title'],
                    'extends' => $entityI['extends'],
                    'useCache' => $entityI['useCache'],
                    'titleFieldId' => $entityI['titleFieldId'],
                    'hasRole' => in_array($entityI['id'], self::$_roleA),
                    'fields' => new Field_Rowset_Base(array(
                        'table' => 'field',
                        'rows' => $eFieldA[$entityI['id']]['rows'],
                        'aliases' => array_values($eFieldA[$entityI['id']]['aliases'] ?: array()),
                        'rowClass' => 'Field_Row'
                    ))
                );

                // Default value
                if (!$entityI['spaceScheme']) $entityI['spaceScheme'] = 'none';

                // Set space scheme settings
                self::$_entityA[$modelNameA[$entityI['id']]]['space'] = array(
                    'scheme' => $entityI['spaceScheme'],
                    'fields' => $entityI['spaceScheme'] != 'none'
                        ? array_combine(
                            explode('-', $entityI['spaceScheme']),
                            array_flip(array_intersect(
                                array_flip($eFieldA[$entityI['id']]['aliases']),
                                ar($entityI['spaceFields'])
                            ))
                        ) : array()
                );

                // Free memory, used by fields array for current entity
                unset($eFieldA[$entityI['id']]);

                // If cache usage is setup for current entity, we append it's table name as a key in self::$_cacheA array
                if ($entityI['useCache']) self::$_cacheA[$entityI['table']] = true;
            }

            // Setup notices
            if (self::$_entityA['Notice']) {

                // Get info about notices, attached to entities
                $noticeA = self::$_instance->query('
                    SELECT * FROM `notice` WHERE `toggle` = "y"' . ($entityId ? ' AND `entityId` = "' . $entityId . '"' : '') . '
                ')->fetchAll();

                // Group notices by their entity ids, preliminary converting
                // each notice into an instance of Indi_Db_Table_Row
                $eNoticeA = array();
                foreach ($noticeA as $noticeI)
                    $eNoticeA[$modelNameA[$noticeI['entityId']]][]
                        = new Notice_Row(array('original' => $noticeI));

                // Free memory
                unset($noticeA);

                // Convert array of notices into an instance of Indi_Db_Table_Rowset object,
                // and inject into entity specs array, under 'notices' key
                foreach ($eNoticeA as $modelName => $eNoticeRa)
                    self::$_entityA[$modelName]['notices'] = new Indi_Db_Table_Rowset(array(
                        'table' => 'notice',
                        'rows' => $eNoticeRa
                    ));

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
                if ($check) return null; else throw new Exception('Entity with id ' . $identifier . ' does not exist');
        }

        // Uppercase the first char, as keys in self::$_modelA and self::$_entityA arrays are capitalized
        if (is_object($identifier)) throw new Exception();
        $identifier = ucfirst($identifier);

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
     * @return Indi_Cache_Fetcher|int|PDOStatement
     */
    public function query($sql) {

        // If more than 1 arg is given, assume that other args are values to be injected into a sql query
        if (func_num_args() > 1) $sql = call_user_func_array(array($this, 'sql'), func_get_args());

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
                self::$DELETEQueryA[] = array(
                    'sql' => $sql,
                    'affected' => $affected
                );

            // If no rows were affected and error reporting ($silence argument) is turned on
            // Display error message, backtrace info and make the global stop
            if ($affected === false) $this->jerror($sql);

            // Return affected rows count as a result of query execution
            return $affected;

        // If cache usage is turned on, and current query match cache usage requirements
        } else if (Indi::ini()->db->cache && $params = self::shouldUseCache($sql)) {

            // Pass query to Indi_Cache::fetcher() method
            return Indi_Cache::fetcher($params);

        // Else if query was not UPDATE|DELETE|INSERT, and query did not match Indi_Cache::fetcher() requirements
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

            // Get the native mysql error message
            $errstr = array_pop(self::$_pdo->errorInfo());

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
     * Check if current sql query execution can be handled with Indi_Cache::fetcher() method,
     * and if so, return an array, containing table name, columns list and WHERE clause
     *
     * @param $sql
     * @return array|bool|null
     */
    public function shouldUseCache($sql) {

        // Check if query is enough simple for Indi_Cache::fetcher() to deal with it
        if ($params = Indi_Cache_Fetcher::support($sql))

            // If table name, got from query FROM clause is within keys of self::$_cacheA array
            if (self::$_cacheA[$params['table']] || $params['table'] == 'entity')

                // And if file with cached data for that table exists
                if (file_exists(Indi_Cache::file($params['table'])))

                    // Return info about catched table name, WHERE, ORDER and LIMIT clauses
                    // and columns, that should be retrieved
                    return $params;

        // Return false
        return false;
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

        // Return `false`. Here we do it because we will be using 'return Indi::db()->rollback()' statements
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

        // Return `false`. Here we do it because we will be using 'return Indi::db()->rollback()' statements
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
     * $qpart = $someBool ? Indi::db()->sql(' AND `bar` = :s', $bar) : '';
     * $sql = Indi::db()->sql('SELECT * FROM `table` WHERE `foo` = :s :p LIMIT :i', $foo, $qpart, $qty);
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
}