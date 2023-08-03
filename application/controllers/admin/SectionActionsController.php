<?php
class Admin_SectionActionsController extends Indi_Controller_Admin_Multinew {

    /**
     * @var string
     */
    public $field = 'actionId';

    /**
     * @var string
     */
    public $unset = 'rename';

    /**
     * Create `queueTask` entry
     *
     * @param $cell
     * @param $value
     */
    public function onBeforeCellSave($cell, $value) {

        // If $cell is not 'l10n' - skip
        if ($cell != 'l10n') return;

        // If we're going to create queue task for turning selected language either On or Off
        if (in($value, 'qy,qn')) {

            // If template file not exists - there is nothing to be translated
            if ($file = $this->templateRequired())
                jflush(false, __(I_SCNACS_TPL_404, $file));

            // Ask whether we want to turn l10n On/Off,
            // or want to arrange value of `l10n` for it to match real situation.
            if ('no' == $this->confirm(__(
                I_L10N_TOGGLE_ACTION_Y . ' ' . I_L10N_TOGGLE_MATCH,
                mb_strtolower($value == 'qy' ? I_TOGGLE_Y : I_TOGGLE_N), t()->row->title, I_YES, I_NO), 'YESNOCANCEL'))
                return;

        // Else if we're going to setup fraction-status directly
        } else if ('ok' == $this->confirm(__(
                I_L10N_TOGGLE_ACTION_EXPL,
                t()->row->title, t()->row->enumset($cell, $value)
            ), 'OKCANCEL'))
            return;

        // Applicable languages WHERE clause
        $langId_filter = '"y" IN (`' . im($fraction = ar(t()->row->fraction()), '`, `') . '`)';

        // Create phantom `langId` field
        $langId_combo = m('Field')->new([
            'alias' => 'langId',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'storeRelationAbility' => 'one',
            'relation' => 'lang',
            'filter' => $langId_filter,
            'mode' => 'hidden',
            'defaultValue' => 0
        ]);

        // Append to fields list
        m()->fields()->append($langId_combo);

        // Set active value
        t()->row->langId = m('lang')->row($langId_filter, '`move`')->id;

        // Build config for langId-combo
        $combo = ['fieldLabel' => '', 'allowBlank' => 0] + t()->row->combo('langId');

        // Prompt for source language
        $prompt = $this->prompt(__($value == 'qy'
            ? I_L10N_TOGGLE_ACTION_LANG_CURR
            : I_L10N_TOGGLE_ACTION_LANG_KEPT
        , t()->row->title), [$combo]);

        // Check prompt data
        $_ = jcheck(['langId' => ['req' => true, 'rex' => 'int11', 'key' => 'lang']], $prompt);

        // Build queue class name
        $queueClassName = 'Indi_Queue_L10n_Action';

        // Check that class exists
        if (!class_exists($queueClassName)) jflush(false, __(I_CLASS_404, $queueClassName));

        // Create queue class instance
        $queue = new $queueClassName();

        // Get target langs
        $target = [];
        foreach ($fraction as $fractionI) $target[$fractionI] = m('Lang')->all([
            '`' . $fractionI . '` = "y"',
            '`alias` != "' . $_['langId']->alias . '"'
        ])->column('alias', true);

        // Prepare params
        $params = [
            'action' => t(1)->row->alias . ':' . t()->row->foreign('actionId')->alias,
            'source' => $_['langId']->alias
        ];

        // Prepare params
        $params['target'] = $target;

        // If we're going to turn l10n On for this field - specify target languages,
        // else setup 'toggle' param as 'n', indicating that l10n will be turned On for this field
        if ($value != 'qy') $params['toggle'] = 'n';

        // Run first stage
        $queueTaskR = $queue->chunk($params);

        // Auto-start queue as a background process
        Indi::cmd('queue', ['queueTaskId' => $queueTaskR->id]);
    }

    /**
     * Check whether there is template php-file exists for the action in the section,
     * that current record is representing, because if no - there is nothing to be translated
     * This used in onBeforeCellSave() for l10n-field, because turning on localization for actions
     * is applicable only if action is aimed to display rendered template
     *
     * @return false|string
     * @throws Exception
     */
    public function templateRequired() {

        // PHP-view files for sections of fraction:
        // - 'system' - will be created in VDR . '/system',
        // - 'public'                   in VDR . '/public',
        // - 'custom'                   in ''
        $repoDirA = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // Get fraction that current record belongs to
        $fraction = t()->row->foreign('sectionId')->fraction;

        // Build the dir name, that controller's js-file should be created in
        $template = DOC . STD . $repoDirA[$fraction]
            . '/application/views/admin/'
            . t()->row->foreign('sectionId')->alias . '/'
            . t()->row->foreign('actionId')->alias . '.php';

        // If view file is not yet exists - return it's name
        return is_file($template) ? false : substr($template, strlen(DOC . STD) + 1);
    }
}