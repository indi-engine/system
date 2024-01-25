<?php
class NoticeGetter_Row extends Indi_Db_Table_Row_Noeval {

    /**
     * Entry, that triggered the notice.
     * Used for building the criteria, that recipients should match
     *
     * @var Indi_Db_Table_Row
     */
    public $row = null;

    /**
     * Can be 'insert', 'update' or 'delete'
     *
     * @var string
     */
    public $event = 'update';

    /**
     * This method was redefined to provide ability for some field
     * props to be set using aliases rather than ids
     *
     * @param  string $columnName The column key.
     * @param  mixed  $value      The value for the property.
     * @return void
     */
    public function __set($columnName, $value) {

        // Provide ability for some field props to be set using aliases rather than ids
        if (is_string($value) && !Indi::rexm('int11', $value)) {
            if ($columnName == 'roleId') $value = role($value)->id;
        }

        // Standard __set()
        parent::__set($columnName, $value);
    }

    /**
     * Notify recipients
     *
     * @param Indi_Db_Table_Row $row
     * @param $diff
     */
    public function notify(Indi_Db_Table_Row $row, $diff, $event) {

        // If $diff arg is not 0, it means that `notice` entry (that current `noticeGetter` entry belongs to)
        // has `qtyDiffRelyOn` == 'event', and this, in it's turn, means that change-direction-of-counter,
        // linked to the above mentioned `notice` entry - is already determined, and this direction
        // ('inc' or 'dec' / '+1' or '-1') - will be sole for all getter's recipients, e.g. direction won't
        // differ for different recipients having same role (specified within getter's settings).
        // But, if current getter's settings has 'getter' as the value of `criteriaRelyOn`
        // field - there should be no notifications sent
        if ($diff != 0 && $this->criteriaRelyOn == 'getter') return;

        // If notice is turned Off for current getter - return
        if ($this->toggle == 'n') return;

        // Else if $diff is 0 (e.g if `notice` entry's `qtyDiffRelyOn` prop's value is 'getter'):
        // 1. Assign `row` prop, that will be visible in compiling context
        $this->row = $row;

        // 2.1 If current getter's `criteriaRelyOn` is 'event' - use $diff arg as is
        if ($this->criteriaRelyOn == 'event') $this->_notify($diff, $event);

        // 2.2 Else separately notify one or two groups of recipients: 'dec' and/or 'inc'
        else foreach ([
            'update' => [-1, 1],
            'insert' => [ 1],
            'delete' => [-1]
        ][$event] as $diff) $this->_notify($diff, $event);
    }

    /**
     * Internal fn, responsible for:
     * 1. Preparing the message's header and body, according to $diff arg
     * 2. Fetching the recipients lists, that also depends on $diff arg
     * 3. Sending prepared message to fetched recipients
     *
     * @param $diff
     */
    protected function _notify($diff, $event) {

        // Setup possible directions
        $dirs = [-1 => 'Dec', 0 => 'Evt', 1 => 'Inc'];

        // Get direction, for being used as a part of field names
        $dir = $dirs[$diff];

        // Get header and body
        $subj = $this->foreign('noticeId')->{'tpl' . $dir . 'Subj'};
        $this->foreign('noticeId')->compiled('tpl' . $dir . 'Body', null);
        $body = $this->foreign('noticeId')->compiled('tpl' . $dir . 'Body');
        $audio = $dir == 'Inc' ? $this->foreign('noticeId')->src('tpl' . $dir . 'Audio') : false;

        // Get recipients
        $notifyA = $this->users('criteria' . ($this->criteriaRelyOn == 'event' ? 'Evt' : $dir), $event);

        // If no recipients - return
        if (!$notifyA['rs']) return;

        // For each applicable way - do notify
        foreach ($notifyA['wayA'] as $way => $field)
            if (method_exists($this, $method = '_' . $way))
                $this->$method($notifyA['rs'], $field, $subj, $body, $diff, $audio);
    }

    /**
     * Notify recipients via web-sockets
     *
     * @param $rs
     * @param $field
     * @param $subj
     * @param $body
     * @param $diff
     */
    private function _ws($rs, $field, $subj, $body, $diff, $audio = false) {

        // Prepare msg
        $msg = [
            'header' => $this->wsmsg != 'n' ? $subj : '',
            'body' => $this->wsmsg != 'n' ? preg_replace('~jump=".*?,([^,"]+)"~', 'jump="$1"', $body) : '',
            'bg' => $this->foreign('noticeId')->rgb('bg'),
            'fg' => $this->foreign('noticeId')->rgb('fg'),
        ];
    
        // Append audio if need
        if ($audio) $msg['audio'] = $audio;

        // Trim body
        $msg['body'] = usubstr($msg['body'], 350);

        // Destination definition shortcut
        $to = [$this->roleId => array_column($rs, $field)];

        // Send web-socket messages
        Indi::ws([
            'type' => 'notice',
            'mode' => 'menu-qty',
            'qtyReload' => $this->foreign('noticeId')->qtyReload,
            'noticeId' => $this->noticeId,
            'diff' => $diff,
            'row' => $this->row->id,
            'to' => $to,
            'msg' => $msg
        ]);

        // If http-hook is given
        if ($diff == 1 && $this->http) {

            // Trigger Indi.load(...) call
            Indi::ws([
                'type' => 'load',
                'href' => str_replace('{id}', $this->row->id, $this->http),
                'to' => $to
            ]);
        }
    }

    /**
     * Notify recipients via email
     *
     * @param $rs
     * @param $field
     * @param $subj
     * @param $body
     * @return mixed
     */
    private function _email($rs, $field, $subj, $body) {

        // If message body is empty - return
        if (!$body) return;

        // Collect unique valid emails
        $__ = []; foreach ($rs as $r) if (Indi::rexm('email', $_ = $r[$field])) $__[$_] = true;

        // If no valid emails collected - return
        if (!$emailA = array_keys($__)) return;

        // Convert square brackets into <>
        $body = str_replace(ar('[,]'), ar('<,>'), nl2br($body));

        // Convert hrefs uri's to absolute
        $body = preg_replace(
            '~(\s+jump=")(/[^/][^"]*")~',
            ' href="' . ($_SERVER['REQUEST_SCHEME'] ?: 'http') . '://'. $_SERVER['HTTP_HOST'] . PRE . '/#$2',
            $body
        );

        // Init mailer
        $mailer = Indi::mailer();
        $mailer->Subject = $subj;
        $mailer->Body = $body;

        // Add each valid email address to BCC
        foreach ($emailA as $email) $mailer->addBCC($email);

        // Send email notifications
        $mailer->send();
    }

    /**
     * Notify recipients via sms
     *
     * @param $rs
     * @param $field
     * @param $subj
     * @param $body
     */
    private function _sms($rs, $field, $subj, $body) {

        // Convert body's square brackets into <>
        $body = str_replace(ar('[,]'), ar('<,>'), $body);

        // Strip tags
        $body = strip_tags($body);

        // If message body is empty - return
        if (!$body) return;

        // Send sms. Phone numbers validation will be done within Sms:send() method
        Sms::send(array_column($rs, $field), $body);
    }

    /**
     * Notify recipients via VK
     *
     * @param $rs
     * @param $field
     * @param $subj
     * @param $body
     */
    private function _vk($rs, $field, $subj, $body) {

        // Convert body's square brackets into <>
        $body = str_replace(ar('[,]'), ar('<,>'), $body);

        // Strip tags
        $body = strip_tags($body);

        // If message body is empty - return
        if (!$body) return;

        // Collect unique valid emails
        $vkA = []; foreach ($rs as $r) if ($vk = Indi::rexm('vk', $_ = $r[$field], 1)) $vkA[$vk] = $r['title'];

        // If no valid VK uids collected - return
        if (!$vkA) return;

        // Foreach found
        foreach ($vkA as $vk => $title) {

            // Build msg
            $msg = $title ? $msg = $title . ', ' . mb_lcfirst($body) : $body;

            // Send
            Vk::send($vk, $subj . '<br>' . $msg);
        }
    }

    /**
     * Get array of recipients ids
     */
    public function users($criteriaProp, $event) {

        // Start building WHERE clauses array
        $where = ['`toggle` = "y"'];

        // Find the name of database table, where recipients should be found within
        foreach (Indi_Db::role() as $roleIds => $entityId)
            if (in($this->roleId, $roleIds))
                if ($model = m($entityId))
                    break;

        // Prevent recipients duplication
        if ($model->fields('roleId')) $where[] = '`roleId` = "' . $this->roleId . '"';

        // Make $event to me accessible during complication
        $this->event = $event;

        // If criteria specified
        if (strlen($this->$criteriaProp)) {

            // Unset previously compiled criteria
            unset($this->_compiled[$criteriaProp]);

            // Append compiled criteria to WHERE clauses array
            if (strlen($criteria = $this->compiled($criteriaProp))) $where[] = '(' . $criteria . ')';
        }

        // Ways of notifications delivery and fields to be used as destination addresses
        $_wayA = [
            'email' => 'email',
            'vk' => 'vk',
            'sms' => 'phone'
        ];

        // Foreach way, check if such a way is turned On, and such a field exists, and if so - append field to $fieldA array
        $wayA = ['ws' => 'id'];
        foreach ($_wayA as $way => $field)
            if ($this->$way == 'y' && $model->fields($field))
                $wayA[$way] = $field;

        // Fetch recipients
        $rs = db()->query('
            SELECT `' . im($wayA, '`, `') . '`
            FROM `' . $model->table() . '`
            WHERE ' . im($where, ' AND ')
        )->fetchAll();

        // Convert type of 'id' to integer
        foreach ($rs as &$r) $r['id'] = (int) $r['id'];

        // Return array containing applicable ways and found recipients
        return ['wayA' => $wayA, 'rs' => $rs];
    }

    /**
     * Setter for `title`
     */
    public function setTitle() {
        $this->_setTitle();
    }


    /**
     * Build an expression for creating the current `noticeGetter` entry in another project, running on Indi Engine
     *
     * @param string $certain
     * @return string
     */
    public function export($certain = '') {

        // Build `notice` entry creation expression
        $lineA[] = "noticeGetter('"
            . $this->foreign('noticeId')->foreign('entityId')->table . "', '"
            . $this->foreign('noticeId')->alias . "', '"
            . $this->foreign('roleId')->alias . "', " . $this->_ctor($certain) . ");";

        // If $certain arg is given - export it only
        if ($certain) return $lineA[0];

        // Return newline-separated list of creation expressions
        return im($lineA, "\n");
    }

    /**
     * Build a string, that will be used in NoticeGetter_Row->export()
     *
     * @param string $certain
     * @return string
     */
    protected function _ctor($certain = null) {

        // Use original data as initial ctor
        $ctor = $this->_original;

        // Exclude `id` as it will be set automatically by MySQL
        unset($ctor['id']);

        // Exclude props that will be already represented by shorthand-fn args
        foreach (ar('noticeId,roleId') as $arg) unset($ctor[$arg]);

        // If certain fields should be exported - keep them only
        $ctor = $this->_certain($certain, $ctor);

        // Foreach $ctor prop
        foreach ($ctor as $prop => &$value) {

            // Get field
            $field = $this->model()->fields($prop);

            // Exclude prop, if it has value equal to default value
            if ($field->defaultValue == $value && !in($prop, $certain)) unset($ctor[$prop]);

            // Exclude 'title'-prop due to that it is set automatically to the same value
            // as $this->foreign('roleId')->title, as self 'title' prop has such a dependency
            else if ($prop == 'title' && ($tf = $this->model()->titleField()) && $tf->storeRelationAbility != 'none' && !in($prop, $certain))
                unset($ctor[$prop]);
        }

        // Stringify and return $ctor
        return _var_export($ctor);
    }
}