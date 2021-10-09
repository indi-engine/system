<?php
class Indi_View_Action_Admin implements Indi_View_Action_Interface {

    /**
     * Mode. Can be 'auto' and 'view'. This setting affects on type of the value,
     * returned by 'Indi::trail()->view(true)' call. If $mode is 'auto' (by default) - the
     * type of the return value will be detected automatically (possible types are - rendered
     * plain text or instance of Indi_View_Action_Admin), depending on a number of circumstances.
     * If mode is 'view' - instance of Indi_View_Action_Admin will be forced to be returned, and,
     * additionally, plain contents, got by rendering the found script file - will be placed into ::$plain
     * property within that Indi_View_Action_Admin instance.
     *
     * @var string
     */
    public $mode = 'auto';

    /**
     * Plain contents
     *
     * @var string
     */
    public $plain = '';

    /**
     * Constructor
     *
     * @param $plain
     */
    public function __construct($plain = null) {
        $this->plain = $plain;
    }

    /**
     * Render the view
     *
     * @return string
     */
    public function render() {

        // Create `realtime` entry having `type` = "context"
        if (!m('realtime')->fetchRow([
            '`type` = "context"',
            '`token` = "' . t()->bid() . '"',
            '`realtimeId` = "' . m('realtime')->fetchRow('`token` = "' . CID . '"')->id . '"'
        ])) t()->context();

        // Return json-encoded trail data
        return json_encode(array('route' =>Indi::trail(true)->toArray(), 'plain' => $this->plain), JSON_UNESCAPED_UNICODE);
    }
}