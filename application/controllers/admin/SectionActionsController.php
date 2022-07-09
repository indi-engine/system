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

            // Ask whether we want to turn l10n On/Off,
            // or want to arrange value of `l10n` for it to match real situation.
            if ('no' == $this->confirm(sprintf(
                    'Если вы хотите %s мультиязычность для действия "%s" нажмите "%s". ' .
                    'Если просто нужно привести в соответствие с текущим состоянием - нажмите "%s"',
                    $value == 'qy' ? 'включить' : 'выключить', t()->row->title, I_YES, I_NO), 'YESNOCANCEL'))
                return;

        // Else if we're going to setup fraction-status directly
        } else if ('ok' == $this->confirm(sprintf(
                'Для поля "%s" мультиязычность будет вручную указана как "%s". Продолжить?',
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
        $prompt = $this->prompt(sprintf(
            $value == 'qy' ? 'Выберите текущий язык действия "%s"' : 'Выберите язык который должен остаться для действия "%s"',
            t()->row->title
        ), [$combo]);

        // Check prompt data
        $_ = jcheck(['langId' => ['req' => true, 'rex' => 'int11', 'key' => 'lang']], $prompt);

        // Build queue class name
        $queueClassName = 'Indi_Queue_L10n_Action';

        // Check that class exists
        if (!class_exists($queueClassName)) jflush(false, sprintf('Не найден класс %s', $queueClassName));

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
}