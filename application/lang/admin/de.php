<?php
define('I_URI_ERROR_SECTION_FORMAT', 'Der Abschnittsname hat ein falsches Format');
define('I_URI_ERROR_ACTION_FORMAT', 'Der Aktionsname hat ein falsches Format');
define('I_URI_ERROR_ID_FORMAT', 'Uri param \'id\' sollte einen positiven ganzzahligen Wert haben');
define('I_URI_ERROR_CHUNK_FORMAT', 'Einer der URI-Blöcke hat ein ungültiges Format');

define('I_LOGIN_BOX_USERNAME', 'Nutzername');
define('I_LOGIN_BOX_PASSWORD', 'Passwort');
define('I_LOGIN_BOX_REMEMBER', 'Merken');
define('I_LOGIN_BOX_ENTER', 'Eingeben');
define('I_LOGIN_BOX_RESET', 'Zurücksetzen');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'Error');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'Benutzername nicht angegeben');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'Passwort nicht angegeben');
define('I_LOGIN_BOX_LANGUAGE', 'Sprache');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'Ein solches Konto existiert nicht');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'Falsches Passwort');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'Dieses Konto ist ausgeschaltet');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'Dieses Konto ist vom Typ, der ausgeschaltet ist');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'Es gibt noch keine Abschnitte, auf die über dieses Konto zugegriffen werden kann');

define('I_THROW_OUT_ACCOUNT_DELETED', 'Ihr Konto wurde gerade gelöscht');
define('I_THROW_OUT_PASSWORD_CHANGED', 'Ihr Passwort wurde gerade geändert');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'Ihr Konto wurde gerade ausgeschaltet');
define('I_THROW_OUT_ROLE_IS_OFF', 'Ihr Konto ist vom Typ, der gerade ausgeschaltet wurde');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'Jetzt sind keine Abschnitte mehr für Sie zugänglich');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'Ein solcher Abschnitt existiert nicht');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'Abschnitt ist ausgeschaltet');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'Eine solche Aktion gibt es nicht');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'Diese Aktion ist ausgeschaltet');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'Diese Aktion ist in diesem Abschnitt nicht vorhanden');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'Diese Aktion ist in diesem Abschnitt deaktiviert');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'Sie haben keine Rechte an dieser Aktion in diesem Abschnitt');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'Einer der übergeordneten Abschnitte für den aktuellen Abschnitt - ist ausgeschaltet');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'Das Hinzufügen von Zeilen ist in diesem Abschnitt eingeschränkt');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'Eine Zeile mit einer solchen ID ist in diesem Abschnitt nicht vorhanden');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'Auf die Aktion "%s" kann zugegriffen werden, die aktuellen Umstände eignen sich jedoch nicht für die Ausführung');

define('I_DOWNLOAD_ERROR_NO_ID', 'Die Zeilenkennung ist entweder nicht angegeben oder keine Zahl');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'Die Feldkennung ist entweder nicht angegeben oder keine Zahl');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'Kein Feld mit einer solchen Kennung');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'Dieses Feld behandelt keine Dateien');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'Keine Zeile mit einer solchen Kennung');
define('I_DOWNLOAD_ERROR_NO_FILE', 'In diesem Feld ist keine Datei für diese Zeile hochgeladen');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'Das Abrufen von Dateiinformationen ist fehlgeschlagen');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'Leerer Titel für Standardwert \'%s\'');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'Der Wert "%s" ist bereits in der Liste der zulässigen Werte vorhanden');
define('I_ENUMSET_ERROR_VALUE_LAST', 'Der Wert "%s" ist der letzte mögliche Wert und kann nicht gelöscht werden');

define('I_YES', 'Ja');
define('I_NO', 'Nein');
define('I_ERROR', 'Error');
define('I_MSG', 'Botschaft');
define('I_OR', 'oder');
define('I_AND', 'und');
define('I_BE', 'Sein');
define('I_FILE', 'Datei');
define('I_SHOULD', 'sollte');

define('I_HOME', 'Zuhause');
define('I_LOGOUT', 'Ausloggen');
define('I_MENU', 'Speisekarte');
define('I_CREATE', 'Erstelle neu');
define('I_BACK', 'Zurück');
define('I_SAVE', 'speichern');
define('I_CLOSE', 'Schließen');
define('I_TOTAL', 'Gesamt');
define('I_EXPORT_EXCEL', 'Als Excel-Tabelle exportieren');
define('I_EXPORT_PDF', 'Als PDF-Dokument exportieren');
define('I_NAVTO_ROWSET', 'Gehen Sie zurück zum Rowset');
define('I_NAVTO_ID', 'Gehe zur Zeile nach ID');
define('I_NAVTO_RELOAD', 'Aktualisierung');
define('I_AUTOSAVE', 'Autosave vor goto');
define('I_NAVTO_RESET', 'Rollback-Änderungen');
define('I_NAVTO_PREV', 'Gehe zur vorherigen Zeile');
define('I_NAVTO_SIBLING', 'Gehe zu einer anderen Zeile');
define('I_NAVTO_NEXT', 'Gehe zur nächsten Reihe');
define('I_NAVTO_CREATE', 'Gehe zur neuen Zeilenerstellung');
define('I_NAVTO_NESTED', 'Gehe zu verschachtelten Objekten');
define('I_NAVTO_ROWINDEX', 'Gehe zur Reihe von #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'Feld "%s" ist erforderlich');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'Der Wert des Feldes "%s" kann kein Objekt sein');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'Der Wert des Feldes "%s" kann kein Array sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'Der Wert "%s" des Feldes "%s" sollte nicht größer als eine 11-stellige Dezimalstelle sein');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'Der Wert "%s" des Feldes "%s" befindet sich nicht in der Liste der zulässigen Werte');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'Das Feld "%s" enthält nicht zulässige Werte: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'Der Wert "%s" des Feldes "%s" enthält mindestens ein Element, das keine Dezimalstelle ungleich Null ist');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'Der Wert "%s" des Feldes "%s" sollte "1" oder "0" sein.');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'Der Wert "%s" des Feldes "%s" sollte eine Farbe in den Formaten #rrggbb oder hue # rrggbb sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'Der Wert "%s" des Feldes "%s" ist kein Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'Der Wert "%s" des Feldes "%s" ist ein ungültiges Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'Der Wert "%s" des Feldes "%s" sollte eine Zeit im Format HH:MM:SS sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'Der Wert "%s" des Feldes "%s" ist keine gültige Zeit');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'Der im Feld "%s" als Datum angegebene Wert "%s" ist kein Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'Datum "%s", im Feld "%s" angegeben - ist kein gültiges Datum');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'Der im Feld "%s" als Zeit angegebene Wert "%s" sollte eine Zeit im Format HH:MM:SS sein');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'Die im Feld "%s" angegebene Zeit "%s" ist keine gültige Zeit');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'Der Wert "%s" des Feldes "%s" sollte eine Zahl mit 4 oder weniger Ziffern im ganzzahligen Teil sein, optional mit dem Zeichen "-" vorangestellt, und 2 oder weniger / keine Ziffern im Bruchteil');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'Der Wert "%s" des Feldes "%s" sollte eine Zahl mit 8 oder weniger Ziffern im ganzzahligen Teil sein, optional mit dem Zeichen "-" vorangestellt, und 2 oder weniger / keine Ziffern im Bruchteil');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'Der Wert "%s" des Feldes "%s" sollte eine Zahl mit 10 oder weniger Ziffern im ganzzahligen Teil sein, optional mit dem Zeichen "-" vorangestellt, und 3 oder weniger / keine Ziffern im Bruchteil');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'Der Wert "%s" des Feldes "%s" sollte ein Jahr im Format JJJJ sein');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'Nichts zu speichern');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'Sie haben keine Änderungen vorgenommen');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'Die aktuelle Zeile kann im Feld "%s" nicht als übergeordnete Zeile für sich selbst festgelegt werden.');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'Zeile mit der ID "%s", angegeben im Feld "%s", - ist nicht vorhanden und kann daher nicht als übergeordnete Zeile eingerichtet werden');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'Zeile "%s", angegeben im Feld "%s", - ist eine untergeordnete / untergeordnete Zeile für eine aktuelle Zeile "%s", daher kann sie nicht als übergeordnete Zeile eingerichtet werden');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'Während Ihrer Anfrage wird eine der Operationen bei der Eingabe des Typs "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '- hat die folgenden Fehler zurückgegeben');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'Feld "%s" ist erforderlich');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'Der Wert "%s" des Feldes "%s" wird bereits als Benutzername für ein anderes Konto verwendet');

define('I_ROWFILE_ERROR_MKDIR', 'Die rekursive Erstellung des Verzeichnisses "%s" im Pfad "%s" ist fehlgeschlagen, obwohl dieser Pfad beschreibbar ist');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'Die rekursive Erstellung des Verzeichnisses "%s" im Pfad "%s" ist fehlgeschlagen, da dieser Pfad nicht beschreibbar ist');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'Das Zielverzeichnis "%s" ist vorhanden, aber nicht beschreibbar');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'Es gibt keine Möglichkeit, mit Dateien nicht vorhandener Zeilen umzugehen');

define('I_ROWM4D_NO_SUCH_FIELD', 'Das Feld "m4d" ist in der Entität "%s" nicht vorhanden');

define('I_UPLOAD_ERR_INI_SIZE', 'Die hochgeladene Datei im Feld "%s" überschreitet die Anweisung upload_max_filesize in php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'Die hochgeladene Datei im Feld "%s" überschreitet die angegebene Anweisung MAX_FILE_SIZE');
define('I_UPLOAD_ERR_PARTIAL', 'Die hochgeladene Datei im Feld "%s" wurde nur teilweise hochgeladen');
define('I_UPLOAD_ERR_NO_FILE', 'Im Feld "%s" wurde keine Datei hochgeladen.');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'Fehlender temporärer Ordner auf dem Server zum Speichern der Datei, hochgeladen in Feld "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'Fehler beim Schreiben der Datei, die im Feld "%s" hochgeladen wurde, auf die Festplatte des Servers');
define('I_UPLOAD_ERR_EXTENSION', 'Der Datei-Upload im Feld "%s" wurde von einer der PHP-Erweiterungen gestoppt, die auf dem Server ausgeführt werden');
define('I_UPLOAD_ERR_UNKNOWN', 'Das Hochladen der Datei im Feld "%s" ist aufgrund eines unbekannten Fehlers fehlgeschlagen');

define('I_UPLOAD_ERR_REQUIRED', 'Es gibt noch keine Datei, Sie sollten eine auswählen');
define('I_WGET_ERR_ZEROSIZE', 'Die Verwendung der Web-URL als Dateiquelle für das Feld "%s" ist fehlgeschlagen, da diese Datei die Größe Null hat');

define('I_FORM_UPLOAD_SAVETOHDD', 'Herunterladen');
define('I_FORM_UPLOAD_ORIGINAL', 'Original zeigen');
define('I_FORM_UPLOAD_NOCHANGE', 'Keine Änderung');
define('I_FORM_UPLOAD_DELETE', 'Löschen');
define('I_FORM_UPLOAD_REPLACE', 'Ersetzen');
define('I_FORM_UPLOAD_REPLACE_WITH', 'mit');
define('I_FORM_UPLOAD_NOFILE', 'Nein');
define('I_FORM_UPLOAD_BROWSE', 'Durchsuche');
define('I_FORM_UPLOAD_MODE_TIP', 'Verwenden Sie einen Weblink, um eine Datei auszuwählen');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'Ihre lokale PC-Datei ..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'Datei unter Weblink ..');

define('I_FORM_UPLOAD_ASIMG', 'ein Bild');
define('I_FORM_UPLOAD_ASOFF', 'ein Bürodokument');
define('I_FORM_UPLOAD_ASDRW', 'eine Zeichnung');
define('I_FORM_UPLOAD_ASARC', 'ein Archiv');
define('I_FORM_UPLOAD_OFEXT', 'Typ haben');
define('I_FORM_UPLOAD_INFMT', 'im Format');
define('I_FORM_UPLOAD_HSIZE', 'Größe haben');
define('I_FORM_UPLOAD_NOTGT', 'nicht größer als');
define('I_FORM_UPLOAD_NOTLT', 'nicht weniger als');
define('I_FORM_UPLOAD_FPREF', 'Foto %s');

define('I_FORM_DATETIME_HOURS', 'Std');
define('I_FORM_DATETIME_MINUTES', 'Protokoll');
define('I_FORM_DATETIME_SECONDS', 'Sekunden');
define('I_COMBO_OF', 'von');
define('I_COMBO_MISMATCH_MAXSELECTED', 'Die maximal zulässige Anzahl ausgewählter Optionen beträgt');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'Die Option "%s" ist für die Auswahl im Feld "%s" nicht verfügbar.');
define('I_COMBO_KEYWORD_NO_RESULTS', 'Mit diesem Schlüsselwort wurde nichts gefunden');
define('I_COMBO_ODATA_FIELD404', 'Das Feld "%s" ist weder ein reales Feld noch ein Pseudofeld');
define('I_COMBO_GROUPBY_NOGROUP', 'Gruppierung nicht festgelegt');
define('I_COMBO_WAND_TOOLTIP', 'Erstellen Sie eine neue Option in dieser Liste mit dem in dieses Feld eingegebenen Titel');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'Zeile wurde nicht gefunden');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'Der Umfang der verfügbaren Zeilen des aktuellen Abschnitts');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', ', in Sicht mit angewandten Suchoptionen -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', 'enthält keine Zeile mit einer solchen ID');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'Reihe #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'von');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'Zeile wurde nicht gefunden');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'Der Umfang der Zeilen, die im aktuellen Abschnitt verfügbar sind,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', 'in Ansicht mit angewendeten Suchoptionen');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '- enthält keine Zeile mit einem solchen Index, hat dies aber kürzlich getan');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'Nein');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--Wählen--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'Suche…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'Suche in allen Spalten');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'Unterabschnitte');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--Wählen--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'Nein');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'Botschaft');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'Wählen Sie eine Zeile aus');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'Optionen');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'zwischen');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'und');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'von');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'bis');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'Ja');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'Nein');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'Nichts zu leeren');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'Optionen sind bereits leer oder werden überhaupt nicht verwendet');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'Bestätigen');
define('I_ACTION_DELETE_CONFIRM_MSG', 'Sind Sie sicher, dass Sie löschen möchten');

define('I_SOUTH_PLACEHOLDER_TITLE', 'Der Inhalt dieser Registerkarte wird in einem separaten Fenster geöffnet');
define('I_SOUTH_PLACEHOLDER_GO', 'Gehe zu');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', 'das Fenster');
define('I_SOUTH_PLACEHOLDER_GET', 'Inhalte abrufen');
define('I_SOUTH_PLACEHOLDER_BACK', 'zurück hier');

define('I_DEMO_ACTION_OFF', 'Diese Aktion ist im Demo-Modus deaktiviert');

define('I_MCHECK_REQ', 'Feld "%s" - ist erforderlich');
define('I_MCHECK_REG', 'Der Wert "%s" des Feldes "%s" - hat ein ungültiges Format');
define('I_MCHECK_KEY', 'Mit dem Schlüssel "%s" wurde kein Objekt vom Typ "%s" gefunden.');
define('I_MCHECK_EQL', 'Falscher Wert');
define('I_MCHECK_DIS', 'Der Wert "%s" des Feldes "%s" - befindet sich in der Liste der deaktivierten Werte');
define('I_MCHECK_UNQ', 'Der Wert "%s" des Feldes "%s" - ist nicht eindeutig. Es sollte einzigartig sein.');
define('I_JCHECK_REQ', 'Parameter "%s" - wird nicht angegeben');
define('I_JCHECK_REG', 'Der Wert "%s" von Parameter "%s" - hat ein ungültiges Format');
define('I_JCHECK_KEY', 'Mit dem Schlüssel "%s" wurde kein Objekt vom Typ "%s" gefunden.');
define('I_JCHECK_EQL', 'Falscher Wert');
define('I_JCHECK_DIS', 'Der Wert "%s" von Parameter "%s" - befindet sich in der Liste der deaktivierten Werte');
define('I_JCHECK_UNQ', 'Der Wert "%s" von param "%s" - ist nicht eindeutig. Es sollte einzigartig sein.');

define('I_PRIVATE_DATA', '* private Daten *');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', 'gestern');
define('I_WHEN_TOD', 'heute');
define('I_WHEN_TOM', 'Morgen');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'auf');
define('I_WHEN_WD_ON2', 'auf');
define('I_WHEN_TM_AT', 'beim');

define('I_LANG_LAST', 'Der letzte verbleibende Eintrag "%s" darf nicht gelöscht werden');
define('I_LANG_CURR', 'Es ist nicht gestattet, die Übersetzung zu löschen, die als Ihre aktuelle Übersetzung verwendet wird');
define('I_LANG_FIELD_L10N_DENY', 'Die Lokalisierung kann für das Feld "%s" nicht aktiviert werden.');