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
     * Delete parent session-file if session-entry was deleted, so that corresponding admin will be required to re-login
     */
    public function onDelete() {

        // If it's a session-entry
        if ($this->type == 'session') {

            // If system unlink flag is NOT set to false
            if ($this->_system['unlink'] !== false) {

                // Get session files dir
                $session = ini_get('session.save_path') ?: sys_get_temp_dir();

                // Append filename
                $session .= '/sess_' . $this->token;

                // If session file exists - delete it
                if (is_file($session)) unlink($session);
            }

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
}