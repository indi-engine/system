<?php
/**
 * This is a temporary controller, that used for adjusting database-stored system features of Indi Engine.
 * Methods declared within this class, will be deleted once all database-stored system features will be adjusted
 * on all projects, that run on Indi Engine
 */
class Admin_TemporaryController extends Indi_Controller {

    public function satelliteAction() {

        field('consider', 'required', [
            'title' => 'Обязательное',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'n',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('consider', 'required', 'y', ['title' => '<span class="i-color-box" style="background: blue;"></span>Да']);
        enumset('consider', 'required', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
        grid('consider','required', [
            'alterTitle' => '[ ! ]',
            'tooltip' => 'Обязательное',
        ]);
        $connector = field('consider', 'connector', [
            'title' => 'Коннектор',
            'columnTypeId' => 'INT(11)',
            'elementId' => 'combo',
            'defaultValue' => '0',
            'relation' => 'field',
            'storeRelationAbility' => 'one',
        ]);

        grid('consider', 'connector', true);
        if (!m('Consider')->row('`fieldId` = "' . $connector->id . '"'))
            m('Consider')->new([
                'entityId' => entity('consider')->id,
                'fieldId' => $connector->id,
                'consider' => field('consider', 'fieldId')->id,
                'foreign' => field('field', 'relation')->id,
                'required' => 'y'
            ])->save();
        die('ok');
    }

    /**
     * Convert disabledFields-feature to alteredFields-feature
     */
    public function alteredFieldsAction() {
        die('disabled');
        // If 'disabledFields' entity exists, and 'displayInForm' field exists in it
        if (field('disabledField', 'displayInForm')) {

            // Update `showInForm` field's inner props
            field('disabledField', 'displayInForm', [
                'title' => 'Режим', 'alias' => 'mode', 'storeRelationAbility' => 'one',
                'elementId' => 'combo', 'columnTypeId' => 'ENUM',
            ]);

            // Update existing possible values
            enumset('disabledField', 'mode', '0', ['alias' => 'hidden', 'title' => 'Скрытое', 'color' => 'url(resources/images/icons/field/hidden.png)']);
            enumset('disabledField', 'mode', '1', ['alias' => 'readonly', 'title' => 'Только чтение', 'color' => 'url(resources/images/icons/field/readonly.png)']);

            // Append new possible values
            foreach (['inherit' => 'Без изменений', 'regular' => 'Обычное', 'required' => 'Обязательное'] as $alias => $title)
                enumset('disabledField', 'mode', $alias, ['title' => $title, 'color' => 'url(resources/images/icons/field/' . $alias . '.png)'])
                    ->move(2);

            // Other things
            enumset('disabledField', 'mode', 'hidden')->move(-1);
            field('disabledField', 'mode', ['defaultValue' => 'inherit']);
            field('disabledField', 'fieldId', ['title' => 'Поле']);
            field('disabledField', 'mode')->move(1);
            entity('disabledField')->set(['title' => 'Поле, измененное в рамках раздела'])->save();
            section('disabledFields', ['title' => 'Измененные поля', 'alias' => 'alteredFields']);
            field('disabledField', 'alter', ['title' => 'Изменить свойства поля', 'elementId' => 'span']);
            field('disabledField', 'rename', ['title' => 'Наименование', 'elementId' => 'string', 'columnTypeId' => 'VARCHAR(255)']);
            field('disabledField', 'defaultValue')->move(-5);
            field('disabledField', 'mode')->move(-5);
            grid('alteredFields', 'rename', ['editor' => 1])->move(2);
            grid('alteredFields', 'mode')->move(1);
            grid('alteredFields', 'impact', ['editor' => 1]);
            grid('alteredFields', 'profileIds', ['editor' => 1]);
        }

        // If entity, having table 'disabledField' exists - rename it
        if (entity('disabledField')) entity('disabledField', ['table' => 'alteredField']);

        //
        die('ok');
    }

    public function noticesAction() {
        die('disabled');
        // If notices system is already is in it's last version - return
        if (!(!m('NoticeGetter', true) || !m('NoticeGetter')->fields('criteriaRelyOn'))) die('already ok');

        // Remove previous version of notices, if exists
        if (entity('noticeGetter')) entity('noticeGetter')->delete();
        if (entity('notice')) entity('notice')->delete();

        // Create `notice` entity
        if (true) {
            entity('notice', [
                'title' => 'Уведомление',
                'system' => 'y',
            ]);
            field('notice', 'title', [
                'title' => 'Наименование',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
                'mode' => 'required',
            ]);
            field('notice', 'entityId', [
                'title' => 'Сущность',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'combo',
                'relation' => 'entity',
                'storeRelationAbility' => 'one',
                'mode' => 'required',
            ]);
            field('notice', 'event', [
                'title' => 'Событие / PHP',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
            ]);
            field('notice', 'profileId', [
                'title' => 'Получатели',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'combo',
                'relation' => 'profile',
                'storeRelationAbility' => 'many',
                'mode' => 'required',
            ]);
            field('notice', 'toggle', [
                'title' => 'Статус',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'y',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('notice', 'toggle', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Включено']);
            enumset('notice', 'toggle', 'n', ['title' => '<span class="i-color-box" style="background: red;"></span>Выключено']);
            field('notice', 'qty', [
                'title' => 'Счетчик',
                'elementId' => 'span',
            ]);
            field('notice', 'qtySql', [
                'title' => 'Отображение / SQL',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
                'mode' => 'required',
            ]);
            field('notice', 'qtyDiffRelyOn', [
                'title' => 'Направление изменения',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'event',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('notice', 'qtyDiffRelyOn', 'event', ['title' => 'Одинаковое для всех получателей']);
            enumset('notice', 'qtyDiffRelyOn', 'getter', ['title' => 'Неодинаковое, зависит от получателя']);
            field('notice', 'sectionId', [
                'title' => 'Пункты меню',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'combo',
                'relation' => 'section',
                'satellite' => 'entityId',
                'dependency' => 'с',
                'storeRelationAbility' => 'many',
                'filter' => 'FIND_IN_SET(`sectionId`, "<?=m(\'Section\')->all(\'`sectionId` = "0"\')->column(\'id\', true)?>")',
            ]);
            field('notice', 'bg', [
                'title' => 'Цвет фона',
                'columnTypeId' => 'VARCHAR(10)',
                'elementId' => 'color',
                'defaultValue' => '212#d9e5f3',
            ]);
            field('notice', 'fg', [
                'title' => 'Цвет текста',
                'columnTypeId' => 'VARCHAR(10)',
                'elementId' => 'color',
                'defaultValue' => '216#044099',
            ]);
            field('notice', 'tooltip', [
                'title' => 'Подсказка',
                'columnTypeId' => 'TEXT',
                'elementId' => 'textarea',
            ]);
            field('notice', 'tpl', [
                'title' => 'Сообщение',
                'elementId' => 'span',
            ]);
            field('notice', 'tplFor', [
                'title' => 'Назначение',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'inc',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('notice', 'tplFor', 'inc', ['title' => 'Увеличение']);
            enumset('notice', 'tplFor', 'dec', ['title' => 'Уменьшение']);
            enumset('notice', 'tplFor', 'evt', ['title' => 'Изменение']);
            field('notice', 'tplIncSubj', [
                'title' => 'Заголовок',
                'columnTypeId' => 'TEXT',
                'elementId' => 'string',
            ]);
            field('notice', 'tplIncBody', [
                'title' => 'Текст',
                'columnTypeId' => 'TEXT',
                'elementId' => 'textarea',
            ]);
            field('notice', 'tplDecSubj', [
                'title' => 'Заголовок',
                'columnTypeId' => 'TEXT',
                'elementId' => 'string',
            ]);
            field('notice', 'tplDecBody', [
                'title' => 'Текст',
                'columnTypeId' => 'TEXT',
                'elementId' => 'textarea',
            ]);
            field('notice', 'tplEvtSubj', [
                'title' => 'Заголовок',
                'columnTypeId' => 'TEXT',
                'elementId' => 'string',
            ]);
            field('notice', 'tplEvtBody', [
                'title' => 'Сообщение',
                'columnTypeId' => 'TEXT',
                'elementId' => 'textarea',
            ]);
            entity('notice', ['titleFieldId' => 'title']);
        }

        // Create `noticeGetter` entity
        if (true) {
            entity('noticeGetter', [
                'title' => 'Получатель уведомлений',
                'system' => 'y',
            ]);
            field('noticeGetter', 'noticeId', [
                'title' => 'Уведомление',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'combo',
                'relation' => 'notice',
                'storeRelationAbility' => 'one',
                'mode' => 'readonly',
            ]);
            field('noticeGetter', 'profileId', [
                'title' => 'Роль',
                'columnTypeId' => 'INT(11)',
                'elementId' => 'combo',
                'relation' => 'profile',
                'storeRelationAbility' => 'one',
                'mode' => 'readonly',
            ]);
            field('noticeGetter', 'criteriaRelyOn', [
                'title' => 'Критерий',
                'columnTypeId' => 'ENUM',
                'elementId' => 'radio',
                'defaultValue' => 'event',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('noticeGetter', 'criteriaRelyOn', 'event', ['title' => 'Общий']);
            enumset('noticeGetter', 'criteriaRelyOn', 'getter', ['title' => 'Раздельный']);
            field('noticeGetter', 'criteriaEvt', [
                'title' => 'Общий',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
            ]);
            field('noticeGetter', 'criteriaInc', [
                'title' => 'Для увеличения',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
            ]);
            field('noticeGetter', 'criteriaDec', [
                'title' => 'Для уменьшения',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
            ]);
            field('noticeGetter', 'title', [
                'title' => 'Ауто титле',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
                'mode' => 'hidden',
            ]);
            field('noticeGetter', 'email', [
                'title' => 'Дублирование на почту',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'n',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('noticeGetter', 'email', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
            enumset('noticeGetter', 'email', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да']);
            field('noticeGetter', 'vk', [
                'title' => 'Дублирование в ВК',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'n',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('noticeGetter', 'vk', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
            enumset('noticeGetter', 'vk', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да']);
            field('noticeGetter', 'sms', [
                'title' => 'Дублирование по SMS',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'n',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);
            enumset('noticeGetter', 'sms', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
            enumset('noticeGetter', 'sms', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да']);
            field('noticeGetter', 'criteria', [
                'title' => 'Критерий',
                'columnTypeId' => 'VARCHAR(255)',
                'elementId' => 'string',
                'mode' => 'hidden',
            ]);
            field('noticeGetter', 'mail', [
                'title' => 'Дублирование на почту',
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'n',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
                'mode' => 'hidden',
            ]);
            enumset('noticeGetter', 'mail', 'n', ['title' => '<span class="i-color-box" style="background: lightgray;"></span>Нет']);
            enumset('noticeGetter', 'mail', 'y', ['title' => '<span class="i-color-box" style="background: lime;"></span>Да']);
            entity('noticeGetter', ['titleFieldId' => 'profileId']);
        }

        // Create `notices` section
        if (true) {
            section('notices', [
                'sectionId' => 'configuration',
                'entityId' => 'notice',
                'title' => 'Уведомления',
                'defaultSortField' => 'title',
                'type' => 's',
            ])->nested('grid')->delete();
            section2action('notices','index', ['profileIds' => 1]);
            section2action('notices','form', ['profileIds' => 1]);
            section2action('notices','save', ['profileIds' => 1]);
            section2action('notices','delete', ['profileIds' => 1]);
            section2action('notices','toggle', ['profileIds' => 1]);
            grid('notices','title', true);
            grid('notices','entityId', true);
            grid('notices','profileId', true);
            grid('notices','toggle', true);
            grid('notices','qty', true);
            grid('notices','qtySql', ['gridId' => 'qty']);
            grid('notices','event', ['gridId' => 'qty']);
            grid('notices','sectionId', ['gridId' => 'qty']);
            grid('notices','bg', ['gridId' => 'qty']);
            grid('notices','fg', ['gridId' => 'qty']);
        }

        // Create `noticeGetters` action
        if (true) {
            section('noticeGetters', [
                'sectionId' => 'notices',
                'entityId' => 'noticeGetter',
                'title' => 'Получатели',
                'defaultSortField' => 'profileId',
                'type' => 's',
            ])->nested('grid')->delete();
            section2action('noticeGetters','index', ['profileIds' => 1]);
            section2action('noticeGetters','form', ['profileIds' => 1]);
            section2action('noticeGetters','save', ['profileIds' => 1]);
            section2action('noticeGetters','delete', ['profileIds' => 1]);
            grid('noticeGetters','profileId', true);
            grid('noticeGetters','criteriaEvt', true);
            grid('noticeGetters','email', [
                'alterTitle' => 'Email',
                'tooltip' => 'Дублирование на почту',
            ]);
            grid('noticeGetters','vk', [
                'alterTitle' => 'VK',
                'tooltip' => 'Дублирование во ВКонтакте',
            ]);
            grid('noticeGetters','sms', [
                'alterTitle' => 'SMS',
                'tooltip' => 'Дублирование по SMS',
            ]);
        }

        die('ok');
    }

    public function changelogAction() {

        // Create/update `year` entity
        entity('year', ['title' => 'Год', 'system' => 'y']);
        field('year', 'title', ['title' => 'Наименование', 'columnTypeId' => 'VARCHAR(255)', 'elementId' => 'string', 'mode' => 'required']);
        entity('year', ['titleFieldId' => 'title']);

        // Create/update `month` entity
        entity('month', ['title' => 'Месяц', 'system' => 'y']);
        field('month', 'yearId', ['title' => 'Год', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo', 'relation' => 'year', 'storeRelationAbility' => 'one', 'mode' => 'required']);
        field('month', 'month', ['title' => 'Месяц', 'columnTypeId' => 'ENUM', 'elementId' => 'combo', 'defaultValue' => '01', 'relation' => 'enumset', 'storeRelationAbility' => 'one']);
        enumset('month', 'month', '01', ['title' => 'Январь']);
        enumset('month', 'month', '02', ['title' => 'Февраль']);
        enumset('month', 'month', '03', ['title' => 'Март']);
        enumset('month', 'month', '04', ['title' => 'Апрель']);
        enumset('month', 'month', '05', ['title' => 'Май']);
        enumset('month', 'month', '06', ['title' => 'Июнь']);
        enumset('month', 'month', '07', ['title' => 'Июль']);
        enumset('month', 'month', '08', ['title' => 'Август']);
        enumset('month', 'month', '09', ['title' => 'Сентябрь']);
        enumset('month', 'month', '10', ['title' => 'Октябрь']);
        enumset('month', 'month', '11', ['title' => 'Ноябрь']);
        enumset('month', 'month', '12', ['title' => 'Декабрь']);
        field('month', 'title', ['title' => 'Наименование', 'columnTypeId' => 'VARCHAR(255)', 'elementId' => 'string']);
        field('month', 'move', ['title' => 'Порядок', 'columnTypeId' => 'INT(11)', 'elementId' => 'move']);
        entity('month', ['titleFieldId' => 'title']);

        // Create/update `changeLog` entity
        entity('changeLog', ['title' => 'Корректировка', 'system' => 'y']);
        field('changeLog', 'entityId', ['title' => 'Сущность', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'entity', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        field('changeLog', 'key', ['title' => 'Объект', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'satellite' => 'entityId', 'dependency' => 'e', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        field('changeLog', 'fieldId', ['title' => 'Что изменено', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'field', 'satellite' => 'entityId', 'dependency' => 'с', 'storeRelationAbility' => 'one',
            'filter' => '`columnTypeId` != "0"', 'mode' => 'readonly']);
        field('changeLog', 'was', ['title' => 'Было', 'columnTypeId' => 'TEXT', 'elementId' => 'html', 'mode' => 'readonly']);
        field('changeLog', 'now', ['title' => 'Стало', 'columnTypeId' => 'TEXT', 'elementId' => 'html', 'mode' => 'readonly']);
        field('changeLog', 'datetime', ['title' => 'Когда', 'columnTypeId' => 'DATETIME', 'elementId' => 'datetime',
            'defaultValue' => '0000-00-00 00:00:00', 'mode' => 'readonly']);
        field('changeLog', 'monthId', ['title' => 'Месяц', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'month', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        field('changeLog', 'changerType', ['title' => 'Тип автора', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'entity', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        field('changeLog', 'changerId', ['title' => 'Автор', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'satellite' => 'changerType', 'dependency' => 'e', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        field('changeLog', 'profileId', ['title' => 'Роль', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'profile', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        entity('changeLog', ['titleFieldId' => 'datetime']);

        /**
         * Setup `monthId` for existing `changelog` entries (nasled, vkenguru)
         */

        // Create `year` entries
        foreach(db()->query('
            SELECT DISTINCT YEAR(`datetime`) FROM `changeLog`
        ')->fetchAll(PDO::FETCH_COLUMN) as $year)
            Year::o($year);

        // Create `month` entries
        foreach (db()->query('
            SELECT DISTINCT DATE_FORMAT(`datetime`, "%Y-%m") AS `Ym` FROM `changeLog` ORDER BY `Ym`
        ')->fetchAll(PDO::FETCH_COLUMN) as $Ym)
            $monthA[$Ym] = Month::o($Ym)->id;

        // Setup `monthId` for `changeLog` entries with zero-value in `monthId` col
        foreach (db()->query('
            SELECT `id`, DATE_FORMAT(`datetime`, "%Y-%m") AS `Ym` FROM `changeLog` WHERE `monthId` = "0"
        ')->fetchAll() as $_) db()->query('
            UPDATE `changeLog` SET `monthId` = "' . $monthA[$_['Ym']] . '" WHERE `id` = "' . $_['id'] . '"
        ');

        // Exit
        die('ok');
    }

    public function othersAction() {

        // Add `spaceScheme` and `spaceFields` fields into `entity` entity
        field('entity', 'spaceScheme', [
            'title' => 'Паттерн комплекта календарных полей',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'defaultValue' => 'none',
            'relation' => 'enumset',
            'storeRelationAbility' => 'one',
        ]);
        enumset('entity', 'spaceScheme', 'none', ['title' => 'Нет']);
        enumset('entity', 'spaceScheme', 'date', ['title' => 'DATE']);
        enumset('entity', 'spaceScheme', 'datetime', ['title' => 'DATETIME']);
        enumset('entity', 'spaceScheme', 'date-time', ['title' => 'DATE, TIME']);
        enumset('entity', 'spaceScheme', 'date-timeId', ['title' => 'DATE, timeId']);
        enumset('entity', 'spaceScheme', 'date-dayQty', ['title' => 'DATE, dayQty']);
        enumset('entity', 'spaceScheme', 'datetime-minuteQty', ['title' => 'DATETIME, minuteQty']);
        enumset('entity', 'spaceScheme', 'date-time-minuteQty', ['title' => 'DATE, TIME, minuteQty']);
        enumset('entity', 'spaceScheme', 'date-timeId-minuteQty', ['title' => 'DATE, timeId, minuteQty']);
        enumset('entity', 'spaceScheme', 'date-timespan', ['title' => 'DATE, hh:mm-hh:mm']);
        field('entity', 'spaceFields', [
            'title' => 'Комплект календарных полей',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'relation' => 'field',
            'storeRelationAbility' => 'many',
            'filter' => '`entityId` = "<?=$this->id?>"',
        ]);

        // Add `consider` entity
        entity('consider', ['title' => 'Зависимость', 'system' => 'y']);
        field('consider', 'entityId', ['title' => 'Сущность', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'entity', 'storeRelationAbility' => 'one', 'mode' => 'hidden']);
        field('consider', 'fieldId', ['title' => 'Поле', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'field', 'storeRelationAbility' => 'one', 'mode' => 'readonly']);
        field('consider', 'consider', ['title' => 'От какого поля зависит', 'columnTypeId' => 'INT(11)',
            'elementId' => 'combo', 'relation' => 'field', 'satellite' => 'fieldId', 'dependency' => 'с',
            'storeRelationAbility' => 'one', 'alternative' => 'entityId', 'filter' => '`id` != "<?=$this->fieldId?>" AND `columnTypeId` != "0"',
            'satellitealias' => 'entityId', 'mode' => 'required']);
        field('consider', 'foreign', ['title' => 'Поле по ключу', 'columnTypeId' => 'INT(11)', 'elementId' => 'combo',
            'relation' => 'field', 'satellite' => 'consider', 'dependency' => 'с', 'storeRelationAbility' => 'one', 'alternative' => 'relation']);
        field('consider', 'title', ['title' => 'Auto title', 'columnTypeId' => 'VARCHAR(255)', 'elementId' => 'string', 'mode' => 'hidden']);
        entity('consider', ['titleFieldId' => 'consider']);

        // Add 'consider' section
        $_ = section('consider', ['sectionId' => 'fields', 'entityId' => 'consider', 'title' => 'Зависимости', 'type' => 's']);
        if ($_->affected('id')) $_->nested('grid')->delete();
        section2action('consider','index', ['profileIds' => 1]);
        section2action('consider','form', ['profileIds' => 1]);
        section2action('consider','save', ['profileIds' => 1]);
        section2action('consider','delete', ['profileIds' => 1]);
        grid('consider','consider', true);
        grid('consider','foreign', true);

        // Add menu-expand fields
        $_ = field('section', 'expand', ['title' => 'Разворачивать пункт меню', 'columnTypeId' => 'ENUM', 'elementId' => 'radio',
            'defaultValue' => 'all', 'relation' => 'enumset', 'storeRelationAbility' => 'one']);
        if ($_->affected('id')) $_->move(19)->move(-2);
        enumset('section', 'expand', 'all', ['title' => 'Всем пользователям']);
        enumset('section', 'expand', 'only', ['title' => 'Только выбранным']);
        enumset('section', 'expand', 'except', ['title' => 'Всем кроме выбранных']);
        enumset('section', 'expand', 'none', ['title' => 'Никому']);
        $_ = field('section', 'expandRoles', ['title' => 'Выбранные', 'columnTypeId' => 'VARCHAR(255)', 'elementId' => 'combo',
            'relation' => 'profile', 'storeRelationAbility' => 'many']);
        if ($_->affected('id')) $_->move(19)->move(-3);

        // Exit
        die('ok');
    }

    public function sectionRolesAction() {

        field('section', 'roleIds', [
            'title' => 'Доступ',
            'columnTypeId' => 'VARCHAR(255)',
            'elementId' => 'combo',
            'relation' => 'profile',
            'storeRelationAbility' => 'many',
            'mode' => 'hidden',
        ]);
        field('section', 'entityId', ['title' => 'Сущность']);
        filter('sections', 'roleIds', true);

        $sectionRs = m('Section')->all();
        $sectionRs->nested('section2action');
        foreach ($sectionRs as $sectionR) {
            $sectionR->roleIds = '';
            foreach ($sectionR->nested('section2action') as $section2actionR)
                foreach (ar($section2actionR->profileIds) as $roleId)
                    $sectionR->push('roleIds', $roleId);
            $sectionR->save();
        }
        enumset('grid', 'toggle', 'h', ['title' => 'Скрыт', 'color' => 'lightgray']);
        action('goto', ['title' => 'Перейти', 'type' => 's']);
        die('ok');
    }
}