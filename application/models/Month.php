<?php
class Month extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Month_Row';

    /**
     * @var string
     */
    public $comboDataOrder = '`yearId` $dir, `month`';

    /**
     * @var string
     */
    public $comboDataOrderDirection = 'DESC';

    /**
     * Array of key-value pairs fetched from `month` and `year` tables
     * using CONCAT(`y`.`title`, "-", `m`.`month`) as keys and `m`.`id` as values
     *
     * @var array
     */
    protected static $_monthIdA = null;

    /**
     * Array of key-value pairs fetched from `month` and `year` tables
     * using `m`.`id` as keys and CONCAT(`y`.`title`, "-", `m`.`month`) as values
     *
     * @var array
     */
    protected static $_monthYmA = null;

    /**
     * Return an instance of Month_Row class, representing current month within current year
     *
     * @param int $shift Shift in months, can be negative
     * @return Month_Row|null
     */
    public function now($shift = 0) {

        // Build date in 'Y-m-d' format, using $shift argument, if given
        $date = date('Y-m-d', $shift ? mktime(0, 0, 0, date('m') + $shift, date('d'), date('Y')) : time());

        // Extract 4-digit year and 2-digit month from a current date
        list($y, $m) = explode('-', $date);

        // If there is no such a year entry found
        if (!$yearR = Indi::model('Year')->fetchRow('`title` = "' . $y . '"')) {

            // Create it
            $yearR = Indi::model('Year')->createRow()->assign(array('title' => $y));
            $yearR->save();
        }


        // If there is no such a month entry found
        if (!$monthR = $this->fetchRow('`month` = "' . $m . '" AND `yearId` = "' . $yearR->id . '"')) {

            // Create it
            $monthR = $this->createRow()->assign(array('month' => $m, 'yearId' => $yearR->id));
            $monthR->save();
        }

        // Return month row
        return $monthR;
    }

    /**
     * Return Month_Row instance, representing previous month
     *
     * @return Month_Row|null
     */
    public function was() {
        return $this->now(-1);
    }

    /**
     * Get an entry within `month` table, that represents current (or given by $date arg) month
     * as an instance of stdClass object. If such entry does not yet exists - it will be created
     *
     * @static
     * @param $date string Date in php 'Y-m-d' format
     * @return stdClass
     */
    public static function o($date = null) {

        // Extract 4-digit year and 2-digit month from a current date
        list($y, $m) = explode('-', $date ?: date('Y-m-d'));

        // Get year
        $yearO = Year::o($y);

        // If current month not exists within `month` table
        if (!$monthI = db()->query('
            SELECT * FROM `month` WHERE `month` = "' . $m . '" AND `yearId` = "' . $yearO->id . '"
        ')->fetch()) {

            // Build month title
            $title = db()->query('
                SELECT `es`.`title`
                FROM `enumset` `es`, `field` `f`, `entity` `e`
                WHERE 1
                  AND `es`.`alias` = "'. $m . '"
                  AND `f`.`id` = `es`.`fieldId`
                  AND `f`.`alias` = "month"
                  AND `f`.`entityId` = `e`.`id`
                  AND `e`.`table` = "month"
              ')->fetchColumn() . ' ' . $yearO->title;

            // Create month entry
            db()->query('
                INSERT INTO `month`
                SET
                  `month` = "' . $m . '",
                  ' . db()->sql('`title` = :s,', $title) . '
                  `yearId` = "'. $yearO->id . '"
            ');

            // Get id
            $id = db()->getPDO()->lastInsertId();

            // Get it's id
            $monthI = array(
                'id' => $id,
                'yearId' => $yearO->id,
                'month' => $m,
                'title' => $title
            );

            // Set `move`
            db()->query('UPDATE `month` SET `move` = "' . $id . '" WHERE `id` = "' . $id . '"');
        }

        // Return month entry as an instance of stdClass object
        return (object) $monthI;
    }

    /**
     * Get `id` of `month` entry that $date arg relates to.
     * $date arg should contain month definition, so it can be an exact
     * date (e.g in 'Y-m-d' format) or datetime (e.g. in 'Y-m-d H:i:s') format
     * or expression, containing month definition only (e.g in 'Y-m' format)
     *
     * @static
     * @param null|string $date
     * @return int|array|null
     */
    public static function monthId($date = null) {

        // If self::$_monthIdA is null - fetch key-value pairs
        if (self::$_monthIdA === null) self::$_monthIdA = db()->query('
            SELECT CONCAT(`y`.`title`, "-", `m`.`month`) AS `Ym`, `m`.`id`
            FROM `month` `m`, `year` `y`
            WHERE `m`.`yearId` = `y`.`id`
            ORDER BY `Ym`
        ')->fetchAll(PDO::FETCH_KEY_PAIR);

        // If self::$_monthYmA is null - setup it by flipping self::$_monthIdA
        if (self::$_monthYmA === null) self::$_monthYmA = array_flip(self::$_monthIdA);

        // If $date arg is given - return id of corresponding `month` entry, that $date belongs to
        return $date ? self::$_monthIdA[substr($date, 0, 7)] : self::$_monthIdA;
    }

    /**
     * Get `yyyy-mm` expr of `month` entry having `id` same as $monthId arg
     *
     * @static
     */
    public static function monthYm($monthId = null) {

        // If self::$_monthYmA is null - fetch key-value pairs
        if (self::$_monthYmA === null) self::$_monthYmA = db()->query('
            SELECT `m`.`id`, CONCAT(`y`.`title`, "-", `m`.`month`) AS `Ym`
            FROM `month` `m`, `year` `y`
            WHERE `m`.`yearId` = `y`.`id`
            ORDER BY `Ym`
        ')->fetchAll(PDO::FETCH_KEY_PAIR);

        // If self::$_monthIdA is null - setup it by flipping self::$_monthYmA
        if (self::$_monthIdA === null) self::$_monthIdA = array_flip(self::$_monthYmA);

        // If $monthId arg is given - return 'yyyy-mm' expr of corresponding `month` entry
        return $monthId ? self::$_monthYmA[$monthId] : self::$_monthYmA;
    }

    /**
     * Calc difference in months between $Ym1 and $Ym2 args
     *
     * @static
     * @param $Ym1
     * @param $Ym2
     * @return int
     */
    public static function diff($Ym1, $Ym2) {
        $Ym1 = explode('-', $Ym1);
        $Ym2 = explode('-', $Ym2);
        return $Ym1[0] * 12 + $Ym1[1] - ($Ym2[0] * 12 + $Ym2[1]);
    }
}