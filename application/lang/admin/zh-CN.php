<?php
define('I_URI_ERROR_SECTION_FORMAT', '部分名称格式错误');
define('I_URI_ERROR_ACTION_FORMAT', '操作名称格式错误');
define('I_URI_ERROR_ID_FORMAT', 'Uri 参数“id”应具有正整数值');
define('I_URI_ERROR_CHUNK_FORMAT', '其中一个 URI 块的格式无效');

define('I_LOGIN_BOX_USERNAME', '用户名');
define('I_LOGIN_BOX_PASSWORD', '密码');
define('I_LOGIN_BOX_REMEMBER', '记住');
define('I_LOGIN_BOX_ENTER', '进入');
define('I_LOGIN_BOX_RESET', '重置');
define('I_LOGIN_ERROR_MSGBOX_TITLE', '错误');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', '未指定用户名');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', '未指定密码');
define('I_LOGIN_BOX_LANGUAGE', '语言');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', '该账户不存在');
define('I_LOGIN_ERROR_WRONG_PASSWORD', '密码错误');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', '此帐户已关闭');
define('I_LOGIN_ERROR_ROLE_IS_OFF', '此帐户类型为，已关闭');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', '此帐户尚未访问任何部分');

define('I_THROW_OUT_ACCOUNT_DELETED', '您的帐户刚刚被删除');
define('I_THROW_OUT_PASSWORD_CHANGED', '您的密码刚刚被更改');
define('I_THROW_OUT_ACCOUNT_IS_OFF', '你的帐户刚刚被关闭');
define('I_THROW_OUT_ROLE_IS_OFF', '您的账户类型为，刚刚被关闭');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', '现在没有可供您访问的部分');
define('I_THROW_OUT_SESSION_EXPIRED', '您的会话不再可用。继续重新登录吗？');

define('I_WS_CONNECTED', '%s 毫秒后连接');
define('I_WS_DISCONNECTED', '断开连接');
define('I_WS_RECONNECTING', '正在重新连接...');
define('I_WS_RECONNECTED', '%s 毫秒后重新连接');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', '此部分不存在');
define('I_ACCESS_ERROR_SECTION_IS_OFF', '部分已关闭');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', '此类行为不存在');
define('I_ACCESS_ERROR_ACTION_IS_OFF', '此操作已关闭');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', '此操作不存在于此部分');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', '此操作在此部分被关闭');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', '您无权在本节中执行此操作');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', '当前部分的父部分之一 - 已关闭');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', '此部分限制添加行');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', '此部分中不存在具有该 ID 的行');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', '操作“％s”可以访问，但当前情况不适合执行。');

define('I_DOWNLOAD_ERROR_NO_ID', '未指定行标识符，或者不是数字');
define('I_DOWNLOAD_ERROR_NO_FIELD', '字段标识符未指定，或者不是数字');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', '没有具有该标识符的字段');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', '此字段不处理文件');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', '没有具有该标识符的行');
define('I_DOWNLOAD_ERROR_NO_FILE', '此行的此字段中没有上传文件');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', '获取文件信息失败');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', '默认值为“%s”的空白标题');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', '值“%s”已存在于允许值列表中');
define('I_ENUMSET_ERROR_VALUE_LAST', '值“%s”是最后剩余的可能值，无法删除');

define('I_YES', '是的');
define('I_NO', '不');
define('I_ERROR', '错误');
define('I_MSG', '信息');
define('I_OR', '或者');
define('I_AND', '和');
define('I_BE', '是');
define('I_FILE', '文件');
define('I_SHOULD', '应该');

define('I_HOME', '家');
define('I_LOGOUT', '登出');
define('I_MENU', '菜单');
define('I_CREATE', '创建新的');
define('I_BACK', '后退');
define('I_SAVE', '节省');
define('I_CLOSE', '关闭');
define('I_TOTAL', '全部的');
define('I_TOGGLE_Y', '打开');
define('I_TOGGLE_N', '关');
define('I_EXPORT_EXCEL', '导出为 Excel 电子表格');
define('I_EXPORT_PDF', '导出为 PDF 文档');
define('I_SORT_DEFAULT', '重置为默认排序');
define('I_NAVTO_ROWSET', '返回行集');
define('I_NAVTO_ID', '根据 ID 转到行');
define('I_NAVTO_RELOAD', '刷新');
define('I_AUTOSAVE', '转到前自动保存');
define('I_NAVTO_RESET', '回滚更改');
define('I_NAVTO_PREV', '转至上一行');
define('I_NAVTO_SIBLING', '转到任何其他行');
define('I_NAVTO_NEXT', '转至下一行');
define('I_NAVTO_CREATE', '转到新行创建');
define('I_NAVTO_NESTED', '转到嵌套对象');
define('I_NAVTO_ROWINDEX', '按 # 转到行');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', '字段“%s”为必填项');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', '字段“％s”的值不能是对象');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', '字段“%s”的值不能是数组');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', '字段“％s”的值“％s”不应大于11位小数');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', '字段“%s”的值“%s”不在允许值列表中');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', '字段“％s”包含不允许的值：“％s”');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', '字段“％s”的值“％s”至少包含一个非零小数项');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', '字段“%s”的值“%s”应为“1”或“0”');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', '字段“％s”的值“％s”应为格式为#rrggbb 或 hue#rrggbb 的颜色');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', '字段“％s”的值“％s”不是日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', '字段“%s”的值“%s”是无效日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', '字段“％s”的值“％s”应为格式为 HH:MM:SS 的时间');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', '字段“％s”的值“％s”不是有效时间');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', '字段“％s”中提到的值“％s”作为日期 - 但不是日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', '字段“％s”中提到的日期“％s”不是有效日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', '字段“％s”中提到的值“％s”作为时间 - 应为 HH:MM:SS 格式的时间');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', '字段“％s”中提到的时间“％s”不是有效时间');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', '字段“％s”的值“％s”应为整数部分不超过 4 位的数字，可选择在前面加上“-”号，小数部分不超过 2 位或没有数字');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', '字段“％s”的值“％s”应为整数部分为 8 位或更少的数字，可选择在前面加上“-”号，小数部分为 2 位或更少的数字或无数字');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', '字段“％s”的值“％s”应为整数部分不超过 10 位的数字，可选择在前面加上“-”号，小数部分不超过 3 位或没有数字');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', '字段“％s”的值“％s”应为 YYYY 格式的年份。');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', '没什么可保存的');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', '您未做任何更改');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', '当前行无法在字段“%s”中设置为其自身的父行');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', '字段“％s”中指定的 ID 为“％s”的行不存在，因此无法设置为父行');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', '字段“％s”中指定的行“％s”是当前行“％s”的子行/后代行，因此不能将其设置为父行');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', '在您的请求期间，对类型为“');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', ' - 返回以下错误');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', '字段“%s”为必填项');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', '字段“％s”的值“％s”已被用作另一个帐户的用户名');

define('I_ROWFILE_ERROR_MKDIR', '在路径“％s”内递归创建目录“％s”失败，尽管该路径是可写的');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', '在路径“％s”内递归创建目录“％s”失败，因为该路径不可写');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', '目标目录“%s”存在，但不可写');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', '无法处理不存在行的文件');

define('I_ROWM4D_NO_SUCH_FIELD', '字段“m4d”在“％s”实体中不存在');

define('I_UPLOAD_ERR_INI_SIZE', '字段“%s”中上传的文件超出了 php.ini 中的 upload_max_filesize 指令');
define('I_UPLOAD_ERR_FORM_SIZE', '字段“%s”中上传的文件超出了指定的 MAX_FILE_SIZE 指令 ');
define('I_UPLOAD_ERR_PARTIAL', '字段“%s”中的上传文件仅部分上传');
define('I_UPLOAD_ERR_NO_FILE', '字段“%s”中未上传任何文件');
define('I_UPLOAD_ERR_NO_TMP_DIR', '服务器上缺少用于存储文件的临时文件夹，已上传至字段“%s”');
define('I_UPLOAD_ERR_CANT_WRITE', '无法将字段“%s”中上传的文件写入服务器硬盘');
define('I_UPLOAD_ERR_EXTENSION', '服务器上运行的某个 php 扩展程序停止了字段“%s”中的文件上传');
define('I_UPLOAD_ERR_UNKNOWN', '由于未知错误，字段“%s”中的文件上传失败');

define('I_UPLOAD_ERR_REQUIRED', '尚无文件，您应该选择一个');
define('I_WGET_ERR_ZEROSIZE', '使用 Web-url 作为字段“%s”的文件源失败，因为该文件的大小为零');

define('I_FORM_UPLOAD_SAVETOHDD', '下载');
define('I_FORM_UPLOAD_ORIGINAL', '显示原文');
define('I_FORM_UPLOAD_NOCHANGE', '不用找了');
define('I_FORM_UPLOAD_DELETE', '删除');
define('I_FORM_UPLOAD_REPLACE', '代替');
define('I_FORM_UPLOAD_REPLACE_WITH', '和');
define('I_FORM_UPLOAD_NOFILE', '不');
define('I_FORM_UPLOAD_BROWSE', '浏览');
define('I_FORM_UPLOAD_MODE_TIP', '使用网络链接选择文件');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', '您的本地电脑文件..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', '文件位于网络链接..');

define('I_FORM_UPLOAD_ASIMG', '一个图像');
define('I_FORM_UPLOAD_ASOFF', '办公文件');
define('I_FORM_UPLOAD_ASDRW', '一幅画');
define('I_FORM_UPLOAD_ASARC', '档案');
define('I_FORM_UPLOAD_ASAUD', '音频');
define('I_FORM_UPLOAD_OFEXT', '有类型');
define('I_FORM_UPLOAD_INFMT', '在格式上');
define('I_FORM_UPLOAD_HSIZE', '有尺寸');
define('I_FORM_UPLOAD_NOTGT', '不大于');
define('I_FORM_UPLOAD_NOTLT', '不小于');
define('I_FORM_UPLOAD_FPREF', '照片 %s');

define('I_FORM_DATETIME_HOURS', '小时');
define('I_FORM_DATETIME_MINUTES', '分钟');
define('I_FORM_DATETIME_SECONDS', '秒');
define('I_OF', '的');
define('I_COMBO_MISMATCH_MAXSELECTED', '允许选择的最大选项数为');
define('I_COMBO_MISMATCH_DISABLED_VALUE', '字段“%s”中不可选选项“%s”');
define('I_COMBO_KEYWORD_NO_RESULTS', '使用此关键字未找到任何内容');
define('I_COMBO_ODATA_FIELD404', '字段“％s”既不是实字段也不是伪字段');
define('I_COMBO_GROUPBY_NOGROUP', '未设置分组');
define('I_COMBO_WAND_TOOLTIP', '使用在此字段中输入的标题在此列表中创建新选项');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', '未找到行');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', '当前部分的可用行范围');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', '，结合应用的搜索选项 -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', ' 不包含具有该 ID 的行');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', '排 ＃');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', '的 ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', '未找到行');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', '当前部分中可用的行的范围，');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', ' 通过应用搜索选项查看');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', ' - 不包含具有此类索引的行，但最近确实包含');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', '不');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '- 选择 - ');

define('I_ACTION_INDEX_KEYWORD_LABEL', '搜索…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', '在所有列上搜索');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', '小节');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '- 选择 - ');
define('I_ACTION_INDEX_SUBSECTIONS_NO', '不');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', '信息');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', '选择一行');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', '选项');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', '之间');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', '和');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', '从');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', '直到');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', '是的');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', '不');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', '无需清空');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', '选项已为空或根本没有使用');

define('I_ACTION_DELETE_CONFIRM_TITLE', '确认');
define('I_ACTION_DELETE_CONFIRM_MSG', '你确定你要删除');
define('I_ENTRY_TBQ', '条目,条目,条目');

define('I_SOUTH_PLACEHOLDER_TITLE', '此选项卡的内容将在单独的窗口中打开');
define('I_SOUTH_PLACEHOLDER_GO', '去');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', ' 那扇窗户');
define('I_SOUTH_PLACEHOLDER_GET', '获取内容');
define('I_SOUTH_PLACEHOLDER_BACK', ' 回到这里');

define('I_DEMO_ACTION_OFF', '此操作在演示模式下关闭');

define('I_MCHECK_REQ', '字段“％s”-是必填项');
define('I_MCHECK_REG', '字段“％s”的值“％s”-格式无效');
define('I_MCHECK_KEY', '未通过键“％s”找到类型为“％s”的对象');
define('I_MCHECK_EQL', '错误值');
define('I_MCHECK_DIS', '字段“％s”的值“％s”-在禁用值列表中');
define('I_MCHECK_UNQ', '字段“%s”的值“%s”不唯一。它应该是唯一的。');
define('I_JCHECK_REQ', '参数“％s”-未给出');
define('I_JCHECK_REG', '参数“％s”的值“％s”-格式无效');
define('I_JCHECK_KEY', '未通过键“％s”找到类型为“％s”的对象');
define('I_JCHECK_EQL', '错误值');
define('I_JCHECK_DIS', '参数“％s”的值“％s”-在禁用值列表中');
define('I_JCHECK_UNQ', '参数“%s”的值“%s”不唯一。它应该是唯一的。');

define('I_PRIVATE_DATA', '*私人数据*');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', '昨天');
define('I_WHEN_TOD', '今天');
define('I_WHEN_TOM', '明天');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', '在');
define('I_WHEN_WD_ON2', '在');
define('I_WHEN_TM_AT', '在');

define('I_LANG_LAST', '不允许删除最后剩余的“%s”条目');
define('I_LANG_CURR', '不允许删除当前使用的翻译');
define('I_LANG_FIELD_L10N_DENY', '无法为字段“%s”启用本地化');
define('I_LANG_QYQN_CONFIRM', '如果您想要 %s 语言“%s”表示分数“%s”，请按“%s”。如果您只需要使其与当前状态保持一致 - 请按“%s”');
define('I_LANG_QYQN_CONFIRM2', '对于分数“%s”，语言“%s”将手动标记为“%s”。继续吗？');
define('I_LANG_QYQN_SELECT', '选择源语言');
define('I_LANG_EXPORT_HEADER', '选择导出参数');
define('I_LANG_IMPORT_HEADER', '选择导入参数');
define('I_LANG_NOT_SUPPORTED', '目前不支持');
define('I_LANG_SELECT_CURRENT', '选择分数“%s”的当前语言');
define('I_LANG_MIGRATE_META', '准备字段');
define('I_LANG_MIGRATE_DATA', '迁移标题');
define('I_ADD', '添加');
define('I_DELETE', '删除');
define('I_SECTION_CLONE_SELECT_PARENT', '选择父部分，该部分应从属于所选部分的重复部分');

define('I_TILE_NOTHUMB', '没有拇指');
define('I_TILE_NOFILE', '无文件');

define('I_CHANGELOG_FIELD', '做了哪些改变');
define('I_CHANGELOG_WAS', '曾是');
define('I_CHANGELOG_NOW', '成为');

define('I_WEEK', '星期');
define('I_TODAY', '今天');

define('I_PRINT', '打印');

define('I_NUM2STR_ZERO', '零');
define('I_NUM2STR_1TO9', '一、二、三、四、五、六、七、八、九');
define('I_NUM2STR_1TO9_2', '一、二、三、四、五、六、七、八、九');
define('I_NUM2STR_10TO19', '十、十一、十二、十三、十四、十五、十六、十七、十八、十九');
define('I_NUM2STR_20TO90', '二十、三十、四十、五十、六十、七十、八十、九十');
define('I_NUM2STR_100TO900', '一百,二百,三百,四百,五百,六百,七百,八百,九百');
define('I_NUM2STR_TBQ_KOP', '戈比,戈比,戈比');
define('I_NUM2STR_TBQ_RUB', '卢布,卢布,卢布');
define('I_NUM2STR_TBQ_THD', '千，千，千');
define('I_NUM2STR_TBQ_MLN', '百万,百万,百万');
define('I_NUM2STR_TBQ_BLN', '十亿，十亿，十亿');

define('I_AGO_SECONDS', '秒,秒');
define('I_AGO_MINUTES', '分钟，分钟');
define('I_AGO_HOURS', '小时,小时');
define('I_AGO_DAYS', '天，天');
define('I_AGO_WEEKS', '周，周');
define('I_AGO_MONTHS', '月，月');
define('I_AGO_YEARS', '年，年');

define('I_PANEL_GRID', '网格');
define('I_PANEL_PLAN', '日历');
define('I_PANEL_TILE', '画廊');

define('I_ENT_AUTHOR_SPAN', '创建');
define('I_ENT_AUTHOR_ROLE', '角色');
define('I_ENT_AUTHOR_USER', '用户');
define('I_ENT_AUTHOR_TIME', '约会时间');
define('I_ENT_TOGGLE', '切换');
define('I_ENT_TOGGLE_Y', '打开');
define('I_ENT_TOGGLE_N', '关掉');
define('I_ENT_EXTENDS_OTHER', '带有 php-model 的文件存在，但其中指定 %s 为父类');

define('I_SELECT_CFGFIELD', '选择要为此字段添加的配置参数');
define('I_SELECT_PLEASE', '请选择');
define('I_L10N_TOGGLE_ACTION_Y', '如果您想要对操作“%s”进行 %s 本地化，请按“%s”。');
define('I_L10N_TOGGLE_MATCH', '否则，如果您只需要明确指定本地化状态的值以使其与当前实际情况相匹配 - 请按“％s”');
define('I_L10N_TOGGLE_ACTION_EXPL', '对于操作“%s”，本地化状态将明确设置为“%s”。继续吗？');
define('I_L10N_TOGGLE_ACTION_LANG_CURR', '选择当前操作的语言“%s”');
define('I_L10N_TOGGLE_ACTION_LANG_KEPT', '选择要为操作“%s”保留的语言');

define('I_L10N_TOOGLE_FIELD_DENIED', '不允许打开/关闭依赖字段的本地化');
define('I_L10N_TOGGLE_FIELD_Y', '如果您想要对字段“%s”进行 %s 本地化，请按“%s”。');
define('I_L10N_TOGGLE_FIELD_EXPL', '对于字段“%s”，本地化状态将明确设置为“%s”。继续吗？');
define('I_L10N_TOGGLE_FIELD_LANG_CURR', '选择字段“%s”的当前语言');
define('I_L10N_TOGGLE_FIELD_LANG_KEPT', '选择字段“%s”要保留的语言');

define('I_GRID_COLOR_BREAK_INCOMPAT', '此功能仅适用于数字列');
define('I_REALTIME_CONTEXT_AUTODELETE_ONLY', '上下文类型的条目无法手动删除');
define('I_ONDELETE_RESTRICT', '由于为实体“%s”（`%s`.`%s`）中的字段“%s”配置了 ON DELETE RESTRICT 规则，因此删除受到限制，因为该实体中至少有 1 条记录在该字段中有值，该值是您要删除的记录的直接或级联引用');

define('I_LANG_WORD_DETECT_ONLY', '做初步检查吗？');
define('I_SECTION_ROWSET_MIN_CONF', '为了激活面板 %s，请指定以下最低配置：');
define('I_SECTION_CONF_SETUP', '正在应用配置..');
define('I_SECTION_CONF_SETUP_DONE', '配置已成功应用');
define('I_NOTICE_HIDE_ALL', '全部藏起来');
define('I_RECORD_DELETED', '该记录已被删除');

define('I_FILE_EXISTS', '文件已存在：%s');
define('I_FILE_CREATED', '创建文件：%s');
define('I_CLASS_404', '未找到类 %s');
define('I_FORMAT_HIS', '参数 %s 的值 "%s" 应为 hh:mm:ss 格式的时间');
define('I_GAPI_RESPONSE', 'Google Cloud Translate API 响应：%s');
define('I_GAPI_KEY_REQUIRED', '请在此处指定 API 密钥。');
define('I_QUEUE_STARTED', '开始');
define('I_QUEUE_RESUMED', '恢复');
define('I_QUEUE_COMPLETED', '完全的');
define('I_PLAN_ITEM_LOAD_FAIL', '无法将 %s %s 加载到计划中');
define('I_SECTIONS_TPLCTLR_404', '未找到模板控制器文件');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_JS', 'js-controller 的文件存在，但指定的父类为 %s');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_PHP', 'php-controller 文件存在，但指定的父类为 %s');
define('I_SECTIONS_CTLR_PARENT_404', '无法在 js-controller 文件中查找父类名称');
define('I_SECTIONS_CTLR_EMPTY_JS', 'js-controller 文件为空');
define('I_ENTITIES_TPLMDL_404', '未找到模板模型文件');
define('I_ENTITIES_TPLMDLROW_404', '未找到模型的 rowClass 模板文件');
define('I_FILTERS_FEATURE_UNAPPLICABLE', '此功能仅适用于具有外键字段的过滤器');
define('I_LANG_NEW_QTY', 'Google 支持的新语言数量：%s');
define('I_SCNACS_TPL_404', '缺少操作模板文件：%s');
define('I_MDL_CHLOG_NO_REVERT_1', '对于只读或隐藏的字段，禁用还原');
define('I_MDL_CHLOG_NO_REVERT_2', '仅对类型为 %s 和 %s 的字段启用恢复');
define('I_MDL_CHLOG_NO_REVERT_3', '禁止恢复外键字段');
define('I_MDL_GRID_PARENT_GROUP_DIFFERS', '父条目位于另一个组中');
define('I_EXPORT_CERTAIN', '如果仅需导出某些字段 - 请选择');
define('I_SCHED_UNABLE_SET_REST', '无法在时间表内创建非工作时间空间');
define('I_MDL_ADMIN_VK_1', '页面地址应以 https://vk.com/ 开头');
define('I_MDL_ADMIN_VK_2', '该页面在 VK 中不存在');
define('I_MDL_ADMIN_VK_3', 'VK 中的此页面不是用户页面');

define('I_FIELD_DRAG_CLICK', '拖动以重新排序/单击以查看详细信息');
define('I_ENTITY_CLICK', '单击以查看详细信息');
define('I_ACTION_FORM', '细节');
