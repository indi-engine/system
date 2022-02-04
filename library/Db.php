<?php
/**
 * This class is used to make a queries to some other databases
 */
class Db {

    /**
     * Hostname
     *
     * @var
     */
    public static $h;

    /**
     * Username
     *
     * @var
     */
    public static $u;

    /**
     * Password
     *
     * @var
     */
    public static $p;

    /**
     * Database
     *
     * @var
     */
    public static $d;

    /**
     * Connection
     *
     * @var
     */
    public static $c;

    /**
     * Constructor
     *
     * @param string $h
     * @param string $u
     * @param string $p
     * @param string $d
     */
    function __construct($h, $u, $p, $d) {

        // Setup the access credentials
        self::$h = $h; self::$u = $u; self::$p = $p; self::$d = $d;

        // Setup the connection
        self::$c = @mysqli_connect(self::$h, self::$u, self::$p);
        if ($e = mysqli_connect_error()) die(iconv('windows-1251', 'utf-8', $e));

        // Select the database
        mysqli_select_db(self::$c, self::$d);

        // Setup encoding
        self::query('SET NAMES utf8 COLLATE utf8_general_ci'); self::query('SET CHARACTER SET utf8');
    }

    /**
     * Get the certain $field field value of a certain row, fetched from $table table, according to WHERE and ORDER clauses,
     * represented by $where and $order argument respectively
     *
     * @param string $table
     * @param string $field
     * @param string $where
     * @param string $order
     * @return bool|mixed
     */
    function fget($table, $field, $where, $order = null) {

        // Build the sql query
        $sql = 'SELECT ' . $field . ' FROM ' . $table . ' ' . ($where ? 'WHERE ' . $where : '') . ($order ? ' ORDER BY ' . $order : '') . ' LIMIT 1';

        // Get the result resource
        $mr = self::query($sql);

        // If number of rows in the result is 1 or more - fetch and return
        // the value of first column of a first row, or return false otherwise
        return mysqli_num_rows($mr) ? current((array) mysqli_fetch_object($mr)) : false;
    }

    /**
     * Get the certain row (full row, or it's certain fields), fetched from $table table,
     * according to WHERE and ORDER clauses, represented by $where and $order argument respectively
     *
     * @param string $table
     * @param string $where
     * @param string $field
     * @param string $order
     * @return array|bool
     */
    function rget($table, $where, $field = '*', $order = null) {

        // Build the sql query
        $sql = 'SELECT ' . $field . ' FROM ' . $table . ' ' . ($where ? 'WHERE ' . $where : '') . ($order ? ' ORDER BY ' . $order : '') . ' LIMIT 1';

        // Get the result resource
        $mr = self::query($sql);

        // If number of rows in the result is 1 or more - fetch and return the first row, or return false otherwise
        return mysqli_num_rows($mr) ? (array) mysqli_fetch_object($mr) : false;
    }

    /**
     * Get the certain rowset (full rows, or certain fields), fetched from $table table,
     * according to WHERE and ORDER clauses, represented by $where and $order argument respectively
     *
     * @param string $table
     * @param string $where
     * @param string $field
     * @param string $order
     * @return array
     */
    function mget($table, $where = '', $field = '*', $order = null) {

        // Build the sql query
        $sql = preg_match('/\s/', $table)
            ? $table
            : 'SELECT ' . $field . ' FROM ' . $table . ' ' . ($where ? 'WHERE ' . $where : '') . ($order ? ' ORDER BY ' . $order : '');

        // Get the result resource
        $mr = self::query($sql);

        // Setup the array representation of a fetched rowset
        $rs = []; while ($r = mysqli_fetch_object($mr)) $rs[] = (array) $r;

        // Return that array
        return $rs;
    }

    /**
     * Update the certain $field field with a certain value $value, within database $table table,
     * according to WHERE clause, represented by $where argument
     *
     * @param string $table
     * @param string $field
     * @param string $value
     * @param string $where
     */
    function fset($table, $field, $value, $where) {

        // Build the sql query
        $sql = 'UPDATE ' . $table . ' SET ' . $field . ' = "' . str_replace('"','\"',$value) . '"' . ($where ? ' WHERE ' . $where : '');

        // Run that query
        return self::query($sql);
    }

    /**
     * Update the certain fields with a certain values, represented by keys and values of $data argument respectively,
     * within database $table table, accroding to WHERE clause, represented by $where argument
     *
     * @param string $table
     * @param array $data
     * @param string $where
     */
    function rset($table, array $data, $where) {

        // Start building the sql query
        $sql = 'UPDATE ' . $table . ' SET ';

        // Build the value assigning array
        foreach($data as $field => $value)
            $set[] = '`' . $field . '` = "' . str_replace('"', '\"', $value) . '"';

        // Implode that array and append it to the query
        $sql .= implode(', ', $set);

        // Append WHERE clause to the query
        $sql .= ($where ? ' WHERE ' . $where : '');

        // Run the query
        return self::query($sql);
    }

    /**
     * Update or insert data within a $table table, depend on where or not $where argument was given
     *
     * @param string $table
     * @param array $data
     * @param null $where
     */
    function save($table, array $data, $where = null) {

        // If $where argument was given - call self::mset() method, else
        if ($where) return self::rset($table, $data, $where); else {

            // Start building the INSERT query
            $sql = 'INSERT INTO ' . $table . ' SET ';

            // Build the value assigning array
            foreach ($data as $field => $value) {
                $set[] = '`' . $field . '` = "' . str_replace('"', '&quot;', $value) . '"';
            }

            // Implode that array and append it to the query
            $sql .= implode(', ', $set);

            // Run the query
            return self::query($sql);
        }
    }

    /**
     * Display mysql error, and sql query, that caused that error
     *
     * @param string $sql
     */
    function error($sql) {
        d(mysqli_error(self::$c));
        d($sql);
    }

    /**
     * Run the sql query, and return the result of the execution, or display an error
     *
     * @param string $sql
     */
    function query($sql){

        // Run the query
        $r = mysqli_query(self::$c, $sql);

        // If error occured
        if(mysqli_error(self::$c)) {

            // Show the error
            self::error($sql);

            // Stop the script
            die();

        // Else if query executed ok
        } else {

            // If this was an INSERT query
            if (preg_match('/^INSERT/', $sql)) {

                // Return mysql last insert id
                return mysqli_insert_id(self::$c);

            // Else if it was an UPDATE or DELETE query
            } else if (preg_match('/^UPDATE|DELETE/', $sql)) {

                // Return number of affected rows
                return mysqli_affected_rows(self::$c);

            // Else
            } else {

                // Return result resource
                return $r;
            }
        }
    }
}