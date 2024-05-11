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
        if (!$yearR = m('Year')->row('`title` = "' . $y . '"')) {

            // Create it
            $yearR = m('Year')->new(['title' => $y]);
            $yearR->save();
        }


        // If there is no such a month entry found
        if (!$monthR = $this->row('`month` = "' . $m . '" AND `yearId` = "' . $yearR->id . '"')) {

            // Create it
            $monthR = $this->new(['month' => $m, 'yearId' => $yearR->id]);
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

        // Get year id
        $yearId = yearId($date);

        // If current month not exists within `month` table
        if (!$monthI = db()->query("SELECT * FROM `month` WHERE `month` = '$m' AND `yearId` = '$yearId'")->fetch()) {

            // Create month entry
            $monthR = m('month')->new(['month' => $m, 'yearId' => $yearId]);
            $monthR->save();

            // Get it's id
            $monthI = [
                'id' => $monthR->id,
                'yearId' => $yearId,
                'month' => $m,
                'title' => $monthR->title
            ];
        }

        // Return month entry as an instance of stdClass object
        return (object) $monthI;
    }

    /**
     * Get `id` of `month` entry that $date arg relates to.
     * $date can be any date compatible with php's strtotime() function
     * If $date arg is 'all' - all [year => id] pairs are returned
     * If $date arg is null or not given - current date assumed
     * If $date arg is given but is falsy or is not recognizable by strtotime() - boolean false is returned
     * If no `month` entry exists - it's created
     *
     * @static
     * @param null|string $date
     * @return int|array|bool
     */
    public static function monthId($date = null) {

        // If it's a zero-date - return 0 
        if ($date === '0000-00-00' || $date === '0000-00-00 00:00:00') return 0;
        
        // If self::$_monthIdA is null - fetch key-value pairs
        if (self::$_monthIdA === null) self::$_monthIdA = db()->query('
            SELECT CONCAT(`y`.`title`, "-", `m`.`month`) AS `Ym`, `m`.`id`
            FROM `month` `m`, `year` `y`
            WHERE `m`.`yearId` = `y`.`id`
            ORDER BY `Ym`
        ')->pairs();

        // If self::$_monthYmA is null - setup it by flipping self::$_monthIdA
        if (self::$_monthYmA === null) self::$_monthYmA = array_flip(self::$_monthIdA);

        // If $date arg is 'all' - return all pairs we have
        if ($date === 'all') return self::$_monthIdA;

        // If $date arg is NOT given - assume current date
        if ($date === null) $date = date('Y-m');

        // If $date arg is falsy - return false
        if (!$date) return false;

        // Get time to base on
        $time = strtotime($date);

        // If $date arg is not recognizable by php's strtotime() - return false
        if ($time === false) return false;

        // Get yyyy-mm value
        $monthYm = date('Y-m', $time);

        // If there is no such month so far
        if (!(self::$_monthIdA[$monthYm] ?? null)) {

            // Create new month-record and get it's id
            $monthId = self::o($monthYm)->id;

            // Update pairs
            self::$_monthIdA[$monthYm] = $monthId;

            // Make sure new month to appear inside $_monthIdA according to it's chronology
            ksort(self::$_monthIdA);

            // Update $_monthYmA
            self::$_monthYmA = array_flip(self::$_monthIdA);
        }

        // Return id of corresponding `month` entry, that $date belongs to
        return self::$_monthIdA[$monthYm];
    }

    /**
     * Get `yyyy-mm` expr of `month` entry having `id` same as $monthId arg
     * If $monthId arg is null or not given - current month assumed
     * If monthId arg is 'all' - all [monthId => yyyy-mm] pairs are returned
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
        ')->pairs();

        // If self::$_monthIdA is null - setup it by flipping self::$_monthYmA
        if (self::$_monthIdA === null) self::$_monthIdA = array_flip(self::$_monthYmA);

        // If $monthId arg is 'all' - return all pairs we have
        if ($monthId === 'all') return self::$_monthYmA;

        // If $yearId arg is NOT given - assume current date
        if ($monthId === null) $monthId = monthId();

        // If $monthId arg is falsy - return false
        if (!$monthId) return false;

        // Return month in 'yyyy-mm' format, e.g. the same as date('Y-m') returns, or return false
        return self::$_monthYmA[$monthId] ?? false;
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

    /**
     * This method will be called after foreign-key field is created somewhere
     * pointing to this entity
     *
     * @param Field_Row $field
     */
    public function onAddedAsForeignKey(Field_Row $field) {

        // Make that month-options are grouped by year
        param($field->entityId, $field->alias, 'groupBy', $this->fields('yearId')->id);

        // Make that year is not shown in the option text itself
        param($field->entityId, $field->alias, 'optionTemplate', '<?=$o->foreign(\'month\')->title?>');
    }
}