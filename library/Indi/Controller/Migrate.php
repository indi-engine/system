<?php
class Indi_Controller_Migrate extends Indi_Controller {
    public function geminiAction() {
        action('build', ['title' => 'Build', 'fraction' => 'system', 'selectionRequired' => 'n']);
        # 'Build'-action in 'Entities'-section
        section2action('entities', 'build', [
            'move' => 'update',
            'roleIds' => 'dev',
            'south' => 'no',
            'fitWindow' => 'n',
            'rename' => 'Build with AI',
            'l10n' => 'na',
        ]);
        # 'Fraction'-filter in 'Entities'-section
        filter('entities', 'fraction', ['move' => '', 'defaultValue' => 'custom', 'allowZeroResult' => 1]);
        section('db', ['title' => 'Database', 'move' => '']);
        section('dict', ['title' => 'Dictionaries', 'move' => '']);
        section('usr', ['title' => 'Users', 'move' => '']);
        field('entity', 'stat', [
            'title' => 'Stats',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'filesGroupBy',
        ]);
        field('entity', 'fieldQty', [
            'title' => 'Fields',
            'mode' => 'hidden',
            'elementId' => 'number',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => 0,
            'move' => 'stat',
        ]);
        field('entity', 'countQty', [
            'title' => 'Aggregations',
            'mode' => 'hidden',
            'elementId' => 'number',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => 0,
            'move' => 'fieldQty',
        ]);
        field('entity', 'indexQty', [
            'title' => 'Indexes',
            'mode' => 'hidden',
            'elementId' => 'number',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => 0,
            'move' => 'countQty',
        ]);

        inQtySum('field', 'entityId', 'fieldQty', ['sourceWhere' => '`entry` = 0']);
        inQtySum('inQtySum', 'sourceEntity', 'countQty', true);
        inQtySum('sqlIndex', 'entityId', 'indexQty', true);

        # 'Stats'-column (only used as a group for child columns) in 'Entities'-section
        grid('entities', 'stat', ['gridId' => 'props', 'move' => 'binding']);

        # 'Fields'-column in 'Entities'-section
        grid('entities', 'fieldQty', [
            'gridId' => 'stat',
            'move' => '',
            'rename' => 'FLD',
            'tooltip' => 'Fields quantity',
            'summaryType' => 'sum',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'index',
        ]);

        # 'Aggregations'-column in 'Entities'-section
        grid('entities', 'countQty', [
            'gridId' => 'stat',
            'move' => 'fieldQty',
            'rename' => 'AGG',
            'tooltip' => 'Aggregations quantity',
            'summaryType' => 'sum',
            'jumpSectionId' => 'inQtySum',
            'jumpSectionActionId' => 'index',
        ]);

        # 'Indexes'-column in 'Entities'-section
        grid('entities', 'indexQty', [
            'gridId' => 'stat',
            'move' => 'countQty',
            'rename' => 'IDX',
            'tooltip' => 'Indexes quantity',
            'summaryType' => 'sum',
            'jumpSectionId' => 'sqlIndexes',
            'jumpSectionActionId' => 'index',
        ]);
    }
    public function dontShowGridIdsAction() {
        cfgField('element', 'upload', 'wide', [
            'title' => 'Full width',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'refreshL10nsOnUpdate',
        ]);
        field('grid', 'formNotShowGridIds', [
            'title' => 'Don\'t show which involved columns',
            'storeRelationAbility' => 'many',
            'relation' => 'grid',
            'onDelete' => 'SET NULL',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'formNotHideFieldIds',
        ]);
        consider('grid', 'formNotShowGridIds', 'sectionId', ['required' => 'y']);
        grid('grid', 'formNotShowGridIds', [
            'gridId' => 'form',
            'move' => 'formNotHideFieldIds',
            'icon' => 'resources/images/icons/readonly.png',
            'editor' => '1',
        ]);
    }
    public function usability1Action() {
        enumset('alteredField', 'toggle', 'y', ['boxIcon' => 'resources/images/icons/btn-icon-lime.png']);
        enumset('alteredField', 'toggle', 'n', ['boxIcon' => 'resources/images/icons/btn-icon-red.png']);
    }
    public function compatiblecleanAction(){
        $text = coltype('TEXT');
        $text->set('elementId', $text->foreign('elementId')->fis())->save();
    }
    public function dnsAction() {
        action('dns', ['title' => 'Необходимые DNS-записи', 'fraction' => 'system', 'selectionRequired' => 'n']);
        section2action('actions','dns', [
            'roleIds' => 'dev',
            'move' => 'export',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
    }
    public function noExcel4RoleIdsAction() {
        grid('grid', 'skipExcel', ['icon' => 'resources/images/icons/no-excel.png']);
        enumset('grid', 'skipExcel', 'y', ['boxIcon' => 'resources/images/icons/no-excel.png']);
        field('section', 'noExcel4RoleIds', [
            'title' => 'Отключить Excel-экспорт для указанных ролей',
            'storeRelationAbility' => 'many',
            'relation' => 'role',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'groupBy',
        ]);
        grid('sections', 'noExcel4RoleIds', [
            'gridId' => 'grid',
            'move' => 'groupBy',
            'icon' => 'resources/images/icons/no-excel.png',
            'editor' => '1',
        ]);
        cfgField('element', 'price', 'shade', [
            'title' => 'Шейдинг',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'measure',
        ]);
    }
    public function alteredFieldsMoreToggleAction() {
        field('filter', 'allowZeroResultExceptPrefilteredWith', ['move' => 'allowZeroResult']);
        enumset('alteredField', 'toggle', 'creating', ['title' => 'Turned on, but for creating records only', 'move' => 'n', 'boxIcon' => 'resources/images/icons/btn-icon-lime-create.png']);
        enumset('alteredField', 'toggle', 'existing', ['title' => 'Turned on, but for existing records only', 'move' => 'creating', 'boxIcon' => 'resources/images/icons/btn-icon-lime-form.png']);
        field('alteredField', 'toggle', ['elementId' => 'radio']);
        field('grid', 'accessRoles', ['elementId' => 'radio']);
        field('filter', 'accessRoles', ['elementId' => 'radio']);
        field('alteredField', 'accessRoles', ['elementId' => 'radio']);
        field('grid', 'accessExcept', ['elementId' => 'multicheck']);
        field('filter', 'accessExcept', ['elementId' => 'multicheck']);
        field('alteredField', 'accessExcept', ['elementId' => 'multicheck']);
        param('grid', 'accessExcept', 'cols', '2');
        param('filter', 'accessExcept', 'cols', '2');
        param('alteredField', 'accessExcept', 'cols', '2');
    }
    public function disableQueueNoticeAction() {
        notice('queueTask', 'done', ['toggle' => 'n']);
        notice('queueTask', 'failed', ['toggle' => 'n']);
        notice('queueTask', 'resumed', ['toggle' => 'n']);
        notice('queueTask', 'started', ['toggle' => 'n']);
    }
    public function monthFieldIdAction() {
        field('entity', 'special', ['title' => 'Special fields', 'elementId' => 'span', 'move' => 'changeLogExcept']);
        field('entity', 'titleFieldId', ['move' => 'special']);
        field('entity', 'monthFieldId', [
            'title' => 'Field to setup and derive \'Month\' field from',
            'storeRelationAbility' => 'one',
            'relation' => 'field',
            'filter' => '`entityId` = "<?=$this->id?>" AND `columnTypeId` IN (6,9)',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'titleFieldId',
        ]);
        field('entity', 'filesGroupBy', ['move' => 'monthFieldId']);
        alteredField('entities', 'monthFieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('entities', 'special', ['gridId' => 'features', 'move' => '']);
        grid('entities', 'titleFieldId', [
            'gridId' => 'special',
            'move' => '',
            'composeTip' => '{TitleFieldId_alias}',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('entities', 'titleFieldId', 'alias', ['gridId' => 'special', 'toggle' => 'h', 'move' => 'titleFieldId']);
        grid('entities', 'monthFieldId', ['gridId' => 'special', 'move' => 'titleFieldId_alias', 'icon' => 'resources/images/icons/monthId.png']);
        grid('entities', 'filesGroupBy', ['move' => 'monthFieldId', 'gridId' => 'special']);
    }
    public function makeAction() {
        field('alteredField', 'jumpCreate', [
            'title' => '\'Create new\' button',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'jumpSectionActionId',
        ]);
        enumset('alteredField', 'jumpCreate', 'n', ['title' => 'Disabled', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('alteredField', 'jumpCreate', 'y', ['title' => 'Enabled', 'move' => 'n', 'boxIcon' => 'resources/images/icons/btn-icon-create.png']);
        grid('alteredFields', 'jumpCreate', ['gridId' => 'jump', 'move' => 'jumpSectionActionId', 'icon' => 'resources/images/icons/btn-icon-create.png']);
        alteredField('grid', 'fieldId', ['jumpCreate' => 'y', 'jumpArgs' => 'parent/{parent.entityId}/']);
    }
    public function missing2Action() {
        field('notice', 'tplIncAudio', ['title' => 'Звук', 'elementId' => 'upload', 'move' => 'tplIncBody']);
        param('notice', 'tplIncAudio', 'allowTypes', 'audio');
        field('noticeGetter', 'wsmsg', [
            'title' => 'Сообщение сбоку / WebSocket',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'y',
            'move' => 'method',
        ]);
        enumset('noticeGetter', 'wsmsg', 'y', ['title' => 'Да', 'move' => '', 'boxColor' => '120#00FF00']);
        enumset('noticeGetter', 'wsmsg', 'n', ['title' => 'Нет', 'move' => 'y', 'boxColor' => '000#D3D3D3']);
        field('noticeGetter', 'noticeId', ['move' => '']);
        field('noticeGetter', 'roleId', ['move' => 'noticeId']);
        field('noticeGetter', 'toggle', ['move' => 'roleId']);
        field('noticeGetter', 'getters', ['move' => 'toggle']);
        field('noticeGetter', 'criteriaRelyOn', ['move' => 'getters']);
        field('noticeGetter', 'criteriaInc', ['move' => 'criteriaRelyOn']);
        field('noticeGetter', 'criteriaEvt', ['move' => 'criteriaInc']);
        field('noticeGetter', 'criteriaDec', ['move' => 'criteriaEvt']);
        field('noticeGetter', 'method', ['move' => 'criteriaDec']);
        field('noticeGetter', 'wsmsg', ['move' => 'method']);
        field('noticeGetter', 'title', ['move' => 'wsmsg']);
        field('noticeGetter', 'email', ['move' => 'title']);
        field('noticeGetter', 'vk', ['move' => 'email']);
        field('noticeGetter', 'sms', ['move' => 'vk']);
        field('noticeGetter', 'http', ['move' => 'sms']);
        field('noticeGetter', 'features', ['move' => 'http']);
        field('noticeGetter', 'getters2', ['move' => 'features']);
        grid('noticeGetters', 'features', ['move' => 'getters']);
        grid('noticeGetters', 'http', ['gridId' => 'features', 'move' => 'method', 'editor' => '1']);
        grid('noticeGetters', 'method', ['gridId' => 'features', 'move' => 'wsmsg', 'rename' => 'Дублировать']);
        grid('noticeGetters', 'email', ['gridId' => 'method', 'move' => '']);
        grid('noticeGetters', 'vk', ['gridId' => 'method', 'move' => 'email']);
        grid('noticeGetters', 'sms', ['gridId' => 'method', 'move' => 'vk']);
        grid('noticeGetters', 'wsmsg', ['gridId' => 'features', 'move' => '', 'rename' => 'WS']);
        cfgField('element', 'number', 'shade', [
            'title' => 'Shade',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'measure',
        ]);
        field('filter', 'allowZeroResult', ['move' => 'defaultValue']);
        field('filter', 'allowZeroResultExceptPrefilteredWith', [
            'title' => 'If yes, except when filters are in use for fields',
            'storeRelationAbility' => 'many',
            'relation' => 'field',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'defaultValue',
        ]);
        consider('filter', 'allowZeroResultExceptPrefilteredWith', 'sectionId', ['foreign' => 'entityId', 'required' => 'y']);
        grid('filter', 'allowZeroResult', [
            'gridId' => 'data',
            'move' => 'defaultValue',
            'icon' => 'resources/images/icons/zero-result-ok.png',
            'rename' => 'Показывать все опции, в том числе<br>с пустыми результатами поиска',
        ]);
        grid('filter', 'allowZeroResultExceptPrefilteredWith', [
            'gridId' => 'data',
            'move' => 'allowZeroResult',
            'icon' => 'resources/images/icons/zero-result-ok-except-prefiltered.png',
            'rename' => 'Если да, то кроме случаев когда<br> используются фильтры для полей',
            'editor' => '1',
        ]);
        die('ok');
    }
    public function missing1Action(){
        section2action('fieldsAll','export', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        die('ok');
    }
    public function sqlindexesAction() {
        entity('sqlIndex', ['title' => 'MySQL Index', 'fraction' => 'system']);
        field('sqlIndex', 'entityId', [
            'title' => 'Entity',
            'mode' => 'readonly',
            'storeRelationAbility' => 'one',
            'relation' => 'entity',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => '',
        ]);
        field('sqlIndex', 'columns', [
            'title' => 'Covered fields',
            'mode' => 'required',
            'storeRelationAbility' => 'many',
            'relation' => 'field',
            'filter' => 'IFNULL(`columnTypeId`, 0) != 0 AND `entry` = 0',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'entityId',
        ]);
        param('sqlIndex', 'columns', 'optionAttrs', 'alias');
        consider('sqlIndex', 'columns', 'entityId', ['required' => 'y']);
        field('sqlIndex', 'alias', [
            'title' => 'Index name',
            'mode' => 'readonly',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'columns',
        ]);
        field('sqlIndex', 'type', [
            'title' => 'Index type',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'KEY',
            'move' => 'alias',
        ]);
        enumset('sqlIndex', 'type', 'KEY', ['title' => 'KEY', 'move' => '']);
        enumset('sqlIndex', 'type', 'UNIQUE', ['title' => 'UNIQUE', 'move' => 'KEY']);
        enumset('sqlIndex', 'type', 'FULLTEXT', ['title' => 'FULLTEXT', 'move' => 'UNIQUE']);
        field('sqlIndex', 'visibility', [
            'title' => 'Visibility',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'VISIBLE',
            'move' => 'type',
        ]);
        enumset('sqlIndex', 'visibility', 'VISIBLE', ['title' => 'VISIBLE', 'move' => '']);
        enumset('sqlIndex', 'visibility', 'INVISIBLE', ['title' => 'INVISIBLE', 'move' => 'VISIBLE']);
        entity('sqlIndex', ['titleFieldId' => 'alias']);
        section('sqlIndexes', [
            'title' => 'MySQL Indexes',
            'fraction' => 'system',
            'sectionId' => 'entities',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'extendsJs' => 'Indi.lib.controller.SqlIndex',
            'entityId' => 'sqlIndex',
            'move' => 'inQtySum',
            'roleIds' => 'dev',
        ]);
        section2action('sqlIndexes','index', ['roleIds' => 'dev', 'move' => '']);
        section2action('sqlIndexes','form', ['roleIds' => 'dev', 'move' => 'index']);
        section2action('sqlIndexes','save', [
            'roleIds' => 'dev',
            'move' => 'form',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('sqlIndexes','delete', [
            'roleIds' => 'dev',
            'move' => 'save',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('sqlIndexes','export', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        grid('sqlIndexes', 'columns', ['move' => '']);
        grid('sqlIndexes', 'alias', ['move' => 'columns']);
        grid('sqlIndexes', 'type', ['move' => 'alias']);
        grid('sqlIndexes', 'visibility', ['move' => 'type', 'editor' => 1]);
        alteredField('sqlIndexes', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('sqlIndexes', 'columns', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        section('sqlIndexesAll', [
            'title' => 'All indexes',
            'fraction' => 'system',
            'sectionId' => 'configuration',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'extendsJs' => 'Indi.lib.controller.SqlIndex',
            'entityId' => 'sqlIndex',
            'move' => 'paramsAll',
            'roleIds' => 'dev',
            'groupBy' => 'entityId',
        ]);
        section2action('sqlIndexesAll','index', ['roleIds' => 'dev', 'move' => '']);
        section2action('sqlIndexesAll','form', ['roleIds' => 'dev', 'move' => 'index']);
        section2action('sqlIndexesAll','save', [
            'roleIds' => 'dev',
            'move' => 'form',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('sqlIndexesAll','delete', [
            'roleIds' => 'dev',
            'move' => 'save',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('sqlIndexesAll','export', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        grid('sqlIndexesAll', 'entityId', ['move' => '']);
        grid('sqlIndexesAll', 'columns', ['move' => 'entityId']);
        grid('sqlIndexesAll', 'alias', ['move' => 'columns']);
        grid('sqlIndexesAll', 'type', ['move' => 'alias']);
        grid('sqlIndexesAll', 'visibility', ['move' => 'type', 'editor' => 1]);
        alteredField('sqlIndexesAll', 'entityId', ['mode' => 'required', 'jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('sqlIndexesAll', 'columns', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        filter('sqlIndexesAll', 'entityId', 'fraction', ['move' => '']);
        filter('sqlIndexesAll', 'entityId', ['move' => 'entityId_fraction']);
        filter('sqlIndexesAll', 'type', ['move' => 'entityId']);
        filter('sqlIndexesAll', 'visibility', ['move' => 'type']);

        foreach (m('entity')->all() as $entityR) {
            $def = m($entityR->table)->def();
            preg_match_all('~^\s+(?<type>UNIQUE|FULLTEXT|KEY|)(?: KEY)? `(?<name>[^`]+)` \(`(?<columns>[^)]+)`\)~m', $def, $idxA, PREG_SET_ORDER);
            foreach ($idxA as $m) {
                sqlIndex($entityR->table, $m['name'], [
                    'type' => $m['type'],
                    'columns' => str_replace('`', '', $m['columns'])
                ]);
            }
        }

        sqlIndex('columnType', 'type', ['type' => 'UNIQUE']);
        sqlIndex('entity', 'table', ['type' => 'UNIQUE']);
        sqlIndex('section', 'alias', ['type' => 'UNIQUE']);
        sqlIndex('element', 'alias', ['type' => 'UNIQUE']);
        sqlIndex('enumset', 'fieldId,alias', ['type' => 'UNIQUE']);
        sqlIndex('action', 'alias', ['type' => 'UNIQUE']);
        sqlIndex('role', 'alias', ['type' => 'UNIQUE']);
        sqlIndex('admin', 'email', ['type' => 'UNIQUE']);
        sqlIndex('resize', 'fieldId,alias', ['type' => 'UNIQUE']);
        sqlIndex('lang', 'alias', ['type' => 'UNIQUE']);
        db()->query('UPDATE `notice` SET `alias` = `id` WHERE `alias` = ""');
        sqlIndex('notice', 'entityId,alias', ['type' => 'UNIQUE']);
        sqlIndex('year', 'title', ['type' => 'UNIQUE']);
        sqlIndex('month', 'yearId,month', ['type' => 'UNIQUE']);
        sqlIndex('sqlIndex', 'entityId,alias', ['type' => 'UNIQUE']);
        sqlIndex('field', 'entityId,entry,alias', ['type' => 'UNIQUE']);
        sqlIndex('noticeGetter', 'noticeId,roleId', ['type' => 'UNIQUE']);
        die('ok');
    }
    public function gridNoClipSkipExcelAction() {
        field('grid', 'sizing', [
            'title' => 'Width calculation',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'auto',
            'move' => 'features',
        ]);
        enumset('grid', 'sizing', 'auto', ['title' => 'Auto', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('grid', 'sizing', 'noClip', ['title' => 'Not less than cell content', 'move' => 'auto', 'boxIcon' => 'resources/images/icons/fit.png']);
        field('grid', 'skipExcel', [
            'title' => 'Exclude from Excel export',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'rowReqIfAffected',
        ]);
        enumset('grid', 'skipExcel', 'n', ['title' => 'No', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('grid', 'skipExcel', 'y', ['title' => 'Yes', 'move' => 'n', 'boxIcon' => 'resources/images/icons/excel-deny.png']);
        field('grid', 'editor', ['move' => 'compose']);
        if ($_ = field('grid', 'width')) $_->delete();
        grid('grid', 'sizing', ['gridId' => 'features', 'move' => 'display', 'icon' => 'resources/images/icons/fit.png']);
        grid('grid', 'skipExcel', ['gridId' => 'features', 'move' => 'jump', 'icon' => 'resources/images/icons/excel-deny.png']);
        grid('grid', 'rowReqIfAffected', ['move' => 'compose']);
        if ($_ = field('notice', 'qtyReload')) $_->delete();
        grid('paramsAll', 'entityId', ['toggle' => 'h', 'move' => '']);
        grid('paramsAll', 'entityId', ['move' => '']);
        grid('paramsAll', 'entityId', 'table', ['move' => 'entityId']);
        grid('paramsAll', 'fieldId', ['move' => 'entityId_table']);
        grid('paramsAll', 'fieldId', 'alias', ['move' => 'fieldId']);
        grid('paramsAll', 'cfgField', ['move' => 'fieldId_alias']);
        grid('paramsAll', 'cfgField', 'alias', ['move' => 'cfgField']);
        grid('paramsAll', 'cfgValue', ['move' => 'cfgField_alias']);
        filter('paramsAll', 'entityId', 'fraction', ['move' => '']);
        filter('paramsAll', 'entityId', ['move' => 'entityId_fraction']);
        filter('paramsAll', 'fieldId', ['move' => 'entityId']);
        die('ok');
    }
    public function redirectMatchAction() {
        $hta = file_get_contents('.htaccess');
        $hta = preg_replace('~^RedirectMatch 404 .*$~m', 'RedirectMatch 404 /(\.git|sql|log)/', $hta);
        file_put_contents('.htaccess', $hta);
        file_put_contents('data/upload/.htaccess', 'php_value engine off');
        $this->exec('git add .htaccess');
        $this->exec('git add data/upload/.htaccess');
        $this->exec('git commit -m ".htaccess updated"', '', '(nothing|no changes).+?to commit');
        die('ok');
    }
    public function composeTemplateAction() {
        field('grid', 'compose', ['title' => 'Compose', 'elementId' => 'span', 'move' => 'tooltip']);
        field('grid', 'composeVal', [
            'title' => 'Value template',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'compose',
        ]);
        param('grid', 'composeVal', 'placeholder', '{someField1} - {someField2}{. SomeField3}');
        field('grid', 'composeTip', [
            'title' => 'Tooltip template',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'composeVal',
        ]);
        grid('grid', 'fieldId', 'alias', ['gridId' => 'source', 'toggle' => 'h', 'move' => 'further']);
        grid('grid', 'further', 'alias', ['gridId' => 'source', 'toggle' => 'h', 'move' => 'alias']);
        grid('grid', 'compose', ['gridId' => 'features', 'move' => 'display', 'rename' => 'Value']);
        grid('grid', 'editor', ['gridId' => 'compose']);
        grid('grid', 'composeVal', [
            'gridId' => 'compose',
            'move' => 'editor',
            'icon' => 'resources/images/icons/btn-icon-rename.png',
            'rename' => 'Value compose template',
            'editor' => '1',
        ]);
        grid('grid', 'composeTip', [
            'gridId' => 'compose',
            'move' => 'composeVal',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
            'rename' => 'Tooltip compose template',
            'editor' => '1',
        ]);
        grid('sections', 'defaultSortField', [
            'gridId' => 'load',
            'move' => 'rowsetSeparate',
            'composeVal' => '{defaultSortField defaultSortDirection}',
            'editor' => '1',
            'rowReqIfAffected' => 'n',
        ]);
        grid('sections', 'entityId', 'table', ['gridId' => 'data', 'toggle' => 'h', 'move' => 'entityId']);
        grid('sections', 'entityId', ['composeTip' => '{EntityId_table}']);
        grid('grid', 'fieldId', ['composeTip' => '{FieldId_alias}{_further_alias}']);
        grid('alteredFields', 'fieldId', 'alias', ['toggle' => 'h', 'move' => 'fieldId']);
        grid('alteredFields', 'fieldId', ['composeTip' => '{FieldId_alias}']);
        grid('filter', 'fieldId', 'alias', ['gridId' => 'data', 'toggle' => 'h', 'move' => 'defaultValue']);
        grid('filter', 'further', 'alias', ['gridId' => 'data', 'toggle' => 'h', 'move' => 'alias']);
        grid('filter', 'fieldId', ['composeTip' => '{FieldId_alias}{_further_alias}']);
        grid('sectionActions', 'actionId', 'alias', ['toggle' => 'h', 'move' => 'actionId']);
        grid('sectionActions', 'actionId', ['composeTip' => '{ActionId_alias}']);
        grid('entities', 'titleFieldId', 'alias', ['gridId' => 'features', 'toggle' => 'h', 'move' => 'titleFieldId']);
        grid('entities', 'titleFieldId', ['composeTip' => '{TitleFieldId_alias}']);
        grid('fields', 'relation', 'table', ['gridId' => 'fk', 'toggle' => 'h', 'move' => 'relation']);
        grid('fields', 'relation', ['composeTip' => '{Relation_table}']);
        grid('consider', 'consider', 'alias', ['toggle' => 'h', 'move' => 'consider']);
        grid('consider', 'consider', ['composeTip' => '{Consider_alias}']);
        grid('consider', 'foreign', 'alias', ['toggle' => 'h', 'move' => 'foreign']);
        grid('consider', 'foreign', ['composeTip' => '{consider_alias » foreign_alias}']);
        grid('consider', 'connector', 'alias', ['toggle' => 'h', 'move' => 'connector']);
        grid('consider', 'required', ['rename' => '']);
        grid('consider', 'connector', 'entityId', ['toggle' => 'h', 'move' => 'alias']);
        grid('consider', 'connector', ['composeVal' => '{connector_entityId}: {connector}', 'composeTip' => '{connector_alias}']);
        grid('inQtySum', 'sourceTarget', 'alias', ['toggle' => 'h', 'move' => 'sourceTarget']);
        grid('inQtySum', 'targetField', 'alias', ['toggle' => 'h', 'move' => 'targetField']);
        grid('inQtySum', 'targetField', ['composeTip' => '{sourceTarget_alias} » {targetField_alias}']);
        grid('inQtySum', 'sourceField', 'alias', ['toggle' => 'h', 'move' => 'sourceField']);
        grid('inQtySum', 'sourceField', ['composeTip' => '{SourceField_alias}']);
        grid('role', 'entityId', 'table', ['gridId' => 'binding', 'toggle' => 'h', 'move' => 'entityId']);
        grid('role', 'entityId', ['composeTip' => '{EntityId_table}']);
        grid('elementCfgField', 'relation', 'table', ['gridId' => 'fk', 'toggle' => 'h', 'move' => 'relation']);
        grid('elementCfgField', 'relation', ['composeTip' => '{Relation_table}']);
        grid('fieldsAll', 'relation', 'table', ['gridId' => 'fk', 'toggle' => 'h', 'move' => 'relation']);
        grid('fieldsAll', 'relation', ['composeTip' => '{Relation_table}']);
        grid('paramsAll', 'entityId', 'table', ['toggle' => 'h', 'move' => 'cfgValue']);
        grid('paramsAll', 'fieldId', 'alias', ['toggle' => 'h', 'move' => 'table']);
        grid('paramsAll', 'entityId', ['move' => 'alias']);
        grid('paramsAll', 'cfgField', 'alias', ['toggle' => 'h', 'move' => 'entityId']);
        grid('paramsAll', 'fieldId', ['composeTip' => '{entityId_table}.{fieldId_alias}']);
        grid('paramsAll', 'cfgField', ['composeTip' => '{CfgField_alias}']);
        if ($_ = grid('paramsAll', 'title')) $_->delete();
        field('grid', 'further', ['filter' => '`entry` = "0"']);
        die('ok');
    }
    public function inqtysumtoggleAction() {
        field('inQtySum', 'toggle', [
            'title' => 'Статус',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'y',
            'move' => 'title',
        ]);
        enumset('inQtySum', 'toggle', 'y', ['title' => 'Включен', 'move' => '', 'boxColor' => '120#00FF00']);
        enumset('inQtySum', 'toggle', 'n', ['title' => 'Выключен', 'move' => 'y', 'boxColor' => '000#FF0000']);
        grid('inQtySum', 'toggle', ['move' => 'sourceWhere']);
        section2action('inQtySum','toggle', [
            'roleIds' => 'dev',
            'move' => 'export',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        die('ok');
    }
    public function inqtysumAction() {
        entity('inQtySum', ['title' => 'Count in Qty/Sum', 'fraction' => 'system']);
        field('inQtySum', 'sourceEntity', [
            'title' => 'Source entity',
            'mode' => 'readonly',
            'storeRelationAbility' => 'one',
            'relation' => 'entity',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => '',
        ]);
        field('inQtySum', 'sourceTarget', [
            'title' => 'Foreign-key field pointing to target entry',
            'mode' => 'required',
            'storeRelationAbility' => 'one',
            'relation' => 'field',
            'filter' => '`storeRelationAbility` = "one" AND IFNULL(`relation`, 0) != "0" AND `entry` = "0"',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'sourceEntity',
        ]);
        consider('inQtySum', 'sourceTarget', 'sourceEntity', ['required' => 'y', 'connector' => 'entityId']);
        field('inQtySum', 'targetField', [
            'title' => 'Target field to update qty/sum in',
            'mode' => 'required',
            'storeRelationAbility' => 'one',
            'relation' => 'field',
            'filter' => '`entry` = "0" AND `id` NOT IN (SELECT `targetField` FROM `inQtySum`)',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'sourceTarget',
        ]);
        consider('inQtySum', 'targetField', 'sourceTarget', ['foreign' => 'relation', 'required' => 'y', 'connector' => 'entityId']);
        field('inQtySum', 'type', [
            'title' => 'Count type',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'radio',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'qty',
            'move' => 'targetField',
        ]);
        enumset('inQtySum', 'type', 'qty', ['title' => 'Qty', 'move' => '']);
        enumset('inQtySum', 'type', 'sum', ['title' => 'Sum', 'move' => 'qty']);
        field('inQtySum', 'sourceField', [
            'title' => 'Source field to append/deduct to/from target field',
            'storeRelationAbility' => 'one',
            'relation' => 'field',
            'filter' => '`elementId` IN (18,24,25)',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'type',
        ]);
        consider('inQtySum', 'sourceField', 'sourceEntity', ['required' => 'y', 'connector' => 'entityId']);
        field('inQtySum', 'sourceWhere', [
            'title' => 'SQL WHERE that source entry should match',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'sourceField',
        ]);
        field('inQtySum', 'title', [
            'title' => 'Auto title',
            'mode' => 'hidden',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'sourceWhere',
        ]);
        entity('inQtySum', ['titleFieldId' => 'targetField']);
        section('inQtySum', [
            'title' => 'Учет в количествах/суммах',
            'fraction' => 'system',
            'sectionId' => 'entities',
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'entityId' => 'inQtySum',
            'parentSectionConnector' => 'sourceEntity',
            'move' => 'fields',
            'rowsetSeparate' => 'no',
            'roleIds' => 'dev',
            'groupBy' => 'sourceTarget',
        ]);
        section2action('inQtySum','index', ['roleIds' => 'dev', 'move' => '']);
        section2action('inQtySum','form', ['roleIds' => 'dev', 'move' => 'index']);
        section2action('inQtySum','save', [
            'roleIds' => 'dev',
            'move' => 'form',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('inQtySum','delete', [
            'roleIds' => 'dev',
            'move' => 'save',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('inQtySum','export', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        grid('inQtySum', 'sourceTarget', ['move' => '', 'jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('inQtySum', 'targetField', ['move' => 'sourceTarget', 'jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('inQtySum', 'type', ['move' => 'targetField']);
        grid('inQtySum', 'sourceField', [
            'move' => 'type',
            'rename' => 'Поле источника',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('inQtySum', 'sourceWhere', ['move' => 'sourceField', 'editor' => 1]);
        alteredField('inQtySum', 'sourceTarget', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('inQtySum', 'targetField', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('inQtySum', 'sourceField', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        field('queueTask', 'procSpan', [
            'title' => 'Длительность',
            'mode' => 'readonly',
            'elementId' => 'number',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'procID',
        ]);
        grid('queueTask', 'procSpan', ['gridId' => 'proc', 'move' => 'procSince', 'icon' => 'resources/images/icons/plan-week.png']);
        die('ok');
    }
    public function migratecommittimestampAction() {
        die('disabled');
        cfgField('migration-commit-custom', [
            'title' => 'Custom-package: timestamp of last commit which we did run migrations',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)'
        ]);

        exec("git rev-parse HEAD 2>&1",$commit); $commit = join('', $commit);
        exec("git log -n 1 --pretty=format:%cd $commit 2>&1", $timestamp);
        param('migration-commit-custom', join('', $timestamp));
        die('ok');
    }
    public function instancetypeAction() {
        cfgField('instance-type', [
            'title' => 'In most cases can be either "prod", "demo" or "bare"',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)'
        ]);
        if (m('lang')->all('`adminCustomData` = "y"')->count() > 1) {
            $type = 'demo';
        } else if (ini()->db->type) {
            $type = ini()->db->type;
        } else {
            $type = 'prod';
        }
        param('instance-type', $type);
        die('ok');
    }
    public function migratecommitsAction() {

        cfgField('migration-commit-system', [
            'title' => 'System-package: last commit which we did run migrations',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)'
        ]);

        cfgField('migration-commit-custom', [
            'title' => 'Custom-package: last commit which we did run migrations',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)'
        ]);

        param('migration-commit-system', 'ee31c122a5417ebdc05c5fadaad8ef96e2831b2d');
        exec("git rev-parse HEAD 2>&1", $output);
        d($output);
        param('migration-commit-custom', join('', $output));
        die('ok');
    }
    public function updateactionsAction() {
        action('backup', ['title' => 'Резервное копирование', 'fraction' => 'system', 'selectionRequired' => 'n']);
        action('restore', ['title' => 'Восстановить', 'fraction' => 'system', 'selectionRequired' => 'n']);
        action('update', ['title' => 'Обновить', 'fraction' => 'system', 'selectionRequired' => 'n']);
        section2action('entities','backup', [
            'roleIds' => 'dev',
            'move' => 'export',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('entities','restore', [
            'roleIds' => 'dev',
            'move' => 'backup',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('entities','update', [
            'roleIds' => 'dev',
            'move' => 'restore',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        die('ok');
    }
	public function queuefinishedAction() {
		enumset('section', 'rowsetSeparate', 'auto', ['boxIcon' => 'resources/images/icons/field/inherit.png']);
		enumset('section', 'rowsetSeparate', 'yes', ['boxIcon' => 'resources/images/icons/field/readonly.png']);
		enumset('section', 'rowsetSeparate', 'no', ['boxIcon' => 'resources/images/icons/field/required.png']);
		enumset('field', 'mode', 'regular', ['boxIcon' => 'resources/images/icons/field/regular.png']);
		enumset('field', 'mode', 'required', ['boxIcon' => 'resources/images/icons/field/required.png']);
		enumset('field', 'mode', 'readonly', ['boxIcon' => 'resources/images/icons/field/readonly.png']);
		enumset('field', 'mode', 'hidden', ['boxIcon' => 'resources/images/icons/field/hidden.png']);
		enumset('field', 'storeRelationAbility', 'one', ['boxIcon' => 'resources/images/icons/btn-icon-login.png']);
		enumset('field', 'storeRelationAbility', 'many', ['boxIcon' => 'resources/images/icons/btn-icon-multikey.png']);
		
		notice('queueTask', 'done', [
		  'title' => 'Queue completed',
		  'fraction' => 'system',
		  'event' => '$this->applyState == \'finished\' && preg_match(\'~^L10n~\', $this->title)',
		  'roleId' => 'dev',
		  'qtySql' => '`applyState` = "finished" AND `title` LIKE "L10n%"',
		  'sectionId' => '',
		  'tplIncBody' => 'Queue task PID#<?=$this->row->procID?> is completed',
		]);
		noticeGetter('queueTask', 'done', 'dev', true);
		
		action('backup', ['title' => 'Backup', 'fraction' => 'system', 'selectionRequired' => 'n']);
		action('restore', ['title' => 'Restore', 'fraction' => 'system', 'selectionRequired' => 'n']);
		section2action('entities','backup', [
		  'roleIds' => 'dev',
		  'move' => 'export',
		  'south' => 'no',
		  'fitWindow' => 'n',
		  'l10n' => 'na',
		]);
		section2action('entities','restore', [
		  'roleIds' => 'dev',
		  'move' => 'backup',
		  'south' => 'no',
		  'fitWindow' => 'n',
		  'l10n' => 'na',
		]);
        
        db()->query("ALTER TABLE `section2action`
          CHANGE `sectionId` `sectionId` INT NULL AFTER `id`,
          CHANGE `actionId` `actionId` INT NULL AFTER `sectionId`,
          CHANGE `toggle` `toggle` ENUM ('y', 'n') DEFAULT 'y' NOT NULL AFTER `actionId`,
          CHANGE `filterOwner` `filterOwner` ENUM ('no', 'yes', 'certain') DEFAULT 'no' NOT NULL AFTER `roleIds`,
          CHANGE `south` `south` ENUM ('auto', 'yes', 'no') DEFAULT 'auto' NOT NULL AFTER `multiSelect`,
          CHANGE `fitWindow` `fitWindow` ENUM ('y', 'n') DEFAULT 'y' NOT NULL AFTER `south`
        ");
		die('ok');
	}

    public function checkorderAction(){
        foreach (['sectionId' => 'grid,section2action,filter', 'entityId' => 'field'] as $within => $tables) {
            foreach (ar($tables) as $table) {
                d("Table $table:");
                $withinIdA = db()->query("SELECT DISTINCT $within FROM $table WHERE $within IS NOT NULL")->col();
                foreach ($withinIdA as $withinId) {
                    $order = db()->query("SELECT `id`, `move` FROM $table WHERE $within = $withinId")->pairs();
                    if (count(array_unique($order)) < count($order)) {
                        d("withinId: $withinId");
                        d($order);
                    }
                }
            }
        }
        die('ok');
    }

    public function cosmeticAction() {
        if (uri()->command !== 'step2') {
            
        field('param', 'entityId', [
            'title' => 'Сущность',
            'storeRelationAbility' => 'one',
            'relation' => 'entity',
            'onDelete' => 'CASCADE',
            'mode' => 'readonly',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => '',
        ]);
        param('param', 'entityId', 'groupBy', 'fraction');
        if ($_ = grid('paramsAll', 'fieldId', 'entityId')) $_->delete();
        m('param')->all()->save();
        alteredField('paramsAll', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('params', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        if ($_ = filter('paramsAll', 'fieldId', 'entityId')) $_->delete();
        filter('paramsAll', 'entityId', 'fraction', true);
        filter('paramsAll', 'entityId', true);
        section('paramsAll', ['groupBy' => 'entityId']);
        consider('param', 'fieldId', 'entityId', ['required' => 'y']);
        grid('paramsAll', 'cfgValue', ['rowReqIfAffected' => 'y']);
        grid('params', 'cfgValue', ['rowReqIfAffected' => 'y']);
        field('queueTask', 'error', ['move' => 'queueSize']);
        field('queueTask', 'stage', ['mode' => 'hidden']);
        field('queueTask', 'state', ['mode' => 'hidden']);
        section2action('fields','activate', ['multiSelect' => '1']);
        enumset('section2action', 'south', 'no', ['boxIcon' => '', 'boxColor' => '000#FFFFFF']);
        field('queueTask', 'stages', [
            'title' => 'Шаги',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'applySize',
        ]);
        field('queueTask', 'props', [
            'title' => 'Свойства',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'stages',
        ]);
        field('queueTask', 'config', [
            'title' => 'Параметры',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'props',
        ]);
        section('queueTask')->nested('grid')->delete();
        field('queueTask', 'params', ['title' => 'Настройки']);
        grid('queueTask', 'title', ['move' => '']);
        grid('queueTask', 'stage', ['toggle' => 'h', 'move' => 'title']);
        grid('queueTask', 'state', ['toggle' => 'h', 'move' => 'stage']);
        grid('queueTask', 'error', ['toggle' => 'h', 'move' => 'state', 'rowReqIfAffected' => 'y']);
        grid('queueTask', 'props', ['move' => 'error', 'formToggle' => 'y']);
        grid('queueTask', 'datetime', ['gridId' => 'props', 'move' => '']);
        grid('queueTask', 'config', ['gridId' => 'props', 'move' => 'datetime']);
        grid('queueTask', 'params', ['gridId' => 'config', 'move' => '']);
        grid('queueTask', 'chunk', [
            'gridId' => 'config',
            'move' => 'params',
            'jumpSectionId' => 'queueChunk',
            'jumpSectionActionId' => 'index',
        ]);
        grid('queueTask', 'proc', ['gridId' => 'props', 'move' => 'config']);
        grid('queueTask', 'procID', ['gridId' => 'proc', 'move' => '']);
        grid('queueTask', 'procSince', ['gridId' => 'proc', 'move' => 'procID']);
        grid('queueTask', 'stages', ['move' => 'props', 'formToggle' => 'y']);
        grid('queueTask', 'count', ['gridId' => 'stages', 'move' => '']);
        grid('queueTask', 'countState', ['gridId' => 'count', 'move' => '']);
        grid('queueTask', 'countSize', ['gridId' => 'count', 'move' => 'countState']);
        grid('queueTask', 'items', ['gridId' => 'stages', 'move' => 'count']);
        grid('queueTask', 'itemsState', ['gridId' => 'items', 'move' => '']);
        grid('queueTask', 'itemsSize', ['gridId' => 'items', 'move' => 'itemsState']);
        grid('queueTask', 'itemsBytes', ['gridId' => 'items', 'move' => 'itemsSize', 'summaryType' => 'sum']);
        grid('queueTask', 'queue', ['gridId' => 'stages', 'move' => 'items']);
        grid('queueTask', 'queueState', ['gridId' => 'queue', 'move' => '']);
        grid('queueTask', 'queueSize', ['gridId' => 'queue', 'move' => 'queueState']);
        grid('queueTask', 'apply', ['gridId' => 'stages', 'move' => 'queue']);
        grid('queueTask', 'applyState', ['gridId' => 'apply', 'move' => '']);
        field('queueChunk', 'location', ['title' => 'Источник']);
        field('queueChunk', 'where', ['move' => 'location']);
        field('queueChunk', 'fraction', ['mode' => 'hidden']);
        field('queueChunk', 'stages', [
            'title' => 'Шаги',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'move',
        ]);
        grid('queueChunk', 'stages', ['move' => 'move', 'formToggle' => 'y']);
        grid('queueChunk', 'count', ['gridId' => 'stages']);
        grid('queueChunk', 'items', ['gridId' => 'stages']);
        grid('queueChunk', 'queue', ['gridId' => 'stages']);
        grid('queueChunk', 'apply', ['gridId' => 'stages']);
        field('queueChunk', 'queueChunkId', ['elementId' => 'combo', 'mode' => 'regular']);
        grid('queueChunk', 'itemsSize', ['jumpSectionId' => 'queueItem', 'jumpSectionActionId' => 'index']);
        field('queueItem', 'target', ['title' => 'Ключ в источнике']);
        field('notice', 'event', ['title' => 'Событие / PHP IF']);
        field('notice', 'qtySql', ['title' => 'Отображение / SQL WHERE']);
        enumset('notice', 'fraction', 'custom', ['title' => 'Проектная']);
        enumset('notice', 'fraction', 'system', ['title' => 'Системная']);
        grid('notices', 'trigger')->delete();
        grid('notices', 'trigger', ['move' => 'props']);
        grid('notices', 'entityId', [
            'gridId' => 'trigger',
            'move' => '',
            'jumpSectionId' => 'entities',
            'jumpSectionActionId' => 'form',
        ]);
        grid('notices', 'qty', ['gridId' => 'trigger', 'move' => 'entityId']);
        grid('notices', 'qtySql', ['gridId' => 'qty', 'move' => '']);
        grid('notices', 'sectionId', ['gridId' => 'qty', 'move' => 'qtySql']);
        grid('notices', 'tooltip', [
            'gridId' => 'qty',
            'move' => 'sectionId',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
            'editor' => '1',
        ]);
        grid('notices', 'qtyDiffRelyOn', ['gridId' => 'qty', 'move' => 'tooltip']);
        grid('notices', 'event', ['gridId' => 'trigger', 'move' => 'qty']);
        field('notice', 'tplFor', ['title' => 'Для вектора']);
        notice('queueTask', 'failed', ['tooltip' => 'Очереди задач, процессинг<br>которых завершился ошибкой']);
        field('noticeGetter', 'method', ['title' => 'Дублировать', 'elementId' => 'span', 'move' => 'http']);
        field('noticeGetter', 'email', ['title' => 'Email']);
        field('noticeGetter', 'vk', ['title' => 'VK']);
        field('noticeGetter', 'sms', ['title' => 'SMS']);
        field('noticeGetter', 'criteriaRelyOn', ['title' => 'Условие получения / PHP IF']);
        field('noticeGetter', 'roleId', ['title' => 'Роль получателя']);
        enumset('noticeGetter', 'criteriaRelyOn', 'event', ['title' => 'Общее - не зависит от вектора']);
        enumset('noticeGetter', 'criteriaRelyOn', 'getter', ['title' => 'Зависит от вектора - а значит и от получателя']);
        field('noticeGetter', 'criteriaEvt', ['title' => 'Получатели изменения']);
        field('noticeGetter', 'criteriaInc', ['title' => 'Получатели увеличения']);
        field('noticeGetter', 'criteriaDec', ['title' => 'Получатели уменьшения']);
        field('noticeGetter', 'getters', ['title' => 'Уточнение получателей', 'elementId' => 'span', 'move' => 'roleId']);
        field('noticeGetter', 'http', ['move' => 'roleId']);
        field('noticeGetter', 'noticeId', ['move' => '']);
        field('noticeGetter', 'roleId', ['move' => 'noticeId']);
        entity('noticeGetter', ['title' => 'Настройка получателей уведомления']);
        section('noticeGetters', ['title' => 'Настройки получателей']);
        field('notice', 'roleId', ['title' => 'Роли получателей']);
        field('noticeGetter', 'roleId', ['title' => 'Получатели с ролью']);
        enumset('noticeGetter', 'toggle', 'y', ['title' => 'Включены']);
        enumset('noticeGetter', 'toggle', 'n', ['title' => 'Выключены']);
        field('noticeGetter', 'http', ['title' => 'Делать HTTP-запрос']);
        field('noticeGetter', 'getters', ['title' => 'Кто именно получит']);
        field('noticeGetter', 'features', [
            'title' => 'Функции',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'sms',
        ]);
        section('noticeGetters', ['rowsetSeparate' => 'no']);
        field('noticeGetter', 'getters', ['title' => 'Требование к получателям / SQL WHERE']);
        field('noticeGetter', 'criteriaRelyOn', ['title' => 'Тип требования']);
        cfgField('element', 'string', 'placeholder', [
            'title' => 'Плейсхолдер',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'allowedTags',
        ]);
        section2action('lang','dict', ['toggle' => 'n']);
        section('enumset', ['extendsPhp' => 'Indi_Controller_Admin_Enumset']);
        section('elementCfgFieldEnumset', ['extendsPhp' => 'Indi_Controller_Admin_Enumset']);
        grid('enumset', 'cssStyle', [
            'gridId' => 'features',
            'move' => 'textColor',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
        ]);
        grid('elementCfgFieldEnumset', 'features', ['move' => 'alias']);
        grid('elementCfgFieldEnumset', 'boxIcon', [
            'gridId' => 'features',
            'move' => '',
            'icon' => 'resources/images/icons/icon-image.png',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
        ]);
        grid('elementCfgFieldEnumset', 'boxColor', [
            'gridId' => 'features',
            'move' => 'boxIcon',
            'icon' => 'resources/images/icons/box-color.png',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
        ]);
        grid('elementCfgFieldEnumset', 'textColor', [
            'gridId' => 'features',
            'move' => 'boxColor',
            'icon' => 'resources/images/icons/color-text.svg',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
        ]);
        grid('elementCfgFieldEnumset', 'cssStyle', [
            'gridId' => 'features',
            'move' => 'textColor',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
        ]);
        field('action', 'props', ['title' => 'Свойства', 'elementId' => 'span', 'move' => 'hasView']);
        field('action', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'props']);
        field('action', 'selection', ['title' => 'Выделение', 'elementId' => 'span', 'move' => 'features']);
        field('action', 'binding', ['title' => 'Привязка к коду', 'elementId' => 'span', 'move' => 'selection']);
        section('actions')->nested('grid')->delete();
        grid('actions', 'title', ['move' => '', 'editor' => '1']);
        grid('actions', 'props', ['move' => 'title']);
        grid('actions', 'toggle', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('actions', 'fraction', ['gridId' => 'props', 'move' => 'toggle', 'editor' => '1']);
        grid('actions', 'binding', ['gridId' => 'props', 'move' => 'fraction']);
        grid('actions', 'alias', ['gridId' => 'binding', 'move' => '', 'editor' => '1']);
        grid('actions', 'hasView', ['gridId' => 'binding', 'move' => 'alias', 'icon' => 'resources/images/icons/window.png']);
        grid('actions', 'features', ['move' => 'props']);
        grid('actions', 'icon', ['gridId' => 'features', 'move' => '', 'icon' => 'resources/images/icons/icon-image.png']);
        grid('actions', 'selection', ['gridId' => 'features', 'move' => 'icon']);
        grid('actions', 'selectionRequired', ['gridId' => 'selection', 'move' => '', 'editor' => '1']);
        grid('actions', 'multiSelect', ['gridId' => 'selection', 'move' => 'selectionRequired', 'icon' => 'resources/images/icons/btn-icon-multi-select.png']);
        enumset('action', 'toggle', 'h', ['title' => 'Скрыто', 'move' => 'n', 'boxColor' => '000#D3D3D3']);
        db()->query('UPDATE `action` SET `toggle` = "h" WHERE `display` = "0"');
        if ($_ = field('action', 'display')) $_->delete();
        field('action', 'fraction', ['elementId' => 'combo']);
        field('action', 'binding', ['mode' => 'hidden']);
        field('action', 'props', ['mode' => 'hidden']);
        field('action', 'selection', ['mode' => 'hidden']);

        field('action', 'title', ['move' => '']);
        field('action', 'alias', ['move' => 'title']);
        field('action', 'toggle', ['move' => 'alias']);
        field('action', 'fraction', ['move' => 'toggle']);
        field('action', 'binding', ['move' => 'fraction']);
        field('action', 'hasView', ['move' => 'binding']);
        field('action', 'props', ['move' => 'hasView']);
        field('action', 'features', ['move' => 'props']);
        field('action', 'icon', ['move' => 'features']);
        field('action', 'selection', ['move' => 'icon']);
        field('action', 'selectionRequired', ['move' => 'selection']);
        field('action', 'multiSelect', ['move' => 'selectionRequired']);

        enumset('role', 'demo', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('role', 'demo', 'y', ['title' => 'Да', 'move' => 'n', 'boxIcon' => 'resources/images/icons/readonly.png']);
        field('role', 'props', ['title' => 'Свойства', 'elementId' => 'span', 'move' => 'demo']);
        field('role', 'binding', ['title' => 'Привязка к коду', 'elementId' => 'span', 'move' => 'props']);
        field('role', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'binding']);
        field('role', 'other', ['title' => 'Разное', 'elementId' => 'span', 'move' => 'features']);

        section('role')->nested('grid')->delete();
        grid('role', 'title', ['move' => '', 'editor' => '1']);
        grid('role', 'move', ['move' => 'title']);
        grid('role', 'props', ['move' => 'move']);
        grid('role', 'toggle', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('role', 'fraction', ['gridId' => 'props', 'move' => 'toggle']);
        grid('role', 'binding', ['gridId' => 'props', 'move' => 'fraction']);
        grid('role', 'alias', ['gridId' => 'binding', 'move' => '']);
        grid('role', 'entityId', [
            'gridId' => 'binding',
            'move' => 'alias',
            'editor' => '1',
            'jumpSectionId' => 'entities',
            'jumpSectionActionId' => 'form',
        ]);
        grid('role', 'features', ['move' => 'props']);
        grid('role', 'demo', ['gridId' => 'features', 'move' => '', 'icon' => 'resources/images/icons/readonly.png']);
        grid('role', 'other', ['gridId' => 'features', 'move' => 'demo']);
        grid('role', 'dashboard', ['gridId' => 'other', 'move' => '', 'editor' => '1']);
        grid('role', 'maxWindows', ['gridId' => 'other', 'move' => 'dashboard', 'editor' => '1']);
        field('role', 'props', ['mode' => 'hidden']);
        field('role', 'binding', ['mode' => 'hidden']);
        field('role', 'other', ['mode' => 'hidden']);
        field('role', 'fraction', ['elementId' => 'combo']);
        field('role', 'title', ['move' => '']);
        field('role', 'alias', ['move' => 'title']);
        field('role', 'toggle', ['move' => 'alias']);
        field('role', 'fraction', ['move' => 'toggle']);
        field('role', 'entityId', ['move' => 'fraction']);
        field('role', 'features', ['move' => 'entityId']);
        field('role', 'demo', ['move' => 'features']);
        field('role', 'dashboard', ['move' => 'demo']);
        field('role', 'move', ['move' => 'dashboard']);
        field('role', 'maxWindows', ['move' => 'move']);
        field('role', 'props', ['move' => 'maxWindows']);
        field('role', 'binding', ['move' => 'props']);
        field('role', 'other', ['move' => 'binding']);

        field('admin', 'props', [
            'title' => 'Свойства',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'uiedit',
        ]);
        field('admin', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'props']);
        field('admin', 'roleId', ['move' => '']);
        field('admin', 'title', ['move' => 'roleId']);
        field('admin', 'email', ['move' => 'title']);
        field('admin', 'password', ['move' => 'email']);
        field('admin', 'phone', ['move' => 'password']);
        field('admin', 'toggle', ['move' => 'phone']);
        field('admin', 'features', ['move' => 'toggle']);
        field('admin', 'demo', ['move' => 'features']);
        field('admin', 'uiedit', ['move' => 'demo']);
        field('admin', 'props', ['move' => 'uiedit']);
        enumset('admin', 'demo', 'n', ['title' => 'Выключен', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('admin', 'demo', 'y', ['title' => 'Включен', 'move' => 'n', 'boxIcon' => 'resources/images/icons/readonly.png']);

        section('admins')->nested('grid')->delete();
        grid('admins', 'title', ['move' => '', 'editor' => '1']);
        grid('admins', 'props', ['move' => 'title']);
        grid('admins', 'toggle', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('admins', 'email', ['gridId' => 'props', 'move' => 'toggle', 'editor' => '1']);
        grid('admins', 'phone', ['gridId' => 'props', 'move' => 'email', 'editor' => '1']);
        grid('admins', 'features', ['move' => 'props']);
        grid('admins', 'demo', ['gridId' => 'features', 'move' => '', 'icon' => 'resources/images/icons/readonly.png']);
        grid('admins', 'uiedit', ['gridId' => 'features', 'move' => 'demo']);

        enumset('consider', 'required', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('consider', 'required', 'y', ['title' => 'Да', 'move' => 'n', 'boxIcon' => 'resources/images/icons/field/required.png']);
        grid('consider', 'required', ['rename' => '', 'tooltip' => '', 'icon' => 'resources/images/icons/field/required.png']);
        field('resize', 'fieldId', ['mode' => 'readonly']);
        field('resize', 'note', ['move' => 'alias']);
        field('resize', 'size', ['title' => 'Размер', 'elementId' => 'span', 'move' => 'note']);
        grid('resize', 'note', ['move' => 'mode', 'editor' => '1']);

        field('entity', 'props', ['elementId' => 'span']);
        field('entity', 'binding', ['elementId' => 'span']);
        field('entity', 'features', ['elementId' => 'span']);
        field('entity', 'space', ['elementId' => 'span']);
        field('entity', 'changeLog', ['elementId' => 'span']);

        field('entity', 'props', ['mode' => 'hidden']);
        field('entity', 'binding', ['mode' => 'hidden']);
        field('entity', 'features', ['mode' => 'hidden']);

        field('entity', 'title', ['move' => '']);
        field('entity', 'table', ['move' => 'title']);
        field('entity', 'extends', ['move' => 'table']);
        field('entity', 'fraction', ['move' => 'extends']);
        field('entity', 'titleFieldId', ['move' => 'fraction']);
        field('entity', 'filesGroupBy', ['move' => 'titleFieldId']);
        field('entity', 'props', ['move' => 'filesGroupBy']);
        field('entity', 'binding', ['move' => 'props']);
        field('entity', 'features', ['move' => 'binding']);
        field('entity', 'space', ['move' => 'features']);
        field('entity', 'spaceScheme', ['move' => 'space']);
        field('entity', 'spaceFields', ['move' => 'spaceScheme']);
        field('entity', 'changeLog', ['move' => 'spaceFields']);
        field('entity', 'changeLogToggle', ['move' => 'changeLog']);
        field('entity', 'changeLogExcept', ['move' => 'changeLogToggle']);

        grid('sections', 'entityId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'index']);
        grid('entities', 'title', ['move' => '', 'editor' => '1']);
        grid('entities', 'props', ['move' => 'title']);
        grid('entities', 'fraction', ['gridId' => 'props', 'move' => '']);
        grid('entities', 'binding', ['gridId' => 'props', 'move' => 'fraction']);
        grid('entities', 'table', ['gridId' => 'binding', 'move' => '', 'editor' => '1']);
        grid('entities', 'extends', [
            'gridId' => 'binding',
            'move' => 'table',
            'icon' => 'resources/images/icons/btn-icon-php-parent.png',
            'editor' => '1',
        ]);
        grid('entities', 'features', ['move' => 'props']);
        grid('entities', 'titleFieldId', ['gridId' => 'features', 'move' => '']);
        grid('entities', 'filesGroupBy', [
            'gridId' => 'features',
            'move' => 'titleFieldId',
            'icon' => 'resources/images/icons/group.png',
            'editor' => '1',
        ]);
        grid('entities', 'space', ['gridId' => 'features', 'move' => 'filesGroupBy']);
        grid('entities', 'spaceScheme', ['gridId' => 'space', 'move' => '']);
        grid('entities', 'spaceFields', ['gridId' => 'space', 'move' => 'spaceScheme']);
        grid('entities', 'changeLog', ['gridId' => 'features', 'move' => 'space']);
        grid('entities', 'changeLogToggle', ['gridId' => 'changeLog', 'move' => '']);
        grid('entities', 'changeLogExcept', ['gridId' => 'changeLog', 'move' => 'changeLogToggle']);
        section2action('alteredFields','toggle', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        field('alteredField', 'props', [
            'title' => 'Свойства',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'jumpArgs',
        ]);
        field('alteredField', 'features', [
            'title' => 'Функции',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'props',
        ]);
        section('alteredFields')->nested('grid')->delete();
        grid('alteredFields', 'fieldId', ['move' => '']);
        grid('alteredFields', 'props', ['move' => 'fieldId']);
        grid('alteredFields', 'toggle', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('alteredFields', 'access', ['gridId' => 'props', 'move' => 'toggle']);
        grid('alteredFields', 'accessRoles', [
            'gridId' => 'access',
            'move' => '',
            'rename' => 'Роли',
            'editor' => '1',
        ]);
        grid('alteredFields', 'accessExcept', [
            'gridId' => 'access',
            'move' => 'accessRoles',
            'editor' => '1',
            'jumpSectionId' => 'role',
            'jumpSectionActionId' => 'form',
        ]);
        grid('alteredFields', 'features', ['move' => 'props']);
        grid('alteredFields', 'alter', ['gridId' => 'features', 'move' => '']);
        grid('alteredFields', 'rename', [
            'gridId' => 'alter',
            'move' => '',
            'icon' => 'resources/images/icons/btn-icon-rename.png',
            'editor' => '1',
        ]);
        grid('alteredFields', 'mode', ['gridId' => 'alter', 'move' => 'rename', 'icon' => 'resources/images/icons/field/readonly.png']);
        grid('alteredFields', 'defaultValue', [
            'gridId' => 'alter',
            'move' => 'mode',
            'icon' => 'resources/images/icons/default.png',
            'editor' => '1',
        ]);
        grid('alteredFields', 'elementId', ['gridId' => 'alter', 'move' => 'defaultValue', 'editor' => '1']);
        grid('alteredFields', 'jump', ['gridId' => 'features', 'move' => 'alter']);
        grid('alteredFields', 'jumpSectionId', [
            'gridId' => 'jump',
            'move' => '',
            'icon' => 'resources/images/icons/tree2.png',
            'editor' => '1',
        ]);
        grid('alteredFields', 'jumpSectionActionId', [
            'gridId' => 'jump',
            'move' => 'jumpSectionId',
            'icon' => 'resources/images/icons/action.png',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
        ]);
        grid('alteredFields', 'jumpArgs', [
            'gridId' => 'jump',
            'move' => 'jumpSectionActionId',
            'icon' => 'resources/images/icons/args.png',
            'editor' => '1',
        ]);

        // Filters
        field('filter', 'consistence', [
            'title' => 'Показывать опции даже без результатов',
            'alias' => 'allowZeroResult',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => '0',
            'move' => 'flags',
        ]);
        enumset('filter', 'allowZeroResult', '0', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('filter', 'allowZeroResult', '1', ['title' => 'Да', 'move' => '0', 'boxIcon' => 'resources/images/icons/zero-result-ok.png']);
        field('filter', 'allowClear', [
            'title' => 'Запретить сброс значения',
            'alias' => 'denyClear',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => '0',
            'move' => 'allowZeroResult',
        ]);
        enumset('filter', 'denyClear', '0', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('filter', 'denyClear', '1', ['title' => 'Да', 'move' => '0', 'boxIcon' => 'resources/images/icons/clear-deny.png']);
        field('filter', 'ignoreTemplate', [
            'title' => 'Игнорировать шаблон опций',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => '0',
            'move' => 'denyClear',
        ]);
        enumset('filter', 'ignoreTemplate', '0', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('filter', 'ignoreTemplate', '1', ['title' => 'Да', 'move' => '0', 'boxIcon' => 'resources/images/icons/template-ignore.png']);
        field('filter', 'multiSelect', [
            'title' => 'Выбор более одного значения',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => '0',
            'move' => 'ignoreTemplate',
        ]);
        enumset('filter', 'multiSelect', '0', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('filter', 'multiSelect', '1', ['title' => 'Да', 'move' => '0', 'boxIcon' => 'resources/images/icons/btn-icon-multi-select.png']);
        db()->query('UPDATE `filter` SET `allowZeroResult` = "0"');
        db()->query('UPDATE `filter` SET `ignoreTemplate` = "0"');
        db()->query('UPDATE `filter` SET `denyClear` = IF(`denyClear` = "0", "1", "0")');
        field('filter', 'props', ['title' => 'Свойства', 'elementId' => 'span', 'move' => 'multiSelect']);
        field('filter', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'props']);
        field('filter', 'data', ['title' => 'Данные', 'elementId' => 'span', 'move' => 'features']);

        section('filter')->nested('grid')->delete();
        grid('filter', 'fieldId', [
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('filter', 'props', ['move' => 'fieldId']);
        grid('filter', 'toggle', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('filter', 'data', ['gridId' => 'props', 'move' => 'toggle']);
        grid('filter', 'further', [
            'gridId' => 'data',
            'move' => '',
            'icon' => 'resources/images/icons/join.png',
            'editor' => '1',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('filter', 'filter', [
            'gridId' => 'data',
            'move' => 'further',
            'icon' => 'resources/images/icons/filter-db-blue.png',
            'editor' => '1',
        ]);
        grid('filter', 'defaultValue', [
            'gridId' => 'data',
            'move' => 'filter',
            'icon' => 'resources/images/icons/default.png',
            'editor' => '1',
        ]);
        grid('filter', 'access', ['gridId' => 'props', 'move' => 'data']);
        grid('filter', 'accessRoles', ['gridId' => 'access', 'move' => '', 'editor' => '1']);
        grid('filter', 'accessExcept', [
            'gridId' => 'access',
            'move' => 'accessRoles',
            'editor' => '1',
            'jumpSectionId' => 'role',
            'jumpSectionActionId' => 'form',
        ]);
        grid('filter', 'features', ['move' => 'props']);
        grid('filter', 'display', ['gridId' => 'features', 'move' => '', 'rename' => 'Вид']);
        grid('filter', 'move', ['gridId' => 'display', 'move' => '']);
        grid('filter', 'rename', [
            'gridId' => 'display',
            'move' => 'move',
            'icon' => 'resources/images/icons/btn-icon-rename.png',
            'editor' => '1',
        ]);
        grid('filter', 'tooltip', [
            'gridId' => 'display',
            'move' => 'rename',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
            'editor' => '1',
        ]);
        grid('filter', 'flags', ['gridId' => 'features', 'move' => 'display']);
        grid('filter', 'allowZeroResult', [
            'gridId' => 'flags',
            'move' => '',
            'icon' => 'resources/images/icons/zero-result-ok.png',
            'tooltip' => 'Показывать все опции, в том числе<br>с пустыми результатами поиска',
        ]);
        grid('filter', 'denyClear', ['gridId' => 'flags', 'move' => 'allowZeroResult', 'icon' => 'resources/images/icons/clear-deny.png']);
        grid('filter', 'ignoreTemplate', ['gridId' => 'flags', 'move' => 'denyClear', 'icon' => 'resources/images/icons/template-ignore.png']);
        grid('filter', 'multiSelect', ['gridId' => 'flags', 'move' => 'ignoreTemplate', 'icon' => 'resources/images/icons/btn-icon-multi-select.png']);

        field('filter', 'props', ['mode' => 'hidden']);
        field('filter', 'features', ['mode' => 'hidden']);

        field('filter', 'sectionId', ['move' => '']);
        field('filter', 'fieldId', ['move' => 'sectionId']);
        field('filter', 'toggle', ['move' => 'fieldId']);
        field('filter', 'data', ['move' => 'toggle']);
        field('filter', 'further', ['move' => 'data']);
        field('filter', 'move', ['move' => 'further']);
        field('filter', 'filter', ['move' => 'move']);
        field('filter', 'defaultValue', ['move' => 'filter']);
        field('filter', 'access', ['move' => 'defaultValue']);
        field('filter', 'accessRoles', ['move' => 'access']);
        field('filter', 'accessExcept', ['move' => 'accessRoles']);
        field('filter', 'title', ['move' => 'accessExcept']);
        field('filter', 'display', ['move' => 'title']);
        field('filter', 'rename', ['move' => 'display']);
        field('filter', 'tooltip', ['move' => 'rename']);
        field('filter', 'flags', ['move' => 'tooltip']);
        field('filter', 'allowZeroResult', ['move' => 'flags']);
        field('filter', 'denyClear', ['move' => 'allowZeroResult']);
        field('filter', 'ignoreTemplate', ['move' => 'denyClear']);
        field('filter', 'multiSelect', ['move' => 'ignoreTemplate']);
        field('filter', 'props', ['move' => 'multiSelect']);
        field('filter', 'features', ['move' => 'props']);

        field('field', 'props', [
            'title' => 'Свойства',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'move',
        ]);
        field('field', 'mode', ['move' => 'alias']);

        section('fields')->nested('grid')->delete();
        grid('fields', 'title', ['move' => '', 'editor' => '1']);
        grid('fields', 'alias', ['move' => 'title', 'editor' => '1']);
        grid('fields', 'props', ['move' => 'alias']);
        grid('fields', 'mode', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/field/readonly.png']);
        grid('fields', 'fk', ['gridId' => 'props', 'move' => 'mode']);
        grid('fields', 'storeRelationAbility', [
            'gridId' => 'fk',
            'move' => '',
            'icon' => 'resources/images/icons/btn-icon-multikey.png',
            'editor' => 'enumNoCycle',
        ]);
        grid('fields', 'relation', [
            'gridId' => 'fk',
            'move' => 'storeRelationAbility',
            'rename' => 'Сущность',
            'editor' => '1',
            'jumpSectionId' => 'entities',
            'jumpSectionActionId' => 'form',
        ]);
        grid('fields', 'filter', [
            'gridId' => 'fk',
            'move' => 'relation',
            'icon' => 'resources/images/icons/btn-icon-filter.png',
            'editor' => '1',
        ]);
        grid('fields', 'onDelete', ['gridId' => 'fk', 'move' => 'filter', 'icon' => 'resources/images/icons/ondelete.png']);
        grid('fields', 'el', ['gridId' => 'props', 'move' => 'fk']);
        grid('fields', 'elementId', [
            'gridId' => 'el',
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'controlElements',
            'jumpSectionActionId' => 'form',
        ]);
        grid('fields', 'tooltip', [
            'gridId' => 'el',
            'move' => 'elementId',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
            'editor' => '1',
        ]);
        grid('fields', 'mysql', ['gridId' => 'props', 'move' => 'el']);
        grid('fields', 'columnTypeId', [
            'gridId' => 'mysql',
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'columnTypes',
            'jumpSectionActionId' => 'form',
        ]);
        grid('fields', 'defaultValue', [
            'gridId' => 'mysql',
            'move' => 'columnTypeId',
            'rename' => 'DEFAULT',
            'editor' => '1',
        ]);
        grid('fields', 'l10n', ['gridId' => 'props', 'move' => 'mysql', 'icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('fields', 'move', ['move' => 'props']);
        //
        section('fieldsAll')->nested('grid')->delete();
        grid('fieldsAll', 'entityId', ['move' => '']);
        grid('fieldsAll', 'title', ['move' => 'entityId', 'editor' => '1']);
        grid('fieldsAll', 'alias', ['move' => 'title', 'editor' => '1']);
        grid('fieldsAll', 'props', ['move' => 'alias']);
        grid('fieldsAll', 'mode', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/field/readonly.png']);
        grid('fieldsAll', 'fk', ['gridId' => 'props', 'move' => 'mode']);
        grid('fieldsAll', 'storeRelationAbility', [
            'gridId' => 'fk',
            'move' => '',
            'icon' => 'resources/images/icons/btn-icon-multikey.png',
            'editor' => 'enumNoCycle',
        ]);
        grid('fieldsAll', 'relation', [
            'gridId' => 'fk',
            'move' => 'storeRelationAbility',
            'rename' => 'Сущность',
            'editor' => '1',
            'jumpSectionId' => 'entities',
            'jumpSectionActionId' => 'form',
        ]);
        grid('fieldsAll', 'filter', [
            'gridId' => 'fk',
            'move' => 'relation',
            'icon' => 'resources/images/icons/btn-icon-filter.png',
            'editor' => '1',
        ]);
        grid('fieldsAll', 'onDelete', ['gridId' => 'fk', 'move' => 'filter', 'icon' => 'resources/images/icons/ondelete.png']);
        grid('fieldsAll', 'el', ['gridId' => 'props', 'move' => 'fk']);
        grid('fieldsAll', 'elementId', [
            'gridId' => 'el',
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'controlElements',
            'jumpSectionActionId' => 'form',
        ]);
        grid('fieldsAll', 'tooltip', [
            'gridId' => 'el',
            'move' => 'elementId',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
            'editor' => '1',
        ]);
        grid('fieldsAll', 'mysql', ['gridId' => 'props', 'move' => 'el']);
        grid('fieldsAll', 'columnTypeId', [
            'gridId' => 'mysql',
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'columnTypes',
            'jumpSectionActionId' => 'form',
        ]);
        grid('fieldsAll', 'defaultValue', [
            'gridId' => 'mysql',
            'move' => 'columnTypeId',
            'rename' => 'DEFAULT',
            'editor' => '1',
        ]);
        grid('fieldsAll', 'l10n', ['gridId' => 'props', 'move' => 'mysql', 'icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('fieldsAll', 'move', ['move' => 'props']);
        section('fieldsAll')->nested('filter')->delete();
        filter('fieldsAll', 'entityId', 'fraction', true);
        filter('fieldsAll', 'entityId', true);
        filter('fieldsAll', 'mode', true);
        filter('fieldsAll', 'elementId', true);
        filter('fieldsAll', 'columnTypeId', ['rename' => 'Тип столбца']);
        filter('fieldsAll', 'l10n', true);
        filter('fieldsAll', 'storeRelationAbility', true);
        filter('fieldsAll', 'relation', true);
        filter('fieldsAll', 'onDelete', true);

        section('elementCfgField')->nested('grid')->delete();
        grid('elementCfgField', 'title', ['move' => '', 'editor' => '1']);
        grid('elementCfgField', 'alias', ['move' => 'title', 'editor' => '1']);
        grid('elementCfgField', 'move', ['move' => 'alias']);
        grid('elementCfgField', 'props', ['move' => 'move']);
        grid('elementCfgField', 'mode', ['gridId' => 'props', 'move' => '', 'icon' => 'resources/images/icons/field/readonly.png']);
        grid('elementCfgField', 'fk', ['gridId' => 'props', 'move' => 'mode']);
        grid('elementCfgField', 'storeRelationAbility', [
            'gridId' => 'fk',
            'move' => '',
            'icon' => 'resources/images/icons/btn-icon-multikey.png',
            'editor' => 'enumNoCycle',
        ]);
        grid('elementCfgField', 'relation', [
            'gridId' => 'fk',
            'move' => 'storeRelationAbility',
            'rename' => 'Сущность',
            'editor' => '1',
            'jumpSectionId' => 'entities',
            'jumpSectionActionId' => 'form',
        ]);
        grid('elementCfgField', 'filter', [
            'gridId' => 'fk',
            'move' => 'relation',
            'icon' => 'resources/images/icons/btn-icon-filter.png',
            'editor' => '1',
        ]);
        grid('elementCfgField', 'el', ['gridId' => 'props', 'move' => 'fk']);
        grid('elementCfgField', 'elementId', [
            'gridId' => 'el',
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'controlElements',
            'jumpSectionActionId' => 'form',
        ]);
        grid('elementCfgField', 'tooltip', [
            'gridId' => 'el',
            'move' => 'elementId',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
            'editor' => '1',
        ]);
        grid('elementCfgField', 'mysql', ['gridId' => 'props', 'move' => 'el']);
        grid('elementCfgField', 'columnTypeId', [
            'gridId' => 'mysql',
            'move' => '',
            'editor' => '1',
            'jumpSectionId' => 'columnTypes',
            'jumpSectionActionId' => 'form',
        ]);
        grid('elementCfgField', 'defaultValue', [
            'gridId' => 'mysql',
            'move' => 'columnTypeId',
            'rename' => 'DEFAULT',
            'editor' => '1',
        ]);
        grid('elementCfgField', 'l10n', ['gridId' => 'props', 'move' => 'mysql', 'icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('entities', 'titleFieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('entities', 'spaceFields', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('entities', 'changeLogExcept', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        entity('noticeGetter', ['title' => 'Настройка получателей уведомления', 'fraction' => 'system']);
        field('noticeGetter', 'noticeId', [
            'title' => 'Уведомление',
            'mode' => 'readonly',
            'storeRelationAbility' => 'one',
            'relation' => 'notice',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => '',
        ]);
        field('noticeGetter', 'roleId', [
            'title' => 'Получатели с ролью',
            'mode' => 'readonly',
            'storeRelationAbility' => 'one',
            'relation' => 'role',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'noticeId',
        ]);
        field('noticeGetter', 'toggle', [
            'title' => 'Статус',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'y',
            'move' => 'roleId',
        ]);
        enumset('noticeGetter', 'toggle', 'y', ['title' => 'Включены', 'move' => '', 'boxColor' => '120#00FF00']);
        enumset('noticeGetter', 'toggle', 'n', ['title' => 'Выключены', 'move' => 'y', 'boxColor' => '000#FF0000']);
        field('noticeGetter', 'http', [
            'title' => 'Делать HTTP-запрос',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'toggle',
        ]);
        field('noticeGetter', 'getters', ['title' => 'Требование к получателям / SQL WHERE', 'elementId' => 'span', 'move' => 'http']);
        field('noticeGetter', 'criteriaRelyOn', [
            'title' => 'Тип требования',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'CASCADE',
            'elementId' => 'radio',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'event',
            'move' => 'getters',
        ]);
        enumset('noticeGetter', 'criteriaRelyOn', 'event', ['title' => 'Одинаковое для всех получателей', 'move' => '']);
        enumset('noticeGetter', 'criteriaRelyOn', 'getter', ['title' => 'Неодинаковое, зависит от получателя', 'move' => 'event']);
        field('noticeGetter', 'criteriaEvt', [
            'title' => 'Одинаковое для всех получателей',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'criteriaRelyOn',
        ]);
        param('noticeGetter', 'criteriaEvt', 'placeholder', '`someStatus` = "someValue"');
        field('noticeGetter', 'criteriaInc', [
            'title' => 'Для кого увеличение',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'criteriaEvt',
        ]);
        param('noticeGetter', 'criteriaInc', 'placeholder', '`id` = "<?=$this->row->someOwnerIdProp?>"');
        field('noticeGetter', 'criteriaDec', [
            'title' => 'Для кого уменьшение',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'criteriaInc',
        ]);
        param('noticeGetter', 'criteriaDec', 'placeholder', '`id` = "<?=$this->row->was(\'someOwnerIdProp\')?>"');
        field('noticeGetter', 'title', [
            'title' => 'Ауто титле',
            'mode' => 'hidden',
            'elementId' => 'string',
            'columnTypeId' => 'TEXT',
            'move' => 'criteriaDec',
        ]);
        consider('noticeGetter', 'title', 'roleId', ['foreign' => 'title', 'required' => 'y']);
        field('noticeGetter', 'method', ['title' => 'Дублировать', 'elementId' => 'span', 'move' => 'title']);
        field('noticeGetter', 'email', [
            'title' => 'Email',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'method',
        ]);
        enumset('noticeGetter', 'email', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#D3D3D3']);
        enumset('noticeGetter', 'email', 'y', ['title' => 'Да', 'move' => 'n', 'boxColor' => '120#00FF00']);
        field('noticeGetter', 'vk', [
            'title' => 'VK',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'email',
        ]);
        enumset('noticeGetter', 'vk', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#D3D3D3']);
        enumset('noticeGetter', 'vk', 'y', ['title' => 'Да', 'move' => 'n', 'boxColor' => '120#00FF00']);
        field('noticeGetter', 'sms', [
            'title' => 'SMS',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'vk',
        ]);
        enumset('noticeGetter', 'sms', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#D3D3D3']);
        enumset('noticeGetter', 'sms', 'y', ['title' => 'Да', 'move' => 'n', 'boxColor' => '120#00FF00']);
        field('noticeGetter', 'features', [
            'title' => 'Функции',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'sms',
        ]);
        field('noticeGetter', 'getters2', [
            'title' => 'Получатели',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'features',
        ]);
        entity('noticeGetter', ['titleFieldId' => 'roleId']);
        section('noticeGetters', [
            'title' => 'Настройки получателей',
            'fraction' => 'system',
            'sectionId' => 'notices',
            'entityId' => 'noticeGetter',
            'move' => '',
            'rowsetSeparate' => 'no',
            'defaultSortField' => 'roleId',
            'roleIds' => 'dev',
        ]);
        section2action('noticeGetters','index', ['roleIds' => 'dev', 'move' => '']);
        section2action('noticeGetters','form', ['roleIds' => 'dev', 'move' => 'index']);
        section2action('noticeGetters','save', [
            'roleIds' => 'dev',
            'move' => 'form',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section2action('noticeGetters','delete', [
            'roleIds' => 'dev',
            'move' => 'save',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        grid('noticeGetters', 'roleId', ['move' => '']);
        grid('noticeGetters', 'toggle', ['move' => 'roleId', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('noticeGetters', 'getters', ['move' => 'toggle']);
        grid('noticeGetters', 'criteriaRelyOn', ['gridId' => 'getters', 'move' => '']);
        grid('noticeGetters', 'criteriaEvt', ['gridId' => 'getters', 'move' => 'criteriaRelyOn', 'rename' => 'Одинаковое']);
        grid('noticeGetters', 'getters2', ['gridId' => 'getters', 'move' => 'criteriaEvt', 'rename' => 'Зависящее от получателя']);
        grid('noticeGetters', 'criteriaInc', ['gridId' => 'getters2', 'move' => '']);
        grid('noticeGetters', 'criteriaDec', ['gridId' => 'getters2', 'move' => 'criteriaInc']);
        grid('noticeGetters', 'features', ['move' => 'getters']);
        grid('noticeGetters', 'http', ['gridId' => 'features', 'move' => '', 'editor' => 1]);
        grid('noticeGetters', 'method', ['gridId' => 'features', 'move' => 'http']);
        grid('noticeGetters', 'email', ['gridId' => 'method', 'move' => '']);
        grid('noticeGetters', 'vk', ['gridId' => 'method', 'move' => 'email']);
        grid('noticeGetters', 'sms', ['gridId' => 'method', 'move' => 'vk']);
        alteredField('noticeGetters', 'noticeId', ['jumpSectionId' => 'notices', 'jumpSectionActionId' => 'form']);
        alteredField('noticeGetters', 'roleId', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);

        grid('sections', 'fraction', ['rowReqIfAffected' => 'y']);
        grid('sections', 'alias', ['rowReqIfAffected' => 'y']);
        grid('sections', 'extendsPhp', ['rowReqIfAffected' => 'y']);
        grid('sections', 'extendsJs', ['rowReqIfAffected' => 'y']);
        enumset('section', 'defaultSortDirection', 'DESC', ['cssStyle' => 'background-position: -3px -1px;']);
        enumset('section', 'defaultSortDirection', 'ASC', ['cssStyle' => 'background-position: -3px -1px;']);

        grid('queueTask', 'applySize', ['gridId' => 'apply', 'move' => 'applyState']);
        section2action('paramsAll','export', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        section('paramsAll', ['multiSelect' => '1']);
        param('grid', 'jumpArgs', 'placeholder', '?filter[someProp]=someValue');
        param('alteredField', 'jumpArgs', 'placeholder', '?filter[someProp]=someValue');
        //
        cfgField('element', 'string', 'wide', [
            'title' => 'Во всю ширину',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'placeholder',
        ]);
        grid('entities', 'fraction', ['rowReqIfAffected' => 'n']);
        field('section2action', 'roleIds', ['defaultValue' => '']);
        grid('alteredFields', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        param('filter', 'further', 'optionAttrs', 'storeRelationAbility');
        grid('filter', 'filter', ['icon' => 'resources/images/icons/filter-combo.png']);
        grid('grid', 'editor', ['icon' => 'resources/images/icons/edit-cell.png']);
        section2action('columnTypes','export', [
            'roleIds' => 'dev',
            'move' => 'delete',
            'south' => 'no',
            'fitWindow' => 'n',
            'l10n' => 'na',
        ]);
        coltype('DECIMAL(14,3)', ['elementId' => 'decimal143']);
        field('element', 'defaultType', [
            'title' => 'Тип столбца по умолчанию',
            'storeRelationAbility' => 'one',
            'relation' => 'columnType',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '0',
            'move' => 'storeRelationAbility',
        ]);
        grid('controlElements', 'defaultType', ['move' => 'storeRelationAbility', 'editor' => '1']);
        section('controlElements', ['multiSelect' => '1']);
        element('string', ['defaultType' => 'VARCHAR(255)']);
        element('move', ['defaultType' => 'INT(11)']);
        element('radio', ['defaultType' => 'ENUM']);
        element('textarea', ['defaultType' => 'TEXT']);
        element('multicheck', ['defaultType' => 'VARCHAR(255)']);
        element('check', ['defaultType' => 'BOOLEAN']);
        element('color', ['defaultType' => 'VARCHAR(10)']);
        element('calendar', ['defaultType' => 'DATE']);
        element('html', ['defaultType' => 'TEXT']);
        element('time', ['defaultType' => 'TIME']);
        element('number', ['defaultType' => 'INT(11)']);
        element('datetime', ['defaultType' => 'DATETIME']);
        element('hidden', ['defaultType' => 'VARCHAR(255)']);
        element('combo', ['defaultType' => 'INT(11)']);
        element('price', ['defaultType' => 'DECIMAL(11,2)']);
        element('decimal143', ['defaultType' => 'DECIMAL(14,3)']);
        element('icon', ['defaultType' => 'VARCHAR(255)']);
        cfgField('element', 'combo', 'optionAttrs', ['filter' => '`entry` = "0"']);
        grid('fields', 'filter', ['icon' => 'resources/images/icons/filter-combo.png']);
        grid('elementCfgField', 'filter', ['icon' => 'resources/images/icons/filter-combo.png']);
        grid('fieldsAll', 'filter', ['icon' => 'resources/images/icons/filter-combo.png']);
        param('field', 'elementId', 'optionAttrs', 'defaultType');
        enumset('section', 'rownumberer', '0', ['cssStyle' => '']);
        section2action('lang','export', ['multiSelect' => 'inherit']);
        section2action('lang','import', ['multiSelect' => '1']);
        } else {
        field('queueTask', 'stageState')->delete();
        field('section', 'multiSelect')->delete();
        }
        die('ok');
    }
    public function panelsAction(){
        Indi::iflush(true);
        set_time_limit(0);
        field('section', 'rowset', ['title' => 'Панели', 'mode' => 'hidden', 'elementId' => 'span', 'move' => 'features']);
        field('section', 'grid', ['title' => 'Грид', 'elementId' => 'span', 'move' => 'rowset']);
        field('section', 'gridToggle', [
            'title' => 'Статус',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'y',
            'move' => 'grid',
        ]);
        enumset('section', 'gridToggle', 'y', ['title' => 'Включен', 'move' => '', 'boxIcon' => 'resources/images/icons/grid.png']);
        enumset('section', 'gridToggle', 'n', ['title' => 'Выключен', 'move' => 'y', 'boxColor' => '000#FFFFFF']);
        field('section', 'groupBy', ['move' => 'gridToggle']);
        field('section', 'plan', ['title' => 'Календарь', 'elementId' => 'span', 'move' => 'groupBy']);
        field('section', 'planToggle', [
            'title' => 'Статус',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'plan',
        ]);
        enumset('section', 'planToggle', 'n', ['title' => 'Выключен', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('section', 'planToggle', 'y', ['title' => 'Включен', 'move' => 'n', 'boxIcon' => 'resources/images/icons/btn-icon-calendar.png']);
        field('section', 'planTypes', [
            'title' => 'Типы',
            'storeRelationAbility' => 'many',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'SET',
            'defaultValue' => 'month,week,day',
            'move' => 'planToggle',
        ]);
        enumset('section', 'planTypes', 'month', ['title' => 'Месяц', 'move' => '', 'boxIcon' => 'resources/images/icons/plan-month.png']);
        enumset('section', 'planTypes', 'week', ['title' => 'Неделя', 'move' => 'month', 'boxIcon' => 'resources/images/icons/plan-week.png']);
        enumset('section', 'planTypes', 'day', ['title' => 'День', 'move' => 'week', 'boxIcon' => 'resources/images/icons/plan-day.png']);
        field('section', 'tile', ['title' => 'Галерея', 'elementId' => 'span', 'move' => 'planTypes']);
        field('section', 'tileToggle', [
            'title' => 'Статус',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'tile',
        ]);
        enumset('section', 'tileToggle', 'n', ['title' => 'Выключена', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('section', 'tileToggle', 'y', ['title' => 'Включена', 'move' => 'n', 'boxIcon' => 'resources/images/icons/icon-image.png']);
        field('section', 'tileField', ['move' => 'tileToggle']);
        field('section', 'tileThumb', ['move' => 'tileField']);
        field('section', 'other', [
            'title' => 'Другие',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'tileThumb',
        ]);

        grid('sections', 'rowset', ['move' => 'features']);
        grid('sections', 'grid', ['gridId' => 'rowset', 'move' => '']);
        grid('sections', 'gridToggle', ['gridId' => 'grid', 'move' => '', 'icon' => 'resources/images/icons/grid.png']);
        grid('sections', 'groupBy', ['move' => 'gridToggle', 'gridId' => 'grid']);
        grid('sections', 'other', ['gridId' => 'rowset', 'move' => 'grid']);
        grid('sections', 'planToggle', [
            'gridId' => 'other',
            'move' => '',
            'icon' => 'resources/images/icons/plan-day.png',
            'rename' => 'Календарь',
        ]);
        grid('sections', 'tileToggle', [
            'gridId' => 'other',
            'move' => 'planToggle',
            'icon' => 'resources/images/icons/icon-image.png',
            'rename' => 'Галерея',
        ]);
        if ($_ = grid('sections', 'tileField')) $_->delete();
        if ($_ = grid('sections', 'tileThumb')) $_->delete();
        enumset('section', 'multiSelect', '0', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        grid('sections', 'multiSelect', ['move' => 'display', 'gridId' => 'features']);
        field('section', 'multiSelect', ['move' => 'groupBy']);
        field('section', 'rowsetDefault', [
            'title' => 'Панель по умолчанию',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'CASCADE',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'grid',
            'move' => 'showID',
        ]);
        enumset('section', 'rowsetDefault', 'grid', ['title' => 'Грид', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('section', 'rowsetDefault', 'planMonth', ['title' => 'Календарь - месяц', 'move' => 'grid', 'boxIcon' => 'resources/images/icons/plan-month.png']);
        enumset('section', 'rowsetDefault', 'planWeek', ['title' => 'Календарь - неделя', 'move' => 'planMonth', 'boxIcon' => 'resources/images/icons/plan-week.png']);
        enumset('section', 'rowsetDefault', 'planDay', ['title' => 'Календарь - день', 'move' => 'planWeek', 'boxIcon' => 'resources/images/icons/plan-day.png']);
        enumset('section', 'rowsetDefault', 'tile', ['title' => 'Галерея', 'move' => 'planDay', 'boxIcon' => 'resources/images/icons/icon-image.png']);
        grid('sections', 'rowsetDefault', ['gridId' => 'rowset', 'move' => 'other', 'icon' => 'resources/images/icons/rowset-default.png']);
        grid('sections', 'planTypes', [
            'gridId' => 'other',
            'toggle' => 'h',
            'move' => 'tileToggle',
            'rename' => 'Типы календаря',
        ]);
        field('action', 'multiSelect', [
            'title' => 'Режим работы с пакетным выделением записей',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'radio',
            'columnTypeId' => 'ENUM',
            'defaultValue' => '0',
            'move' => 'selectionRequired',
        ]);
        enumset('action', 'multiSelect', '0', ['title' => 'Нет, должна быть выбрана только одна запись', 'boxColor' => '000#FFFFFF']);
        enumset('action', 'multiSelect', '1', ['title' => 'Да, может быть выбрано несколько записей', 'boxIcon' => 'resources/images/icons/btn-icon-multi-select.png']);
        field('action', 'fraction', ['move' => 'alias']);
        grid('actions', 'multiSelect', ['move' => 'selectionRequired', 'icon' => 'resources/images/icons/btn-icon-multi-select.png']);
        grid('actions', 'title', ['move' => '']);
        grid('actions', 'toggle', ['move' => 'title']);
        grid('actions', 'fraction', ['move' => 'toggle']);
        grid('actions', 'alias', ['move' => 'fraction']);
        grid('actions', 'icon', ['move' => 'alias']);
        grid('actions', 'selectionRequired', ['move' => 'icon']);
        grid('actions', 'multiSelect', ['move' => 'selectionRequired']);
        grid('actions', 'display', ['move' => 'multiSelect']);
        action('up', ['multiSelect' => '1']);
        action('down', ['multiSelect' => '1']);
        action('toggle', ['multiSelect' => '1']);
        action('export', ['multiSelect' => '1']);
        action('copy', ['multiSelect' => '1']);
        filter('actions', 'multiSelect', true);

        field('section2action', 'multiSelect', [
            'title' => 'Изменить пакетный режим',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'radio',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'inherit',
            'move' => 'features',
        ]);
        enumset('section2action', 'multiSelect', 'inherit', ['title' => 'Нет, использовать пакетный режим по умолчанию', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('section2action', 'multiSelect', '1', ['title' => 'Да, включить для этого раздела', 'move' => 'inherit', 'boxIcon' => 'resources/images/icons/btn-icon-multi-select.png']);
        enumset('section2action', 'multiSelect', '0', ['title' => 'Да, выключить для этого раздела', 'move' => '0', 'boxIcon' => 'resources/images/icons/btn-icon-single-select.png']);
        field('section2action', 'mode', [
            'title' => 'Режим',
            'mode' => 'hidden',
            'elementId' => 'span',
            'move' => 'props',
        ]);
        grid('sectionActions', 'mode', ['gridId' => 'features', 'move' => 'view', 'tooltip' => 'Пакетный режим - режим работы<br>при пакетном выделении записей']);
        grid('sectionActions', 'actionId', 'multiSelect', [
            'gridId' => 'mode',
            'move' => '',
            'icon' => 'resources/images/icons/btn-icon-multi-select.png',
            'rename' => 'Пакетный режим по умолчанию',
        ]);
        grid('sectionActions', 'multiSelect', ['gridId' => 'mode', 'move' => 'multiSelect', 'icon' => 'resources/images/icons/btn-icon-multi-select.png']);
        enumset('section2action', 'l10n', 'na', ['title' => 'Не применимо', 'move' => 'qn', 'boxColor' => '000#FFFFFF']);
        section2action('sectionActions','delete', ['multiSelect' => '1']);
        section2action('grid','delete', ['multiSelect' => '1']);
        section2action('alteredFields','delete', ['multiSelect' => '1']);
        section2action('filter','delete', ['multiSelect' => '1']);
        section2action('fields','delete', ['multiSelect' => '1']);
        section2action('lang','export', ['multiSelect' => '0']);
        section2action('fieldsAll','delete', ['multiSelect' => '1']);
        section2action('queueTask','delete', ['multiSelect' => '1']);
        section2action('realtime','delete', ['roleIds' => 'dev', 'move' => 'index', 'multiSelect' => '1']);
        field('grid', 'panels', ['title' => 'Статус в панелях', 'elementId' => 'span', 'move' => 'gridId']);
        field('grid', 'toggle', ['title' => 'Грид', 'elementId' => 'radio']);
        field('grid', 'togglePlan', [
            'title' => 'Календарь',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'toggle',
        ]);
        enumset('grid', 'togglePlan', 'n', ['title' => 'Выключен', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('grid', 'togglePlan', 'y', ['title' => 'Включен', 'move' => 'n', 'boxIcon' => 'resources/images/icons/plan-day.png']);
        field('grid', 'toggleTile', [
            'title' => 'Галерея',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'togglePlan',
        ]);
        enumset('grid', 'toggleTile', 'n', ['title' => 'Выключен', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('grid', 'toggleTile', 'y', ['title' => 'Текст под картинкой', 'move' => 'n', 'boxIcon' => 'resources/images/icons/tile-bottom-lines.png']);
        enumset('grid', 'toggleTile', 'bottomBoxes', ['title' => 'Бокс под картинкой', 'move' => 'y', 'boxIcon' => 'resources/images/icons/tile-bottom-boxes.png']);
        enumset('grid', 'toggleTile', 'rightBoxes', ['title' => 'Бокс справа', 'move' => 'bottomBoxes', 'boxIcon' => 'resources/images/icons/tile-right-boxes.png']);
        grid('grid', 'panels', [
            'gridId' => 'properties',
            'move' => '',
            'rename' => 'Статус',
            'tooltip' => 'Статус в панелях',
        ]);
        grid('grid', 'toggle', ['gridId' => 'panels', 'move' => '', 'icon' => 'resources/images/icons/grid.png']);
        grid('grid', 'togglePlan', ['gridId' => 'panels', 'move' => 'toggle', 'icon' => 'resources/images/icons/plan-day.png']);
        grid('grid', 'toggleTile', ['gridId' => 'panels', 'move' => 'togglePlan', 'icon' => 'resources/images/icons/icon-image.png']);
        enumset('section', 'planToggle', 'y', ['title' => 'Yes', 'move' => 'n', 'boxIcon' => 'resources/images/icons/plan-day.png']);
        action('form', ['multiSelect' => '1']);
        enumset('grid', 'toggle', 'y', ['boxIcon' => 'resources/images/icons/grid.png', 'boxColor' => '', 'cssStyle' => '']);
        enumset('grid', 'toggle', 'n', ['boxIcon' => '', 'boxColor' => '000#FFFFFF', 'cssStyle' => '']);
        enumset('grid', 'toggle', 'h', ['boxIcon' => 'resources/images/icons/grid-gray.png', 'boxColor' => '', 'cssStyle' => '']);
        enumset('grid', 'toggle', 'e', ['boxIcon' => 'resources/images/icons/grid-rowbody.png', 'boxColor' => '', 'cssStyle' => '']);
        enumset('grid', 'toggle', 'y', ['move' => '']);
        enumset('grid', 'toggle', 'h', ['move' => 'y']);
        enumset('grid', 'toggle', 'e', ['move' => 'h']);
        enumset('grid', 'toggle', 'n', ['move' => 'e']);
        field('action', 'hasView', [
            'title' => 'Есть окно',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'radio',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'icon',
        ]);
        enumset('action', 'hasView', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('action', 'hasView', 'y', ['title' => 'Да', 'move' => 'n', 'boxIcon' => 'resources/images/icons/window.png']);
        action('index', ['hasView' => 'y']);
        action('form', ['hasView' => 'y']);
        action('start', ['hasView' => 'y']);
        action('print', ['hasView' => 'y']);
        action('receipt', ['hasView' => 'y']);
        action('form', ['multiSelect' => '1']);
        action('up', ['multiSelect' => '1']);
        action('down', ['multiSelect' => '1']);
        action('toggle', ['multiSelect' => '1']);
        action('export', ['multiSelect' => '1']);
        action('copy', ['multiSelect' => '1']);
        filter('actions', 'display')->delete();
        filter('actions', 'hasView', true);
        grid('actions', 'hasView', ['move' => 'icon', 'icon' => 'resources/images/icons/window.png']);
        grid('sectionActions', 'actionId', 'hasView', ['gridId' => 'win', 'move' => '', 'icon' => 'resources/images/icons/window.png']);
        db()->query("
            UPDATE `section2action` `sa`, `action` `a` 
            SET `sa`.`fitWindow` = 'n', `sa`.`south` = 'no', `sa`.`l10n` = 'na'
            WHERE `sa`.`actionId` = `a`.`id` AND `a`.`hasView` = 'n'
        ");
        enumset('section2action', 'l10n', 'na', ['title' => 'Не применимо', 'move' => 'n', 'boxColor' => '000#FFFFFF']);
        grid('sectionActions', 'actionId', 'hasView', ['gridId' => 'win', 'move' => '', 'icon' => 'resources/images/icons/window.png']);
        param('section2action', 'actionId', 'optionAttrs', ['cfgValue' => '3636']);
        enumset('section2action', 'multiSelect', 'separate', ['title' => 'Да, включить, но делать запрос для каждой записи', 'move' => '0', 'boxIcon' => 'resources/images/icons/select-queue.png']);
        action('index', ['icon' => 'resources/images/icons/grid.png']);
        section('queueTask', ['defaultSortField' => '-1', 'showID' => 'y']);
        enumset('section2action', 'south', 'no', ['boxColor' => '000#FFFFFF']);
        die('ok');
    }
    public function gridChunkAction(){
        field('grid', 'form', ['title' => 'В форме', 'elementId' => 'span', 'move' => 'colorEntry']);
        field('grid', 'formToggle', [
            'title' => 'Отображать',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'n',
            'move' => 'form',
        ]);
        enumset('grid', 'formToggle', 'n', ['title' => 'Нет', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('grid', 'formToggle', 'y', ['title' => 'Да', 'move' => 'n', 'boxIcon' => 'resources/images/icons/show-in-form.png']);
        field('grid', 'formMoreGridIds', [
            'title' => 'Добавить еще столбцы',
            'storeRelationAbility' => 'many',
            'relation' => 'grid',
            'onDelete' => 'SET NULL',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'formToggle',
        ]);
        param('grid', 'formMoreGridIds', 'groupBy', ['cfgValue' => field('grid', 'group')->id]);
        consider('grid', 'formMoreGridIds', 'sectionId', ['required' => 'y']);
        field('grid', 'formNotHideFieldIds', [
            'title' => 'Не скрывать поля',
            'storeRelationAbility' => 'many',
            'relation' => 'field',
            'onDelete' => 'SET NULL',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'formMoreGridIds',
        ]);
        consider('grid', 'formNotHideFieldIds', 'sectionId', ['foreign' => 'entityId', 'required' => 'y']);
        grid('grid', 'form', ['gridId' => 'features', 'move' => 'rowReqIfAffected']);
        grid('grid', 'formToggle', ['gridId' => 'form', 'move' => '', 'icon' => 'resources/images/icons/show-in-form.png']);
        grid('grid', 'formMoreGridIds', [
            'gridId' => 'form',
            'move' => 'formToggle',
            'icon' => 'resources/images/icons/join2.png',
            'editor' => '1',
        ]);
        grid('grid', 'formNotHideFieldIds', [
            'gridId' => 'form',
            'move' => 'formMoreGridIds',
            'icon' => 'resources/images/icons/field/required.png',
            'editor' => '1',
        ]);
        die('ok');
    }
    public function innodbAction() {
        set_time_limit(0);

        Indi::iflush(true);

        // Make all tables InnoDB
        if (uri()->command == 'engine') {
            foreach (m('entity')->all() as $entity) {
                $table = $entity->table;
                if (!m($table)->isVIEW()) {
                    if (db()->query("SHOW TABLE STATUS WHERE Name = '$table'")->fetch()['Engine'] == 'InnoDB') {
                        d($table . ' already InnoDB');                        
                    } else {
                        db()->query("ALTER TABLE `$table` ENGINE = InnoDB");
                        d($table . ' done');                        
                    }
                } else {
                    d($table . ' is VIEW');
                }
            }
            die('engine only');
        }


        // Add onDelete-field
        $fieldR = field('field', 'onDelete', [
            'title' => 'ON DELETE',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'onDelete' => 'RESTRICT',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'RESTRICT'
        ]);
        enumset('field', 'onDelete', '-', ['title' => '', 'move' => '', 'boxColor' => '000#FFFFFF']);
        enumset('field', 'onDelete', 'CASCADE', ['title' => 'CASCADE', 'move' => '-', 'boxIcon' => 'resources/images/icons/ondelete-cascade.png']);
        enumset('field', 'onDelete', 'SET NULL', ['title' => 'SET NULL', 'move' => 'CASCADE', 'boxIcon' => 'resources/images/icons/ondelete-setnull.png']);
        enumset('field', 'onDelete', 'RESTRICT', ['title' => 'RESTRICT', 'move' => 'SET NULL', 'boxIcon' => 'resources/images/icons/ondelete-restrict.png']);        m('field')->reload();
        field('field', 'onDelete', ['defaultValue' => '-', 'move' => 'filter']);
        db()->query("UPDATE `field` SET `onDelete` = '-' WHERE `id` != '{$fieldR->id}'");

        // Add gridcols to fields sections
        grid('fieldsAll', 'onDelete', ['gridId' => 'fk', 'move' => 'filter', 'icon' => 'resources/images/icons/ondelete.png']);
        grid('fields', 'onDelete', ['gridId' => 'fk', 'move' => 'filter', 'icon' => 'resources/images/icons/ondelete.png']);
        grid('enumset', 'title', ['editor' => '1', 'rowReqIfAffected' => 'y']);
        grid('enumset', 'alias', ['editor' => '1']);

        // Setup CASCADE values for onDelete for all single-value non-config non-enumset foreign key fields
        db()->query('
            UPDATE `field` 
            SET `onDelete` = "CASCADE" 
            WHERE 1
              AND `storeRelationAbility` = "one" 
              AND `relation` != "6"
              AND `entry` = "0"
        ');

        // Setup SET NULL values for onDelete for all multi-value non-config non-enumset foreign key fields
        db()->query('
            UPDATE `field` 
            SET `onDelete` = "SET NULL" 
            WHERE 1
              AND `storeRelationAbility` = "many" 
              AND `relation` != "6"
              AND `entry` = "0"
        ');

        // Setup RESTRICT values for onDelete for all non-config fields, pointing to enumset entity
        db()->query('
            UPDATE `field` 
            SET `onDelete` = "RESTRICT" 
            WHERE 1
              AND `relation` = "6"
              AND `entry` = "0"
        ');

        // Setup non-CASCADE values
        field('alteredField', 'elementId', ['onDelete' => 'SET NULL']);
        field('alteredField', 'jumpSectionId', ['onDelete' => 'SET NULL']);
        field('alteredField', 'jumpSectionActionId', ['onDelete' => 'SET NULL']);
        field('field', 'relation', ['onDelete' => 'RESTRICT']);
        field('field', 'elementId', ['onDelete' => 'RESTRICT']);
        field('field', 'columnTypeId', ['onDelete' => 'RESTRICT']);
        field('section', 'parentSectionConnector', ['onDelete' => 'RESTRICT']);

        foreach ($sortById = m('section')->all('`defaultSortField` = "-1"') as $section)
            $section->set('defaultSortField', 0)->save();

        foreach ($connectorById = m('consider')->all('`connector` = "-1"') as $consider)
            $consider->set('connector', 0)->save();

        field('section', 'defaultSortField', ['onDelete' => 'SET NULL']);
        field('section', 'groupBy', ['onDelete' => 'SET NULL']);
        field('section', 'tileField', ['onDelete' => 'SET NULL']);
        field('section', 'tileThumb', ['onDelete' => 'SET NULL']);
        field('section', 'colorField', ['onDelete' => 'SET NULL']);
        field('section', 'colorFurther', ['onDelete' => 'SET NULL']);
        field('role', 'entityId', ['onDelete' => 'RESTRICT']);
        field('grid', 'jumpSectionId', ['onDelete' => 'SET NULL']);
        field('grid', 'jumpSectionActionId', ['onDelete' => 'SET NULL']);
        field('grid', 'colorField', ['onDelete' => 'SET NULL']);
        field('entity', 'titleFieldId', ['onDelete' => 'SET NULL']);
        field('entity', 'filesGroupBy', ['onDelete' => 'SET NULL']);


        // Add ibfk
        foreach (m('field')->all("`onDelete` != '-'") as $field) {
            $table = $field->foreign('entityId')->table;
            if (m($table)->isVIEW()) {
                d($table . '.' . $field->alias . ' - VIEW');
            } else {
                d($table . '.' . $field->alias);
                $field->addIbfk();
            }
        }
        foreach ($sortById as $section) $section->set('defaultSortField', -1)->save();
        foreach ($connectorById as $consider) $consider->set('connector', -1)->save();

        // Create whichEntities cfgField to be able to specify the list of entities, that multi-entity foreign key fields can point to
        cfgField('element', 'combo', 'whichEntities', [
            'title' => 'Какие сущности',
            'storeRelationAbility' => 'many',
            'relation' => 'entity',
            'onDelete' => 'SET NULL',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'filterOwner',
        ]);

        // Setup whichEntities-param for all multi-entity foreign key fields we have
        param('field', 'entry', 'whichEntities', ['cfgValue' => m('element')->id()]);
        // Mind changelog
        if ($entityIdsHavingChangeLogToggledOn = m('entity')
            ->all('`changeLogToggle` = "all" OR `changeLogExcept` != ""')
            ->ids())
            param('changeLog', 'entryId', 'whichEntities', ['cfgValue' => $entityIdsHavingChangeLogToggledOn]);
        param('changeLog', 'adminId', 'whichEntities', ['cfgValue' => im(Indi_Db::role())]);
        param('realtime', 'adminId', 'whichEntities', ['cfgValue' => im(Indi_Db::role())]);
        if ($colorFieldIds = db()->query('SELECT DISTINCT `colorField` FROM `grid` WHERE NOT ISNULL(`colorField`)')->in())
            if ($entityIds = db()->query("SELECT DISTINCT `entityId` FROM `field` WHERE `entry` = '0' AND `id` IN ($colorFieldIds)")->in())
                param('grid', 'colorEntry', 'whichEntities', ['cfgValue' => $entityIds]);

        field('entity', 'titleFieldId', ['filter' => '`entityId` = "<?=$this->id?>" AND IFNULL(`columnTypeId`, 0) != "0"']);
        field('changeLog', 'fieldId', ['filter' => 'IFNULL(`columnTypeId`, 0) != "0"']);
        field('section', 'groupBy', ['filter' => 'IFNULL(`columnTypeId`, 0) != "0"']);
        field('notice', 'sectionId', ['filter' => 'FIND_IN_SET(`sectionId`, "<?=m(\'section\')->all(\'IFNULL(`sectionId`, 0) = "0"\')->column(\'id\', true)?>")']);
        field('consider', 'consider', ['filter' => '`id` != "<?=$this->fieldId?>" AND IFNULL(`columnTypeId`, 0) != "0"']);
        die('ok');
    }
    public function queueResumeAction() {
        notice('queueTask', 'resumed', [
            'title' => 'Очередь задач возобновлена',
            'fraction' => 'system',
            'event' => '$this->affected("procID", true)',
            'roleId' => 'dev',
            'qtySql' => '`procID` != "0"',
            'tplIncBody' => 'Очередь задач возобновлена с PID: <?=$this->row->procID?>',
        ]);
        noticeGetter('queueTask', 'resumed', 'dev', true);
        field('noticeGetter', 'criteria')->delete();
        field('noticeGetter', 'mail')->delete();
        field('noticeGetter', 'http', [
            'title' => 'HTTP-запрос',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'title',
        ]);
        grid('noticeGetters', 'http', ['move' => 'criteriaEvt', 'editor' => '1']);
        noticeGetter('queueTask', 'failed', 'dev', ['http' => '/queueTask/run/id/{id}/gapikey/']);
        section2action('realtime','restart')->delete();
        section2action('realtime','form')->delete();
        section2action('realtime','save')->delete();
        die('ok');
    }
    public function changelogAction() {
        field('changeLog', 'changerId', ['alias' => 'adminId']);
        field('changeLog', 'key', ['alias' => 'entryId']);
        consider('changeLog', 'adminId', 'roleId', ['foreign' => 'entityId', 'required' => 'y']);
        field('entity', 'changeLogToggle', [
            'title' => 'Журнал изменений',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'elementId' => 'combo',
            'columnTypeId' => 'ENUM',
            'defaultValue' => 'none',
            'move' => 'filesGroupBy',
        ]);
        enumset('entity', 'changeLogToggle', 'none', ['title' => 'Нет', 'move' => '']);
        enumset('entity', 'changeLogToggle', 'all', ['title' => 'Да', 'move' => 'none']);
        field('entity', 'changeLogExcept', [
            'title' => 'Кроме полей',
            'storeRelationAbility' => 'many',
            'relation' => 'field',
            'filter' => '`entityId` = "<?=$this->id?>"',
            'elementId' => 'combo',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'changeLogToggle',
        ]);
        field('changeLog', 'changerType')->delete();
        param('changeLog', 'datetime', 'displayTimeFormat', ['cfgValue' => 'H:i:s']);
        field('notice', 'sectionId', ['filter' => '']);
        die('ok');
    }
    public function missingThingsAction() {
        action('revert', ['title' => 'Восстановить', 'fraction' => 'system', 'icon' => 'resources/images/icons/revert.png']);
        action('author', ['title' => 'Автор', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-author.png']);
        action('cancel', ['title' => 'Отменить', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-cancel.png']);
        action('confirm', ['title' => 'Подтвердить', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-confirm.png']);
        action('deactivate', ['title' => 'Деактивировать', 'fraction' => 'system', 'icon' => 'resources/images/icons/field/readonly.png']);
        action('notify', ['title' => 'Уведомить', 'fraction' => 'system']);
        action('pay', ['title' => 'Оплатить', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-pay.png']);
        action('receipt', ['title' => 'Чек', 'fraction' => 'system', 'icon' => 'resources/images/icons/receipt.png']);
        action('refund', ['title' => 'Вернуть платеж', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-refund.png']);
        action('reset', ['title' => 'Сбросить', 'fraction' => 'system']);
        action('start', ['title' => 'Начать', 'fraction' => 'system']);
        cfgField('element', 'combo', 'substr', [
            'title' => 'Длина опции не более',
            'elementId' => 'number',
            'columnTypeId' => 'INT(11)',
            'defaultValue' => '50',
            'move' => 'colorField',
        ]);
        if ($_ = cfgField('element', 'combo', 'ignoreAlternate'))
            cfgField('element', 'combo', 'ignoreAlternate', ['alias' => 'filterOwner']);

        cfgField('element', 'combo', 'filterOwner', [
            'title' => 'Владельцам - только свои опции',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'substr',
        ]);
        cfgField('element', 'textarea', 'shade', [
            'title' => 'Шейдинг',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'allowedTags',
        ]);
        field('admin', 'phone', [
            'title' => 'Телефон',
            'elementId' => 'string',
            'columnTypeId' => 'VARCHAR(255)',
            'move' => 'email',
        ]);
        param('admin', 'phone', 'inputMask', ['cfgValue' => '+9 (999) 999-99-99']);
        grid('admins', 'password', ['fieldId' => 'phone', 'editor' => '1']);
        if ($_ = field('filter', 'any')) field('filter', 'any', ['alias' => 'multiSelect']);
        field('filter', 'multiSelect', [
            'title' => 'Выбор более одного значения',
            'elementId' => 'check',
            'columnTypeId' => 'BOOLEAN',
            'defaultValue' => '0',
            'move' => 'ignoreTemplate',
        ]);
        field('filter', 'accessExcept', ['elementId' => 'combo']);
        param('realtime', 'spaceUntil', 'displayTimeFormat', ['cfgValue' => 'H:i:s']);
        grid('realtime', 'spaceUntil', ['toggle' => 'y']);
        grid('realtime', 'adminId', ['move' => 'roleId', 'rowReqIfAffected' => 'y']);
        db()->query('UPDATE `field` SET `move` = `id` WHERE `entityId` = "8"');
        field('section2action', 'sectionId', ['move' => '']);
        field('section2action', 'actionId', ['move' => 'sectionId']);
        field('section2action', 'toggle', ['move' => 'actionId']);
        field('section2action', 'access', ['move' => 'toggle']);
        field('section2action', 'roleIds', ['move' => 'access']);
        field('section2action', 'filterOwner', ['move' => 'roleIds']);
        field('section2action', 'filterOwnerRoleIds', ['move' => 'filterOwner']);
        field('section2action', 'move', ['move' => 'filterOwnerRoleIds']);
        field('section2action', 'title', ['move' => 'move']);
        field('section2action', 'features', ['move' => 'title']);
        field('section2action', 'south', ['move' => 'features']);
        field('section2action', 'fitWindow', ['move' => 'south']);
        field('section2action', 'rename', ['move' => 'fitWindow']);
        field('section2action', 'l10n', ['move' => 'rename']);
        field('section2action', 'view', ['move' => 'l10n']);
        field('section2action', 'win', ['move' => 'view']);
        field('section2action', 'props', ['move' => 'win']);
        field('section2action', 'roleIds', ['elementId' => 'combo']);
        die('ok');
    }
    public function fieldmoveAction(){
        foreach (m('entity')->all()->column('table') as $table) {
            $colA = m($table)->fields(null, 'columns');
            $def = db()->query("SHOW CREATE TABLE `$table`")->cell(1);
            preg_match_all('~^  `([^`]+)` .*?(?:,)$~m', $def, $m);
            array_walk($m[0], fn (&$line) => $line = trim($line, ' ,'));
            $defA = array_combine($m[1], $m[0]);
            //d($colA);
            //d($posA);
            //d($defA);
            $sql = "ALTER TABLE `$table`\n";
            $change = [];
            foreach ($colA as $idx => $col) $change []= "\tCHANGE `$col` {$defA[$col]} AFTER `" . ($colA[$idx - 1] ?: 'id') . "`";
            $sql .= im($change, ",\n") . ";";
            db()->query($sql);
        }
        db()->query('DELETE FROM `enumset` WHERE `fieldId` = 0');
        $c = coltype('INT(11)');
        $c->set('elementId', $c->foreign('elementId')->column('id', ','))->save();
        die('ok');
    }
    public function enumsetStyleAction() {
        field('enumset', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'move']);
        field('enumset', 'boxIcon', [
            'title' => 'Иконка',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'icon',
            'move' => 'features',
        ]);
        field('enumset', 'boxColor', [
            'title' => 'Цвет бокса',
            'columnTypeId' => 'VARCHAR(10)',
            'elementId' => 'color',
            'move' => 'boxIcon',
        ]);
        field('enumset', 'textColor', [
            'title' => 'Цвет текста',
            'columnTypeId' => 'VARCHAR(10)',
            'elementId' => 'color',
            'move' => 'boxColor',
        ]);
        field('enumset', 'cssStyle', [
            'title' => 'CSS стили',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'textColor',
        ]);
        section('enumset', ['multiSelect' => '1'])->nested('grid')->delete();
        grid('enumset', 'title', ['move' => '']);
        grid('enumset', 'alias', ['move' => 'title']);
        grid('enumset', 'features', ['move' => 'alias']);
        grid('enumset', 'boxIcon', [
            'move' => '',
            'gridId' => 'features',
            'editor' => 1,
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/icon-image.png',
        ]);
        grid('enumset', 'boxColor', [
            'move' => 'boxIcon',
            'gridId' => 'features',
            'editor' => 1,
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/box-color.png',
        ]);
        grid('enumset', 'textColor', [
            'move' => 'boxColor',
            'gridId' => 'features',
            'editor' => 1,
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/color-text.svg',
        ]);
        grid('enumset', 'move', ['move' => 'features']);
        $fieldIdA = db()->query('SELECT DISTINCT `fieldId` FROM `enumset` WHERE `title` LIKE "%<%"')->col();
        d('fields having styled enumset ' . count($fieldIdA));
        d($fieldIdA);
        foreach (m('field')->all('`id` IN (' . im($fieldIdA ?: [0]) . ')') as $f) {
            $enumsetRs = m('enumset')->all('fieldId = "' . $f->id . '"');
            if ($f->l10n == 'y') {
                foreach ($enumsetRs as $enumsetR) {
                    $option = Indi_View_Helper_Admin_FormCombo::detectColor(['title' => $enumsetR->title]);
                    unset($option['box'], $option['color'], $option['style']);
                    d($option);
                    foreach ($enumsetR->language('title') ?: [] as $lang => $title) {
                        $enumsetR->language('title', $lang, strip_tags($title));
                    }
                    if ($option['boxColor'] == 'transparent') $option['boxColor'] = 'white';
                    $enumsetR->set($option)->save();

                    //db()->query('UPDATE `enumset` SET `tooltip` = \'{"ru":"","en":""}\' WHERE `id` = "' . $enumsetR->id . '"');
                }

            } else {
                foreach ($enumsetRs as $enumsetR) {
                    $option = Indi_View_Helper_Admin_FormCombo::detectColor(['title' => $enumsetR->title]);
                    unset($option['box'], $option['color'], $option['style']);
                    d($option);
                    $enumsetR->set($option)->save();
                }
            }
        }
        enumset('field', 'l10n', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('field', 'l10n', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('grid', 'toggle', 'e', ['cssStyle' => 'border: 1px solid blue;']);
        enumset('lang', 'adminCustomConst', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('lang', 'adminCustomConst', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('lang', 'adminCustomData', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('lang', 'adminCustomData', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('lang', 'adminCustomTmpl', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('lang', 'adminCustomTmpl', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('lang', 'adminCustomUi', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('lang', 'adminCustomUi', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('lang', 'adminSystemConst', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('lang', 'adminSystemConst', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('lang', 'adminSystemUi', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('lang', 'adminSystemUi', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('section2action', 'l10n', 'qn', ['cssStyle' => 'border: 3px solid lightgray;']);
        enumset('section2action', 'l10n', 'qy', ['cssStyle' => 'border: 3px solid blue;']);
        enumset('section', 'defaultSortDirection', 'ASC', ['cssStyle' => 'background-position: -5px -1px;']);
        enumset('section', 'defaultSortDirection', 'DESC', ['cssStyle' => 'background-position: -5px -1px;']);
        enumset('section', 'disableAdd', '0', ['cssStyle' => 'background: transparent;']);
        enumset('section', 'rownumberer', '0', ['cssStyle' => 'background: transparent;']);
        die('ok');
    }
    public function filterOwnerAction() {
        field('section2action', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'title']);
        section('sectionActions', ['rowsetSeparate' => 'no']);
        field('section', 'filterOwner', [
            'title' => 'Владельцам - только свои записи',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'no',
            'move' => 'filter',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'filterOwner', 'no', ['title' => '<span class="i-color-box" style="background: white;"></span>Нет', 'move' => '']);
        enumset('section', 'filterOwner', 'yes', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/filter-key-red.png);"></span>Да, для всех действий', 'move' => 'no']);
        enumset('section', 'filterOwner', 'certain', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/filter-key-blue.png);"></span>Да, для некоторых действий', 'move' => 'yes']);
        grid('sections', 'filter', ['icon' => 'resources/images/icons/filter-db-blue.png']);
        grid('sections', 'filterOwner', ['move' => 'filter', 'gridId' => 'data', 'icon' => 'resources/images/icons/filter-key-red.png']);

        grid('actions', 'icon', ['icon' => 'resources/images/icons/icon-image.png']);
        grid('grid', 'icon', ['icon' => 'resources/images/icons/icon-image.png']);

        field('section2action', 'access', ['title' => 'Доступ', 'elementId' => 'span', 'move' => 'toggle']);
        field('section2action', 'filterOwner', [
            'title' => 'Только для владельцев',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'no',
            'move' => 'roleIds',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'filterOwner', 'no', ['title' => '<span class="i-color-box" style="background: white;"></span>Нет', 'move' => '']);
        enumset('section2action', 'filterOwner', 'yes', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/filter-key-red.png);"></span>Да, для всех ролей', 'move' => 'no']);
        enumset('section2action', 'filterOwner', 'certain', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/filter-key-blue.png);"></span>Да, для некоторых ролей', 'move' => 'yes']);
        field('section2action', 'filterOwnerRoleIds', [
            'title' => 'Для каких ролей владельцев',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'filterOwner',
            'relation' => 'role',
            'storeRelationAbility' => 'many',
        ]);
        consider('section2action', 'filterOwnerRoleIds', 'roleIds', ['required' => 'y', 'connector' => -1]);
        field('section2action', 'view', [
            'title' => 'Вид',
            'elementId' => 'span',
            'move' => 'l10n',
            'mode' => 'hidden',
        ]);
        field('section2action', 'win', [
            'title' => 'Окно',
            'elementId' => 'span',
            'move' => 'view',
            'mode' => 'hidden',
        ]);
        field('section2action', 'props', [
            'title' => 'Свойства',
            'elementId' => 'span',
            'move' => 'win',
            'mode' => 'hidden',
        ]);
        field('section2action', 'access', ['title' => 'Доступ', 'elementId' => 'span', 'move' => 'toggle']);
        field('section2action', 'filterOwner', [
            'title' => 'Только для владельцев',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'no',
            'move' => 'roleIds',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'filterOwner', 'no', ['title' => '<span class="i-color-box" style="background: white;"></span>Нет', 'move' => '']);
        enumset('section2action', 'filterOwner', 'yes', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/filter-key-red.png);"></span>Да, для всех ролей', 'move' => 'no']);
        enumset('section2action', 'filterOwner', 'certain', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/filter-key-blue.png);"></span>Да, для некоторых ролей', 'move' => 'yes']);
        field('section2action', 'filterOwnerRoleIds', [
            'title' => 'Для каких ролей владельцев',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'move' => 'filterOwner',
            'relation' => 'role',
            'storeRelationAbility' => 'many',
        ]);
        consider('section2action', 'filterOwnerRoleIds', 'roleIds', ['required' => 'y', 'connector' => '-1']);
        field('section2action', 'view', [
            'title' => 'Вид',
            'elementId' => 'span',
            'move' => 'l10n',
            'mode' => 'hidden',
        ]);
        field('section2action', 'win', [
            'title' => 'Окно',
            'elementId' => 'span',
            'move' => 'view',
            'mode' => 'hidden',
        ]);
        field('section2action', 'props', [
            'title' => 'Свойства',
            'elementId' => 'span',
            'move' => 'win',
            'mode' => 'hidden',
        ]);

        enumset('section2action', 'south', 'auto', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/south-blue.png);"></span>Авто', 'move' => '']);
        enumset('section2action', 'south', 'yes', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/south-green.png);"></span>Отображать', 'move' => 'auto']);
        enumset('section2action', 'south', 'no', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/south-red.png);"></span>Не отображать', 'move' => 'yes']);
        enumset('section2action', 'fitWindow', 'n', ['title' => '<span class="i-color-box" style="background: white;"></span>Выключено', 'move' => '']);
        enumset('section2action', 'fitWindow', 'y', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/fit.png);"></span>Включено', 'move' => 'n']);

        // Reorder fields
        field('section2action', 'sectionId', ['move' => '']);
        field('section2action', 'actionId', ['move' => 'sectionId']);
        field('section2action', 'toggle', ['move' => 'actionId']);
        field('section2action', 'access', ['move' => 'toggle']);
        field('section2action', 'roleIds', ['move' => 'access']);
        field('section2action', 'filterOwner', ['move' => 'roleIds']);
        field('section2action', 'filterOwnerRoleIds', ['move' => 'filterOwner']);
        field('section2action', 'move', ['move' => 'filterOwnerRoleIds']);
        field('section2action', 'title', ['move' => 'move']);
        field('section2action', 'features', ['move' => 'title']);
        field('section2action', 'rename', ['move' => 'features']);
        field('section2action', 'south', ['move' => 'rename']);
        field('section2action', 'fitWindow', ['move' => 'south']);
        field('section2action', 'l10n', ['move' => 'fitWindow']);
        field('section2action', 'view', ['move' => 'l10n']);
        field('section2action', 'win', ['move' => 'view']);
        field('section2action', 'props', ['move' => 'win']);

        section('sectionActions')->nested('grid')->delete();

        grid('sectionActions', 'actionId', ['move' => '']);
        grid('sectionActions', 'props', ['move' => 'actionId']);
        grid('sectionActions', 'toggle', ['move' => '', 'gridId' => 'props', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('sectionActions', 'access', ['move' => 'toggle', 'gridId' => 'props']);
        grid('sectionActions', 'roleIds', [
            'move' => '',
            'gridId' => 'access',
            'editor' => '1',
            'jumpSectionId' => 'role',
            'jumpSectionActionId' => 'form',
        ]);
        grid('sectionActions', 'filterOwner', ['move' => 'roleIds', 'gridId' => 'access', 'icon' => 'resources/images/icons/filter-key-red.png']);
        grid('sectionActions', 'filterOwnerRoleIds', [
            'move' => 'filterOwner',
            'gridId' => 'access',
            'editor' => '1',
            'icon' => 'resources/images/icons/square-key-blue.png',
            'jumpSectionId' => 'role',
            'jumpSectionActionId' => 'form',
        ]);
        grid('sectionActions', 'features', ['move' => 'props']);
        grid('sectionActions', 'view', ['move' => '', 'gridId' => 'features']);
        grid('sectionActions', 'actionId', 'icon', ['move' => '', 'gridId' => 'view', 'icon' => 'resources/images/icons/icon-image.png']);
        grid('sectionActions', 'rename', [
            'move' => 'icon',
            'gridId' => 'view',
            'editor' => '1',
            'icon' => 'resources/images/icons/btn-icon-rename.png',
        ]);
        grid('sectionActions', 'win', ['move' => 'view', 'gridId' => 'features']);
        grid('sectionActions', 'fitWindow', ['move' => '', 'gridId' => 'win', 'icon' => 'resources/images/icons/fit.png']);
        grid('sectionActions', 'south', [
            'move' => 'fitWindow',
            'rename' => 'ЮП',
            'tooltip' => 'Режим отображения южной панели',
            'gridId' => 'win',
            'icon' => 'resources/images/icons/south-green.png',
        ]);
        grid('sectionActions', 'l10n', ['move' => 'win', 'gridId' => 'features', 'icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('sectionActions', 'move', ['move' => 'features']);
        die('ok');
    }
    public function allowcycleAction() {
        enumset('grid', 'editor', 'enumNoCycle', ['title' => '<span class="i-color-box" style="background: orange;"></span>Выключен, в том числе смена значения из набора по клику', 'move' => '1']);
        grid('fields', 'storeRelationAbility', ['editor' => 'enumNoCycle']);
        grid('elementCfgField', 'storeRelationAbility', ['editor' => 'enumNoCycle']);
        grid('fieldsAll', 'storeRelationAbility', ['editor' => 'enumNoCycle']);
        die('ok');
    }
    public function idcolAction(){
        field('section', 'showID', [
            'title' => 'Show ID column',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'groupBy',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section', 'showID', 'n', ['title' => '<span class="i-color-box" style="background: white;"></span>No', 'move' => '']);
        enumset('section', 'showID', 'y', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/id.png);"></span>Yes', 'move' => 'n']);
        grid('sections', 'showID', ['move' => 'tileThumb', 'gridId' => 'display', 'icon' => 'resources/images/icons/id.png']);
        grid('sections', 'color', ['move' => '']);
        grid('sections', 'groupBy', ['move' => '']);
        die('ok');
    }
    public function rowcolorAction() {
        field('section', 'color', ['title' => 'Цвет', 'elementId' => 'span', 'move' => 'help']);
        field('section', 'colorField', [
            'title' => 'Поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'color',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('section', 'colorField', 'entityId', ['required' => 'y']);
        field('section', 'colorFurther', [
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'colorField',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);
        consider('section', 'colorFurther', 'colorField', ['foreign' => 'relation', 'required' => 'y', 'connector' => 'entityId']);
        field('section', 'features', [
            'title' => 'Функции',
            'elementId' => 'span',
            'move' => 'colorFurther',
            'mode' => 'hidden',
        ]);
        grid('sections', 'features', ['move' => 'store']);
        grid('sections', 'display', ['gridId' => 'features']);
        grid('sections', 'groupBy', [
            'icon' => 'resources/images/icons/group.png',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('sections', 'color', ['move' => 'display', 'gridId' => 'features']);
        grid('sections', 'multiSelect', ['move' => 'color', 'gridId' => 'features']);
        grid('sections', 'colorField', [
            'move' => '',
            'gridId' => 'color',
            'editor' => '1',
            'icon' => 'resources/images/icons/table-col.png',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('sections', 'colorFurther', [
            'move' => 'colorField',
            'gridId' => 'color',
            'editor' => '1',
            'icon' => 'resources/images/icons/join.png',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        alteredField('sections', 'colorField', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'colorFurther', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        die('ok');
    }
    public function colcolorAction() {
        foreach (ar('decimal143,number,price') as $element)
            cfgField('element', $element, 'colorBreakLevel', [
                'title' => 'Уровень разбивки по цвету',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'number',
                'defaultValue' => '0',
                'move' => '',
            ]);

        cfgField('element', 'combo', 'colorField', [
            'title' => 'Цветовое поле',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'noLookup',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);

        field('grid', 'color', ['title' => 'Цвет', 'elementId' => 'span', 'move' => 'properties']);
        field('grid', 'colorBreak', [
            'title' => 'Разбивка по уровню',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'move' => 'color',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('grid', 'colorBreak', 'n', ['title' => '<span class="i-color-box" style="background: white;"></span>Выключена', 'move' => '']);
        enumset('grid', 'colorBreak', 'y', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/color-level.png);"></span>Включена', 'move' => 'n']);
        field('grid', 'colorDirect', [
            'title' => 'Указан вручную',
            'columnTypeId' => 'VARCHAR(10)',
            'elementId' => 'color',
            'move' => 'colorBreak',
        ]);
        field('grid', 'colorField', [
            'title' => 'Внешний источник',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'colorDirect',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`elementId` = "11"',
        ]);
        param('grid', 'colorField', 'groupBy', ['cfgValue' => '6']);
        field('grid', 'colorEntry', [
            'title' => 'Внешняя запись',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'colorField',
            'storeRelationAbility' => 'one',
        ]);
        consider('grid', 'colorEntry', 'colorField', ['foreign' => 'entityId', 'required' => 'y']);

        grid('grid', 'color', ['move' => 'rowReqIfAffected', 'gridId' => 'features']);
        grid('grid', 'colorBreak', ['move' => '', 'gridId' => 'color', 'icon' => 'resources/images/icons/color-level.png']);
        grid('grid', 'colorDirect', [
            'move' => 'colorBreak',
            'gridId' => 'color',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/color-text.svg',
        ]);
        grid('grid', 'colorField', [
            'move' => 'colorDirect',
            'gridId' => 'color',
            'editor' => '1',
            'icon' => 'resources/images/icons/table-col.png',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('grid', 'colorEntry', [
            'move' => 'colorField',
            'gridId' => 'color',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/table-row.png',
        ]);
        alteredField('grid', 'colorField', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        die('ok');
    }
    public function jumpsAction() {
        grid('elementCfgField', 'filter', ['rowReqIfAffected' => 'n']);
        field('grid', 'jump', ['title' => 'Навигация', 'elementId' => 'span', 'move' => 'accessExcept']);
        field('grid', 'jumpSectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'jump',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
        ]);
        field('grid', 'jumpSectionActionId', [
            'title' => 'Действие',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'jumpSectionId',
            'relation' => 'section2action',
            'storeRelationAbility' => 'one',
        ]);
        consider('grid', 'jumpSectionActionId', 'jumpSectionId', ['required' => 'y', 'connector' => 'sectionId']);
        field('grid', 'jumpArgs', [
            'title' => 'Аргументы',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'jumpSectionActionId',
        ]);
        field('grid', 'features', ['title' => 'Функции', 'elementId' => 'span', 'move' => 'jumpArgs']);
        field('grid', 'properties', ['title' => 'Свойства', 'elementId' => 'span', 'move' => 'features', 'mode' => 'hidden']);
        field('grid', 'display', ['title' => 'Отображение']);
        field('grid', 'icon', ['move' => 'display']);
        field('grid', 'tooltip', ['move' => 'rename']);
        field('grid', 'rename', ['move' => 'tooltip']);
        field('grid', 'toggle', ['move' => 'gridId']);
        field('grid', 'features', ['move' => 'rename']);
        field('grid', 'group', ['move' => 'display']);
        field('grid', 'rowReqIfAffected', ['move' => 'editor']);
        section('grid')->nested('grid')->delete();
        grid('grid', 'title', ['move' => '', 'rename' => 'Столбец']);
        grid('grid', 'move', ['move' => 'title']);
        grid('grid', 'properties', ['move' => 'move']);
        grid('grid', 'toggle', ['move' => '', 'gridId' => 'properties', 'icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('grid', 'source', ['move' => 'toggle', 'gridId' => 'properties']);
        grid('grid', 'fieldId', [
            'move' => '',
            'gridId' => 'source',
            'editor' => '1',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('grid', 'further', [
            'move' => 'fieldId',
            'gridId' => 'source',
            'editor' => '1',
            'icon' => 'resources/images/icons/join.png',
            'jumpSectionId' => 'fields',
            'jumpSectionActionId' => 'form',
        ]);
        grid('grid', 'access', ['move' => 'source', 'gridId' => 'properties']);
        grid('grid', 'accessRoles', [
            'move' => '',
            'rename' => 'Кому',
            'gridId' => 'access',
            'editor' => '1',
        ]);
        grid('grid', 'accessExcept', ['move' => 'accessRoles', 'gridId' => 'access', 'editor' => '1']);
        grid('grid', 'features', ['move' => 'properties']);
        grid('grid', 'display', ['move' => '', 'gridId' => 'features']);
        grid('grid', 'icon', [
            'move' => '',
            'gridId' => 'display',
            'editor' => '1',
            'icon' => 'resources/images/icons/btn-icon-tile.png',
        ]);
        grid('grid', 'group', ['move' => 'icon', 'gridId' => 'display']);
        grid('grid', 'tooltip', [
            'move' => 'group',
            'gridId' => 'display',
            'editor' => '1',
            'icon' => 'resources/images/icons/btn-icon-tooltip.png',
        ]);
        grid('grid', 'rename', [
            'move' => 'tooltip',
            'gridId' => 'display',
            'editor' => '1',
            'icon' => 'resources/images/icons/btn-icon-rename.png',
        ]);
        grid('grid', 'width', [
            'move' => 'rename',
            'toggle' => 'n',
            'gridId' => 'display',
            'editor' => '1',
        ]);
        grid('grid', 'editor', ['move' => 'display', 'gridId' => 'features', 'icon' => 'resources/images/icons/btn-icon-editor.png']);
        grid('grid', 'jump', ['move' => 'editor', 'gridId' => 'features']);
        grid('grid', 'jumpSectionId', [
            'move' => '',
            'gridId' => 'jump',
            'editor' => '1',
            'icon' => 'resources/images/icons/tree2.png',
        ]);
        grid('grid', 'jumpSectionActionId', [
            'move' => 'jumpSectionId',
            'gridId' => 'jump',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/action.png',
        ]);
        grid('grid', 'jumpArgs', [
            'move' => 'jumpSectionActionId',
            'gridId' => 'jump',
            'editor' => '1',
            'icon' => 'resources/images/icons/args.png',
        ]);
        grid('grid', 'rowReqIfAffected', ['move' => 'jump', 'gridId' => 'features', 'icon' => 'resources/images/icons/btn-icon-reload-affected.png']);
        grid('grid', 'summaryType', ['move' => 'rowReqIfAffected', 'gridId' => 'features', 'editor' => '1']);
        grid('filter', 'further', ['move' => 'fieldId', 'editor' => '1', 'icon' => 'resources/images/icons/join.png']);
        grid('fields', 'tooltip', ['icon' => 'resources/images/icons/btn-icon-tooltip.png']);
        field('role', 'entityId', ['filter' => '`fraction`= "custom"']);

        grid('sections', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('sections', 'groupBy', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('grid', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('grid', 'further', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('filter', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('filter', 'further', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);

        grid('fields', 'relation',     ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('fields', 'elementId',    ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        grid('fields', 'columnTypeId', ['jumpSectionId' => 'columnTypes', 'jumpSectionActionId' => 'form']);
        grid('fieldsAll', 'relation',     ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('fieldsAll', 'elementId',    ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        grid('fieldsAll', 'columnTypeId', ['jumpSectionId' => 'columnTypes', 'jumpSectionActionId' => 'form']);
        grid('params', 'cfgField', ['jumpSectionId' => 'elementCfgField', 'jumpSectionActionId' => 'form']);
        grid('consider', 'consider',  ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('consider', 'foreign',   ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('consider', 'connector', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        section('profiles', ['alias' => 'role']);
        grid('role', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('columnTypes', 'elementId', ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        grid('notices', 'roleId', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
        grid('notices', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('paramsAll', 'fieldId', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('paramsAll', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        grid('paramsAll', 'cfgField', ['jumpSectionId' => 'elementCfgField', 'jumpSectionActionId' => 'form']);
        field('grid', 'rowReqIfAffected', ['move' => 'features']);
        field('grid', 'editor', ['move' => 'features']);
        field('grid', 'tooltip', ['move' => 'rename']);
        grid('sectionActions', 'roleIds', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
        grid('elementCfgField', 'relation',     ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        grid('elementCfgField', 'elementId',    ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        grid('elementCfgField', 'columnTypeId', ['jumpSectionId' => 'columnTypes', 'jumpSectionActionId' => 'form']);
        field('alteredField', 'jump', ['title' => 'Навигация', 'elementId' => 'span', 'move' => 'defaultValue']);
        field('alteredField', 'jumpSectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'jump',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
        ]);
        field('alteredField', 'jumpSectionActionId', [
            'title' => 'Действие',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'move' => 'jumpSectionId',
            'relation' => 'section2action',
            'storeRelationAbility' => 'one',
        ]);
        consider('alteredField', 'jumpSectionActionId', 'jumpSectionId', ['required' => 'y', 'connector' => 'sectionId']);
        field('alteredField', 'jumpArgs', [
            'title' => 'Аргументы',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'jumpSectionActionId',
        ]);
        section('alteredFields', ['rowsetSeparate' => 'no']);
        grid('alteredFields', 'jump', ['move' => 'access']);
        grid('alteredFields', 'jumpSectionId', [
            'move' => '',
            'gridId' => 'jump',
            'editor' => '1',
            'icon' => 'resources/images/icons/tree2.png',
        ]);
        grid('alteredFields', 'jumpSectionActionId', [
            'move' => 'jumpSectionId',
            'gridId' => 'jump',
            'editor' => '1',
            'rowReqIfAffected' => 'y',
            'icon' => 'resources/images/icons/action.png',
        ]);
        grid('alteredFields', 'jumpArgs', [
            'move' => 'jumpSectionActionId',
            'gridId' => 'jump',
            'editor' => '1',
            'icon' => 'resources/images/icons/args.png',
        ]);
        grid('alteredFields', 'rename', ['icon' => 'resources/images/icons/btn-icon-rename.png']);
        grid('alteredFields', 'accessExcept', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
                 grid('grid', 'accessExcept', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
               grid('filter', 'accessExcept', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
        alteredField('elementCfgField', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'sectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'parentSectionConnector', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'defaultSortField', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'groupBy', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'tileField', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('sections', 'tileThumb', ['jumpSectionId' => 'resize', 'jumpSectionActionId' => 'form']);
        alteredField('sectionActions', 'actionId', ['jumpSectionId' => 'actions', 'jumpSectionActionId' => 'form']);
        alteredField('grid', 'sectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('grid', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('grid', 'further', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('grid', 'gridId', ['jumpSectionId' => 'grid', 'jumpSectionActionId' => 'form']);
        alteredField('grid', 'jumpSectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('grid', 'jumpSectionActionId', ['jumpSectionId' => 'sectionActions', 'jumpSectionActionId' => 'form']);
        alteredField('alteredFields', 'sectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('alteredFields', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('alteredFields', 'elementId', ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        alteredField('alteredFields', 'jumpSectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('alteredFields', 'jumpSectionActionId', ['jumpSectionId' => 'sectionActions', 'jumpSectionActionId' => 'form']);
        alteredField('sectionActions', 'sectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('filter', 'sectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('filter', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('filter', 'further', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('entities', 'titleFieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('entities', 'spaceFields', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('entities', 'filesGroupBy', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('fields', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('fields', 'relation', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('fields', 'elementId', ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        alteredField('fields', 'columnTypeId', ['jumpSectionId' => 'columnTypes', 'jumpSectionActionId' => 'form']);
        alteredField('notices', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('resize', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('params', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('params', 'cfgField', ['jumpSectionId' => 'elementCfgField', 'jumpSectionActionId' => 'form']);
        alteredField('consider', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('consider', 'consider', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('consider', 'foreign', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('consider', 'connector', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('role', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('admins', 'roleId', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
        alteredField('columnTypes', 'elementId', ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        alteredField('elementCfgField', 'relation', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('elementCfgField', 'elementId', ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        alteredField('elementCfgField', 'columnTypeId', ['jumpSectionId' => 'columnTypes', 'jumpSectionActionId' => 'form']);
        alteredField('notices', 'roleId', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
        alteredField('notices', 'sectionId', ['jumpSectionId' => 'sections', 'jumpSectionActionId' => 'form']);
        alteredField('noticeGetters', 'noticeId', ['jumpSectionId' => 'notices', 'jumpSectionActionId' => 'form']);
        alteredField('noticeGetters', 'roleId', ['jumpSectionId' => 'role', 'jumpSectionActionId' => 'form']);
        alteredField('fieldsAll', 'entityId', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('fieldsAll', 'relation', ['jumpSectionId' => 'entities', 'jumpSectionActionId' => 'form']);
        alteredField('fieldsAll', 'elementId', ['jumpSectionId' => 'controlElements', 'jumpSectionActionId' => 'form']);
        alteredField('fieldsAll', 'columnTypeId', ['jumpSectionId' => 'columnTypes', 'jumpSectionActionId' => 'form']);
        alteredField('paramsAll', 'fieldId', ['jumpSectionId' => 'fields', 'jumpSectionActionId' => 'form']);
        alteredField('paramsAll', 'cfgField', ['jumpSectionId' => 'elementCfgField', 'jumpSectionActionId' => 'form']);
        die('ok');
    }
    public function iconsAction() {
        section('alteredFields',  ['multiSelect' => '1']);
        section('filter',         ['multiSelect' => '1']);
        section('sectionActions', ['multiSelect' => '1']);
        grid('sectionActions', 'rename',  ['editor' => '1']);
        grid('sectionActions', 'roleIds', ['editor' => '1']);
        field('section', 'rowsOnPage', ['elementId' => 'number']);
        section('columnTypes', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);

        element('icon', ['title' => 'Иконка']);
        cfgField('element', 'icon', 'dir', [
            'title' => 'Директория',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'resources/images/icons,/i/admin/icons',
            'move' => '',
        ]);
        coltype('VARCHAR(255)', ['canStoreRelation' => 'y', 'elementId' => 'hidden,combo,string,multicheck,icon']);

        field('grid', 'icon', [
            'title' => 'Иконка',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'icon',
            'move' => 'editor'
        ]);
        grid('sections', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('sections', 'rowsOnPage', ['icon' => 'resources/images/icons/btn-icon-qty-on-page.png']);
        grid('sectionActions', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('profiles', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('admins', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('filter', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('alteredFields', 'mode', ['icon' => 'resources/images/icons/field/readonly.png']);
        grid('filter', 'filter', ['icon' => 'resources/images/icons/btn-icon-filter.png']);
        grid('grid', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('fields', 'mode', ['icon' => 'resources/images/icons/field/readonly.png']);
        grid('actions', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('grid', 'editor', ['icon' => 'resources/images/icons/btn-icon-editor.png']);
        grid('sections', 'rowsetSeparate', ['icon' => 'resources/images/icons/field/required.png']);
        grid('sectionActions', 'fitWindow', ['icon' => 'resources/images/icons/btn-icon-toggle-lime-gray.png']);
        grid('fields', 'l10n', ['icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('fieldsAll', 'mode', ['icon' => 'resources/images/icons/field/readonly.png']);
        grid('fieldsAll', 'storeRelationAbility', ['icon' => 'resources/images/icons/btn-icon-multikey.png']);
        grid('fieldsAll', 'filter', ['icon' => 'resources/images/icons/btn-icon-filter.png']);
        grid('fieldsAll', 'l10n', ['icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('sections', 'extendsPhp', ['icon' => 'resources/images/icons/btn-icon-php-parent.png']);
        grid('sections', 'extendsJs', ['icon' => 'resources/images/icons/btn-icon-js-parent.png']);
        grid('noticeGetters', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('lang', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('sectionActions', 'l10n', ['icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('fields', 'storeRelationAbility', ['icon' => 'resources/images/icons/btn-icon-multikey.png']);
        grid('fields', 'filter', ['icon' => 'resources/images/icons/btn-icon-filter.png']);
        grid('sections', 'disableAdd', ['icon' => 'resources/images/icons/btn-icon-create-deny.png']);
        grid('sections', 'filter', ['icon' => 'resources/images/icons/btn-icon-filter.png']);
        grid('sections', 'multiSelect', ['icon' => 'resources/images/icons/btn-icon-multi-select.png']);
        grid('sections', 'rownumberer', ['icon' => 'resources/images/icons/btn-icon-numberer.png']);
        grid('grid', 'rowReqIfAffected', ['icon' => 'resources/images/icons/btn-icon-reload-affected.png']);
        grid('elementCfgField', 'storeRelationAbility', ['icon' => 'resources/images/icons/btn-icon-multikey.png']);
        grid('elementCfgField', 'filter', ['icon' => 'resources/images/icons/btn-icon-filter.png']);
        grid('elementCfgField', 'mode', ['icon' => 'resources/images/icons/field/readonly.png']);
        grid('elementCfgField', 'l10n', ['icon' => 'resources/images/icons/btn-icon-l10n.ico']);
        grid('notices', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        grid('entities', 'extends', ['icon' => 'resources/images/icons/btn-icon-php-parent.png']);
        grid('alteredFields', 'toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);

        field('action', 'icon', [
            'title' => 'Иконка',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'icon',
            'move' => 'toggle',
        ]);
        action('index', ['icon' => 'resources/images/icons/btn-icon-grid.png']);
        action('form', ['icon' => 'resources/images/icons/btn-icon-form.png']);
        action('save', ['icon' => 'resources/images/icons/btn-icon-save.png']);
        action('delete', ['icon' => 'resources/images/icons/btn-icon-delete.png']);
        action('up', ['icon' => 'resources/images/icons/btn-icon-up.png']);
        action('down', ['icon' => 'resources/images/icons/btn-icon-down.png']);
        action('toggle', ['icon' => 'resources/images/icons/btn-icon-toggle.png']);
        action('login', ['icon' => 'resources/images/icons/btn-icon-login.png']);
        action('php', ['icon' => 'resources/images/icons/btn-icon-php.png']);
        action('js', ['icon' => 'resources/images/icons/btn-icon-js.png']);
        action('export', ['icon' => 'resources/images/icons/btn-icon-export.png']);
        action('goto', ['icon' => 'resources/images/icons/btn-icon-goto.png']);
        action('activate', ['icon' => 'resources/images/icons/btn-icon-activate.png']);
        action('chart', ['icon' => 'resources/images/icons/btn-icon-chart.png']);
        action('copy', ['icon' => 'resources/images/icons/btn-icon-copy.png']);
        action('import', ['icon' => 'resources/images/icons/btn-icon-import.png']);

        grid('actions', 'icon', ['move' => 'title', 'editor' => '1', 'icon' => 'resources/images/icons/btn-icon-tile.png']);
        grid('sectionActions', 'actionId', 'icon', ['move' => 'actionId', 'icon' => 'resources/images/icons/btn-icon-tile.png']);
        grid('grid', 'rename', ['move' => 'tooltip', 'icon' => 'resources/images/icons/btn-icon-rename.png']);
        grid('grid', 'tooltip', ['icon' => 'resources/images/icons/btn-icon-tooltip.png']);
        field('grid', 'source', [
            'title' => 'Источник',
            'elementId' => 'span',
            'move' => 'icon',
            'mode' => 'hidden',
        ]);
        grid('grid', 'source', ['move' => 'move']);
        grid('grid', 'fieldId', ['gridId' => 'source']);
        grid('grid', 'further', ['gridId' => 'source']);
        grid('grid', 'width', ['toggle' => 'n']); // ?
        field('grid', 'width', ['mode' => 'hidden']);
        grid('grid', 'icon', [
            'move' => 'editor',
            'gridId' => 'display',
            'editor' => '1',
            'icon' => 'resources/images/icons/btn-icon-tile.png',
        ]);
        field('grid', 'rowReqIfAffected', ['move' => 'summaryText']);
        grid('filter', 'rename', ['icon' => 'resources/images/icons/btn-icon-rename.png']);
        grid('filter', 'tooltip', ['icon' => 'resources/images/icons/btn-icon-tooltip.png']);
        grid('sections', 'filter', ['rowReqIfAffected' => 'n']);
        grid('sections', 'extendsPhp', ['rowReqIfAffected' => 'n']);
        grid('sections', 'extendsJs', ['rowReqIfAffected' => 'n']);
        grid('entities', 'extends', ['rowReqIfAffected' => 'n']);
        grid('filter', 'filter', ['rowReqIfAffected' => 'n']);
        grid('fields', 'filter', ['rowReqIfAffected' => 'n']);
        grid('elementCfgField', 'filter', ['rowReqIfAffected' => 'n']);
        action('revert', ['title' => 'Восстановить', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-reset.png']);
        action('print', ['title' => 'Печать', 'fraction' => 'system', 'icon' => 'resources/images/icons/btn-icon-print.png']);
        die('ok');
    }
    public function refactorAllExceptAction() {
        field('action', 'rowRequired', ['alias' => 'selectionRequired']);
        entity('search', ['table' => 'filter']);
        section('search', ['alias' => 'filter']);
        foreach (ar('grid,filter') as $entity) {
            db()->query('UPDATE `' . $entity . '` SET `access` = "all" WHERE `access` = "except"');
            enumset($entity, 'access', 'except')->delete();
            enumset($entity, 'access', 'all', ['title' => 'Any role']);
            enumset($entity, 'access', 'only', ['title' => 'No roles', 'alias' => 'none', 'move' => 'all']);
            field($entity, 'access',  ['alias' => 'accessRoles', 'title' => 'Roles']);
            field($entity, 'roleIds', ['alias' => 'accessExcept', 'title' => 'Except']);
            field($entity, 'accesss', ['alias' => 'access']);
        }

        db()->query('UPDATE `alteredField` SET `impact` = "all" WHERE `impact` = "except"');
        enumset('alteredField', 'impact', 'except')->delete();
        enumset('alteredField', 'impact', 'all', ['title' => 'Any role']);
        enumset('alteredField', 'impact', 'only', ['title' => 'No roles', 'alias' => 'none', 'move' => 'all']);
        field('alteredField', 'impact',  ['alias' => 'accessRoles', 'title' => 'Roles']);
        field('alteredField', 'roleIds', ['alias' => 'accessExcept', 'title' => 'Except']);
        field('alteredField', 'impactt', ['alias' => 'access', 'title' => 'Access']);

        field('alteredField', 'toggle', [
            'title' => 'Toggle',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'move' => 'title',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('alteredField', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Turned on', 'move' => '']);
        enumset('alteredField', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Turned off', 'move' => 'y']);
        grid('alteredFields', 'toggle', ['move' => 'fieldId']);
        grid('filter', 'filter', ['gridId' => 'display', 'rowReqIfAffected' => 'y']);
        grid('fields', 'filter', ['rowReqIfAffected' => 'y']);
        grid('sections', 'filter', ['rowReqIfAffected' => 'y']);
        grid('elementCfgField', 'filter', ['rowReqIfAffected' => 'y']);
        field('filter', 'alt', ['alias' => 'rename']);
        field('grid', 'alterTitle', ['alias' => 'rename']);
        field('entity', 'useCache')->delete();
        action('cache')->delete();
        die('ok');
    }
    public function renameProfileAction() {
        field('notice', 'profileId', ['alias' => 'roleId']);
        field('admin', 'profileId', ['alias' => 'roleId']);
        field('changeLog', 'profileId', ['alias' => 'roleId']);
        field('noticeGetter', 'profileId', ['alias' => 'roleId']);
        field('realtime', 'profileId', ['alias' => 'roleId']);

        field('grid', 'profileIds', ['alias' => 'roleIds']);
        field('search', 'profileIds', ['alias' => 'roleIds']);
        field('section2action', 'profileIds', ['alias' => 'roleIds']);
        field('alteredField', 'profileIds', ['alias' => 'roleIds']);

        entity('profile', ['table' => 'role']);
        die('ok');
    }
    public function fixFractionAliasesAction() {;

        enumset('action', 'type', 'p', ['alias' => 'custom']);
        enumset('action', 'type', 's', ['alias' => 'system']);
        enumset('action', 'type', 'o', ['alias' => 'public']);
        field('action', 'type', ['alias' => 'fraction']);

        enumset('entity', 'system', 'n', ['alias' => 'custom']);
        enumset('entity', 'system', 'y', ['alias' => 'system']);
        enumset('entity', 'system', 'o', ['alias' => 'public']);
        field('entity', 'system', ['alias' => 'fraction']);

        enumset('notice', 'type', 'p', ['alias' => 'custom']);
        enumset('notice', 'type', 's', ['alias' => 'system']);
        field('notice', 'type', ['alias' => 'fraction']);

        enumset('profile', 'type', 'p', ['alias' => 'custom']);
        enumset('profile', 'type', 's', ['alias' => 'system']);
        field('profile', 'type', ['alias' => 'fraction']);

        enumset('section', 'type', 's', ['alias' => 'system']);
        enumset('section', 'type', 'p', ['alias' => 'custom']);
        enumset('section', 'type', 'o', ['alias' => 'public']);
        field('section', 'type', ['alias' => 'fraction']);
        field('profile', 'entityId', ['filter' => '`fraction`= "custom"']);
        die('ok');
    }
    public function updateResizeAction(){
        entity('resize', ['titleFieldId' => 'alias']);
        if (field('resize', 'color')) field('resize', 'color')->delete();
        if (field('resize', 'changeColor')) field('resize', 'changeColor')->delete();
        if (field('resize', 'masterDimensionValue')) field('resize', 'masterDimensionValue', ['alias' => 'width']);
        if (field('resize', 'slaveDimensionValue')) field('resize', 'slaveDimensionValue', ['alias' => 'height']);
        field('resize', 'mode', [
            'title' => 'Set exactly',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'both',
            'move' => 'height',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('resize', 'mode', 'both', ['title' => 'Both dimensions, crop if need', 'move' => '']);
        enumset('resize', 'mode', 'width', ['title' => 'Width, auto fit height', 'move' => 'both']);
        enumset('resize', 'mode', 'height', ['title' => 'Height, auto fit width', 'move' => 'width']);
        enumset('resize', 'mode', 'auto', ['title' => 'Only one dimension, auto fit other', 'move' => 'height']);
        if (field('resize', 'title')) field('resize', 'title', [
            'title' => 'Note',
            'alias' => 'note',
            'mode' => 'regular',
            'move' => 'mode',
        ]);
        section('resize')->nested('grid')->delete();
        grid('resize', 'alias', ['move' => '']);
        grid('resize', 'width', ['move' => 'alias']);
        grid('resize', 'height', ['move' => 'width']);
        grid('resize', 'mode', ['move' => 'height']);
        grid('resize', 'note', ['move' => 'mode']);

        if (m('resize')->fields('proportions')) foreach (m('resize')->all() as $resizeR) {
            if ($resizeR->proportions == 'o') {
                //$resizeR->delete();
                d('Proportions - just a copy with no resize: '
                    . $resizeR->foreign('fieldId')->foreign('entityId')->table . '->'
                    . $resizeR->foreign('fieldId')->alias . '->'
                    . $resizeR->alias
                );
            } else if ($resizeR->proportions == 'c') {
                $resizeR->set(['mode' => 'both'])->basicUpdate();
            } else if ($resizeR->proportions == 'p') {
                if ($resizeR->slaveDimensionLimitation) {
                    $resizeR->set(['mode' => 'auto'])->basicUpdate();
                } else {
                    if ($resizeR->masterDimensionAlias == 'width') {
                        $resizeR->set(['mode' => 'width'])->basicUpdate();
                    } else {
                        $resizeR->set(['mode' => 'height'])->basicUpdate();
                    }
                }
            }
        }

        if (field('resize', 'proportions')) field('resize', 'proportions')->delete();
        if (field('resize', 'masterDimensionAlias')) field('resize', 'masterDimensionAlias')->delete();
        if (field('resize', 'slaveDimensionLimitation')) field('resize', 'slaveDimensionLimitation')->delete();
        section('resize', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        die('ok');
    }
    public function noticeColorAction() {

        // ------notices-----
        field('notice', 'alias', [
            'title' => 'Alias',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'title',
            'mode' => 'required',
        ]);
        field('notice', 'type', ['move' => 'alias']);
        field('notice', 'bg', ['defaultValue' => '195#008dbc']);
        field('notice', 'fg', [
            'title' => 'Foreground color',
            'defaultValue' => '000#ffffff',
        ]);
        field('notice', 'props', [
            'title' => 'Properties',
            'elementId' => 'span',
            'move' => 'tplEvtBody',
            'mode' => 'hidden',
        ]);
        field('notice', 'color', [
            'title' => 'Color',
            'elementId' => 'span',
            'move' => 'props',
            'mode' => 'hidden',
        ]);
        field('notice', 'trigger', [
            'title' => 'Trigger',
            'elementId' => 'span',
            'move' => 'color',
            'mode' => 'hidden',
        ]);
        section('notices', [
            'extendsPhp' => 'Indi_Controller_Admin_Exportable',
            'multiSelect' => '1',
        ])->nested('grid')->delete();
        section2action('notices','export', ['move' => 'toggle', 'profileIds' => '1']);
        grid('notices', 'title', ['move' => '']);
        grid('notices', 'props', ['move' => 'title']);
        grid('notices', 'toggle', ['move' => '', 'gridId' => 'props']);
        grid('notices', 'type', ['move' => 'toggle', 'gridId' => 'props']);
        grid('notices', 'alias', ['move' => 'type', 'gridId' => 'props', 'editor' => 1]);
        grid('notices', 'color', ['move' => 'alias', 'gridId' => 'props']);
        grid('notices', 'bg', ['move' => '', 'alterTitle' => 'Background', 'gridId' => 'color']);
        grid('notices', 'fg', ['move' => 'bg', 'alterTitle' => 'Foreground', 'gridId' => 'color']);
        grid('notices', 'profileId', ['move' => 'color', 'gridId' => 'props']);
        grid('notices', 'trigger', ['move' => 'props']);
        grid('notices', 'entityId', ['move' => '', 'gridId' => 'trigger']);
        grid('notices', 'qty', ['move' => 'entityId', 'gridId' => 'trigger']);
        grid('notices', 'qtySql', ['move' => '', 'gridId' => 'qty']);
        grid('notices', 'sectionId', ['move' => 'qtySql', 'gridId' => 'qty']);
        grid('notices', 'qtyDiffRelyOn', ['move' => 'sectionId', 'gridId' => 'qty']);
        grid('notices', 'event', ['move' => 'qty', 'gridId' => 'trigger']);
        notice('queueTask', 'failed', [
            'title' => 'Queue failed',
            'event' => '$this->queueState == \'error\'',
            'profileId' => '1',
            'qtySql' => '`queueState` = "error"',
            'bg' => '353#e3495a',
            'tooltip' => 'Queue tasks having processing error',
            'tplIncBody' => 'Queue task failed due to Google Cloud Translate API response: <?=$this->row->error?>',
            'type' => 's',
        ]);
        noticeGetter('queueTask', 'failed', 'dev', true);
        notice('queueTask', 'started', [
            'title' => 'Queue started',
            'event' => '$this->procID != 0',
            'profileId' => '1',
            'qtySql' => '`procID` != "0"',
            'tplIncBody' => 'Queue task started with PID: <?=$this->row->procID?>',
            'type' => 's',
        ]);
        noticeGetter('queueTask', 'started', 'dev', true);
        // ------entities-----
        section('entities')->nested('grid')->delete();
        grid('entities', 'title', ['move' => '', 'editor' => 1]);
        grid('entities', 'system', ['move' => 'title']);
        grid('entities', 'table', ['move' => 'system', 'editor' => 1]);
        grid('entities', 'extends', ['move' => 'table', 'editor' => 1]);
        grid('entities', 'filesGroupBy', ['move' => 'extends', 'editor' => 1]);
        // ------queueTask-----
        enumset('queueTask', 'queueState', 'error', ['title' => '<font color=red>Error</font>', 'move' => 'noneed']);
        field('queueTask', 'error', [
            'title' => 'Error',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'applySize',
        ]);
        grid('queueTask', 'error', ['move' => 'apply', 'toggle' => 'h', 'rowReqIfAffected' => 'y']);
        // ------- langs--------
        section('lang', ['defaultSortField' => 'move']);
        entity('field', ['extends' => 'Field_Base']);
        grid('entities', 'extends', ['move' => 'table', 'editor' => '1', 'rowReqIfAffected' => 'y']);
        die('ok');
    }
    public function dropGridAliasAction() {
        if ($_ = field('grid', 'alias')) {
            $hasConflict = false;
            foreach (m('grid')->all('`fieldId` = "0"') as $gridR) {
                $sectionR = $gridR->foreign('sectionId');
                $entityR = entity($sectionR->entityId);
                $alias = $gridR->alias ?: 'span' . $gridR->id;
                if ($fieldR = field($entityR->table, $alias)) {
                    d('grid alias conflict: ' . $entityR->table . '.' . $alias . ' - ' . $sectionR->alias . '/' . $alias);
                    $hasConflict = true;
                } else {
                    $fieldR = field($entityR->table, $alias, [
                        'elementId' => 'span',
                        'mode' => 'hidden'
                    ]);
                    db()->query('
                        UPDATE 
                          `field` `f`, 
                          `grid` `g`
                        SET
                          `f`.`title` = `g`.`alterTitle`,
                          `g`.`title` = `g`.`alterTitle`,
                          `g`.`fieldId` = :i,
                          `g`.`alterTitle` = ""  
                        WHERE 1
                          AND `f`.`id` = :i
                          AND `g`.`id` = :i
                    ', $fieldR->id, $fieldR->id, $gridR->id);
                }
            }
            if (!$hasConflict) $_->delete();
        }
        die('ok');
    }
    public function syncMissingAction() {
        consider('realtime', 'title', 'type', ['required' => 'y']);
        filter('lang', 'state', ['defaultValue' => 'smth']);
        action('import', ['title' => 'Импорт', 'type' => 's']);
        section2action('lang', 'export', ['move' => 'down', 'profileIds' => '1']);
        section2action('lang','import', ['move' => 'export', 'profileIds' => '1']);
        section('queueTask', ['groupBy' => 0]);
        if ($_ = action('sitemap')) $_->delete();
        action('revert', ['title' => 'Восстановить', 'type' => 's']);
        if ($_ = alteredField('sections', 'type')) $_->delete();
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
        cfgField('element', 'price', 'measure', [
            'title' => 'Единица измерения',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'maxlength',
        ]);
        cfgField('element', 'string', 'allowedTags', [
            'title' => 'Разрешенные теги',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string'
        ]);
        cfgField('element', 'upload', 'postfix', [
            'title' => 'Постфикс к имени файла при загрузке',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ]);
        cfgField('element', 'upload', 'rowTitle', [
            'title' => 'Включать заголовок записи в наименование файла при загрузке',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
        ]);
        if ($auto = enumset('section2action', 'fitWindow', 'auto')) {
            if (enumset('section2action', 'fitWindow', 'y')) {
                db()->query('UPDATE `section2action` SET `fitWindow` = "y" WHERE `fitWindow` = "auto"');
                $auto->delete();
            } else {
                $auto->alias = 'y';
                $auto->save();
            }
        }
        if ($_ = field('section', 'grid')) $_->delete();
        db()->query('DELETE FROM `grid` WHERE `sectionId` = "' . section('queueChunk')->id . '" AND `fieldId` = 0');
        section2action('lang', 'form', ['toggle' => 'n']);
        section2action('lang', 'delete', ['toggle' => 'n']);
        section('fieldsAll', ['defaultSortField' => 'move']);
        grid('sections', 'load', ['alterTitle' => 'Подгрузка']);
        m('profile')->row(1)->set('title', 'Разработчик')->save();
        filter('fieldsAll', 'entityId', 'system', ['alt' => '']);
        field('section2action', 'fitWindow', ['defaultValue' => 'y']);
        if (field('field', 'title')->l10n == 'y') {
            if (field('param', 'title')->l10n != 'y') {
                if ($_ = consider('param', 'title', 'cfgField')) $_->delete();
                field('param', 'title')->toggleL10n('qy', 'ru', false);
                consider('param', 'title', 'cfgField', ['foreign' => 'title', 'required' => 'y']);
            }
            if (field('consider', 'title')->l10n != 'y') {
                if ($_ = consider('consider', 'title', 'consider')) $_->delete();
                field('consider', 'title')->toggleL10n('qy', 'ru', false);
                consider('consider', 'title', 'consider', ['foreign' => 'title', 'required' => 'y']);
            }
            if (field('alteredField', 'rename')->l10n != 'y') {
                if ($_ = consider('alteredField', 'rename', 'title')) $_->delete();
                field('alteredField', 'rename')->toggleL10n('qy', 'ru', false);
                consider('alteredField', 'rename', 'title', ['required' => 'y']);
            }
            if (field('noticeGetter', 'title')->l10n != 'y') {
                if ($_ = consider('noticeGetter', 'title', 'profileId')) $_->delete();
                field('noticeGetter', 'title')->toggleL10n('qy', 'ru', false);
                consider('noticeGetter', 'title', 'profileId', ['foreign' => 'title', 'required' => 'y']);
            }
            if (field('queueTask', 'stageState')->l10n != 'y') {
                if ($_ = consider('queueTask', 'stageState', 'stage')) $_->delete();
                if ($_ = consider('queueTask', 'stageState', 'state')) $_->delete();
                field('queueTask', 'stageState')->toggleL10n('qy', 'ru', false);
                consider('queueTask', 'stageState', 'stage', ['foreign' => 'title', 'required' => 'y']);
                consider('queueTask', 'stageState', 'state', ['foreign' => 'title', 'required' => 'y']);
            }
            if (field('realtime', 'title')->l10n != 'y') {
                if ($_ = consider('realtime', 'title', 'type')) $_->delete();
                field('realtime', 'title')->toggleL10n('qy', 'ru', false);
                consider('realtime', 'title', 'type', ['foreign' => 'title', 'required' => 'y']);
            }
        }
        field('profile', 'alias', [
            'title' => 'Псевдоним',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'type',
            'mode' => 'required',
        ]);
        grid('profiles', 'alias', ['move' => 'title']);
        db()->query('UPDATE `profile` SET `alias` = "dev" WHERE `id` = "1"');
        db()->query('UPDATE `profile` SET `alias` = "admin" WHERE `id` = "12"');
        section('profiles', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        section2action('profiles','export', ['move' => 'toggle', 'profileIds' => '1']);
        section('controlElements', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        section2action('controlElements','export', ['move' => 'form', 'profileIds' => '1']);
        section('admins', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        section2action('admins','export', ['move' => 'toggle', 'profileIds' => '1']);
        if ($_ = field('columnType', 'title')) $_->delete();
        $fieldIdA_enumset = im(db()->query('SELECT `id` FROM `field` WHERE `relation` = "6"')->col());
        $fieldIdA_dependent = im(db()->query('SELECT DISTINCT `fieldId` FROM `consider`')->col());
        $textTypes = im([coltype('TEXT')->id, coltype('VARCHAR(255)')->id]);
        $fieldIdA_text = im(db()->query('
            SELECT `id` 
            FROM `field` 
            WHERE 1
              AND `id` IN (' . $fieldIdA_dependent . ') 
              AND `relation` = "0" 
              AND `columnTypeId` IN (' . $textTypes . ')
        ')->col());
        $foreign = field('enumset', 'title')->id;
        d(m('consider')->all([
            '`fieldId` IN (' . $fieldIdA_text . ')',
            '`consider` IN (' . $fieldIdA_enumset . ')',
            '`foreign` = "0"'
        ]));
        db()->query('
            UPDATE `consider` 
            SET `foreign` = "' . $foreign . '"
            WHERE 1
              AND `fieldId` IN (' . $fieldIdA_text . ')
              AND `consider` IN (' . $fieldIdA_enumset . ')
              AND `foreign` = "0" 
        ');
        field('realtime', 'title', ['columnTypeId' => 'TEXT']);
        field('section', 'help', [
            'title' => 'Справка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
            'move' => 'params',
        ]);
        field('realtime', 'adminId', ['relation' => 0]);
        field('realtime', 'entries', ['columnTypeId' => 'TEXT']);
        field('notice', 'sectionId', [
            'filter' => 'FIND_IN_SET(`sectionId`, "<?=m(\'section\')->all(\'`sectionId` = "0"\')->column(\'id\', true)?>")',
        ]);
        element('decimal143', ['title' => 'Число .000']);
        grid('queueChunk', 'move', ['move' => 'apply']);
        grid('sections', 'defaultSortField', ['rowReqIfAffected' => 'y']);
        if ($e = entity('manager'))
            if ($r = m('profile')->row('`entityId` = '. $e->id))
                $r->set('alias', 'manager')->save();
        param('realtime', 'spaceUntil', 'displayTimeFormat', ['cfgValue' => 'H:i:s']);
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
        section2action('lang','form', ['toggle' => 'n']);
        section2action('lang','delete', ['toggle' => 'n']);
        die('ok');
    }
    public function cfgFieldRemoveLegacyAction() {
        entity('possibleElementParam')->delete();
        field('param', 'value')->delete();
        entity('param', ['titleFieldId' => 'cfgField']);
        consider('param', 'title', 'cfgField', ['foreign' => 'title', 'required' => 'y']);
        m('Param')->all('`cfgField` = "0"')->delete();
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
        cfgField('element', 'price', 'measure', [
            'title' => 'Единица измерения',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'move' => 'maxlength',
        ]);
        cfgField('element', 'string', 'allowedTags', [
            'title' => 'Разрешенные теги',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string'
        ]);
        cfgField('element', 'upload', 'postfix', [
            'title' => 'Постфикс к имени файла при загрузке',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ]);
        cfgField('element', 'upload', 'rowTitle', [
            'title' => 'Включать заголовок записи в наименование файла при загрузке',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
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

        foreach (m('param')->all() as $paramR) {
            $possibleR = $paramR->foreign('possibleParamId');
            if ($cfgField = m('Field')->row([
                '`entityId` = "' . entity('element')->id . '"',
                '`entry` = "' . $possibleR->elementId . '"',
                '`alias` = "' . $possibleR->alias . '"'
            ])) {
                $paramR->cfgField = $cfgField->id;
                $paramR->basicUpdate();
            }
        }

        foreach (m('param')->all() as $paramR) {
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
        m('Section')->all('`type` = "s"')->delete();
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
        enumset('field', 'storeRelationAbility', 'one', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-login.png);"></span>Да, но только один ключ', 'move' => 'none']);
        enumset('field', 'storeRelationAbility', 'many', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-multikey.png);"></span>Да, несколько ключей', 'move' => 'one']);
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
        enumset('field', 'mode', 'regular', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/regular.png);"></span>Обычное', 'move' => '']);
        enumset('field', 'mode', 'required', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/required.png);"></span>Обязательное', 'move' => 'regular']);
        enumset('field', 'mode', 'readonly', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/readonly.png);"></span>Только чтение', 'move' => 'required']);
        enumset('field', 'mode', 'hidden', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/hidden.png);"></span>Скрытое', 'move' => 'readonly']);
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
        enumset('alteredField', 'mode', 'inherit', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/inherit.png);"></span>Без изменений', 'move' => '']);
        enumset('alteredField', 'mode', 'regular', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/regular.png);"></span>Обычное', 'move' => 'inherit']);
        enumset('alteredField', 'mode', 'required', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/required.png);"></span>Обязательное', 'move' => 'regular']);
        enumset('alteredField', 'mode', 'readonly', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/readonly.png);"></span>Только чтение', 'move' => 'required']);
        enumset('alteredField', 'mode', 'hidden', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/hidden.png);"></span>Скрытое', 'move' => 'readonly']);
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
        enumset('section', 'rowsetSeparate', 'auto', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/inherit.png);"></span>Авто', 'move' => '']);
        enumset('section', 'rowsetSeparate', 'yes', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/readonly.png);"></span>Отдельным запросом', 'move' => 'auto']);
        enumset('section', 'rowsetSeparate', 'no', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/field/required.png);"></span>В том же запросе', 'move' => 'yes']);
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
            'relation' => 'admin',
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
            'filter' => 'FIND_IN_SET(`sectionId`, "<?=m(\'Section\')->all(\'`sectionId` = "0"\')->column(\'id\', true)?>")',
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
        entity('realtime', [
            'title' => 'Рилтайм',
            'system' => 'y',
        ]);
        field('realtime', 'realtimeId', [
            'title' => 'Родительская запись',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'realtime',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'type', [
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'session',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('realtime', 'type', 'session', ['title' => 'Сессия']);
        enumset('realtime', 'type', 'channel', ['title' => 'Вкладка']);
        enumset('realtime', 'type', 'context', ['title' => 'Контекст']);
        field('realtime', 'profileId', [
            'title' => 'Роль',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'profile',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'adminId', [
            'title' => 'Пользователь',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'admin',
            'storeRelationAbility' => 'one',
        ]);
        consider('realtime', 'adminId', 'profileId', [
            'foreign' => 'entityId',
            'required' => 'y',
        ]);
        field('realtime', 'token', [
            'title' => 'Токен',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ]);
        field('realtime', 'spaceSince', [
            'title' => 'Начало',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>',
        ]);
        param('realtime', 'spaceSince', 'displayTimeFormat', 'H:i:s');
        param('realtime', 'spaceSince', 'displayDateFormat', 'Y-m-d');
        field('realtime', 'spaceUntil', [
            'title' => 'Конец',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
        ]);
        field('realtime', 'spaceFrame', [
            'title' => 'Длительность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ]);
        field('realtime', 'langId', [
            'title' => 'Язык',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'lang',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'sectionId', [
            'title' => 'Раздел',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'section',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'entityId', [
            'title' => 'Сущность',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'entity',
            'storeRelationAbility' => 'one',
        ]);
        field('realtime', 'entries', [
            'title' => 'Записи',
            'columnTypeId' => 'TEXT',
            'elementId' => 'combo',
            'storeRelationAbility' => 'many',
        ]);
        consider('realtime', 'entries', 'sectionId', [
            'foreign' => 'entityId',
            'required' => 'y',
        ]);
        field('realtime', 'fields', [
            'title' => 'Поля',
            'columnTypeId' => 'TEXT',
            'elementId' => 'combo',
            'relation' => 'field',
            'storeRelationAbility' => 'many',
        ]);
        consider('realtime', 'fields', 'entityId', [
            'required' => 'y',
        ]);
        field('realtime', 'title', [
            'title' => 'Запись',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'hidden',
        ]);
        field('realtime', 'mode', [
            'title' => 'Режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('realtime', 'mode', 'none', ['title' => 'Не применимо']);
        enumset('realtime', 'mode', 'rowset', ['title' => 'Набор записей']);
        enumset('realtime', 'mode', 'row', ['title' => 'Одна запись']);
        field('realtime', 'scope', [
            'title' => 'Scope',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ]);
        entity('realtime', ['titleFieldId' => 'title']);
        section('realtime', [
            'sectionId' => 'configuration',
            'entityId' => 'realtime',
            'title' => 'Рилтайм',
            'defaultSortField' => 'spaceSince',
            'type' => 's',
            'groupBy' => 'adminId',
            'roleIds' => '1',
            'multiSelect' => '1',
        ])->nested('grid')->delete();
        section2action('realtime','form', ['profileIds' => '1']);
        section2action('realtime','index', ['profileIds' => '1']);
        section2action('realtime','save', ['profileIds' => '1']);
        section2action('realtime','delete', ['profileIds' => '1']);
        action('restart', ['title' => 'Перезапустить', 'rowRequired' => 'n', 'type' => 's']);
        section2action('realtime','restart', [
            'profileIds' => '1',
            'rename' => 'Перезагрузить websocket-сервер',
        ]);
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
    public function testAction() {
        mt();
        //for ($i = 0; $i < 50; $i++) m('Test')->new(['title' => 'Test' . str_pad($i+1, 2, '0', STR_PAD_LEFT)])->save();
        /*for ($i = 0; $i < 10000; $i++) {
            //db()->query('SELECT * FROM `test` WHERE `id`="169" OR `title` <= "Тест 12111" ORDER BY `title` DESC LIMIT 2')->fetchAll();
            db()->query('SELECT * FROM `test` ORDER BY `title` ASC LIMIT 25, 1')->fetchAll();
        }*/
        //m('Test')->new(['title' => 'Жопа 2'])->save();
        //m('Test')->row('`id` = "206"')->delete();
        Indi::iflush(true);
        for ($i = 1; $i <= 1000; $i++) {
            break;
            $data = ['title' => 'Test ' . $i]; d($data);
            m('Test')->new($data)->save();
        }
        m('Test')->all()->delete();
        Indi::ws(false);
        d(mt());
        die('xx1');
    }
    public function actionsl10nAction(){
        field('section2action', 'l10n', [
            'title' => 'Мультиязычность',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('section2action', 'l10n', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('section2action', 'l10n', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включена']);
        enumset('section2action', 'l10n', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        enumset('section2action', 'l10n', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключена']);
        grid('sectionActions', 'l10n', true)->move(6);
        die('ok');
    }
    public function fixQueueTaskParamsAction() {
        field('queueTask', 'params', [
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ]);
        field('alteredField', 'elementId', [
            'title' => 'Элемент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'element',
            'storeRelationAbility' => 'one',
        ])->move(1);
        param('alteredField', 'elementId', 'placeholder', 'Без изменений');
        alteredField('queueItem', 'result', ['elementId' => 'string']);
        field('queueChunk', 'where', [
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ]);
        die('ok');
    }
    public function wordingsAction() {
        action('wordings', ['title' => 'Вординги', 'type' => 's']);
        section2action('lang','wordings', ['profileIds' => '1']);
        die('ok');
    }
    public function titleField2considerAction() {
        foreach (m('Entity')->all() as $entityR) {
            if (!$entityR->titleFieldId) continue;
            if ($entityR->foreign('titleFieldId')->storeRelationAbility == 'none') continue;
            if (!$fieldR_title = m($entityR->id)->fields('title')) continue;
            $existing = consider($entityR->table, 'title', $entityR->foreign('titleFieldId')->alias);
            d($entityR->table . ':' . $entityR->foreign('titleFieldId')->alias);
            if (Indi::get()->do) $newupdated = consider($entityR->table, 'title', $entityR->foreign('titleFieldId')->alias, ['foreign' => 'title']);
        }
        die('xx');
    }
    public function l10n2Action() {
        action('chart', ['title' => 'График', 'type' => 's']);
        consider('grid', 'alterTitle', 'title', ['required' => 'y']);
        consider('search', 'alt', 'title', ['required' => 'y']);
        consider('section2action', 'rename', 'title', ['required' => 'y']);
        consider('alteredField', 'rename', 'title', ['required' => 'y']);
        consider('grid', 'tooltip', 'fieldId', ['foreign' => 'tooltip', 'required' => 'y']);
        field('queueChunk', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
        ]);
        field('queueItem', 'result', [
            'title' => 'Результат',
            'columnTypeId' => 'TEXT',
            'elementId' => 'html',
        ]);
        field('queueChunk', 'queueChunkId', [
            'title' => 'Родительский сегмент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'relation' => 'queueChunk',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ])->move(14);
        entity('queueChunk', ['titleFieldId' => 'location']);
        field('queueChunk', 'fraction', [
            'title' => 'Фракция',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ])->move(14);
        enumset('queueChunk', 'fraction', 'none', ['title' => 'Не указана']);
        enumset('queueChunk', 'fraction', 'adminSystemUi', ['title' => 'AdminSystemUi']);
        enumset('queueChunk', 'fraction', 'adminCustomUi', ['title' => 'AdminCustomUi']);
        enumset('queueChunk', 'fraction', 'adminCustomData', ['title' => 'AdminCustomData']);
        section('queueChunk', ['groupBy' => 'fraction']);

        field('enumset', 'title', ['columnTypeId' => 'TEXT']);
        if (action('login')) action('login', ['type' => 's']);
        foreach (ar('grid,alteredField,search') as $table) db()->query('
            UPDATE `' . $table . '` `g`, `field` `f` SET `g`.`title` = `f`.`title` WHERE `g`.`fieldId` = `f`.`id`
        ');
        field('queueTask', 'stageState', [
            'title' => 'Этап - Статус',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'hidden',
        ])->move(14);
        consider('queueTask', 'stageState', 'stage', [
            'required' => 'y',
        ]);
        consider('queueTask', 'stageState', 'state', [
            'required' => 'y',
        ]);
        section('queueTask', ['groupBy' => 'stageState']);
        m('QueueTask')->batch(function($r){$r->setStageState(); $r->save();});
        field('queueChunk', 'where', ['columnTypeId' => 'TEXT']);
        grid('queueTask', 'itemsBytes', ['summaryType' => 'sum']);
        die('xx');
    }
    public function l10nAction() {
        enumset('field', 'l10n', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключена']);
        enumset('field', 'l10n', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение'])->move(1);
        enumset('field', 'l10n', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включена']);
        enumset('field', 'l10n', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);

        entity('lang', [
            'title' => 'Язык',
            'system' => 'y',
        ]);
        field('lang', 'title', [
            'title' => 'Наименование',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'required',
        ]);
        field('lang', 'alias', [
            'title' => 'Ключ',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'required',
        ]);
        field('lang', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен']);
        enumset('lang', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен']);
        field('lang', 'state', [
            'title' => 'Состояние',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'noth',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('lang', 'state', 'noth', ['title' => 'Ничего']);
        enumset('lang', 'state', 'smth', ['title' => 'Чтото']);
        field('lang', 'admin', [
            'title' => 'Админка',
            'elementId' => 'span',
        ]);
        field('lang', 'adminSystem', [
            'title' => 'Система',
            'elementId' => 'span',
        ]);
        field('lang', 'adminSystemUi', [
            'title' => 'Интерфейс',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminSystemUi', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен']);
        enumset('lang', 'adminSystemUi', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('lang', 'adminSystemUi', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен']);
        enumset('lang', 'adminSystemUi', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        field('lang', 'adminSystemConst', [
            'title' => 'Константы',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminSystemConst', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен']);
        enumset('lang', 'adminSystemConst', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('lang', 'adminSystemConst', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен']);
        enumset('lang', 'adminSystemConst', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        field('lang', 'adminCustom', [
            'title' => 'Проект',
            'elementId' => 'span',
        ]);
        field('lang', 'adminCustomUi', [
            'title' => 'Интерфейс',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomUi', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен']);
        enumset('lang', 'adminCustomUi', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('lang', 'adminCustomUi', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен']);
        enumset('lang', 'adminCustomUi', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        field('lang', 'adminCustomConst', [
            'title' => 'Константы',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomConst', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен']);
        enumset('lang', 'adminCustomConst', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('lang', 'adminCustomConst', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен']);
        enumset('lang', 'adminCustomConst', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        field('lang', 'adminCustomData', [
            'title' => 'Данные',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomData', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен']);
        enumset('lang', 'adminCustomData', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('lang', 'adminCustomData', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен']);
        enumset('lang', 'adminCustomData', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        field('lang', 'adminCustomTmpl', [
            'title' => 'Шаблоны',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('lang', 'adminCustomTmpl', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключен']);
        enumset('lang', 'adminCustomTmpl', 'qy', ['title' => '<span class="i-color-box" style="background: lightgray; border: 3px solid blue;"></span>В очереди на включение']);
        enumset('lang', 'adminCustomTmpl', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включен']);
        enumset('lang', 'adminCustomTmpl', 'qn', ['title' => '<span class="i-color-box" style="background: blue; border: 3px solid lightgray;"></span>В очереди на выключение']);
        field('lang', 'move', [
            'title' => 'Порядок',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'move',
            'defaultValue' => '0',
        ]);
        entity('lang', ['titleFieldId' => 'title']);
        section('lang', [
            'sectionId' => 'configuration',
            'entityId' => 'lang',
            'title' => 'Языки',
            'type' => 's',
            'groupBy' => 'state',
            'roleIds' => '1',
            'multiSelect' => '1',
        ])->nested('grid')->delete();
        section2action('lang','index', ['profileIds' => '1']);
        section2action('lang','form', ['profileIds' => '1']);
        section2action('lang','save', ['profileIds' => '1']);
        section2action('lang','delete', ['profileIds' => '1']);
        section2action('lang','up', ['profileIds' => '1']);
        section2action('lang','down', ['profileIds' => '1']);
        action('dict', ['title' => 'Доступные языки', 'rowRequired' => 'n', 'type' => 's']);
        action('run', ['title' => 'Запустить', 'rowRequired' => 'y', 'type' => 's']);
        section2action('lang','dict', ['profileIds' => '1']);
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
        filter('lang', 'state', true);
        filter('lang', 'toggle', true);
        entity('queueTask', [
            'title' => 'Очередь задач',
            'system' => 'y',
        ]);
        field('queueTask', 'title', [
            'title' => 'Задача',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'required',
        ]);
        field('queueTask', 'datetime', [
            'title' => 'Создана',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'params', [
            'title' => 'Параметры',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ]);
        field('queueTask', 'proc', [
            'title' => 'Процесс',
            'elementId' => 'span',
        ]);
        field('queueTask', 'procSince', [
            'title' => 'Начат',
            'columnTypeId' => 'DATETIME',
            'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00',
        ]);
        field('queueTask', 'procID', [
            'title' => 'PID',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'stage', [
            'title' => 'Этап',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'count',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueTask', 'stage', 'count', ['title' => 'Оценка масштабов']);
        enumset('queueTask', 'stage', 'items', ['title' => 'Создание очереди']);
        enumset('queueTask', 'stage', 'queue', ['title' => 'Процессинг очереди']);
        enumset('queueTask', 'stage', 'apply', ['title' => 'Применение результатов']);
        field('queueTask', 'state', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueTask', 'state', 'waiting', ['title' => 'Ожидание']);
        enumset('queueTask', 'state', 'progress', ['title' => 'В работе']);
        enumset('queueTask', 'state', 'finished', ['title' => 'Завершено']);
        field('queueTask', 'chunk', [
            'title' => 'Сегменты',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ]);
        field('queueTask', 'count', [
            'title' => 'Оценка',
            'elementId' => 'span',
        ]);
        field('queueTask', 'countState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'countState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueTask', 'countState', 'progress', ['title' => 'В работе']);
        enumset('queueTask', 'countState', 'finished', ['title' => 'Завершено']);
        field('queueTask', 'countSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'items', [
            'title' => 'Создание',
            'elementId' => 'span',
        ]);
        field('queueTask', 'itemsState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'itemsState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueTask', 'itemsState', 'progress', ['title' => 'В работе']);
        enumset('queueTask', 'itemsState', 'finished', ['title' => 'Завершено']);
        field('queueTask', 'itemsSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        field('queueTask', 'itemsBytes', [
            'title' => 'Байт',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ]);
        field('queueTask', 'queue', [
            'title' => 'Процессинг',
            'elementId' => 'span',
        ]);
        field('queueTask', 'queueState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'queueState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueTask', 'queueState', 'progress', ['title' => 'В работе']);
        enumset('queueTask', 'queueState', 'finished', ['title' => 'Завершено']);
        enumset('queueTask', 'queueState', 'noneed', ['title' => 'Не требуется']);
        field('queueTask', 'queueSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ]);
        field('queueTask', 'apply', [
            'title' => 'Применение',
            'elementId' => 'span',
        ]);
        field('queueTask', 'applyState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueTask', 'applyState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueTask', 'applyState', 'progress', ['title' => 'В работе']);
        enumset('queueTask', 'applyState', 'finished', ['title' => 'Завершено']);
        field('queueTask', 'applySize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        entity('queueTask', ['titleFieldId' => 'title']);
        entity('queueChunk', [
            'title' => 'Сегмент очереди',
            'system' => 'y',
        ]);
        field('queueChunk', 'queueTaskId', [
            'title' => 'Очередь задач',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'queueTask',
            'storeRelationAbility' => 'one',
        ]);
        field('queueChunk', 'location', [
            'title' => 'Расположение',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ]);
        field('queueChunk', 'where', [
            'title' => 'Условие выборки',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ]);
        field('queueChunk', 'count', [
            'title' => 'Оценка',
            'elementId' => 'span',
        ]);
        field('queueChunk', 'countState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'countState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueChunk', 'countState', 'progress', ['title' => 'В работе']);
        enumset('queueChunk', 'countState', 'finished', ['title' => 'Завершено']);
        field('queueChunk', 'countSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        field('queueChunk', 'items', [
            'title' => 'Создание',
            'elementId' => 'span',
        ]);
        field('queueChunk', 'itemsState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'itemsState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueChunk', 'itemsState', 'progress', ['title' => 'В работе']);
        enumset('queueChunk', 'itemsState', 'finished', ['title' => 'Завершено']);
        field('queueChunk', 'itemsSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        field('queueChunk', 'queue', [
            'title' => 'Процессинг',
            'elementId' => 'span',
        ]);
        field('queueChunk', 'queueState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'queueState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueChunk', 'queueState', 'progress', ['title' => 'В работе']);
        enumset('queueChunk', 'queueState', 'finished', ['title' => 'Завершено']);
        enumset('queueChunk', 'queueState', 'noneed', ['title' => 'Не требуется']);
        field('queueChunk', 'queueSize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ]);
        field('queueChunk', 'apply', [
            'title' => 'Применение',
            'elementId' => 'span',
        ]);
        field('queueChunk', 'applyState', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'waiting',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        enumset('queueChunk', 'applyState', 'waiting', ['title' => 'Ожидание']);
        enumset('queueChunk', 'applyState', 'progress', ['title' => 'В работе']);
        enumset('queueChunk', 'applyState', 'finished', ['title' => 'Завершено']);
        field('queueChunk', 'applySize', [
            'title' => 'Размер',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'mode' => 'readonly',
        ]);
        entity('queueChunk', [
        ]);
        entity('queueItem', [
            'title' => 'Элемент очереди',
            'system' => 'y',
        ]);
        field('queueItem', 'queueTaskId', [
            'title' => 'Очередь',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
            'relation' => 'queueTask',
            'storeRelationAbility' => 'one',
            'mode' => 'readonly',
        ]);
        field('queueItem', 'queueChunkId', [
            'title' => 'Сегмент',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'queueChunk',
            'storeRelationAbility' => 'one',
        ]);
        field('queueItem', 'target', [
            'title' => 'Таргет',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'mode' => 'readonly',
        ]);
        field('queueItem', 'value', [
            'title' => 'Значение',
            'columnTypeId' => 'TEXT',
            'elementId' => 'string',
            'mode' => 'readonly',
        ]);
        field('queueItem', 'result', [
            'title' => 'Результат',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ]);
        field('queueItem', 'stage', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'items',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('queueItem', 'stage', 'items', ['title' => 'Добавлен']);
        enumset('queueItem', 'stage', 'queue', ['title' => 'Обработан']);
        enumset('queueItem', 'stage', 'apply', ['title' => 'Применен']);
        section('queueTask', [
            'sectionId' => 'configuration',
            'entityId' => 'queueTask',
            'title' => 'Очереди задач',
            'defaultSortField' => 'datetime',
            'defaultSortDirection' => 'DESC',
            'disableAdd' => '1',
            'type' => 's',
            'roleIds' => '1',
        ])->nested('grid')->delete();
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
        grid('queueTask', 'itemsBytes', ['gridId' => 'items']);
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
            'disableAdd' => '1',
            'type' => 's',
            'rowsetSeparate' => 'no',
            'roleIds' => '1',
            'rownumberer' => '1',
        ])->nested('grid')->delete();
        section2action('queueChunk','index', ['profileIds' => '1']);
        section2action('queueChunk','form', ['profileIds' => '1']);
        grid('queueChunk', 'entityId', ['toggle' => 'n']);
        grid('queueChunk', 'fieldId', ['toggle' => 'n']);
        grid('queueChunk', 'location', true);
        grid('queueChunk', 'where', true);
        grid('queueChunk', 'count', true);
        grid('queueChunk', 'countState', ['gridId' => 'count']);
        grid('queueChunk', 'countSize', ['gridId' => 'count', 'summaryType' => 'sum']);
        grid('queueChunk', 'items', true);
        grid('queueChunk', 'itemsState', ['gridId' => 'items']);
        grid('queueChunk', 'itemsSize', ['gridId' => 'items', 'summaryType' => 'sum']);
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
        ])->nested('grid')->delete();
        section2action('queueItem','index', ['profileIds' => '1']);
        section2action('queueItem','save', ['profileIds' => '1']);
        grid('queueItem', 'target', true);
        grid('queueItem', 'value', true);
        grid('queueItem', 'result', ['editor' => 1,
        ]);
        grid('queueItem', 'stage', true);
        filter('queueItem', 'stage', true);
        die('xx');
    }

    public function filterTipAction() {
        field('search', 'tooltip', [
            'title' => 'Подсказка',
            'columnTypeId' => 'TEXT',
            'elementId' => 'textarea',
        ]);
        die('ok');
    }
    public function sectiontogglehAction() {
        enumset('section', 'toggle', 'h', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Скрыт']);
        die('ok');
    }
    public function noticegettertoggleAction() {
        field('noticeGetter', 'toggle', [
            'title' => 'Статус',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'y',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ])->move(14);
        enumset('noticeGetter', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включен']);
        enumset('noticeGetter', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключен']);
        grid('noticeGetters', 'toggle', true)->move(5);
        die('ok');
    }
    public function rowexpanderAction() {
        enumset('grid', 'toggle', 'e', ['title' => '<span class="i-color-box" style="background: lightgray; border: 1px solid blue;"></span>Скрыт, но показан в развороте']);
        die('ok');
    }
    public function fieldsmodeAction() {
        action('activate', ['title' => 'Активировать', 'rowRequired' => 'y', 'type' => 's']);
        section2action('fields','activate', ['profileIds' => '1', 'rename' => 'Выбрать режим'])->move(1);
        die('ok');
    }

    public function gridcolWidthUsageAction() {
        field('grid', 'width', [
            'title' => 'Ширина',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ])->move(6);
        param('grid', 'width', 'measure', 'px');
        grid('grid', 'width', ['editor' => '1']);
        action('rwu', ['rowRequired' => 'n', 'type' => 's', 'display' => 0]);
        die('ok');
    }

    public function foreignFilterAction() {
        field('search', 'further', [
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ])->move(12);
        consider('search', 'further', 'fieldId', [
            'foreign' => 'relation',
            'required' => 'y',
            'connector' => 'entityId',
        ]);
        die('ok');
    }

    public function foreignGridAction(){
        field('grid', 'further', [
            'title' => 'Поле по ключу',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ])->move(13);
        consider('grid', 'further', 'fieldId', [
            'foreign' => 'relation',
            'required' => 'y',
            'connector' => 'entityId',
        ]);
        die('ok');
    }

    public function last4Action() {
        foreach (ar('sectionancestor,rownumberer,filterallowclear,sectionmultiselect') as $_)
            $this->{$_ . 'Action'}();
        foreach (ar('sectionActions,grid,alteredFields,search') as $s)
            section($s, ['extendsPhp' => 'Indi_Controller_Admin_Multinew']);
        die('ok');
    }

    public function sectionmultiselectAction() {
        field('section', 'multiSelect', [
            'title' => 'Выделение более одной записи',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check'
        ]);
        //die('ok');
    }

    public function filterallowclearAction() {
        field('search', 'allowClear', [
            'title' => 'Разрешить сброс',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '1',
        ])->move(6);
        //die('ok');
    }

    public function rownumbererAction() {
        field('section', 'rownumberer', [
            'title' => 'Включить нумерацию строк',
            'columnTypeId' => 'BOOLEAN',
            'elementId' => 'check',
            'defaultValue' => '0',
        ]);
        //die('ok');
    }

    public function sectionancestorAction() {
        field('section', 'extends', ['title' => 'Родительский класс PHP', 'alias' => 'extendsPhp']);
        field('section', 'extendsJs', [
            'title' => 'Родительский класс JS',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
            'defaultValue' => 'Indi.lib.controller.Controller',
        ])->move(12);
        grid('sections','rowsOnPage', [
            'alterTitle' => 'СНС',
            'tooltip' => 'Строк на странице',
        ]);
        grid('sections','extendsPhp', ['editor' => 1]);
        grid('sections','extendsJs', ['editor' => 1]);
        //die('ok');
    }

    public function lockedAction() {
        field('grid', 'group', [
            'title' => 'Группа',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'normal',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ])->move(2);
        enumset('grid', 'group', 'normal', ['title' => 'Обычные']);
        enumset('grid', 'group', 'locked', ['title' => 'Зафиксированные']);
        section('grid', ['groupBy' => 'group']);
        grid('grid', 'group', true);
        param('grid', 'gridId', 'groupBy', 'group');
        die('ok');
    }

    public function summaryTypeAction() {
        field('grid', 'summaryType', [
            'title' => 'Внизу',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ])->move(2);
        enumset('grid', 'summaryType', 'none', ['title' => 'Пусто']);
        enumset('grid', 'summaryType', 'sum', ['title' => 'Сумма']);
        enumset('grid', 'summaryType', 'average', ['title' => 'Среднее']);
        enumset('grid', 'summaryType', 'min', ['title' => 'Минимум']);
        enumset('grid', 'summaryType', 'max', ['title' => 'Максимум']);
        enumset('grid', 'summaryType', 'text', ['title' => 'Текст']);
        field('grid', 'summaryText', [
            'title' => 'Текст',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'string',
        ])->move(2);
        die('ok');
    }

    public function convertSatelliteAction() {
        if (entity('user'))
            foreach (ar('color,testIds,storeRelationAbility,test,entityId') as $prop)
                if ($_ = field('user', $prop))
                    $_->delete();
        entity('columnType', ['titleFieldId' => 'type']);
        enumset('field', 'storeRelationAbility', 'none', ['title' => '<span class="i-color-box" style="background: white;"></span>Нет']);
        enumset('field', 'storeRelationAbility', 'one', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-login.png);"></span>Да, но для только одного значения ключа']);
        enumset('field', 'storeRelationAbility', 'many', ['title' => '<span class="i-color-box" style="background: url(resources/images/icons/btn-icon-multikey.png);"></span>Да, для энного количества значений ключей']);
        if ($_ = section('fieldsAll')) $_->delete();
        section('fieldsAll', [
            'sectionId' => 'configuration',
            'entityId' => 'field',
            'title' => 'Все поля',
            'disableAdd' => '1',
            'type' => 's',
            'groupBy' => 'entityId',
        ])->nested('grid')->delete();
        section2action('fieldsAll','index', ['profileIds' => '1']);
        section2action('fieldsAll','form', ['profileIds' => '1']);
        section2action('fieldsAll','save', ['profileIds' => '1']);
        grid('fieldsAll','entityId', [
            'alterTitle' => 'Сущность',
            'tooltip' => 'Сущность, в структуру которой входит это поле',
        ]);
        grid('fieldsAll','title', [
            'alterTitle' => 'Наименование',
            'editor' => 1,
        ]);
        grid('fieldsAll','view', ['alterTitle' => 'Отображение']);
        grid('fieldsAll','mode', ['gridId' => 'view']);
        grid('fieldsAll','elementId', [
            'alterTitle' => 'UI',
            'tooltip' => 'Элемент управления',
            'gridId' => 'view',
        ]);
        grid('fieldsAll','tooltip', ['gridId' => 'view']);
        grid('fieldsAll','mysql', ['alterTitle' => 'MySQL']);
        grid('fieldsAll','alias', [
            'alterTitle' => 'Имя',
            'gridId' => 'mysql',
        ]);
        grid('fieldsAll','columnTypeId', [
            'alterTitle' => 'Тип',
            'tooltip' => 'Тип столбца MySQL',
            'gridId' => 'mysql',
        ]);
        grid('fieldsAll','defaultValue', [
            'alterTitle' => 'По умолчанию',
            'tooltip' => 'Значение по умолчанию',
            'gridId' => 'mysql',
        ]);
        grid('fieldsAll','fk', ['alterTitle' => 'Ключи']);
        grid('fieldsAll','storeRelationAbility', [
            'alterTitle' => 'Режим',
            'tooltip' => 'Предназначено для хранения ключей',
            'gridId' => 'fk',
        ]);
        grid('fieldsAll','relation', [
            'alterTitle' => 'Сущность',
            'tooltip' => 'Ключи какой сущности будут храниться в этом поле',
            'gridId' => 'fk',
        ]);
        grid('fieldsAll','filter', [
            'alterTitle' => 'Фильтрация',
            'tooltip' => 'Статическая фильтрация',
            'gridId' => 'fk',
        ]);
        grid('fieldsAll','l10n', [
            'alterTitle' => 'l10n',
            'tooltip' => 'Мультиязычность',
        ]);
        grid('fieldsAll','move', true);
        grid('fieldsAll','satellitealias', ['toggle' => 'n']);
        grid('fieldsAll','span', ['toggle' => 'n']);
        grid('fieldsAll','dependency', ['gridId' => 'span']);
        grid('fieldsAll','satellite', ['gridId' => 'span']);
        grid('fieldsAll','alternative', ['gridId' => 'span']);
        filter('fieldsAll', 'entityId', ['alt' => 'Сущность']);
        filter('fieldsAll', 'mode', true);
        filter('fieldsAll', 'relation', ['alt' => 'Ключи']);
        filter('fieldsAll', 'elementId', ['alt' => 'Элемент']);
        filter('fieldsAll', 'dependency', true);

        // Convert satellite-cfg into consider-cfg
        foreach (m('Field')->all('`dependency` != "u"') as $fieldR) {
            $ctor = [];
            if ($fieldR->alternative) $ctor['foreign'] = $fieldR->alternative;
            if (!m($fieldR->entityId)->fields($fieldR->alias)->param('allowZeroSatellite'))
                $ctor['required'] = 'y';
            if ($_ = $fieldR->foreign('satellite')->satellitealias) $ctor['connector'] = $_;
            if (!$ctor) $ctor = true;
            consider($fieldR->foreign('entityId')->table, $fieldR->alias, $fieldR->foreign('satellite')->alias, $ctor);
        }
        field('section', 'parentSectionConnector', ['filter' => '`storeRelationAbility`!="none"']);

        // Erase satellite-cfg and hide fields, responsible for satellite-functionaity
        //db()->query('UPDATE `field` SET `dependency` = "u", `satellitealias` = "", `satellite` = "0", `alternative` = ""');
        foreach (ar('span,dependency,satellitealias,satellite,alternative') as $field)
            field('field', $field, ['mode' => 'readonly']);
        die('ok');
    }
    public function admindemoAction() {
        field('admin', 'demo', [
            'title' => 'Демо-режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('admin', 'demo', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
        enumset('admin', 'demo', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да']);
        grid('admins', 'demo', ['alterTitle' => 'Демо', 'tooltip' => 'Демо-режим']);
        field('profile', 'demo', [
            'title' => 'Демо-режим',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('profile', 'demo', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
        enumset('profile', 'demo', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да']);
        grid('profiles', 'demo', ['alterTitle' => 'Демо', 'tooltip' => 'Демо-режим']);
        die('ok');
    }

    public function queueChunkItemBytesAction() {
        field('queueChunk', 'itemsBytes', [
            'title' => 'Байт',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'number',
            'defaultValue' => '0',
        ])->move(6);
        grid('queueChunk', 'itemsBytes', ['gridId' => 'items', 'summaryType' => 'sum']);
        grid('queueChunk', 'move');
        section('queueChunk', [
            'defaultSortField' => 'move',
        ]);
        db()->query('UPDATE `profile` SET `entityId` = "11" WHERE `entityId` = "0"');
        field('profile', 'type', [
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'radio',
            'defaultValue' => 'p',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('profile', 'type', 's', ['title' => '<font color=red>Системная</font>']);
        enumset('profile', 'type', 'p', ['title' => 'Проектная']);
        db()->query('UPDATE `profile` SET `type` = "s" WHERE `id` = "1"');
        grid('profiles', 'type', true)->move(3);
        die('ok');
    }

    public function filesGroupByAction() {
        field('entity', 'filesGroupBy', [
            'title' => 'Группировать файлы',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`entityId` = "<?=$this->id?>" AND `storeRelationAbility` = "one"',
        ]);
        grid('entities', 'filesGroupBy', ['editor' => 1]);
        die('ok');
    }

    public function exportAction() {
        section2action('sectionActions','export', ['profileIds' => '1']);
        section2action('grid','export', ['profileIds' => '1']);
        section2action('alteredFields','export', ['profileIds' => '1']);
        section2action('search','export', ['profileIds' => '1']);
        section2action('fields','export', ['profileIds' => '1']);
        section2action('enumset','export', ['profileIds' => '1']);
        section2action('resize','export', ['profileIds' => '1']);
        section2action('params','export', ['profileIds' => '1']);
        section2action('consider','export', ['profileIds' => '1']);
        section('enumset', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        section('params', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        section('consider', ['extendsPhp' => 'Indi_Controller_Admin_Exportable']);
        if ($_ = section2action('entities','cache')) $_->delete();
        if ($_ = section2action('entities','author')) $_->delete();
        die('ok');
    }

    public function noticetypeAction(){
        field('notice', 'type', [
            'title' => 'Тип',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'p',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ])->move(19);
        enumset('notice', 'type', 'p', ['title' => 'Проектное']);
        enumset('notice', 'type', 's', ['title' => '<font color=red>Системное</font>']);
        die('ok');
    }
    public function tileAction() {
        field('section', 'tileField', [
            'title' => 'Плитка',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
            'filter' => '`elementId` = "14"',
        ])->move(7);
        consider('section', 'tileField', 'entityId', [
            'required' => 'y',
        ]);
        field('section', 'tileThumb', [
            'title' => 'Превью',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'resize',
            'storeRelationAbility' => 'one',
        ])->move(7);
        consider('section', 'tileThumb', 'tileField', [
            'required' => 'y',
            'connector' => 'fieldId',
        ]);
        die('ok');
    }

    public function uieditAction() {
        field('admin', 'uiedit', [
            'title' => 'Правки UI',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('admin', 'uiedit', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Выключено']);
        enumset('admin', 'uiedit', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Включено']);
        grid('admins', 'uiedit', true);
        die('ok');
    }
}