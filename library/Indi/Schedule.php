<?php
class Indi_Schedule {

    /**
     * Array of key-value pairs fetched from `time` table
     * using `title` as keys and `id` as values
     *
     * @var array
     */
    protected static $_timeIdA = null;

    /**
     * Array of key-value pairs fetched from `time` table
     * using `id` as keys and `title` as values
     *
     * @var array
     */
    protected static $_timeHiA = null;

    /**
     * Schedule's left bound (e.g. beginning)
     *
     * @var int
     */
    protected $_since;

    /**
     * Schedule's right bound (e.g. ending)
     *
     * @var int
     */
    protected $_until;

    /**
     * Params
     *
     * @var array
     */
    protected $_shift = [

        // This param is used within $this->bounds() method as a number of seconds within 24 hours
        // Also, it's used in $this->until() method, for deducting from actual value of $this->_until,
        // because $this->until() method is for formatting a schedule's right bound's date, but actual
        // timestamp containing in $this->_until points to next date
        'system' => 86400,

        // Gap between busy spaces
        'gap' => 0,

        // This param is used in cases when our aim is to check the possibility of new busy space to be injected,
        // starting at most possible late date (e.g schedule's right bound's date), so this param will be used for
        // expanding schedule's right bound. Example: we have schedule covering a certain week starting at monday's
        // midnight and ending at next monday's midnight. So, if we need to check availability of 6 hours, starting
        // at sunday's 11PM, we'll not be able to check it, as our schedule ends in 1 hour so we have no info about
        // remaining 5 hours. That is why we need to expand schedule's right bound by 6 hours, and it will lead to
        // that WHERE clause, built for fetching events (converted into busy spaces) from db - will cover as more wider
        // time-range as we need to have the info.
        'frame' => 0
    ];

    /**
     * Indexes, used for omitting unneeded iterations performed on $this->_spaces array.
     * Note: both indexes are set to zero at the beginning of $this->refill() call
     *
     * @var array
     */
    protected $_seek = [

        // This index is used as a start point for walking through $this->_spaces' items in $this->busy() method
        'from' => 0,

        // This index is being auto-updated automatically while walking through $this->_spaces' items in $this->busy()
        // method, and contains the index of the last iterated item
        'last' => 0
    ];

    /**
     * Iterations counter
     *
     * @var bool
     */
    protected $_counter = [
        'state' => false,
        'value' => 0
    ];

    /**
     * Spaces counter
     *
     * @var int
     */
    protected $_total = 0;

    /**
     * Spaces, that schedule is consists of
     *
     * @var array
     */
    protected $_spaces = [];

    /**
     * Backed up $this->_spaces and $this->total
     *
     * @var array
     */
    protected $_backup = [
        'spaces' => [],
        'total' => 0
    ];

    /**
     * Daily working hours timestamps
     *
     * @var array
     */
    protected $_daily = [
        'since' => 0,
        'until' => 0
    ];

    /**
     * Array of distinct values of props, faced within $this->_rs.
     * You should use $this->distinct(['roomId', 'teacherId']) call for
     * walking through $this->_rs and collecting distinct values,
     * For obtaining already collected values of certain prop - use $this->distinct($prop)
     *
     * @var array
     */
    public $_distinct = [];

    /**
     * Rowset, that will be used for refilling the schedule.
     * You should use $this->preload() call for initial setup,
     * and $this->refill() calls for refilling the schedule using
     * entries that match selection criteria, specified by $this->refill()
     * call args. This is useful for cases when you do not want execute
     * MySQL query each time you want to fulfil the schedule
     *
     * @var Indi_Db_Table_Rowset
     */
    public $_rs = null;

    /**
     * Array of all dates involved in current schedule
     *
     * @var null|array
     */
    public $dates = null;

    /**
     * Constructor. Possible usage:
     * 1. new Indi_Schedule(int|string $since, int|string $until), both arguments should be unixtimes
     *    or dates in format, recognizable with strtotime()
     *    This will create schedule instance having exact specified bounds
     * 2. new Indi_Schedule(string $wrap, int|string|null $date),
     *    - $wrap arg can be either 'month' or 'week'
     *    - $date arg can be either unixtime or date in format, recognizable with strtotime()
     *    if $wrap arg is 'week', newly created instance will have left bound as a monday of a week,
     *      that date specified by $date arg is within. If not specified, current date will be used
     *    if $wrap arg is 'month', newly created instance will have left bound as first date of a month,
     *      that date specified by $date arg is within. If not specified, current date will be used
     * 3.new Indi_Schedule(int|string $since, string $append)
     *    - $append arg can be a strtotime-compatible expression like '+1 week', or '+2 days'
     *   new Indi_Schedule(time(), '+2 months') will create schedule starting now and ending after 2 months
     *
     * @param $since
     * @param $until
     * @param $gap
     */
    public function __construct($since = 'month', $until = null, $gap = false) {

        // If $since arg is either 'week' or 'month'
        if (in($since, 'day,week,month')) extract($this->bounds($since, $until));

        // Set `_since`
        $this->_since = is_numeric($since) ? $since : strtotime($since);

        // If $until arg contains strtotime()-compatible expression, such as '+1 week' etc
        // Set `_until` by appending the interval, specified by $until arg, to `_since`
        if (preg_match('/^\+[0-9]+ (week|month|day|hour|second)s?/', $until))
            $this->_until = strtotime(date('Y-m-d H:i:s', $this->_since) . ' ' . $until);

        // Else use exact value as a schedule's right bound
        else $this->_until = is_numeric($until) ? $until : strtotime($until);

        // Create free space, that match whole schedule, initially
        $this->_spaces[] = new Indi_Schedule_Space($this->_since, $this->_until, 'free');

        // Update spaces counter
        $this->_total = 1;

        // Set gap
        if ($gap) $this->_shift['gap'] = _2sec($gap);
    }

    /**
     * Create busy space within the current schedule, or check whether it's possible to create it
     *
     * @param int $since Unix-timestamp or a string representation, recognizable by strtotime()
     * @param int $duration
     * @param bool $checkOnly
     * @param bool $includeGap
     * @param string $avail
     * @return bool
     */
    public function busy($since, $duration = null, $checkOnly = false, $includeGap = true, $avail = 'busy') {

        // If $since arg in an instance of Indi_Db_Table_Row
        if ($since instanceof Indi_Db_Table_Row) {

            // Back it up
            $entry = clone $since;

            // Overwrite $since and $duration args with $row's `spaceSince` and `spaceFrame` props respectively
            $since = $entry->spaceSince; $duration = $entry->spaceFrame;
        }

        // Default value for $isBusy flag
        $isBusy = true; $entry ??= null; $enough = $found = false;

        // Convert $since arg into timestamp
        $since = is_numeric($since) ? $since : strtotime($since);

        // Include gap
        if ($includeGap) $duration += $this->_shift['gap'];

        // If part, that we want to mark as busy - is starting earlier schedule's left bound
        if ($since < $this->_since) {

            // Deduct duration
            $duration -= $this->_since - $since;

            // Shift $since right, for it to match schedule left bound
            $since = $this->_since;
        }

        // If part, that we want to mark as busy - is ending later schedule's right bound
        if ($since + $duration > $this->_until) {

            // Deduct duration (so, part's right bound will be shifted left and will match schedule's right bound)
            $duration = $this->_until - $since;
        }

        // Foreach spaces within datetime-schedule
        for ($i = $this->_seek['from']; $i < $this->_total; $i++) {

            // Get current space
            $space = &$this->_spaces[$i];

            // If counter is turned On - count iterations
            if ($this->_counter['state']) $this->_counter['value'] ++;

            // Update index of last iterated space
            $this->_seek['last'] = $i;

            // If $since is within current $space - set $found flag as `true`
            if ($space->since <= $since && $space->until > $since) $found = true;

            // If $found flag is not yet set to `true` - keep searching (try next space)
            if (!$found) continue;

            // If space's frame size is enough for desired $duration - set $enough flag to `true`
            if ($space->since <= $since && $space->until >= $since + $duration) $enough = true;

            // Pick `avail` prop
            if ($isBusy && $enough) $isBusy = $space;

            // Setup $existing flag indicating that we're trying to load existing event into
            // current schedule, but event's start not within daily working hours
            $existing = $entry && $space->avail != 'busy'; // != 'busy' mean is 'early' or 'late'

            // If found $space has enough size for the desired new busy space
            if ($enough) {

                // If found $space is free, or, at least, non-busy
                if ($space->avail == 'free' || $existing) {

                    // Setup $isBusy flag
                    $isBusy = false;

                    // If we needed only to check schedule availability - break the loop
                    if ($checkOnly) break;

                    // Inject new busy space over found $space
                    $this->_busy($space, $i, $since, $duration, $avail, $entry);
                }

            // Else if found $space does not have enough size, but we're trying
            // to create busy space, that will represent an already existing event entry
            } else if ($existing) {

                // Detect whether there are enough subsequent quantity of non-busy spaces having enough summarily duration
                $chunk = []; $chunkSum = 0;
                for ($j = $i; $j < $this->_total; $j++) {
                    if ($this->_spaces[$j]->avail == 'busy') break;
                    $chunk[$j]['since'] = max($this->_spaces[$j]->since, $since);
                    $chunk[$j]['frame'] = min($since + $duration, $this->_spaces[$j]->until) - $chunk[$j]['since'];
                    $chunk[$j]['space'] = $this->_spaces[$j];
                    $chunkSum += $chunk[$j]['frame'];
                    if ($chunkSum >= $duration) break;
                }
                
                // If summary duration - is less than required duration - break
                if ($chunkSum < $duration) break;

                // Setup chunk-spaces
                $offset = 0;
                foreach ($chunk as $j => $info) $offset += 
                    $this->_busy($info['space'], $j + $offset, $info['since'], $info['frame'], $avail, $entry, true);

                // Detect chunk-spaces indexes range
                $range['since'] = $range['until'] = false;
                for ($m = $i; $m < $this->_total; $m++) {
                    if ($range['since'] === false &&  $this->_spaces[$m]->chunk) $range['since'] = $m;
                    if ($range['since'] !== false &&  $this->_spaces[$m]->chunk) $range['until'] = $m;
                    if ($range['since'] !== false && !$this->_spaces[$m]->chunk) break;
                }

                // Merge chunk-spaces into one, having `since` as first
                // chunk-space's `since` and `until` as last chunk-space's `until`
                $this->_spaces[$range['since']]->until = $this->_spaces[$range['until']]->until;
                array_splice($this->_spaces, $range['since'] + 1, $range['until'] - $range['since']);
                $this->_spaces[$range['since']]->chunk = false;

                // Setup $isBusy flag
                $isBusy = false;
            }

            // If $found flag is `true` - break
            if ($found) break;
        }

        // Return
        return $isBusy;
    }

    /**
     * Internal function for space updating and/or injection
     *
     * @param $space
     * @param $i
     * @param $since
     * @param $duration
     * @param $avail
     * @param $entry
     * @param bool $chunk
     * @return int
     */
    protected function _busy(Indi_Schedule_Space $space, $i, $since, $duration, $avail, $entry, $chunk = false) {

        // Array for spaces to be injected $this->_spaces
        $inject = [];

        // If free space starts earlier than busy space we want to inject
        if ($space->since < $since) {

            // Prepare busy-space for injection
            array_push($inject, $busy = new Indi_Schedule_Space($since, $since + $duration, $avail, $entry ?: null, $chunk));

            // If free schedule will remain after busy-part injection
            if ($space->until > $since + $duration)

                // Wrap that free schedule into free-space, and append to 2be-injected parts
                array_push($inject, $free = new Indi_Schedule_Space($since + $duration, $space->until, $space->avail));

            // Move current free part's ending mark
            $space->until = $since;

            // Inject
            array_splice($this->_spaces, $i + 1, 0, $inject);

            // Increase total spaces counter
            $this->_total += count($inject);

        // Else if found free space starts at the exact same moment as busy part we want to inject
        } else if ($space->since == $since) {

            // If free space ends later than busy part
            if ($space->until > $since + $duration) {

                // Prepare array (to be injected into $this->_spaces), containing busy
                // space starting at the exact same moment as current free space
                array_push($inject, $busy = new Indi_Schedule_Space($since, $since + $duration, $avail, $entry ?: null, $chunk));

                // Move current free space's starting mark to the ending of busy space
                $space->since = $since + $duration;

                // Inject busy part BEFORE current free part
                array_splice($this->_spaces, $i, 0, $inject);

                // Increase total spaces counter
                $this->_total ++;

            // Else if current free space is going to became busy in it's whole bounds
            } else if ($space->until == $since + $duration) {

                // Change it's 'avail' prop to $avail
                $space->avail = $avail;

                // Set whether space is a chunk
                $space->chunk = $chunk;

                // Set entry
                if ($entry) $space->entry = $entry;
            }
        }

        // Return quantity of injected spaces
        return count($inject);
    }

    /**
     * Detect calendar bounds for a given date
     *
     * @static
     * @param string $wrap
     * @param $date
     * @return array
     */
    public function bounds($wrap = 'month', $date = null) {

        // If $date arg is not given - use current date by default
        if (!$date) $date = date('Y-m-d H:i:s');

        // Config for different wrap kinds
        $wrapCfg = [
            'month' => ['format' => 'Y-m-01', 'days' => 7 * 6],
            'week'  => ['format' => 'Y-m-d',  'days' => 7 * 1],
            'day'   => ['format' => 'Y-m-d',  'days' => 1]
        ];

        // Get unix-timestamp
        $ts = strtotime(date($wrapCfg[$wrap]['format'], is_numeric($date) ? $date : strtotime($date)));

        // Get date, that is a monday in the same week as $ts
        $since = date('Y-m-d', $ts - ($wrap == 'day' ? 0 : date('N', $ts) - 1) * $this->_shift['system']);

        // Get bottom right date of pseudo-calendar
        $until = date('Y-m-d', strtotime($since . '+' . $wrapCfg[$wrap]['days'] . ' days'));

        // Return
        return compact('since', 'until');
    }

    /**
     * Return schedule's left bound as a unix-timestamp, or as a date, formatted according to $format arg
     *
     * @param null $format
     * @param bool $ldate
     * @return int|string
     */
    public function since($format = null, $ldate = false) {

        // If $format arg is not given - return `_since` prop
        if (!$format) return $this->_since;

        // If $ldate arg is given - return localized date
        if ($ldate) return ldate($format, $this->_since, $ldate);

        // Else return date, formatted without locale
        return strtolower(date($format, $this->_since));
    }

    /**
     * Return schedule's left bound as a unix-timestamp, or as a date, formatted according to $format arg
     * Note that if any of args is given, function will assume that the aim of the call is to retrieve schedule's
     * right bound in some kind of a human-readable format, so, 86400 seconds will be deducted before returning
     * formatted right bound, as right bound without such a deduction is used for internal purposes only
     *
     * @param null $format
     * @param bool $ldate
     * @return int|string
     */
    public function until($format = null, $ldate = false) {

        // If $format arg is not given - return `_since` prop
        if (!$format) return $this->_until;

        // If $ldate arg is given - return localized date
        if ($ldate) return ldate($format, $this->_until - $this->_shift['system'], $ldate);

        // Else return date, formatted without locale
        return strtolower(date($format, $this->_until - $this->_shift['system']));
    }

    /**
     * Load existing busy spaces into schedule
     *
     * @param $table
     * @param array $where
     * @param callable $pre
     * @return Indi_Schedule
     */
    public function load($table, $where = [], $pre = null) {

        // Get rowset
        $rs = $this->rowset($table, $where, $pre);

        // Load existing busy spaces into schedule
        foreach ($rs as $r)
            if ($this->busy($r))
                jflush(false, __(I_PLAN_ITEM_LOAD_FAIL, m($table)->title(), $r->id));

        // Return schedule itself
        return $this;
    }

    /**
     * Preload db entries, for later selection and conversion to busy spaces
     *
     * @param $table
     * @param array $where
     * @param null $pre
     * @return Indi_Schedule
     */
    public function preload($table, $where = [], $pre = null) {

        // Get rowset
        $this->_rs = $this->rowset($table, $where, $pre);

        // Return schedule itself
        return $this;
    }

    /**
     * Get rowset of entries, that current schedule is in intersection with
     */
    public function rowset($table, $where = [], $pre = null) {

        // Get model
        $model = m($table);

        // Normalize $where arg
        if (is_array($where)) $where = un($where, [null, '']);
        else if (is_string($where) && strlen($where)) $where = [$where];
        else $where = [];

        // Convert `_since` and `_until` timestamps into datetime format
        $since = date('Y-m-d H:i:s', $this->_since);
        $until = date('Y-m-d H:i:s', $this->_until);

        // Append WHERE clause part, responsible for fetching entries
        // that are within schedule bounds (fully or partially)
        $where[] = self::where($since, $until);

        // Get schedule's busy spaces
        $rs = $model->all($where, '`spaceSince` ASC');

        // If $pre arg is callable - call it, passing row, and schedule-related fields
        if (is_callable($pre)) foreach ($rs as $r) $pre($r);

        // Return rowset
        return $rs;
    }

    /**
     * Method for building WHERE clause, needed for fetching events that
     * are fully/partially within a schedule bounds
     *
     * @static
     * @param $since
     * @param $until
     * @return string
     */
    public static function where($since, $until) {
        return '(' . im([
            '(`spaceSince` <= "' . $since . '" AND `spaceUntil` >  "' . $since . '")',
            '(`spaceSince` <  "' . $until . '" AND `spaceUntil` >= "' . $until . '")',
            '(`spaceSince` >= "' . $since . '" AND `spaceUntil` <= "' . $until . '")'], ' OR ') . ')';
    }

    /**
     * Get spaces
     *
     * @return array
     */
    public function spaces() {
        return $this->_spaces;
    }

    /**
     * For EACH day within schedule, create spaces starting at midnight and ending at time,
     * specified by $until arg, and set availability of those spaces as 'early', or, if such
     * exact spaces is not possible to create because of already existing non-free spaces, that
     * would intersect with the desired spaces - create spaces among existing non-free spaces,
     * so the result is that the given period of time (within ech day) will be anyway be non-free,
     * as some parts of that period will be used for already existing non-free spaces, and remaining
     * parts will be filled with 'early' spaces
     *
     * @param bool $until
     * @return Indi_Schedule
     */
    public function early($until = false) {

        // If no $since arg given, or it is `false` - return
        if (!$until) return $this;

        // Ensure $since arg to be in 'hh:mm:ss' format
        if (!Indi::rexm('time', $until)) jflush(false, __(I_FORMAT_HIS, $until, '$until'));

        // Fill schedule with 'early' spaces, where possible
        $this->fillwp(0, $this->_daily['since'] = _2sec($until), 'early');

        // Return itself
        return $this;
    }

    /**
     * For EACH day within schedule, create spaces starting at time, according to given $since arg
     * and ending at midnight, and set availability of those spaces as 'late', or, if such
     * exact spaces is not possible to create because of already existing non-free spaces, that
     * would intersect with the desired spaces - create spaces among existing non-free spaces,
     * so the result is that the given period of time (within ech day) will be anyway be non-free,
     * as some parts of that period will be used for already existing non-free spaces, and remaining
     * parts will be filled with 'late' spaces
     *
     * @param bool $since
     * @return Indi_Schedule
     */
    public function late($since = false) {

        // If no $since arg given, or it is `false` - return
        if (!$since) return $this;

        // Ensure $since arg to be in 'hh:mm:ss' format
        if (!Indi::rexm('time', $since)) jflush(false, __(I_FORMAT_HIS, $since, '$since'));

        // Fill schedule with 'late' spaces, where possible
        $this->fillwp($this->_daily['until'] = _2sec($since) + $this->_shift['gap'], _2sec('1d'), 'late');

        // Return itself
        return $this;
    }

    /**
     * For EACH day within schedule, fill (where possible) the period
     * between $since and $until with spaces marked as $avail
     *
     * @param $since
     * @param $until
     * @param $avail
     */
    public function fillwp($since, $until, $avail) {

        // Set initial day-timestamp to be the date of the schedule left bound
        $daystamp = strtotime(date('Y-m-d', $this->_since));

        // Space index
        $idx = 0;

        // While $mark does not exceed schedule's right bound
        while ($daystamp < $this->_until) {

            // Get date for
            $next = $daystamp + $this->_shift['system'];

            // Set absolute timestamps
            $_since = $daystamp + $since;
            $_until = $daystamp + $until;

            // Foreach space within schedule, starting with $idx
            for (; $idx < $this->_total; $idx++) {

                // Get space
                $space = $this->_spaces[$idx];

                // If current space's start date is one of the next days to $_since - break;
                if ($space->since > $next) break;

                // If current space ends earlier than $_since - skip
                if ($space->until <= $_since) continue;

                // If current space is not 'free'
                if ($space->avail != 'free') continue;

                // Calculate the intersection of what we have and what we need
                $frame = min($space->until, $_until) - max($space->since, $_since);

                // If no current space's part, suitable for marking as 'late' found - break
                if ($frame <= 0) break;

                // Set start seeking point
                $this->_seek['from'] = $idx;

                // Mark found part according to $avail arg
                if ($this->busy(max($space->since, $_since), $frame, false, false, $avail))
                    jflush(false, 'Can\'t set ' . $avail . ' space');
            }

            // Jump to next day
            $daystamp += $this->_shift['system'];
        }

        // Reset indexes
        $this->_seek['from'] = $this->_seek['last'] = 0;
    }

    /**
     * Set daily spaces that are not between $since and $until - as not available.
     * This can be useful when there is a need to setup working hours
     *
     * @param bool|string $opened
     * @param bool|string $closed
     * @return Indi_Schedule
     */
    public function daily($opened = false, $closed = false) {
        return $this->early($opened)->late($closed);
    }

    /**
     * Get array of dates within current schedule, where given $frame can't be injected as a NEW busy space.
     *
     * @param string $frame Amount of time. Possible values: '10:23:50', '1h', '30m', etc
     * @param bool|array $both If is an array - it will be fufilled with dates having at least one busy and one free spaces
     * @param bool|array $hours Explicit hours of availability, that certain date should be checked against,
     *                          to detect whether that certain date is busy
     * @return array
     */
    public function busyDates($frame, &$both = false, $hours = false) {

        // Convert to seconds
        $frame = _2sec($frame);

        // Array of busy dates
        $busy = [];

        // Set initial mark to be same as schedule's left bound
        $mark = $this->_since;

        // Set $stop flag, indicating whether we'll stop checking $mark's date once desired free space was found there.
        // If $stop is `false` - we'll use $both arg as an array for fulfilling it with dates, having both free and busy
        // spaces(s), e.g. having not only at least one free space suitable for desired frame injection, but also
        // at least one busy space, so there will be a collection of dates that are partially busy
        $stop = $both === false; if (!$stop) $both = [];

        // Set index of space, that we should start searching from within each $mark's date
        $idx = 0;

        // While $mark does not exceed schedule's right bound
        while ($mark < $this->_until - $this->_shift['frame']) {

            // Foreach parts within datetime-space - find the space part, that mark is belonging to
            for ($i = $idx; $i < $this->_total; $i++) {
                if ($mark >= $this->_spaces[$i]->since && $mark < $this->_spaces[$i]->until) {
                    $space = $this->_spaces[$idx = $i];
                    break;
                }
            }

            // Set/reset $hasFree and $hasBusy flags
            $gotFree = $gotBusy = false;

            // Shortcut to $mark's date
            $date = date('Y-m-d', $mark);

            // Reset $timeA
            $timeA = [];

            // If $timeA arg is not `false`, and $timeA['idsFn'] is callable
            if ($hours && is_callable($hours['idsFn'])) {

                // Get `time` entries ids by calling $timeA['idsFn'], passing $date amonth other
                // arguments, so ids-fn can return `time` entries ids depending on certain date, if need
                $info = $hours['idsFn']($hours['owner'], $hours['event'], $date);

                // If idsFn call returned false set $timeA to be false
                if ($info === false) $timeA = false;

                // Else set $timeA to be an array of actual hours in 'H:i' format, got by `time` ids
                else foreach (ar($info['time']) as $timeId) $timeA[] = timeHi($timeId);

            // Else use as is
            } else $timeA = isset($hours['idsFn']['time']) ? ar($hours['idsFn']['time']) : false;

            // Do
            do {

                // Setup the start point of a frame, that we will check whether it's busy
                // Here we use max() fn, as $space may start at earlier date than $mark's date
                // and in that case usage of $space->since as a start point will produce incorrect results,
                // because our aim is to check whether it's possible to inject new busy space starting
                // at $mark's date, not starting yesterday or at earlier date
                $since = max($space->since, $mark);

                // If $mark belongs to 'free' space
                if ($space->avail == 'free') {

                    // Set start seeking point
                    $this->_seek['from'] = $idx;

                    // Here we handle situation when date availability should be checked
                    // using specific hours only. So, certain date will be considered as non-busy
                    // only in case if it is possible to create a new busy space at, for example
                    // 10:30 or 15:00 or 18:30 only. This can be useful in situations when some space
                    // owner, for example - teacher - has it's own set of hours of availability
                    if (is_array($timeA) && !($etime = false)) foreach ($timeA as $time)
                        if ($since <= ($_etime = $mark + _2sec($time . ':00')))
                            if ($etime = $since = $_etime)
                                break;

                    // If given $frame CAN be injected as NEW busy space - set $gotFree flag as `true`
                    if (($timeA === false || $etime) && !$this->busy($since, $frame, true)) $gotFree = true;

                // Else if space's `avail` is 'busy' - set $gotBusy flag
                } else if ($space->avail == 'busy') $gotBusy = true;

                // Jump to next space
                $space = $this->_spaces[++$i];

            // While  $space's date is same as $mark's date
            } while ((!$gotFree || !$stop) && date('Y-m-d', $space->since) == $date);

            // If $free flag is still false - append $mark's date to busy dates array
            if (!$gotFree) $busy[] = $date;

            // If partially busy dates should be collected, and current $date is such a date - collect
            if (!$stop && $gotFree && $gotBusy) $both[] = $date;

            // Jump to next date
            $mark += $this->_shift['system'];
        }

        // Reset indexes
        $this->_seek['from'] = $this->_seek['last'] = 0;

        // Return busy dates
        return $busy;
    }

    /**
     * Get array of hours within a given date in current space, where given $frame can't be injected as a NEW busy space
     *
     * @param $date
     * @param string $step
     * @param bool $seekFromLast
     * @param bool|array $hours Explicit hours of availability, that certain date should be checked against,
     *                          to detect whether that certain date is busy
     * @return array
     */
    public function busyHours($date, $step = '1h', $seekFromLast = false, $hours = false) {

        // Array of busy hours
        $busy = [];

        // Get timestamp
        $time = strtotime($date);

        // Set initial mark
        $mark = $time + $this->_daily['since'];

        // Set late point
        $late = $time + ($this->_daily['until'] ?: $this->_shift['system']);

        // Convert $step arg to seconds
        $step = _2sec($step);

        // If $timeA arg is not `false`, and $timeA['idsFn'] is callable
        if (is_callable($hours['idsFn'] ?? 0)) {

            // Get `time` entries ids by calling $timeA['idsFn'], passing $date amonth other
            // arguments, so ids-fn can return `time` entries ids depending on certain date, if need
            $info = $hours['idsFn']($hours['owner'], $hours['event'], $date);

            // If idsFn call returned false set $timeA to be false
            if ($info === false) $timeA = false;

            // Else set $timeA to be an array of actual hours in 'H:i' format, got by `time` ids
            else foreach (ar($info['time']) as $timeId) $timeA[] = timeHi($timeId);

        // Else use as is
        } else $timeA = isset($hours['idsFn']['time']) ? ar($hours['idsFn']['time']) : false;

        // If $timeA is array
        if (is_array($timeA)) {

            // Flip it
            $timeA = array_flip($timeA);

            // Set $_hours flag to `true`
            $_hours = true;
        }

        // While $mark is within $date
        while ($mark < $late) {

            // If $seekFromLast flag is given as `true` - setup start index as last index
            if ($seekFromLast) $this->_seek['from'] = $this->_seek['last'];

            // Get time
            $Hi = date('H:i', $mark);

            // If given $frame can't be injected as NEW busy space - append $mark's date in busy time-steps array
            if ((($_hours ?? 0) && !isset($timeA[$Hi])) || $this->busy($mark, $this->_shift['frame'], true)) $busy[] = $Hi;

            // Jump to next time-step
            $mark += $step;
        }

        // Return busy time-steps
        return $busy;
    }

    /**
     * Convert schedule to a number of strings, each representing certain space
     *
     * @return string
     */
    public function __toString() {

        // Setup pad length
        $l = strlen('' . ($this->_total - 1)); $o = '';

        // Collect stringified spaces
        foreach ($this->_spaces as $i => $s) $o .= str_pad($i, $l, '0', STR_PAD_LEFT) . ' - ' . $s . "\n";

        // Return stringified schedule
        return $o;
    }

    /**
     * Shift schedule's right bound.
     * Use this method BEFORE $this->preload() and $this->load() calls,
     * as schedule's right bound is involved in building WHERE clause
     * for fetching event-entries from database
     */
    public function frame($frame) {

        // Revert previous shift
        $this->_until -= $this->_shift['frame'];

        // Setup new value for $this->_shift['frame'] prop
        $this->_shift['frame'] = is_string($frame) ? _2sec($frame) : $frame;

        // Apply new shift
        $this->_until += $this->_shift['frame'];

        // Apply new shift to schedule's single space.
        $this->_spaces[0]->until = $this->_until;
    }

    /**
     * Empty the schedule and fill it with another collection of spaces,
     * based on entries, selected from $this->_rs according to criteria,
     * given by $keys and $type args
     *
     * Example usages:
     *   by prop name and value: $schedule->refill('1', 'teacherId', ['since' => '10:00:00', 'until' => '20:00:00']);
     *   by $this->_rs indexes : $schedule->refill([0,1,2], null, ['since' => '10:00:00', 'until' => '20:00:00']);
     *   using backup          : $schedule->refill([0,1,2], null, null);
     *
     * @param $keys
     * @param string $type
     * @param array $daily
     * @param callable $pre
     * @return Indi_Schedule
     */
    public function refill($keys, $type = 'id', $daily = [], $pre = null) {

        // If $type arg is null - restore spaces from backup (if it was previously created), else
        if ($daily === null && $this->_backup['spaces']) $this->restore(); else {

            // Drop all existing spaces and insert a new free one, matching schedule bounds
            $this->_spaces = [new Indi_Schedule_Space($this->_since, $this->_until, 'free')];

            // Reset spaces counter
            $this->_total = 1;
        }

        // Reset indexes
        $this->_seek['from'] = $this->_seek['last'] = 0;

        // Select certain entries from preloaded rowset and add them as busy spaces into schedule
        foreach ($type === null ? $keys : $this->_rs->select($keys, $type) as $_) {

            // Pick entry from rowset
            $r = $type === null ? clone $this->_rs[$_] : $_;

            // If $pre arg is callable - call it, passing row as a 1st arg
            if (is_callable($pre)) $pre($r);

            // Set start index
            $this->_seek['from'] = $this->_seek['last'];

            // Flush failure
            if ($this->busy($r)) jflush(false, __(I_PLAN_ITEM_LOAD_FAIL, m($this->_rs->table())->title(), $r->id));
        }

        // Setup daily hours
        if ($daily) $this->daily($daily['since'], $daily['until']);

        // Reset indexes
        $this->_seek['from'] = $this->_seek['last'] = 0;

        // Return schedule instance itself
        return $this;
    }

    /**
     * Collect distinct values per given props within preloaded rowset,
     * and collect indexes of entries having those values
     *
     * @param $prop
     * @param $self
     * @param $strict
     * @return array
     */
    public function distinct($prop = null, Indi_Db_Table_Row $self = null, $strict = false) {

        // If no args given - return $this->_distinct as is
        if (!func_num_args() || (!is_string($prop) && !is_array($prop))) return $this->_distinct;

        // If $prop arg is given and it's a string
        if (is_string($prop)) return $this->_distinct[$prop] ?? [];

        // Foreach prop that we need to collect distinct values for
        foreach ($prop as $propI => $ruleA) {

            // If self event's system 'purpose' param is 'drag', it means that user is
            // started dragging event from original date to some another date, so we need
            // to highlight disabled dates within calendar UI, that, in it's turn, means
            // that we do not need to detect disabled dates for ALL possible space owners,
            // but only for space owners, mentioned within dragged event's props
            if ($self->system('purpose') == 'drag') $_self[$propI] = array_flip(ar($self->$propI));

            // Foreach entry within preloaded rowset - collect distinct values
            foreach ($this->_rs as $idx => $r) if ($r->$propI)
                foreach (ar($r->$propI) as $v)
                    if ($self->system('purpose') != 'drag' || isset($_self[$propI][$v]))
                        $this->_distinct[$propI][$v]['idxA'][] = $idx;

            // If hours-rule no set - skip, else
            if (!($ruleA['hours'] ?? 0)) continue; else $spaceOwnerProp = $propI;

            // Also, consider values of event entry's prop
            if ($self->$propI)
                foreach (ar($self->$propI) as $v)
                    if (!($this->_distinct[$propI][$v] ?? 0))
                        $this->_distinct[$propI][$v]['idxA'] = [];

            // If $strict arg is false, this means that current distinct() call was NOT made within $self->validate() call,
            // and this, in it's turn, means that we're in the process of detecting disabled values for space fields, so
            // we need to find disabled values among ALL POSSIBLE values (rather than only values used by existing events):
            // 1 .Initially, the process of detection of disabled values was based only on those values actual usage, so, for
            // example, some teacher is unavailable at some date and time because he has a classroom scheduled at that exact
            // date and time.
            // 2. But the real life shows that existing classroom, scheduled at certain date/time - is not the only reason
            // of why teacher can be unavailable at that date/time. The second possible reason is that teacher's own settings,
            // that he had set up, and those settings assume that teacher is available only at, for example 11:00 at Mondays,
            // and 18:00 at Wednesdays
            if (!$strict && $self->system('purpose') != 'drag')
                foreach ($self->getComboData($propI)->column('id') as $v)
                    if (!($this->_distinct[$propI][$v] ?? 0))
                        $this->_distinct[$propI][$v]['idxA'] = [];

            // If no distinct values collected - skip
            if (!$vA = array_keys($this->_distinct[$spaceOwnerProp] ?: [])) continue;

            // Get model, that space owner prop relates to
            $spaceOwnerModelId = $this->_rs->model()->fields($spaceOwnerProp)->relation;

            // Shortcut for space owner model
            $m = m($spaceOwnerModelId);

            // Collect space owner distict entries and inject them into $this->_distinct array
            $rs = $m->preload() ? $m->preloadedAll($vA) : $m->all('`id` IN (' . im($vA) . ')');
            foreach ($rs as $spaceOwnerEntry)
                $this->_distinct[$spaceOwnerProp][$spaceOwnerEntry->id]['entry'] = $spaceOwnerEntry;
        }
    }

    /**
     * Toggle $this->_space iterations counter and get value
     *
     * @param null $toggle
     * @return mixed
     */
    public function counter($toggle = null) {

        // If $toggle arg is given and is bool - update flag indicating whether counter is turned On
        if (is_bool($toggle)) $this->_counter['state'] = $toggle;

        // Else return counter's current value
        else return $this->_counter['value'];
    }

    /**
     * Restore $this->_spaces and $this->_total from previously made backup.
     */
    public function restore() {

        // Reset $this->_spaces to an empty array
        $this->_spaces = [];

        // Foreach backed up instance of Indi_Schedule_Space - make a clone and add into $this->_spaces
        foreach ($this->_backup['spaces'] as $space) $this->_spaces[] = clone $space;

        // Restore $this->_total from backed up value
        $this->_total = $this->_backup['total'];
    }

    /**
     * Backup current values of $this->_spaces and $this->_total
     * This can be used for cases when you need to create separate schedules for many schedule owners,
     * but you do not want to re-setup, for example, 'early' and/or 'late' spaces created by
     * daily(), early() and late() methods calls as in most cases daily working hours are same
     * for each schedule owner. So, usage of this method allows to 'remember' spaces collection,
     * that is already containing 'early' and/or 'late' spaces, and you'll just need to call
     * refill() method with null $daily arg, so refilling schedule with 'busy' spaces will be
     * performed on schedule, that is already containing 'early' and/or 'late' spaces
     */
    public function backup() {
        $this->_backup['spaces'] = $this->_spaces;
        $this->_backup['total'] = $this->_total;
    }

    /**
     * Get `id` of `time` entry having `title` same as $Hi arg
     * Example timeId('10:00');
     *
     * @static
     */
    public static function timeId($Hi = null) {

        // If self::$_timeIdA is null - fetch key-value pairs
        if (self::$_timeIdA === null) self::$_timeIdA = db()->query('
            SELECT `title`, `id` FROM `time` WHERE `title` REGEXP ":(00|15|30|45)$" ORDER BY `title`
        ')->pairs();

        // If self::$_timeHiA is null - setup it by flipping self::$_timeIdA
        if (self::$_timeHiA === null) self::$_timeHiA = array_flip(self::$_timeIdA);

        // If t$Hi arg (time in 'H:i' format) is given - return id of corresponding `time` entry
        return $Hi ? self::$_timeIdA[func_get_arg(0)] : self::$_timeIdA;
    }

    /**
     * Get `title` of `time` entry having `id` same as $timeId arg
     * Example timeHi(123);
     *
     * @static
     */
    public static function timeHi($timeId = null) {

        // If self::$_timeHiA is null - fetch key-value pairs
        if (self::$_timeHiA === null) self::$_timeHiA = db()->query('
            SELECT `id`, `title` FROM `time` WHERE `title` REGEXP ":(00|15|30|45)$" ORDER BY `title`
        ')->pairs();

        // If self::$_timeIdA is null - setup it by flipping self::$_timeHiA
        if (self::$_timeIdA === null) self::$_timeIdA = array_flip(self::$_timeHiA);

        // If $timeId arg is given - return `title` of corresponding `time` entry
        return $timeId ? self::$_timeHiA[func_get_arg(0)] : self::$_timeHiA;
    }

    /**
     * Setup space-owner daily working hours, per each date, separately
     *
     * @param $hours
     */
    public function ownerDaily($hours) {

        // If $hours is false - return
        if ($hours === false) return;

        // Set initial day-timestamp to be the date of the schedule left bound
        $daystamp = strtotime(date('Y-m-d', $this->_since));

        // Space index
        $idx = 0;

        // While $mark does not exceed schedule's right bound
        while ($daystamp < $this->_until - $this->_shift['frame']) {

            // Get next date's timestamp
            $next = $daystamp + $this->_shift['system'];

            // Get current date
            $date = date('Y-m-d', $daystamp);

            // Reset $timeA
            $timeA = [];

            // If $timeA arg is not `false`, and $timeA['idsFn'] is callable
            if (is_callable($hours['idsFn'])) {

                // Get `time` entries ids by calling $timA['idsFn'], passing $date amonth other
                // arguments, so ids-fn can return `time` entries ids depending on certain date, if need
                $info = $hours['idsFn']($hours['owner'], $hours['event'], $date);

                // If idsFn call returned false set $timeA to be false
                if ($info === false) $timeA = false; else {

                    // Else set $timeA to be an array of actual hours in 'H:i' format, got by `time` ids
                    foreach (ar($info['time']) as $timeId) $timeA[] = timeHi($timeId);

                    // Get duration of how long space-owner is available at those times
                    $tspan = $info['span'];
                }

            // Else use as is
            } else {
                $timeA = ar($hours['idsFn']['time']);
                $tspan = $hours['idsFn']['span'];
            }

            // $timeA is array (even empty array)
            if (is_array($timeA)) {

                // Create separate schedule for current date
                $inverted = Indi::schedule($daystamp, $daystamp + $this->_shift['system']);

                // Setup space-owner working hours as 'busy' spaces within inverted schedule
                // We do that because all other spaces will be 'free' after that
                // and we'll use that 'free' spaces as 'busy' spaces within current schedule
                foreach ($timeA as $time) $inverted->busy($daystamp + _2sec($time . ':00'), $tspan);

                // Use 'free' spaces' of inverted day-schedule to create 'busy' spaces within current schedule
                foreach ($inverted->spaces() as $ispace) if ($ispace->avail == 'free') {

                    // Backup space index
                    $backup = $idx;

                    // Foreach space within schedule, starting with $idx
                    for ($idx = $backup; $idx < $this->_total; $idx++) {

                        // Get space
                        $space = $this->_spaces[$idx];

                        // If current space's start date is one of the next days to $_since - break;
                        if ($space->since > $next) break;

                        // If current space is not 'free'
                        if ($space->avail != 'free') continue;

                        // If current space ends earlier than $_since - skip
                        if ($space->until <= $ispace->since) continue;

                        // Shortcut to max value
                        $max = max($space->since, $ispace->since);

                        // Calculate the intersection of what we have and what we need
                        $frame = min($space->until, $ispace->until) - $max;

                        // If no current space's part (suitable for marking as 'rest') found - break
                        if ($frame <= 0) break;

                        // Set start seeking point
                        $this->_seek['from'] = $idx;

                        // Mark found part as 'rest'
                        if ($this->busy($max, $frame, false, false, 'rest'))
                            jflush(false, I_SCHED_UNABLE_SET_REST);
                    }
                }
            }

            // Jump to next day
            $daystamp += $this->_shift['system'];
        }

        // Reset indexes
        $this->_seek['from'] = $this->_seek['last'] = 0;
    }

    /**
     * Get all involved dates and collect within $this->dates
     */
    public function dates() {

        // Set initial day-timestamp to be the date of the schedule left bound
        $daystamp = strtotime(date('Y-m-d', $this->_since));

        // While $mark does not exceed schedule's right bound
        while ($daystamp < $this->_until - $this->_shift['frame']) {

            // Get current date
            $this->dates []= date('Y-m-d', $daystamp);

            // Jump to next day
            $daystamp += $this->_shift['system'];
        }
    }
}