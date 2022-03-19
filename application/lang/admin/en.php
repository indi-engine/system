<?php
define('I_URI_ERROR_SECTION_FORMAT', 'Section name is in wrong format');
define('I_URI_ERROR_ACTION_FORMAT', 'Action name is in wrong format');
define('I_URI_ERROR_ID_FORMAT', 'Uri param \'id\' should have a positive integer value');
define('I_URI_ERROR_CHUNK_FORMAT', 'One of URI chunk has invalid format');

define('I_LOGIN_BOX_USERNAME', 'Username');
define('I_LOGIN_BOX_PASSWORD', 'Password');
define('I_LOGIN_BOX_REMEMBER', 'Remember');
define('I_LOGIN_BOX_ENTER', 'Enter');
define('I_LOGIN_BOX_RESET', 'Reset');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'Error');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'Username not specified');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'Password not specified');
define('I_LOGIN_BOX_LANGUAGE', 'Language');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'Such account does not exist');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'Wrong password');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'This account is switched off');
define('I_LOGIN_ERROR_PROFILE_IS_OFF', 'This account is of type, that is switched off');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'There is yet no sections, accessible by this account');

define('I_THROW_OUT_ACCOUNT_DELETED', 'Your account had just been deleted');
define('I_THROW_OUT_PASSWORD_CHANGED', 'Your password had just been changed');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'Your account had just beed switched off');
define('I_THROW_OUT_PROFILE_IS_OFF', 'Your account is of type, that had just been switched off');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'Now there is no sections remaining accessible for you');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'Such section does not exist');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'Section is switched off');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'Such action does not exist');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'This action is switched off');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'This action does not exist in this section');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'This action is switched off in this section');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'You have no rights on this action in this section');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'One of parent sections for current section - is switched off');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'Row adding is restricted in this section');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'Row with such an id does not exist in this section');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'Action "%s" is accessible, but current circumstances do not suit for it to be performed');

define('I_DOWNLOAD_ERROR_NO_ID', 'Row identifier either is not specified, or is not a number');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'Field identifier either is not specified, or is not a number');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'No field with such identifier');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'This field does not deal with files');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'No row with such identifier');
define('I_DOWNLOAD_ERROR_NO_FILE', 'There is no file, uploaded in this field for this row');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'Getting file information failed');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'Blank title for default value \'%s\'');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'Value "%s" is already exists within the list of allowed values');
define('I_ENUMSET_ERROR_VALUE_LAST', 'Value "%s" is the last remaining possible value, and cannot be deleted');

define('I_YES', 'Yes');
define('I_NO', 'No');
define('I_ERROR', 'Error');
define('I_MSG', 'Message');
define('I_OR', 'or');
define('I_AND', 'and');
define('I_BE', 'be');
define('I_FILE', 'File');
define('I_SHOULD', 'should');

define('I_HOME', 'Home');
define('I_LOGOUT', 'Logout');
define('I_MENU', 'Menu');
define('I_CREATE', 'Create new');
define('I_BACK', 'Back');
define('I_SAVE', 'Save');
define('I_CLOSE', 'Close');
define('I_TOTAL', 'Total');
define('I_EXPORT_EXCEL', 'Export as an Excel spreadsheet');
define('I_EXPORT_PDF', 'Export as an PDF document');
define('I_NAVTO_ROWSET', 'Go back to rowset');
define('I_NAVTO_ID', 'Goto row by ID');
define('I_NAVTO_RELOAD', 'Refresh');
define('I_AUTOSAVE', 'Autosave before goto');
define('I_NAVTO_RESET', 'Rollback changes');
define('I_NAVTO_PREV', 'Goto previous row');
define('I_NAVTO_SIBLING', 'Goto any other row');
define('I_NAVTO_NEXT', 'Goto next row');
define('I_NAVTO_CREATE', 'Goto new row creation');
define('I_NAVTO_NESTED', 'Goto nested objects');
define('I_NAVTO_ROWINDEX', 'Goto row by #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'Field "%s" is required');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'Value of field "%s" can\'t be an object');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'Value of field "%s" can\'t be an array');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'Value "%s" of field "%s" should not be greater than a 11-digit decimal');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'Value "%s" of field "%s" is not within the list of allowed values');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'Field "%s" contains unallowed values: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'Value "%s" of field "%s" contains at least one item that is not an non-zero decimal');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'Value "%s" of field "%s" should be "1" or "0"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'Value "%s" of field "%s" should be a color in formats #rrggbb or hue#rrggbb');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'Value "%s" of field "%s" is not a date');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'Value "%s" of field "%s" is an invalid date');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'Value "%s" of field "%s" should be a time in format HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'Value "%s" of field "%s" is not a valid time');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'Value "%s", mentioned in field "%s" as a date - is not a date');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'Date "%s", mentioned in field "%s"  - is not a valid date');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'Value "%s", mentioned in field "%s" as a time - should be a time in format HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'Time "%s", mentioned in field "%s" - is not a valid time');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'Value "%s" of field "%s" should be a number with 4 or less digits in integer part, optionally prepended with "-" sign, and 2 or less/none digits in fractional part');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'Value "%s" of field "%s" should be a number with 8 or less digits in integer part, optionally prepended with "-" sign, and 2 or less/none digits in fractional part');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'Value "%s" of field "%s" should be a number with 10 or less digits in integer part, optionally prepended with "-" sign, and 3 or less/none digits in fractional part');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'Value "%s" of field "%s" should be a year in format YYYY');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'Nothing to save');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'You did not make any changes');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'Current row cannot be set as parent for itself in field "%s"');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'Row with id "%s", specified in field "%s", - is not exists, so can not be set up as parent row');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'Row "%s", specified in field "%s", - is a child/descendant row for a current row "%s", so it can not be set up as parent row');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'During your request, one of the operations on the entry of type "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', ' - returned the below errors');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'Field "%s" is required');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'Value "%s" of field "%s" is already used as an username for another account');

define('I_ROWFILE_ERROR_MKDIR', 'Recursive creation of directory "%s" within path "%s" is failed, despite on that path is writable');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'Recursive creation of directory "%s" within path "%s" is failed, because that path is not writable');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'Target directory "%s" exists, but is not writable');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'There is no possibility to deal with files of nonexistent row');

define('I_ROWM4D_NO_SUCH_FIELD', 'Field `m4d` does not exist within "%s" entity');

define('I_UPLOAD_ERR_INI_SIZE', 'The uploaded file in field "%s" exceeds the upload_max_filesize directive in php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'The uploaded file in field "%s" exceeds the MAX_FILE_SIZE directive that was specified ');
define('I_UPLOAD_ERR_PARTIAL', 'The uploaded file in field "%s" was only partially uploaded');
define('I_UPLOAD_ERR_NO_FILE', 'No file was uploaded in field "%s"');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'Missing a temporary folder on server for storing file, uploaded in field "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'Failed to write file, uploaded in field "%s", to server\'s hard drive');
define('I_UPLOAD_ERR_EXTENSION', 'File upload in field "%s" stopped by one of the php extensions, running on server');
define('I_UPLOAD_ERR_UNKNOWN', 'File upload in field "%s" failed due to unknown error');

define('I_UPLOAD_ERR_REQUIRED', 'There is no file yet, you should pick one');
define('I_WGET_ERR_ZEROSIZE', 'Web-url usage as file\'s source for field "%s" failed because that file is zero-size');

define('I_FORM_UPLOAD_SAVETOHDD', 'Download');
define('I_FORM_UPLOAD_ORIGINAL', 'Show original');
define('I_FORM_UPLOAD_NOCHANGE', 'No change');
define('I_FORM_UPLOAD_DELETE', 'Delete');
define('I_FORM_UPLOAD_REPLACE', 'Replace');
define('I_FORM_UPLOAD_REPLACE_WITH', 'with');
define('I_FORM_UPLOAD_NOFILE', 'No');
define('I_FORM_UPLOAD_BROWSE', 'Browse');
define('I_FORM_UPLOAD_MODE_TIP', 'Use a web-link to pick a file');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'your local PC file..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'file at web-link..');

define('I_FORM_UPLOAD_ASIMG', 'an image');
define('I_FORM_UPLOAD_ASOFF', 'an office document');
define('I_FORM_UPLOAD_ASDRW', 'a drawing');
define('I_FORM_UPLOAD_ASARC', 'an archive');
define('I_FORM_UPLOAD_OFEXT', 'have type');
define('I_FORM_UPLOAD_INFMT', 'in format');
define('I_FORM_UPLOAD_HSIZE', 'have size');
define('I_FORM_UPLOAD_NOTGT', 'not greater than');
define('I_FORM_UPLOAD_NOTLT', 'not less than');
define('I_FORM_UPLOAD_FPREF', 'Foto %s');

define('I_FORM_DATETIME_HOURS', 'hours');
define('I_FORM_DATETIME_MINUTES', 'minutes');
define('I_FORM_DATETIME_SECONDS', 'seconds');
define('I_COMBO_OF', 'of');
define('I_COMBO_MISMATCH_MAXSELECTED', 'The maximum allowed number of selected options is');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'Option "%s" is unavailable for selection in field "%s"');
define('I_COMBO_KEYWORD_NO_RESULTS', 'Nothing found using this keyword');
define('I_COMBO_ODATA_FIELD404', 'Field "%s" is neither real field nor pseudo field');
define('I_COMBO_GROUPBY_NOGROUP', 'Grouping not set');
define('I_COMBO_WAND_TOOLTIP', 'Create new option in this list using title, entered in this field');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'Row is not found');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'The current section\'s scope of available rows');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', ', in view with applied search options -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', ' does not contain a row with such an ID');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'Row #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'of ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'Row is not found');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'The scope of rows that are available in current section,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', ' in view with applied search options');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', ' - does not contain a row with such an index, but it recently did');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'No');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--Select--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'Search…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'Search on all columns');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'Subsections');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--Select--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'No');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'Message');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'Select a row');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'Options');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'between');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'and');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'from');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'until');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'Yes');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'No');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'Nothing to be emptied');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'Options are already empty or not used at all');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'Confirm');
define('I_ACTION_DELETE_CONFIRM_MSG', 'Are you sure you want to delete');
define('I_ENTRY_TBQ', 'entry,entries,entries');

define('I_SOUTH_PLACEHOLDER_TITLE', 'Contents of this tab is opened in a separate window');
define('I_SOUTH_PLACEHOLDER_GO', 'Go to');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', ' that window');
define('I_SOUTH_PLACEHOLDER_GET', 'Get contents');
define('I_SOUTH_PLACEHOLDER_BACK', ' back here');

define('I_DEMO_ACTION_OFF', 'This action is turned Off in demo-mode');

define('I_MCHECK_REQ', 'Field "%s" - is required');
define('I_MCHECK_REG', 'Value "%s" of field "%s" - is in invalid format');
define('I_MCHECK_KEY', 'No object of type "%s" was found by key "%s"');
define('I_MCHECK_EQL', 'Wrong value');
define('I_MCHECK_DIS', 'Value "%s" of field "%s" - is in the list of disabled values');
define('I_MCHECK_UNQ', 'Value "%s" of field "%s" - is not unique. It should be unique.');
define('I_JCHECK_REQ', 'Param "%s" - is not given');
define('I_JCHECK_REG', 'Value "%s" of param "%s" - is in invalid format');
define('I_JCHECK_KEY', 'No object of type "%s" was found by key "%s"');
define('I_JCHECK_EQL', 'Wrong value');
define('I_JCHECK_DIS', 'Value "%s" of param "%s" - is in the list of disabled values');
define('I_JCHECK_UNQ', 'Value "%s" of param "%s" - is not unique. It should be unique.');

define('I_PRIVATE_DATA', '*private data*');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', 'yesterday');
define('I_WHEN_TOD', 'today');
define('I_WHEN_TOM', 'tomorrow');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'on');
define('I_WHEN_WD_ON2', 'on');
define('I_WHEN_TM_AT', 'at');

define('I_LANG_LAST', 'It is not allowed to delete the last remaining "%s" entry');
define('I_LANG_CURR', 'It is not allowed to delete the translation, that is used as your current translation');
define('I_LANG_FIELD_L10N_DENY', 'Localization cannot be turned on for field "%s"');
define('I_LANG_QYQN_CONFIRM', 'If you want %s language "%s" for fraction "%s" press "%s". If you just need to bring it into line with the current state - press "%s"');
define('I_LANG_QYQN_CONFIRM2', 'For fraction "%s", language "%s" will be manually marked as "%s". Proceed?');
define('I_LANG_QYQN_SELECT', 'Select source language');
define('I_LANG_EXPORT_HEADER', 'Select export params');
define('I_LANG_IMPORT_HEADER', 'Select import params');
define('I_LANG_NOT_SUPPORTED', 'Not supported so far');
define('I_LANG_SELECT_CURRENT', 'Select current language of fraction "%s"');
define('I_LANG_MIGRATE_META', 'Prepare fields');
define('I_LANG_MIGRATE_DATA', 'Migrate titles');
define('I_ADD', 'Add');
define('I_DELETE', 'Delete');
define('I_SECTION_CLONE_SELECT_PARENT', 'Select the parent section, which should be subordinate to duplicates of the selected sections');

define('I_TILE_NOTHUMB', 'No thumb');
define('I_TILE_NOFILE', 'No file');

define('I_CHANGELOG_FIELD', 'What was changed');
define('I_CHANGELOG_WAS', 'Was');
define('I_CHANGELOG_NOW', 'Became');

define('I_WEEK', 'Week');
define('I_TODAY', 'Today');

define('I_PRINT', 'Print');

define('I_NUM2STR_ZERO', 'zero');
define('I_NUM2STR_1TO9', 'one,two,three,four,five,six,seven,eight,nine');
define('I_NUM2STR_1TO9_2', 'one,two,three,four,five,six,seven,eight,nine');
define('I_NUM2STR_10TO19', 'ten,eleven,twelve,thirteen,fourteen,fifteen,sixteen,seventeen,eighteen,nineteen');
define('I_NUM2STR_20TO90', 'twenty,thirty,forty,fifty,sixty,seventy,eighty,ninety');
define('I_NUM2STR_100TO900', 'one hundred,two hundred,three hundred,four hundred,five hundred,six hundred,seven hundred,eight hundred,nine hundred');
define('I_NUM2STR_TBQ_KOP', 'kopeck,kopecks,kopecks');
define('I_NUM2STR_TBQ_RUB', 'ruble,rubles,rubles');
define('I_NUM2STR_TBQ_THD', 'thousand,thousand,thousand');
define('I_NUM2STR_TBQ_MLN', 'million,million,million');
define('I_NUM2STR_TBQ_BLN', 'billion,billion,billion');

define('I_ENT_AUTHOR_SPAN', 'Created');
define('I_ENT_AUTHOR_ROLE', 'Role');
define('I_ENT_AUTHOR_USER', 'User');
define('I_ENT_AUTHOR_TIME', 'Datetime');
define('I_ENT_TOGGLE_Y', 'Turned on');
define('I_ENT_TOGGLE_N', 'Turned off');