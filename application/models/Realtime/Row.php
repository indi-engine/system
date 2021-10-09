<?php
class Realtime_Row extends Indi_Db_Table_Row {

    public function onBeforeSave() {

        // Set `title`
        $this->setTitle();
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
     */
    public function onInsert() {
        if ($this->type == 'context')
            if ($parent = $this->parent())
                if (!$parent->title)
                    $parent->setTitle()->basicUpdate();
    }

    /**
     * Delete parent `realtime` entry (session-entry) if this was the last remaining tab/channel
     * For now it's disabled, as in case if there is only one tab, and it's reloaded - session is deleted
     */
    public function onDelete() {
        if ($this->type == 'channel')
            if (!$this->model()->fetchRow(['`type` = "channel"', '`realtimeId` = "' . $this->realtimeId . '"']))
                $this->foreign('realtimeId')->delete();
    }
}