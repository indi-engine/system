<?php
// Array of js files to be imploded
$js = [
    '/js/jquery-1.9.1.min.js',
    '/library/extjs4/ext-all.js',

    '/js/admin/app/lib/calendar/view/AbstractCalendar.js',
    '/js/admin/app/lib/calendar/template/BoxLayout.js',
    '/js/admin/app/lib/calendar/template/DayHeader.js',
    '/js/admin/app/lib/calendar/view/MonthDayDetail.js',
    '/js/admin/app/lib/calendar/util/Date.js',
    '/js/admin/app/lib/calendar/util/WeekEventRenderer.js',
    '/js/admin/app/lib/calendar/template/Month.js',
    '/js/admin/app/lib/calendar/view/Month.js',
    '/js/admin/app/lib/calendar/view/DayHeader.js',
    '/js/admin/app/lib/calendar/template/DayBody.js',
    '/js/admin/app/lib/calendar/data/EventMappings.js',
    '/js/admin/app/lib/calendar/dd/StatusProxy.js',
    '/js/admin/app/lib/calendar/dd/DragZone.js',
    '/js/admin/app/lib/calendar/dd/DayDragZone.js',
    '/js/admin/app/lib/calendar/dd/DropZone.js',
    '/js/admin/app/lib/calendar/dd/DayDropZone.js',
    '/js/admin/app/lib/calendar/view/DayBody.js',
    '/js/admin/app/lib/calendar/view/Day.js',
    '/js/admin/app/lib/calendar/view/Week.js',
    '/js/admin/app/lib/calendar/CalendarPanel.js',

    '/library/extjs4/ext-lang-' . ini()->lang->admin . '.js',
    '/library/extjs4/examples/ux/BoxReorderer.js',
    '/library/extjs4/examples/ux/TabReorderer.js',
    '/library/extjs4/examples/ux/CheckColumn.js',

    '/js/admin/app/override/Ext.Base.js',
    '/js/admin/app/override/Ext.data.Connection.js',
    '/js/admin/app/override/Ext.dom.Element.js',
    '/js/admin/app/override/Ext.dom.CompositeElementLite.js',
    '/js/admin/app/override/Ext.ZIndexManager.js',
    '/js/admin/app/override/Ext.button.Button.js',
    '/js/admin/app/override/Ext.tip.ToolTip.js',
    '/js/admin/app/override/Ext.Component.js',
    '/js/admin/app/override/Ext.form.action.Submit.js',
    '/js/admin/app/override/Ext.form.field.Base.js',
    '/js/admin/app/override/Ext.form.field.Number.js',
    '/js/admin/app/override/Ext.form.field.Date.js',
    '/js/admin/app/override/Ext.picker.Date.js',
    '/js/admin/app/override/Ext.form.field.Checkbox.js',
    '/js/admin/app/override/Ext.grid.feature.Summary.js',
    '/js/admin/app/override/Ext.grid.View.js',
    '/js/admin/app/override/Ext.data.Model.js',
    '/js/admin/app/override/Ext.tab.Bar.js',
    '/js/admin/app/override/Ext.panel.Panel.js',
    '/js/admin/app/override/Ext.toolbar.Toolbar.js',
    '/js/admin/app/override/Ext.form.Panel.js',
    '/js/admin/app/override/Ext.grid.header.Container.js',
    '/js/admin/app/override/Ext.grid.Panel.js',
    '/js/admin/app/override/Ext.tab.Panel.js',
    '/js/admin/app/override/Ext.grid.plugin.Editing.js',
    '/js/admin/app/override/Ext.grid.column.Column.js',

    '/js/admin/app/ux/Ext.ux.form.field.plugin.InputMask.js',

    '/js/admin/indi.js',

    '/js/admin/app/util/Shrinkable.js',

    '/js/admin/app/view/LoginBox.js',
    '/js/admin/app/view/Menu.js',
    '/js/admin/app/view/Viewport.js',
    '/js/admin/app/lib/trail/Button.js',
    '/js/admin/app/view/desktop/Window.js',
    '/js/admin/app/view/desktop/WindowButton.js',
    '/js/admin/app/view/desktop/WindowBar.js',
    '/js/admin/app/view/desktop/TaskBar.js',

    '/js/admin/app/lib/chart/HighStock.js',
    '/js/admin/app/lib/chart/HighStockSerie.js',

    '/js/admin/app/lib/view/action/south/South.js',
    '/js/admin/app/lib/view/action/south/Row.js',
    '/js/admin/app/lib/view/action/south/Rowset.js',
    '/js/admin/app/lib/view/action/Panel.js',
    '/js/admin/app/lib/view/action/Rowset.js',
    '/js/admin/app/lib/view/action/Row.js',
    '/js/admin/app/lib/view/action/Tab.js',
    '/js/admin/app/lib/view/action/TabRowset.js',
    '/js/admin/app/lib/view/action/TabRow.js',
    '/js/admin/app/lib/view/dialer/Dialer.js',
    '/js/admin/app/lib/view/dialer/Button.js',

    '/js/admin/app/lib/trail/Trail.js',
    '/js/admin/app/lib/trail/Item.js',
    '/js/admin/app/lib/dbtable/Row.js',
    '/js/admin/app/lib/view/ShrinkList.js',
    '/js/admin/app/lib/form/field/Combo.js',
    '/js/admin/app/lib/toolbar/Info.js',
    '/js/admin/app/lib/toolbar/Filter.js',
    '/js/admin/app/lib/form/field/SiblingCombo.js',
    '/js/admin/app/lib/form/field/CellCombo.js',
    '/js/admin/app/lib/form/field/AutoCombo.js',
    '/js/admin/app/lib/form/field/FilterCombo.js',
    '/js/admin/app/lib/form/field/CkEditor.js',
    '/js/admin/app/lib/form/field/FilePanel.js',
    '/js/admin/app/lib/form/field/Radios.js',
    '/js/admin/app/lib/form/field/MultiCheck.js',
    '/js/admin/app/lib/form/field/Phone.js',
    '/js/admin/app/lib/form/field/TimeSpan.js',
    '/js/admin/app/lib/form/field/Color.js',

    '/js/admin/app/lib/form/field/Time.js',
    '/js/admin/app/lib/picker/DateTime.js',
    '/js/admin/app/lib/picker/Color.js',
    '/js/admin/app/lib/form/field/DateTime.js',

    '/js/admin/app/lib/controller/Controller.js',
    '/js/admin/app/lib/controller/action/Action.js',
    '/js/admin/app/lib/controller/action/Rowset.js',
    '/js/admin/app/lib/controller/action/Grid.js',
    '/js/admin/app/lib/controller/action/Chart.js',
    '/js/admin/app/lib/controller/action/ChangeLog.js',
    '/js/admin/app/lib/controller/action/Calendar.js',
    '/js/admin/app/lib/controller/action/Row.js',
    '/js/admin/app/lib/controller/action/Form.js',
    '/js/admin/app/lib/controller/action/Print.js',
    '/js/admin/app/lib/controller/action/Call.js',
    '/wrtc.js'
];

// If google maps API key defined - append GmapPanel.js
if (ini('gmap')->key) $js[] = '/library/extjs4/examples/ux/GMapPanel.js';
if (ini('ymap')->mode) $js[] = '/library/extjs4/examples/ux/YMapPanel.js';

// Array of css files to be imploded
$css = [
    '/library/extjs4/resources/css/ext-all.css',
    '/library/extjs4/examples/ux/css/CheckHeader.css',
    '/library/extjs4/resources/css/colorpicker.css',
    '/css/admin/indi.all.css',
    '/css/admin/indi.all.default.css',
    '/css/admin/indi.layout.css',
    '/css/admin/indi.action.form.css',
    '/css/admin/indi.trail.css',
    '/css/admin/indi.combo.css',
    '/css/admin/indi.combo.default.css',
    '/css/admin/indi.calendar.css',
    '/css/admin/indi.dialer.css'
];
// Implode js files
Indi::implode(['/application/lang/admin/' . ini()->lang->admin . '.php:Indi$lang'], ini()->lang->admin);
Indi::implode($js, isIE() ? 'ie' : null);

// Implode css files
Indi::implode($css, isIE() ? 'ie' : null);
