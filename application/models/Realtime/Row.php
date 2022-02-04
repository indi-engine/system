<?php
class Realtime_Row extends Indi_Db_Table_Row {

    /**
     * @throws Exception
     */
    public function onBeforeSave() {

        // Set `title`
        $this->setTitle();
    }

    /**
     * Keep spaceUntil-prop updated
     */
    public function onBeforeUpdate() {

        // If entry of type = context is going to be updated
        if ($this->type == 'context' && $this->_modified)

            // Update it's spaceUntil-prop as well
            $this->set('spaceUntil', date('Y-m-d H:i:s'));
    }

    /**
     * Keep parent parent/channel record's spaceUntil-prop updated
     *
     * @throws Exception
     */
    public function onUpdate() {

        // If entry of type = context was really updated
        if ($this->type == 'context' && $this->_affected)

            // Update parent/channel record's spaceUntil-prop
            $this->foreign('realtimeId')->set('spaceUntil', date('Y-m-d H:i:s'))->basicUpdate();
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function setTitle() {

        // If `type` is 'section'
        if ($this->type == 'session') $this->title = $this->foreign('type')->title
            . ' - ' . $this->token . ', ' . $this->foreign('langId')->title;

        // Else if `type` is 'channel'
        else if ($this->type == 'channel') $this->title = $this->foreign('type')->title . ' - ' . $this->token;

        // Return
        return $this;
    }

    /**
     * Force `title` to be set on parent (channel) entry
     * Make sure parent/channel entry's spaceUntil is updated as well
     */
    public function onInsert() {
        if ($this->type == 'context' && $parent = $this->parent()) {
            if (!$parent->title) $parent->setTitle();
            $parent->set('spaceUntil', date('Y-m-d H:i:s'))->basicUpdate();
        }
    }

    /**
     * Delete parent `realtime` entry (session-entry) if this was the last remaining tab/channel
     */
    public function onDelete() {

        // If it's a channel-entry
        if ($this->type == 'channel') {

            // If no more channels left for that session - remove parent/session entry
            if (!$this->model()->row([
                '`type` = "channel"',
                '`realtimeId` = "' . $this->realtimeId . '"'
            ])) $this->foreign('realtimeId')->delete();

        // Else if it's a context-entry
        } else if ($this->type == 'context') {

            // Make sure parent/channel entry's spaceUntil prop is updated
            $this->foreign('realtimeId')->set('spaceUntil', date('Y-m-d H:i:s'))->basicUpdate();
        }
    }
}