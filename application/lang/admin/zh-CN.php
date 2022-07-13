<?php
define('I_URI_ERROR_SECTION_FORMAT', '节名称格式错误');
define('I_URI_ERROR_ACTION_FORMAT', '动作名称格式错误');
define('I_URI_ERROR_ID_FORMAT', '参数“ id”必须为正整数');
define('I_URI_ERROR_CHUNK_FORMAT', 'URI的一部分格式错误');

define('I_LOGIN_BOX_USERNAME', '用户');
define('I_LOGIN_BOX_PASSWORD', '密码');
define('I_LOGIN_BOX_REMEMBER', '记得');
define('I_LOGIN_BOX_ENTER', '输入');
define('I_LOGIN_BOX_RESET', '重启');
define('I_LOGIN_ERROR_MSGBOX_TITLE', '错误');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', '未指定用户名');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', '未指定密码');
define('I_LOGIN_BOX_LANGUAGE', '舌');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', '没有这样的帐户');
define('I_LOGIN_ERROR_WRONG_PASSWORD', '您输入了错误的密码');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', '此帐户已被禁用。');
define('I_LOGIN_ERROR_role_IS_OFF', '此帐户类型已被禁用。');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', '系统中尚无此帐户可用的分区。');

define('I_THROW_OUT_ACCOUNT_DELETED', '您的帐户刚刚被删除。');
define('I_THROW_OUT_PASSWORD_CHANGED', '您的密码刚刚被更改。');
define('I_THROW_OUT_ACCOUNT_IS_OFF', '您的帐户刚刚被禁用。');
define('I_THROW_OUT_role_IS_OFF', '您的帐户类型刚刚被禁用');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', '系统中没有可用的分区');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', '没有这样的部分');
define('I_ACCESS_ERROR_SECTION_IS_OFF', '本节关闭。');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', '没有这样的动作');
define('I_ACCESS_ERROR_ACTION_IS_OFF', '此操作已关闭。');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', '本节中没有此类操作。');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', '此操作在本节中关闭。');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', '您无权在此部分中执行此操作。');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', '此部分的上游部分之一被禁用。');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', '本节中没有创建条目的权利。');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', '本节中没有具有此ID的条目');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', '动作\'％s\'可用，但在当前情况下不可用');

define('I_DOWNLOAD_ERROR_NO_ID', '对象标识符未指定或不是数字');
define('I_DOWNLOAD_ERROR_NO_FIELD', '字段标识符未指定或不是数字');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', '没有带有此标识符的字段');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', '具有此标识符的字段不适用于文件');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', '没有使用此标识符的对象');
define('I_DOWNLOAD_ERROR_NO_FILE', '上传到指定对象的指定字段的文件-不存在');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', '无法获取文件信息。');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', '默认值\'％s\'的标题');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', '值“％s”已在可能值的列表中');
define('I_ENUMSET_ERROR_VALUE_LAST', '值“％s”是可能列表中的最后一个剩余值，因此无法删除');

define('I_YES', '是');
define('I_NO', '没有');
define('I_ERROR', '错误');
define('I_MSG', '信息');
define('I_OR', '要么');
define('I_AND', '和');
define('I_BE', '成为');
define('I_FILE', '文件');
define('I_SHOULD', '应该');

define('I_HOME', '开始');
define('I_LOGOUT', '输出量');
define('I_MENU', '菜单');
define('I_CREATE', '建立新讯息');
define('I_BACK', '回来');
define('I_SAVE', '救');
define('I_CLOSE', '关');
define('I_TOTAL', '总');
define('I_EXPORT_EXCEL', '导出到Excel');
define('I_EXPORT_PDF', '导出为PDF');
define('I_NAVTO_ROWSET', '返回目录');
define('I_NAVTO_ID', '按ID记录');
define('I_NAVTO_RELOAD', '刷新');
define('I_AUTOSAVE', '转换前自动保存');
define('I_NAVTO_RESET', '取消变更');
define('I_NAVTO_PREV', '转到上一篇文章');
define('I_NAVTO_SIBLING', '转到任何其他条目');
define('I_NAVTO_NEXT', '转到下一篇文章');
define('I_NAVTO_CREATE', '转到创建新帖子');
define('I_NAVTO_NESTED', '转到嵌套条目列表');
define('I_NAVTO_ROWINDEX', '转到记录');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', '字段“％s”为必填项');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', '字段“％s”的值不能是对象');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', '字段“％s”的值不能是数组');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', '字段“％s”的值“％s”必须是一个最大为11位数字的整数');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', '字段“％s”的值“％s”不在有效值列表中');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', '字段“％s”包含无效值：“％s”');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', '字段“％s”的值“％s”包含至少一个不是非零整数的元素');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', '字段“％s”的值“％s”必须为“ 1”或“ 0”');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', '字段“％s”的值“％s”不是#rrggbb或色调#rrggbb格式的颜色');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', '字段“％s”的值“％s”不是日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', '字段“％s”的值“％s”不是有效日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', '字段“％s”的值“％s”不是时间，格式为HH：MM：SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', '字段“％s”的时间“％s”不是有效时间');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', '在字段“％s”中指定为日期的值“％s”不是日期');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', '在字段“％s”中指定的日期“％s”-必须正确');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', '在字段“％s”中指定的值“％s”不是时间');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', '在字段“％s”中指定的时间“％s”-必须正确');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', '字段“％s”的值“％s”必须是整数部分不超过5位且小数部分不超过2位的数字');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', '字段“％s”的值“％s”必须是整数部分不超过8位且小数部分不超过2位的数字');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', '字段“％s”的值“％s”应为整数部分不超过10位的数字，可能带有“-”，且小数部分不得超过3');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', '字段“％s”的值“％s”不是YYYY格式的年份');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', '没什么可保存的');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', '您尚未进行任何更改。');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', '无法在“％s”字段中将当前记录指定为其自身的父级');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', '在字段“％s”中指定的标识为“％s”的记录不存在，因此不能选择为父记录');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', '在字段“％s”中指定的记录“％s”是当前记录“％s”的子级/下级，因此不能将其选择为父级');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', '根据您的请求，自动执行一项操作，尤其是在“');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '-发出以下错误');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', '字段“％s”为必填项');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', '在字段“％s”中指定的值“％s”已用作另一个帐户的用户名');

define('I_ROWFILE_ERROR_MKDIR', '尽管文件夹可写，但是在文件夹“％s”中创建目录“％s”失败');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', '在文件夹“％s”中创建目录“％s”失败，因为该文件夹不可写');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', '下载所需的目录“％s”-存在，但不可写');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', '无法使用与不存在的条目相关的文件');

define('I_ROWM4D_NO_SUCH_FIELD', '实体结构\'％s\'中不存在字段\'m4d\'');

define('I_UPLOAD_ERR_INI_SIZE', '在“％s”字段中选择要上传的文件的大小超过了php.ini配置文件的upload_max_filesize指令指定的最大大小');
define('I_UPLOAD_ERR_FORM_SIZE', '在“％s”字段中选择上载的文件大小超过了HTML表单中指定的MAX_FILE_SIZE值');
define('I_UPLOAD_ERR_PARTIAL', '在“％s”字段中选择上载的文件仅被部分接收');
define('I_UPLOAD_ERR_NO_FILE', '在“％s”字段中选择的文件-尚未上传');
define('I_UPLOAD_ERR_NO_TMP_DIR', '服务器上没有临时文件夹可从“％s”字段下载文件');
define('I_UPLOAD_ERR_CANT_WRITE', '无法在“％s”字段中选择要上传的文件写入服务器的硬盘驱动器');
define('I_UPLOAD_ERR_EXTENSION', '在服务器上运行的一种PHP扩展已停止从“％s”字段中加载文件');
define('I_UPLOAD_ERR_UNKNOWN', '由于未知错误，文件上传到\'％s\'失败');

define('I_UPLOAD_ERR_REQUIRED', '您必须选择一个文件');
define('I_WGET_ERR_ZEROSIZE', '使用Web链接将文件下载到\'％s\'失败，因为此文件为空');

define('I_FORM_UPLOAD_SAVETOHDD', '保存到磁盘');
define('I_FORM_UPLOAD_ORIGINAL', '显示原图');
define('I_FORM_UPLOAD_NOCHANGE', '离开');
define('I_FORM_UPLOAD_DELETE', '删除');
define('I_FORM_UPLOAD_REPLACE', '更换');
define('I_FORM_UPLOAD_REPLACE_WITH', '在');
define('I_FORM_UPLOAD_NOFILE', '失踪');
define('I_FORM_UPLOAD_BROWSE', '选择');
define('I_FORM_UPLOAD_MODE_TIP', '通过网页链接下载');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', '文件从您的电脑..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', '通过网页链接文件..');

define('I_FORM_UPLOAD_ASIMG', '图片');
define('I_FORM_UPLOAD_ASOFF', '文献');
define('I_FORM_UPLOAD_ASDRW', '图形布局');
define('I_FORM_UPLOAD_ASARC', '封存');
define('I_FORM_UPLOAD_OFEXT', '有一个扩展');
define('I_FORM_UPLOAD_INFMT', '格式');
define('I_FORM_UPLOAD_HSIZE', '有一个尺寸');
define('I_FORM_UPLOAD_NOTGT', '不再');
define('I_FORM_UPLOAD_NOTLT', '不下');
define('I_FORM_UPLOAD_FPREF', '％S的照片');

define('I_FORM_DATETIME_HOURS', '小时');
define('I_FORM_DATETIME_MINUTES', '分钟');
define('I_FORM_DATETIME_SECONDS', '秒');
define('I_COMBO_OF', '的');
define('I_COMBO_MISMATCH_MAXSELECTED', '所选的最大选项数是');
define('I_COMBO_MISMATCH_DISABLED_VALUE', '在字段“％s”中无法选择值“％s”');
define('I_COMBO_KEYWORD_NO_RESULTS', '没有发现');
define('I_COMBO_ODATA_FIELD404', '字段“％s”既不是真实字段也不是伪字段');
define('I_COMBO_GROUPBY_NOGROUP', '没有隶属关系');
define('I_COMBO_WAND_TOOLTIP', '在此下拉列表中创建一个新选项<br>使用此字段中指示的名称');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', '未发现记录');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', '在本节下可用的一组记录中，');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', '根据当前的搜索选项');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', '-没有使用该ID的记录');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', '记录号');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', '的');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', '未发现记录');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', '在本节下可用的一组记录中，');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', '根据当前的搜索选项');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '-没有记录有这样的序列号，但是在加载表格时');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', '缺席');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '- 选择 -');

define('I_ACTION_INDEX_KEYWORD_LABEL', '搜索...');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', '搜索所有列');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', '小节');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '- 选择 -');
define('I_ACTION_INDEX_SUBSECTIONS_NO', '缺席');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', '信息');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', '选择行');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', '筛选器');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', '从');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', '之前');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'C');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', '通过');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', '是');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', '没有');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', '重置所有过滤器');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', '筛选器已重置或根本不使用');

define('I_ACTION_DELETE_CONFIRM_TITLE', '确认');
define('I_ACTION_DELETE_CONFIRM_MSG', '您确定要删除该条目吗');

define('I_SOUTH_PLACEHOLDER_TITLE', '该面板的内容在单独的窗口中打开。');
define('I_SOUTH_PLACEHOLDER_GO', '去');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', '到窗户');
define('I_SOUTH_PLACEHOLDER_GET', '返回');
define('I_SOUTH_PLACEHOLDER_BACK', '内容回到这里');

define('I_DEMO_ACTION_OFF', '在演示模式下，此操作被禁用。');

define('I_MCHECK_REQ', '栏位“％s”-必填');
define('I_MCHECK_REG', '字段“％s”的值“％s”格式错误');
define('I_MCHECK_KEY', '标识符为\'％s\'的类型为\'％s\'的对象-找不到');
define('I_MCHECK_EQL', '值不正确');
define('I_MCHECK_DIS', '字段“％s”的值“％s”在不可用值列表中');
define('I_MCHECK_UNQ', '字段“％s”的值“％s”-必须唯一');
define('I_JCHECK_REQ', '参数“％s”-是必需的');
define('I_JCHECK_REG', '参数“％s”的值“％s”格式错误');
define('I_JCHECK_KEY', '标识符为\'％s\'的类型为\'％s\'的对象-找不到');
define('I_JCHECK_EQL', '值不正确');
define('I_JCHECK_DIS', '参数“％s”的值“％s”在不可用值列表中');
define('I_JCHECK_UNQ', '参数“％s”的值“％s”-必须唯一');

define('I_PRIVATE_DATA', '*数据被隐藏*');

define('I_WHEN_DBY', '前天');
define('I_WHEN_YST', '昨天');
define('I_WHEN_TOD', '今天');
define('I_WHEN_TOM', '明天');
define('I_WHEN_DAT', '后天');
define('I_WHEN_WD_ON1', '在');
define('I_WHEN_WD_ON2', '在');
define('I_WHEN_TM_AT', '在');

define('I_LANG_LAST', '无法删除类型为\'％s\'的最后一条记录');
define('I_LANG_CURR', '您不能删除当前系统语言');
define('I_LANG_FIELD_L10N_DENY', '无法启用字段“％s”的本地化');