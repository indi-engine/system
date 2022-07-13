<?php
define('I_URI_ERROR_SECTION_FORMAT', 'セクション名の形式が間違っています');
define('I_URI_ERROR_ACTION_FORMAT', 'アクション名の形式が間違っています');
define('I_URI_ERROR_ID_FORMAT', 'パラメータ \'id\'は正の整数でなければなりません');
define('I_URI_ERROR_CHUNK_FORMAT', 'URIの一部の形式が間違っています');

define('I_LOGIN_BOX_USERNAME', 'ユーザー');
define('I_LOGIN_BOX_PASSWORD', 'パスワード');
define('I_LOGIN_BOX_REMEMBER', '覚えて');
define('I_LOGIN_BOX_ENTER', '入力');
define('I_LOGIN_BOX_RESET', 'リセット');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'エラー');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'ユーザー名が指定されていません');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'パスワードが指定されていません');
define('I_LOGIN_BOX_LANGUAGE', '舌');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'そのようなアカウントはありません');
define('I_LOGIN_ERROR_WRONG_PASSWORD', '間違ったパスワードを入力した');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'このアカウントは無効になっています。');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'このアカウントタイプは無効になっています。');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'システムには、このアカウントで使用できるパーティションがまだありません。');

define('I_THROW_OUT_ACCOUNT_DELETED', 'アカウントが削除されました。');
define('I_THROW_OUT_PASSWORD_CHANGED', 'パスワードが変更されました。');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'アカウントが無効になりました。');
define('I_THROW_OUT_ROLE_IS_OFF', 'アカウントの種類が無効になりました');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'システムに利用可能なパーティションがありません');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'そのようなセクションはありません');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'このセクションはオフです。');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'そのような行動はありません');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'このアクションはオフです。');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'このセクションではそのようなアクションはありません。');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'このセクションでは、このアクションはオフになっています。');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'このセクションでは、これを行う権限がありません。');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'このセクションの上流セクションの1つが無効になっています。');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'このセクションでは、エントリを作成する権限はありません。');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'このセクションにこのIDのエントリはありません');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'アクション \'％s\'は使用できますが、現在の状況では使用できません');

define('I_DOWNLOAD_ERROR_NO_ID', 'オブジェクト識別子が指定されていないか、数値ではありません');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'フィールド識別子が指定されていないか、数値ではありません');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'この識別子を持つフィールドはありません');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'この識別子のフィールドはファイルでは機能しません');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'この識別子を持つオブジェクトはありません');
define('I_DOWNLOAD_ERROR_NO_FILE', '指定されたオブジェクトの指定されたフィールドにアップロードされたファイル-存在しません');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'ファイル情報の取得に失敗しました。');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'デフォルト値 \'％s\'のタイトル');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', '値 "％s"は可能な値のリストに既にあります');
define('I_ENUMSET_ERROR_VALUE_LAST', '値 "％s"は可能なリストの最後の残りの値なので、削除できません');

define('I_YES', 'はい');
define('I_NO', '番号');
define('I_ERROR', 'エラー');
define('I_MSG', 'メッセージ');
define('I_OR', 'または');
define('I_AND', 'そして');
define('I_BE', 'することが');
define('I_FILE', 'ファイル');
define('I_SHOULD', 'すべき');

define('I_HOME', '開始');
define('I_LOGOUT', '出力');
define('I_MENU', 'メニュー');
define('I_CREATE', '新しい投稿を作成');
define('I_BACK', '戻るには');
define('I_SAVE', 'セーブ');
define('I_CLOSE', '閉じる');
define('I_TOTAL', '合計');
define('I_EXPORT_EXCEL', 'Excelにエクスポート');
define('I_EXPORT_PDF', 'PDFにエクスポート');
define('I_NAVTO_ROWSET', 'リストに戻る');
define('I_NAVTO_ID', 'IDで記録に移動');
define('I_NAVTO_RELOAD', 'リフレッシュ');
define('I_AUTOSAVE', '移行前の自動保存');
define('I_NAVTO_RESET', '変更をキャンセル');
define('I_NAVTO_PREV', '前の投稿に移動');
define('I_NAVTO_SIBLING', '他のエントリに移動します');
define('I_NAVTO_NEXT', '次の投稿へ');
define('I_NAVTO_CREATE', '新しい投稿の作成に進みます');
define('I_NAVTO_NESTED', 'ネストされたエントリのリストに移動します');
define('I_NAVTO_ROWINDEX', 'レコードに移動＃');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'フィールド \'％s\'は必須です');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'フィールド "％s"の値はオブジェクトにできません');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'フィールド "％s"の値は配列にできません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'フィールド "％s"の値 "％s"は、最大11桁の整数である必要があります');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'フィールド "％s"の値 "％s"は有効な値のリストにありません');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'フィールド "％s"に無効な値が含まれています： "％s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'フィールド "％s"の値 "％s"には、ゼロ以外の整数ではない要素が少なくとも1つ含まれています');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'フィールド "％s"の値 "％s"は "1"または "0"でなければなりません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'フィールド "％s"の値 "％s"は、＃rrggbbまたはhue＃rrggbb形式の色ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'フィールド "％s"の値 "％s"は日付ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'フィールド "％s"の値 "％s"は有効な日付ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'フィールド "％s"の値 "％s"は、HH：MM：SSの形式の時間ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'フィールド \'％s\'の時間 \'％s\'は有効な時間ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', '日付が日付ではないため、フィールド「％s」に指定された値「％s」');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'フィールド "％s"で指定された日付 "％s"-正しい必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', '時間は時間ではないため、フィールド "％s"に指定された値 "％s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'フィールド "％s"で指定された時間 "％s"-正しい必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'フィールド "％s"の値 "％s"は、整数部が5桁以下、小数部が2桁以下の数値である必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'フィールド "％s"の値 "％s"は、整数部が8桁以下、小数部が2桁以下の数値である必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'フィールド "％s"の値 "％s"は、整数部分に10桁以下の数字である必要があり、おそらく "-"があり、小数部は3以下です。');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'フィールド "％s"の値 "％s"は、YYYY形式の年ではありません');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', '保存するものはありません');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'まだ何も変更していません。');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', '現在のレコードは、フィールド "％s"でそれ自体の親として指定できません');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', '「％s」フィールドに指定された識別子「％s」のレコードは存在しないため、親として選択できません');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'フィールド "％s"に指定されたレコード "％s"は、現在のレコード "％s"の子/従属であるため、親として選択できません');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'リクエストが完了すると、特に「」タイプのレコードで自動的に実行される操作の1つ');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '-次のエラーを発行しました');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'フィールド \'％s\'は必須です');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'フィールド「％s」で指定された値「％s」は、別のアカウントのユーザー名としてすでに使用されています');

define('I_ROWFILE_ERROR_MKDIR', 'フォルダ "％s"にディレクトリ "％s"を作成できませんでしたが、フォルダは書き込み可能です');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'フォルダ "％s"にディレクトリ "％s"を作成できませんでした。このフォルダは書き込み可能ではありません');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'ダウンロードに必要なディレクトリ "％s"-存在しますが、書き込みできません');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', '存在しないエントリに関連するファイルは操作できません');

define('I_ROWM4D_NO_SUCH_FIELD', 'エンティティ構造 \'％s\'にフィールド `m4d`がありません');

define('I_UPLOAD_ERR_INI_SIZE', '[％s]フィールドでアップロード用に選択されたファイルのサイズが、php.ini設定ファイルのupload_max_filesizeディレクティブで指定された最大サイズを超えました');
define('I_UPLOAD_ERR_FORM_SIZE', '[％s]フィールドでアップロード用に選択されたファイルサイズが、HTMLフォームで指定されたMAX_FILE_SIZE値を超えました');
define('I_UPLOAD_ERR_PARTIAL', '[％s]フィールドでアップロード用に選択されたファイルは、部分的にしか受信されませんでした');
define('I_UPLOAD_ERR_NO_FILE', 'フィールド "％s"で選択されたファイル-アップロードされていません');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'サーバーにフィールド "％s"からファイルをダウンロードするための一時フォルダーがありません');
define('I_UPLOAD_ERR_CANT_WRITE', '[％s]フィールドでアップロード用に選択されたファイルをサーバーのハードドライブに書き込むことができませんでした');
define('I_UPLOAD_ERR_EXTENSION', 'サーバーで実行されているPHP拡張機能の1つが "％s"フィールドからのファイルのロードを停止しました');
define('I_UPLOAD_ERR_UNKNOWN', '不明なエラーのため、 \'％s\'へのファイルのアップロードに失敗しました');

define('I_UPLOAD_ERR_REQUIRED', 'ファイルを選択する必要があります');
define('I_WGET_ERR_ZEROSIZE', 'このファイルが空であるため、Webリンクを使用して \'％s\'にファイルをダウンロードできませんでした');

define('I_FORM_UPLOAD_SAVETOHDD', 'ディスクに保存');
define('I_FORM_UPLOAD_ORIGINAL', 'オリジナルを表示');
define('I_FORM_UPLOAD_NOCHANGE', '去る');
define('I_FORM_UPLOAD_DELETE', '削除する');
define('I_FORM_UPLOAD_REPLACE', '交換する');
define('I_FORM_UPLOAD_REPLACE_WITH', 'に');
define('I_FORM_UPLOAD_NOFILE', '行方不明');
define('I_FORM_UPLOAD_BROWSE', '選択する');
define('I_FORM_UPLOAD_MODE_TIP', 'ウェブリンク経由でダウンロード');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'PCからのファイル..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'Webリンクによるファイル..');

define('I_FORM_UPLOAD_ASIMG', '画像');
define('I_FORM_UPLOAD_ASOFF', '資料');
define('I_FORM_UPLOAD_ASDRW', 'グラフィックレイアウト');
define('I_FORM_UPLOAD_ASARC', 'アーカイブ');
define('I_FORM_UPLOAD_OFEXT', '拡張子がある');
define('I_FORM_UPLOAD_INFMT', '形式で');
define('I_FORM_UPLOAD_HSIZE', 'サイズを持っている');
define('I_FORM_UPLOAD_NOTGT', 'もういや');
define('I_FORM_UPLOAD_NOTLT', '劣らず');
define('I_FORM_UPLOAD_FPREF', '％Sの写真');

define('I_FORM_DATETIME_HOURS', '時間');
define('I_FORM_DATETIME_MINUTES', '分');
define('I_FORM_DATETIME_SECONDS', '秒');
define('I_COMBO_OF', 'の');
define('I_COMBO_MISMATCH_MAXSELECTED', '選択されるオプションの最大数は');
define('I_COMBO_MISMATCH_DISABLED_VALUE', '値 "％s"はフィールド "％s"で選択できません');
define('I_COMBO_KEYWORD_NO_RESULTS', '何も見つかりません');
define('I_COMBO_ODATA_FIELD404', 'フィールド "％s"は実フィールドでも疑似フィールドでもありません');
define('I_COMBO_GROUPBY_NOGROUP', '所属なし');
define('I_COMBO_WAND_TOOLTIP', 'このドロップダウンリストで新しいオプションを作成します<br>このフィールドに示された名前を使用');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', '記録が見当たりませんでした');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'このセクションで利用可能な一連のレコードの中で、');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', '現在の検索オプションに基づく');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', '-このIDのレコードはありません');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', '記録＃');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'の');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', '記録が見当たりませんでした');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'このセクションで利用可能な一連のレコードの中で、');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', '現在の検索オプションに基づく');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '-そのようなシリアル番号を持つレコードはありませんが、フォームのロード時に');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', '欠席している');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '- 選択する -');

define('I_ACTION_INDEX_KEYWORD_LABEL', '探す ...');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'すべての列を検索');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'サブセクション');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '- 選択する -');
define('I_ACTION_INDEX_SUBSECTIONS_NO', '欠席している');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'メッセージ');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', '行を選択');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'フィルター');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'から');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', '前');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'c');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', '沿って');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'はい');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', '番号');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'すべてのフィルターをリセット');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'フィルターは既にリセットされているか、現在まったく使用されていません');

define('I_ACTION_DELETE_CONFIRM_TITLE', '確認');
define('I_ACTION_DELETE_CONFIRM_MSG', 'エントリを削除してもよろしいですか');

define('I_SOUTH_PLACEHOLDER_TITLE', 'このパネルの内容は、別のウィンドウで開きます。');
define('I_SOUTH_PLACEHOLDER_GO', 'に行く');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', '窓へ');
define('I_SOUTH_PLACEHOLDER_GET', '戻る');
define('I_SOUTH_PLACEHOLDER_BACK', 'ここに戻る');

define('I_DEMO_ACTION_OFF', 'このアクションはデモモードでは無効です。');

define('I_MCHECK_REQ', 'フィールド "％s"-必須');
define('I_MCHECK_REG', 'フィールド "％s"の値 "％s"の形式が正しくありません');
define('I_MCHECK_KEY', 'タイプ \'％s\'、識別子 \'％s\'のオブジェクト-見つかりません');
define('I_MCHECK_EQL', '不正な値');
define('I_MCHECK_DIS', 'フィールド "％s"の値 "％s"は、使用できない値のリストにあります');
define('I_MCHECK_UNQ', 'フィールド "％s"の値 "％s"-一意である必要があります');
define('I_JCHECK_REQ', 'パラメータ「％s」-必須');
define('I_JCHECK_REG', 'パラメータ「％s」の値「％s」の形式が正しくありません');
define('I_JCHECK_KEY', 'タイプ \'％s\'、識別子 \'％s\'のオブジェクト-見つかりません');
define('I_JCHECK_EQL', '不正な値');
define('I_JCHECK_DIS', 'パラメータ「％s」の値「％s」は、使用できない値のリストにあります');
define('I_JCHECK_UNQ', 'パラメータ「％s」の値「％s」-一意である必要があります');

define('I_PRIVATE_DATA', '*データは非表示*');

define('I_WHEN_DBY', '昨日の前日');
define('I_WHEN_YST', '昨日');
define('I_WHEN_TOD', '今日');
define('I_WHEN_TOM', '明日');
define('I_WHEN_DAT', '明後日');
define('I_WHEN_WD_ON1', 'で');
define('I_WHEN_WD_ON2', 'に');
define('I_WHEN_TM_AT', 'で');

define('I_LANG_LAST', 'タイプ \'％s\'の最後のレコードを削除できません');
define('I_LANG_CURR', '現在のシステム言語である言語は削除できません');
define('I_LANG_FIELD_L10N_DENY', 'フィールド "％s"のローカライズを有効にできません');