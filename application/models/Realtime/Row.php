<?php
class Realtime_Row extends Indi_Db_Table_Row {

    /**
     * Setter method for title-prop
     *
     * @return $this
     * @throws Exception
     */
    public function setTitle() {

        // If `type` is 'session'
        if ($this->type == 'session') $this->title = $this->foreign('type')->title
            . ' - ' . $this->token . ', ' . $this->foreign('langId')->title;

        // Else if `type` is 'channel'
        else if ($this->type == 'channel') $this->title = $this->foreign('type')->title . ' - ' . $this->token;

        // Return
        return $this;
    }

    /**
     * Set title- and spaceUntil-prop
     *
     * @throws Exception
     */
    public function onBeforeSave() {

        // Set `title`
        $this->setTitle();

        // Keep spaceUntil-prop updated
        $this->set('spaceUntil', date('Y-m-d H:i:s'));
    }

    /**
     * Update parent records, if any
     */
    public function onUpdate() {
        $this->updateParents();
    }

    /**
     * Update parent records, if any
     */
    public function onInsert() {
        $this->updateParents();
    }

    /**
     * Get ws-client channels which we should close/reload
     *
     * @throws Exception
     */
    public function onBeforeDelete() {
        $this->channelA = $this->nested('realtime')->col('token');
    }

    /**
     * Delete parent session-file if session-entry was deleted, so that corresponding admin will be required to re-login
     */
    public function onDelete() {

        // If it's a session-entry
        if ($this->type == 'session') {

            // If system unlink flag is explicitly set to false, it means
            // we're in attempt to login on behalf some other admin via *_Admin->loginAction()
            // and this mean we need to reload all other browser tabs which are currently opened
            // by that current admin who clicked 'Login on behalf...' button in one of those tabs
            if (($this->_system['unlink'] ?? null) === false) $type = 'F5'; else {

                // Get session files dir
                $session = ini_get('session.save_path') ?: sys_get_temp_dir();

                // Append filename
                $session .= '/sess_' . $this->token;

                // If session file exists - delete it
                if (is_file($session)) unlink($session);

                // Set type of websocket message to be sent to browser tabs/channels to be 'expired'
                // as the fact we're here means session is no longer available
                $type = 'expired';
            }

            // If there are client browser tabs/channels to be closed/reloaded
            if ($this->channelA)
                foreach ($this->channelA as $channel)
                    Indi::ws(['type' => $type, 'to' => $channel]);

        // Else if it was channel- or context- entry - update parents
        } else $this->updateParents();
    }

    /**
     * Keep spaceUntil-prop's value up-to-date for all parents of current entry
     * Also set title-prop for those of parent where it is so far empty
     *
     * @throws Exception
     */
    public function updateParents() {

        // If it's session-entry, e.g. it has no parent entries - do nothing
        if ($this->type == 'session') return;

        // Shortcuts
        $parent = $this; $ts = ['spaceUntil' => date('Y-m-d H:i:s')];

        // Update spaceUntil on parent entries
        while ($parent = $parent->parent()) {

            // If parent has no title yet - set it
            if (!$parent->title) $parent->setTitle();

            // Set spaceUntil timestamp
            $parent->set($ts)->basicUpdate();
        }
    }

    /**
     * Set `spaceSince` prop
     */
    public function onBeforeInsert() {

        // Set spaceSince to be current timestamp
        $this->spaceSince = date('Y-m-d H:i:s');
    }

    /**
     * Create `realtime` entry of `type` = "channel" nested under current entry of `type` = "session"
     */
    public function channel() {

        // This method is applicable for session-entries, due to that we're going to create channel-entry
        if ($this->type != 'session') return;

        // Generate channel token
        $token = grs(30);

        // Prepare data for `realtime` entry of `type` = "channel"
        $ctor = ['type' => 'channel', 'token' => $token, 'realtimeId' => $this->id];

        // Pick other props from parent entry of `type` = "session"
        $ctor += $this->toArray();

        // Unset 'id' and 'title'
        unset($ctor['id'], $ctor['title']);

        // Create and save `realtime` entry of `type` = "channel"
        $channel = $this->model()->new($ctor);
        $channel->save();

        // Return channel
        return $channel;
    }

    /**
     * Create rabbitmq-queue, and return it's name
     */
    public function queue() {

        // If it's not a channel-entry - return
        if ($this->type != 'channel') return;

        // Prepare rabbitmq-queue name
        $qn = qn('opentab--') . $this->token;

        // Create queue
        mq()->queue_declare($qn);

        // Return queue name
        return $qn;
    }

    /**
     * Detect id of next page's first record
     *
     * @param $table
     * @param $scope
     * @param $order
     * @return int
     */
    public function nextPage1st(string $table, array $scope, string $order) {

        // Setup a flag indicating whether we should transform sql query into CTE expression
        $treeify = ($scope['tree'] ?? false) && !db()->version('5');

        // Prepare WHERE clause
        $where = [];
        if ($scope['WHERE']) $where['scope']            = $scope['WHERE'];
        if ($this->entries)  $where['skipCurrPageRows'] = "`id` NOT IN ($this->entries)";
        $where = rif(join(' AND ', $where), 'WHERE $1');

        // Prepare OFFSET clause for current page's first record
        $offset = $scope['rowsOnPage'] * ($scope['page'] - 1);

        // Prepare sql-query to get next page's first record's ID
        $sql = "SELECT `id` FROM `$table` $where $order LIMIT $offset, 1";

        // If current model has a tree-column - modify sql to rely on CTE
        if ($treeify) $sql = db()->treeify($sql);

        // Run query and return ID
        return (int) db()->query($sql)->cell() ?? 0;
    }
}