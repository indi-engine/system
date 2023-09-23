<?php
define('I_URI_ERROR_SECTION_FORMAT', 'El nombre de la sección tiene un formato incorrecto');
define('I_URI_ERROR_ACTION_FORMAT', 'El nombre de la acción tiene un formato incorrecto');
define('I_URI_ERROR_ID_FORMAT', 'Uri param \'id\' debe tener un valor entero positivo');
define('I_URI_ERROR_CHUNK_FORMAT', 'Uno de los fragmentos de URI tiene un formato no válido');

define('I_LOGIN_BOX_USERNAME', 'Nombre de usuario');
define('I_LOGIN_BOX_PASSWORD', 'Contraseña');
define('I_LOGIN_BOX_REMEMBER', 'Recordar');
define('I_LOGIN_BOX_ENTER', 'Ingresar');
define('I_LOGIN_BOX_RESET', 'Reiniciar');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'Error');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'Nombre de usuario no especificado');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'Contraseña no especificada');
define('I_LOGIN_BOX_LANGUAGE', 'Idioma');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'esa cuenta no existe');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'Contraseña incorrecta');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'Esta cuenta está desactivada');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'Esta cuenta es del tipo que está apagada.');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'Aún no hay secciones a las que pueda acceder esta cuenta');

define('I_THROW_OUT_ACCOUNT_DELETED', 'Tu cuenta acababa de ser eliminada');
define('I_THROW_OUT_PASSWORD_CHANGED', 'Tu contraseña acaba de ser cambiada');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'Tu cuenta acababa de ser desactivada.');
define('I_THROW_OUT_ROLE_IS_OFF', 'Su cuenta es del tipo que acaba de ser desactivada');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'Ahora ya no hay secciones accesibles para usted.');
define('I_THROW_OUT_SESSION_EXPIRED', 'Tu sesión ya no está disponible. ¿Continuar para volver a iniciar sesión?');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'Tal sección no existe.');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'La sección está apagada');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'Tal acción no existe.');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'Esta acción está desactivada.');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'Esta acción no existe en esta sección.');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'Esta acción está desactivada en esta sección.');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'No tienes derechos sobre esta acción en esta sección.');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'Una de las secciones principales de la sección actual está apagada');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'La adición de filas está restringida en esta sección.');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'La fila con dicha identificación no existe en esta sección');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'La acción "%s" es accesible, pero las circunstancias actuales no son las adecuadas para realizarla');

define('I_DOWNLOAD_ERROR_NO_ID', 'El identificador de fila no está especificado o no es un número');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'El identificador de campo no está especificado o no es un número');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'Ningún campo con dicho identificador');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'Este campo no trata con archivos.');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'No hay fila con dicho identificador');
define('I_DOWNLOAD_ERROR_NO_FILE', 'No hay ningún archivo subido en este campo para esta fila');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'Error al obtener información del archivo');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'Título en blanco para el valor predeterminado \'%s\'');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'El valor "%s" ya existe dentro de la lista de valores permitidos');
define('I_ENUMSET_ERROR_VALUE_LAST', 'El valor "%s" es el último valor posible restante y no se puede eliminar');

define('I_YES', 'Sí');
define('I_NO', 'No');
define('I_ERROR', 'Error');
define('I_MSG', 'Mensaje');
define('I_OR', 'o');
define('I_AND', 'y');
define('I_BE', 'ser');
define('I_FILE', 'Archivo');
define('I_SHOULD', 'debería');

define('I_HOME', 'Hogar');
define('I_LOGOUT', 'Cerrar sesión');
define('I_MENU', 'Menú');
define('I_CREATE', 'Crear nuevo');
define('I_BACK', 'Atrás');
define('I_SAVE', 'Ahorrar');
define('I_CLOSE', 'Cerca');
define('I_TOTAL', 'Total');
define('I_TOGGLE_Y', 'Encender');
define('I_TOGGLE_N', 'Apagar');
define('I_EXPORT_EXCEL', 'Exportar como una hoja de cálculo de Excel');
define('I_EXPORT_PDF', 'Exportar como documento PDF');
define('I_NAVTO_ROWSET', 'Volver al conjunto de filas');
define('I_NAVTO_ID', 'Ir a fila por ID');
define('I_NAVTO_RELOAD', 'Actualizar');
define('I_AUTOSAVE', 'Guardar automáticamente antes de ir');
define('I_NAVTO_RESET', 'Revertir cambios');
define('I_NAVTO_PREV', 'Ir a la fila anterior');
define('I_NAVTO_SIBLING', 'Ir a cualquier otra fila');
define('I_NAVTO_NEXT', 'Ir a la siguiente fila');
define('I_NAVTO_CREATE', 'Ir a creación de nueva fila');
define('I_NAVTO_NESTED', 'Ir a objetos anidados');
define('I_NAVTO_ROWINDEX', 'Ir a fila por #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'El campo "%s" es obligatorio');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'El valor del campo "%s" no puede ser un objeto');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'El valor del campo "%s" no puede ser una matriz');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'El valor "%s" del campo "%s" no debe ser mayor que un decimal de 11 dígitos');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'El valor "%s" del campo "%s" no está dentro de la lista de valores permitidos');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'El campo "%s" contiene valores no permitidos: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'El valor "%s" del campo "%s" contiene al menos un elemento que no es un decimal distinto de cero');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'El valor "%s" del campo "%s" debe ser "1" o "0"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'El valor "%s" del campo "%s" debe ser un color en los formatos #rrggbb o hue#rrggbb');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'El valor "%s" del campo "%s" no es una fecha');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'El valor "%s" del campo "%s" no es una fecha válida');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'El valor "%s" del campo "%s" debe ser una hora en formato HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'El valor "%s" del campo "%s" no es una hora válida');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'Valor "%s", mencionado en el campo "%s" como fecha - no es una fecha');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'Fecha "%s", mencionada en el campo "%s" - no es una fecha válida');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'Valor "%s", mencionado en el campo "%s" como hora; debe ser una hora en formato HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'Hora "%s", mencionada en el campo "%s" - no es una hora válida');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'El valor "%s" del campo "%s" debe ser un número con 4 o menos dígitos en la parte entera, opcionalmente antepuesto por el signo "-", y 2 o menos/ninguno dígitos en la parte fraccionaria');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'El valor "%s" del campo "%s" debe ser un número con 8 o menos dígitos en la parte entera, opcionalmente antepuesto por el signo "-", y 2 o menos/ninguno dígitos en la parte fraccionaria');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'El valor "%s" del campo "%s" debe ser un número con 10 o menos dígitos en la parte entera, opcionalmente antepuesto por el signo "-", y 3 o menos/ninguno dígitos en la parte fraccionaria');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'El valor "%s" del campo "%s" debe ser un año en formato AAAA');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'Nada que salvar');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'No hiciste ningún cambio');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'La fila actual no se puede establecer como principal en el campo "%s"');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'La fila con ID "%s", especificada en el campo "%s", no existe, por lo que no se puede configurar como fila principal.');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'La fila "%s", especificada en el campo "%s", es una fila secundaria/descendiente de una fila actual "%s", por lo que no se puede configurar como fila principal.');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'Durante su solicitud, una de las operaciones sobre la entrada del tipo "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '- devolvió los siguientes errores');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'El campo "%s" es obligatorio');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'El valor "%s" del campo "%s" ya se utiliza como nombre de usuario para otra cuenta');

define('I_ROWFILE_ERROR_MKDIR', 'La creación recursiva del directorio "%s" dentro de la ruta "%s" falló, a pesar de que en esa ruta se puede escribir');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'La creación recursiva del directorio "%s" dentro de la ruta "%s" falló porque no se puede escribir en esa ruta');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'El directorio de destino "%s" existe, pero no se puede escribir');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'No hay posibilidad de tratar con archivos de fila inexistente.');

define('I_ROWM4D_NO_SUCH_FIELD', 'El campo `m4d` no existe dentro de la entidad "%s"');

define('I_UPLOAD_ERR_INI_SIZE', 'El archivo cargado en el campo "%s" excede la directiva upload_max_filesize en php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'El archivo cargado en el campo "%s" excede la directiva MAX_FILE_SIZE que se especificó');
define('I_UPLOAD_ERR_PARTIAL', 'El archivo cargado en el campo "%s" se cargó solo parcialmente');
define('I_UPLOAD_ERR_NO_FILE', 'No se cargó ningún archivo en el campo "%s"');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'Falta una carpeta temporal en el servidor para almacenar el archivo, cargada en el campo "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'No se pudo escribir el archivo cargado en el campo "%s" en el disco duro del servidor');
define('I_UPLOAD_ERR_EXTENSION', 'Carga de archivos en el campo "%s" detenida por una de las extensiones php, ejecutándose en el servidor');
define('I_UPLOAD_ERR_UNKNOWN', 'La carga del archivo en el campo "%s" falló debido a un error desconocido');

define('I_UPLOAD_ERR_REQUIRED', 'Aún no hay ningún archivo, debes elegir uno.');
define('I_WGET_ERR_ZEROSIZE', 'El uso de la URL web como origen del archivo para el campo "%s" falló porque ese archivo tiene tamaño cero');

define('I_FORM_UPLOAD_SAVETOHDD', 'Descargar');
define('I_FORM_UPLOAD_ORIGINAL', 'Mostrar original');
define('I_FORM_UPLOAD_NOCHANGE', 'Ningún cambio');
define('I_FORM_UPLOAD_DELETE', 'Borrar');
define('I_FORM_UPLOAD_REPLACE', 'Reemplazar');
define('I_FORM_UPLOAD_REPLACE_WITH', 'con');
define('I_FORM_UPLOAD_NOFILE', 'No');
define('I_FORM_UPLOAD_BROWSE', 'Navegar');
define('I_FORM_UPLOAD_MODE_TIP', 'Utilice un enlace web para seleccionar un archivo');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'el archivo de su PC local...');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'archivo en enlace web..');

define('I_FORM_UPLOAD_ASIMG', 'una imagen');
define('I_FORM_UPLOAD_ASOFF', 'un documento de oficina');
define('I_FORM_UPLOAD_ASDRW', 'un dibujo');
define('I_FORM_UPLOAD_ASARC', 'un archivo');
define('I_FORM_UPLOAD_OFEXT', 'tener tipo');
define('I_FORM_UPLOAD_INFMT', 'en formato');
define('I_FORM_UPLOAD_HSIZE', 'tener tamaño');
define('I_FORM_UPLOAD_NOTGT', 'no mayor que');
define('I_FORM_UPLOAD_NOTLT', 'no menos que');
define('I_FORM_UPLOAD_FPREF', 'Foto %s');

define('I_FORM_DATETIME_HOURS', 'horas');
define('I_FORM_DATETIME_MINUTES', 'minutos');
define('I_FORM_DATETIME_SECONDS', 'segundos');
define('I_COMBO_OF', 'de');
define('I_COMBO_MISMATCH_MAXSELECTED', 'El número máximo permitido de opciones seleccionadas es');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'La opción "%s" no está disponible para su selección en el campo "%s"');
define('I_COMBO_KEYWORD_NO_RESULTS', 'No se encontró nada usando esta palabra clave');
define('I_COMBO_ODATA_FIELD404', 'El campo "%s" no es ni un campo real ni un pseudocampo');
define('I_COMBO_GROUPBY_NOGROUP', 'Agrupación no establecida');
define('I_COMBO_WAND_TOOLTIP', 'Cree una nueva opción en esta lista usando el título, ingresado en este campo');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'No se encuentra la fila');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'El alcance de las filas disponibles de la sección actual');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', ', a la vista con opciones de búsqueda aplicadas -');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', 'no contiene una fila con dicho ID');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'Fila #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'de');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'No se encuentra la fila');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'El alcance de las filas que están disponibles en la sección actual,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', 'a la vista con opciones de búsqueda aplicadas');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '- no contiene una fila con dicho índice, pero recientemente sí lo hizo');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'No');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--Seleccionar--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'Buscar…');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'Buscar en todas las columnas');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'Subsecciones');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--Seleccionar--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'No');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'Mensaje');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'Seleccione una fila');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'Opciones');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'entre');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'y');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'de');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'hasta');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'Sí');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'No');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'Nada que vaciar');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'Las opciones ya están vacías o no se utilizan en absoluto');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'Confirmar');
define('I_ACTION_DELETE_CONFIRM_MSG', 'estas seguro que quieres borrarlo');
define('I_ENTRY_TBQ', 'entrada,entradas,entradas');

define('I_SOUTH_PLACEHOLDER_TITLE', 'El contenido de esta pestaña se abre en una ventana separada.');
define('I_SOUTH_PLACEHOLDER_GO', 'Ir a');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', 'esa ventana');
define('I_SOUTH_PLACEHOLDER_GET', 'Obtener contenidos');
define('I_SOUTH_PLACEHOLDER_BACK', 'aquí atrás');

define('I_DEMO_ACTION_OFF', 'Esta acción está desactivada en el modo de demostración.');

define('I_MCHECK_REQ', 'Campo "%s" - es obligatorio');
define('I_MCHECK_REG', 'El valor "%s" del campo "%s" tiene un formato no válido');
define('I_MCHECK_KEY', 'No se encontró ningún objeto de tipo "%s" con la clave "%s"');
define('I_MCHECK_EQL', 'Valor incorrecto');
define('I_MCHECK_DIS', 'Valor "%s" del campo "%s" - está en la lista de valores deshabilitados');
define('I_MCHECK_UNQ', 'El valor "%s" del campo "%s" no es único. Debería ser único.');
define('I_JCHECK_REQ', 'Parámetro "%s" - no se proporciona');
define('I_JCHECK_REG', 'El valor "%s" del parámetro "%s" tiene un formato no válido');
define('I_JCHECK_KEY', 'No se encontró ningún objeto de tipo "%s" con la clave "%s"');
define('I_JCHECK_EQL', 'Valor incorrecto');
define('I_JCHECK_DIS', 'Valor "%s" del parámetro "%s" - está en la lista de valores deshabilitados');
define('I_JCHECK_UNQ', 'El valor "%s" del parámetro "%s" no es único. Debería ser único.');

define('I_PRIVATE_DATA', '*datos privados*');

define('I_WHEN_DBY', '');
define('I_WHEN_YST', 'ayer');
define('I_WHEN_TOD', 'hoy');
define('I_WHEN_TOM', 'mañana');
define('I_WHEN_DAT', '');
define('I_WHEN_WD_ON1', 'en');
define('I_WHEN_WD_ON2', 'en');
define('I_WHEN_TM_AT', 'en');

define('I_LANG_LAST', 'No está permitido eliminar la última entrada restante "%s"');
define('I_LANG_CURR', 'No está permitido eliminar la traducción que se utiliza como su traducción actual.');
define('I_LANG_FIELD_L10N_DENY', 'No se puede activar la localización para el campo "%s"');
define('I_LANG_QYQN_CONFIRM', 'Si desea %s idioma "%s" para la fracción "%s", presione "%s". Si solo necesita alinearlo con el estado actual, presione "%s"');
define('I_LANG_QYQN_CONFIRM2', 'Para la fracción "%s", el idioma "%s" se marcará manualmente como "%s". ¿Proceder?');
define('I_LANG_QYQN_SELECT', 'Seleccionar idioma de origen');
define('I_LANG_EXPORT_HEADER', 'Seleccionar parámetros de exportación');
define('I_LANG_IMPORT_HEADER', 'Seleccionar parámetros de importación');
define('I_LANG_NOT_SUPPORTED', 'No soportado hasta el momento');
define('I_LANG_SELECT_CURRENT', 'Seleccione el idioma actual de la fracción "%s"');
define('I_LANG_MIGRATE_META', 'Preparar campos');
define('I_LANG_MIGRATE_DATA', 'Migrar títulos');
define('I_ADD', 'Agregar');
define('I_DELETE', 'Borrar');
define('I_SECTION_CLONE_SELECT_PARENT', 'Seleccione la sección principal, que debe estar subordinada a los duplicados de las secciones seleccionadas.');

define('I_TILE_NOTHUMB', 'sin pulgar');
define('I_TILE_NOFILE', 'Ningún archivo');

define('I_CHANGELOG_FIELD', 'que fue cambiado');
define('I_CHANGELOG_WAS', 'Era');
define('I_CHANGELOG_NOW', 'Convertirse');

define('I_WEEK', 'Semana');
define('I_TODAY', 'Hoy');

define('I_PRINT', 'Imprimir');

define('I_NUM2STR_ZERO', 'cero');
define('I_NUM2STR_1TO9', 'uno,dos,tres,cuatro,cinco,seis,siete,ocho,nueve');
define('I_NUM2STR_1TO9_2', 'uno,dos,tres,cuatro,cinco,seis,siete,ocho,nueve');
define('I_NUM2STR_10TO19', 'diez,once,doce,trece,catorce,quince,dieciséis,diecisiete,dieciocho,diecinueve');
define('I_NUM2STR_20TO90', 'veinte,treinta,cuarenta,cincuenta,sesenta,setenta,ochenta,noventa');
define('I_NUM2STR_100TO900', 'cien doscientos trescientos cuatrocientos quinientos seiscientos setecientos ochocientos novecientos');
define('I_NUM2STR_TBQ_KOP', 'kopeks,kopeks,kopeks');
define('I_NUM2STR_TBQ_RUB', 'rublo, rublos, rublos');
define('I_NUM2STR_TBQ_THD', 'mil,mil,mil');
define('I_NUM2STR_TBQ_MLN', 'millones,millones,millones');
define('I_NUM2STR_TBQ_BLN', 'mil millones, mil millones, mil millones');

define('I_AGO_SECONDS', 'segundo,segundos');
define('I_AGO_MINUTES', 'minuto, minutos');
define('I_AGO_HOURS', 'hora, horas');
define('I_AGO_DAYS', 'día días');
define('I_AGO_WEEKS', 'semana, semanas');
define('I_AGO_MONTHS', 'mes, meses');
define('I_AGO_YEARS', 'año, años');

define('I_PANEL_GRID', 'Red');
define('I_PANEL_PLAN', 'Calendario');
define('I_PANEL_TILE', 'Galería');

define('I_ENT_AUTHOR_SPAN', 'Creado');
define('I_ENT_AUTHOR_ROLE', 'Role');
define('I_ENT_AUTHOR_USER', 'Usuario');
define('I_ENT_AUTHOR_TIME', 'Fecha y hora');
define('I_ENT_TOGGLE', 'Palanca');
define('I_ENT_TOGGLE_Y', 'encendido');
define('I_ENT_TOGGLE_N', 'Apagado');
define('I_ENT_EXTENDS_OTHER', 'El archivo con el modelo php existe, pero %s está especificado como clase principal allí');

define('I_GAPI_KEY_REQUIRED', 'Especifique la clave API aquí.');
define('I_SELECT_CFGFIELD', 'Seleccione el parámetro de configuración que desea agregar para este campo');
define('I_SELECT_PLEASE', 'Por favor seleccione');
define('I_L10N_TOGGLE_ACTION_Y', 'Si desea localizar %s para la acción "%s", presione "%s".');
define('I_L10N_TOGGLE_MATCH', 'De lo contrario, si solo necesita especificar explícitamente el valor del estado de localización para que coincida con la realidad actual, presione "%s"');
define('I_L10N_TOGGLE_ACTION_EXPL', 'Para la acción "%s", el estado de localización se establecerá explícitamente como "%s". ¿Continuar?');
define('I_L10N_TOGGLE_ACTION_LANG_CURR', 'Seleccione el idioma actual de acción "%s"');
define('I_L10N_TOGGLE_ACTION_LANG_KEPT', 'Seleccione el idioma que se mantendrá para la acción "%s"');

define('I_L10N_TOOGLE_FIELD_DENIED', 'No está permitido activar/desactivar la localización para campos dependientes');
define('I_L10N_TOGGLE_FIELD_Y', 'Si desea localizar %s para el campo "%s", presione "%s".');
define('I_L10N_TOGGLE_FIELD_EXPL', 'Para el campo "%s", el estado de localización se establecerá explícitamente como "%s". ¿Continuar?');
define('I_L10N_TOGGLE_FIELD_LANG_CURR', 'Seleccione el idioma actual del campo "%s"');
define('I_L10N_TOGGLE_FIELD_LANG_KEPT', 'Seleccione el idioma que se mantendrá para el campo "%s"');

define('I_GRID_COLOR_BREAK_INCOMPAT', 'Esta función solo se aplica a columnas numéricas.');
define('I_REALTIME_CONTEXT_AUTODELETE_ONLY', 'Las entradas de tipo Contexto no se pueden eliminar manualmente');
define('I_ONDELETE_RESTRICT', 'La eliminación está restringida debido a la regla ON DELETE RESTRICT configurada para el campo "%s" en la entidad "%s" (`%s`.`%s`), ya que al menos 1 registro de esa entidad tiene un valor en ese campo, que es una referencia directa o en cascada al registro que desea eliminar');

define('I_LANG_WORD_DETECT_ONLY', '¿Hacer control preliminar?');
define('I_SECTION_ROWSET_MIN_CONF', 'Para activar el panel %s, especifique la siguiente configuración mínima:');
define('I_SECTION_CONF_SETUP', 'Se está aplicando la configuración.');
define('I_SECTION_CONF_SETUP_DONE', 'La configuración se ha aplicado correctamente');
define('I_NOTICE_HIDE_ALL', 'Ocultar todo');
define('I_RECORD_DELETED', 'Este registro fue eliminado');

define('I_FILE_EXISTS', 'El archivo ya existe: %s');
define('I_FILE_CREATED', 'Archivo creado: %s');
define('I_CLASS_404', 'Clase no encontrada %s');
define('I_FORMAT_HIS', 'El valor "%s" del argumento %s debe ser una hora en formato hh:mm:ss');
define('I_GAPI_RESPONSE', 'Respuesta de la API de Google Cloud Translate: %s');
define('I_GAPI_KEY_REQUIRED', 'Especifique la clave API aquí.');
define('I_PLAN_ITEM_LOAD_FAIL', 'No se puede cargar %s %s en la programación');
define('I_SECTIONS_TPLCTLR_404', 'No se encontró ningún archivo de controlador de plantilla');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_JS', 'El archivo para js-controller existe, pero la clase principal especificada es %s');
define('I_SECTIONS_CTLR_PARENT_MISMATCH_PHP', 'El archivo para el controlador php existe, pero la clase principal especificada es %s');
define('I_SECTIONS_CTLR_PARENT_404', 'No se puede encontrar el nombre de la clase principal dentro del archivo js-controller');
define('I_SECTIONS_CTLR_EMPTY_JS', 'El archivo para js-controller está vacío');
define('I_ENTITIES_TPLMDL_404', 'No se encontró ningún archivo de modelo de plantilla');
define('I_ENTITIES_TPLMDLROW_404', 'No se encontró ningún archivo de plantilla para la clase de fila del modelo');
define('I_FILTERS_FEATURE_UNAPPLICABLE', 'Esta característica solo se aplica a filtros que tienen campos de clave externa detrás');
define('I_LANG_NEW_QTY', 'Cantidad de nuevos idiomas admitidos por Google: %s');
define('I_SCNACS_TPL_404', 'Falta el archivo de plantilla de acción: %s');
define('I_MDL_CHLOG_NO_REVERT_1', 'La reversión está deshabilitada para campos que son de solo lectura u ocultos');
define('I_MDL_CHLOG_NO_REVERT_2', 'La reversión está habilitada solo para campos de tipos %s y %s');
define('I_MDL_CHLOG_NO_REVERT_3', 'La reversión está deshabilitada para campos de clave externa');
define('I_MDL_GRID_PARENT_GROUP_DIFFERS', 'La entrada de los padres está en otro grupo.');
define('I_EXPORT_CERTAIN', 'Si solo se deben exportar ciertos campos, seleccione');
define('I_SCHED_UNABLE_SET_REST', 'No se puede crear espacio fuera del horario laboral dentro del horario');
define('I_MDL_ADMIN_VK_1', 'La dirección de la página debe comenzar con https://vk.com/');
define('I_MDL_ADMIN_VK_2', 'Esta página no existe en VK.');
define('I_MDL_ADMIN_VK_3', 'Esta página en VK no es una página de usuario.');