<?php
define('I_URI_ERROR_SECTION_FORMAT', 'El nombre de la sección está en el formato incorrecto');
define('I_URI_ERROR_ACTION_FORMAT', 'El nombre de la acción está en el formato incorrecto');
define('I_URI_ERROR_ID_FORMAT', 'El parámetro \'id\' debe ser un entero positivo');
define('I_URI_ERROR_CHUNK_FORMAT', 'Una parte del URI está en el formato incorrecto');

define('I_LOGIN_BOX_USERNAME', 'Usuario');
define('I_LOGIN_BOX_PASSWORD', 'Contraseña');
define('I_LOGIN_BOX_REMEMBER', 'Recuerda');
define('I_LOGIN_BOX_ENTER', 'entrada');
define('I_LOGIN_BOX_RESET', 'Reiniciar');
define('I_LOGIN_ERROR_MSGBOX_TITLE', 'Error');
define('I_LOGIN_ERROR_ENTER_YOUR_USERNAME', 'Nombre de usuario no especificado');
define('I_LOGIN_ERROR_ENTER_YOUR_PASSWORD', 'Contraseña no especificada');
define('I_LOGIN_BOX_LANGUAGE', 'Lengua');

define('I_LOGIN_ERROR_NO_SUCH_ACCOUNT', 'No hay tal cuenta');
define('I_LOGIN_ERROR_WRONG_PASSWORD', 'Ingresaste la contraseña incorrecta');
define('I_LOGIN_ERROR_ACCOUNT_IS_OFF', 'Esta cuenta se ha inhabilitado.');
define('I_LOGIN_ERROR_ROLE_IS_OFF', 'Este tipo de cuenta ha sido deshabilitado.');
define('I_LOGIN_ERROR_NO_ACCESSIBLE_SECTIONS', 'Todavía no hay particiones disponibles para esta cuenta en el sistema.');

define('I_THROW_OUT_ACCOUNT_DELETED', 'Su cuenta acaba de ser eliminada.');
define('I_THROW_OUT_PASSWORD_CHANGED', 'Su contraseña acaba de ser cambiada.');
define('I_THROW_OUT_ACCOUNT_IS_OFF', 'Su cuenta acaba de ser deshabilitada.');
define('I_THROW_OUT_ROLE_IS_OFF', 'Su tipo de cuenta acaba de deshabilitarse');
define('I_THROW_OUT_NO_ACCESSIBLE_SECTIONS', 'No hay particiones disponibles para usted en el sistema');

define('I_ACCESS_ERROR_NO_SUCH_SECTION', 'No hay tal sección');
define('I_ACCESS_ERROR_SECTION_IS_OFF', 'Esta sección está apagada.');
define('I_ACCESS_ERROR_NO_SUCH_ACTION', 'No hay tal acción');
define('I_ACCESS_ERROR_ACTION_IS_OFF', 'Esta acción está apagada.');
define('I_ACCESS_ERROR_NO_SUCH_ACTION_IN_SUCH_SECTION', 'No existe tal acción en esta sección.');
define('I_ACCESS_ERROR_ACTION_IS_OFF_IN_SUCH_SECTION', 'Esta acción está desactivada en esta sección.');
define('I_ACCESS_ERROR_ACTION_IS_NOT_ACCESSIBLE', 'No está autorizado para hacer esto en esta sección.');
define('I_ACCESS_ERROR_ONE_OF_PARENT_SECTIONS_IS_OFF', 'Una de las secciones anteriores para esta sección está deshabilitada.');
define('I_ACCESS_ERROR_ROW_ADDING_DISABLED', 'El derecho a crear entradas no está disponible en esta sección.');
define('I_ACCESS_ERROR_ROW_DOESNT_EXIST', 'No hay entrada con esta identificación en esta sección');
define('I_ACCESS_ERROR_ACTION_IS_OFF_DUETO_CIRCUMSTANCES', 'La acción "%s" está disponible, pero no en las circunstancias actuales');

define('I_DOWNLOAD_ERROR_NO_ID', 'El identificador de objeto no está especificado o no es un número');
define('I_DOWNLOAD_ERROR_NO_FIELD', 'El identificador de campo no está especificado o no es un número');
define('I_DOWNLOAD_ERROR_NO_SUCH_FIELD', 'No hay campo con este identificador');
define('I_DOWNLOAD_ERROR_FIELD_DOESNT_DEAL_WITH_FILES', 'Un campo con este identificador no funciona con archivos');
define('I_DOWNLOAD_ERROR_NO_SUCH_ROW', 'Ningún objeto con este identificador');
define('I_DOWNLOAD_ERROR_NO_FILE', 'Archivo cargado en el campo especificado para el objeto especificado: no existe');
define('I_DOWNLOAD_ERROR_FILEINFO_FAILED', 'Error al obtener la información del archivo.');

define('I_ENUMSET_DEFAULT_VALUE_BLANK_TITLE', 'Título del valor predeterminado \'%s\'');
define('I_ENUMSET_ERROR_VALUE_ALREADY_EXISTS', 'El valor "%s" ya está en la lista de valores posibles');
define('I_ENUMSET_ERROR_VALUE_LAST', 'El valor "%s" es el último valor restante en la lista de posibles y, por lo tanto, no se puede eliminar');

define('I_YES', 'si');
define('I_NO', 'No');
define('I_ERROR', 'Error');
define('I_MSG', 'Mensaje');
define('I_OR', 'o');
define('I_AND', 'y');
define('I_BE', 'ser - estar');
define('I_FILE', 'Expediente');
define('I_SHOULD', 'debería');

define('I_HOME', 'comienzo');
define('I_LOGOUT', 'Salida');
define('I_MENU', 'Menú');
define('I_CREATE', 'Crear nueva publicación');
define('I_BACK', 'Regresar');
define('I_SAVE', 'Salvar');
define('I_CLOSE', 'Cerca');
define('I_TOTAL', 'Total');
define('I_EXPORT_EXCEL', 'Exportar a Excel');
define('I_EXPORT_PDF', 'Exportar a PDF');
define('I_NAVTO_ROWSET', 'Volver a la lista');
define('I_NAVTO_ID', 'Ir al registro por ID');
define('I_NAVTO_RELOAD', 'Actualizar');
define('I_AUTOSAVE', 'Guardar automáticamente antes de las transiciones');
define('I_NAVTO_RESET', 'Cancelar cambios');
define('I_NAVTO_PREV', 'Ir al post anterior');
define('I_NAVTO_SIBLING', 'Ir a cualquier otra entrada');
define('I_NAVTO_NEXT', 'Ir al siguiente post');
define('I_NAVTO_CREATE', 'Ir a crear una nueva publicación');
define('I_NAVTO_NESTED', 'Ir a la lista de entradas anidadas');
define('I_NAVTO_ROWINDEX', 'Ir al registro #');

define('I_ROWSAVE_ERROR_VALUE_REQUIRED', 'El campo "%s" es obligatorio');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_OBJECT', 'El valor del campo "%s" no puede ser un objeto');
define('I_ROWSAVE_ERROR_VALUE_CANT_BE_ARRAY', 'El valor del campo "%s" no puede ser una matriz');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_INT11', 'El valor "%s" del campo "%s" debe ser un número entero con un máximo de 11 dígitos');
define('I_ROWSAVE_ERROR_VALUE_IS_NOT_ALLOWED', 'El valor "%s" del campo "%s" no está en la lista de valores válidos');
define('I_ROWSAVE_ERROR_VALUE_CONTAINS_UNALLOWED_ITEMS', 'El campo "%s" contiene valores no válidos: "%s"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_LIST_OF_NON_ZERO_DECIMALS', 'El valor "%s" del campo "%s" contiene al menos un elemento que no es un entero distinto de cero');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_BOOLEAN', 'El valor "%s" del campo "%s" debe ser "1" o "0"');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_COLOR', 'El valor "%s" del campo "%s" no es un color en los formatos #rrggbb o hue # rrggbb');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DATE', 'El valor "%s" del campo "%s" no es una fecha');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_DATE', 'El valor "%s" del campo "%s" no es una fecha válida');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_TIME', 'El valor "%s" del campo "%s" no es una hora en el formato HH:MM:SS');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_VALID_TIME', 'El tiempo "%s" del campo "%s" no es un tiempo válido');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_DATE', 'El valor "%s" especificado en el campo "%s" ya que la fecha no es una fecha');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_DATE', 'La fecha "%s" especificada en el campo "%s" - debe ser correcta');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_TIME', 'El valor "%s" especificado en el campo "%s" ya que la hora no es una hora');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_CONTAIN_VALID_TIME', 'El tiempo "%s" especificado en el campo "%s" - debe ser correcto');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DOUBLE72', 'El valor "%s" del campo "%s" debe ser un número que no tenga más de 5 dígitos en la parte entera, y no más de 2 dígitos en la fracción');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL112', 'El valor "%s" del campo "%s" debe ser un número que no tenga más de 8 dígitos en la parte entera y no más de 2 dígitos en la fracción');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_DECIMAL143', 'El valor "%s" del campo "%s" debe ser un número con no más de 10 dígitos en la parte entera, posiblemente con un "-", y no más de 3 en fracción');
define('I_ROWSAVE_ERROR_VALUE_SHOULD_BE_YEAR', 'El valor "%s" del campo "%s" no es un año en el formato AAAA');
define('I_ROWSAVE_ERROR_NOTDIRTY_TITLE', 'Nada que salvar');
define('I_ROWSAVE_ERROR_NOTDIRTY_MSG', 'Aún no ha realizado ningún cambio.');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_SELF', 'El registro actual no se puede especificar como padre en el campo "%s"');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_404', 'El registro con el identificador "%s" especificado en el campo "%s" no existe y, por lo tanto, no puede seleccionarse como padre');
define('I_ROWSAVE_ERROR_VALUE_TREECOLUMN_INVALID_CHILD', 'El registro "%s" especificado en el campo "%s" es un hijo / subordinado del registro actual "%s" y, por lo tanto, no puede seleccionarse como padre');
define('I_ROWSAVE_ERROR_MFLUSH_MSG1', 'En el cumplimiento de su solicitud, una de las operaciones realizadas automáticamente, en particular en un registro del tipo "');
define('I_ROWSAVE_ERROR_MFLUSH_MSG2', '- emitió los siguientes errores');

define('I_ADMIN_ROWSAVE_LOGIN_REQUIRED', 'El campo "%s" es obligatorio');
define('I_ADMIN_ROWSAVE_LOGIN_OCCUPIED', 'El valor "%s" especificado en el campo "%s" ya se utiliza como nombre de usuario en otra cuenta');

define('I_ROWFILE_ERROR_MKDIR', 'La creación del directorio "%s" en la carpeta "%s" falló, aunque la carpeta se puede escribir');
define('I_ROWFILE_ERROR_UPPER_DIR_NOT_WRITABLE', 'La creación del directorio "%s" en la carpeta "%s" falló porque esta carpeta no se puede escribir');
define('I_ROWFILE_ERROR_TARGET_DIR_NOT_WRITABLE', 'Directorio "%s" requerido para la descarga: existe, pero no se puede escribir');
define('I_ROWFILE_ERROR_NONEXISTENT_ROW', 'No se puede trabajar con archivos relacionados con entradas inexistentes');

define('I_ROWM4D_NO_SUCH_FIELD', 'El campo `m4d` está ausente en la estructura de la entidad "%s"');

define('I_UPLOAD_ERR_INI_SIZE', 'El tamaño del archivo seleccionado para cargar en el campo "%s" excedió el tamaño máximo especificado por la directiva upload_max_filesize del archivo de configuración php.ini');
define('I_UPLOAD_ERR_FORM_SIZE', 'El tamaño de archivo seleccionado para cargar en el campo "%s" excedió el valor MAX_FILE_SIZE especificado en el formulario HTML');
define('I_UPLOAD_ERR_PARTIAL', 'El archivo seleccionado para cargar en el campo "%s" solo se recibió parcialmente');
define('I_UPLOAD_ERR_NO_FILE', 'El archivo seleccionado en el campo "%s" - no se ha subido');
define('I_UPLOAD_ERR_NO_TMP_DIR', 'No hay una carpeta temporal en el servidor para descargar el archivo del campo "%s"');
define('I_UPLOAD_ERR_CANT_WRITE', 'El archivo seleccionado para cargar en el campo "%s" no se pudo escribir en el disco duro del servidor');
define('I_UPLOAD_ERR_EXTENSION', 'Una de las extensiones PHP que se ejecuta en el servidor dejó de cargar el archivo desde el campo "%s"');
define('I_UPLOAD_ERR_UNKNOWN', 'La carga del archivo a "%s" falló debido a un error desconocido');

define('I_UPLOAD_ERR_REQUIRED', 'Debes seleccionar un archivo');
define('I_WGET_ERR_ZEROSIZE', 'No se pudo descargar el archivo a "%s" usando el enlace web porque este archivo está vacío');

define('I_FORM_UPLOAD_SAVETOHDD', 'Guardar en el disco');
define('I_FORM_UPLOAD_ORIGINAL', 'Mostrar original');
define('I_FORM_UPLOAD_NOCHANGE', 'Salir');
define('I_FORM_UPLOAD_DELETE', 'Eliminar');
define('I_FORM_UPLOAD_REPLACE', 'Reemplazar');
define('I_FORM_UPLOAD_REPLACE_WITH', 'sobre el');
define('I_FORM_UPLOAD_NOFILE', 'Desaparecido');
define('I_FORM_UPLOAD_BROWSE', 'Seleccione');
define('I_FORM_UPLOAD_MODE_TIP', 'Descargar a través del enlace web');
define('I_FORM_UPLOAD_MODE_LOCAL_PLACEHOLDER', 'archivo de su PC ..');
define('I_FORM_UPLOAD_MODE_REMOTE_PLACEHOLDER', 'archivo por enlace web ..');

define('I_FORM_UPLOAD_ASIMG', 'imagen');
define('I_FORM_UPLOAD_ASOFF', 'documento');
define('I_FORM_UPLOAD_ASDRW', 'diseño gráfico');
define('I_FORM_UPLOAD_ASARC', 'archivo');
define('I_FORM_UPLOAD_OFEXT', 'tener una extensión');
define('I_FORM_UPLOAD_INFMT', 'en el formato');
define('I_FORM_UPLOAD_HSIZE', 'tener una talla');
define('I_FORM_UPLOAD_NOTGT', 'no más');
define('I_FORM_UPLOAD_NOTLT', 'no menos');
define('I_FORM_UPLOAD_FPREF', 'Foto de %s');

define('I_FORM_DATETIME_HOURS', 'horas');
define('I_FORM_DATETIME_MINUTES', 'minutos');
define('I_FORM_DATETIME_SECONDS', 'segundos');
define('I_COMBO_OF', 'de');
define('I_COMBO_MISMATCH_MAXSELECTED', 'El número máximo de opciones seleccionadas es');
define('I_COMBO_MISMATCH_DISABLED_VALUE', 'El valor "%s" no se puede seleccionar en el campo "%s"');
define('I_COMBO_KEYWORD_NO_RESULTS', 'Nada Encontrado');
define('I_COMBO_ODATA_FIELD404', 'El campo "%s" no es un campo real ni un pseudocampo');
define('I_COMBO_GROUPBY_NOGROUP', 'Sin afiliación');
define('I_COMBO_WAND_TOOLTIP', 'Cree una nueva opción en esta lista desplegable <br> utilizando el nombre indicado en este campo');

define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_TITLE', 'Registro no encontrado');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_START', 'Entre el conjunto de registros disponibles en esta sección,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_SPM', 'basado en las opciones de búsqueda actuales');
define('I_ACTION_FORM_TOPBAR_NAVTOROWID_NOT_FOUND_MSGBOX_MSG_END', '- no hay registro con esta ID');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_TITLE', 'Registro #');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_OF', 'de');

define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_TITLE', 'Registro no encontrado');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_START', 'Entre el conjunto de registros disponibles en esta sección,');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_SPM', 'basado en las opciones de búsqueda actuales');
define('I_ACTION_FORM_TOPBAR_NAVTOROWOFFSET_NOT_FOUND_MSGBOX_MSG_END', '- no hay registro con dicho número de serie, pero al momento de cargar el formulario era');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_NO_SUBSECTIONS', 'Están ausentes');
define('I_ACTION_FORM_TOPBAR_NAVTOSUBSECTION_SELECT', '--Seleccione--');

define('I_ACTION_INDEX_KEYWORD_LABEL', 'Buscar ...');
define('I_ACTION_INDEX_KEYWORD_TOOLTIP', 'Buscar todas las columnas');
define('I_ACTION_INDEX_SUBSECTIONS_LABEL', 'Subsecciones');
define('I_ACTION_INDEX_SUBSECTIONS_VALUE', '--Seleccione--');
define('I_ACTION_INDEX_SUBSECTIONS_NO', 'Están ausentes');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE', 'Mensaje');
define('I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG', 'Seleccionar fila');
define('I_ACTION_INDEX_FILTER_TOOLBAR_TITLE', 'Filtros');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM', 'desde');
define('I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO', 'antes de');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM', 'C');
define('I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO', 'por');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_YES', 'si');
define('I_ACTION_INDEX_FILTER_TOOLBAR_CHECK_NO', 'No');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE', 'Restablecer todos los filtros');
define('I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG', 'Los filtros ya se han restablecido o no se utilizan actualmente.');

define('I_ACTION_DELETE_CONFIRM_TITLE', 'la confirmación');
define('I_ACTION_DELETE_CONFIRM_MSG', '¿Está seguro de que desea eliminar la entrada?');

define('I_SOUTH_PLACEHOLDER_TITLE', 'El contenido de este panel se abre en una ventana separada.');
define('I_SOUTH_PLACEHOLDER_GO', 'Ir');
define('I_SOUTH_PLACEHOLDER_TOWINDOW', 'a la ventana');
define('I_SOUTH_PLACEHOLDER_GET', 'Regreso');
define('I_SOUTH_PLACEHOLDER_BACK', 'contenido de vuelta a aquí');

define('I_DEMO_ACTION_OFF', 'Esta acción está deshabilitada en el modo de demostración.');

define('I_MCHECK_REQ', 'Campo "%s": obligatorio');
define('I_MCHECK_REG', 'El valor "%s" del campo "%s" está en el formato incorrecto');
define('I_MCHECK_KEY', 'Objeto de tipo "%s" con identificador "%s" - no encontrado');
define('I_MCHECK_EQL', 'Valor incorrecto');
define('I_MCHECK_DIS', 'El valor "%s" del campo "%s" está en la lista de valores no disponibles');
define('I_MCHECK_UNQ', 'El valor "%s" del campo "%s" - debe ser único');
define('I_JCHECK_REQ', 'El parámetro "%s" - es obligatorio');
define('I_JCHECK_REG', 'El valor "%s" del parámetro "%s" está en el formato incorrecto');
define('I_JCHECK_KEY', 'Objeto de tipo "%s" con identificador "%s" - no encontrado');
define('I_JCHECK_EQL', 'Valor incorrecto');
define('I_JCHECK_DIS', 'El valor "%s" del parámetro "%s" está en la lista de valores no disponibles');
define('I_JCHECK_UNQ', 'Valor "%s" del parámetro "%s": debe ser exclusivo');

define('I_PRIVATE_DATA', '* los datos están ocultos *');

define('I_WHEN_DBY', 'antier');
define('I_WHEN_YST', 'ayer');
define('I_WHEN_TOD', 'Hoy');
define('I_WHEN_TOM', 'mañana');
define('I_WHEN_DAT', 'Pasado mañana');
define('I_WHEN_WD_ON1', 'a');
define('I_WHEN_WD_ON2', 'en');
define('I_WHEN_TM_AT', 'a');

define('I_LANG_LAST', 'No se puede eliminar el último registro del tipo "%s"');
define('I_LANG_CURR', 'No puede eliminar un idioma que es el idioma actual del sistema');
define('I_LANG_FIELD_L10N_DENY', 'No se puede habilitar la localización para el campo "%s"');