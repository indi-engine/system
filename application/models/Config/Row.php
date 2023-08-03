<?php
class Config_Row extends Indi_Db_Table_Row {

    /**
     * Here we provide expiration check right after row instantiation,
     * to ensure that all row's further involvements will operate using
     * non-expired value of `currentValue` prop
     *
     * @param array $config
     */
    public function __construct(array $config = []) {

        // Call parent
        parent::__construct($config);

        // Check expiration
        $this->expiry();
    }

    /**
     * @return int
     */
    public function save(){

        // If we got 'constantly' as `expiryType`
        if ($this->expiryType == 'constantly') {

            // Set up `expiryDurationStr` as empty string
            $this->expiryDurationStr = '';

        // Else
        } else {

            // Set up wording versions
            $versionA = [
                'minute' => I_AGO_MINUTES,
                'hour'   => I_AGO_HOURS,
                'day'    => I_AGO_DAYS,
                'week'   => I_AGO_WEEKS,
                'month'  => I_AGO_MONTHS,
                'year'   => I_AGO_YEARS,
            ];

            // Set up `expiryDurationStr` according to `expiryMeasure` and it's duration
            $this->expiryDurationStr = tbq($this->expiryDuration, $versionA[$this->expiryMeasure]);
        }

        // Standard save
        return parent::save();
    }

    /**
     * Check if `currentValue` has expired, and if so - update it
     *
     * @return mixed
     */
    public function expiry() {

        // If current entry's expiry type is not 'temporary' (mean it is 'constantly') - return `currentValue` prop's value
        if (!($this->expiryType == 'temporary')) return $this->currentValue;

        // Get `expiryStart` as unix-timestmp
        $expiryStart_ts = strtotime($this->expiryStart);

        // Get `expiryDuration` as a php's strtotime()-compatible expression
        $expiryDuration_expr = '+' . $this->expiryDuration . ' ' . $this->expiryMeasure;

        // Get expiration datetime
        $expiryEndDt = date('Y-m-d H:i:s', strtotime($expiryDuration_expr, $expiryStart_ts));

        // If current datetime is greater than expiration datetime,
        // we assume that existing `currentValue` has expired, so we
        // should rollback it to `defaultValue`
        if (date('Y-m-d H:i:s') >= $expiryEndDt) {

            $this->currentValue = $this->defaultValue;
            $this->expiryStart = date('Y-m-d H:i:s');
            $this->expiryType = 'constantly';
            $this->save();
        }

        // Return
        return $this->currentValue;
    }
}