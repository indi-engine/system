<?php
define('I_URI_ERROR_SECTION_FORMAT', 'セクション名の形式が間違っています');
define('I_URI_ERROR_ACTION_FORMAT', 'アクション名の形式が間違っています');
define('I_URI_ERROR_ID_FORMAT', 'URIパラメータ「id」は正の整数値である必要があります');
define('I_URI_ERROR_CHUNK_FORMAT', 'URI チャンクの 1 つに無効な形式があります');

define('I_LOGIN_BOX_USERNAME', 'ユーザー名');
define('I_LOGIN_BOX_PASSWORD', 'パスワード');
define('I_LOGIN_BOX_REMEMBER', '覚えて');
define('I_LOGIN_BOX_ENTER', '入力');
define('I_LOGIN_BOX_RESET', 'リセット');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'エラー');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'ユーザー名が指定されていません');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'パスワードが指定されていません');
define('I_LOGIN_BOX_LANGUAGE', '言語');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'そのようなアカウントは存在しません');
define('I_LOGIN_ERROR_WRONG_PASSWORD', '間違ったパスワード');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'このアカウントはオフになっています');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'このアカウントはオフになっているタイプです');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'このアカウントでアクセスできるセクションはまだありません');

define('I_THROW_OUT_ACCOUNT_DELETED', 'あなたのアカウントは削除されました');
define('I_THROW_OUT_PASSWORD_CHANGED', 'パスワードが変更されました');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'あなたのアカウントはちょうどオフになりました');
define('I_THROW_OUT_ROLE_IS_OFF', 'あなたのアカウントは、ちょうどオフにされたタイプです');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', '現在、アクセス可能なセクションは残っていません');
define('I_THROW_OUT_SESSION_EXPIRED', 'セッションは利用できなくなりました。再度ログインしますか?');

define('I_WS_CONNECTED', '%s ミリ秒で接続されました');
define('I_WS_DISCONNECTED', '切断');
define('I_WS_RECONNECTING', '再接続しています...');
define('I_WS_RECONNECTED', '%s ミリ秒で再接続しました');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'そのようなセクションは存在しません');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'セクションはオフになっています');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'そのような行為は存在しない');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'このアクションはオフになっています');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'このアクションはこのセクションには存在しません');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'このセクションではこのアクションはオフになっています');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'このセクションではこのアクションを実行する権限がありません');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', '現在のセクションの親セクションの 1 つがオフになっています');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'このセクションでは行の追加が制限されています');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'このセクションにはそのような ID の行は存在しません');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'アクション「%s」はアクセス可能ですが、現在の状況では実行できません');

define('I_DOWNLOAD_ERROR_NO_ID', '行識別子が指定されていないか、数値ではありません');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'フィールド識別子が指定されていないか、数値ではありません');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'そのような識別子を持つフィールドはありません');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'このフィールドはファイルを扱いません');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'そのような識別子を持つ行はありません');
define('I_DOWNLOAD_ERROR_NO_FILE', 'この行のこのフィールドにアップロードされたファイルはありません');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'ファイル情報の取得に失敗しました');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'デフォルト値 \'%s\' のタイトルが空白です');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', '値「%s」は許可された値のリスト内に既に存在します');
define('I_ENUMSET_ERROR_VALUE_LAST', '値「%s」は最後に残った値なので削除できません');

define('I_YES', 'はい');
define('I_NO', 'いいえ');
define('I_ERROR', 'エラー');
define('I_MSG', 'メッセージ');
define('I_OR', 'または');
define('I_AND', 'そして');
define('I_BE', 'なれ');
define('I_FILE', 'ファイル');
define('I_SHOULD', 'すべき');

define('I_HOME', '家');
define('I_LOGOUT', 'ログアウト');
define('I_MENU', 'メニュー');
define('I_CREATE', '新しく作る');
define('I_BACK', '戻る');
define('I_SAVE', '保存');
define('I_CLOSE', '近い');
define('I_TOTAL', '合計');
define('I_TOGGLE_Y', 'オンにする');
define('I_TOGGLE_N', '消す');
define('I_EXPORT_EXCEL', 'Excel スプレッドシートとしてエクスポート');
define('I_EXPORT_PDF', 'PDF文書としてエクスポート');
define('I_SORT_DEFAULT', 'デフォルトのソートにリセット');
define('I_NAVTO_ROWSET', '行セットに戻る');
define('I_NAVTO_ID', 'ID で行へ移動');
define('I_NAVTO_RELOAD', 'リフレッシュ');
define('I_AUTOSAVE', '移動前に自動保存');
define('I_NAVTO_RESET', '変更をロールバックする');
define('I_NAVTO_PREV', '前の行へ移動');
define('I_NAVTO_SIBLING', '他の行へ移動');
define('I_NAVTO_NEXT', '次の行へ移動');
define('I_NAVTO_CREATE', '新しい行の作成へ進む');
define('I_NAVTO_NESTED', 'ネストされたオブジェクトへ移動');
define('I_NAVTO_ROWINDEX', '# で行へ移動');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'フィールド「%s」は必須です');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'フィールド「%s」の値はオブジェクトにできません');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'フィールド「%s」の値は配列にできません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'フィールド「%s」の値「%s」は 11 桁の小数点以下である必要があります');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'フィールド「%s」の値「%s」は、許可された値のリスト内にありません');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'フィールド「%s」には許可されていない値「%s」が含まれています');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'フィールド「%s」の値「%s」には、ゼロ以外の小数ではない項目が少なくとも 1 つ含まれています');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'フィールド「%s」の値「%s」は「1」または「0」である必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'フィールド「%s」の値「%s」は、#rrggbb または hue#rrggbb 形式の色である必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'フィールド「%s」の値「%s」は日付ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'フィールド「%s」の値「%s」は無効な日付です');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'フィールド「%s」の値「%s」は、HH:MM:SS 形式の時刻である必要があります');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'フィールド「%s」の値「%s」は有効な時間ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'フィールド「%s」で日付として指定されている値「%s」は日付ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'フィールド「%s」に記載されている日付「%s」は有効な日付ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', '値「%s」は、フィールド「%s」で時間として指定されています。HH:MM:SS 形式の時間である必要があります。');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'フィールド「%s」に記載されている時間「%s」は有効な時間ではありません');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'フィールド "%s" の値 "%s" は、整数部が 4 桁以下で、オプションで先頭に "-" 記号が付き、小数部が 2 桁以下または 0 桁の数値である必要があります。');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'フィールド "%s" の値 "%s" は、整数部が 8 桁以下で、オプションで先頭に "-" 記号が付き、小数部が 2 桁以下または 0 桁の数値である必要があります。');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'フィールド "%s" の値 "%s" は、整数部が 10 桁以下で、オプションで先頭に "-" 記号が付き、小数部が 3 桁以下または 0 桁の数値である必要があります。');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'フィールド「%s」の値「%s」は、YYYY 形式の年である必要があります');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', '保存するものはありません');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', '変更は行われませんでした');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', '現在の行は、フィールド「%s」でそれ自体の親として設定できません');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'フィールド "%s" で指定された ID "%s" の行は存在しないため、親行として設定できません');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'フィールド "%s" で指定された行 "%s" は、現在の行 "%s" の子/子孫行であるため、親行として設定できません。');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'リクエスト中に、タイプ「');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', ' - 以下のエラーが返されました');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'フィールド「%s」は必須です');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'フィールド「%s」の値「%s」は、すでに別のアカウントのユーザー名として使用されています');

define('I_ROWFILE_ERROR_MKDIR', 'パス「%s」内のディレクトリ「%s」の再帰作成は、そのパスが書き込み可能であるにもかかわらず失敗しました');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'パス「%s」内のディレクトリ「%s」の再帰作成は、そのパスが書き込み可能ではないため失敗しました');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'ターゲットディレクトリ「%s」は存在しますが、書き込み可能ではありません');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', '存在しない行のファイルを処理することはできない');

define('I_ROWM4D_NO_SUCH_FIELD', 'フィールド `m4d` は "%s" エンティティ内に存在しません');

define('I_UPLOAD_ERR_INI_SIZE', 'フィールド「%s」にアップロードされたファイルは、php.ini の upload_max_filesize ディレクティブを超えています');
define('I_UPLOAD_ERR_FORM_SIZE', 'フィールド「%s」にアップロードされたファイルは、指定された MAX_FILE_SIZE ディレクティブを超えています ');
define('I_UPLOAD_ERR_PARTIAL', 'フィールド「%s」にアップロードされたファイルは部分的にしかアップロードされていません');
define('I_UPLOAD_ERR_NO_FILE', 'フィールド「%s」にファイルがアップロードされませんでした');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'フィールド「%s」にアップロードされたファイルを保存するための一時フォルダがサーバー上にありません');
define('I_UPLOAD_ERR_CANT_WRITE', 'フィールド「%s」にアップロードされたファイルをサーバーのハードドライブに書き込むことができませんでした');
define('I_UPLOAD_ERR_EXTENSION', 'フィールド "%s" のファイルアップロードが、サーバー上で実行されている PHP 拡張機能の 1 つによって停止されました');
define('I_UPLOAD_ERR_UNKNOWN', '不明なエラーのため、フィールド「%s」へのファイルアップロードに失敗しました');

define('I_UPLOAD_ERR_REQUIRED', 'まだファイルがありません。選択してください');
define('I_WGET_ERR_ZEROSIZE', 'ファイルのサイズがゼロであるため、フィールド "%s" のファイル ソースとして Web URL を使用できませんでした。');

define('I_FORM_UPLOAD_SAVETOHDD', 'ダウンロード');
define('I_FORM_UPLOAD_ORIGINAL', 'オリジナルを表示');
define('I_FORM_UPLOAD_NOCHANGE', '変化なし');
define('I_FORM_UPLOAD_DELETE', '消去');
define('I_FORM_UPLOAD_REPLACE', '交換する');
define('I_FORM_UPLOAD_REPLACE_WITH', 'と');
define('I_FORM_UPLOAD_NOFILE', 'いいえ');
define('I_FORM_UPLOAD_BROWSE', 'ブラウズ');
define('I_FORM_UPLOAD_MODE_TIP', 'ウェブリンクを使用してファイルを選択する');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'ローカル PC ファイル。');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'ファイルはウェブリンクにあります。');

define('I_FORM_UPLOAD_ASIMG', '画像');
define('I_FORM_UPLOAD_ASOFF', 'オフィス文書');
define('I_FORM_UPLOAD_ASDRW', '絵');
define('I_FORM_UPLOAD_ASARC', 'アーカイブ');
define('I_FORM_UPLOAD_ASAUD', 'オーディオ');
define('I_FORM_UPLOAD_OFEXT', 'タイプがある');
define('I_FORM_UPLOAD_INFMT', 'フォーマット');
define('I_FORM_UPLOAD_HSIZE', '大きさがある');
define('I_FORM_UPLOAD_NOTGT', '以下');
define('I_FORM_UPLOAD_NOTLT', 'よりは少なくない');
define('I_FORM_UPLOAD_FPREF', '写真 %s');

define('I_FORM_DATETIME_HOURS', '時間');
define('I_FORM_DATETIME_MINUTES', '分');
define('I_FORM_DATETIME_SECONDS', '秒');
define('I_OF', 'の');
define('I_COMBO_MISMATCH_MAXSELECTED', '選択可能なオプションの最大数は');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'オプション「%s」はフィールド「%s」では選択できません');
define('I_COMBO_KEYWORD_NO_RESULTS', 'このキーワードでは何も見つかりません');
define('I_COMBO_ODATA_FIELD404', 'フィールド「%s」は実フィールドでも疑似フィールドでもありません');
define('I_COMBO_GROUPBY_NOGROUP', 'グループ化が設定されていません');
define('I_COMBO_WAND_TOOLTIP', 'このフィールドに入力されたタイトルを使用して、このリストに新しいオプションを作成します');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', '行が見つかりません');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', '現在のセクションの利用可能な行の範囲');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', '、検索オプションを適用したビュー -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', ' そのようなIDの行は含まれません');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', '行 ＃');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'の ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', '行が見つかりません');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', '現在のセクションで利用可能な行の範囲、');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', ' 検索オプションを適用したビュー');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', ' - そのようなインデックスを持つ行は含まれていないが、最近含まれていた');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'いいえ');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '- 選択する - ');

define('I_ACTION_INDEX_KEYWORD_LABEL', '検索…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'すべての列を検索');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'サブセクション');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '- 選択する - ');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'いいえ');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'メッセージ');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', '行を選択');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'オプション');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', '間');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'そして');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'から');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'それまで');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'はい');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'いいえ');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', '空にするものは何もない');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'オプションはすでに空であるか、まったく使用されていません');

define('I_ACTION_DELETE_CONFIRM_TITLE', '確認する');
define('I_ACTION_DELETE_CONFIRM_MSG', '消去してもよろしいですか');
define('I_ENTRY_TBQ', 'エントリー、エントリー、エントリー');

define('I_SOUTH_PLACEHOLDER_TITLE', 'このタブの内容は別のウィンドウで開きます');
define('I_SOUTH_PLACEHOLDER_GO', 'へ移動');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', ' その窓');
define('I_SOUTH_PLACEHOLDER_GET', 'コンテンツを取得');
define('I_SOUTH_PLACEHOLDER_BACK', ' ここに戻って');

define('I_DEMO_ACTION_OFF', 'このアクションはデモモードではオフになっています');

define('I_MCHECK_REQ', 'フィールド「%s」は必須です');
define('I_MCHECK_REG', 'フィールド「%s」の値「%s」は無効な形式です');
define('I_MCHECK_KEY', 'キー「%s」でタイプ「%s」のオブジェクトが見つかりませんでした');
define('I_MCHECK_EQL', '値が間違っています');
define('I_MCHECK_DIS', 'フィールド「%s」の値「%s」は無効な値のリストにあります');
define('I_MCHECK_UNQ', 'フィールド "%s" の値 "%s" は一意ではありません。一意である必要があります。');
define('I_JCHECK_REQ', 'パラメータ「%s」が指定されていません');
define('I_JCHECK_REG', 'パラメータ「%s」の値「%s」は無効な形式です');
define('I_JCHECK_KEY', 'キー「%s」でタイプ「%s」のオブジェクトが見つかりませんでした');
define('I_JCHECK_EQL', '値が間違っています');
define('I_JCHECK_DIS', 'パラメータ「%s」の値「%s」は無効な値のリストにあります');
define('I_JCHECK_UNQ', 'パラメータ "%s" の値 "%s" は一意ではありません。一意である必要があります。');

define('I_PRIVATE_DATA', '*個人データ*');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', '昨日');
define('I_WHEN_TOD', '今日');
define('I_WHEN_TOM', '明日');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'の上');
define('I_WHEN_WD_ON2', 'の上');
define('I_WHEN_TM_AT', 'で');

define('I_LANG_LAST', '最後に残った「%s」エントリを削除することはできません');
define('I_LANG_CURR', '現在の翻訳として使用されている翻訳を削除することはできません');
define('I_LANG_FIELD_L10N_DENY', 'フィールド「%s」のローカリゼーションをオンにできません');
define('I_LANG_QYQN_CONFIRM', '分数「%s」に %s 言語「%s」が必要な場合は、「%s」を押してください。現在の状態に合わせるだけの場合は、「%s」を押してください。');
define('I_LANG_QYQN_CONFIRM2', '分数「%s」の場合、言語「%s」は手動で「%s」としてマークされます。続行しますか?');
define('I_LANG_QYQN_SELECT', 'ソース言語を選択');
define('I_LANG_EXPORT_HEADER', 'エクスポートパラメータを選択');
define('I_LANG_IMPORT_HEADER', 'インポートパラメータを選択');
define('I_LANG_NOT_SUPPORTED', '今のところサポートされていません');
define('I_LANG_SELECT_CURRENT', '分数「%s」の現在の言語を選択します');
define('I_LANG_MIGRATE_META', 'フィールドを準備する');
define('I_LANG_MIGRATE_DATA', 'タイトルを移行する');
define('I_ADD', '追加');
define('I_DELETE', '消去');
define('I_SECTION_CLONE_SELECT_PARENT', '選択したセクションの複製に従属する親セクションを選択します');

define('I_TILE_NOTHUMB', '親指なし');
define('I_TILE_NOFILE', 'ファイルがない');

define('I_CHANGELOG_FIELD', '何が変わったか');
define('I_CHANGELOG_WAS', 'だった');
define('I_CHANGELOG_NOW', 'なりました');

define('I_WEEK', '週');
define('I_TODAY', '今日');

define('I_PRINT', '印刷');

define('I_NUM2STR_ZERO', 'ゼロ');
define('I_NUM2STR_1TO9', '1,2,3,4,5,6,7,8,9');
define('I_NUM2STR_1TO9_2', '1,2,3,4,5,6,7,8,9');
define('I_NUM2STR_10TO19', '10,11,12,13,14,15,16,17,18,19');
define('I_NUM2STR_20TO90', '20,30,40,50,60,70,80,90');
define('I_NUM2STR_100TO900', '100,200,300,400,500,600,700,800,900');
define('I_NUM2STR_TBQ_KOP', 'コペイカ,コペイカ,コペイカ');
define('I_NUM2STR_TBQ_RUB', 'ルーブル,ルーブル,ルーブル');
define('I_NUM2STR_TBQ_THD', '千,千,千');
define('I_NUM2STR_TBQ_MLN', '百万,百万,百万');
define('I_NUM2STR_TBQ_BLN', '0億,10億,10億');

define('I_AGO_SECONDS', '秒,秒');
define('I_AGO_MINUTES', '分,分');
define('I_AGO_HOURS', '時間,時間');
define('I_AGO_DAYS', '日,日');
define('I_AGO_WEEKS', '週,週');
define('I_AGO_MONTHS', '月,月');
define('I_AGO_YEARS', '年,年');

define('I_PANEL_GRID', 'グリッド');
define('I_PANEL_PLAN', 'カレンダー');
define('I_PANEL_TILE', 'ギャラリー');

define('I_ENT_AUTHOR_SPAN', '作成した');
define('I_ENT_AUTHOR_ROLE', '役割');
define('I_ENT_AUTHOR_USER', 'ユーザー');
define('I_ENT_AUTHOR_TIME', '日付時刻');
define('I_ENT_TOGGLE', 'トグル');
define('I_ENT_TOGGLE_Y', 'オン');
define('I_ENT_TOGGLE_N', 'オフ');
define('I_ENT_EXTENDS_OTHER', 'php-model のファイルは存在しますが、%s が親クラスとして指定されています');

define('I_SELECT_CFGFIELD', 'このフィールドに追加する設定パラメータを選択してください');
define('I_SELECT_PLEASE', '選んでください');
define('I_L10N_TOGGLE_ACTION_Y', 'アクション「%s」のローカリゼーションを %s にしたい場合は、「%s」を押してください。');
define('I_L10N_TOGGLE_MATCH', 'それ以外の場合は、現在の状況に合わせてローカリゼーション ステータスの値を明示的に指定する必要がある場合は、「%s」を押します。');
define('I_L10N_TOGGLE_ACTION_EXPL', 'アクション「%s」のローカリゼーション ステータスは明示的に「%s」に設定されます。続行しますか?');
define('I_L10N_TOGGLE_ACTION_LANG_CURR', 'アクション「%s」の現在の言語を選択します');
define('I_L10N_TOGGLE_ACTION_LANG_KEPT', 'アクション「%s」に保持する言語を選択します');

define('I_L10N_TOOGLE_FIELD_DENIED', '依存フィールドのローカリゼーションをオン/オフにすることはできません');
define('I_L10N_TOGGLE_FIELD_Y', 'フィールド「%s」のローカライズを %s する場合は、「%s」を押してください。');
define('I_L10N_TOGGLE_FIELD_EXPL', 'フィールド「%s」のローカリゼーション ステータスは明示的に「%s」に設定されます。続行しますか?');
define('I_L10N_TOGGLE_FIELD_LANG_CURR', 'フィールド「%s」の現在の言語を選択します');
define('I_L10N_TOGGLE_FIELD_LANG_KEPT', 'フィールド「%s」に保持する言語を選択します');

define('I_GRID_COLOR_BREAK_INCOMPAT', 'この機能は数値列にのみ適用されます');
define('I_REALTIME_CONTEXT_AUTODELETE_ONLY', 'コンテキストタイプのエントリは手動で削除できません');
define('I_ONDELETE_RESTRICT', 'エンティティ "%s" (`%s`.`%s`) のフィールド "%s" に設定されている ON DELETE RESTRICT ルールにより、削除が制限されています。そのエンティティの少なくとも 1 つのレコードに、そのフィールドに値があり、それが削除するレコードへの直接参照またはカスケード参照であるためです。');

define('I_LANG_WORD_DETECT_ONLY', '事前チェックはしますか?');
define('I_SECTION_ROWSET_MIN_CONF', 'パネル %s を有効にするには、次の最小構成を指定してください:');
define('I_SECTION_CONF_SETUP', '設定を適用しています。');
define('I_SECTION_CONF_SETUP_DONE', '設定が正常に適用されました');
define('I_NOTICE_HIDE_ALL', 'すべて非表示');
define('I_RECORD_DELETED', 'この記録は削除されました');

define('I_FILE_EXISTS', 'ファイルは既に存在します: %s');
define('I_FILE_CREATED', '作成されたファイル: %s');
define('I_CLASS_404', 'クラスが見つかりません %s');
define('I_FORMAT_HIS', '引数 %s の値 "%s" は hh:mm:ss 形式の時刻である必要があります');
define('I_GAPI_RESPONSE', 'Google Cloud Translate API レスポンス: %s');
define('I_GAPI_KEY_REQUIRED', 'ここで API キーを指定してください。');
define('I_QUEUE_STARTED', '開始');
define('I_QUEUE_RESUMED', '再開した');
define('I_QUEUE_COMPLETED', '完了');
define('I_PLAN_ITEM_LOAD_FAIL', '%s %s をスケジュールにロードできません');
define('I_SECTIONS_TPLCTLR_404', 'テンプレート コントローラ ファイルが見つかりません');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_JS', 'js-controller のファイルは存在しますが、そこに指定されている親クラスは %s です');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_PHP', 'php-controller のファイルは存在しますが、そこに指定されている親クラスは %s です');
define('I_SECTIONS_CTLR_PARENT_404', 'js-controller ファイル内で親クラス名が見つかりません');
define('I_SECTIONS_CTLR_EMPTY_JS', 'js-controller のファイルが空です');
define('I_ENTITIES_TPLMDL_404', 'テンプレートモデルファイルが見つかりません');
define('I_ENTITIES_TPLMDLROW_404', 'モデルの rowClass のテンプレート ファイルが見つかりません');
define('I_FILTERS_FEATURE_UNAPPLICABLE', 'この機能は、外部キーフィールドを持つフィルタにのみ適用されます。');
define('I_LANG_NEW_QTY', 'Google がサポートする新しい言語の数: %s');
define('I_SCNACS_TPL_404', 'アクション テンプレート ファイルが見つかりません: %s');
define('I_MDL_CHLOG_NO_REVERT_1', '読み取り専用または非表示のフィールドでは元に戻すことはできません');
define('I_MDL_CHLOG_NO_REVERT_2', '元に戻すは、%s および %s タイプのフィールドに対してのみ有効です');
define('I_MDL_CHLOG_NO_REVERT_3', '外部キーフィールドでは元に戻す操作は無効です');
define('I_MDL_GRID_PARENT_GROUP_DIFFERS', '親エントリは別のグループにあります');
define('I_EXPORT_CERTAIN', '特定のフィールドのみをエクスポートする場合は、選択してください');
define('I_SCHED_UNABLE_SET_REST', 'スケジュール内に勤務時間外のスペースを作成できません');
define('I_MDL_ADMIN_VK_1', 'ページアドレスは https://vk.com/ で始まる必要があります');
define('I_MDL_ADMIN_VK_2', 'このページはVKに存在しません');
define('I_MDL_ADMIN_VK_3', 'VKのこのページはユーザーページではありません');

define('I_FIELD_DRAG_CLICK', 'ドラッグして並べ替え / クリックして詳細を表示');
define('I_ENTITY_CLICK', 'クリックして詳細を表示');
define('I_ACTION_FORM', '詳細');
