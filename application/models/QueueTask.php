<?php
class QueueTask extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'QueueTask_Row';

    /**
     * Flag to use mysql native ON DELETE CASCADE for deletion
     * of queueChunk- and queueItem- child entries for it to be much quicker
     *
     * @var bool
     */
    protected $_nativeCascade = true;
}