<?php
class Notice_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Entry, that triggered the notice.
     * Used for building notification message's body, in NoticeGetter_Row::_notify()
     *
     * @var Indi_Db_Table_Row
     */
    public $row = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = array()) {

        // Explicitly set table name
        $config['table'] = 'notice';

        // Call parent
        parent::__construct($config);
    }

    /**
     * Sync nested `noticeGetter` entries with keys, mentioned in `profileId` field
     *
     * @return int
     */
    public function onSave() {
        
        // Sync keys, mentioned as comma-separated values in `profileId` prop, with entries, nested in `noticeGetter` table
        $this->keys2nested('profileId', 'noticeGetter');
    }

    /**
     * Trigger the notice
     *
     * @param Indi_Db_Table_Row $row
     * @param int $diff
     */
    public function trigger(Indi_Db_Table_Row $row, $diff) {

        // Assign `row` prop, that will be visible in compiling context
        $this->row = $row;

        // Foreach getter, defined for current notice
        foreach ($this->nested('noticeGetter') as $noticeGetterR) {

            // Directly setup foreign data for `noticeId` key, to prevent it
            // from being pulled, as there is no need to do that
            $noticeGetterR->foreign('noticeId', $this);

            // Notify
            $noticeGetterR->notify($row, $diff);
        }
    }
}