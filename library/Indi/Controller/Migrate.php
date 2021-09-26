<?php
class Indi_Controller_Migrate extends Indi_Controller {
    public function adminSystemUiFieldsToggleL10nAction() {
        consider('realtime', 'title', 'type', ['required' => 'y']);
        field('element', 'storeRelationAbility')->toggleL10n('qy', 'ru', false);
        field('columnType', 'canStoreRelation')->toggleL10n('qy', 'ru', false);
        field('grid', 'summaryType')->toggleL10n('qy', 'ru', false);
        field('section', 'toggle')->toggleL10n('qy', 'ru', false);
        field('section2action', 'toggle')->toggleL10n('qy', 'ru', false);
        field('profile', 'toggle')->toggleL10n('qy', 'ru', false);
        field('admin', 'toggle')->toggleL10n('qy', 'ru', false);
        field('search', 'toggle')->toggleL10n('qy', 'ru', false);
        field('resize', 'proportions')->toggleL10n('qy', 'ru', false);
        field('resize', 'masterDimensionAlias')->toggleL10n('qy', 'ru', false);
        field('action', 'rowRequired')->toggleL10n('qy', 'ru', false);
        field('field', 'storeRelationAbility')->toggleL10n('qy', 'ru', false);
        field('section2action', 'south')->toggleL10n('qy', 'ru', false);
        field('section', 'defaultSortDirection')->toggleL10n('qy', 'ru', false);
        field('resize', 'changeColor')->toggleL10n('qy', 'ru', false);
        field('entity', 'system')->toggleL10n('qy', 'ru', false);
        field('action', 'type')->toggleL10n('qy', 'ru', false);
        field('section', 'type')->toggleL10n('qy', 'ru', false);
        field('section', 'rownumberer')->toggleL10n('qy', 'ru', false);
        field('section', 'disableAdd')->toggleL10n('qy', 'ru', false);
        field('action', 'display')->toggleL10n('qy', 'ru', false);
        field('grid', 'toggle')->toggleL10n('qy', 'ru', false);
        field('alteredField', 'mode')->toggleL10n('qy', 'ru', false);
        field('field', 'mode')->toggleL10n('qy', 'ru', false);
        field('search', 'access')->toggleL10n('qy', 'ru', false);
        field('action', 'toggle')->toggleL10n('qy', 'ru', false);
        field('grid', 'access')->toggleL10n('qy', 'ru', false);
        field('alteredField', 'impact')->toggleL10n('qy', 'ru', false);
        field('grid', 'editor')->toggleL10n('qy', 'ru', false);
        field('section', 'rowsetSeparate')->toggleL10n('qy', 'ru', false);
        field('section2action', 'fitWindow')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplFor')->toggleL10n('qy', 'ru', false);
        field('notice', 'toggle')->toggleL10n('qy', 'ru', false);
        field('notice', 'qtyDiffRelyOn')->toggleL10n('qy', 'ru', false);
        field('lang', 'toggle')->toggleL10n('qy', 'ru', false);
        field('field', 'l10n')->toggleL10n('qy', 'ru', false);
        field('entity', 'spaceScheme')->toggleL10n('qy', 'ru', false);
        field('noticeGetter', 'criteriaRelyOn')->toggleL10n('qy', 'ru', false);
        field('noticeGetter', 'email')->toggleL10n('qy', 'ru', false);
        field('noticeGetter', 'vk')->toggleL10n('qy', 'ru', false);
        field('noticeGetter', 'sms')->toggleL10n('qy', 'ru', false);
        field('month', 'month')->toggleL10n('qy', 'ru', false);
        field('section', 'expand')->toggleL10n('qy', 'ru', false);
        field('consider', 'required')->toggleL10n('qy', 'ru', false);
        field('grid', 'group')->toggleL10n('qy', 'ru', false);
        field('section', 'multiSelect')->toggleL10n('qy', 'ru', false);
        field('noticeGetter', 'toggle')->toggleL10n('qy', 'ru', false);
        field('admin', 'demo')->toggleL10n('qy', 'ru', false);
        field('profile', 'demo')->toggleL10n('qy', 'ru', false);
        field('notice', 'type')->toggleL10n('qy', 'ru', false);
        field('admin', 'uiedit')->toggleL10n('qy', 'ru', false);
        field('lang', 'state')->toggleL10n('qy', 'ru', false);
        field('lang', 'adminSystemUi')->toggleL10n('qy', 'ru', false);
        field('lang', 'adminSystemConst')->toggleL10n('qy', 'ru', false);
        field('lang', 'adminCustomUi')->toggleL10n('qy', 'ru', false);
        field('lang', 'adminCustomConst')->toggleL10n('qy', 'ru', false);
        field('lang', 'adminCustomData')->toggleL10n('qy', 'ru', false);
        field('lang', 'adminCustomTmpl')->toggleL10n('qy', 'ru', false);
        field('queueTask', 'stage')->toggleL10n('qy', 'ru', false);
        field('queueTask', 'state')->toggleL10n('qy', 'ru', false);
        field('queueTask', 'countState')->toggleL10n('qy', 'ru', false);
        field('queueTask', 'itemsState')->toggleL10n('qy', 'ru', false);
        field('queueTask', 'queueState')->toggleL10n('qy', 'ru', false);
        field('queueTask', 'applyState')->toggleL10n('qy', 'ru', false);
        field('queueChunk', 'countState')->toggleL10n('qy', 'ru', false);
        field('queueChunk', 'itemsState')->toggleL10n('qy', 'ru', false);
        field('queueChunk', 'queueState')->toggleL10n('qy', 'ru', false);
        field('queueChunk', 'applyState')->toggleL10n('qy', 'ru', false);
        field('queueItem', 'stage')->toggleL10n('qy', 'ru', false);
        field('queueChunk', 'fraction')->toggleL10n('qy', 'ru', false);
        field('profile', 'type')->toggleL10n('qy', 'ru', false);
        field('section2action', 'l10n')->toggleL10n('qy', 'ru', false);
        field('realtime', 'type')->toggleL10n('qy', 'ru', false);
        field('realtime', 'mode')->toggleL10n('qy', 'ru', false);
        field('grid', 'rowReqIfAffected')->toggleL10n('qy', 'ru', false);
        field('columnType', 'title')->toggleL10n('qy', 'ru', false);
        field('entity', 'title')->toggleL10n('qy', 'ru', false);
        field('field', 'title')->toggleL10n('qy', 'ru', false);
        field('section', 'title')->toggleL10n('qy', 'ru', false);
        field('action', 'title')->toggleL10n('qy', 'ru', false);
        field('profile', 'title')->toggleL10n('qy', 'ru', false);
        field('admin', 'title')->toggleL10n('qy', 'ru', false);
        field('element', 'title')->toggleL10n('qy', 'ru', false);
        field('resize', 'title')->toggleL10n('qy', 'ru', false);
        field('field', 'tooltip')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplDecSubj')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplIncBody')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplIncSubj')->toggleL10n('qy', 'ru', false);
        field('notice', 'tooltip')->toggleL10n('qy', 'ru', false);
        field('notice', 'title')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplDecBody')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplEvtSubj')->toggleL10n('qy', 'ru', false);
        field('notice', 'tplEvtBody')->toggleL10n('qy', 'ru', false);
        field('month', 'title')->toggleL10n('qy', 'ru', false);
        field('grid', 'summaryText')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'datetime', 'when')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'calendar', 'when')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'string', 'vtype')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'combo', 'placeholder')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'string', 'inputMask')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'calendar', 'displayFormat')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'number', 'measure')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'datetime', 'displayTimeFormat')->toggleL10n('qy', 'ru', false);
        cfgField('element', 'datetime', 'displayDateFormat')->toggleL10n('qy', 'ru', false);
        die('ok');
    }
    public function cfgFieldMissingAction() {
        cfgField('element', 'textarea', 'wide', [
            'title' => 'Во всю ширину',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'refreshL10nsOnUpdate',
        ]);
        cfgField('element', 'time', 'displayFormat', [
            'title' => 'Формат отображения',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'H:i',
            'move' => '',
        ]);
        field('param', 'fieldId', ['title' => 'Поле']);
        grid('paramsAll', 'fieldId', 'entityId', ['move' => '']);
        field('param', 'cfgField', ['filter' => '`entry` != "0"']);
        field('notice', 'type', ['title' => 'Фракция']);
        section('fields', [
            'extendsPhp' => 'Indi_Controller_Admin_Field',
            'extendsJs' => 'Indi.lib.controller.Field',
        ]);
        section('fieldsAll', [
            'filter' => '`entry` = "0"',
            'extendsPhp' => 'Indi_Controller_Admin_Field',
            'extendsJs' => 'Indi.lib.controller.Field'
        ]);
        field('queueTask', 'chunk', ['move' => 'params']);
        field('queueChunk', 'itemsBytes', ['move' => 'queueSize']);
        grid('search', 'consistence', ['move' => '']);
        filter('fieldsAll', 'entityId', true)->move(10);
        filter('fieldsAll', 'entityId', 'system', true)->move(10);
        section2action('lang','export', ['profileIds' => '1'])->move(2);
        die('ok');
    }
    public function cfgFieldRemoveLegacyAction() {
        entity('possibleElementParam')->delete();
        field('param', 'value')->delete();
        entity('param', ['titleFieldId' => 'cfgField']);
        consider('param', 'title', 'cfgField', ['foreign' => 'title', 'required' => 'y']);
        m('Param')->fetchAll('`cfgField` = "0"')->delete();
        die('ok');
    }
    public function cfgFieldMetaAction() {
        field('param', 'cfgField', [
            'title' => 'Параметр',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'title',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`entityId` = "4"',
        ]);
        field('param', 'cfgValue', [
            'title' => 'Значение',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'cfgField',
        ]);
        field('field', 'entry', [
            'title' => 'Экземпляр',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'entityId',
            'storeRelationAbility' => 'one',
        ]);
        consider('param', 'cfgField', 'fieldId', ['foreign' => 'elementId', 'required' => 'y', 'connector' => 'entry']);
        consider('field', 'entry', 'entityId', ['required' => 'y']);
        field('field', 'title', ['move' => 'entry']);
        field('element', 'optionTemplate', [
            'title' => 'Шаблон содержимого опции',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'hidden',
            'entry' => '23',
        ]);
        field('element', 'optionHeight', [
            'title' => 'Высота опции',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '14',
            'move' => 'optionTemplate',
            'entry' => '23',
        ]);
        field('element', 'placeholder', [
            'title' => 'Плейсхолдер',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'optionHeight',
            'entry' => '23',
        ]);
        field('element', 'groupBy', [
            'title' => 'Группировка опций по столбцу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'placeholder',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'entry' => '23',
        ]);
        field('element', 'optionAttrs', [
            'title' => 'Дополнительно передавать параметры (в виде атрибутов)',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'groupBy',
            'relation' => 'field',
            'storeRelationAbility' => 'many',
            'entry' => '23',
        ]);
        field('element', 'noLookup', [
            'title' => 'Отключить лукап',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'optionAttrs',
            'entry' => '23',
        ]);
        field('element', 'titleColumn', [ //
            'title' => 'Заголовочное поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'cols',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'entry' => '23',
        ]);
        field('element', 'wide', [ //
            'title' => 'Во всю ширину',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'when',
            'entry' => '23',
        ]);
        field('element', 'maxlength', [ //
            'title' => 'Максимальная длина в символах',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'prependEntityTitle',
            'entry' => '1',
        ]);
        field('element', 'inputMask', [
            'title' => 'Маска',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'maxlength',
            'entry' => '1',
        ]);
        field('element', 'shade', [
            'title' => 'Шейдинг',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'inputMask',
            'entry' => '1',
        ]);
        field('element', 'refreshL10nsOnUpdate', [ //
            'title' => 'Обновлять локализации для других языков',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'allowedTags',
            'entry' => '1',
        ]);
        field('element', 'titleColumn', [ //
            'title' => 'Заголовочное поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'move' => 'cols',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'entry' => '5',
        ]);
        field('element', 'allowedTags', [
            'title' => 'Разрешенные теги',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'titleColumn',
            'entry' => '6',
        ]);
        field('element', 'refreshL10nsOnUpdate', [
            'title' => 'Обновлять локализации для других языков',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'allowedTags',
            'entry' => '6',
        ]);
        field('element', 'cols', [
            'title' => 'Количество столбцов',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '1',
            'move' => 'refreshL10nsOnUpdate',
            'entry' => '7',
        ]);
        field('element', 'titleColumn', [
            'title' => 'Заголовочное поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'cols',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'entry' => '7',
        ]);
        field('element', 'displayFormat', [
            'title' => 'Отображаемый формат',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Y-m-d',
            'move' => 'titleColumn',
            'entry' => '12',
        ]);
        field('element', 'when', [ //
            'title' => 'Когда',
            'columnTypeId' => 'SET',
            'elementId' => 'combo',
            'move' => 'allowTypes',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'entry' => '12',
        ]);
        cfgEnumset('element', 'calendar', 'when', 'month', ['title' => 'Месяц', 'move' => '']);
        cfgEnumset('element', 'calendar', 'when', 'week', ['title' => 'День недели', 'move' => 'month']);
        field('element', 'wide', [
            'title' => 'Во всю ширину',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'when',
            'entry' => '13',
        ]);
        field('element', 'height', [
            'title' => 'Высота в пикселях',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '200',
            'move' => 'wide',
            'entry' => '13',
        ]);
        field('element', 'width', [
            'title' => 'Ширина в пикселях',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'height',
            'entry' => '13',
        ]);
        field('element', 'bodyClass', [
            'title' => 'Css класс для body',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'width',
            'entry' => '13',
        ]);
        field('element', 'contentsCss', [
            'title' => 'Путь к css-нику для подцепки редактором',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'bodyClass',
            'entry' => '13',
        ]);
        field('element', 'style', [
            'title' => 'Стили',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'contentsCss',
            'entry' => '13',
        ]);
        field('element', 'contentsJs', [
            'title' => 'Путь к js-нику для подцепки редактором',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'style',
            'entry' => '13',
        ]);
        field('element', 'script', [
            'title' => 'Скрипт',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'contentsJs',
            'entry' => '13',
        ]);
        field('element', 'sourceStripper', [
            'title' => 'Скрипт обработки исходного кода',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'script',
            'entry' => '13',
        ]);
        field('element', 'appendFieldTitle', [
            'title' => 'Включать наименование поля в имя файла при загрузке',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
            'move' => 'sourceStripper',
            'entry' => '14',
        ]);
        field('element', 'prependEntityTitle', [
            'title' => 'Включать наименование сущности в имя файла при download-е',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
            'move' => 'appendFieldTitle',
            'entry' => '14',
        ]);
        field('element', 'maxlength', [
            'title' => 'Максимальная длина в символах',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '5',
            'move' => 'prependEntityTitle',
            'entry' => '18',
        ]);
        field('element', 'measure', [
            'title' => 'Единица измерения',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'maxlength',
            'entry' => '18',
        ]);
        field('element', 'displayTimeFormat', [
            'title' => 'Отображаемый формат времени',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'H:i',
            'move' => 'measure',
            'entry' => '19',
        ]);
        field('element', 'displayDateFormat', [
            'title' => 'Отображаемый формат даты',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Y-m-d',
            'move' => 'displayTimeFormat',
            'entry' => '19',
        ]);
        field('element', 'vtype', [
            'title' => 'Валидация',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'displayDateFormat',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'entry' => '1',
        ]);
        cfgEnumset('element', 'string', 'vtype', 'none', ['title' => 'Нет', 'move' => '']);
        cfgEnumset('element', 'string', 'vtype', 'email', ['title' => 'Email', 'move' => 'none']);
        cfgEnumset('element', 'string', 'vtype', 'url', ['title' => 'URL', 'move' => 'email']);
        field('element', 'allowTypes', [
            'title' => 'Допустимые типы',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'vtype',
            'tooltip' => 'Укажите список расширений и/или группы расширений, через запятую: 
- image: gif,png,jpeg,jpg
- office: doc,pdf,docx,xls,xlsx,txt,odt,ppt,pptx
- draw: psd,ai,cdr
- archive: zip,rar,7z,gz,tar',
            'entry' => '14',
        ]);
        field('element', 'when', [
            'title' => 'Когда',
            'columnTypeId' => 'SET',
            'elementId' => 'combo',
            'move' => 'allowTypes',
            'relation' => 'enumset',
            'storeRelationAbility' => 'many',
            'entry' => '19',
        ]);
        cfgEnumset('element', 'datetime', 'when', 'month', ['title' => 'Месяц', 'move' => '']);
        cfgEnumset('element', 'datetime', 'when', 'week', ['title' => 'День недели', 'move' => 'month']);
        cfgField('element', 'upload', 'maxSize', [
            'title' => 'Максимальный размер',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => '5M',
            'move' => 'prependEntityTitle',
        ]);
        cfgField('element', 'upload', 'minSize', [
            'title' => 'Минимальный размер',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'maxSize',
        ]);        
        alteredField('fields', 'entry', ['mode' => 'hidden']);
        section('params', ['extendsPhp' => 'Indi_Controller_Admin_CfgValue']);
        section('fields', ['filter' => '`entry` = "0"']);
        grid('params', 'possibleParamId', ['toggle' => 'h']);
        grid('params', 'value', ['toggle' => 'h']);
        grid('params', 'cfgField', true);
        grid('params', 'cfgValue', ['editor' => 1]);
        section('controlElements', ['toggle' => 'y']);
        section('elementCfgField', [
            'sectionId' => 'controlElements',
            'entityId' => 'field',
            'title' => 'Возможные настройки',
            'rowsOnPage' => '100',
            'extendsPhp' => 'Indi_Controller_Admin_CfgField',
            'defaultSortField' => 'move',
            'type' => 's',
            'parentSectionConnector' => 'entry',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
            'extendsJs' => 'Indi.lib.controller.Field',
            'multiSelect' => '1',
        ]);
        section2action('elementCfgField','index', ['profileIds' => '1']);
        section2action('elementCfgField','form', ['profileIds' => '1']);
        section2action('elementCfgField','save', ['profileIds' => '1']);
        section2action('elementCfgField','delete', ['profileIds' => '1']);
        section2action('elementCfgField','up', ['profileIds' => '1']);
        section2action('elementCfgField','down', ['profileIds' => '1']);
        section2action('elementCfgField','activate', ['profileIds' => '1', 'rename' => 'Выбрать режим']);
        section2action('elementCfgField','export', ['profileIds' => '1']);
        grid('elementCfgField', 'title', ['editor' => 1]);
        grid('elementCfgField', 'alias', ['alterTitle' => 'Псевдоним', 'editor' => 1]);
        grid('elementCfgField', 'fk', true);
        grid('elementCfgField', 'storeRelationAbility', ['gridId' => 'fk']);
        grid('elementCfgField', 'relation', ['alterTitle' => 'Сущность', 'gridId' => 'fk', 'editor' => 1]);
        grid('elementCfgField', 'filter', ['alterTitle' => 'Фильтрация', 'gridId' => 'fk', 'editor' => 1]);
        grid('elementCfgField', 'el', ['alterTitle' => 'Элемент управления']);
        grid('elementCfgField', 'mode', ['gridId' => 'el']);
        grid('elementCfgField', 'elementId', ['alterTitle' => 'Элемент', 'gridId' => 'el', 'editor' => 1]);
        grid('elementCfgField', 'tooltip', ['gridId' => 'el', 'editor' => 1]);
        grid('elementCfgField', 'mysql', true);
        grid('elementCfgField', 'columnTypeId', ['alterTitle' => 'Тип столбца', 'gridId' => 'mysql', 'editor' => 1]);
        grid('elementCfgField', 'defaultValue', ['alterTitle' => 'По умолчанию', 'gridId' => 'mysql', 'editor' => 1]);
        grid('elementCfgField', 'l10n', ['alterTitle' => 'l10n', 'gridId' => 'mysql']);
        grid('elementCfgField', 'move', true);
        alteredField('elementCfgField', 'entityId', ['defaultValue' => '4']);
        section('elementCfgFieldEnumset', [
            'sectionId' => 'elementCfgField',
            'entityId' => 'enumset',
            'title' => 'Возможные значения',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'defaultSortField' => 'move',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('elementCfgFieldEnumset','index', ['profileIds' => '1']);
        section2action('elementCfgFieldEnumset','form', ['profileIds' => '1']);
        section2action('elementCfgFieldEnumset','save', ['profileIds' => '1']);
        section2action('elementCfgFieldEnumset','delete', ['profileIds' => '1']);
        section2action('elementCfgFieldEnumset','up', ['profileIds' => '1']);
        section2action('elementCfgFieldEnumset','down', ['profileIds' => '1']);
        section2action('elementCfgFieldEnumset','export', ['profileIds' => '1']);
        grid('elementCfgFieldEnumset', 'title', ['editor' => 1]);
        grid('elementCfgFieldEnumset', 'alias', ['editor' => 1]);
        grid('elementCfgFieldEnumset', 'move', true);
        section('paramsAll', [
            'sectionId' => 'configuration',
            'entityId' => 'param',
            'title' => 'Все параметры',
            'extendsPhp' => 'Indi_Controller_Admin_CfgValue',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('paramsAll','index', ['profileIds' => '1']);
        section2action('paramsAll','form', ['profileIds' => '1']);
        section2action('paramsAll','save', ['profileIds' => '1']);
        section2action('paramsAll','delete', ['profileIds' => '1']);
        grid('paramsAll', 'fieldId', true);
        grid('paramsAll', 'possibleParamId', true);
        grid('paramsAll', 'value', true);
        grid('paramsAll', 'title', true);
        grid('paramsAll', 'cfgField', true);
        grid('paramsAll', 'cfgValue', true);
        filter('paramsAll', 'fieldId', 'entityId', true);
        grid('paramsAll', 'fieldId', 'entityId', ['move' => '']);
        die('ok');
    }
    public function cfgFieldImportAction() {

        foreach (m('param')->fetchAll() as $paramR) {
            $possibleR = $paramR->foreign('possibleParamId');
            if ($cfgField = m('Field')->fetchRow([
                '`entityId` = "' . entity('element')->id . '"',
                '`entry` = "' . $possibleR->elementId . '"',
                '`alias` = "' . $possibleR->alias . '"'
            ])) {
                $paramR->cfgField = $cfgField->id;
                $paramR->basicUpdate();
            }
        }

        foreach (m('param')->fetchAll() as $paramR) {
            $param = $paramR->foreign('possibleParamId')->alias;
            $rel = $paramR->foreign('fieldId')->rel();
            if (in($param, 'groupBy,titleColumn')) $paramR->cfgValue = $rel->fields($paramR->value)->id;
            else if ($param == 'optionAttrs')
                $paramR->cfgValue = $rel->fields()->select($paramR->value, 'alias')->column('id', true);
            else if ($param == 'vtype') $paramR->cfgValue = $paramR->value == '' ? 'none' : $paramR->value;
            else if ($param == 'when') $paramR->cfgValue = $paramR->value;
            else if (in($param, 'wide,noLookup,appendFieldTitle,prependEntityTitle,refreshL10nsOnUpdate,shade'))
                $paramR->cfgValue = in($paramR->value, 'true,1') ? 1 : 0;
            else $paramR->cfgValue = $paramR->value;
            $paramR->save();
        }
        param('field', 'entityId', 'groupBy', ['cfgValue' => '612']);
        die('ok');
    }
    public function rowReqIfAffectedAction() {
        field('grid', 'rowReqIfAffected', [
          'title' => 'При изменении ячейки обновлять всю строку',
          'columnTypeId' => 'ENUM',
          'elementId' => 'combo',
          'defaultValue' => 'n',
          'move' => 'profileIds',
          'relation' => 'enumset',
          'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'rowReqIfAffected', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('grid', 'rowReqIfAffected', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span> Да', 'move' => 'n']);
        grid('grid', 'rowReqIfAffected', true);
        section('grid', ['multiSelect' => '1']);
        grid('sections', 'extendsPhp', ['rowReqIfAffected' => 'y']);
        grid('sections', 'extendsJs', ['rowReqIfAffected' => 'y']);
        grid('sections', 'extendsJs', ['rowReqIfAffected' => 'y']);
        grid('sections', 'defaultSortDirection', ['rowReqIfAffected' => 'y']);
        die('ok');
    }
    public function syncSectionsAction() {
        m('Section')->fetchAll('`type` = "s"')->delete();
        section('configuration', ['title' => 'Конфигурация', 'type' => 's']);
        section('sections', [
            'sectionId' => 'configuration',
            'entityId' => 'section',
            'title' => 'Разделы',
            'rowsOnPage' => '50',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'defaultSortField' => 'move',
            'type' => 's',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('sections','index', ['profileIds' => '1']);
        section2action('sections','form', ['profileIds' => '1']);
        section2action('sections','save', ['profileIds' => '1']);
        section2action('sections','delete', ['profileIds' => '1']);
        section2action('sections','up', ['profileIds' => '1']);
        section2action('sections','down', ['profileIds' => '1']);
        section2action('sections','toggle', ['profileIds' => '1']);
        section2action('sections','php', ['profileIds' => '1']);
        section2action('sections','js', ['profileIds' => '1']);
        section2action('sections','export', ['profileIds' => '1']);
        section2action('sections','copy', ['profileIds' => '1']);
        grid('sections', 'title', ['editor' => 1]);
        grid('sections', 'move', ['editor' => 1]);
        grid('sections', 'params', ['alterTitle' => 'Свойства']);
        grid('sections', 'toggle', ['gridId' => 'params']);
        grid('sections', 'type', ['gridId' => 'params', 'editor' => 1]);
        grid('sections', 'extends', ['alterTitle' => 'Привязка к коду', 'gridId' => 'params']);
        grid('sections', 'alias', ['gridId' => 'extends', 'editor' => 1]);
        grid('sections', 'extendsPhp', [
            'gridId' => 'extends',
            'editor' => 1,
            'width' => 50,
            'rowReqIfAffected' => 'y',
        ]);
        grid('sections', 'extendsJs', [
            'gridId' => 'extends',
            'editor' => 1,
            'width' => 50,
            'rowReqIfAffected' => 'y',
        ]);
        grid('sections', 'store', true);
        grid('sections', 'data', ['alterTitle' => 'Источник', 'gridId' => 'store']);
        grid('sections', 'entityId', ['alterTitle' => 'Сущность', 'gridId' => 'data']);
        grid('sections', 'filter', ['gridId' => 'data', 'editor' => 1]);
        grid('sections', 'disableAdd', ['gridId' => 'data']);
        grid('sections', 'load', ['gridId' => 'store']);
        grid('sections', 'rowsetSeparate', ['gridId' => 'load', 'tooltip' => 'Режим подгрузки данных']);
        grid('sections', 'defaultSortField', ['gridId' => 'load', 'editor' => 1]);
        grid('sections', 'rowsOnPage', ['gridId' => 'load', 'editor' => 1]);
        grid('sections', 'defaultSortDirection', ['toggle' => 'h', 'gridId' => 'load', 'rowReqIfAffected' => 'y']);
        grid('sections', 'display', ['alterTitle' => 'Отображение', 'gridId' => 'store']);
        grid('sections', 'multiSelect', ['gridId' => 'display']);
        grid('sections', 'rownumberer', ['gridId' => 'display']);
        grid('sections', 'groupBy', ['gridId' => 'display', 'editor' => 1]);
        grid('sections', 'tileField', ['toggle' => 'h', 'gridId' => 'display']);
        grid('sections', 'tileThumb', ['toggle' => 'h', 'gridId' => 'display']);
        filter('sections', 'type', true);
        filter('sections', 'entityId', true);
        filter('sections', 'toggle', true);
        filter('sections', 'roleIds', true);
        filter('sections', 'rowsetSeparate', true);
        section('sectionActions', [
            'sectionId' => 'sections',
            'entityId' => 'section2action',
            'title' => 'Действия',
            'extendsPhp' => 'Indi_Controller_Admin_Multinew',
            'defaultSortField' => 'move',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('sectionActions','index', ['profileIds' => '1']);
        section2action('sectionActions','form', ['profileIds' => '1']);
        section2action('sectionActions','save', ['profileIds' => '1']);
        section2action('sectionActions','delete', ['profileIds' => '1']);
        section2action('sectionActions','up', ['profileIds' => '1']);
        section2action('sectionActions','down', ['profileIds' => '1']);
        section2action('sectionActions','toggle', ['profileIds' => '1']);
        section2action('sectionActions','export', ['profileIds' => '1']);
        grid('sectionActions', 'actionId', true);
        grid('sectionActions', 'rename', true);
        grid('sectionActions', 'profileIds', true);
        grid('sectionActions', 'toggle', true);
        grid('sectionActions', 'south', ['alterTitle' => 'ЮП', 'tooltip' => 'Режим отображения южной панели']);
        grid('sectionActions', 'fitWindow', true);
        grid('sectionActions', 'l10n', true);
        grid('sectionActions', 'move', true);
        section('grid', [
            'sectionId' => 'sections',
            'entityId' => 'grid',
            'title' => 'Столбцы грида',
            'extendsPhp' => 'Indi_Controller_Admin_Multinew',
            'defaultSortField' => 'move',
            'type' => 's',
            'groupBy' => 'group',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('grid','index', ['profileIds' => '1']);
        section2action('grid','form', ['profileIds' => '1']);
        section2action('grid','save', ['profileIds' => '1']);
        section2action('grid','delete', ['profileIds' => '1']);
        section2action('grid','up', ['profileIds' => '1']);
        section2action('grid','down', ['profileIds' => '1']);
        section2action('grid','toggle', ['profileIds' => '1']);
        section2action('grid','export', ['profileIds' => '1']);
        grid('grid', 'title', ['alterTitle' => 'Столбец']);
        grid('grid', 'fieldId', ['editor' => 1]);
        grid('grid', 'move', true);
        grid('grid', 'further', ['editor' => 1]);
        grid('grid', 'display', true);
        grid('grid', 'toggle', ['gridId' => 'display']);
        grid('grid', 'editor', ['gridId' => 'display']);
        grid('grid', 'alterTitle', ['gridId' => 'display', 'editor' => 1]);
        grid('grid', 'group', ['gridId' => 'display']);
        grid('grid', 'tooltip', ['gridId' => 'display', 'editor' => 1]);
        grid('grid', 'width', ['gridId' => 'display', 'editor' => 1]);
        grid('grid', 'summaryType', ['gridId' => 'display', 'editor' => 1]);
        grid('grid', 'accesss', true);
        grid('grid', 'access', ['alterTitle' => 'Кому', 'gridId' => 'accesss', 'editor' => 1]);
        grid('grid', 'profileIds', ['gridId' => 'accesss', 'editor' => 1]);
        grid('grid', 'rowReqIfAffected', true);
        section('alteredFields', [
            'sectionId' => 'sections',
            'entityId' => 'alteredField',
            'title' => 'Измененные поля',
            'extendsPhp' => 'Indi_Controller_Admin_Multinew',
            'defaultSortField' => 'fieldId',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('alteredFields','index', ['profileIds' => '1']);
        section2action('alteredFields','form', ['profileIds' => '1']);
        section2action('alteredFields','save', ['profileIds' => '1']);
        section2action('alteredFields','delete', ['profileIds' => '1']);
        section2action('alteredFields','export', ['profileIds' => '1']);
        grid('alteredFields', 'fieldId', true);
        grid('alteredFields', 'alter', true);
        grid('alteredFields', 'rename', ['gridId' => 'alter', 'editor' => 1]);
        grid('alteredFields', 'mode', ['gridId' => 'alter']);
        grid('alteredFields', 'elementId', ['gridId' => 'alter', 'editor' => 1]);
        grid('alteredFields', 'defaultValue', ['gridId' => 'alter', 'editor' => 1]);
        grid('alteredFields', 'impactt', true);
        grid('alteredFields', 'impact', ['alterTitle' => 'Роли', 'gridId' => 'impactt', 'editor' => 1]);
        grid('alteredFields', 'profileIds', ['gridId' => 'impactt', 'editor' => 1]);
        section('search', [
            'sectionId' => 'sections',
            'entityId' => 'search',
            'title' => 'Фильтры',
            'extendsPhp' => 'Indi_Controller_Admin_Multinew',
            'defaultSortField' => 'move',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('search','index', ['profileIds' => '1']);
        section2action('search','form', ['profileIds' => '1']);
        section2action('search','save', ['profileIds' => '1']);
        section2action('search','delete', ['profileIds' => '1']);
        section2action('search','up', ['profileIds' => '1']);
        section2action('search','down', ['profileIds' => '1']);
        section2action('search','toggle', ['profileIds' => '1']);
        section2action('search','export', ['profileIds' => '1']);
        grid('search', 'fieldId', ['editor' => 1]);
        grid('search', 'further', ['editor' => 1]);
        grid('search', 'filter', ['editor' => 1]);
        grid('search', 'defaultValue', ['alterTitle' => 'Значение<br>по умолчанию', 'editor' => 1]);
        grid('search', 'display', true);
        grid('search', 'toggle', ['gridId' => 'display']);
        grid('search', 'move', ['gridId' => 'display']);
        grid('search', 'alt', ['gridId' => 'display', 'editor' => 1]);
        grid('search', 'tooltip', ['gridId' => 'display', 'editor' => 1]);
        grid('search', 'accesss', true);
        grid('search', 'access', ['gridId' => 'accesss', 'editor' => 1]);
        grid('search', 'profileIds', ['gridId' => 'accesss', 'editor' => 1]);
        grid('search', 'flags', true);
        grid('search', 'allowClear', ['alterTitle' => 'РС', 'gridId' => 'flags', 'editor' => 1]);
        grid('search', 'ignoreTemplate', ['alterTitle' => 'ИШ', 'gridId' => 'flags', 'editor' => 1]);
        grid('search', 'consistence', ['alterTitle' => 'НР', 'gridId' => 'flags', 'editor' => 1]);
        section('entities', [
            'sectionId' => 'configuration',
            'entityId' => 'entity',
            'title' => 'Сущности',
            'rowsOnPage' => '50',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'defaultSortField' => 'title',
            'type' => 's',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('entities','index', ['profileIds' => '1']);
        section2action('entities','form', ['profileIds' => '1']);
        section2action('entities','save', ['profileIds' => '1']);
        section2action('entities','delete', ['profileIds' => '1']);
        section2action('entities','toggle', ['profileIds' => '1']);
        section2action('entities','php', ['profileIds' => '1']);
        section2action('entities','export', ['profileIds' => '1']);
        grid('entities', 'title', ['editor' => 1]);
        grid('entities', 'table', ['editor' => 1]);
        grid('entities', 'system', true);
        grid('entities', 'filesGroupBy', ['editor' => 1]);
        filter('entities', 'system', true);
        filter('entities', 'useCache', ['toggle' => 'n']);
        filter('entities', 'spaceScheme', true);
        section('fields', [
            'sectionId' => 'entities',
            'entityId' => 'field',
            'title' => 'Поля в структуре',
            'rowsOnPage' => '100',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'defaultSortField' => 'move',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('fields','index', ['profileIds' => '1']);
        section2action('fields','form', ['profileIds' => '1']);
        section2action('fields','save', ['profileIds' => '1']);
        section2action('fields','delete', ['profileIds' => '1']);
        section2action('fields','up', ['profileIds' => '1']);
        section2action('fields','down', ['profileIds' => '1']);
        section2action('fields','activate', ['profileIds' => '1', 'rename' => 'Выбрать режим']);
        section2action('fields','export', ['profileIds' => '1']);
        grid('fields', 'title', ['editor' => 1]);
        grid('fields', 'alias', ['alterTitle' => 'Псевдоним', 'editor' => 1]);
        grid('fields', 'fk', true);
        grid('fields', 'storeRelationAbility', ['gridId' => 'fk']);
        grid('fields', 'relation', ['alterTitle' => 'Сущность', 'gridId' => 'fk', 'editor' => 1]);
        grid('fields', 'filter', ['alterTitle' => 'Фильтрация', 'gridId' => 'fk', 'editor' => 1]);
        grid('fields', 'el', ['alterTitle' => 'Элемент управления']);
        grid('fields', 'mode', ['gridId' => 'el']);
        grid('fields', 'elementId', ['alterTitle' => 'Элемент', 'gridId' => 'el', 'editor' => 1]);
        grid('fields', 'tooltip', ['gridId' => 'el', 'editor' => 1]);
        grid('fields', 'mysql', true);
        grid('fields', 'columnTypeId', ['alterTitle' => 'Тип столбца', 'gridId' => 'mysql', 'editor' => 1]);
        grid('fields', 'defaultValue', ['alterTitle' => 'По умолчанию', 'gridId' => 'mysql', 'editor' => 1]);
        grid('fields', 'l10n', ['alterTitle' => 'l10n', 'gridId' => 'mysql']);
        grid('fields', 'move', true);
        section('enumset', [
            'sectionId' => 'fields',
            'entityId' => 'enumset',
            'title' => 'Возможные значения',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'defaultSortField' => 'move',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('enumset','index', ['profileIds' => '1']);
        section2action('enumset','form', ['profileIds' => '1']);
        section2action('enumset','save', ['profileIds' => '1']);
        section2action('enumset','delete', ['profileIds' => '1']);
        section2action('enumset','up', ['profileIds' => '1']);
        section2action('enumset','down', ['profileIds' => '1']);
        section2action('enumset','export', ['profileIds' => '1']);
        grid('enumset', 'title', true);
        grid('enumset', 'alias', true);
        grid('enumset', 'move', true);
        section('resize', [
            'sectionId' => 'fields',
            'entityId' => 'resize',
            'title' => 'Копии изображения',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('resize','index', ['profileIds' => '1']);
        section2action('resize','form', ['profileIds' => '1']);
        section2action('resize','save', ['profileIds' => '1']);
        section2action('resize','delete', ['profileIds' => '1']);
        section2action('resize','export', ['profileIds' => '1']);
        grid('resize', 'title', true);
        grid('resize', 'alias', true);
        grid('resize', 'proportions', true);
        grid('resize', 'masterDimensionValue', true);
        grid('resize', 'slaveDimensionValue', true);
        grid('resize', 'slaveDimensionLimitation', true);
        section('params', [
            'sectionId' => 'fields',
            'entityId' => 'param',
            'title' => 'Параметры',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('params','index', ['profileIds' => '1']);
        section2action('params','form', ['profileIds' => '1']);
        section2action('params','save', ['profileIds' => '1']);
        section2action('params','delete', ['profileIds' => '1']);
        section2action('params','export', ['profileIds' => '1']);
        grid('params', 'possibleParamId', true);
        grid('params', 'value', true);
        section('consider', [
            'sectionId' => 'fields',
            'entityId' => 'consider',
            'title' => 'Зависимости',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('consider','index', ['profileIds' => '1']);
        section2action('consider','form', ['profileIds' => '1']);
        section2action('consider','save', ['profileIds' => '1']);
        section2action('consider','delete', ['profileIds' => '1']);
        section2action('consider','export', ['profileIds' => '1']);
        grid('consider', 'consider', true);
        grid('consider', 'foreign', true);
        grid('consider', 'required', ['alterTitle' => '[ ! ]', 'tooltip' => 'Обязательное']);
        grid('consider', 'connector', true);
        section('profiles', [
            'sectionId' => 'configuration',
            'entityId' => 'profile',
            'title' => 'Роли',
            'defaultSortField' => 'move',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('profiles','index', ['profileIds' => '1']);
        section2action('profiles','form', ['profileIds' => '1']);
        section2action('profiles','save', ['profileIds' => '1']);
        section2action('profiles','delete', ['profileIds' => '1']);
        section2action('profiles','up', ['profileIds' => '1']);
        section2action('profiles','down', ['profileIds' => '1']);
        section2action('profiles','toggle', ['profileIds' => '1']);
        grid('profiles', 'title', ['editor' => 1]);
        grid('profiles', 'type', true);
        grid('profiles', 'toggle', true);
        grid('profiles', 'maxWindows', ['alterTitle' => 'МКО', 'tooltip' => 'Максимальное количество окон', 'editor' => 1]);
        grid('profiles', 'demo', ['alterTitle' => 'Демо', 'tooltip' => 'Демо-режим']);
        grid('profiles', 'entityId', ['editor' => 1]);
        grid('profiles', 'dashboard', ['editor' => 1]);
        grid('profiles', 'move', true);
        section('admins', [
            'sectionId' => 'profiles',
            'entityId' => 'admin',
            'title' => 'Пользователи',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
        ]);
        section2action('admins','index', ['profileIds' => '1']);
        section2action('admins','form', ['profileIds' => '1']);
        section2action('admins','save', ['profileIds' => '1']);
        section2action('admins','delete', ['profileIds' => '1']);
        section2action('admins','toggle', ['profileIds' => '1']);
        section2action('admins','login', ['profileIds' => '1']);
        grid('admins', 'title', ['editor' => 1]);
        grid('admins', 'email', ['editor' => 1]);
        grid('admins', 'password', ['editor' => 1]);
        grid('admins', 'toggle', true);
        grid('admins', 'demo', ['alterTitle' => 'Демо', 'tooltip' => 'Демо-режим']);
        grid('admins', 'uiedit', true);
        section('columnTypes', [
            'sectionId' => 'configuration',
            'entityId' => 'columnType',
            'title' => 'Столбцы',
            'toggle' => 'n',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('columnTypes','index', ['profileIds' => '1']);
        section2action('columnTypes','form', ['profileIds' => '1']);
        section2action('columnTypes','save', ['profileIds' => '1']);
        section2action('columnTypes','delete', ['toggle' => 'n', 'profileIds' => '1']);
        grid('columnTypes', 'title', true);
        grid('columnTypes', 'type', true);
        grid('columnTypes', 'canStoreRelation', true);
        grid('columnTypes', 'elementId', ['editor' => 1]);
        section('actions', [
            'sectionId' => 'configuration',
            'entityId' => 'action',
            'title' => 'Действия',
            'toggle' => 'n',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'type' => 's',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('actions','index', ['profileIds' => '1']);
        section2action('actions','form', ['profileIds' => '1']);
        section2action('actions','save', ['profileIds' => '1']);
        section2action('actions','toggle', ['profileIds' => '1']);
        section2action('actions','delete', ['profileIds' => '1']);
        section2action('actions','export', ['profileIds' => '1']);
        grid('actions', 'title', ['editor' => 1]);
        grid('actions', 'alias', ['editor' => 1]);
        grid('actions', 'type', ['editor' => 1]);
        grid('actions', 'rowRequired', ['editor' => 1]);
        grid('actions', 'display', ['editor' => 1]);
        grid('actions', 'toggle', true);
        filter('actions', 'type', true);
        filter('actions', 'toggle', true);
        filter('actions', 'rowRequired', true);
        filter('actions', 'display', true);
        section('controlElements', [
            'sectionId' => 'configuration',
            'entityId' => 'element',
            'title' => 'Элементы',
            'toggle' => 'n',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('controlElements','index', ['profileIds' => '1']);
        section2action('controlElements','form', ['profileIds' => '1']);
        section2action('controlElements','save', ['profileIds' => '1']);
        section2action('controlElements','delete', ['toggle' => 'n', 'profileIds' => '1']);
        grid('controlElements', 'title', true);
        grid('controlElements', 'alias', true);
        grid('controlElements', 'storeRelationAbility', true);
        grid('controlElements', 'hidden', true);
        section('possibleParams', [
            'sectionId' => 'controlElements',
            'entityId' => 'possibleElementParam',
            'title' => 'Возможные параметры',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('possibleParams','index', ['profileIds' => '1']);
        section2action('possibleParams','form', ['profileIds' => '1']);
        section2action('possibleParams','save', ['profileIds' => '1']);
        section2action('possibleParams','delete', ['profileIds' => '1']);
        grid('possibleParams', 'title', true);
        grid('possibleParams', 'alias', true);
        grid('possibleParams', 'defaultValue', true);
        section('lang', [
            'sectionId' => 'configuration',
            'entityId' => 'lang',
            'title' => 'Языки',
            'type' => 's',
            'groupBy' => 'state',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('lang','index', ['profileIds' => '1']);
        section2action('lang','form', ['profileIds' => '1']);
        section2action('lang','save', ['profileIds' => '1']);
        section2action('lang','delete', ['profileIds' => '1']);
        section2action('lang','up', ['profileIds' => '1']);
        section2action('lang','down', ['profileIds' => '1']);
        section2action('lang','dict', ['profileIds' => '1']);
        section2action('lang','wordings', ['profileIds' => '1']);
        grid('lang', 'title', true);
        grid('lang', 'alias', true);
        grid('lang', 'admin', true);
        grid('lang', 'toggle', ['gridId' => 'admin']);
        grid('lang', 'adminSystem', ['gridId' => 'admin']);
        grid('lang', 'adminSystemUi', ['gridId' => 'adminSystem']);
        grid('lang', 'adminSystemConst', ['gridId' => 'adminSystem']);
        grid('lang', 'adminCustom', ['gridId' => 'admin']);
        grid('lang', 'adminCustomUi', ['gridId' => 'adminCustom']);
        grid('lang', 'adminCustomConst', ['gridId' => 'adminCustom']);
        grid('lang', 'adminCustomData', ['gridId' => 'adminCustom']);
        grid('lang', 'adminCustomTmpl', ['gridId' => 'adminCustom']);
        grid('lang', 'move', true);
        filter('lang', 'state', ['defaultValue' => 'smth']);
        filter('lang', 'toggle', true);
        section('notices', [
            'sectionId' => 'configuration',
            'entityId' => 'notice',
            'title' => 'Уведомления',
            'defaultSortField' => 'title',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('notices','index', ['profileIds' => '1']);
        section2action('notices','form', ['profileIds' => '1']);
        section2action('notices','save', ['profileIds' => '1']);
        section2action('notices','delete', ['profileIds' => '1']);
        section2action('notices','toggle', ['profileIds' => '1']);
        grid('notices', 'title', true);
        grid('notices', 'entityId', true);
        grid('notices', 'profileId', true);
        grid('notices', 'toggle', true);
        grid('notices', 'qty', true);
        grid('notices', 'qtySql', ['gridId' => 'qty']);
        grid('notices', 'event', ['gridId' => 'qty']);
        grid('notices', 'sectionId', ['gridId' => 'qty']);
        grid('notices', 'bg', ['gridId' => 'qty']);
        grid('notices', 'fg', ['gridId' => 'qty']);
        section('noticeGetters', [
            'sectionId' => 'notices',
            'entityId' => 'noticeGetter',
            'title' => 'Получатели',
            'defaultSortField' => 'profileId',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('noticeGetters','index', ['profileIds' => '1']);
        section2action('noticeGetters','form', ['profileIds' => '1']);
        section2action('noticeGetters','save', ['profileIds' => '1']);
        section2action('noticeGetters','delete', ['profileIds' => '1']);
        grid('noticeGetters', 'toggle', true);
        grid('noticeGetters', 'profileId', true);
        grid('noticeGetters', 'criteriaEvt', true);
        grid('noticeGetters', 'email', ['alterTitle' => 'Email', 'tooltip' => 'Дублирование на почту']);
        grid('noticeGetters', 'vk', ['alterTitle' => 'VK', 'tooltip' => 'Дублирование во ВКонтакте']);
        grid('noticeGetters', 'sms', ['alterTitle' => 'SMS', 'tooltip' => 'Дублирование по SMS']);
        section('fieldsAll', [
            'sectionId' => 'configuration',
            'entityId' => 'field',
            'title' => 'Все поля',
            'disableAdd' => '1',
            'type' => 's',
            'groupBy' => 'entityId',
            'roleIds' => '1',
        ]);
        section2action('fieldsAll','index', ['profileIds' => '1']);
        section2action('fieldsAll','form', ['profileIds' => '1']);
        section2action('fieldsAll','save', ['profileIds' => '1']);
        grid('fieldsAll', 'entityId', ['alterTitle' => 'Сущность', 'tooltip' => 'Сущность, в структуру которой входит это поле']);
        grid('fieldsAll', 'title', ['editor' => 1]);
        grid('fieldsAll', 'alias', ['alterTitle' => 'Псевдоним', 'editor' => 1]);
        grid('fieldsAll', 'fk', true);
        grid('fieldsAll', 'storeRelationAbility', ['gridId' => 'fk']);
        grid('fieldsAll', 'relation', ['alterTitle' => 'Сущность', 'gridId' => 'fk', 'editor' => 1]);
        grid('fieldsAll', 'filter', ['alterTitle' => 'Фильтрация', 'gridId' => 'fk', 'editor' => 1]);
        grid('fieldsAll', 'el', true);
        grid('fieldsAll', 'mode', ['gridId' => 'el']);
        grid('fieldsAll', 'elementId', ['alterTitle' => 'Элемент', 'gridId' => 'el', 'editor' => 1]);
        grid('fieldsAll', 'tooltip', ['gridId' => 'el', 'editor' => 1]);
        grid('fieldsAll', 'mysql', true);
        grid('fieldsAll', 'columnTypeId', ['alterTitle' => 'Тип столбца', 'gridId' => 'mysql', 'editor' => 1]);
        grid('fieldsAll', 'defaultValue', ['alterTitle' => 'По умолчанию', 'gridId' => 'mysql', 'editor' => 1]);
        grid('fieldsAll', 'l10n', ['alterTitle' => 'l10n', 'gridId' => 'mysql', 'tooltip' => 'Мультиязычность']);
        grid('fieldsAll', 'move', true);
        filter('fieldsAll', 'entityId', 'system', true);
        filter('fieldsAll', 'entityId', ['alt' => 'Сущность']);
        filter('fieldsAll', 'columnTypeId', true);
        filter('fieldsAll', 'l10n', true);
        filter('fieldsAll', 'storeRelationAbility', true);
        filter('fieldsAll', 'relation', true);
        filter('fieldsAll', 'mode', true);
        filter('fieldsAll', 'elementId', ['alt' => 'Элемент']);
        section('queueTask', [
            'sectionId' => 'configuration',
            'entityId' => 'queueTask',
            'title' => 'Очереди задач',
            'defaultSortField' => 'datetime',
            'defaultSortDirection' => 'DESC',
            'disableAdd' => '1',
            'type' => 's',
            'groupBy' => 'stageState',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('queueTask','index', ['profileIds' => '1']);
        section2action('queueTask','form', ['profileIds' => '1']);
        section2action('queueTask','delete', ['profileIds' => '1']);
        section2action('queueTask','run', ['profileIds' => '1']);
        grid('queueTask', 'datetime', true);
        grid('queueTask', 'title', true);
        grid('queueTask', 'params', true);
        grid('queueTask', 'stage', ['toggle' => 'h']);
        grid('queueTask', 'state', ['toggle' => 'h']);
        grid('queueTask', 'chunk', true);
        grid('queueTask', 'proc', true);
        grid('queueTask', 'procID', ['gridId' => 'proc']);
        grid('queueTask', 'procSince', ['gridId' => 'proc']);
        grid('queueTask', 'count', true);
        grid('queueTask', 'countState', ['gridId' => 'count']);
        grid('queueTask', 'countSize', ['gridId' => 'count']);
        grid('queueTask', 'items', true);
        grid('queueTask', 'itemsState', ['gridId' => 'items']);
        grid('queueTask', 'itemsSize', ['gridId' => 'items']);
        grid('queueTask', 'itemsBytes', ['gridId' => 'items', 'summaryType' => 'sum']);
        grid('queueTask', 'queue', true);
        grid('queueTask', 'queueState', ['gridId' => 'queue']);
        grid('queueTask', 'queueSize', ['gridId' => 'queue']);
        grid('queueTask', 'apply', true);
        grid('queueTask', 'applyState', ['gridId' => 'apply']);
        grid('queueTask', 'applySize', ['gridId' => 'apply']);
        section('queueChunk', [
            'sectionId' => 'queueTask',
            'entityId' => 'queueChunk',
            'title' => 'Сегменты очереди',
            'defaultSortField' => 'move',
            'disableAdd' => '1',
            'type' => 's',
            'groupBy' => 'fraction',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
            'rownumberer' => '1',
        ]);
        section2action('queueChunk','index', ['profileIds' => '1']);
        section2action('queueChunk','form', ['profileIds' => '1']);
        grid('queueChunk', '', ['toggle' => 'n']);
        grid('queueChunk', '', ['toggle' => 'n']);
        grid('queueChunk', 'location', true);
        grid('queueChunk', 'where', true);
        grid('queueChunk', 'count', true);
        grid('queueChunk', 'countState', ['gridId' => 'count']);
        grid('queueChunk', 'countSize', ['gridId' => 'count', 'summaryType' => 'sum']);
        grid('queueChunk', 'items', true);
        grid('queueChunk', 'itemsState', ['gridId' => 'items']);
        grid('queueChunk', 'itemsSize', ['gridId' => 'items', 'summaryType' => 'sum']);
        grid('queueChunk', 'itemsBytes', ['gridId' => 'items', 'summaryType' => 'sum']);
        grid('queueChunk', 'queue', true);
        grid('queueChunk', 'queueState', ['gridId' => 'queue']);
        grid('queueChunk', 'queueSize', ['gridId' => 'queue', 'summaryType' => 'sum']);
        grid('queueChunk', 'apply', true);
        grid('queueChunk', 'applyState', ['gridId' => 'apply']);
        grid('queueChunk', 'applySize', ['gridId' => 'apply', 'summaryType' => 'sum']);
        section('queueItem', [
            'sectionId' => 'queueChunk',
            'entityId' => 'queueItem',
            'title' => 'Элементы очереди',
            'disableAdd' => '1',
            'type' => 's',
            'roleIds' => '1',
        ]);
        section2action('queueItem','index', ['profileIds' => '1']);
        section2action('queueItem','save', ['profileIds' => '1']);
        grid('queueItem', 'target', true);
        grid('queueItem', 'value', true);
        grid('queueItem', 'result', ['editor' => 1]);
        grid('queueItem', 'stage', true);
        alteredField('queueItem', 'result', ['elementId' => 'string']);
        filter('queueItem', 'stage', true);
        section('realtime', [
            'sectionId' => 'configuration',
            'entityId' => 'realtime',
            'title' => 'Рилтайм',
            'defaultSortField' => 'spaceSince',
            'type' => 's',
            'groupBy' => 'adminId',
            'roleIds' => '1',
            'multiSelect' => '1',
        ]);
        section2action('realtime','form', ['profileIds' => '1']);
        section2action('realtime','index', ['profileIds' => '1']);
        section2action('realtime','save', ['profileIds' => '1']);
        section2action('realtime','delete', ['profileIds' => '1']);
        section2action('realtime','restart', ['profileIds' => '1', 'rename' => 'Перезагрузить websocket-сервер']);
        grid('realtime', 'title', true);
        grid('realtime', 'token', ['toggle' => 'h']);
        grid('realtime', 'sectionId', ['toggle' => 'h']);
        grid('realtime', 'type', ['toggle' => 'h']);
        grid('realtime', 'profileId', ['toggle' => 'h']);
        grid('realtime', 'adminId', true);
        grid('realtime', 'spaceSince', true);
        grid('realtime', 'spaceUntil', ['toggle' => 'h']);
        grid('realtime', 'spaceFrame', ['toggle' => 'h']);
        grid('realtime', 'langId', ['toggle' => 'h']);
        grid('realtime', 'entityId', ['toggle' => 'h']);
        grid('realtime', 'entries', ['toggle' => 'n']);
        grid('realtime', 'fields', ['toggle' => 'h']);
        filter('realtime', 'type', true);
        filter('realtime', 'profileId', true);
        filter('realtime', 'langId', true);
        filter('realtime', 'adminId', true);
        die('ok');
    }
    public function syncActionsAction() {
        action('index', [
            'title' => 'Список',
            'rowRequired' => 'n',
            'type' => 's',
            'display' => '0',
        ]);
        action('form', ['title' => 'Детали', 'type' => 's']);
        action('save', ['title' => 'Сохранить', 'type' => 's', 'display' => '0']);
        action('delete', ['title' => 'Удалить', 'type' => 's']);
        action('up', ['title' => 'Выше', 'type' => 's']);
        action('down', ['title' => 'Ниже', 'type' => 's']);
        action('toggle', ['title' => 'Статус', 'type' => 's']);
        action('cache', ['title' => 'Обновить кэш', 'type' => 's']);
        action('login', ['title' => 'Авторизация', 'type' => 's']);
        action('author', ['title' => 'Автор', 'type' => 's']);
        action('php', ['title' => 'PHP', 'type' => 's']);
        action('js', ['title' => 'JS', 'type' => 's']);
        action('export', ['title' => 'Экспорт', 'type' => 's']);
        action('goto', ['title' => 'Перейти', 'type' => 's']);
        action('rwu', ['rowRequired' => 'n', 'type' => 's', 'display' => '0']);
        action('activate', ['title' => 'Активировать', 'type' => 's']);
        action('dict', ['title' => 'Доступные языки', 'rowRequired' => 'n', 'type' => 's']);
        action('run', ['title' => 'Запустить', 'type' => 's']);
        action('chart', ['title' => 'График', 'type' => 's']);
        action('wordings', ['title' => 'Вординги', 'type' => 's']);
        action('restart', ['title' => 'Перезапустить', 'rowRequired' => 'n', 'type' => 's']);
        action('copy', ['title' => 'Копировать', 'type' => 's']);
        die('ok');
    }
    public function syncEntitiesAction() {
        entity('admin', ['title' => 'Администратор', 'system' => 'y']);
        field('admin', 'profileId', [
            'title' => 'Роль',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'move' => '',
            'relation' => 'profile',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('admin', 'title', [
            'title' => 'Имя',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'profileId',
            'mode' => 'required',
        ]);
        field('admin', 'email', [
            'title' => 'Логин',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('admin', 'password', [
            'title' => 'Пароль',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'email',
            'mode' => 'required',
        ]);
        field('admin', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'password',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('admin', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен', 'move' => '']);
        enumset('admin', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен', 'move' => 'y']);
        field('admin', 'demo', [
            'title' => 'Демо-режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'toggle',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('admin', 'demo', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('admin', 'demo', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да', 'move' => 'n']);
        field('admin', 'uiedit', [
            'title' => 'Правки UI',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'demo',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('admin', 'uiedit', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключено', 'move' => '']);
        enumset('admin', 'uiedit', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включено', 'move' => 'n']);
        entity('admin', ['titleFieldId' => 'title']);
        entity('possibleElementParam', ['title' => 'Возможный параметр', 'system' => 'y']);
        field('possibleElementParam', 'elementId', [
            'title' => 'Элемент управления',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'element',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('possibleElementParam', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'elementId',
            'mode' => 'required',
        ]);
        field('possibleElementParam', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('possibleElementParam', 'defaultValue', [
            'title' => 'Значение по умолчанию',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'alias',
        ]);
        entity('possibleElementParam', ['titleFieldId' => 'title']);
        entity('year', ['title' => 'Год', 'system' => 'y']);
        field('year', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        entity('year', ['titleFieldId' => 'title']);
        entity('action', ['title' => 'Действие', 'system' => 'y']);
        field('action', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('action', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('action', 'rowRequired', [
            'title' => 'Нужно выбрать запись',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'y',
            'move' => 'alias',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('action', 'rowRequired', 'y', ['title' => 'Да', 'move' => '']);
        enumset('action', 'rowRequired', 'n', ['title' => 'Нет', 'move' => 'y']);
        field('action', 'type', [
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'p',
            'move' => 'rowRequired',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('action', 'type', 'p', ['title' => 'Проектная', 'move' => '']);
        enumset('action', 'type', 's', ['title' => '<font color=red>Системная</font>', 'move' => 'p']);
        enumset('action', 'type', 'o', ['title' => '<font color=lime>Публичная</font>', 'move' => 's']);
        field('action', 'display', [
            'title' => 'Отображать в панели действий',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => '1',
            'move' => 'type',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('action', 'display', '0', ['title' => 'Нет', 'move' => '']);
        enumset('action', 'display', '1', ['title' => 'Да', 'move' => '0']);
        field('action', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'display',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('action', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включено', 'move' => '']);
        enumset('action', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключено', 'move' => 'y']);
        entity('action', ['titleFieldId' => 'title']);
        entity('section2action', ['title' => 'Действие в разделе', 'system' => 'y']);
        field('section2action', 'sectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'move' => '',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('section2action', 'actionId', [
            'title' => 'Действие',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'move' => 'sectionId',
            'relation' => 'action',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        field('section2action', 'profileIds', [
            'title' => 'Доступ',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'multicheck',
            'defaultValue' => '14',
            'move' => 'actionId',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
        ]);
        field('section2action', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'profileIds',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включено', 'move' => '']);
        enumset('section2action', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключено', 'move' => 'y']);
        field('section2action', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'move' => 'toggle',
        ]);
        field('section2action', 'title', [
            'title' => 'Auto title',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'move',
            'mode' => 'hidden',
        ]);
        consider('section2action', 'title', 'actionId', ['foreign' => 'title', 'required' => 'y']);
        field('section2action', 'rename', [
            'title' => 'Переименовать',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'title',
        ]);
        consider('section2action', 'rename', 'title', ['required' => 'y']);
        field('section2action', 'south', [
            'title' => 'Южная панель',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'auto',
            'move' => 'rename',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'south', 'auto', ['title' => '<span class="i-color-box" style="background: blue;"></span>Авто', 'move' => '']);
        enumset('section2action', 'south', 'yes', ['title' => '<span class="i-color-box" style="background: lime;"></span>Отображать', 'move' => 'auto']);
        enumset('section2action', 'south', 'no', ['title' => '<span class="i-color-box" style="background: red;"></span>Не отображать', 'move' => 'yes']);
        field('section2action', 'fitWindow', [
            'title' => 'Автосайз окна',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'auto',
            'move' => 'south',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'fitWindow', 'auto', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включено', 'move' => '']);
        enumset('section2action', 'fitWindow', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключено', 'move' => 'auto']);
        field('section2action', 'l10n', [
            'title' => 'Мультиязычность',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'fitWindow',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'l10n', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключена', 'move' => '']);
        enumset('section2action', 'l10n', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('section2action', 'l10n', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включена', 'move' => 'qy']);
        enumset('section2action', 'l10n', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        entity('section2action', ['titleFieldId' => 'actionId']);
        entity('consider', ['title' => 'Зависимость', 'system' => 'y']);
        field('consider', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
            'mode' => 'hidden',
        ]);
        field('consider', 'fieldId', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'entityId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('consider', 'consider', [
            'title' => 'От какого поля зависит',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'fieldId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`id` != "<?=$this->fieldId?>" AND `columnTypeId` != "0"',
            'mode' => 'required',
        ]);
        consider('consider', 'consider', 'fieldId', ['foreign' => 'entityId', 'required' => 'y']);
        field('consider', 'foreign', [
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'consider',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('consider', 'foreign', 'consider', ['foreign' => 'relation', 'required' => 'y', 'connector' => 'entityId']);
        field('consider', 'title', [
            'title' => 'Auto title',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'foreign',
            'mode' => 'hidden',
        ]);
        consider('consider', 'title', 'consider', ['foreign' => 'title', 'required' => 'y']);
        field('consider', 'required', [
            'title' => 'Обязательное',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'title',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('consider', 'required', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('consider', 'required', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Да', 'move' => 'n']);
        field('consider', 'connector', [
            'title' => 'Коннектор',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'required',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('consider', 'connector', 'fieldId', ['foreign' => 'relation', 'required' => 'y']);
        entity('consider', ['titleFieldId' => 'consider']);
        entity('enumset', ['title' => 'Значение из набора', 'system' => 'y']);
        field('enumset', 'fieldId', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('enumset', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'fieldId',
            'mode' => 'required',
        ]);
        field('enumset', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('enumset', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'alias',
        ]);
        entity('enumset', ['titleFieldId' => 'title']);
        entity('resize', ['title' => 'Копия', 'system' => 'y']);
        field('resize', 'fieldId', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        field('resize', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'fieldId',
            'mode' => 'required',
        ]);
        field('resize', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('resize', 'proportions', [
            'title' => 'Размер',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'o',
            'move' => 'alias',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('resize', 'proportions', 'p', ['title' => 'Поменять, но с сохранением пропорций', 'move' => '']);
        enumset('resize', 'proportions', 'c', ['title' => 'Поменять', 'move' => 'p']);
        enumset('resize', 'proportions', 'o', ['title' => 'Не менять', 'move' => 'c']);
        field('resize', 'masterDimensionAlias', [
            'title' => 'При расчете пропорций отталкиваться от',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'width',
            'move' => 'proportions',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('resize', 'masterDimensionAlias', 'width', ['title' => 'Ширины', 'move' => '']);
        enumset('resize', 'masterDimensionAlias', 'height', ['title' => 'Высоты', 'move' => 'width']);
        field('resize', 'masterDimensionValue', [
            'title' => 'Ширина',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'masterDimensionAlias',
        ]);
        param('resize', 'masterDimensionValue', 'measure', ['value' => 'px']);
        field('resize', 'slaveDimensionLimitation', [
            'title' => 'Ограничить пропорциональную <span id="slaveDimensionTitle">высоту</span>',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
            'move' => 'masterDimensionValue',
        ]);
        field('resize', 'slaveDimensionValue', [
            'title' => 'Высота',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'slaveDimensionLimitation',
        ]);
        param('resize', 'slaveDimensionValue', 'measure', ['value' => 'px']);
        field('resize', 'changeColor', [
            'title' => 'Изменить оттенок',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'slaveDimensionValue',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('resize', 'changeColor', 'y', ['title' => 'Да', 'move' => '']);
        enumset('resize', 'changeColor', 'n', ['title' => 'Нет', 'move' => 'y']);
        field('resize', 'color', [
            'title' => 'Оттенок',
            'columnTypeId' => 'VARCHAR(10)',
            'elementId' => 'color',
            'move' => 'changeColor',
        ]);
        entity('resize', ['titleFieldId' => 'title']);
        entity('changeLog', ['title' => 'Корректировка', 'system' => 'y']);
        field('changeLog', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('changeLog', 'key', [
            'title' => 'Объект',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'entityId',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        consider('changeLog', 'key', 'entityId', ['required' => 'y']);
        field('changeLog', 'fieldId', [
            'title' => 'Что изменено',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'key',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`columnTypeId` != "0"',
            'mode' => 'readonly',
        ]);
        consider('changeLog', 'fieldId', 'entityId', ['required' => 'y']);
        field('changeLog', 'was', [
            'title' => 'Было',
            'columnTypeId' => 'TEXT',
            'elementId' => 'html',
            'move' => 'fieldId',
            'mode' => 'readonly',
        ]);
        field('changeLog', 'now', [
            'title' => 'Стало',
            'columnTypeId' => 'TEXT',
            'elementId' => 'html',
            'move' => 'was',
            'mode' => 'readonly',
        ]);
        field('changeLog', 'datetime', [
            'title' => 'Когда',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
            'move' => 'now',
            'mode' => 'readonly',
        ]);
        field('changeLog', 'monthId', [
            'title' => 'Месяц',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'datetime',
            'relation' => 'month',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('changeLog', 'changerType', [
            'title' => 'Тип автора',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'monthId',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('changeLog', 'changerId', [
            'title' => 'Автор',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'changerType',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        consider('changeLog', 'changerId', 'changerType', ['required' => 'y']);
        field('changeLog', 'profileId', [
            'title' => 'Роль',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'changerId',
            'relation' => 'profile',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        entity('changeLog', ['titleFieldId' => 'datetime']);
        entity('month', ['title' => 'Месяц', 'system' => 'y']);
        field('month', 'yearId', [
            'title' => 'Год',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'year',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        field('month', 'month', [
            'title' => 'Месяц',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => '01',
            'move' => 'yearId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('month', 'month', '01', ['title' => 'Январь', 'move' => '']);
        enumset('month', 'month', '02', ['title' => 'Февраль', 'move' => '01']);
        enumset('month', 'month', '03', ['title' => 'Март', 'move' => '02']);
        enumset('month', 'month', '04', ['title' => 'Апрель', 'move' => '03']);
        enumset('month', 'month', '05', ['title' => 'Май', 'move' => '04']);
        enumset('month', 'month', '06', ['title' => 'Июнь', 'move' => '05']);
        enumset('month', 'month', '07', ['title' => 'Июль', 'move' => '06']);
        enumset('month', 'month', '08', ['title' => 'Август', 'move' => '07']);
        enumset('month', 'month', '09', ['title' => 'Сентябрь', 'move' => '08']);
        enumset('month', 'month', '10', ['title' => 'Октябрь', 'move' => '09']);
        enumset('month', 'month', '11', ['title' => 'Ноябрь', 'move' => '10']);
        enumset('month', 'month', '12', ['title' => 'Декабрь', 'move' => '11']);
        field('month', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'month',
        ]);
        field('month', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'title',
        ]);
        entity('month', ['titleFieldId' => 'title']);
        entity('queueTask', ['title' => 'Очередь задач', 'system' => 'y']);
        field('queueTask', 'title', [
            'title' => 'Задача',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('queueTask', 'datetime', [
            'title' => 'Создана',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>',
            'move' => 'title',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'params', [
            'title' => 'Параметры',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'datetime',
        ]);
        field('queueTask', 'proc', ['title' => 'Процесс', 'elementId' => 'span', 'move' => 'params']);
        field('queueTask', 'procSince', [
            'title' => 'Начат',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
            'move' => 'proc',
        ]);
        field('queueTask', 'procID', [
            'title' => 'PID',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'procSince',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'stage', [
            'title' => 'Этап',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'count',
            'move' => 'procID',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueTask', 'stage', 'count', ['title' => 'Оценка масштабов', 'move' => '']);
        enumset('queueTask', 'stage', 'items', ['title' => 'Создание очереди', 'move' => 'count']);
        enumset('queueTask', 'stage', 'queue', ['title' => 'Процессинг очереди', 'move' => 'items']);
        enumset('queueTask', 'stage', 'apply', ['title' => 'Применение результатов', 'move' => 'queue']);
        field('queueTask', 'state', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'stage',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueTask', 'state', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueTask', 'state', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueTask', 'state', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueTask', 'stageState', [
            'title' => 'Этап - Статус',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'state',
            'mode' => 'hidden',
        ]);
        consider('queueTask', 'stageState', 'stage', ['required' => 'y']);
        consider('queueTask', 'stageState', 'state', ['required' => 'y']);
        field('queueTask', 'chunk', [
            'title' => 'Сегменты',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'stageState',
        ]);
        field('queueTask', 'count', ['title' => 'Оценка', 'elementId' => 'span', 'move' => 'chunk']);
        field('queueTask', 'countState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'count',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'countState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueTask', 'countState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueTask', 'countState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueTask', 'countSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'countState',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'items', ['title' => 'Создание', 'elementId' => 'span', 'move' => 'countSize']);
        field('queueTask', 'itemsState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'items',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'itemsState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueTask', 'itemsState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueTask', 'itemsState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueTask', 'itemsSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'itemsState',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'itemsBytes', [
            'title' => 'Байт',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'itemsSize',
        ]);
        field('queueTask', 'queue', ['title' => 'Процессинг', 'elementId' => 'span', 'move' => 'itemsBytes']);
        field('queueTask', 'queueState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'queue',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'queueState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueTask', 'queueState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueTask', 'queueState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        enumset('queueTask', 'queueState', 'noneed', ['title' => 'Не требуется', 'move' => 'finished']);
        field('queueTask', 'queueSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'queueState',
        ]);
        field('queueTask', 'apply', ['title' => 'Применение', 'elementId' => 'span', 'move' => 'queueSize']);
        field('queueTask', 'applyState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'apply',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'applyState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueTask', 'applyState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueTask', 'applyState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueTask', 'applySize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'applyState',
            'mode' => 'readonly',
        ]);
        entity('queueTask', ['titleFieldId' => 'title']);
        entity('param', ['title' => 'Параметр', 'system' => 'y']);
        field('param', 'fieldId', [
            'title' => 'В контексте какого поля',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        field('param', 'possibleParamId', [
            'title' => 'Параметр настройки',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'fieldId',
            'relation' => 'possibleElementParam',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        consider('param', 'possibleParamId', 'fieldId', ['foreign' => 'elementId', 'required' => 'y']);
        field('param', 'value', [
            'title' => 'Значение параметра',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'possibleParamId',
        ]);
        field('param', 'title', [
            'title' => 'Auto title',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'value',
            'mode' => 'hidden',
        ]);
        consider('param', 'title', 'possibleParamId', ['foreign' => 'title', 'required' => 'y']);
        entity('param', ['titleFieldId' => 'possibleParamId']);
        entity('field', ['title' => 'Поле', 'system' => 'y', 'useCache' => '1']);
        field('field', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('field', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'entityId',
            'mode' => 'required',
        ]);
        field('field', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('field', 'fk', ['title' => 'Внешние ключи', 'elementId' => 'span', 'move' => 'alias']);
        field('field', 'storeRelationAbility', [
            'title' => 'Хранит ключи',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'fk',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('field', 'storeRelationAbility', 'none', ['title' => '<span class="i-color-box" style="background: white;"></span>Нет', 'move' => '']);
        enumset('field', 'storeRelationAbility', 'one', ['title' => '<span class="i-color-box" style="background: url(/i/admin/btn-icon-login.png);"></span>Да, но только один ключ', 'move' => 'none']);
        enumset('field', 'storeRelationAbility', 'many', ['title' => '<span class="i-color-box" style="background: url(/i/admin/btn-icon-multikey.png);"></span>Да, несколько ключей', 'move' => 'one']);
        field('field', 'relation', [
            'title' => 'Ключи какой сущности',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'storeRelationAbility',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
        ]);
        param('field', 'relation', 'groupBy', ['value' => 'system']);
        field('field', 'filter', [
            'title' => 'Статическая фильтрация',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'relation',
        ]);
        field('field', 'el', ['title' => 'Элемент управления', 'elementId' => 'span', 'move' => 'filter']);
        field('field', 'mode', [
            'title' => 'Режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'regular',
            'move' => 'el',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('field', 'mode', 'regular', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/regular.png);"></span>Обычное', 'move' => '']);
        enumset('field', 'mode', 'required', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/required.png);"></span>Обязательное', 'move' => 'regular']);
        enumset('field', 'mode', 'readonly', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/readonly.png);"></span>Только чтение', 'move' => 'required']);
        enumset('field', 'mode', 'hidden', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/hidden.png);"></span>Скрытое', 'move' => 'readonly']);
        field('field', 'elementId', [
            'title' => 'Элемент управления',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'mode',
            'relation' => 'element',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        consider('field', 'elementId', 'storeRelationAbility', ['required' => 'y']);
        field('field', 'tooltip', [
            'title' => 'Подсказка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'elementId',
        ]);
        field('field', 'mysql', ['title' => 'MySQL', 'elementId' => 'span', 'move' => 'tooltip']);
        field('field', 'columnTypeId', [
            'title' => 'Тип столбца MySQL',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'mysql',
            'relation' => 'columnType',
            'storeRelationAbility' => 'one',
        ]);
        consider('field', 'columnTypeId', 'elementId', ['required' => 'y']);
        field('field', 'defaultValue', [
            'title' => 'Значение по умолчанию',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'columnTypeId',
        ]);
        field('field', 'l10n', [
            'title' => 'Мультиязычность',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'defaultValue',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('field', 'l10n', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключена', 'move' => '']);
        enumset('field', 'l10n', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('field', 'l10n', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включена', 'move' => 'qy']);
        enumset('field', 'l10n', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('field', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'l10n',
        ]);
        entity('field', ['titleFieldId' => 'title']);
        entity('alteredField', ['title' => 'Поле, измененное в рамках раздела', 'system' => 'y']);
        field('alteredField', 'sectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('alteredField', 'fieldId', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'sectionId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        consider('alteredField', 'fieldId', 'sectionId', ['foreign' => 'entityId', 'required' => 'y']);
        field('alteredField', 'title', [
            'title' => 'Auto title',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'fieldId',
            'mode' => 'hidden',
        ]);
        consider('alteredField', 'title', 'fieldId', ['foreign' => 'title', 'required' => 'y']);
        field('alteredField', 'impactt', ['title' => 'Влияние', 'elementId' => 'span', 'move' => 'title']);
        field('alteredField', 'impact', [
            'title' => 'Влияние',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'all',
            'move' => 'impactt',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('alteredField', 'impact', 'all', ['title' => 'Все', 'move' => '']);
        enumset('alteredField', 'impact', 'only', ['title' => 'Никто, кроме', 'move' => 'all']);
        enumset('alteredField', 'impact', 'except', ['title' => 'Все, кроме', 'move' => 'only']);
        field('alteredField', 'profileIds', [
            'title' => 'Кроме',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'multicheck',
            'move' => 'impact',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
        ]);
        field('alteredField', 'alter', ['title' => 'Изменить свойства', 'elementId' => 'span', 'move' => 'profileIds']);
        field('alteredField', 'rename', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'alter',
        ]);
        consider('alteredField', 'rename', 'title', ['required' => 'y']);
        field('alteredField', 'mode', [
            'title' => 'Режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'inherit',
            'move' => 'rename',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('alteredField', 'mode', 'inherit', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/inherit.png);"></span>Без изменений', 'move' => '']);
        enumset('alteredField', 'mode', 'regular', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/regular.png);"></span>Обычное', 'move' => 'inherit']);
        enumset('alteredField', 'mode', 'required', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/required.png);"></span>Обязательное', 'move' => 'regular']);
        enumset('alteredField', 'mode', 'readonly', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/readonly.png);"></span>Только чтение', 'move' => 'required']);
        enumset('alteredField', 'mode', 'hidden', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/hidden.png);"></span>Скрытое', 'move' => 'readonly']);
        field('alteredField', 'elementId', [
            'title' => 'Элемент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'mode',
            'relation' => 'element',
            'storeRelationAbility' => 'one',
        ]);
        param('alteredField', 'elementId', 'placeholder', ['value' => 'Без изменений']);
        field('alteredField', 'defaultValue', [
            'title' => 'Значение по умолчанию',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'elementId',
        ]);
        entity('alteredField', ['titleFieldId' => 'fieldId']);
        entity('noticeGetter', ['title' => 'Получатель уведомлений', 'system' => 'y']);
        field('noticeGetter', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => '',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('noticeGetter', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен', 'move' => '']);
        enumset('noticeGetter', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен', 'move' => 'y']);
        field('noticeGetter', 'noticeId', [
            'title' => 'Уведомление',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'toggle',
            'relation' => 'notice',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('noticeGetter', 'profileId', [
            'title' => 'Роль',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'noticeId',
            'relation' => 'profile',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('noticeGetter', 'criteriaRelyOn', [
            'title' => 'Критерий',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'event',
            'move' => 'profileId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('noticeGetter', 'criteriaRelyOn', 'event', ['title' => 'Общий', 'move' => '']);
        enumset('noticeGetter', 'criteriaRelyOn', 'getter', ['title' => 'Раздельный', 'move' => 'event']);
        field('noticeGetter', 'criteriaEvt', [
            'title' => 'Общий',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'criteriaRelyOn',
        ]);
        field('noticeGetter', 'criteriaInc', [
            'title' => 'Для увеличения',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'criteriaEvt',
        ]);
        field('noticeGetter', 'criteriaDec', [
            'title' => 'Для уменьшения',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'criteriaInc',
        ]);
        field('noticeGetter', 'title', [
            'title' => 'Ауто титле',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'criteriaDec',
            'mode' => 'hidden',
        ]);
        consider('noticeGetter', 'title', 'profileId', ['foreign' => 'title', 'required' => 'y']);
        field('noticeGetter', 'email', [
            'title' => 'Дублирование на почту',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'title',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('noticeGetter', 'email', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('noticeGetter', 'email', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да', 'move' => 'n']);
        field('noticeGetter', 'vk', [
            'title' => 'Дублирование в ВК',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'email',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('noticeGetter', 'vk', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('noticeGetter', 'vk', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да', 'move' => 'n']);
        field('noticeGetter', 'sms', [
            'title' => 'Дублирование по SMS',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'vk',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('noticeGetter', 'sms', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('noticeGetter', 'sms', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да', 'move' => 'n']);
        field('noticeGetter', 'criteria', [
            'title' => 'Критерий',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'sms',
            'mode' => 'hidden',
        ]);
        field('noticeGetter', 'mail', [
            'title' => 'Дублирование на почту',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'criteria',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'hidden',
        ]);
        enumset('noticeGetter', 'mail', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('noticeGetter', 'mail', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да', 'move' => 'n']);
        entity('noticeGetter', ['titleFieldId' => 'profileId']);
        entity('section', ['title' => 'Раздел', 'system' => 'y']);
        field('section', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('section', 'alias', [
            'title' => 'Контроллер',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('section', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'alias',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен', 'move' => '']);
        enumset('section', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен', 'move' => 'y']);
        enumset('section', 'toggle', 'h', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Скрыт', 'move' => 'n']);
        field('section', 'type', [
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'p',
            'move' => 'toggle',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'type', 'p', ['title' => 'Проектная', 'move' => '']);
        enumset('section', 'type', 's', ['title' => '<font color=red>Системная</font>', 'move' => 'p']);
        enumset('section', 'type', 'o', ['title' => '<font color=lime>Публичная</font>', 'move' => 's']);
        field('section', 'sectionId', [
            'title' => 'Вышестоящий раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'type',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
        ]);
        param('section', 'sectionId', 'groupBy', ['value' => 'type']);
        field('section', 'expand', [
            'title' => 'Разворачивать пункт меню',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'all',
            'move' => 'sectionId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'expand', 'all', ['title' => 'Всем пользователям', 'move' => '']);
        enumset('section', 'expand', 'only', ['title' => 'Только выбранным', 'move' => 'all']);
        enumset('section', 'expand', 'except', ['title' => 'Всем кроме выбранных', 'move' => 'only']);
        enumset('section', 'expand', 'none', ['title' => 'Никому', 'move' => 'except']);
        field('section', 'expandRoles', [
            'title' => 'Выбранные',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'expand',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
        ]);
        field('section', 'extends', ['title' => 'Родительские классы', 'elementId' => 'span', 'move' => 'expandRoles']);
        field('section', 'extendsJs', [
            'title' => 'JS',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Indi.lib.controller.Controller',
            'move' => 'extends',
            'tooltip' => 'Родительский класс JS',
        ]);
        field('section', 'extendsPhp', [
            'title' => 'PHP',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Indi_Controller_Admin',
            'move' => 'extendsJs',
            'tooltip' => 'Родительский класс PHP',
        ]);
        field('section', 'data', ['title' => 'Источник записей', 'elementId' => 'span', 'move' => 'extendsPhp']);
        field('section', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'data',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
        ]);
        param('section', 'entityId', 'groupBy', ['value' => 'system']);
        field('section', 'parentSectionConnector', [
            'title' => 'Связь с вышестоящим разделом по полю',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'entityId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`storeRelationAbility`!="none"',
        ]);
        consider('section', 'parentSectionConnector', 'entityId', ['required' => 'y']);
        field('section', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'move' => 'parentSectionConnector',
        ]);
        field('section', 'disableAdd', [
            'title' => 'Запретить создание новых записей',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'move',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'disableAdd', '0', ['title' => '<span class="i-color-box" style="background: transparent;"></span>Нет', 'move' => '']);
        enumset('section', 'disableAdd', '1', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-create-deny.png);"></span>Да', 'move' => '0']);
        field('section', 'filter', [
            'title' => 'Фильтрация через SQL WHERE',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'disableAdd',
        ]);
        field('section', 'load', ['title' => 'Подгрузка записей', 'elementId' => 'span', 'move' => 'filter']);
        field('section', 'rowsetSeparate', [
            'title' => 'Режим подгрузки',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'auto',
            'move' => 'load',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'rowsetSeparate', 'auto', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/inherit.png);"></span>Авто', 'move' => '']);
        enumset('section', 'rowsetSeparate', 'yes', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/readonly.png);"></span>Отдельным запросом', 'move' => 'auto']);
        enumset('section', 'rowsetSeparate', 'no', ['title' => '<span class="i-color-box" style="background: url(/i/admin/field/required.png);"></span>В том же запросе', 'move' => 'yes']);
        field('section', 'defaultSortField', [
            'title' => 'Сортировка',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'rowsetSeparate',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('section', 'defaultSortField', 'entityId', ['required' => 'y']);
        field('section', 'defaultSortDirection', [
            'title' => 'Направление сортировки',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'ASC',
            'move' => 'defaultSortField',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'defaultSortDirection', 'DESC', ['title' => '<span class="i-color-box" style="background: url(resources/images/grid/sort_desc.png) -5px -1px;"></span>По убыванию', 'move' => '']);
        enumset('section', 'defaultSortDirection', 'ASC', ['title' => '<span class="i-color-box" style="background: url(resources/images/grid/sort_asc.png) -5px -1px;"></span>По возрастанию', 'move' => 'DESC']);
        field('section', 'rowsOnPage', [
            'title' => 'Количество записей на странице',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'string',
            'defaultValue' => '25',
            'move' => 'defaultSortDirection',
        ]);
        field('section', 'display', ['title' => 'Отображение записей', 'elementId' => 'span', 'move' => 'rowsOnPage']);
        field('section', 'multiSelect', [
            'title' => 'Выделение более одной записи',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'display',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'multiSelect', '0', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-single-select.png);"></span>Нет', 'move' => '']);
        enumset('section', 'multiSelect', '1', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-multi-select.png);"></span>Да', 'move' => '0']);
        field('section', 'rownumberer', [
            'title' => 'Включить нумерацию записей',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'multiSelect',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'rownumberer', '0', ['title' => '<span class="i-color-box" style="background: transparent;"></span>Нет', 'move' => '']);
        enumset('section', 'rownumberer', '1', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-numberer.png);"></span>Да', 'move' => '0']);
        field('section', 'groupBy', [
            'title' => 'Группировка',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'rownumberer',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('section', 'groupBy', 'entityId', ['required' => 'y']);
        field('section', 'tileField', [
            'title' => 'Плитка',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'groupBy',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`elementId` = "14"',
        ]);
        consider('section', 'tileField', 'entityId', ['required' => 'y']);
        field('section', 'tileThumb', [
            'title' => 'Превью',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'tileField',
            'relation' => 'resize',
            'storeRelationAbility' => 'one',
        ]);
        consider('section', 'tileThumb', 'tileField', ['required' => 'y', 'connector' => 'fieldId']);
        field('section', 'roleIds', [
            'title' => 'Доступ',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'tileThumb',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
            'mode' => 'hidden',
        ]);
        field('section', 'store', [
            'title' => 'Записи',
            'elementId' => 'span',
            'move' => 'roleIds',
            'mode' => 'hidden',
        ]);
        field('section', 'params', [
            'title' => 'Параметры',
            'elementId' => 'span',
            'move' => 'store',
            'mode' => 'hidden',
        ]);
        entity('section', ['titleFieldId' => 'title']);
        entity('realtime', ['title' => 'Рилтайм', 'system' => 'y']);
        field('realtime', 'realtimeId', [
            'title' => 'Родительская запись',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'realtime',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'type', [
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'session',
            'move' => 'realtimeId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('realtime', 'type', 'session', ['title' => 'Сессия', 'move' => '']);
        enumset('realtime', 'type', 'channel', ['title' => 'Вкладка', 'move' => 'session']);
        enumset('realtime', 'type', 'context', ['title' => 'Контекст', 'move' => 'channel']);
        field('realtime', 'profileId', [
            'title' => 'Роль',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'type',
            'relation' => 'profile',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'adminId', [
            'title' => 'Пользователь',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'profileId',
            'storeRelationAbility' => 'one',
        ]);
        consider('realtime', 'adminId', 'profileId', ['foreign' => 'entityId', 'required' => 'y']);
        field('realtime', 'token', [
            'title' => 'Токен',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'adminId',
        ]);
        field('realtime', 'spaceSince', [
            'title' => 'Начало',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>',
            'move' => 'token',
        ]);
        param('realtime', 'spaceSince', 'displayTimeFormat', ['value' => 'H:i:s']);
        param('realtime', 'spaceSince', 'displayDateFormat', ['value' => 'Y-m-d']);
        field('realtime', 'spaceUntil', [
            'title' => 'Конец',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
            'move' => 'spaceSince',
        ]);
        field('realtime', 'spaceFrame', [
            'title' => 'Длительность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'spaceUntil',
        ]);
        field('realtime', 'langId', [
            'title' => 'Язык',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'spaceFrame',
            'relation' => 'lang',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'sectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'langId',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'sectionId',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'entries', [
            'title' => 'Записи',
            'columnTypeId' => 'TEXT',
            'elementId' => 'combo',
            'move' => 'entityId',
            'storeRelationAbility' => 'many',
        ]);
        consider('realtime', 'entries', 'sectionId', ['foreign' => 'entityId', 'required' => 'y']);
        field('realtime', 'fields', [
            'title' => 'Поля',
            'columnTypeId' => 'TEXT',
            'elementId' => 'combo',
            'move' => 'entries',
            'relation' => 'field',
            'storeRelationAbility' => 'many',
        ]);
        consider('realtime', 'fields', 'entityId', ['required' => 'y']);
        field('realtime', 'title', [
            'title' => 'Запись',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'fields',
            'mode' => 'hidden',
        ]);
        field('realtime', 'mode', [
            'title' => 'Режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'title',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('realtime', 'mode', 'none', ['title' => 'Не применимо', 'move' => '']);
        enumset('realtime', 'mode', 'rowset', ['title' => 'Набор записей', 'move' => 'none']);
        enumset('realtime', 'mode', 'row', ['title' => 'Одна запись', 'move' => 'rowset']);
        field('realtime', 'scope', [
            'title' => 'Scope',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'mode',
        ]);
        entity('realtime', ['titleFieldId' => 'title']);
        entity('profile', ['title' => 'Роль', 'system' => 'y']);
        field('profile', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('profile', 'type', [
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'p',
            'move' => 'title',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('profile', 'type', 'p', ['title' => 'Проектная', 'move' => '']);
        enumset('profile', 'type', 's', ['title' => '<font color=red>Системная</font>', 'move' => 'p']);
        field('profile', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'type',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('profile', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включена', 'move' => '']);
        enumset('profile', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключена', 'move' => 'y']);
        field('profile', 'entityId', [
            'title' => 'Сущность пользователей',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '11',
            'move' => 'toggle',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
            'filter' => '`system`= "n"',
        ]);
        field('profile', 'dashboard', [
            'title' => 'Дэшборд',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'entityId',
        ]);
        field('profile', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'dashboard',
        ]);
        field('profile', 'maxWindows', [
            'title' => 'Максимальное количество окон',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '15',
            'move' => 'move',
        ]);
        field('profile', 'demo', [
            'title' => 'Демо-режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'maxWindows',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('profile', 'demo', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('profile', 'demo', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да', 'move' => 'n']);
        entity('profile', ['titleFieldId' => 'title']);
        entity('queueChunk', ['title' => 'Сегмент очереди', 'system' => 'y']);
        field('queueChunk', 'queueTaskId', [
            'title' => 'Очередь задач',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'queueTask',
            'storeRelationAbility' => 'one',
        ]);
        field('queueChunk', 'location', [
            'title' => 'Расположение',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'queueTaskId',
        ]);
        field('queueChunk', 'queueChunkId', [
            'title' => 'Родительский сегмент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'location',
            'relation' => 'queueChunk',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('queueChunk', 'fraction', [
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'queueChunkId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueChunk', 'fraction', 'none', ['title' => 'Не указана', 'move' => '']);
        enumset('queueChunk', 'fraction', 'adminSystemUi', ['title' => 'AdminSystemUi', 'move' => 'none']);
        enumset('queueChunk', 'fraction', 'adminCustomUi', ['title' => 'AdminCustomUi', 'move' => 'adminSystemUi']);
        enumset('queueChunk', 'fraction', 'adminCustomData', ['title' => 'AdminCustomData', 'move' => 'adminCustomUi']);
        field('queueChunk', 'where', [
            'title' => 'Условие выборки',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'fraction',
        ]);
        field('queueChunk', 'count', ['title' => 'Оценка', 'elementId' => 'span', 'move' => 'where']);
        field('queueChunk', 'countState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'count',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'countState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueChunk', 'countState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueChunk', 'countState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueChunk', 'countSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'countState',
            'mode' => 'readonly',
        ]);
        field('queueChunk', 'items', ['title' => 'Создание', 'elementId' => 'span', 'move' => 'countSize']);
        field('queueChunk', 'itemsState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'items',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'itemsState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueChunk', 'itemsState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueChunk', 'itemsState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueChunk', 'itemsSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'itemsState',
            'mode' => 'readonly',
        ]);
        field('queueChunk', 'queue', ['title' => 'Процессинг', 'elementId' => 'span', 'move' => 'itemsSize']);
        field('queueChunk', 'itemsBytes', [
            'title' => 'Байт',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'queue',
        ]);
        field('queueChunk', 'queueState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'itemsBytes',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'queueState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueChunk', 'queueState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueChunk', 'queueState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        enumset('queueChunk', 'queueState', 'noneed', ['title' => 'Не требуется', 'move' => 'finished']);
        field('queueChunk', 'queueSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'queueState',
        ]);
        field('queueChunk', 'apply', ['title' => 'Применение', 'elementId' => 'span', 'move' => 'queueSize']);
        field('queueChunk', 'applyState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'move' => 'apply',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'applyState', 'waiting', ['title' => 'Ожидание', 'move' => '']);
        enumset('queueChunk', 'applyState', 'progress', ['title' => 'В работе', 'move' => 'waiting']);
        enumset('queueChunk', 'applyState', 'finished', ['title' => 'Завершено', 'move' => 'progress']);
        field('queueChunk', 'applySize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'applyState',
            'mode' => 'readonly',
        ]);
        field('queueChunk', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'applySize',
        ]);
        entity('queueChunk', ['titleFieldId' => 'location']);
        entity('grid', ['title' => 'Столбец грида', 'system' => 'y']);
        field('grid', 'sectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'move' => '',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('grid', 'fieldId', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'move' => 'sectionId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        param('grid', 'fieldId', 'optionAttrs', ['value' => 'storeRelationAbility']);
        consider('grid', 'fieldId', 'sectionId', ['foreign' => 'entityId', 'required' => 'y']);
        field('grid', 'further', [
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'fieldId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('grid', 'further', 'fieldId', ['foreign' => 'relation', 'required' => 'y', 'connector' => 'entityId']);
        field('grid', 'gridId', [
            'title' => 'Вышестоящий столбец',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'further',
            'relation' => 'grid',
            'storeRelationAbility' => 'one',
            'filter' => '`sectionId` = "<?=$this->sectionId?>"',
        ]);
        param('grid', 'gridId', 'groupBy', ['value' => 'group']);
        field('grid', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'move' => 'gridId',
        ]);
        field('grid', 'title', [
            'title' => 'Auto title',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'move',
            'mode' => 'hidden',
        ]);
        consider('grid', 'title', 'fieldId', ['foreign' => 'title', 'required' => 'y']);
        field('grid', 'display', ['title' => 'Отображение', 'elementId' => 'span', 'move' => 'title']);
        field('grid', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'display',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен', 'move' => '']);
        enumset('grid', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен', 'move' => 'y']);
        enumset('grid', 'toggle', 'h', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Скрыт', 'move' => 'n']);
        enumset('grid', 'toggle', 'e', ['title' => '<span class="i-color-box" style="background: lightgray; border: 1px solid blue;"></span>Скрыт, но показан в развороте', 'move' => 'h']);
        field('grid', 'editor', [
            'title' => 'Редактор',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'toggle',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'editor', '0', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('grid', 'editor', '1', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => '0']);
        field('grid', 'alterTitle', [
            'title' => 'Переименовать',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'editor',
        ]);
        consider('grid', 'alterTitle', 'title', ['required' => 'y']);
        field('grid', 'group', [
            'title' => 'Группа',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'normal',
            'move' => 'alterTitle',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'group', 'locked', ['title' => 'Зафиксированные', 'move' => '']);
        enumset('grid', 'group', 'normal', ['title' => 'Обычные', 'move' => 'locked']);
        field('grid', 'tooltip', [
            'title' => 'Подсказка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'group',
        ]);
        consider('grid', 'tooltip', 'fieldId', ['foreign' => 'tooltip', 'required' => 'y']);
        field('grid', 'width', [
            'title' => 'Ширина',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => 'tooltip',
        ]);
        param('grid', 'width', 'measure', ['value' => 'px']);
        field('grid', 'summaryType', [
            'title' => 'Внизу',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'width',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'summaryType', 'none', ['title' => 'Пусто', 'move' => '']);
        enumset('grid', 'summaryType', 'sum', ['title' => 'Сумма', 'move' => 'none']);
        enumset('grid', 'summaryType', 'average', ['title' => 'Среднее', 'move' => 'sum']);
        enumset('grid', 'summaryType', 'min', ['title' => 'Минимум', 'move' => 'average']);
        enumset('grid', 'summaryType', 'max', ['title' => 'Максимум', 'move' => 'min']);
        enumset('grid', 'summaryType', 'text', ['title' => 'Текст', 'move' => 'max']);
        field('grid', 'summaryText', [
            'title' => 'Текст',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'summaryType',
        ]);
        field('grid', 'accesss', ['title' => 'Доступ', 'elementId' => 'span', 'move' => 'summaryText']);
        field('grid', 'access', [
            'title' => 'Доступ',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'all',
            'move' => 'accesss',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'access', 'all', ['title' => 'Всем', 'move' => '']);
        enumset('grid', 'access', 'only', ['title' => 'Никому, кроме', 'move' => 'all']);
        enumset('grid', 'access', 'except', ['title' => 'Всем, кроме', 'move' => 'only']);
        field('grid', 'profileIds', [
            'title' => 'Кроме',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'multicheck',
            'move' => 'access',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
        ]);
        field('grid', 'rowReqIfAffected', [
            'title' => 'При изменении ячейки обновлять всю строку',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'profileIds',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'rowReqIfAffected', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет', 'move' => '']);
        enumset('grid', 'rowReqIfAffected', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span> Да', 'move' => 'n']);
        entity('grid', ['titleFieldId' => 'fieldId']);
        entity('entity', ['title' => 'Сущность', 'system' => 'y', 'useCache' => '1']);
        field('entity', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('entity', 'table', [
            'title' => 'Таблица БД',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('entity', 'extends', [
            'title' => 'Родительский класс PHP',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Indi_Db_Table',
            'move' => 'table',
            'mode' => 'required',
        ]);
        field('entity', 'system', [
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'extends',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('entity', 'system', 'y', ['title' => '<span style=\'color: red\'>Системная</span>', 'move' => '']);
        enumset('entity', 'system', 'n', ['title' => 'Проектная', 'move' => 'y']);
        enumset('entity', 'system', 'o', ['title' => '<font color=lime>Публичная</font>', 'move' => 'n']);
        field('entity', 'useCache', [
            'title' => 'Включить в кэш',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'system',
            'mode' => 'hidden',
        ]);
        field('entity', 'titleFieldId', [
            'title' => 'Заголовочное поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'useCache',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`entityId` = "<?=$this->id?>" AND `columnTypeId` != "0"',
        ]);
        field('entity', 'spaceScheme', [
            'title' => 'Паттерн комплекта календарных полей',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'titleFieldId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('entity', 'spaceScheme', 'none', ['title' => 'Нет', 'move' => '']);
        enumset('entity', 'spaceScheme', 'date', ['title' => 'DATE', 'move' => 'none']);
        enumset('entity', 'spaceScheme', 'datetime', ['title' => 'DATETIME', 'move' => 'date']);
        enumset('entity', 'spaceScheme', 'date-time', ['title' => 'DATE, TIME', 'move' => 'datetime']);
        enumset('entity', 'spaceScheme', 'date-timeId', ['title' => 'DATE, timeId', 'move' => 'date-time']);
        enumset('entity', 'spaceScheme', 'date-dayQty', ['title' => 'DATE, dayQty', 'move' => 'date-timeId']);
        enumset('entity', 'spaceScheme', 'datetime-minuteQty', ['title' => 'DATETIME, minuteQty', 'move' => 'date-dayQty']);
        enumset('entity', 'spaceScheme', 'date-time-minuteQty', ['title' => 'DATE, TIME, minuteQty', 'move' => 'datetime-minuteQty']);
        enumset('entity', 'spaceScheme', 'date-timeId-minuteQty', ['title' => 'DATE, timeId, minuteQty', 'move' => 'date-time-minuteQty']);
        enumset('entity', 'spaceScheme', 'date-timespan', ['title' => 'DATE, hh:mm-hh:mm', 'move' => 'date-timeId-minuteQty']);
        field('entity', 'spaceFields', [
            'title' => 'Комплект календарных полей',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'spaceScheme',
            'relation' => 'field',
            'storeRelationAbility' => 'many',
            'filter' => '`entityId` = "<?=$this->id?>"',
        ]);
        field('entity', 'filesGroupBy', [
            'title' => 'Группировать файлы',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'spaceFields',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`entityId` = "<?=$this->id?>" AND `storeRelationAbility` = "one"',
        ]);
        entity('entity', ['titleFieldId' => 'title']);
        entity('columnType', ['title' => 'Тип столбца', 'system' => 'y']);
        field('columnType', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('columnType', 'type', [
            'title' => 'Тип столбца MySQL',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('columnType', 'canStoreRelation', [
            'title' => 'Можно хранить ключи',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'type',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('columnType', 'canStoreRelation', 'n', ['title' => 'Нет', 'move' => '']);
        enumset('columnType', 'canStoreRelation', 'y', ['title' => 'Да', 'move' => 'n']);
        field('columnType', 'elementId', [
            'title' => 'Совместимые элементы управления',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'canStoreRelation',
            'relation' => 'element',
            'storeRelationAbility' => 'many',
            'mode' => 'required',
        ]);
        entity('columnType', ['titleFieldId' => 'type']);
        entity('notice', ['title' => 'Уведомление', 'system' => 'y']);
        field('notice', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('notice', 'type', [
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'p',
            'move' => 'title',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('notice', 'type', 'p', ['title' => 'Проектное', 'move' => '']);
        enumset('notice', 'type', 's', ['title' => '<font color=red>Системное</font>', 'move' => 'p']);
        field('notice', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'type',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
            'mode' => 'required',
        ]);
        field('notice', 'event', [
            'title' => 'Событие / PHP',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'entityId',
        ]);
        field('notice', 'profileId', [
            'title' => 'Получатели',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'event',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
            'mode' => 'required',
        ]);
        field('notice', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'profileId',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('notice', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включено', 'move' => '']);
        enumset('notice', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключено', 'move' => 'y']);
        field('notice', 'qty', ['title' => 'Счетчик', 'elementId' => 'span', 'move' => 'toggle']);
        field('notice', 'qtySql', [
            'title' => 'Отображение / SQL',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'qty',
            'mode' => 'required',
        ]);
        field('notice', 'qtyDiffRelyOn', [
            'title' => 'Направление изменения',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'event',
            'move' => 'qtySql',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('notice', 'qtyDiffRelyOn', 'event', ['title' => 'Одинаковое для всех получателей', 'move' => '']);
        enumset('notice', 'qtyDiffRelyOn', 'getter', ['title' => 'Неодинаковое, зависит от получателя', 'move' => 'event']);
        field('notice', 'sectionId', [
            'title' => 'Пункты меню',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'qtyDiffRelyOn',
            'relation' => 'section',
            'storeRelationAbility' => 'many',
            'filter' => 'FIND_IN_SET(`sectionId`, "<?=Indi::model(\'Section\')->fetchAll(\'`sectionId` = "0"\')->column(\'id\', true)?>")',
        ]);
        consider('notice', 'sectionId', 'entityId', ['required' => 'y']);
        field('notice', 'bg', [
            'title' => 'Цвет фона',
            'columnTypeId' => 'VARCHAR(10)',
            'elementId' => 'color',
            'defaultValue' => '212#d9e5f3',
            'move' => 'sectionId',
        ]);
        field('notice', 'fg', [
            'title' => 'Цвет текста',
            'columnTypeId' => 'VARCHAR(10)',
            'elementId' => 'color',
            'defaultValue' => '216#044099',
            'move' => 'bg',
        ]);
        field('notice', 'tooltip', [
            'title' => 'Подсказка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'fg',
        ]);
        field('notice', 'tpl', ['title' => 'Сообщение', 'elementId' => 'span', 'move' => 'tooltip']);
        field('notice', 'tplFor', [
            'title' => 'Назначение',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'inc',
            'move' => 'tpl',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('notice', 'tplFor', 'inc', ['title' => 'Увеличение', 'move' => '']);
        enumset('notice', 'tplFor', 'dec', ['title' => 'Уменьшение', 'move' => 'inc']);
        enumset('notice', 'tplFor', 'evt', ['title' => 'Изменение', 'move' => 'dec']);
        field('notice', 'tplIncSubj', [
            'title' => 'Заголовок',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'tplFor',
        ]);
        field('notice', 'tplIncBody', [
            'title' => 'Текст',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'tplIncSubj',
        ]);
        field('notice', 'tplDecSubj', [
            'title' => 'Заголовок',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'tplIncBody',
        ]);
        field('notice', 'tplDecBody', [
            'title' => 'Текст',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'tplDecSubj',
        ]);
        field('notice', 'tplEvtSubj', [
            'title' => 'Заголовок',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'tplDecBody',
        ]);
        field('notice', 'tplEvtBody', [
            'title' => 'Сообщение',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'tplEvtSubj',
        ]);
        entity('notice', ['titleFieldId' => 'title']);
        entity('search', ['title' => 'Фильтр', 'system' => 'y']);
        field('search', 'sectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('search', 'fieldId', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'sectionId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`elementId` NOT IN (4,14,16,20,22)',
            'mode' => 'required',
        ]);
        param('search', 'fieldId', 'optionAttrs', ['value' => 'storeRelationAbility']);
        consider('search', 'fieldId', 'sectionId', ['foreign' => 'entityId', 'required' => 'y']);
        field('search', 'further', [
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'fieldId',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('search', 'further', 'fieldId', ['foreign' => 'relation', 'required' => 'y', 'connector' => 'entityId']);
        field('search', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'further',
        ]);
        field('search', 'filter', [
            'title' => 'Фильтрация',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'move',
        ]);
        field('search', 'defaultValue', [
            'title' => 'Значение по умолчанию',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'filter',
        ]);
        field('search', 'title', [
            'title' => 'Auto title',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'defaultValue',
            'mode' => 'hidden',
        ]);
        consider('search', 'title', 'fieldId', ['foreign' => 'title', 'required' => 'y']);
        field('search', 'display', ['title' => 'Отображение', 'elementId' => 'span', 'move' => 'title']);
        field('search', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'display',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('search', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен', 'move' => '']);
        enumset('search', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен', 'move' => 'y']);
        field('search', 'alt', [
            'title' => 'Переименовать',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'toggle',
        ]);
        consider('search', 'alt', 'title', ['required' => 'y']);
        field('search', 'tooltip', [
            'title' => 'Подсказка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'alt',
        ]);
        field('search', 'accesss', ['title' => 'Доступ', 'elementId' => 'span', 'move' => 'tooltip']);
        field('search', 'access', [
            'title' => 'Доступ',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'all',
            'move' => 'accesss',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('search', 'access', 'all', ['title' => 'Всем', 'move' => '']);
        enumset('search', 'access', 'only', ['title' => 'Никому, кроме', 'move' => 'all']);
        enumset('search', 'access', 'except', ['title' => 'Всем, кроме', 'move' => 'only']);
        field('search', 'profileIds', [
            'title' => 'Кроме',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'multicheck',
            'move' => 'access',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
        ]);
        field('search', 'flags', ['title' => 'Флаги', 'elementId' => 'span', 'move' => 'profileIds']);
        field('search', 'consistence', [
            'title' => 'Непустой результат',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
            'move' => 'flags',
        ]);
        field('search', 'allowClear', [
            'title' => 'Разрешить сброс',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
            'move' => 'consistence',
        ]);
        field('search', 'ignoreTemplate', [
            'title' => 'Игнорировать шаблон опций',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
            'move' => 'allowClear',
        ]);
        entity('search', ['titleFieldId' => 'fieldId']);
        entity('queueItem', ['title' => 'Элемент очереди', 'system' => 'y']);
        field('queueItem', 'queueTaskId', [
            'title' => 'Очередь',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'move' => '',
            'relation' => 'queueTask',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('queueItem', 'queueChunkId', [
            'title' => 'Сегмент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'queueTaskId',
            'relation' => 'queueChunk',
            'storeRelationAbility' => 'one',
        ]);
        field('queueItem', 'target', [
            'title' => 'Таргет',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'queueChunkId',
            'mode' => 'readonly',
        ]);
        field('queueItem', 'value', [
            'title' => 'Значение',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'move' => 'target',
            'mode' => 'readonly',
        ]);
        field('queueItem', 'result', [
            'title' => 'Результат',
            'columnTypeId' => 'TEXT',
            'elementId' => 'html',
            'move' => 'value',
        ]);
        field('queueItem', 'stage', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'items',
            'move' => 'result',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueItem', 'stage', 'items', ['title' => 'Добавлен', 'move' => '']);
        enumset('queueItem', 'stage', 'queue', ['title' => 'Обработан', 'move' => 'items']);
        enumset('queueItem', 'stage', 'apply', ['title' => 'Применен', 'move' => 'queue']);
        entity('element', ['title' => 'Элемент управления', 'system' => 'y']);
        field('element', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('element', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('element', 'storeRelationAbility', [
            'title' => 'Совместимость с внешними ключами',
            'columnTypeId' => 'SET',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'move' => 'alias',
            'relation' => 'enumset',
            'storeRelationAbility' => 'many',
        ]);
        enumset('element', 'storeRelationAbility', 'none', ['title' => 'Нет', 'move' => '']);
        enumset('element', 'storeRelationAbility', 'one', ['title' => 'Только с одним значением ключа', 'move' => 'none']);
        enumset('element', 'storeRelationAbility', 'many', ['title' => 'С набором значений ключей', 'move' => 'one']);
        field('element', 'hidden', [
            'title' => 'Не отображать в формах',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
            'move' => 'storeRelationAbility',
        ]);
        entity('element', ['titleFieldId' => 'title']);
        entity('lang', ['title' => 'Язык', 'system' => 'y']);
        field('lang', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => '',
            'mode' => 'required',
        ]);
        field('lang', 'alias', [
            'title' => 'Ключ',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('lang', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'alias',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен', 'move' => '']);
        enumset('lang', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен', 'move' => 'y']);
        field('lang', 'state', [
            'title' => 'Состояние',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'noth',
            'move' => 'toggle',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('lang', 'state', 'noth', ['title' => 'Ничего', 'move' => '']);
        enumset('lang', 'state', 'smth', ['title' => 'Чтото', 'move' => 'noth']);
        field('lang', 'admin', ['title' => 'Админка', 'elementId' => 'span', 'move' => 'state']);
        field('lang', 'adminSystem', ['title' => 'Система', 'elementId' => 'span', 'move' => 'admin']);
        field('lang', 'adminSystemUi', [
            'title' => 'Интерфейс',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'adminSystem',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminSystemUi', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('lang', 'adminSystemUi', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('lang', 'adminSystemUi', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => 'qy']);
        enumset('lang', 'adminSystemUi', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('lang', 'adminSystemConst', [
            'title' => 'Константы',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'adminSystemUi',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminSystemConst', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('lang', 'adminSystemConst', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('lang', 'adminSystemConst', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => 'qy']);
        enumset('lang', 'adminSystemConst', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('lang', 'adminCustom', ['title' => 'Проект', 'elementId' => 'span', 'move' => 'adminSystemConst']);
        field('lang', 'adminCustomUi', [
            'title' => 'Интерфейс',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'adminCustom',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomUi', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('lang', 'adminCustomUi', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('lang', 'adminCustomUi', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => 'qy']);
        enumset('lang', 'adminCustomUi', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('lang', 'adminCustomConst', [
            'title' => 'Константы',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'adminCustomUi',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomConst', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('lang', 'adminCustomConst', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('lang', 'adminCustomConst', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => 'qy']);
        enumset('lang', 'adminCustomConst', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('lang', 'adminCustomData', [
            'title' => 'Данные',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'adminCustomConst',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomData', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('lang', 'adminCustomData', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('lang', 'adminCustomData', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => 'qy']);
        enumset('lang', 'adminCustomData', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('lang', 'adminCustomTmpl', [
            'title' => 'Шаблоны',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'move' => 'adminCustomData',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomTmpl', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен', 'move' => '']);
        enumset('lang', 'adminCustomTmpl', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение', 'move' => 'n']);
        enumset('lang', 'adminCustomTmpl', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен', 'move' => 'qy']);
        enumset('lang', 'adminCustomTmpl', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение', 'move' => 'y']);
        field('lang', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
            'move' => 'adminCustomTmpl',
        ]);
        entity('lang', ['titleFieldId' => 'title']);
        die('ok');
    }
    public function realtimeAction() {
        entity('realtime', array (
            'title' => 'Рилтайм',
            'system' => 'y',
        ));
        field('realtime', 'realtimeId', array (
            'title' => 'Родительская запись',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'realtime',
            'storeRelationAbility' => 'one',
        ));
        field('realtime', 'type', array (
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'session',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('realtime', 'type', 'session', array('title' => 'Сессия'));
        enumset('realtime', 'type', 'channel', array('title' => 'Вкладка'));
        enumset('realtime', 'type', 'context', array('title' => 'Контекст'));
        field('realtime', 'profileId', array (
            'title' => 'Роль',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'profile',
            'storeRelationAbility' => 'one',
        ));
        field('realtime', 'adminId', array (
            'title' => 'Пользователь',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'storeRelationAbility' => 'one',
        ));
        consider('realtime', 'adminId', 'profileId', array (
            'foreign' => 'entityId',
            'required' => 'y',
        ));
        field('realtime', 'token', array (
            'title' => 'Токен',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ));
        field('realtime', 'spaceSince', array (
            'title' => 'Начало',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>',
        ));
        param('realtime', 'spaceSince', 'displayTimeFormat', 'H:i:s');
        param('realtime', 'spaceSince', 'displayDateFormat', 'Y-m-d');
        field('realtime', 'spaceUntil', array (
            'title' => 'Конец',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
        ));
        field('realtime', 'spaceFrame', array (
            'title' => 'Длительность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ));
        field('realtime', 'langId', array (
            'title' => 'Язык',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'lang',
            'storeRelationAbility' => 'one',
        ));
        field('realtime', 'sectionId', array (
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
        ));
        field('realtime', 'entityId', array (
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
        ));
        field('realtime', 'entries', array (
            'title' => 'Записи',
            'columnTypeId' => 'TEXT',
            'elementId' => 'combo',
            'storeRelationAbility' => 'many',
        ));
        consider('realtime', 'entries', 'sectionId', array (
            'foreign' => 'entityId',
            'required' => 'y',
        ));
        field('realtime', 'fields', array (
            'title' => 'Поля',
            'columnTypeId' => 'TEXT',
            'elementId' => 'combo',
            'relation' => 'field',
            'storeRelationAbility' => 'many',
        ));
        consider('realtime', 'fields', 'entityId', array (
            'required' => 'y',
        ));
        field('realtime', 'title', array (
            'title' => 'Запись',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'hidden',
        ));
        field('realtime', 'mode', array (
            'title' => 'Режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('realtime', 'mode', 'none', array('title' => 'Не применимо'));
        enumset('realtime', 'mode', 'rowset', array('title' => 'Набор записей'));
        enumset('realtime', 'mode', 'row', array('title' => 'Одна запись'));
        field('realtime', 'scope', array (
            'title' => 'Scope',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ));
        entity('realtime', array('titleFieldId' => 'title'));
        section('realtime', array (
            'sectionId' => 'configuration',
            'entityId' => 'realtime',
            'title' => 'Рилтайм',
            'defaultSortField' => 'spaceSince',
            'type' => 's',
            'groupBy' => 'adminId',
            'roleIds' => '1',
            'multiSelect' => '1',
        ))->nested('grid')->delete();
        section2action('realtime','form', array('profileIds' => '1'));
        section2action('realtime','index', array('profileIds' => '1'));
        section2action('realtime','save', array('profileIds' => '1'));
        section2action('realtime','delete', array('profileIds' => '1'));
        action('restart', ['title' => 'Перезапустить', 'rowRequired' => 'n', 'type' => 's']);
        section2action('realtime','restart', array (
            'profileIds' => '1',
            'rename' => 'Перезагрузить websocket-сервер',
        ));
        grid('realtime', 'title', true);
        grid('realtime', 'token', array('toggle' => 'h'));
        grid('realtime', 'sectionId', array('toggle' => 'h'));
        grid('realtime', 'type', array('toggle' => 'h'));
        grid('realtime', 'profileId', array('toggle' => 'h'));
        grid('realtime', 'adminId', true);
        grid('realtime', 'spaceSince', true);
        grid('realtime', 'spaceUntil', array('toggle' => 'h'));
        grid('realtime', 'spaceFrame', array('toggle' => 'h'));
        grid('realtime', 'langId', array('toggle' => 'h'));
        grid('realtime', 'entityId', array('toggle' => 'h'));
        grid('realtime', 'entries', array('toggle' => 'n'));
        grid('realtime', 'fields', array('toggle' => 'h'));
        filter('realtime', 'type', true);
        filter('realtime', 'profileId', true);
        filter('realtime', 'langId', true);
        filter('realtime', 'adminId', true);
        die('ok');
    }
    public function testAction() {
        mt();
        //for ($i = 0; $i < 50; $i++) m('Test')->createRow(['title' => 'Test' . str_pad($i+1, 2, '0', STR_PAD_LEFT)], true)->save();
        /*for ($i = 0; $i < 10000; $i++) {
            //Indi::db()->query('SELECT * FROM `test` WHERE `id`="169" OR `title` <= "Тест 12111" ORDER BY `title` DESC LIMIT 2')->fetchAll();
            Indi::db()->query('SELECT * FROM `test` ORDER BY `title` ASC LIMIT 25, 1')->fetchAll();
        }*/
        //m('Test')->createRow(['title' => 'Жопа 2'], true)->save();
        //m('Test')->fetchRow('`id` = "206"')->delete();
        Indi::iflush(true);
        for ($i = 1; $i <= 1000; $i++) {
            break;
            $data = ['title' => 'Test ' . $i]; d($data);
            m('Test')->createRow($data, true)->save();
        }
        m('Test')->fetchAll()->delete();
        Indi::ws(false);
        d(mt());
        die('xx1');
    }
    public function actionsl10nAction(){
        field('section2action', 'l10n', array (
            'title' => 'Мультиязычность',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('section2action', 'l10n', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('section2action', 'l10n', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включена'));
        enumset('section2action', 'l10n', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        enumset('section2action', 'l10n', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключена'));
        grid('sectionActions', 'l10n', true)->move(6);
        die('ok');
    }
    public function fixQueueTaskParamsAction() {
        field('queueTask', 'params', array (
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ));
        field('alteredField', 'elementId', array (
            'title' => 'Элемент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'element',
            'storeRelationAbility' => 'one',
        ))->move(1);
        param('alteredField', 'elementId', 'placeholder', 'Без изменений');
        alteredField('queueItem', 'result', array('elementId' => 'string'));
        field('queueChunk', 'where', array (
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ));
        die('ok');
    }
    public function wordingsAction() {
        action('wordings', ['title' => 'Вординги', 'type' => 's']);
        section2action('lang','wordings', array('profileIds' => '1'));
        die('ok');
    }
    public function titleField2considerAction() {
        foreach (m('Entity')->fetchAll() as $entityR) {
            if (!$entityR->titleFieldId) continue;
            if ($entityR->foreign('titleFieldId')->storeRelationAbility == 'none') continue;
            if (!$fieldR_title = Indi::model($entityR->id)->fields('title')) continue;
            $existing = consider($entityR->table, 'title', $entityR->foreign('titleFieldId')->alias);
            d($entityR->table . ':' . $entityR->foreign('titleFieldId')->alias);
            if (Indi::get()->do) $newupdated = consider($entityR->table, 'title', $entityR->foreign('titleFieldId')->alias, ['foreign' => 'title']);
        }
        die('xx');
    }
    public function l10n2Action() {
        action('chart', ['title' => 'График', 'type' => 's']);
        consider('grid', 'alterTitle', 'title', array ('required' => 'y'));
        consider('search', 'alt', 'title', array ('required' => 'y'));
        consider('section2action', 'rename', 'title', array ('required' => 'y'));
        consider('alteredField', 'rename', 'title', array ('required' => 'y'));
        consider('grid', 'tooltip', 'fieldId', array ('foreign' => 'tooltip', 'required' => 'y'));
        field('queueChunk', 'move', array(
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
        ));
        field('queueItem', 'result', array (
            'title' => 'Результат',
            'columnTypeId' => 'TEXT',
            'elementId' => 'html',
        ));
        field('queueChunk', 'queueChunkId', array (
            'title' => 'Родительский сегмент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'relation' => 'queueChunk',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ))->move(14);
        entity('queueChunk', array('titleFieldId' => 'location'));
        field('queueChunk', 'fraction', array (
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ))->move(14);
        enumset('queueChunk', 'fraction', 'none', array('title' => 'Не указана'));
        enumset('queueChunk', 'fraction', 'adminSystemUi', array('title' => 'AdminSystemUi'));
        enumset('queueChunk', 'fraction', 'adminCustomUi', array('title' => 'AdminCustomUi'));
        enumset('queueChunk', 'fraction', 'adminCustomData', array('title' => 'AdminCustomData'));
        section('queueChunk', array ('groupBy' => 'fraction'));

        field('enumset', 'title', array('columnTypeId' => 'TEXT'));
        if (action('login')) action('login', ['type' => 's']);
        foreach (ar('grid,alteredField,search') as $table) Indi::db()->query('
            UPDATE `' . $table . '` `g`, `field` `f` SET `g`.`title` = `f`.`title` WHERE `g`.`fieldId` = `f`.`id`
        ');
        field('queueTask', 'stageState', array (
            'title' => 'Этап - Статус',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'hidden',
        ))->move(14);
        consider('queueTask', 'stageState', 'stage', array (
            'required' => 'y',
        ));
        consider('queueTask', 'stageState', 'state', array (
            'required' => 'y',
        ));
        section('queueTask', ['groupBy' => 'stageState']);
        m('QueueTask')->batch(function($r){$r->setStageState(); $r->save();});
        field('queueChunk', 'where', array ('columnTypeId' => 'TEXT'));
        grid('queueTask', 'itemsBytes', array('summaryType' => 'sum'));
        die('xx');
    }
    public function l10nAction() {
        enumset('field', 'l10n', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключена'));
        enumset('field', 'l10n', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'))->move(1);
        enumset('field', 'l10n', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включена'));
        enumset('field', 'l10n', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));

        entity('lang', array (
            'title' => 'Язык',
            'system' => 'y',
        ));
        field('lang', 'title', array (
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'required',
        ));
        field('lang', 'alias', array (
            'title' => 'Ключ',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'required',
        ));
        field('lang', 'toggle', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'toggle', 'y', array('title' => '<span class="i-color-box" style="background: lime;"></span>Включен'));
        enumset('lang', 'toggle', 'n', array('title' => '<span class="i-color-box" style="background: red;"></span>Выключен'));
        field('lang', 'state', array (
            'title' => 'Состояние',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'noth',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('lang', 'state', 'noth', array('title' => 'Ничего'));
        enumset('lang', 'state', 'smth', array('title' => 'Чтото'));
        field('lang', 'admin', array (
            'title' => 'Админка',
            'elementId' => 'span',
        ));
        field('lang', 'adminSystem', array (
            'title' => 'Система',
            'elementId' => 'span',
        ));
        field('lang', 'adminSystemUi', array (
            'title' => 'Интерфейс',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'adminSystemUi', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен'));
        enumset('lang', 'adminSystemUi', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('lang', 'adminSystemUi', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включен'));
        enumset('lang', 'adminSystemUi', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        field('lang', 'adminSystemConst', array (
            'title' => 'Константы',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'adminSystemConst', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен'));
        enumset('lang', 'adminSystemConst', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('lang', 'adminSystemConst', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включен'));
        enumset('lang', 'adminSystemConst', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        field('lang', 'adminCustom', array (
            'title' => 'Проект',
            'elementId' => 'span',
        ));
        field('lang', 'adminCustomUi', array (
            'title' => 'Интерфейс',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'adminCustomUi', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен'));
        enumset('lang', 'adminCustomUi', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('lang', 'adminCustomUi', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включен'));
        enumset('lang', 'adminCustomUi', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        field('lang', 'adminCustomConst', array (
            'title' => 'Константы',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'adminCustomConst', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен'));
        enumset('lang', 'adminCustomConst', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('lang', 'adminCustomConst', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включен'));
        enumset('lang', 'adminCustomConst', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        field('lang', 'adminCustomData', array (
            'title' => 'Данные',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'adminCustomData', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен'));
        enumset('lang', 'adminCustomData', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('lang', 'adminCustomData', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включен'));
        enumset('lang', 'adminCustomData', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        field('lang', 'adminCustomTmpl', array (
            'title' => 'Шаблоны',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('lang', 'adminCustomTmpl', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен'));
        enumset('lang', 'adminCustomTmpl', 'qy', array('title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'));
        enumset('lang', 'adminCustomTmpl', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включен'));
        enumset('lang', 'adminCustomTmpl', 'qn', array('title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение'));
        field('lang', 'move', array (
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
        ));
        entity('lang', array('titleFieldId' => 'title'));
        section('lang', array (
            'sectionId' => 'configuration',
            'entityId' => 'lang',
            'title' => 'Языки',
            'type' => 's',
            'groupBy' => 'state',
            'roleIds' => '1',
            'multiSelect' => '1',
        ))->nested('grid')->delete();
        section2action('lang','index', array('profileIds' => '1'));
        section2action('lang','form', array('profileIds' => '1'));
        section2action('lang','save', array('profileIds' => '1'));
        section2action('lang','delete', array('profileIds' => '1'));
        section2action('lang','up', array('profileIds' => '1'));
        section2action('lang','down', array('profileIds' => '1'));
        action('dict', array('title' => 'Доступные языки', 'rowRequired' => 'n', 'type' => 's'));
        action('run', array('title' => 'Запустить', 'rowRequired' => 'y', 'type' => 's'));
        section2action('lang','dict', array('profileIds' => '1'));
        grid('lang', 'title', true);
        grid('lang', 'alias', true);
        grid('lang', 'admin', true);
        grid('lang', 'toggle', array('gridId' => 'admin'));
        grid('lang', 'adminSystem', array('gridId' => 'admin'));
        grid('lang', 'adminSystemUi', array('gridId' => 'adminSystem'));
        grid('lang', 'adminSystemConst', array('gridId' => 'adminSystem'));
        grid('lang', 'adminCustom', array('gridId' => 'admin'));
        grid('lang', 'adminCustomUi', array('gridId' => 'adminCustom'));
        grid('lang', 'adminCustomConst', array('gridId' => 'adminCustom'));
        grid('lang', 'adminCustomData', array('gridId' => 'adminCustom'));
        grid('lang', 'adminCustomTmpl', array('gridId' => 'adminCustom'));
        grid('lang', 'move', true);
        filter('lang', 'state', true);
        filter('lang', 'toggle', true);
        entity('queueTask', array (
            'title' => 'Очередь задач',
            'system' => 'y',
        ));
        field('queueTask', 'title', array (
            'title' => 'Задача',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'required',
        ));
        field('queueTask', 'datetime', array (
            'title' => 'Создана',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>',
            'mode' => 'readonly',
        ));
        field('queueTask', 'params', array (
            'title' => 'Параметры',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ));
        field('queueTask', 'proc', array (
            'title' => 'Процесс',
            'elementId' => 'span',
        ));
        field('queueTask', 'procSince', array (
            'title' => 'Начат',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
        ));
        field('queueTask', 'procID', array (
            'title' => 'PID',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        field('queueTask', 'stage', array (
            'title' => 'Этап',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'count',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('queueTask', 'stage', 'count', array('title' => 'Оценка масштабов'));
        enumset('queueTask', 'stage', 'items', array('title' => 'Создание очереди'));
        enumset('queueTask', 'stage', 'queue', array('title' => 'Процессинг очереди'));
        enumset('queueTask', 'stage', 'apply', array('title' => 'Применение результатов'));
        field('queueTask', 'state', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('queueTask', 'state', 'waiting', array('title' => 'Ожидание'));
        enumset('queueTask', 'state', 'progress', array('title' => 'В работе'));
        enumset('queueTask', 'state', 'finished', array('title' => 'Завершено'));
        field('queueTask', 'chunk', array (
            'title' => 'Сегменты',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ));
        field('queueTask', 'count', array (
            'title' => 'Оценка',
            'elementId' => 'span',
        ));
        field('queueTask', 'countState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueTask', 'countState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueTask', 'countState', 'progress', array('title' => 'В работе'));
        enumset('queueTask', 'countState', 'finished', array('title' => 'Завершено'));
        field('queueTask', 'countSize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        field('queueTask', 'items', array (
            'title' => 'Создание',
            'elementId' => 'span',
        ));
        field('queueTask', 'itemsState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueTask', 'itemsState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueTask', 'itemsState', 'progress', array('title' => 'В работе'));
        enumset('queueTask', 'itemsState', 'finished', array('title' => 'Завершено'));
        field('queueTask', 'itemsSize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        field('queueTask', 'itemsBytes', array (
            'title' => 'Байт',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ));
        field('queueTask', 'queue', array (
            'title' => 'Процессинг',
            'elementId' => 'span',
        ));
        field('queueTask', 'queueState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueTask', 'queueState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueTask', 'queueState', 'progress', array('title' => 'В работе'));
        enumset('queueTask', 'queueState', 'finished', array('title' => 'Завершено'));
        enumset('queueTask', 'queueState', 'noneed', array('title' => 'Не требуется'));
        field('queueTask', 'queueSize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ));
        field('queueTask', 'apply', array (
            'title' => 'Применение',
            'elementId' => 'span',
        ));
        field('queueTask', 'applyState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueTask', 'applyState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueTask', 'applyState', 'progress', array('title' => 'В работе'));
        enumset('queueTask', 'applyState', 'finished', array('title' => 'Завершено'));
        field('queueTask', 'applySize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        entity('queueTask', array('titleFieldId' => 'title'));
        entity('queueChunk', array (
            'title' => 'Сегмент очереди',
            'system' => 'y',
        ));
        field('queueChunk', 'queueTaskId', array (
            'title' => 'Очередь задач',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'queueTask',
            'storeRelationAbility' => 'one',
        ));
        field('queueChunk', 'location', array (
            'title' => 'Расположение',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ));
        field('queueChunk', 'where', array (
            'title' => 'Условие выборки',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ));
        field('queueChunk', 'count', array (
            'title' => 'Оценка',
            'elementId' => 'span',
        ));
        field('queueChunk', 'countState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueChunk', 'countState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueChunk', 'countState', 'progress', array('title' => 'В работе'));
        enumset('queueChunk', 'countState', 'finished', array('title' => 'Завершено'));
        field('queueChunk', 'countSize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        field('queueChunk', 'items', array (
            'title' => 'Создание',
            'elementId' => 'span',
        ));
        field('queueChunk', 'itemsState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueChunk', 'itemsState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueChunk', 'itemsState', 'progress', array('title' => 'В работе'));
        enumset('queueChunk', 'itemsState', 'finished', array('title' => 'Завершено'));
        field('queueChunk', 'itemsSize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        field('queueChunk', 'queue', array (
            'title' => 'Процессинг',
            'elementId' => 'span',
        ));
        field('queueChunk', 'queueState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueChunk', 'queueState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueChunk', 'queueState', 'progress', array('title' => 'В работе'));
        enumset('queueChunk', 'queueState', 'finished', array('title' => 'Завершено'));
        enumset('queueChunk', 'queueState', 'noneed', array('title' => 'Не требуется'));
        field('queueChunk', 'queueSize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ));
        field('queueChunk', 'apply', array (
            'title' => 'Применение',
            'elementId' => 'span',
        ));
        field('queueChunk', 'applyState', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        enumset('queueChunk', 'applyState', 'waiting', array('title' => 'Ожидание'));
        enumset('queueChunk', 'applyState', 'progress', array('title' => 'В работе'));
        enumset('queueChunk', 'applyState', 'finished', array('title' => 'Завершено'));
        field('queueChunk', 'applySize', array (
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ));
        entity('queueChunk', array (
        ));
        entity('queueItem', array (
            'title' => 'Элемент очереди',
            'system' => 'y',
        ));
        field('queueItem', 'queueTaskId', array (
            'title' => 'Очередь',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'relation' => 'queueTask',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ));
        field('queueItem', 'queueChunkId', array (
            'title' => 'Сегмент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'queueChunk',
            'storeRelationAbility' => 'one',
        ));
        field('queueItem', 'target', array (
            'title' => 'Таргет',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'readonly',
        ));
        field('queueItem', 'value', array (
            'title' => 'Значение',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'mode' => 'readonly',
        ));
        field('queueItem', 'result', array (
            'title' => 'Результат',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ));
        field('queueItem', 'stage', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'items',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('queueItem', 'stage', 'items', array('title' => 'Добавлен'));
        enumset('queueItem', 'stage', 'queue', array('title' => 'Обработан'));
        enumset('queueItem', 'stage', 'apply', array('title' => 'Применен'));
        section('queueTask', array (
            'sectionId' => 'configuration',
            'entityId' => 'queueTask',
            'title' => 'Очереди задач',
            'defaultSortField' => 'datetime',
            'defaultSortDirection' => 'DESC',
            'disableAdd' => '1',
            'type' => 's',
            'roleIds' => '1',
        ))->nested('grid')->delete();
        section2action('queueTask','index', array('profileIds' => '1'));
        section2action('queueTask','form', array('profileIds' => '1'));
        section2action('queueTask','delete', array('profileIds' => '1'));
        section2action('queueTask','run', array('profileIds' => '1'));
        grid('queueTask', 'datetime', true);
        grid('queueTask', 'title', true);
        grid('queueTask', 'params', true);
        grid('queueTask', 'stage', array('toggle' => 'h'));
        grid('queueTask', 'state', array('toggle' => 'h'));
        grid('queueTask', 'chunk', true);
        grid('queueTask', 'proc', true);
        grid('queueTask', 'procID', array('gridId' => 'proc'));
        grid('queueTask', 'procSince', array('gridId' => 'proc'));
        grid('queueTask', 'count', true);
        grid('queueTask', 'countState', array('gridId' => 'count'));
        grid('queueTask', 'countSize', array('gridId' => 'count'));
        grid('queueTask', 'items', true);
        grid('queueTask', 'itemsState', array('gridId' => 'items'));
        grid('queueTask', 'itemsSize', array('gridId' => 'items'));
        grid('queueTask', 'itemsBytes', array('gridId' => 'items'));
        grid('queueTask', 'queue', true);
        grid('queueTask', 'queueState', array('gridId' => 'queue'));
        grid('queueTask', 'queueSize', array('gridId' => 'queue'));
        grid('queueTask', 'apply', true);
        grid('queueTask', 'applyState', array('gridId' => 'apply'));
        grid('queueTask', 'applySize', array('gridId' => 'apply'));
        section('queueChunk', array (
            'sectionId' => 'queueTask',
            'entityId' => 'queueChunk',
            'title' => 'Сегменты очереди',
            'disableAdd' => '1',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
            'rownumberer' => '1',
        ))->nested('grid')->delete();
        section2action('queueChunk','index', array('profileIds' => '1'));
        section2action('queueChunk','form', array('profileIds' => '1'));
        grid('queueChunk', 'entityId', array('toggle' => 'n'));
        grid('queueChunk', 'fieldId', array('toggle' => 'n'));
        grid('queueChunk', 'location', true);
        grid('queueChunk', 'where', true);
        grid('queueChunk', 'count', true);
        grid('queueChunk', 'countState', array('gridId' => 'count'));
        grid('queueChunk', 'countSize', array('gridId' => 'count', 'summaryType' => 'sum'));
        grid('queueChunk', 'items', true);
        grid('queueChunk', 'itemsState', array('gridId' => 'items'));
        grid('queueChunk', 'itemsSize', array('gridId' => 'items', 'summaryType' => 'sum'));
        grid('queueChunk', 'queue', true);
        grid('queueChunk', 'queueState', array('gridId' => 'queue'));
        grid('queueChunk', 'queueSize', array('gridId' => 'queue', 'summaryType' => 'sum'));
        grid('queueChunk', 'apply', true);
        grid('queueChunk', 'applyState', array('gridId' => 'apply'));
        grid('queueChunk', 'applySize', array('gridId' => 'apply', 'summaryType' => 'sum'));
        section('queueItem', array (
            'sectionId' => 'queueChunk',
            'entityId' => 'queueItem',
            'title' => 'Элементы очереди',
            'disableAdd' => '1',
            'type' => 's',
            'roleIds' => '1',
        ))->nested('grid')->delete();
        section2action('queueItem','index', array('profileIds' => '1'));
        section2action('queueItem','save', array('profileIds' => '1'));
        grid('queueItem', 'target', true);
        grid('queueItem', 'value', true);
        grid('queueItem', 'result', array(  'editor' => 1,
        ));
        grid('queueItem', 'stage', true);
        filter('queueItem', 'stage', true);
        die('xx');
    }

    public function filterTipAction() {
        field('search', 'tooltip', array (
            'title' => 'Подсказка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ));
        die('ok');
    }
    public function sectiontogglehAction() {
        enumset('section', 'toggle', 'h', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Скрыт'));
        die('ok');
    }
    public function noticegettertoggleAction() {
        field('noticeGetter', 'toggle', array (
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ))->move(14);
        enumset('noticeGetter', 'toggle', 'y', array('title' => '<span class="i-color-box" style="background: lime;"></span>Включен'));
        enumset('noticeGetter', 'toggle', 'n', array('title' => '<span class="i-color-box" style="background: red;"></span>Выключен'));
        grid('noticeGetters', 'toggle', true)->move(5);
        die('ok');
    }
    public function rowexpanderAction() {
        enumset('grid', 'toggle', 'e', array('title' => '<span class="i-color-box" style="background: lightgray; border: 1px solid blue;"></span>Скрыт, но показан в развороте'));
        die('ok');
    }
    public function fieldsmodeAction() {
        action('activate', array('title' => 'Активировать', 'rowRequired' => 'y', 'type' => 's'));
        section2action('fields','activate', array ('profileIds' => '1', 'rename' => 'Выбрать режим'))->move(1);
        die('ok');
    }

    public function gridcolWidthUsageAction() {
        field('grid', 'width', array (
            'title' => 'Ширина',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ))->move(6);
        param('grid', 'width', 'measure', 'px');
        grid('grid', 'width', array('editor' => '1'));
        action('rwu', array('rowRequired' => 'n', 'type' => 's', 'display' => 0));
        die('ok');
    }

    public function foreignFilterAction() {
        field('search', 'further', array (
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ))->move(12);
        consider('search', 'further', 'fieldId', array (
            'foreign' => 'relation',
            'required' => 'y',
            'connector' => 'entityId',
        ));
        die('ok');
    }

    public function foreignGridAction(){
        field('grid', 'further', array (
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ))->move(13);
        consider('grid', 'further', 'fieldId', array (
            'foreign' => 'relation',
            'required' => 'y',
            'connector' => 'entityId',
        ));
        die('ok');
    }

    public function last4Action() {
        foreach (ar('sectionancestor,rownumberer,filterallowclear,sectionmultiselect') as $_)
            $this->{$_ . 'Action'}();
        foreach (ar('sectionActions,grid,alteredFields,search') as $s)
            section($s, array('extendsPhp' => 'Indi_Controller_Admin_Multinew'));
        die('ok');
    }

    public function sectionmultiselectAction() {
        field('section', 'multiSelect', array(
            'title' => 'Выделение более одной записи',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check'
        ));
        //die('ok');
    }

    public function filterallowclearAction() {
        field('search', 'allowClear', array (
            'title' => 'Разрешить сброс',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
        ))->move(6);
        //die('ok');
    }

    public function rownumbererAction() {
        field('section', 'rownumberer', array (
            'title' => 'Включить нумерацию строк',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
        ));
        //die('ok');
    }

    public function sectionancestorAction() {
        field('section', 'extends', array('title' => 'Родительский класс PHP', 'alias' => 'extendsPhp'));
        field('section', 'extendsJs', array (
            'title' => 'Родительский класс JS',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Indi.lib.controller.Controller',
        ))->move(12);
        grid('sections','rowsOnPage', array (
            'alterTitle' => 'СНС',
            'tooltip' => 'Строк на странице',
        ));
        grid('sections','extendsPhp', array('editor' => 1));
        grid('sections','extendsJs', array('editor' => 1));
        //die('ok');
    }

    public function lockedAction() {
        field('grid', 'group', array (
            'title' => 'Группа',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'normal',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ))->move(2);
        enumset('grid', 'group', 'normal', array('title' => 'Обычные'));
        enumset('grid', 'group', 'locked', array('title' => 'Зафиксированные'));
        section('grid', array('groupBy' => 'group'));
        grid('grid', 'group', true);
        param('grid', 'gridId', 'groupBy', 'group');
        die('ok');
    }

    public function summaryTypeAction() {
        field('grid', 'summaryType', array (
            'title' => 'Внизу',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ))->move(2);
        enumset('grid', 'summaryType', 'none', array('title' => 'Пусто'));
        enumset('grid', 'summaryType', 'sum', array('title' => 'Сумма'));
        enumset('grid', 'summaryType', 'average', array('title' => 'Среднее'));
        enumset('grid', 'summaryType', 'min', array('title' => 'Минимум'));
        enumset('grid', 'summaryType', 'max', array('title' => 'Максимум'));
        enumset('grid', 'summaryType', 'text', array('title' => 'Текст'));
        field('grid', 'summaryText', array (
            'title' => 'Текст',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ))->move(2);
        die('ok');
    }

    public function convertSatelliteAction() {
        if (entity('user'))
            foreach (ar('color,testIds,storeRelationAbility,test,entityId') as $prop)
                if ($_ = field('user', $prop))
                    $_->delete();
        entity('columnType', array('titleFieldId' => 'type'));
        enumset('field', 'storeRelationAbility', 'none', array('title' => '<span class="i-color-box" style="background: white;"></span>Нет'));
        enumset('field', 'storeRelationAbility', 'one', array('title' => '<span class="i-color-box" style="background: url(/i/admin/btn-icon-login.png);"></span>Да, но для только одного значения ключа'));
        enumset('field', 'storeRelationAbility', 'many', array('title' => '<span class="i-color-box" style="background: url(/i/admin/btn-icon-multikey.png);"></span>Да, для энного количества значений ключей'));
        if ($_ = section('fieldsAll')) $_->delete();
        section('fieldsAll', array (
            'sectionId' => 'configuration',
            'entityId' => 'field',
            'title' => 'Все поля',
            'disableAdd' => '1',
            'type' => 's',
            'groupBy' => 'entityId',
        ))->nested('grid')->delete();
        section2action('fieldsAll','index', array('profileIds' => '1'));
        section2action('fieldsAll','form', array('profileIds' => '1'));
        section2action('fieldsAll','save', array('profileIds' => '1'));
        grid('fieldsAll','entityId', array (
            'alterTitle' => 'Сущность',
            'tooltip' => 'Сущность, в структуру которой входит это поле',
        ));
        grid('fieldsAll','title', array (
            'alterTitle' => 'Наименование',
            'editor' => 1,
        ));
        grid('fieldsAll','view', array('alterTitle' => 'Отображение'));
        grid('fieldsAll','mode', array('gridId' => 'view'));
        grid('fieldsAll','elementId', array (
            'alterTitle' => 'UI',
            'tooltip' => 'Элемент управления',
            'gridId' => 'view',
        ));
        grid('fieldsAll','tooltip', array('gridId' => 'view'));
        grid('fieldsAll','mysql', array('alterTitle' => 'MySQL'));
        grid('fieldsAll','alias', array (
            'alterTitle' => 'Имя',
            'gridId' => 'mysql',
        ));
        grid('fieldsAll','columnTypeId', array (
            'alterTitle' => 'Тип',
            'tooltip' => 'Тип столбца MySQL',
            'gridId' => 'mysql',
        ));
        grid('fieldsAll','defaultValue', array (
            'alterTitle' => 'По умолчанию',
            'tooltip' => 'Значение по умолчанию',
            'gridId' => 'mysql',
        ));
        grid('fieldsAll','fk', array('alterTitle' => 'Ключи'));
        grid('fieldsAll','storeRelationAbility', array (
            'alterTitle' => 'Режим',
            'tooltip' => 'Предназначено для хранения ключей',
            'gridId' => 'fk',
        ));
        grid('fieldsAll','relation', array (
            'alterTitle' => 'Сущность',
            'tooltip' => 'Ключи какой сущности будут храниться в этом поле',
            'gridId' => 'fk',
        ));
        grid('fieldsAll','filter', array (
            'alterTitle' => 'Фильтрация',
            'tooltip' => 'Статическая фильтрация',
            'gridId' => 'fk',
        ));
        grid('fieldsAll','l10n', array (
            'alterTitle' => 'l10n',
            'tooltip' => 'Мультиязычность',
        ));
        grid('fieldsAll','move', true);
        grid('fieldsAll','satellitealias', array('toggle' => 'n'));
        grid('fieldsAll','span', array('toggle' => 'n'));
        grid('fieldsAll','dependency', array('gridId' => 'span'));
        grid('fieldsAll','satellite', array('gridId' => 'span'));
        grid('fieldsAll','alternative', array('gridId' => 'span'));
        filter('fieldsAll', 'entityId', array('alt' => 'Сущность'));
        filter('fieldsAll', 'mode', true);
        filter('fieldsAll', 'relation', array('alt' => 'Ключи'));
        filter('fieldsAll', 'elementId', array('alt' => 'Элемент'));
        filter('fieldsAll', 'dependency', true);

        // Convert satellite-cfg into consider-cfg
        foreach (Indi::model('Field')->fetchAll('`dependency` != "u"') as $fieldR) {
            $ctor = array();
            if ($fieldR->alternative) $ctor['foreign'] = $fieldR->alternative;
            if (!Indi::model($fieldR->entityId)->fields($fieldR->alias)->param('allowZeroSatellite'))
                $ctor['required'] = 'y';
            if ($_ = $fieldR->foreign('satellite')->satellitealias) $ctor['connector'] = $_;
            if (!$ctor) $ctor = true;
            consider($fieldR->foreign('entityId')->table, $fieldR->alias, $fieldR->foreign('satellite')->alias, $ctor);
        }
        field('section', 'parentSectionConnector', array('filter' => '`storeRelationAbility`!="none"'));

        // Erase satellite-cfg and hide fields, responsible for satellite-functionaity
        //Indi::db()->query('UPDATE `field` SET `dependency` = "u", `satellitealias` = "", `satellite` = "0", `alternative` = ""');
        foreach (ar('span,dependency,satellitealias,satellite,alternative') as $field)
            field('field', $field, array('mode' => 'readonly'));
        die('ok');
    }
    public function admindemoAction() {
        field('admin', 'demo', array (
            'title' => 'Демо-режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('admin', 'demo', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет'));
        enumset('admin', 'demo', 'y', array('title' => '<span class="i-color-box" style="background: lime;"></span>Да'));
        grid('admins', 'demo', array('alterTitle' => 'Демо', 'tooltip' => 'Демо-режим'));
        field('profile', 'demo', array (
            'title' => 'Демо-режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('profile', 'demo', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет'));
        enumset('profile', 'demo', 'y', array('title' => '<span class="i-color-box" style="background: lime;"></span>Да'));
        grid('profiles', 'demo', array('alterTitle' => 'Демо', 'tooltip' => 'Демо-режим'));
        die('ok');
    }

    public function queueChunkItemBytesAction() {
        field('queueChunk', 'itemsBytes', array(
            'title' => 'Байт',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ))->move(6);
        grid('queueChunk', 'itemsBytes', ['gridId' => 'items', 'summaryType' => 'sum']);
        grid('queueChunk', 'move');
        section('queueChunk', array(
            'defaultSortField' => 'move',
        ));
        Indi::db()->query('UPDATE `profile` SET `entityId` = "11" WHERE `entityId` = "0"');
        field('profile', 'type', array(
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'p',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('profile', 'type', 's', array('title' => '<font color=red>Системная</font>'));
        enumset('profile', 'type', 'p', array('title' => 'Проектная'));
        Indi::db()->query('UPDATE `profile` SET `type` = "s" WHERE `id` = "1"');
        grid('profiles', 'type', true)->move(3);
        die('ok');
    }

    public function filesGroupByAction() {
        field('entity', 'filesGroupBy', array (
            'title' => 'Группировать файлы',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`entityId` = "<?=$this->id?>" AND `storeRelationAbility` = "one"',
        ));
        grid('entities', 'filesGroupBy', array('editor' => 1));
        die('ok');
    }

    public function exportAction() {
        section2action('sectionActions','export', array('profileIds' => '1'));
        section2action('grid','export', array('profileIds' => '1'));
        section2action('alteredFields','export', array('profileIds' => '1'));
        section2action('search','export', array('profileIds' => '1'));
        section2action('fields','export', array('profileIds' => '1'));
        section2action('enumset','export', array('profileIds' => '1'));
        section2action('resize','export', array('profileIds' => '1'));
        section2action('params','export', array('profileIds' => '1'));
        section2action('consider','export', array('profileIds' => '1'));
        section('enumset', array ('extendsPhp' => 'Indi_Controller_Admin_Exportable'));
        section('params', array ('extendsPhp' => 'Indi_Controller_Admin_Exportable'));
        section('consider', array ('extendsPhp' => 'Indi_Controller_Admin_Exportable'));
        if ($_ = section2action('entities','cache')) $_->delete();
        if ($_ = section2action('entities','author')) $_->delete();
        die('ok');
    }

    public function noticetypeAction(){
        field('notice', 'type', array (
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'p',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ))->move(19);
        enumset('notice', 'type', 'p', array('title' => 'Проектное'));
        enumset('notice', 'type', 's', array('title' => '<font color=red>Системное</font>'));
        die('ok');
    }
    public function tileAction() {
        field('section', 'tileField', array (
            'title' => 'Плитка',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`elementId` = "14"',
        ))->move(7);
        consider('section', 'tileField', 'entityId', array (
            'required' => 'y',
        ));
        field('section', 'tileThumb', array (
            'title' => 'Превью',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'resize',
            'storeRelationAbility' => 'one',
        ))->move(7);
        consider('section', 'tileThumb', 'tileField', array (
            'required' => 'y',
            'connector' => 'fieldId',
        ));
        die('ok');
    }

    public function uieditAction() {
        field('admin', 'uiedit', array (
            'title' => 'Правки UI',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ));
        enumset('admin', 'uiedit', 'n', array('title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключено'));
        enumset('admin', 'uiedit', 'y', array('title' => '<span class="i-color-box" style="background: blue;"></span>Включено'));
        grid('admins', 'uiedit', true);
        die('ok');
    }
}