<?php
class Year extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Year_Row';

    /**
     * Array of [title => id] pairs fetched from `year` table, where title is 4-digit year e.g same as given by date('Y')
     *
     * @var array
     */
    protected static $_yearIdA = null;

    /**
     * Array of [id => title] pairs fetched from `year` table, where title is 4-digit year e.g same as given by date('Y')
     *
     * @var array
     */
    protected static $_yearYA = null;

    /**
     * Get an entry within `year` table, that represents current (or given by $year arg) year
     * as an instance of stdClass object. If such entry does not yet exists - it will be created
     *
     * @static
     * @param $year 4-digit year
     * @return stdClass
     */
    public static function o($year = null) {

        // Extract 4-digit year from a current date
        list($y) = explode('-', $year ?: date('Y'));

        // If current 4-digit year not exists within `year` table
        if (!$yearI = db()->query('SELECT `id`, `title` FROM `year` WHERE `title` = "' . $y . '"')->fetch()) {

            // Create it
            db()->query('INSERT INTO `year` SET `title` = "' . $y . '"');

            // Get it's id
            $yearO = (object) [
                'id' => db()->getPDO()->lastInsertId(),
                'title' => $y
            ];

            // Else convert got $yearA into an stdClass instance
        } else $yearO = (object) $yearI;

        // Return
        return $yearO;
    }

    /**
     * Get `id` of `year` entry that $date arg relates to.
     * $date can be any date compatible with php's strtotime() function
     * If $date arg is 'all' - all [year => id] pairs are returned
     * If $date arg is not given - current date assumed
     * If $date arg is given but is falsy or is not recognizable by strtotime() - boolean false is returned
     * If no `year` entry exists - it's created
     *
     * @static
     * @param null|string $date
     * @return int|array|bool
     */
    public static function yearId($date = null) {

        // If self::$_yearIdA is null - fetch key-value pairs
        if (self::$_yearIdA === null) self::$_yearIdA = db()->query('
            SELECT `title`, `id`
            FROM `year`
            ORDER BY `title`
        ')->pairs();

        // If self::$_yearYA is null - setup it by flipping self::$_yearIdA
        if (self::$_yearYA === null) self::$_yearYA = array_flip(self::$_yearIdA);

        // If $date arg is 'all' - return all pairs we have
        if ($date === 'all') return self::$_yearIdA;

        // If $date arg is NOT given - assume current date
        if ($date === null) $date = date('Y');

        // If $date arg is falsy - return false
        if (!$date) return false;

        // Get time to base on
        $time = strtotime($date);

        // If $date arg is not recognizable by php's strtotime() - return false
        if ($time === false) return false;

        // Get yyyy value
        $yearY = date('Y', $time);

        // If there is no year-record for such year so far
        if (!self::$_yearIdA[$yearY]) {

            // Create new year-record and get it's id
            $yearId = self::o($yearY)->id;

            // Update pairs
            self::$_yearIdA[$yearY] = $yearId;

            // Make sure new year to appear inside $_yearIdA according to it's chronology
            ksort(self::$_yearIdA);

            // Update $_yearhYA
            self::$_yearYA = array_flip(self::$_yearIdA);
        }

        // Return id of corresponding `year` entry, that $date belongs to
        return self::$_yearIdA[$yearY];
    }

    /**
     * Get title of a year-record by $yearId, where title is 4-digit year number, e.g same as given by date('Y')
     * If $yearId arg is 'all' - all [year => id] pairs are returned
     * If $yearId arg is null or not given - current date assumed
     *
     * @static
     * @param null|int $yearId
     * @return array|false|string
     */
    public static function yearY($yearId = null) {

        // If self::$_yearYA is null - fetch key-value pairs
        if (self::$_yearYA === null) self::$_yearYA = db()->query('
            SELECT `id`, `title`
            FROM `year`
            ORDER BY `title`
        ')->pairs();

        // If self::$_yearIdA is null - setup it by flipping self::$_yearYA
        if (self::$_yearIdA === null) self::$_yearIdA = array_flip(self::$_yearYA);

        // If $yearId arg is 'all' - return all pairs we have
        if ($yearId === 'all') return self::$_yearYA;

        // If $yearId arg is null or not given - assume current date
        if ($yearId === null) $yearId = yearId();

        // If $yearId arg is falsy - return false
        if (!$yearId) return false;

        // Return 4-digit year or false
        return self::$_yearYA[$yearId] ?? false;
    }
}