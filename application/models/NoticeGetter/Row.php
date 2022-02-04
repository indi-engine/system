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
     * Notify recipients
     *
     * @param Indi_Db_Table_Row $row
     * @param $diff
     */
    public function notify(Indi_Db_Table_Row $row, $diff) {

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
        if ($this->criteriaRelyOn == 'event') $this->_notify($diff);

        // 2.2 Else separately notify two groups of recipients: ones for 'dec' and others for 'inc'
        else foreach ([-1, 1] as $diff) $this->_notify($diff);
    }

    /**
     * Internal fn, responsible for:
     * 1. Preparing the message's header and body, according to $diff arg
     * 2. Fetching the recipients lists, that also depends on $diff arg
     * 3. Sending prepared message to fetched recipients
     *
     * @param $diff
     */
    protected function _notify($diff) {

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
        $notifyA = $this->users('criteria' . ($this->criteriaRelyOn == 'event' ? 'Evt' : $dir));

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
            'body' => $this->wsmsg != 'n' ? preg_replace('~jump=".*?,([^,"]+)"~', 'jump="$1"', $body) : ''
        ];
    
        // Append audio if need
        if ($audio) $msg['audio'] = $audio;

        // Trim body
        $msg['body'] = usubstr($msg['body'], 350);
    
        // Send web-socket messages
        Indi::ws([
            'type' => 'notice',
            'mode' => 'menu-qty',
            'qtyReload' => $this->foreign('noticeId')->qtyReload,
            'noticeId' => $this->noticeId,
            'diff' => $diff,
            'row' => $this->row->id,
            'to' => [$this->profileId => array_column($rs, $field)],
            'msg' => $msg
        ]);
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
    public function users($criteriaProp) {

        // Start building WHERE clauses array
        $where = ['`toggle` = "y"'];

        // Find the name of database table, where recipients should be found within
        foreach (Indi_Db::role() as $profileIds => $entityId)
            if (in($this->profileId, $profileIds))
                if ($model = m($entityId))
                    break;

        // Prevent recipients duplication
        if ($model->table() == 'admin') $where[] = '`profileId` = "' . $this->profileId . '"';

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
}