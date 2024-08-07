<?php
class Realtime extends Indi_Db_Table {

    /**
     * Classname for row
     *
     * @var string
     */
    public $_rowClass = 'Realtime_Row';

    /**
     * Get `realtime`-entry of `type`="session" having `token`= session_id().
     * If not exists - it will be created
     *
     * @return Realtime_Row
     */
    public static function session($checkOnly = false) {

        // Shortcuts
        $m = m('realtime'); $session_id = session_id();

        // Get either existing realtime-record having type=session and token=session_id() or create a new one
        $session = $m->row(['`type` =  "session"', "`token` = '$session_id'"]);

        // Pick roleId and adminId from $_SESSION, if any
        $roleId = $_SESSION['admin']['roleId'] ?? 0; $adminId = $_SESSION['admin']['id'] ?? 0;

        // If no realtime-record of type 'session' is found, or found but it's roleId and/or adminId differs
        if (!$session || "$session->roleId-$session->adminId" !== "$roleId-$adminId")

            // If we're in $checkOnly mode - just return false
            if ($checkOnly) return false;


        // If no existing session found having given $session_id
        if (!$session) {

            // Create new `realtime` entry of type 'session'
            $session = $m->new([ 'type' => 'session' ,  'token' => $session_id  ]);
        }

        // Update props
        $session->set([
            'roleId' => $roleId ?: null,
            'adminId' => $adminId ?: null,
            'langId' => m('Lang')->row('`alias` = "' . $_COOKIE['i-language'] . '"')->id,
        ])->save();

        // Return
        return $session;
    }

    /**
     * Get `realtime`-entry of `type`="channel", representing browser tab, where request came from
     *
     * @return Realtime_Row|bool
     */
    public static function channel() {
        return defined('CID')
            ? m('realtime')->row(['`type` = "channel"', '`token` = "' . CID . '"'])
            : false;
    }

    /**
     * Create `realtime` entry having `type` = "context"
     *
     * @return Indi_Db_Table_Row
     */
    public static function context() {

        // If no channel found - return
        if (!$realtimeR_channel = self::channel()) return;

        // If context entry already exists - return it
        if ($realtimeR_context = m('realtime')->row([
            '`type` = "context"',
            '`token` = "' . t()->bid() . '"',
            '`realtimeId` = "' . $realtimeR_channel->id . '"'
        ])) return $realtimeR_context;

        // Else:

        // Get data to be copied
        $data = $realtimeR_channel->original(); unset($data['id'], $data['spaceSince']);

        // Get involved fields
        $fields = t()->row
            ? t()->fields->select('regular,required,readonly', 'mode')
            : t()->gridFields->select(': > 0');

        //
        $fieldIdA = [];
        foreach ($fields as $field)
            if ($id = $field->original('id'))
                $fieldIdA []= $id;

        // Create `realtime` entry of `type` = "context"
        $realtimeR_context = m('realtime')->new([
            'realtimeId' => $realtimeR_channel->id,
            'type' => 'context',
            'token' => t()->bid(),
            'sectionId' => t()->section->id,
            'entityId' => t()->section->entityId,
            'fields' => im($fieldIdA),
            'title' => t(true)->toString(),
            'mode' => t()->action->selectionRequired == 'y' ? 'row' : 'rowset'
        ] + $data);

        // If it's a row-action
        if (t()->action->selectionRequired == 'y') {

            // Scope params
            $scope = [
                'hash' => t()->section->primaryHash,
                'rowsOnPage' => t()->section->rowsOnPage,
                'rowReqIfAffected' => t()->grid->select('y', 'rowReqIfAffected')->column('fieldId', true),
                'icon' => t()->icons(),
                'jump' => t()->jumps(),
                'color' => t()->scope->color,
                'filterOwner' => t()->filterOwner('section'),
            ];

            // Row color definition
            if (t()->section->colorField) $scope += [
                'colorField' => t()->section->foreign('colorField')->alias,
                'colorFurther' => t()->section->foreign('colorFurther')->alias
            ];

            // Apply to content-record
            $realtimeR_context->set([
                'entries' => t()->row->id,
                'fields'  => im(array_keys(
                    array_flip(ar($realtimeR_context->fields))
                    + t()->gridChunks()['gridChunksInvolvedFieldIds']
                )),
                'scope' => json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT)
            ]);
        }

        // Save it
        $realtimeR_context->save();

        // Return it
        return $realtimeR_context;
    }
}