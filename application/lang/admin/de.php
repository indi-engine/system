<?php
define('I_URI_ERROR_SECTION_FORMAT', 'Der Abschnittsname hat das falsche Format');
define('I_URI_ERROR_ACTION_FORMAT', 'Der Aktionsname hat das falsche Format');
define('I_URI_ERROR_ID_FORMAT', 'Der URI-Parameter „id“ sollte einen positiven ganzzahligen Wert haben');
define('I_URI_ERROR_CHUNK_FORMAT', 'Einer der URI-Teile hat ein ungültiges Format');

define('I_LOGIN_BOX_USERNAME', 'Nutzername');
define('I_LOGIN_BOX_PASSWORD', 'Passwort');
define('I_LOGIN_BOX_REMEMBER', 'Erinnern');
define('I_LOGIN_BOX_ENTER', 'Eingeben');
define('I_LOGIN_BOX_RESET', 'Zurücksetzen');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'Fehler');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'Benutzername nicht angegeben');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'Kennwort nicht angegeben');
define('I_LOGIN_BOX_LANGUAGE', 'Sprache');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'Dieses Konto existiert nicht');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'Falsches Passwort');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'Dieses Konto ist deaktiviert');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'Dieses Konto ist vom Typ, der ausgeschaltet ist');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'Es gibt noch keine Abschnitte, auf die über dieses Konto zugegriffen werden kann');

define('I_THROW_OUT_ACCOUNT_DELETED', 'Ihr Konto wurde gerade gelöscht');
define('I_THROW_OUT_PASSWORD_CHANGED', 'Ihr Passwort wurde gerade geändert');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'Ihr Konto wurde gerade deaktiviert');
define('I_THROW_OUT_ROLE_IS_OFF', 'Ihr Konto ist vom Typ, der gerade deaktiviert wurde');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'Derzeit sind für Sie keine Abschnitte mehr zugänglich');
define('I_THROW_OUT_SESSION_EXPIRED', 'Ihre Sitzung ist nicht mehr verfügbar. Möchten Sie sich erneut anmelden?');

define('I_WS_CONNECTED', 'Verbunden in %s ms');
define('I_WS_DISCONNECTED', 'Getrennt');
define('I_WS_RECONNECTING', 'Verbindung wird wiederhergestellt...');
define('I_WS_RECONNECTED', 'In %s ms wieder verbunden');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'Ein solcher Abschnitt existiert nicht');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'Abschnitt ist ausgeschaltet');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'Eine solche Aktion gibt es nicht');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'Diese Aktion ist ausgeschaltet');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'Diese Aktion existiert in diesem Abschnitt nicht');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'Diese Aktion ist in diesem Abschnitt ausgeschaltet');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'Sie haben keine Rechte an dieser Aktion in diesem Abschnitt');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'Einer der übergeordneten Abschnitte für den aktuellen Abschnitt – ist ausgeschaltet');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'Das Hinzufügen von Zeilen ist in diesem Abschnitt eingeschränkt');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'Zeile mit einer solchen ID existiert in diesem Abschnitt nicht');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'Die Aktion „%s“ ist zugänglich, aber die aktuellen Umstände erlauben ihre Ausführung nicht.');

define('I_DOWNLOAD_ERROR_NO_ID', 'Der Zeilenbezeichner wurde entweder nicht angegeben oder ist keine Zahl.');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'Der Feldbezeichner wurde entweder nicht angegeben oder ist keine Zahl');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'Kein Feld mit solcher Kennung');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'Dieses Feld befasst sich nicht mit Dateien');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'Keine Zeile mit dieser Kennung');
define('I_DOWNLOAD_ERROR_NO_FILE', 'Für diese Zeile wurde in diesem Feld keine Datei hochgeladen');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'Abrufen der Dateiinformationen ist fehlgeschlagen');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'Leerer Titel für Standardwert „%s“');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'Der Wert „%s“ ist bereits in der Liste der zulässigen Werte vorhanden');
define('I_ENUMSET_ERROR_VALUE_LAST', 'Der Wert „%s“ ist der letzte verbleibende mögliche Wert und kann nicht gelöscht werden.');

define('I_YES', 'Ja');
define('I_NO', 'NEIN');
define('I_ERROR', 'Fehler');
define('I_MSG', 'Nachricht');
define('I_OR', 'oder');
define('I_AND', 'Und');
define('I_BE', 'Sei');
define('I_FILE', 'Datei');
define('I_SHOULD', 'sollen');

define('I_HOME', 'Heim');
define('I_LOGOUT', 'Ausloggen');
define('I_MENU', 'Speisekarte');
define('I_CREATE', 'Erstelle neu');
define('I_BACK', 'Zurück');
define('I_SAVE', 'Speichern');
define('I_CLOSE', 'Schließen');
define('I_TOTAL', 'Gesamt');
define('I_TOGGLE_Y', 'Anmachen');
define('I_TOGGLE_N', 'Abschalten');
define('I_EXPORT_EXCEL', 'Als Excel-Tabelle exportieren');
define('I_EXPORT_PDF', 'Als PDF-Dokument exportieren');
define('I_NAVTO_ROWSET', 'Zurück zum Rowset');
define('I_NAVTO_ID', 'Gehe zu Zeile nach ID');
define('I_NAVTO_RELOAD', 'Aktualisierung');
define('I_AUTOSAVE', 'Automatisches Speichern vor „Gehe zu“');
define('I_NAVTO_RESET', 'Rollback-Änderungen');
define('I_NAVTO_PREV', 'Zur vorherigen Zeile gehen');
define('I_NAVTO_SIBLING', 'Gehe zu einer beliebigen anderen Zeile');
define('I_NAVTO_NEXT', 'Zur nächsten Zeile gehen');
define('I_NAVTO_CREATE', 'Gehe zu „Neue Zeilenerstellung“');
define('I_NAVTO_NESTED', 'Gehe zu verschachtelten Objekten');
define('I_NAVTO_ROWINDEX', 'Gehe zu Zeile mit #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'Feld "%s" ist erforderlich');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'Der Wert des Felds „%s“ kann kein Objekt sein');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'Der Wert des Felds „%s“ kann kein Array sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'Der Wert "%s" des Feldes "%s" darf nicht größer als eine 11-stellige Dezimalzahl sein.');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'Der Wert „%s“ des Felds „%s“ liegt nicht in der Liste der zulässigen Werte');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'Feld „%s“ enthält unzulässige Werte: „%s“');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'Der Wert „%s“ des Felds „%s“ enthält mindestens ein Element, das keine von Null verschiedene Dezimalzahl ist.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'Der Wert „%s“ des Felds „%s“ sollte „1“ oder „0“ sein.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'Der Wert „%s“ des Felds „%s“ sollte eine Farbe in den Formaten #rrggbb oder hue#rrggbb sein.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'Der Wert „%s“ des Felds „%s“ ist kein Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'Der Wert „%s“ des Felds „%s“ ist ein ungültiges Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'Der Wert „%s“ des Felds „%s“ sollte eine Zeit im Format HH:MM:SS sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'Der Wert „%s“ des Felds „%s“ ist keine gültige Zeit');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'Wert „%s“, im Feld „%s“ als Datum angegeben – ist kein Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'Das im Feld „%s“ angegebene Datum „%s“ ist kein gültiges Datum.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'Wert „%s“, im Feld „%s“ als Zeit angegeben – sollte eine Zeit im Format HH:MM:SS sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'Die im Feld „%s“ angegebene Zeit „%s“ ist keine gültige Zeit.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'Der Wert „%s“ des Felds „%s“ muss eine Zahl mit 4 oder weniger Ziffern im ganzzahligen Teil sein, optional mit vorangestelltem „-“-Zeichen, und 2 oder weniger/keinen Ziffern im Dezimalteil.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'Der Wert „%s“ des Felds „%s“ muss eine Zahl mit 8 oder weniger Ziffern im ganzzahligen Teil sein, optional mit vorangestelltem „-“-Zeichen, und 2 oder weniger/keinen Ziffern im Dezimalteil.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'Der Wert „%s“ des Felds „%s“ muss eine Zahl mit 10 oder weniger Ziffern im ganzzahligen Teil sein, optional mit vorangestelltem „-“-Zeichen, und 3 oder weniger/keinen Ziffern im Dezimalteil.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'Der Wert "%s" des Feldes "%s" sollte ein Jahr im Format JJJJ sein.');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'Nichts zu speichern');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'Sie haben keine Änderungen vorgenommen');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'Die aktuelle Zeile kann im Feld „%s“ nicht als übergeordnete Zeile für sich selbst festgelegt werden.');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'Zeile mit der ID „%s“, angegeben im Feld „%s“, – existiert nicht und kann daher nicht als übergeordnete Zeile eingerichtet werden');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'Zeile „%s“, angegeben im Feld „%s“, ist eine untergeordnete/nachkommende Zeile für eine aktuelle Zeile „%s“, daher kann sie nicht als übergeordnete Zeile eingerichtet werden');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'Während Ihrer Anfrage wurde eine der Operationen auf den Eintrag vom Typ "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', ' - hat die folgenden Fehler zurückgegeben');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'Feld "%s" ist erforderlich');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'Der Wert „%s“ des Felds „%s“ wird bereits als Benutzername für ein anderes Konto verwendet');

define('I_ROWFILE_ERROR_MKDIR', 'Die rekursive Erstellung des Verzeichnisses "%s" im Pfad "%s" ist fehlgeschlagen, obwohl dieser Pfad beschreibbar ist.');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'Die rekursive Erstellung des Verzeichnisses „%s“ im Pfad „%s“ ist fehlgeschlagen, da dieser Pfad nicht beschreibbar ist.');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'Zielverzeichnis "%s" existiert, ist aber nicht beschreibbar');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'Es besteht keine Möglichkeit, mit Dateien nicht vorhandener Zeilen umzugehen.');

define('I_ROWM4D_NO_SUCH_FIELD', 'Das Feld „m4d“ existiert nicht innerhalb der Entität „%s“.');

define('I_UPLOAD_ERR_INI_SIZE', 'Die hochgeladene Datei im Feld „%s“ überschreitet die upload_max_filesize-Anweisung in php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'Die hochgeladene Datei im Feld "%s" überschreitet die angegebene MAX_FILE_SIZE-Anweisung ');
define('I_UPLOAD_ERR_PARTIAL', 'Die hochgeladene Datei im Feld "%s" wurde nur teilweise hochgeladen');
define('I_UPLOAD_ERR_NO_FILE', 'Im Feld „%s“ wurde keine Datei hochgeladen');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'Auf dem Server fehlt ein temporärer Ordner zum Speichern der im Feld „%s“ hochgeladenen Datei.');
define('I_UPLOAD_ERR_CANT_WRITE', 'Das Schreiben der im Feld „%s“ hochgeladenen Datei auf die Festplatte des Servers ist fehlgeschlagen');
define('I_UPLOAD_ERR_EXTENSION', 'Der Dateiupload im Feld „%s“ wurde durch eine der auf dem Server ausgeführten PHP-Erweiterungen gestoppt.');
define('I_UPLOAD_ERR_UNKNOWN', 'Der Dateiupload im Feld „%s“ ist aufgrund eines unbekannten Fehlers fehlgeschlagen');

define('I_UPLOAD_ERR_REQUIRED', 'Es ist noch keine Datei vorhanden, Sie sollten eine auswählen');
define('I_WGET_ERR_ZEROSIZE', 'Die Verwendung der Web-URL als Dateiquelle für das Feld „%s“ ist fehlgeschlagen, da die Datei die Größe Null hat.');

define('I_FORM_UPLOAD_SAVETOHDD', 'Herunterladen');
define('I_FORM_UPLOAD_ORIGINAL', 'Original zeigen');
define('I_FORM_UPLOAD_NOCHANGE', 'Keine Änderung');
define('I_FORM_UPLOAD_DELETE', 'Löschen');
define('I_FORM_UPLOAD_REPLACE', 'Ersetzen');
define('I_FORM_UPLOAD_REPLACE_WITH', 'mit');
define('I_FORM_UPLOAD_NOFILE', 'NEIN');
define('I_FORM_UPLOAD_BROWSE', 'Durchsuche');
define('I_FORM_UPLOAD_MODE_TIP', 'Verwenden Sie einen Weblink, um eine Datei auszuwählen');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'Ihre lokale PC-Datei.');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'Datei unter Weblink..');

define('I_FORM_UPLOAD_ASIMG', 'ein Bild');
define('I_FORM_UPLOAD_ASOFF', 'ein Office-Dokument');
define('I_FORM_UPLOAD_ASDRW', 'eine Zeichnung');
define('I_FORM_UPLOAD_ASARC', 'ein Archiv');
define('I_FORM_UPLOAD_ASAUD', 'ein Audio');
define('I_FORM_UPLOAD_OFEXT', 'haben Typ');
define('I_FORM_UPLOAD_INFMT', 'im Format');
define('I_FORM_UPLOAD_HSIZE', 'Größe haben');
define('I_FORM_UPLOAD_NOTGT', 'nicht größer als');
define('I_FORM_UPLOAD_NOTLT', 'nicht weniger als');
define('I_FORM_UPLOAD_FPREF', 'Foto %s');

define('I_FORM_DATETIME_HOURS', 'Std.');
define('I_FORM_DATETIME_MINUTES', 'Protokoll');
define('I_FORM_DATETIME_SECONDS', 'Sekunden');
define('I_OF', 'von');
define('I_COMBO_MISMATCH_MAXSELECTED', 'Die maximal zulässige Anzahl ausgewählter Optionen beträgt');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'Die Option „%s“ ist im Feld „%s“ nicht zur Auswahl verfügbar.');
define('I_COMBO_KEYWORD_NO_RESULTS', 'Mit diesem Stichwort wurde nichts gefunden');
define('I_COMBO_ODATA_FIELD404', 'Feld "%s" ist weder echtes Feld noch Pseudofeld');
define('I_COMBO_GROUPBY_NOGROUP', 'Gruppierung nicht festgelegt');
define('I_COMBO_WAND_TOOLTIP', 'Erstellen Sie eine neue Option in dieser Liste mit dem in dieses Feld eingegebenen Titel');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'Zeile wurde nicht gefunden');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'Der Umfang der verfügbaren Zeilen im aktuellen Abschnitt');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', ', in der Ansicht mit angewandten Suchoptionen -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', ' enthält keine Zeile mit einer solchen ID');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'Reihe #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'von ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'Zeile wurde nicht gefunden');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'Der Umfang der Zeilen, die im aktuellen Abschnitt verfügbar sind,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', ' in der Ansicht mit angewandten Suchoptionen');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', ' - enthält keine Zeile mit einem solchen Index, hat es aber vor kurzem getan');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'NEIN');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--Wählen--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'Suchen…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'Suche in allen Spalten');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'Unterabschnitte');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--Wählen--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'NEIN');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'Nachricht');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'Wählen Sie eine Zeile aus');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'Optionen');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'zwischen');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'Und');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'aus');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'bis');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'Ja');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'NEIN');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'Nichts muss geleert werden');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'Optionen sind bereits leer oder werden überhaupt nicht verwendet');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'Bestätigen');
define('I_ACTION_DELETE_CONFIRM_MSG', 'Sind Sie sicher, dass Sie löschen möchten');
define('I_ENTRY_TBQ', 'Eintrag,Einträge,Einträge');

define('I_SOUTH_PLACEHOLDER_TITLE', 'Der Inhalt dieser Registerkarte wird in einem separaten Fenster geöffnet');
define('I_SOUTH_PLACEHOLDER_GO', 'Gehe zu');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', ' das Fenster');
define('I_SOUTH_PLACEHOLDER_GET', 'Inhalte abrufen');
define('I_SOUTH_PLACEHOLDER_BACK', ' zurück hier');

define('I_DEMO_ACTION_OFF', 'Diese Aktion ist im Demo-Modus deaktiviert');

define('I_MCHECK_REQ', 'Feld "%s" - ist erforderlich');
define('I_MCHECK_REG', 'Wert „%s“ des Felds „%s“ – hat ein ungültiges Format');
define('I_MCHECK_KEY', 'Es wurde kein Objekt vom Typ „%s“ mit dem Schlüssel „%s“ gefunden.');
define('I_MCHECK_EQL', 'Falscher Wert');
define('I_MCHECK_DIS', 'Wert „%s“ des Felds „%s“ – befindet sich in der Liste der deaktivierten Werte');
define('I_MCHECK_UNQ', 'Wert "%s" des Feldes "%s" - ist nicht eindeutig. Er sollte eindeutig sein.');
define('I_JCHECK_REQ', 'Param "%s" - ist nicht angegeben');
define('I_JCHECK_REG', 'Wert „%s“ des Parameters „%s“ – hat ein ungültiges Format');
define('I_JCHECK_KEY', 'Es wurde kein Objekt vom Typ „%s“ mit dem Schlüssel „%s“ gefunden.');
define('I_JCHECK_EQL', 'Falscher Wert');
define('I_JCHECK_DIS', 'Wert „%s“ des Parameters „%s“ – befindet sich in der Liste der deaktivierten Werte');
define('I_JCHECK_UNQ', 'Der Wert „%s“ des Parameters „%s“ ist nicht eindeutig. Er sollte eindeutig sein.');

define('I_PRIVATE_DATA', '*private Daten*');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', 'gestern');
define('I_WHEN_TOD', 'Heute');
define('I_WHEN_TOM', 'morgen');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'An');
define('I_WHEN_WD_ON2', 'An');
define('I_WHEN_TM_AT', 'bei');

define('I_LANG_LAST', 'Es ist nicht erlaubt, den letzten verbleibenden „%s“-Eintrag zu löschen');
define('I_LANG_CURR', 'Es ist nicht erlaubt, die Übersetzung zu löschen, die als Ihre aktuelle Übersetzung verwendet wird');
define('I_LANG_FIELD_L10N_DENY', 'Lokalisierung kann für das Feld „%s“ nicht aktiviert werden');
define('I_LANG_QYQN_CONFIRM', 'Wenn Sie die Sprache „%s“ für den Bruchteil „%s“ in %s wünschen, drücken Sie „%s“. Wenn Sie es nur an den aktuellen Stand anpassen möchten, drücken Sie „%s“.');
define('I_LANG_QYQN_CONFIRM2', 'Für den Bruchteil „%s“ wird die Sprache „%s“ manuell als „%s“ markiert. Fortfahren?');
define('I_LANG_QYQN_SELECT', 'Ausgangssprache auswählen');
define('I_LANG_EXPORT_HEADER', 'Exportparameter auswählen');
define('I_LANG_IMPORT_HEADER', 'Importparameter auswählen');
define('I_LANG_NOT_SUPPORTED', 'Wird bisher nicht unterstützt');
define('I_LANG_SELECT_CURRENT', 'Aktuelle Sprache des Bruchteils „%s“ auswählen');
define('I_LANG_MIGRATE_META', 'Felder vorbereiten');
define('I_LANG_MIGRATE_DATA', 'Titel migrieren');
define('I_ADD', 'Hinzufügen');
define('I_DELETE', 'Löschen');
define('I_SECTION_CLONE_SELECT_PARENT', 'Wählen Sie den übergeordneten Abschnitt aus, dem Duplikate der ausgewählten Abschnitte untergeordnet sein sollen');

define('I_TILE_NOTHUMB', 'Kein Daumen');
define('I_TILE_NOFILE', 'Keine Datei');

define('I_CHANGELOG_FIELD', 'Was wurde geändert');
define('I_CHANGELOG_WAS', 'War');
define('I_CHANGELOG_NOW', 'Wurde');

define('I_WEEK', 'Woche');
define('I_TODAY', 'Heute');

define('I_PRINT', 'Drucken');

define('I_NUM2STR_ZERO', 'null');
define('I_NUM2STR_1TO9', 'eins, zwei, drei, vier, fünf, sechs, sieben, acht, neun');
define('I_NUM2STR_1TO9_2', 'eins, zwei, drei, vier, fünf, sechs, sieben, acht, neun');
define('I_NUM2STR_10TO19', 'zehn, elf, zwölf, dreizehn, vierzehn, fünfzehn, sechzehn, siebzehn, achtzehn, neunzehn');
define('I_NUM2STR_20TO90', 'zwanzig, dreißig, vierzig, fünfzig, sechzig, siebzig, achtzig, neunzig');
define('I_NUM2STR_100TO900', 'einhundert, zweihundert, dreihundert, vierhundert, fünfhundert, sechshundert, siebenhundert, achthundert, neunhundert');
define('I_NUM2STR_TBQ_KOP', 'Kopeke, Kopeken, Kopeken');
define('I_NUM2STR_TBQ_RUB', 'Rubel, Rubel, Rubel');
define('I_NUM2STR_TBQ_THD', 'Tausend, Tausend, Tausend');
define('I_NUM2STR_TBQ_MLN', 'Millionen, Millionen, Millionen');
define('I_NUM2STR_TBQ_BLN', 'Milliarden, Milliarde, Milliarde');

define('I_AGO_SECONDS', 'Sekunde,Sekunden');
define('I_AGO_MINUTES', 'Minute,Minuten');
define('I_AGO_HOURS', 'Stunde, Stunden');
define('I_AGO_DAYS', 'Tag, Tage');
define('I_AGO_WEEKS', 'Woche, Wochen');
define('I_AGO_MONTHS', 'Monat, Monate');
define('I_AGO_YEARS', 'Jahr, Jahre');

define('I_PANEL_GRID', 'Netz');
define('I_PANEL_PLAN', 'Kalender');
define('I_PANEL_TILE', 'Galerie');

define('I_ENT_AUTHOR_SPAN', 'Erstellt');
define('I_ENT_AUTHOR_ROLE', 'Rolle');
define('I_ENT_AUTHOR_USER', 'Benutzer');
define('I_ENT_AUTHOR_TIME', 'Terminzeit');
define('I_ENT_TOGGLE', 'Umschalten');
define('I_ENT_TOGGLE_Y', 'Eingeschaltet');
define('I_ENT_TOGGLE_N', 'Ausgeschaltet');
define('I_ENT_EXTENDS_OTHER', 'Datei mit PHP-Modell existiert, aber dort ist %s als übergeordnete Klasse angegeben');

define('I_SELECT_CFGFIELD', 'Wählen Sie den Konfigurationsparameter aus, den Sie für dieses Feld hinzufügen möchten');
define('I_SELECT_PLEASE', 'Bitte auswählen');
define('I_L10N_TOGGLE_ACTION_Y', 'Wenn Sie %s-Lokalisierung für Aktion „%s“ wünschen, drücken Sie „%s“.');
define('I_L10N_TOGGLE_MATCH', 'Andernfalls drücken Sie „%s“, wenn Sie den Wert des Lokalisierungsstatus nur explizit angeben müssen, damit er der aktuellen Realität entspricht.');
define('I_L10N_TOGGLE_ACTION_EXPL', 'Für die Aktion „%s“ wird der Lokalisierungsstatus explizit auf „%s“ gesetzt. Fortfahren?');
define('I_L10N_TOGGLE_ACTION_LANG_CURR', 'Aktuelle Sprache der Aktion „%s“ auswählen');
define('I_L10N_TOGGLE_ACTION_LANG_KEPT', 'Wählen Sie die Sprache aus, die für die Aktion „%s“ beibehalten werden soll.');

define('I_L10N_TOOGLE_FIELD_DENIED', 'Es ist nicht erlaubt, die Lokalisierung für abhängige Felder ein-/auszuschalten');
define('I_L10N_TOGGLE_FIELD_Y', 'Wenn Sie eine %s-Lokalisierung für das Feld „%s“ wünschen, drücken Sie „%s“.');
define('I_L10N_TOGGLE_FIELD_EXPL', 'Für das Feld „%s“ wird der Lokalisierungsstatus explizit auf „%s“ gesetzt. Fortfahren?');
define('I_L10N_TOGGLE_FIELD_LANG_CURR', 'Aktuelle Sprache des Felds „%s“ auswählen');
define('I_L10N_TOGGLE_FIELD_LANG_KEPT', 'Wählen Sie die Sprache aus, die für das Feld „%s“ beibehalten werden soll.');

define('I_GRID_COLOR_BREAK_INCOMPAT', 'Diese Funktion ist nur für numerische Spalten anwendbar');
define('I_REALTIME_CONTEXT_AUTODELETE_ONLY', 'Einträge vom Typ „Kontext“ können nicht manuell gelöscht werden.');
define('I_ONDELETE_RESTRICT', 'Die Löschung ist aufgrund der für das Feld „%s“ in der Entität „%s“ (`%s`.`%s`) konfigurierten Regel ON DELETE RESTRICT eingeschränkt, da mindestens ein Datensatz dieser Entität einen Wert in diesem Feld hat, der eine direkte oder kaskadierende Referenz zu dem Datensatz ist, den Sie löschen möchten.');

define('I_LANG_WORD_DETECT_ONLY', 'Vorabkontrolle durchführen?');
define('I_SECTION_ROWSET_MIN_CONF', 'Zur Aktivierung des Panels %s geben Sie bitte die folgende Mindestkonfiguration an:');
define('I_SECTION_CONF_SETUP', 'Konfiguration wird angewendet..');
define('I_SECTION_CONF_SETUP_DONE', 'Konfiguration wurde erfolgreich angewendet');
define('I_NOTICE_HIDE_ALL', 'Versteck alles');
define('I_RECORD_DELETED', 'Dieser Datensatz wurde gelöscht');

define('I_FILE_EXISTS', 'Datei existiert bereits: %s');
define('I_FILE_CREATED', 'Erstellte Datei: %s');
define('I_CLASS_404', 'Klasse nicht gefunden %s');
define('I_FORMAT_HIS', 'Der Wert "%s" des Arguments %s sollte eine Zeit im Format hh:mm:ss sein.');
define('I_GAPI_RESPONSE', 'Google Cloud Translate API-Antwort: %s');
define('I_GAPI_KEY_REQUIRED', 'Bitte geben Sie hier den API-Schlüssel an.');
define('I_QUEUE_STARTED', 'gestartet');
define('I_QUEUE_RESUMED', 'wieder aufgenommen');
define('I_QUEUE_COMPLETED', 'vollendet');
define('I_PLAN_ITEM_LOAD_FAIL', '%s %s konnte nicht in den Zeitplan geladen werden');
define('I_SECTIONS_TPLCTLR_404', 'Keine Template-Controller-Datei gefunden');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_JS', 'Datei für JS-Controller vorhanden, aber die dort angegebene übergeordnete Klasse ist %s');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_PHP', 'Datei für PHP-Controller existiert, aber die dort angegebene übergeordnete Klasse ist %s');
define('I_SECTIONS_CTLR_PARENT_404', 'Name der übergeordneten Klasse kann in der JS-Controller-Datei nicht gefunden werden');
define('I_SECTIONS_CTLR_EMPTY_JS', 'Die Datei für den JS-Controller ist leer');
define('I_ENTITIES_TPLMDL_404', 'Keine Vorlagenmodelldatei gefunden');
define('I_ENTITIES_TPLMDLROW_404', 'Keine Vorlagendatei für die Zeilenklasse des Modells gefunden');
define('I_FILTERS_FEATURE_UNAPPLICABLE', 'Diese Funktion ist nur für Filter anwendbar, hinter denen Fremdschlüsselfelder stehen');
define('I_LANG_NEW_QTY', 'Anzahl der von Google unterstützten neuen Sprachen: %s');
define('I_SCNACS_TPL_404', 'Aktionsvorlagendatei fehlt: %s');
define('I_MDL_CHLOG_NO_REVERT_1', 'Für schreibgeschützte oder ausgeblendete Felder ist die Wiederherstellung deaktiviert.');
define('I_MDL_CHLOG_NO_REVERT_2', 'Zurücksetzen ist nur für Felder der Typen %s und %s aktiviert.');
define('I_MDL_CHLOG_NO_REVERT_3', 'Für Fremdschlüsselfelder ist das Zurücksetzen deaktiviert.');
define('I_MDL_GRID_PARENT_GROUP_DIFFERS', 'Der übergeordnete Eintrag befindet sich in einer anderen Gruppe.');
define('I_EXPORT_CERTAIN', 'Wenn nur bestimmte Felder exportiert werden sollen, wählen Sie bitte');
define('I_SCHED_UNABLE_SET_REST', 'Es ist nicht möglich, innerhalb des Zeitplans Zeiträume außerhalb der Arbeitszeit zu schaffen');
define('I_MDL_ADMIN_VK_1', 'Die Seitenadresse sollte mit https://vk.com/ beginnen.');
define('I_MDL_ADMIN_VK_2', 'Diese Seite existiert nicht in VK');
define('I_MDL_ADMIN_VK_3', 'Diese Seite in VK ist keine Benutzerseite');

define('I_FIELD_DRAG_CLICK', 'Zum Neuordnen ziehen / Zum Anzeigen der Details klicken');
define('I_ENTITY_CLICK', 'Zum Anzeigen der Details klicken');
define('I_ACTION_FORM', 'Einzelheiten');
