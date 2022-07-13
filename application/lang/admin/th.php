<?php
define('I_URI_ERROR_SECTION_FORMAT', 'ชื่อส่วนผิดรูปแบบ');
define('I_URI_ERROR_ACTION_FORMAT', 'ชื่อการกระทำอยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_URI_ERROR_ID_FORMAT', 'Uri param \'id\' ควรมีค่าจำนวนเต็มบวก');
define('I_URI_ERROR_CHUNK_FORMAT', 'อัน URI อันหนึ่งมีรูปแบบที่ไม่ถูกต้อง');

define('I_LOGIN_BOX_USERNAME', 'ชื่อผู้ใช้');
define('I_LOGIN_BOX_PASSWORD', 'รหัสผ่าน');
define('I_LOGIN_BOX_REMEMBER', 'จำ');
define('I_LOGIN_BOX_ENTER', 'เข้าสู่');
define('I_LOGIN_BOX_RESET', 'ตั้งค่าใหม่');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'ความผิดพลาด');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'ไม่ได้ระบุชื่อผู้ใช้');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'ไม่ได้ระบุรหัสผ่าน');
define('I_LOGIN_BOX_LANGUAGE', 'ภาษา');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'บัญชีดังกล่าวไม่มีอยู่');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'รหัสผ่านผิด');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'บัญชีนี้ถูกปิด');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'บัญชีนี้เป็นประเภทที่ถูกปิด');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'ยังไม่มีส่วนที่สามารถเข้าถึงได้โดยบัญชีนี้');

define('I_THROW_OUT_ACCOUNT_DELETED', 'บัญชีของคุณเพิ่งถูกลบ');
define('I_THROW_OUT_PASSWORD_CHANGED', 'รหัสผ่านของคุณเพิ่งถูกเปลี่ยน');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'บัญชีของคุณเพิ่งถูกปิดแล้ว');
define('I_THROW_OUT_ROLE_IS_OFF', 'บัญชีของคุณเป็นประเภทที่เพิ่งถูกปิด');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'ขณะนี้ไม่มีส่วนที่เหลือสำหรับคุณ');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'ไม่มีส่วนดังกล่าว');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'ส่วนถูกปิด');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'การกระทำดังกล่าวไม่มีอยู่จริง');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'การกระทำนี้ถูกปิด');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'การกระทำนี้ไม่มีอยู่ในส่วนนี้');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'การกระทำนี้ถูกปิดในส่วนนี้');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'คุณไม่มีสิทธิ์ในการกระทำนี้ในส่วนนี้');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'หนึ่งในส่วนหลักสำหรับส่วนปัจจุบัน - ถูกปิด');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'การเพิ่มแถวถูก จำกัด ในส่วนนี้');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'แถวที่มีรหัสดังกล่าวไม่มีอยู่ในส่วนนี้');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'การดำเนินการ "%s" สามารถเข้าถึงได้ แต่สถานการณ์ปัจจุบันไม่เหมาะกับการดำเนินการ');

define('I_DOWNLOAD_ERROR_NO_ID', 'ตัวระบุแถวอาจไม่ถูกระบุหรือไม่ใช่ตัวเลข');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'ไม่ได้ระบุตัวระบุฟิลด์หรือไม่ใช่ตัวเลข');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'ไม่มีฟิลด์ที่มีตัวระบุดังกล่าว');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'ฟิลด์นี้ไม่ได้จัดการกับไฟล์');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'ไม่มีแถวที่มีตัวระบุดังกล่าว');
define('I_DOWNLOAD_ERROR_NO_FILE', 'ไม่มีไฟล์ถูกอัปโหลดในฟิลด์นี้สำหรับแถวนี้');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'การรับข้อมูลไฟล์ล้มเหลว');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'ชื่อว่างสำหรับค่าเริ่มต้น "%s"');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'ค่า "%s" มีอยู่แล้วในรายการค่าที่อนุญาต');
define('I_ENUMSET_ERROR_VALUE_LAST', 'ค่า "%s" เป็นค่าสุดท้ายที่เหลืออยู่และไม่สามารถลบได้');

define('I_YES', 'ใช่');
define('I_NO', 'ไม่');
define('I_ERROR', 'ความผิดพลาด');
define('I_MSG', 'ข่าวสาร');
define('I_OR', 'หรือ');
define('I_AND', 'และ');
define('I_BE', 'เป็น');
define('I_FILE', 'ไฟล์');
define('I_SHOULD', 'ควร');

define('I_HOME', 'บ้าน');
define('I_LOGOUT', 'ออกจากระบบ');
define('I_MENU', 'เมนู');
define('I_CREATE', 'สร้างใหม่');
define('I_BACK', 'กลับ');
define('I_SAVE', 'บันทึก');
define('I_CLOSE', 'ปิด');
define('I_TOTAL', 'รวม');
define('I_EXPORT_EXCEL', 'ส่งออกเป็นสเปรดชีต Excel');
define('I_EXPORT_PDF', 'ส่งออกเป็นเอกสาร PDF');
define('I_NAVTO_ROWSET', 'กลับไปที่ rowset');
define('I_NAVTO_ID', 'ไปที่แถวโดย ID');
define('I_NAVTO_RELOAD', 'รีเฟรช');
define('I_AUTOSAVE', 'บันทึกอัตโนมัติก่อนข้ามไป');
define('I_NAVTO_RESET', 'การเปลี่ยนแปลงย้อนกลับ');
define('I_NAVTO_PREV', 'ไปที่แถวก่อนหน้า');
define('I_NAVTO_SIBLING', 'ไปที่แถวอื่น');
define('I_NAVTO_NEXT', 'ไปที่แถวถัดไป');
define('I_NAVTO_CREATE', 'ไปที่การสร้างแถวใหม่');
define('I_NAVTO_NESTED', 'ไปที่วัตถุที่ซ้อนกัน');
define('I_NAVTO_ROWINDEX', 'ไปที่แถวโดย #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'ต้องระบุช่อง "%s"');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'ค่าของฟิลด์ "%s" ไม่สามารถเป็นวัตถุได้');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'ค่าของฟิลด์ "%s" ต้องไม่เป็นอาร์เรย์');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'ค่า "%s" ของฟิลด์ "%s" ไม่ควรมากกว่าทศนิยม 11 หลัก');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'ค่า "%s" ของฟิลด์ "%s" ไม่อยู่ในรายการค่าที่อนุญาต');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'ฟิลด์ "%s" มีค่าที่ไม่ได้รับอนุญาต: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'ค่า "%s" ของฟิลด์ "%s" มีอย่างน้อยหนึ่งรายการที่ไม่ใช่ทศนิยมที่ไม่เป็นศูนย์');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็น "1" หรือ "0"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็นสีในรูปแบบ #rrggbb หรือ hue # rrggbb');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'ค่า "%s" ของฟิลด์ "%s" ไม่ใช่วันที่');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'ค่า "%s" ของฟิลด์ "%s" เป็นวันที่ไม่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็นเวลาในรูปแบบ HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'ค่า "%s" ของฟิลด์ "%s" ไม่ใช่เวลาที่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'ค่า "%s" ที่กล่าวถึงในฟิลด์ "%s" เป็นวันที่ - ไม่ใช่วันที่');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'วันที่ "%s" ที่กล่าวถึงในฟิลด์ "%s" - ไม่ใช่วันที่ที่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'ค่า "%s" ที่กล่าวถึงในฟิลด์ "%s" เป็นเวลา - ควรเป็นเวลาในรูปแบบ HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'เวลา "%s" ที่กล่าวถึงในฟิลด์ "%s" - ไม่ใช่เวลาที่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็นตัวเลขที่มีตัวเลข 4 หรือน้อยกว่าในส่วนจำนวนเต็มเสริมด้วยเครื่องหมาย "-" และ 2 หรือน้อยกว่า / ไม่มีตัวเลขในส่วนที่เป็นเศษส่วน');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็นตัวเลขที่มีตัวเลข 8 หรือน้อยกว่าในส่วนจำนวนเต็มเสริมด้วยเครื่องหมาย "-" และ 2 หรือน้อยกว่า / ไม่มีตัวเลขในส่วนที่เป็นเศษส่วน');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็นตัวเลขที่มี 10 หรือน้อยกว่าตัวเลขในส่วนจำนวนเต็มเสริมด้วยเครื่องหมาย "-" และ 3 หรือน้อยกว่า / ไม่มีตัวเลขในส่วนที่เป็นเศษส่วน');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'ค่า "%s" ของฟิลด์ "%s" ควรเป็นปีในรูปแบบ YYYY');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'ไม่มีอะไรจะบันทึก');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'คุณไม่ได้ทำการเปลี่ยนแปลงใด ๆ');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'ไม่สามารถตั้งค่าแถวปัจจุบันเป็นพาเรนต์สำหรับตัวเองในฟิลด์ "%s"');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'แถวที่มี id "%s" ที่ระบุในฟิลด์ "%s", - ไม่มีอยู่ดังนั้นจึงไม่สามารถตั้งค่าเป็นแถวหลักได้');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'แถว "%s" ที่ระบุในฟิลด์ "%s", - เป็นแถวลูก / ลูกหลานสำหรับแถวปัจจุบัน "%s" ดังนั้นจึงไม่สามารถตั้งค่าเป็นแถวหลักได้');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'ในระหว่างการร้องขอของคุณหนึ่งในการดำเนินการในรายการประเภท "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '- ส่งคืนข้อผิดพลาดด้านล่าง');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'ต้องระบุช่อง "%s"');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'ค่า "%s" ของฟิลด์ "%s" ถูกใช้เป็นชื่อผู้ใช้สำหรับบัญชีอื่นแล้ว');

define('I_ROWFILE_ERROR_MKDIR', 'การสร้างไดเรกทอรีซ้ำ "%s" ภายในเส้นทาง "%s" ล้มเหลวแม้จะอยู่บนเส้นทางนั้นก็เขียนได้');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'การสร้างไดเรกทอรีซ้ำ "%s" ภายในเส้นทาง "%s" ล้มเหลวเนื่องจากเส้นทางนั้นไม่สามารถเขียนได้');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'มีไดเรกทอรีเป้าหมาย "%s" อยู่ แต่ไม่สามารถเขียนได้');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'ไม่มีความเป็นไปได้ที่จะจัดการกับไฟล์ของแถวที่ไม่มีอยู่');

define('I_ROWM4D_NO_SUCH_FIELD', 'ไม่มีฟิลด์ `m4d` อยู่ภายในเอนทิตี"% s "');

define('I_UPLOAD_ERR_INI_SIZE', 'ไฟล์ที่อัปโหลดในฟิลด์ "%s" มีขนาดเกินกว่าคำสั่ง upload_max_filesize ใน php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'ไฟล์ที่อัปโหลดในฟิลด์ "%s" เกินคำสั่ง MAX_FILE_SIZE ที่ระบุไว้');
define('I_UPLOAD_ERR_PARTIAL', 'ไฟล์ที่อัปโหลดในฟิลด์ "%s" ถูกอัปโหลดเพียงบางส่วนเท่านั้น');
define('I_UPLOAD_ERR_NO_FILE', 'ไม่มีการอัปโหลดไฟล์ในฟิลด์ "%s"');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'ไม่มีโฟลเดอร์ชั่วคราวบนเซิร์ฟเวอร์สำหรับจัดเก็บไฟล์อัพโหลดในฟิลด์ "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'ไม่สามารถเขียนไฟล์อัพโหลดในช่อง "%s" ไปยังฮาร์ดไดรฟ์ของเซิร์ฟเวอร์');
define('I_UPLOAD_ERR_EXTENSION', 'การอัปโหลดไฟล์ในฟิลด์ "%s" หยุดลงโดยหนึ่งใน php extension ซึ่งทำงานบนเซิร์ฟเวอร์');
define('I_UPLOAD_ERR_UNKNOWN', 'การอัปโหลดไฟล์ในฟิลด์ "%s" ล้มเหลวเนื่องจากข้อผิดพลาดที่ไม่รู้จัก');

define('I_UPLOAD_ERR_REQUIRED', 'ยังไม่มีไฟล์คุณควรเลือกหนึ่งไฟล์');
define('I_WGET_ERR_ZEROSIZE', 'การใช้งานเว็บ URL เป็นแหล่งของไฟล์สำหรับฟิลด์ "%s" ล้มเหลวเนื่องจากไฟล์นั้นมีขนาดเป็นศูนย์');

define('I_FORM_UPLOAD_SAVETOHDD', 'ดาวน์โหลด');
define('I_FORM_UPLOAD_ORIGINAL', 'แสดงต้นฉบับ');
define('I_FORM_UPLOAD_NOCHANGE', 'ไม่มีการเปลี่ยนแปลง');
define('I_FORM_UPLOAD_DELETE', 'ลบ');
define('I_FORM_UPLOAD_REPLACE', 'แทนที่');
define('I_FORM_UPLOAD_REPLACE_WITH', 'กับ');
define('I_FORM_UPLOAD_NOFILE', 'ไม่');
define('I_FORM_UPLOAD_BROWSE', 'หมวด');
define('I_FORM_UPLOAD_MODE_TIP', 'ใช้เว็บลิงค์เพื่อเลือกไฟล์');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'ไฟล์ PC ในเครื่องของคุณ ..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'ไฟล์ที่เว็บลิงค์ ..');

define('I_FORM_UPLOAD_ASIMG', 'รูปภาพ');
define('I_FORM_UPLOAD_ASOFF', 'เอกสารสำนักงาน');
define('I_FORM_UPLOAD_ASDRW', 'ภาพวาด');
define('I_FORM_UPLOAD_ASARC', 'เก็บถาวร');
define('I_FORM_UPLOAD_OFEXT', 'มีประเภท');
define('I_FORM_UPLOAD_INFMT', 'ในรูปแบบ');
define('I_FORM_UPLOAD_HSIZE', 'มีขนาด');
define('I_FORM_UPLOAD_NOTGT', 'ไม่มากกว่า');
define('I_FORM_UPLOAD_NOTLT', 'ไม่น้อยกว่า');
define('I_FORM_UPLOAD_FPREF', 'รูปภาพ %s');

define('I_FORM_DATETIME_HOURS', 'ชั่วโมง');
define('I_FORM_DATETIME_MINUTES', 'นาที');
define('I_FORM_DATETIME_SECONDS', 'วินาที');
define('I_COMBO_OF', 'ของ');
define('I_COMBO_MISMATCH_MAXSELECTED', 'จำนวนตัวเลือกที่อนุญาตสูงสุดคือ');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'ตัวเลือก "%s" ไม่พร้อมใช้งานสำหรับการเลือกในฟิลด์ "%s"');
define('I_COMBO_KEYWORD_NO_RESULTS', 'ไม่พบสิ่งใดโดยใช้คำหลักนี้');
define('I_COMBO_ODATA_FIELD404', 'ฟิลด์ "%s" ไม่ใช่ฟิลด์จริงหรือฟิลด์หลอก');
define('I_COMBO_GROUPBY_NOGROUP', 'ไม่ได้ตั้งกลุ่ม');
define('I_COMBO_WAND_TOOLTIP', 'สร้างตัวเลือกใหม่ในรายการนี้โดยใช้ชื่อป้อนในฟิลด์นี้');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'ไม่พบแถว');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'ขอบเขตของส่วนปัจจุบันของแถวที่มีอยู่');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', 'ในมุมมองที่มีตัวเลือกการค้นหาที่ใช้ -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', 'ไม่มีแถวที่มี ID ดังกล่าว');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'แถว #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'ของ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'ไม่พบแถว');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'ขอบเขตของแถวที่มีอยู่ในส่วนปัจจุบัน');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', 'ในมุมมองที่มีตัวเลือกการค้นหาที่ใช้');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '- ไม่มีแถวที่มีดัชนีดังกล่าว แต่เพิ่งเกิดขึ้น');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'ไม่');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--เลือก--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'ค้นหา…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'ค้นหาในทุกคอลัมน์');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'ส่วนย่อย');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--เลือก--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'ไม่');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'ข่าวสาร');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'เลือกแถว');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'ตัวเลือก');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'ระหว่าง');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'และ');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'จาก');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'จนกระทั่ง');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'ใช่');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'ไม่');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'ไม่มีอะไรจะว่างเปล่า');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'ตัวเลือกนั้นว่างเปล่าหรือไม่ได้ใช้เลย');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'ยืนยัน');
define('I_ACTION_DELETE_CONFIRM_MSG', 'คุณแน่ใจหรือว่าต้องการลบ');

define('I_SOUTH_PLACEHOLDER_TITLE', 'เนื้อหาของแท็บนี้เปิดในหน้าต่างแยกต่างหาก');
define('I_SOUTH_PLACEHOLDER_GO', 'ไปที่');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', 'หน้าต่างนั้น');
define('I_SOUTH_PLACEHOLDER_GET', 'รับเนื้อหา');
define('I_SOUTH_PLACEHOLDER_BACK', 'กลับมาที่นี่');

define('I_DEMO_ACTION_OFF', 'การกระทำนี้ถูกปิดในโหมดสาธิต');

define('I_MCHECK_REQ', 'ช่อง "%s" - ต้องระบุ');
define('I_MCHECK_REG', 'ค่า "%s" ของฟิลด์ "%s" - อยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_MCHECK_KEY', 'ไม่พบวัตถุประเภท "%s" โดยรหัส "%s"');
define('I_MCHECK_EQL', 'ค่าผิด');
define('I_MCHECK_DIS', 'ค่า "%s" ของฟิลด์ "%s" - อยู่ในรายการค่าที่ปิดใช้งาน');
define('I_MCHECK_UNQ', 'ค่า "%s" ของฟิลด์ "%s" - ไม่ซ้ำกัน มันควรจะไม่ซ้ำกัน');
define('I_JCHECK_REQ', 'ไม่ได้รับพารามิเตอร์ "%s" -');
define('I_JCHECK_REG', 'ค่า "%s" ของพารามิเตอร์ "%s" - อยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_JCHECK_KEY', 'ไม่พบวัตถุประเภท "%s" โดยรหัส "%s"');
define('I_JCHECK_EQL', 'ค่าผิด');
define('I_JCHECK_DIS', 'ค่า "%s" ของพารามิเตอร์ "%s" - อยู่ในรายการค่าที่ปิดใช้งาน');
define('I_JCHECK_UNQ', 'ค่า "%s" ของพารามิเตอร์ "%s" - ไม่ซ้ำกัน มันควรจะไม่ซ้ำกัน');

define('I_PRIVATE_DATA', '* ข้อมูลส่วนตัว *');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', 'เมื่อวาน');
define('I_WHEN_TOD', 'ในวันนี้');
define('I_WHEN_TOM', 'วันพรุ่งนี้');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'บน');
define('I_WHEN_WD_ON2', 'บน');
define('I_WHEN_TM_AT', 'ที่');

define('I_LANG_LAST', 'ไม่อนุญาตให้ลบรายการ "%s" สุดท้ายที่เหลืออยู่');
define('I_LANG_CURR', 'ไม่อนุญาตให้ลบการแปลที่ใช้เป็นการแปลปัจจุบันของคุณ');
define('I_LANG_FIELD_L10N_DENY', 'ไม่สามารถเปิดการแปลเป็นภาษาท้องถิ่นสำหรับฟิลด์ "%s"');