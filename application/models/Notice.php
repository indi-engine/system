<?php
class Notice extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Notice_Row';

    /**
     * Array of fields, which contents will be evaluated with php's eval() function
     * @var array
     */
    protected $_evalFields = ['event', 'qtySql', 'tplIncBody', 'tplDecBody', 'tplEvtBody'];

    /**
     * Info for l10n-fraction detection
     *
     * @var array
     */
    protected $_fraction = [
        'field' => 'fraction',
        'value' => [
            'system' => 'adminSystemUi',
            'custom' => 'adminCustomUi'
        ]
    ];

    /**
     * Get notices info for given $admin for given $sectionIdA
     *
     * @param $admin
     * @param array $sectionIdA [sectionId => WHERE clause] pairs
     * @return array
     */
    public function info($admin, array $sectionIdA) {

        // Get ids of relyOnGetter-notices, that should be used to setup counters for sections given by $sectionIdA
        $noticeIdA_relyOnGetter = db()->query("
            SELECT `noticeId`
            FROM `noticeGetter`
            WHERE 1
              AND `criteriaRelyOn` = 'getter'
              AND `roleId` = '{$admin->roleId}'
              AND `toggle` = 'y'
        ")->col();

        // Get ids of relyOnEvent-notices, that should be used to setup counters for sections given by $sectionIdA
        $noticeIdA_relyOnEvent = db()->query("
            SELECT `noticeId`, `criteriaEvt`
            FROM `noticeGetter`
            WHERE 1
              AND `criteriaRelyOn` = 'event'
              AND `roleId` = '{$admin->roleId}'
              AND `toggle` = 'y'
        ")->pairs();

        // Remove relyOnEvent-notices having criteria that current user/getter not match
        foreach ($noticeIdA_relyOnEvent as $noticeId => $criteriaEvt)
            if ($criteriaEvt && !$admin->model()->row("`id` = '{$admin->id}' AND $criteriaEvt"))
                unset($noticeIdA_relyOnEvent[$noticeId]);
        $noticeIdA_relyOnEvent = array_keys($noticeIdA_relyOnEvent);

        // Get notices
        $_noticeRs = m('notice')->all([
            'FIND_IN_SET("' . $admin->roleId . '", `roleId`)',
            'CONCAT(",", `sectionId`, ",") REGEXP ",(' . im(array_keys($sectionIdA), '|') . '),"',
            'FIND_IN_SET(`id`, IF(`qtyDiffRelyOn` = "event", "' . im($noticeIdA_relyOnEvent) . '", "' . im($noticeIdA_relyOnGetter) . '"))',
            '`toggle` = "y"'
        ]);

        // If no notices - return
        if (!$_noticeRs->count()) return [];

        // Foreach notice
        foreach ($_noticeRs as $_noticeR) {

            // Collect qtys for each sections
            foreach (ar($_noticeR->sectionId) as $sectionId) {

                // Prepare WHERE clause
                $where = [];
                if (strlen($sectionIdA[$sectionId])) $where []= $sectionIdA[$sectionId];
                $where []= $_noticeR->compiled('qtySql');
                $where = '(' . join(') AND (', $where) . ')';

                // Get qty
                $_noticeR->qty = db()->query('
                    SELECT COUNT(`id`)
                    FROM `' . m($_noticeR->entityId)->table().'`
                    WHERE ' . $where
                )->cell();

                // Prepare info item
                $info[$sectionId][] = [
                    'qty' => $_noticeR->qty ?: 0,
                    'id' => $_noticeR->id,
                    'bg' => $_noticeR->colorHex('bg'),
                    'fg' => $_noticeR->colorHex('fg'),
                    'tip' => $_noticeR->tooltip
                ];
            }
        }

        // Return info
        return $info;
    }
}