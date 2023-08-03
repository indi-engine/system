<?php
define('I_URI_ERROR_SECTION_FORMAT', 'Имя раздела имеет неправильный формат');
define('I_URI_ERROR_ACTION_FORMAT', 'Имя действия имеет неправильный формат');
define('I_URI_ERROR_ID_FORMAT', 'Параметр \'id\' должен быть имеет целым положительным числом');
define('I_URI_ERROR_CHUNK_FORMAT', 'Одна из частей URI имеет неправильный формат');

define('I_LOGIN_BOX_USERNAME', 'Пользователь');
define('I_LOGIN_BOX_PASSWORD', 'Пароль');
define('I_LOGIN_BOX_REMEMBER', 'Запомнить');
define('I_LOGIN_BOX_ENTER', 'Вход');
define('I_LOGIN_BOX_RESET', 'Сброс');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'Ошибка');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'Имя пользователя не указано');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'Пароль не указан');
define('I_LOGIN_BOX_LANGUAGE', 'Язык');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'Нет такого аккаунта');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'Вы ввели неправильный пароль');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'Данный аккаунт отключен');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'Тип данного аккаунта отключен');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'В системе пока нет ни одного раздела, доступного этой для учетной записи');

define('I_THROW_OUT_ACCOUNT_DELETED', 'Ваш аккаунт только что был удален');
define('I_THROW_OUT_PASSWORD_CHANGED', 'Ваш пароль только что был изменен');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'Ваш аккаунт только что был отключен');
define('I_THROW_OUT_ROLE_IS_OFF', 'Тип вашего аккаунта только что был отключен');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'В системе не осталось ни одного доступного для вас раздела');
define('I_THROW_OUT_SESSION_EXPIRED', 'Ваш сеанс более недоступен, требуется повторный вход в систему. Продолжить?');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'Нет такого раздела');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'Этот раздел выключен');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'Нет такого действия');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'Это действие выключено');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'Нет такого действия в этом разделе');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'Это действие выключено в этом разделе');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'У вас нет прав на это действие в этом разделе');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'Один из вышестоящих разделов для этого раздела отключен');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'Право на создание записей недоступно в этом разделе');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'Нет записи с таким id в этом разделе');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'Действие "%s" доступно, но не в текущих обстоятельствах');

define('I_DOWNLOAD_ERROR_NO_ID', 'Идентификатор объекта или не указан, или не является числом');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'Идентификатор поля или не указан, или не является числом');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'Нет поля с таким идентификатором');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'Поле с этим идентификатором не работает с файлами');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'Нет объекта с таким идентификатором');
define('I_DOWNLOAD_ERROR_NO_FILE', 'Файл, загруженный в указанное поле для указанного объекта - не существует');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'Не удалось получить сведения о файле');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'Заголовок для значения по умолчанию \'%s\'');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'Значение "%s" уже присутствует в списке возможных значений');
define('I_ENUMSET_ERROR_VALUE_LAST', 'Значение "%s" - последнее оставшееся значение в списке возможных, и поэтому не может быть удалено');

define('I_YES', 'Да');
define('I_NO', 'Нет');
define('I_ERROR', 'Ошибка');
define('I_MSG', 'Сообщение');
define('I_OR', 'или');
define('I_AND', 'и');
define('I_BE', 'быть');
define('I_FILE', 'Файл');
define('I_SHOULD', 'должен');

define('I_HOME', 'Начало');
define('I_LOGOUT', 'Выход');
define('I_MENU', 'Меню');
define('I_CREATE', 'Создать новую запись');
define('I_BACK', 'Вернуться');
define('I_SAVE', 'Сохранить');
define('I_CLOSE', 'Закрыть');
define('I_TOTAL', 'Всего');
define('I_TOGGLE_Y', 'Включить');
define('I_TOGGLE_N', 'Выключить');
define('I_EXPORT_EXCEL', 'Экспортировать в Excel');
define('I_EXPORT_PDF', 'Экспортировать в PDF');
define('I_NAVTO_ROWSET', 'Вернуться к списку');
define('I_NAVTO_ID', 'Перейти к записи по ID');
define('I_NAVTO_RELOAD', 'Обновить');
define('I_AUTOSAVE', 'Автосохранять перед переходами');
define('I_NAVTO_RESET', 'Отменить изменения');
define('I_NAVTO_PREV', 'Перейти к предыдущей записи');
define('I_NAVTO_SIBLING', 'Перейти к любой другой записи');
define('I_NAVTO_NEXT', 'Перейти к следующей записи');
define('I_NAVTO_CREATE', 'Перейти к созданию новой записи');
define('I_NAVTO_NESTED', 'Перейти к списку вложенных записей');
define('I_NAVTO_ROWINDEX', 'Перейти к записи #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'Поле "%s" обязательно для заполнения');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'Значением поля "%s" не может быть объект');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'Значением поля "%s" не может быть массив');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'Значение "%s" поля "%s" должно быть целым числом, имеющим не более 11 разрядов');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'Значение "%s" поля "%s" отсутствует в списке допустимых значений');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'В поле "%s" присутствуют недопустимые значения: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'Значение "%s" поля "%s" содержит как минимум один элемент не являющийся ненулевым целым числом');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'Значение "%s" поля "%s" должно быть "1" или "0"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'Значение "%s" поля "%s" не является цветом в форматах #rrggbb или hue#rrggbb');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'Значение "%s" поля "%s" не является датой');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'Значение "%s" поля "%s" не является корректной датой');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'Значение "%s" поля "%s" не является временем в формате ЧЧ:ММ:СС');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'Время "%s" поля "%s" не является корректным временем');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'Значение "%s", указанное в поле "%s" в качестве даты - не является датой');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'Дата "%s", указанная в поле "%s" - должна быть корректной');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'Значение "%s", указанное в поле "%s" в качестве времени - не является временем');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'Время "%s", указанное в поле "%s" - должно быть корректным');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'Значение "%s" поля "%s" должно быть числом имеющим не более 5 разрядов в целочисленной части, и не более 2-х - в дробной');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'Значение "%s" поля "%s" должно быть числом имеющим не более 8 разрядов в целочисленной части, и не более 2-х - в дробной');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'Значение "%s" поля "%s" должно быть числом имеющим не более 10 разрядов в целочисленной части, возможно с указанием знака "-", и не более 3-х - в дробной');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'Значение "%s" поля "%s" не является годом в формате ГГГГ');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'Нечего сохранять');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'Вы пока не сделали никаких изменений');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'Текущая запись не может быть указана как родительская для самой себя в поле "%s"');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'Запись с идентификатором "%s", указанным в поле "%s", - не существует, и поэтому не может быть выбрана в качестве родительской');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'Запись "%s", указанная в поле "%s", является дочерней/подчиненной по отношению к текущей записи "%s", и поэтому не может быть выбрана в качестве родительской');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'При выполнении вашего запроса, одна из автоматически производимых операций, в частности над записью типа "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', ' - выдала следующие ошибки');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'Поле "%s" обязательно для заполнения');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'Значение "%s" указанное в поле "%s" уже задействовано в качестве имени пользователя в другой учетной записи');

define('I_ROWFILE_ERROR_MKDIR', 'Создание директории "%s" в папке "%s" не удалось, несмотря на то что папка доступна для записи');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'Создание директории "%s" в папке "%s" не удалось, так как эта папка недоступна для записи');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'Директория "%s", необходимая для загрузки, - существует, но недоступна для записи');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'Нельзя работать с файлами, относящимися к несуществующим записям');

define('I_ROWM4D_NO_SUCH_FIELD', 'Поле `m4d` отсутствует в структуре сущности "%s"');

define('I_UPLOAD_ERR_INI_SIZE', 'Размер файла, выбранного для загрузки в поле "%s", превысил максимально допустимый размер, заданный директивой upload_max_filesize конфигурационного файла php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'Размер файла, выбанного для загрузки в поле "%s" превысил значение MAX_FILE_SIZE, указанное в HTML-форме');
define('I_UPLOAD_ERR_PARTIAL', 'Файл, выбранный для загрузки в поле "%s" был получен только частично');
define('I_UPLOAD_ERR_NO_FILE', 'Файл, выбранный в поле "%s" -  не был загружен');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'На сервере отсутствует временная папка для загрузки файла из поля "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'Файл, выбранный для загрузки в поле "%s", не удалось записать на жесткий диск сервера');
define('I_UPLOAD_ERR_EXTENSION', 'Одно из PHP-расширений, работающих на сервере, остановило загрузку файла из поля "%s"');
define('I_UPLOAD_ERR_UNKNOWN', 'Загрузка файла в поле "%s" не удалась из-за неизвестной ошибки');

define('I_UPLOAD_ERR_REQUIRED', 'Вы должны выбрать файл');
define('I_WGET_ERR_ZEROSIZE', 'Загрузка файла в поле "%s" с использованием веб-ссылки не удалась, так как этот файл пустой');

define('I_FORM_UPLOAD_SAVETOHDD', 'Сохранить на диск');
define('I_FORM_UPLOAD_ORIGINAL', 'Показать оригинал');
define('I_FORM_UPLOAD_NOCHANGE', 'Оставить');
define('I_FORM_UPLOAD_DELETE', 'Удалить');
define('I_FORM_UPLOAD_REPLACE', 'Заменить');
define('I_FORM_UPLOAD_REPLACE_WITH', 'на');
define('I_FORM_UPLOAD_NOFILE', 'Отсутствует');
define('I_FORM_UPLOAD_BROWSE', 'Выбрать');
define('I_FORM_UPLOAD_MODE_TIP', 'Загрузить по веб-ссылке');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'файл с вашего ПК..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'файл по веб-ссылке..');

define('I_FORM_UPLOAD_ASIMG', 'изображением');
define('I_FORM_UPLOAD_ASOFF', 'документом');
define('I_FORM_UPLOAD_ASDRW', 'графическим макетом');
define('I_FORM_UPLOAD_ASARC', 'архивом');
define('I_FORM_UPLOAD_OFEXT', 'иметь расширение');
define('I_FORM_UPLOAD_INFMT', 'в формате');
define('I_FORM_UPLOAD_HSIZE', 'иметь размер');
define('I_FORM_UPLOAD_NOTGT', 'не более');
define('I_FORM_UPLOAD_NOTLT', 'не менее');
define('I_FORM_UPLOAD_FPREF', 'Фотография %s');

define('I_FORM_DATETIME_HOURS', 'часов');
define('I_FORM_DATETIME_MINUTES', 'минут');
define('I_FORM_DATETIME_SECONDS', 'секунд');
define('I_COMBO_OF', 'из');
define('I_COMBO_MISMATCH_MAXSELECTED', 'Максимальное количество выбранных опций -');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'Значение "%s" недоступно для выбора в поле "%s"');
define('I_COMBO_KEYWORD_NO_RESULTS', 'Ничего не найдено');
define('I_COMBO_ODATA_FIELD404', 'Поле "%s" не является ни реальным полем, ни псевдо-полем');
define('I_COMBO_GROUPBY_NOGROUP', 'Принадлежность не указана');
define('I_COMBO_WAND_TOOLTIP', 'Создать новую опцию в этом выпадающем списке<br> используя наименование, указанное в этом поле');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'Запись не найдена');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'Среди набора записей, доступных в рамках данного раздела,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', ' с учетом текущих параметров поиска');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', ' - нет записи с таким ID');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'Запись #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'из ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'Запись не найдена');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'Среди набора записей, доступных в рамках данного раздела,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', ' с учетом текущих параметров поиска');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', ' - нет записи с таким порядковым номером, но на момент загрузки формы она была');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'Отсутствуют');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--Выберите--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'Искать…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'Искать по всем столбцам');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'Подразделы');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--Выберите--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'Отсутствуют');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'Сообщение');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'Выберите строку');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'Фильтры');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'от');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'до');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'c');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'по');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'Да');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'Нет');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'Сброс всех фильтров');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'Фильтры уже сброшены или на текущий момент не используются вовсе');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'Подтверждение');
define('I_ACTION_DELETE_CONFIRM_MSG', 'Вы уверены что хотите удалить');
define('I_ENTRY_TBQ', 'записей,запись,записи');

define('I_SOUTH_PLACEHOLDER_TITLE', 'Содержимое этой панели открыто в отдельном окне');
define('I_SOUTH_PLACEHOLDER_GO', 'Перейти');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', ' к окну');
define('I_SOUTH_PLACEHOLDER_GET', 'Вернуть');
define('I_SOUTH_PLACEHOLDER_BACK', ' содержимое обратно сюда');

define('I_DEMO_ACTION_OFF', 'Это действие отключено в демо-режиме');

define('I_MCHECK_REQ', 'Поле "%s" - обязательно для заполнения');
define('I_MCHECK_REG', 'Значение "%s" поля "%s" - имеет неправильный формат');
define('I_MCHECK_KEY', 'Объект типа "%s" с идентификатором "%s" - не найден');
define('I_MCHECK_EQL', 'Неверное значение');
define('I_MCHECK_DIS', 'Значение "%s" поля "%s" - в списке недоступных значений');
define('I_MCHECK_UNQ', 'Значение "%s" поля "%s" - должно быть уникальным');
define('I_JCHECK_REQ', 'Параметр "%s" - является обязательным');
define('I_JCHECK_REG', 'Значение "%s" параметра "%s" - имеет неправильный формат');
define('I_JCHECK_KEY', 'Объект типа "%s" с идентификатором "%s" - не найден');
define('I_JCHECK_EQL', 'Неверное значение');
define('I_JCHECK_DIS', 'Значение "%s" параметра "%s" - в списке недоступных значений');
define('I_JCHECK_UNQ', 'Значение "%s" параметра "%s" - должно быть уникальным');

define('I_PRIVATE_DATA', '*данные скрыты*');

define('I_WHEN_DBY', 'позавчера');
define('I_WHEN_YST', 'вчера');
define('I_WHEN_TOD', 'сегодня');
define('I_WHEN_TOM', 'завтра');
define('I_WHEN_DAT', 'послезавтра');
define('I_WHEN_WD_ON1', 'в');
define('I_WHEN_WD_ON2', 'во');
define('I_WHEN_TM_AT', 'в');

define('I_LANG_LAST', 'Нельзя удалять последнюю запись типа "%s"');
define('I_LANG_CURR', 'Нельзя удалять язык, являющийся текущим языком системы');
define('I_LANG_FIELD_L10N_DENY', 'Нельзя включить локализацию для поля "%s"');
define('I_LANG_QYQN_CONFIRM', 'Если вы хотите %s язык "%s" для фракции "%s" нажмите "%s". Если просто нужно привести в соответствие с текущим состоянием - нажмите "%s"');
define('I_LANG_QYQN_CONFIRM2', 'Для фракции "%s" язык "%s" будет вручную помечен как "%s". Продолжить?');
define('I_LANG_QYQN_SELECT', 'Выберите исходный язык');
define('I_LANG_EXPORT_HEADER', 'Выберите параметры экспорта');
define('I_LANG_IMPORT_HEADER', 'Выберите параметры импорта');
define('I_LANG_NOT_SUPPORTED', 'Пока не поддерживается');
define('I_LANG_SELECT_CURRENT', 'Выберите текущий язык фракции "%s"');
define('I_LANG_MIGRATE_META', 'Подготовка полей');
define('I_LANG_MIGRATE_DATA', 'Миграция переводов');
define('I_ADD', 'Добавить');
define('I_DELETE', 'Удалить');
define('I_SECTION_CLONE_SELECT_PARENT', 'Выберите родительский раздел, в подчинении у которого должны быть<br>созданы дубликаты выбранных разделов');

define('I_TILE_NOTHUMB', 'Нет превью');
define('I_TILE_NOFILE', 'Нет файла');

define('I_CHANGELOG_FIELD', 'Что изменено');
define('I_CHANGELOG_WAS', 'Было');
define('I_CHANGELOG_NOW', 'Стало');

define('I_WEEK', 'Неделя');
define('I_TODAY', 'Сегодня');

define('I_PRINT', 'Распечатать');

define('I_NUM2STR_ZERO', 'ноль');
define('I_NUM2STR_1TO9', 'один,два,три,четыре,пять,шесть,семь,восемь,девять');
define('I_NUM2STR_1TO9_2', 'одна,две,три,четыре,пять,шесть,семь,восемь,девять');
define('I_NUM2STR_10TO19', 'десять,одиннадцать,двенадцать,тринадцать,четырнадцать,пятнадцать,шестнадцать,семнадцать,восемнадцать,девятнадцать');
define('I_NUM2STR_20TO90', 'двадцать,тридцать,сорок,пятьдесят,шестьдесят,семьдесят,восемьдесят,девяносто');
define('I_NUM2STR_100TO900', 'сто,двести,триста,четыреста,пятьсот,шестьсот,семьсот,восемьсот,девятьсот');
define('I_NUM2STR_TBQ_KOP', 'копейка,копейки,копеек');
define('I_NUM2STR_TBQ_RUB', 'рубль,рубля,рублей');
define('I_NUM2STR_TBQ_THD', 'тысяча,тысячи,тысяч');
define('I_NUM2STR_TBQ_MLN', 'миллион,миллиона,миллионов');
define('I_NUM2STR_TBQ_BLN', 'миллиард,милиарда,миллиардов');

define('I_ENT_AUTHOR_SPAN', 'Создание');
define('I_ENT_AUTHOR_ROLE', 'Роль');
define('I_ENT_AUTHOR_USER', 'Пользователь');
define('I_ENT_AUTHOR_TIME', 'Дата и время');
define('I_ENT_TOGGLE', 'Статус');
define('I_ENT_TOGGLE_Y', 'Включено');
define('I_ENT_TOGGLE_N', 'Выключено');
define('I_ENT_EXTENDS_OTHER', 'Файл php-модели существует, но в нем родительский класс указан как %s');

define('I_GAPI_KEY_REQUIRED', 'Вам необходимо получить ключ доступа для Google Cloud Translate API');
define('I_SELECT_CFGFIELD', 'Выберите параметр настройки элемента управления');
define('I_SELECT_PLEASE', 'Пожалуйста, выберите');
define('I_L10N_TOGGLE_ACTION_Y', 'Если вы хотите %s мультиязычность для действия "%s" нажмите "%s".');
define('I_L10N_TOGGLE_MATCH', 'Иначе если просто нужно привести в соответствие с текущим состоянием - нажмите "%s"');
define('I_L10N_TOGGLE_ACTION_EXPL', 'Для действия "%s" мультиязычность будет вручную указана как "%s". Продолжить?');
define('I_L10N_TOGGLE_ACTION_LANG_CURR', 'Выберите текущий язык действия "%s"');
define('I_L10N_TOGGLE_ACTION_LANG_KEPT', 'Выберите язык который должен остаться для действия "%s"');

define('I_L10N_TOOGLE_FIELD_DENIED', 'Нельзя вручную менять мультиязычность для зависимых полей');
define('I_L10N_TOGGLE_FIELD_Y', 'Если вы хотите %s мультиязычность для поля "%s" нажмите "%s".');
define('I_L10N_TOGGLE_FIELD_EXPL', 'Для поля "%s" мультиязычность будет вручную указана как "%s". Продолжить?');
define('I_L10N_TOGGLE_FIELD_LANG_CURR', 'Выберите текущий язык поля "%s"');
define('I_L10N_TOGGLE_FIELD_LANG_KEPT', 'Выберите язык который должен остаться в поле "%s"');

define('I_GRID_COLOR_BREAK_INCOMPAT', 'Эта опция доступна только для числовых столбцов');
define('I_REALTIME_CONTEXT_AUTODELETE_ONLY', 'Записи типа Контекст нельзя удалять вручную');
define('I_ONDELETE_RESTRICT', 'Удаление запрещено в связи с правилом ON DELETE RESTRICT, установленным для поля "%s" сущности "%s" (`%s`.`%s`), так как минимум один экземпляр этой сущности имеет в этом поле значение, напрямую или через цепочку связей являющееся ссылкой на удаляемую вами запись');

define('I_LANG_WORD_DETECT_ONLY', 'Сначала проверить?');
define('I_SECTION_ROWSET_MIN_CONF', 'Для активации панели %s требуется как минимум указать:');
define('I_SECTION_CONF_SETUP', 'Применение параметров..');
define('I_SECTION_CONF_SETUP_DONE', 'Применение параметров завершено');
define('I_NOTICE_HIDE_ALL', 'Скрыть все');

define('I_FILE_EXISTS', 'Файл уже существует: %s');
define('I_FILE_CREATED', 'Файл создан: %s');
define('I_CLASS_404', 'Не найден класс %s');
define('I_FORMAT_HIS', 'Значение "%s" аргумента %s должно быть временем в формате чч:мм:сс');
define('I_GAPI_RESPONSE', 'Ответ Google Cloud Translate API: %s');
define('I_GAPI_KEY_REQUIRED', 'Укажите API-ключ здесь..');
define('I_PLAN_ITEM_LOAD_FAIL', 'Не удалось загрузить %s %s в раcписание');
define('I_SECTIONS_TPLCTLR_404', 'Не найден файл шаблона контроллера');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_JS', 'Файл js-контроллера существует, но в нем родительский класс указан как %s');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_PHP', 'Файл php-контроллера существует, но в нем родительский класс указан как %s');
define('I_SECTIONS_CTLR_PARENT_404', 'В файле js-контроллера не удалось найти родительский класс');
define('I_SECTIONS_CTLR_EMPTY_JS', 'Файл js-контроллера пустой');
define('I_ENTITIES_TPLMDL_404', 'Не найден файл шаблона модели');
define('I_ENTITIES_TPLMDLROW_404', 'Не найден файл шаблона rowClass-а модели');
define('I_FILTERS_FEATURE_UNAPPLICABLE', 'Эта опция применима только для фильтров работающих с ключами');
define('I_LANG_NEW_QTY', 'Новых языков: %s');
define('I_SCNACS_TPL_404', 'Для этого действия отсутствует файл шаблона: <br> %s');
define('I_MDL_CHLOG_NO_REVERT_1', 'Восстановление недоступно для полей, заполняемых автоматически');
define('I_MDL_CHLOG_NO_REVERT_2', 'Восстановление доступно только для полей типа %s и %s');
define('I_MDL_CHLOG_NO_REVERT_3', 'Восстановление недоступно для полей, являющихся внешними ключами');
define('I_MDL_GRID_PARENT_GROUP_DIFFERS', 'Минимум одна из родительских записей находится в другой группe');
define('I_EXPORT_CERTAIN', 'Если нужно экспортировать только некоторые поля - выберите их');
define('I_SCHED_UNABLE_SET_REST', 'Не удается назначить отрезок нерабочего времени в расписании');
define('I_MDL_ADMIN_VK_1', 'Адрес страницы должен начинаться с https://vk.com/');
define('I_MDL_ADMIN_VK_2', 'Этой страницы ВКонтакте не существует');
define('I_MDL_ADMIN_VK_3', 'Эта страница ВКонтакте не является страницей пользователя');
