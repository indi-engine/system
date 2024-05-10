<?php
define('I_URI_ERROR_SECTION_FORMAT', 'ชื่อส่วนอยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_URI_ERROR_ACTION_FORMAT', 'ชื่อการดำเนินการอยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_URI_ERROR_ID_FORMAT', 'พารามิเตอร์ Uri \'id\' ควรมีค่าจำนวนเต็มบวก');
define('I_URI_ERROR_CHUNK_FORMAT', 'URI ชิ้นหนึ่งมีรูปแบบที่ไม่ถูกต้อง');

define('I_LOGIN_BOX_USERNAME', 'ชื่อผู้ใช้');
define('I_LOGIN_BOX_PASSWORD', 'รหัสผ่าน');
define('I_LOGIN_BOX_REMEMBER', 'จดจำ');
define('I_LOGIN_BOX_ENTER', 'เข้า');
define('I_LOGIN_BOX_RESET', 'รีเซ็ต');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'ข้อผิดพลาด');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'ไม่ได้ระบุชื่อผู้ใช้');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'ไม่ได้ระบุรหัสผ่าน');
define('I_LOGIN_BOX_LANGUAGE', 'ภาษา');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'ไม่มีบัญชีดังกล่าว');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'รหัสผ่านผิด');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'บัญชีนี้ถูกปิด');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'บัญชีนี้เป็นประเภทที่ถูกปิดอยู่');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'ยังไม่มีส่วนต่างๆ เข้าถึงได้โดยบัญชีนี้');

define('I_THROW_OUT_ACCOUNT_DELETED', 'บัญชีของคุณเพิ่งถูกลบไป');
define('I_THROW_OUT_PASSWORD_CHANGED', 'รหัสผ่านของคุณเพิ่งถูกเปลี่ยน');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'บัญชีของคุณเพิ่งถูกปิด');
define('I_THROW_OUT_ROLE_IS_OFF', 'บัญชีของคุณเป็นประเภทที่เพิ่งถูกปิด');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'ขณะนี้ไม่มีส่วนใดเหลือให้คุณเข้าถึงได้');
define('I_THROW_OUT_SESSION_EXPIRED', 'เซสชันของคุณไม่สามารถใช้งานได้อีกต่อไป ดำเนินการต่อเพื่อเข้าสู่ระบบใหม่หรือไม่?');

define('I_WS_CONNECTED', 'เชื่อมต่อแล้วใน %s ms');
define('I_WS_DISCONNECTED', 'ตัดการเชื่อมต่อ');
define('I_WS_RECONNECTING', 'กำลังเชื่อมต่อใหม่...');
define('I_WS_RECONNECTED', 'เชื่อมต่อใหม่ใน %s ms');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'ไม่มีส่วนดังกล่าว');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'ส่วนถูกปิด');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'การกระทำดังกล่าวไม่มีอยู่จริง');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'การดำเนินการนี้ปิดอยู่');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'ไม่มีการดำเนินการนี้ในส่วนนี้');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'การดำเนินการนี้จะปิดในส่วนนี้');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'คุณไม่มีสิทธิ์ในการดำเนินการนี้ในส่วนนี้');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'หนึ่งในส่วนหลักสำหรับส่วนปัจจุบัน - ปิดอยู่');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'การเพิ่มแถวถูกจำกัดในส่วนนี้');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'ไม่มีแถวที่มีรหัสดังกล่าวในส่วนนี้');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'สามารถเข้าถึงการดำเนินการ "%s" ได้ แต่สถานการณ์ปัจจุบันไม่เหมาะกับการดำเนินการ');

define('I_DOWNLOAD_ERROR_NO_ID', 'ไม่ได้ระบุตัวระบุแถวหรือไม่ใช่ตัวเลข');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'ไม่ได้ระบุตัวระบุฟิลด์หรือไม่ใช่ตัวเลข');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'ไม่มีฟิลด์ที่มีตัวระบุดังกล่าว');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'ฟิลด์นี้ไม่ได้จัดการกับไฟล์');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'ไม่มีแถวที่มีตัวระบุดังกล่าว');
define('I_DOWNLOAD_ERROR_NO_FILE', 'ไม่มีไฟล์ อัปโหลดในช่องนี้สำหรับแถวนี้');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'การรับข้อมูลไฟล์ล้มเหลว');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'ชื่อว่างสำหรับค่าเริ่มต้น \'%s\'');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'ค่า "%s" มีอยู่แล้วในรายการค่าที่อนุญาต');
define('I_ENUMSET_ERROR_VALUE_LAST', 'ค่า "%s" คือค่าสุดท้ายที่เหลืออยู่ที่เป็นไปได้ และไม่สามารถลบได้');

define('I_YES', 'ใช่');
define('I_NO', 'เลขที่');
define('I_ERROR', 'ข้อผิดพลาด');
define('I_MSG', 'ข้อความ');
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
define('I_TOTAL', 'ทั้งหมด');
define('I_TOGGLE_Y', 'เปิด');
define('I_TOGGLE_N', 'ปิด');
define('I_EXPORT_EXCEL', 'ส่งออกเป็นสเปรดชีต Excel');
define('I_EXPORT_PDF', 'ส่งออกเป็นเอกสาร PDF');
define('I_NAVTO_ROWSET', 'กลับไปที่ชุดแถว');
define('I_NAVTO_ID', 'ไปที่แถวตาม ID');
define('I_NAVTO_RELOAD', 'รีเฟรช');
define('I_AUTOSAVE', 'บันทึกอัตโนมัติก่อนข้ามไป');
define('I_NAVTO_RESET', 'การเปลี่ยนแปลงย้อนกลับ');
define('I_NAVTO_PREV', 'ไปที่แถวก่อนหน้า');
define('I_NAVTO_SIBLING', 'ไปที่แถวอื่น');
define('I_NAVTO_NEXT', 'ไปที่แถวถัดไป');
define('I_NAVTO_CREATE', 'ไปที่การสร้างแถวใหม่');
define('I_NAVTO_NESTED', 'ไปที่วัตถุที่ซ้อนกัน');
define('I_NAVTO_ROWINDEX', 'ไปที่แถวโดย #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'ต้องระบุฟิลด์ "%s"');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'ค่าของฟิลด์ "%s" ไม่สามารถเป็นวัตถุได้');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'ค่าของฟิลด์ "%s" ไม่สามารถเป็นอาร์เรย์ได้');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'ค่า "%s" ของช่อง "%s" ไม่ควรเกินทศนิยม 11 หลัก');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'ค่า "%s" ของฟิลด์ "%s" ไม่อยู่ในรายการค่าที่อนุญาต');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'ฟิลด์ "%s" มีค่าที่ไม่ได้รับอนุญาต: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'ค่า "%s" ของฟิลด์ "%s" มีอย่างน้อยหนึ่งรายการที่ไม่ใช่ทศนิยมที่ไม่ใช่ศูนย์');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'ค่า "%s" ของช่อง "%s" ควรเป็น "1" หรือ "0"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'ค่า "%s" ของช่อง "%s" ควรเป็นสีในรูปแบบ #rrggbb หรือ hue#rrggbb');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'ค่า "%s" ของฟิลด์ "%s" ไม่ใช่วันที่');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'ค่า "%s" ของฟิลด์ "%s" เป็นวันที่ที่ไม่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'ค่า "%s" ของช่อง "%s" ควรเป็นเวลาในรูปแบบ HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'ค่า "%s" ของฟิลด์ "%s" ไม่ใช่เวลาที่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'ค่า "%s" ที่ระบุในช่อง "%s" เป็นวันที่ - ไม่ใช่วันที่');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'วันที่ "%s" ที่กล่าวถึงในฟิลด์ "%s" - ไม่ใช่วันที่ที่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'ค่า "%s" ที่ระบุในฟิลด์ "%s" เป็นเวลา - ควรเป็นเวลาในรูปแบบ HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'เวลา "%s" ที่กล่าวถึงในฟิลด์ "%s" - ไม่ใช่เวลาที่ถูกต้อง');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'ค่า "%s" ของช่อง "%s" ควรเป็นตัวเลขที่มีตัวเลข 4 หลักหรือน้อยกว่าในส่วนที่เป็นจำนวนเต็ม โดยอาจใส่เครื่องหมาย "-" นำหน้าก็ได้ และมีตัวเลข 2 หลักหรือน้อยกว่า/ไม่มีเลยในส่วนที่เป็นเศษส่วน');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'ค่า "%s" ของช่อง "%s" ควรเป็นตัวเลขที่มีตัวเลข 8 หลักหรือน้อยกว่าในส่วนที่เป็นจำนวนเต็ม โดยอาจใส่เครื่องหมาย "-" นำหน้าก็ได้ และมีตัวเลข 2 หลักหรือน้อยกว่า/ไม่มีเลยในส่วนที่เป็นเศษส่วน');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'ค่า "%s" ของช่อง "%s" ควรเป็นตัวเลขที่มีตัวเลข 10 หลักหรือน้อยกว่าในส่วนที่เป็นจำนวนเต็ม โดยอาจใส่เครื่องหมาย "-" นำหน้าก็ได้ และมีตัวเลข 3 หลักหรือน้อยกว่า/ไม่มีเลยในส่วนที่เป็นเศษส่วน');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'ค่า "%s" ของช่อง "%s" ควรเป็นรูปแบบปี YYYY');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'ไม่มีอะไรจะบันทึก');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'คุณไม่ได้ทำการเปลี่ยนแปลงใดๆ');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'ไม่สามารถตั้งค่าแถวปัจจุบันเป็นพาเรนต์สำหรับตัวเองในฟิลด์ "%s"');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'แถวที่มีรหัส "%s" ระบุในช่อง "%s" - ไม่มีอยู่ จึงไม่สามารถตั้งค่าเป็นแถวหลักได้');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'แถว "%s" ที่ระบุในฟิลด์ "%s" - เป็นแถวลูก/ลูกหลานสำหรับแถวปัจจุบัน "%s" ดังนั้นจึงไม่สามารถตั้งค่าเป็นแถวพาเรนต์ได้');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'ในระหว่างการร้องขอของคุณ หนึ่งในการดำเนินการกับรายการประเภท "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '- ส่งคืนข้อผิดพลาดด้านล่าง');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'ต้องระบุฟิลด์ "%s"');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'ค่า "%s" ของช่อง "%s" ถูกใช้เป็นชื่อผู้ใช้สำหรับบัญชีอื่นแล้ว');

define('I_ROWFILE_ERROR_MKDIR', 'การสร้างไดเร็กทอรี "%s" ภายในเส้นทาง "%s" แบบเรียกซ้ำล้มเหลว แม้ว่าจะสามารถเขียนได้บนเส้นทางนั้นก็ตาม');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'การสร้างไดเรกทอรี "%s" ภายในเส้นทาง "%s" แบบเรียกซ้ำล้มเหลว เนื่องจากเส้นทางนั้นไม่สามารถเขียนได้');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'มีไดเรกทอรีเป้าหมาย "%s" อยู่ แต่ไม่สามารถเขียนได้');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'ไม่มีความเป็นไปได้ในการจัดการกับไฟล์ที่ไม่มีแถวอยู่');

define('I_ROWM4D_NO_SUCH_FIELD', 'ไม่มีฟิลด์ `m4d` ภายในเอนทิตี "%s"');

define('I_UPLOAD_ERR_INI_SIZE', 'ไฟล์ที่อัพโหลดในช่อง "%s" เกินคำสั่ง upload_max_filesize ใน php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'ไฟล์ที่อัปโหลดในช่อง "%s" เกินคำสั่ง MAX_FILE_SIZE ที่ระบุไว้');
define('I_UPLOAD_ERR_PARTIAL', 'ไฟล์ที่อัปโหลดในช่อง "%s" ถูกอัปโหลดเพียงบางส่วนเท่านั้น');
define('I_UPLOAD_ERR_NO_FILE', 'ไม่มีการอัปโหลดไฟล์ในช่อง "%s"');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'ไม่มีโฟลเดอร์ชั่วคราวบนเซิร์ฟเวอร์สำหรับจัดเก็บไฟล์ อัปโหลดในฟิลด์ "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'ไม่สามารถเขียนไฟล์ อัปโหลดในฟิลด์ "%s" ไปยังฮาร์ดไดรฟ์ของเซิร์ฟเวอร์');
define('I_UPLOAD_ERR_EXTENSION', 'การอัปโหลดไฟล์ในช่อง "%s" หยุดทำงานโดยส่วนขยาย php อันใดอันหนึ่งซึ่งทำงานบนเซิร์ฟเวอร์');
define('I_UPLOAD_ERR_UNKNOWN', 'การอัปโหลดไฟล์ในช่อง "%s" ล้มเหลวเนื่องจากข้อผิดพลาดที่ไม่ทราบสาเหตุ');

define('I_UPLOAD_ERR_REQUIRED', 'ยังไม่มีไฟล์ คุณควรเลือกหนึ่งไฟล์');
define('I_WGET_ERR_ZEROSIZE', 'การใช้ URL ของเว็บเป็นแหล่งที่มาของไฟล์สำหรับฟิลด์ "%s" ล้มเหลวเนื่องจากไฟล์นั้นมีขนาดเป็นศูนย์');

define('I_FORM_UPLOAD_SAVETOHDD', 'ดาวน์โหลด');
define('I_FORM_UPLOAD_ORIGINAL', 'แสดงต้นฉบับ');
define('I_FORM_UPLOAD_NOCHANGE', 'ไม่มีการเปลี่ยนแปลง');
define('I_FORM_UPLOAD_DELETE', 'ลบ');
define('I_FORM_UPLOAD_REPLACE', 'แทนที่');
define('I_FORM_UPLOAD_REPLACE_WITH', 'กับ');
define('I_FORM_UPLOAD_NOFILE', 'เลขที่');
define('I_FORM_UPLOAD_BROWSE', 'เรียกดู');
define('I_FORM_UPLOAD_MODE_TIP', 'ใช้เว็บลิงค์เพื่อเลือกไฟล์');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'ไฟล์พีซีในเครื่องของคุณ..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'ไฟล์ที่เว็บลิงค์..');

define('I_FORM_UPLOAD_ASIMG', 'รูปภาพ');
define('I_FORM_UPLOAD_ASOFF', 'เอกสารสำนักงาน');
define('I_FORM_UPLOAD_ASDRW', 'ภาพวาด');
define('I_FORM_UPLOAD_ASARC', 'ที่เก็บถาวร');
define('I_FORM_UPLOAD_ASAUD', 'เสียง');
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
define('I_COMBO_MISMATCH_MAXSELECTED', 'จำนวนตัวเลือกที่เลือกสูงสุดที่อนุญาตคือ');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'ตัวเลือก "%s" ไม่พร้อมใช้งานสำหรับการเลือกในฟิลด์ "%s"');
define('I_COMBO_KEYWORD_NO_RESULTS', 'ไม่พบสิ่งใดโดยใช้คำหลักนี้');
define('I_COMBO_ODATA_FIELD404', 'ฟิลด์ "%s" ไม่ใช่ทั้งฟิลด์จริงหรือฟิลด์หลอก');
define('I_COMBO_GROUPBY_NOGROUP', 'ไม่ได้ตั้งค่าการจัดกลุ่ม');
define('I_COMBO_WAND_TOOLTIP', 'สร้างตัวเลือกใหม่ในรายการนี้โดยใช้ชื่อที่ป้อนในฟิลด์นี้');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'ไม่พบแถว');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'ขอบเขตของแถวที่มีอยู่ของส่วนปัจจุบัน');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', 'โดยคำนึงถึงตัวเลือกการค้นหาที่ใช้ -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', 'ไม่มีแถวที่มีรหัสดังกล่าว');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'แถว #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'ของ');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'ไม่พบแถว');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'ขอบเขตของแถวที่มีอยู่ในส่วนปัจจุบัน');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', 'ด้วยตัวเลือกการค้นหาที่ใช้');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '- ไม่มีแถวที่มีดัชนีดังกล่าว แต่เพิ่งมี');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'เลขที่');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--เลือก--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'ค้นหา…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'ค้นหาในทุกคอลัมน์');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'ส่วนย่อย');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--เลือก--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'เลขที่');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'ข้อความ');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'เลือกแถว');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'ตัวเลือก');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'ระหว่าง');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'และ');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'จาก');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'จนกระทั่ง');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'ใช่');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'เลขที่');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'ไม่มีอะไรจะว่างเปล่า');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'ตัวเลือกว่างเปล่าอยู่แล้วหรือไม่ได้ใช้เลย');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'ยืนยัน');
define('I_ACTION_DELETE_CONFIRM_MSG', 'คุณแน่ใจหรือว่าต้องการลบ');
define('I_ENTRY_TBQ', 'รายการ,รายการ,รายการ');

define('I_SOUTH_PLACEHOLDER_TITLE', 'เนื้อหาของแท็บนี้จะเปิดขึ้นในหน้าต่างแยกต่างหาก');
define('I_SOUTH_PLACEHOLDER_GO', 'ไปที่');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', 'หน้าต่างนั้น');
define('I_SOUTH_PLACEHOLDER_GET', 'รับเนื้อหา');
define('I_SOUTH_PLACEHOLDER_BACK', 'กลับมาที่นี่');

define('I_DEMO_ACTION_OFF', 'การดำเนินการนี้ถูกปิดในโหมดสาธิต');

define('I_MCHECK_REQ', 'ฟิลด์ "%s" - จำเป็น');
define('I_MCHECK_REG', 'ค่า "%s" ของช่อง "%s" - อยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_MCHECK_KEY', 'ไม่พบวัตถุประเภท "%s" โดยคีย์ "%s"');
define('I_MCHECK_EQL', 'ค่าไม่ถูกต้อง');
define('I_MCHECK_DIS', 'ค่า "%s" ของฟิลด์ "%s" - อยู่ในรายการค่าที่ปิดใช้งาน');
define('I_MCHECK_UNQ', 'ค่า "%s" ของช่อง "%s" - ไม่ซ้ำกัน มันควรจะไม่ซ้ำกัน');
define('I_JCHECK_REQ', 'ไม่ได้ระบุพารามิเตอร์ "%s" -');
define('I_JCHECK_REG', 'ค่า "%s" ของพารามิเตอร์ "%s" - อยู่ในรูปแบบที่ไม่ถูกต้อง');
define('I_JCHECK_KEY', 'ไม่พบวัตถุประเภท "%s" โดยคีย์ "%s"');
define('I_JCHECK_EQL', 'ค่าไม่ถูกต้อง');
define('I_JCHECK_DIS', 'ค่า "%s" ของพารามิเตอร์ "%s" - อยู่ในรายการค่าที่ปิดใช้งาน');
define('I_JCHECK_UNQ', 'ค่า "%s" ของพารามิเตอร์ "%s" - ไม่ซ้ำกัน มันควรจะไม่ซ้ำกัน');

define('I_PRIVATE_DATA', '*ข้อมูลส่วนตัว*');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', 'เมื่อวาน');
define('I_WHEN_TOD', 'วันนี้');
define('I_WHEN_TOM', 'พรุ่งนี้');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'บน');
define('I_WHEN_WD_ON2', 'บน');
define('I_WHEN_TM_AT', 'ที่');

define('I_LANG_LAST', 'ไม่อนุญาตให้ลบรายการ "%s" สุดท้ายที่เหลือ');
define('I_LANG_CURR', 'ไม่อนุญาตให้ลบคำแปลที่ใช้เป็นคำแปลปัจจุบันของคุณ');
define('I_LANG_FIELD_L10N_DENY', 'ไม่สามารถเปิดการแปลเป็นภาษาท้องถิ่นสำหรับฟิลด์ "%s"');
define('I_LANG_QYQN_CONFIRM', 'หากคุณต้องการภาษา %s "%s" สำหรับเศษส่วน "%s" ให้กด "%s" หากคุณต้องการทำให้มันสอดคล้องกับสถานะปัจจุบัน - กด "%s"');
define('I_LANG_QYQN_CONFIRM2', 'สำหรับเศษส่วน "%s" ภาษา "%s" จะถูกทำเครื่องหมายด้วยตนเองเป็น "%s" ดำเนินการ?');
define('I_LANG_QYQN_SELECT', 'เลือกภาษาต้นทาง');
define('I_LANG_EXPORT_HEADER', 'เลือกพารามิเตอร์การส่งออก');
define('I_LANG_IMPORT_HEADER', 'เลือกพารามิเตอร์การนำเข้า');
define('I_LANG_NOT_SUPPORTED', 'ยังไม่รองรับจนถึงตอนนี้');
define('I_LANG_SELECT_CURRENT', 'เลือกภาษาปัจจุบันของเศษส่วน "%s"');
define('I_LANG_MIGRATE_META', 'เตรียมสนาม');
define('I_LANG_MIGRATE_DATA', 'ย้ายข้อมูลชื่อ');
define('I_ADD', 'เพิ่ม');
define('I_DELETE', 'ลบ');
define('I_SECTION_CLONE_SELECT_PARENT', 'เลือกส่วนหลักซึ่งควรอยู่รองจากส่วนที่ซ้ำกันของส่วนที่เลือก');

define('I_TILE_NOTHUMB', 'ไม่มีนิ้วหัวแม่มือ');
define('I_TILE_NOFILE', 'ไม่มีไฟล์');

define('I_CHANGELOG_FIELD', 'สิ่งที่มีการเปลี่ยนแปลง');
define('I_CHANGELOG_WAS', 'เคยเป็น');
define('I_CHANGELOG_NOW', 'กลายเป็น');

define('I_WEEK', 'สัปดาห์');
define('I_TODAY', 'วันนี้');

define('I_PRINT', 'พิมพ์');

define('I_NUM2STR_ZERO', 'ศูนย์');
define('I_NUM2STR_1TO9', 'หนึ่ง สอง สาม สี่ ห้า หก เจ็ด แปด เก้า');
define('I_NUM2STR_1TO9_2', 'หนึ่ง สอง สาม สี่ ห้า หก เจ็ด แปด เก้า');
define('I_NUM2STR_10TO19', 'สิบ สิบเอ็ด สิบสอง สิบสาม สิบสี่ สิบห้า สิบหก สิบเจ็ด สิบแปด สิบเก้า');
define('I_NUM2STR_20TO90', 'ยี่สิบ,สามสิบ,สี่สิบ,ห้าสิบ,หกสิบ,เจ็ดสิบ,แปดสิบ,เก้าสิบ');
define('I_NUM2STR_100TO900', 'หนึ่งร้อย สองร้อย สามร้อย สี่ร้อย ห้าร้อย หกร้อย เจ็ดร้อย แปดร้อย เก้าร้อย');
define('I_NUM2STR_TBQ_KOP', 'โคเปค,โกเปค,โกเปค');
define('I_NUM2STR_TBQ_RUB', 'รูเบิล,รูเบิล,รูเบิล');
define('I_NUM2STR_TBQ_THD', 'พัน,พัน,พัน');
define('I_NUM2STR_TBQ_MLN', 'ล้านล้านล้าน');
define('I_NUM2STR_TBQ_BLN', 'พันล้านพันล้านพันล้าน');

define('I_AGO_SECONDS', 'วินาทีวินาที');
define('I_AGO_MINUTES', 'นาที นาที');
define('I_AGO_HOURS', 'ชั่วโมงชั่วโมง');
define('I_AGO_DAYS', 'วัน วัน');
define('I_AGO_WEEKS', 'สัปดาห์สัปดาห์');
define('I_AGO_MONTHS', 'เดือน,เดือน');
define('I_AGO_YEARS', 'ปีปี');

define('I_PANEL_GRID', 'กริด');
define('I_PANEL_PLAN', 'ปฏิทิน');
define('I_PANEL_TILE', 'แกลเลอรี่');

define('I_ENT_AUTHOR_SPAN', 'สร้าง');
define('I_ENT_AUTHOR_ROLE', 'บทบาท');
define('I_ENT_AUTHOR_USER', 'ผู้ใช้');
define('I_ENT_AUTHOR_TIME', 'วันเวลา');
define('I_ENT_TOGGLE', 'สลับ');
define('I_ENT_TOGGLE_Y', 'เปิด');
define('I_ENT_TOGGLE_N', 'ปิด');
define('I_ENT_EXTENDS_OTHER', 'มีไฟล์ที่มี php-model อยู่ แต่ %s ถูกระบุเป็นคลาสพาเรนต์ที่นั่น');

define('I_GAPI_KEY_REQUIRED', 'กรุณาระบุคีย์ API ที่นี่..');
define('I_SELECT_CFGFIELD', 'เลือกพารามิเตอร์การกำหนดค่าที่คุณต้องการเพิ่มสำหรับฟิลด์นี้');
define('I_SELECT_PLEASE', 'โปรดเลือก');
define('I_L10N_TOGGLE_ACTION_Y', 'หากคุณต้องการ %s การแปลสำหรับการดำเนินการ "%s" ให้กด "%s"');
define('I_L10N_TOGGLE_MATCH', 'มิฉะนั้น หากคุณต้องการระบุค่าของสถานะการแปลอย่างชัดเจนเพื่อให้ตรงกับความเป็นจริงในปัจจุบัน - กด "%s"');
define('I_L10N_TOGGLE_ACTION_EXPL', 'สำหรับการดำเนินการ สถานะการแปล "%s" จะถูกตั้งค่าอย่างชัดเจนเป็น "%s" ดำเนินการต่อ?');
define('I_L10N_TOGGLE_ACTION_LANG_CURR', 'เลือกภาษาปัจจุบันของการดำเนินการ "%s"');
define('I_L10N_TOGGLE_ACTION_LANG_KEPT', 'เลือกภาษาที่จะเก็บไว้สำหรับการดำเนินการ "%s"');

define('I_L10N_TOOGLE_FIELD_DENIED', 'ไม่อนุญาตให้เปิด/ปิดการแปลสำหรับฟิลด์ที่ต้องพึ่งพา');
define('I_L10N_TOGGLE_FIELD_Y', 'หากคุณต้องการ %s การแปลสำหรับฟิลด์ "%s" ให้กด "%s"');
define('I_L10N_TOGGLE_FIELD_EXPL', 'สำหรับสถานะการแปลของฟิลด์ "%s" จะถูกตั้งค่าอย่างชัดเจนเป็น "%s" ดำเนินการต่อ?');
define('I_L10N_TOGGLE_FIELD_LANG_CURR', 'เลือกภาษาปัจจุบันของฟิลด์ "%s"');
define('I_L10N_TOGGLE_FIELD_LANG_KEPT', 'เลือกภาษาที่จะเก็บไว้สำหรับฟิลด์ "%s"');

define('I_GRID_COLOR_BREAK_INCOMPAT', 'คุณลักษณะนี้ใช้ได้กับคอลัมน์ตัวเลขเท่านั้น');
define('I_REALTIME_CONTEXT_AUTODELETE_ONLY', 'รายการประเภทบริบทไม่สามารถลบด้วยตนเองได้');
define('I_ONDELETE_RESTRICT', 'การลบถูกจำกัดเนื่องจากกฎ ON DELETE RESTRICT ที่กำหนดค่าสำหรับฟิลด์ "%s" ในเอนทิตี "%s" (`%s`.`%s`) เนื่องจากอย่างน้อย 1 เรกคอร์ดจากเอนทิตีนั้นมีค่าในฟิลด์นั้น ซึ่ง เป็นการอ้างอิงโดยตรงหรือแบบเรียงซ้อนไปยังเรคคอร์ดที่คุณต้องการลบ');

define('I_LANG_WORD_DETECT_ONLY', 'ตรวจเช็คเบื้องต้น?');
define('I_SECTION_ROWSET_MIN_CONF', 'สำหรับการเปิดใช้งานพาเนล %s โปรดระบุการกำหนดค่าขั้นต่ำต่อไปนี้:');
define('I_SECTION_CONF_SETUP', 'กำลังใช้การกำหนดค่า..');
define('I_SECTION_CONF_SETUP_DONE', 'ใช้การกำหนดค่าสำเร็จแล้ว');
define('I_NOTICE_HIDE_ALL', 'ซ่อนทั้งหมด');
define('I_RECORD_DELETED', 'บันทึกนี้ถูกลบแล้ว');

define('I_FILE_EXISTS', 'มีไฟล์อยู่แล้ว: %s');
define('I_FILE_CREATED', 'ไฟล์ที่สร้าง: %s');
define('I_CLASS_404', 'ไม่พบคลาส %s');
define('I_FORMAT_HIS', 'ค่า "%s" ของอาร์กิวเมนต์ %s ควรเป็นเวลาในรูปแบบ hh:mm:ss');
define('I_GAPI_RESPONSE', 'การตอบสนองของ Google Cloud Translate API: %s');
define('I_GAPI_KEY_REQUIRED', 'กรุณาระบุคีย์ API ที่นี่..');
define('I_PLAN_ITEM_LOAD_FAIL', 'ไม่สามารถโหลด %s %s ลงในกำหนดการได้');
define('I_SECTIONS_TPLCTLR_404', 'ไม่พบไฟล์ตัวควบคุมเทมเพลต');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_JS', 'มีไฟล์สำหรับ js-controller อยู่แล้ว แต่คลาสพาเรนต์ที่ระบุว่ามี %s');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_PHP', 'มีไฟล์สำหรับ php-controller อยู่แล้ว แต่คลาสพาเรนต์ที่ระบุว่ามี %s');
define('I_SECTIONS_CTLR_PARENT_404', 'ไม่พบชื่อคลาสพาเรนต์ภายในไฟล์ js-controller');
define('I_SECTIONS_CTLR_EMPTY_JS', 'ไฟล์สำหรับ js-controller ว่างเปล่า');
define('I_ENTITIES_TPLMDL_404', 'ไม่พบไฟล์โมเดลเทมเพลต');
define('I_ENTITIES_TPLMDLROW_404', 'ไม่พบไฟล์เทมเพลตสำหรับ rowClass ของโมเดล');
define('I_FILTERS_FEATURE_UNAPPLICABLE', 'คุณลักษณะนี้ใช้ได้กับตัวกรองที่มีฟิลด์คีย์ต่างประเทศอยู่ด้านหลังเท่านั้น');
define('I_LANG_NEW_QTY', 'จำนวนภาษาใหม่ที่ Google รองรับ: %s');
define('I_SCNACS_TPL_404', 'ไฟล์เทมเพลตการดำเนินการหายไป: %s');
define('I_MDL_CHLOG_NO_REVERT_1', 'การย้อนกลับถูกปิดใช้งานสำหรับฟิลด์ที่อ่านอย่างเดียวหรือซ่อนไว้');
define('I_MDL_CHLOG_NO_REVERT_2', 'การย้อนกลับเปิดใช้งานเฉพาะสำหรับฟิลด์ประเภท %s และ %s');
define('I_MDL_CHLOG_NO_REVERT_3', 'การย้อนกลับถูกปิดใช้งานสำหรับฟิลด์คีย์ต่างประเทศ');
define('I_MDL_GRID_PARENT_GROUP_DIFFERS', 'รายการหลักอยู่ในกลุ่มอื่น');
define('I_EXPORT_CERTAIN', 'หากควรส่งออกบางฟิลด์เท่านั้น - โปรดเลือก');
define('I_SCHED_UNABLE_SET_REST', 'ไม่สามารถสร้างพื้นที่นอกเวลาทำงานภายในกำหนดการได้');
define('I_MDL_ADMIN_VK_1', 'ที่อยู่เพจควรขึ้นต้นด้วย https://vk.com/');
define('I_MDL_ADMIN_VK_2', 'หน้านี้ไม่มีอยู่ใน VK');
define('I_MDL_ADMIN_VK_3', 'หน้านี้ใน VK ไม่ใช่หน้าผู้ใช้');

define('I_FIELD_DRAG_CLICK', 'ลากเพื่อเรียงลำดับใหม่/คลิกเพื่อดูรายละเอียด');
define('I_ACTION_FORM', 'รายละเอียด');