/**
 * Base class for all controller actions instances, that operate with rowsets
 */
Ext.define('Indi.lib.controller.action.Rowset', {

    // @inheritdoc
    alternateClassName: 'Indi.Controller.Action.Rowset',

    // @inheritdoc
    extend: 'Indi.Controller.Action',

    // @inheritdoc
    mcopwso: ['store', 'rowset'],

    // @inheritdoc
    panel: {

        /**
         * Array of action-button aliases, that have special icons
         */
        toolbarMasterItemActionIconA: ['form', 'delete', 'save', 'toggle', 'up', 'down'],

        /**
         * Provide store autoloading once panel panel is rendered
         */
        listeners: {
            afterrender: function(me){
                setTimeout(function(){
                    me.ctx().getStore().load();
                });
                Indi.trail(true).breadCrumbs();
            }
        },

        /**
         * Tools special config
         */
        tools: [{alias: 'reset'}],

        /**
         * Docked items special config
         */
        docked: {
            default: {minHeight: 27},
            items: [{alias: 'filter'}, {alias: 'master'}],
            inner: {
                master: [{alias: 'actions'}, '-', {alias: 'nested'}, '->', {alias: 'keyword'}]
            }
        }
    },

    /**
     * Extjs's Store config object for current section
     *
     * @type {*}
     */
    store: {
        method: 'POST',
        remoteSort: true,
        proxy:  new Ext.data.HttpProxy({
            method: 'POST',
            reader: {
                type: 'json',
                root: 'blocks',
                totalProperty: 'totalCount',
                idProperty: 'id'
            }
        }),
        listeners: {
            beforeload: function(){
                this.ctx().filterChange({noReload: true});
            },
            load: function(){
                this.ctx().storeLoadCallbackDefault();
                this.ctx().storeLoadCallback();
            }
        }
    },

    /**
     * Get store, that current action is dealing with
     *
     * @return {*}
     */
    getStore: function() {
        return Ext.getStore(this.bid() + '-store');
    },

    /**
     * Handler for any filter change
     *
     * @param cmp Component, that fired filterChange
     */
    filterChange: function(cmp){

        // Setup auxilliary variables/shortcuts
        var me = this, fieldsetCmpId = me.bid() + '-toolbar-filter-fieldset';

        // Declare and fulfil an array with properties, available for each row in the rowset
        var columnA = []; for (i = 0; i < me.ti().gridFields.length; i++) columnA.push(me.ti().gridFields[i].alias);

        // Declare an array for params, which will be fulfiled with filters's values
        var paramA = [];

        // Declare an array for filter fields (that are currently use for search), that are presented in a list
        // of properties, available for each row within a rowset, retrived by this.getStore(). We will need that
        // array bit later, to be able to determine if corresponding filters are used for all available
        // properties, and if so - keyword component from keyword toolbar should be disabled, because search
        // mechanism, that keyword component is involved in - is searching value, inputted in keyword field,
        // only within available properties. For example, if come row have a details field (as a HTML-editor)
        // which is not in the list of available properties (because list of available properties - is the same
        // almost the same as available grid columns, if Ext.panel.Grid is used to represent a rowset) - the
        // value, inputted in keyword search field - will not be searched in that details field.
        var usedFilterAliasesThatHasGridColumnRepresentedByA = [];

        // Get all filter components
        var filterCmpA = Ext.ComponentQuery.query('#' + fieldsetCmpId + ' > [name]');

        // Foreach filter component id in filterCmpIdA array
        for (var i = 0; i < filterCmpA.length; i++) {

            // We do not involve values of hidden or disabled filter components in request query building
            if (filterCmpA[i].hidden || filterCmpA[i].disabled) continue;

            // Define a shortcut for filter filed alias
            var alias = filterCmpA[i].name;

            // Get current filter value
            var value = filterCmpA[i].getValue();

            // If current filter is filter for color-field, and it's value is [0, 360], we set 'value' variable
            // as '' (empty string) because such value for color field filter mean that filter is not used
            if (filterCmpA[i].xtype == 'multislider' && JSON.stringify(filterCmpA[i].getValue()) == '[0,360]')
                value = '';

            // If value is not empty
            if (value + '' != '' && value !== null) {

                // Prepare param object for storing current filter value. We will be using separate objects for
                // each used filter, e.g [{property1: "value1"}, {property2: "value2"}], instead of single object
                // {property1: "value1", property2: "value2"}, because it's the way of how extjs use it, for
                // passing sorting params within store request, so here we just do by the same way
                var paramO = {};

                // If current filter is a ext's datefield components
                if (filterCmpA[i].xtype == 'datefield') {

                    // If format of date, used in ext's datafield component - differs from 'Y-m-d'
                    if (filterCmpA[i].format != 'Y-m-d') {

                        // We get the raw value in that format, convert it back to 'Y-m-d' format
                        // and assign to paramO's object certain property as a current filter value
                        paramO[alias] = Ext.Date.format(
                            Ext.Date.parse(filterCmpA[i].getRawValue(), filterCmpA[i].format),
                            'Y-m-d'
                        );

                    // Else we just assign the value to param's object certain property as a current filter value
                    } else paramO[alias] = filterCmpA[i].getRawValue();

                // Else if current filter is not a ext's datetime component
                } else {

                    // We just assign the value to param's object certain property as a current filter value, too
                    paramO[alias] = filterCmpA[i].getValue();
                }

                // Push the paramO object to the param stack
                paramA.push(paramO);

                // If current filter field alias is within an array of available properties (columns)
                for (var j =0; j < columnA.length; j++)
                    if (columnA[j] == alias.replace(/-(g|l)te$/, '') &&
                        usedFilterAliasesThatHasGridColumnRepresentedByA.indexOf(
                            alias.replace(/-(g|l)te$/, '')) == -1)

                        // We remember that, by pushing curren filter field alias to the
                        usedFilterAliasesThatHasGridColumnRepresentedByA.push(alias.replace(/-(g|l)te$/, ''));

            }
        }

        // Apply collected used filter alises and their values as a this.getStore().proxy.extraParams property
        me.getStore().getProxy().extraParams = {search: JSON.stringify(paramA)};

        // Get id of the keyword component
        var keywordCmpId = me.bid() + '-toolbar-master-keyword';

        // Get the value of keyword component, if component is not disabled
        var keyword = Ext.getCmp(keywordCmpId) && Ext.getCmp(keywordCmpId).disabled == false &&
            Ext.getCmp(keywordCmpId).getValue() ? Ext.getCmp(keywordCmpId).getValue() : '';

        // Append `keyword` property to the request extra params
        if (keyword) me.getStore().getProxy().extraParams.keyword = keyword;

        // Adjust an 'url' property of  this.getStore().proxy object, to apply keyword search usage
        me.getStore().getProxy().url = Indi.pre + '/' + me.ti().section.alias + '/index/' +
            (me.ti(1).row ? 'id/' + me.ti(1).row.id + '/' : '') + 'json/1/';

        // Disable keyword component, if all available properties are already involved in search by
        // corresponding filters usage
        if (Ext.getCmp(keywordCmpId))
            Ext.getCmp(keywordCmpId).setDisabled(usedFilterAliasesThatHasGridColumnRepresentedByA.length == columnA.length);

        // If there is no noReload flag turned on
        if (!cmp.noReload) {

            // We reset the page's number, that should be retrieved by search, to 1, because if it currently
            // is not 1, there is a possiblity of no results displayed, as there is no guarantee, that there
            // will be enough results found matched new filters params, to display the same page. Example:
            // we were in a Countries section, where were ~300 countries, and we were at page 6, which mean
            // that countries from (if countries-on-page = 25) 126 to 150 were displayed. So if we use filters
            // or keyword search, but page number will remain the same (6) - there should be at least 126 results
            // matched our keyword/filters search, to at least 1 row to be displayed, but there is no guarantee
            // that there will be such number of results, that match our search criteria
            me.getStore().currentPage = 1;
            me.getStore().lastOptions.page = 1;
            me.getStore().lastOptions.start = 0;

            // If used filter is a combobox or multislider, we reload store data immideatly
            if (['combobox', 'combo.filter', 'multislider'].indexOf(cmp.xtype) != -1) {
                me.getStore().reload();

            // Else if used filter is not a datefield, or is, but it's value matches proper date format or
            // value is empty, we reload store data with a 500ms delay, because direct typing is allowed in that
            // datefield, so it's better to reload after user has finished typing.
            } else if (cmp.xtype != 'datefield' || (/^([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{2}\.[0-9]{2}\.[0-9]{4})$/
                .test(cmp.getRawValue()) || !cmp.getRawValue().length)) {
                clearTimeout(me.getStore().timeout);
                me.getStore().timeout = setTimeout(function(){
                    me.getStore().reload();
                }, 500);
            }
        }
    },

    // @inheritdoc
    constructor: function(config) {
        var me = this;

        // Setup trailLevel property
        if (config.trailLevel) me.trailLevel = config.trailLevel;

        // Merge configs
        me.mergeParent(config);

        // Setup store config
        me.store = Ext.merge({
            id: me.bid() + '-store',
            fields: me.storeFieldA(),
            sorters: me.storeSorters(),
            pageSize: parseInt(me.ti().section.rowsOnPage),
            currentPage: me.storeCurrentPage(),
            ctx: Ext.Component.prototype.ctx
        }, me.store);

        // Create store
        Ext.create('Ext.data.Store', me.store);

        // Call panret
        me.callParent(arguments);
    },

    /**
     * Build and return array of panel tools
     *
     * @return {Array}
     */
    panelToolA: function() {
        return this.push(this.panel.tools, 'panelTool', false, function(itemI){
            return itemI.type ? itemI: null;
        });
    },

    /**
     * Filters reset tool config builder
     *
     * @return {Object}
     */
    panelTool$Reset: function() {
        var me = this;

        // We add the filter-reset tool only if there is at least one filter defined for current section
        if (!me.ti().filters.length) return null;

        // Append tool data object to the 'tools' array
        return {
            type: 'search',
            cls: 'i-tool-search-reset',
            handler: function(event, target, owner, tool){

                // Prepare a prefix for filter component ids
                var filterCmpIdPrefix = me.bid() + '-toolbar-filter-';

                // Setup a flag, what will
                var atLeastOneFilterIsUsed = false;

                // We define an array of functions, first within which will check if at least one filter is used
                // and if so, second will do a store reload
                var loopA = [function(cmp, control){
                    if (control == 'color') {
                        if (cmp.getValue().join() != '0,360') atLeastOneFilterIsUsed = true;
                    } else {
                        if ([null, ''].indexOf(cmp.getValue()) == -1) {
                            if (JSON.stringify(cmp.getValue()) != '[""]') {
                                atLeastOneFilterIsUsed = true;
                            }
                        }
                    }
                }, function(cmp, control){
                    if (control == 'color') {
                        cmp.setValue(0, 0, false);
                        cmp.setValue(1, 360, false);
                    } else {
                        cmp.setValue('');
                    }
                }];

                // We iterate throgh filter twice - for each function within loopA array
                for (var l = 0; l < loopA.length; l++) {

                    // We prevent unsetting filters values if they are already empty
                    if (l == 1 && atLeastOneFilterIsUsed == false) break;

                    // For each filter
                    for (var i = 0; i < me.ti().filters.length; i++) {

                        // Define a shortcut for filter field alias
                        var alias =  me.ti().filters[i].foreign('fieldId').alias;

                        // Shortcut for control element, assigned to filter field
                        var control = me.ti().filters[i].foreign('fieldId').foreign('elementId').alias;

                        // If current filter is a range-filter, we reset values for two filter components, that
                        // are representing min and max values
                        if (['number', 'calendar', 'datetime'].indexOf(control) != -1) {

                            // Range filters limits's postfixes
                            var limits = ['gte', 'lte'];

                            // Setup empty values for range filters
                            for (var j = 0; j < limits.length; j++) {
                                Ext.getCmp(filterCmpIdPrefix + alias + '-' + limits[j]).noReload = true;
                                loopA[l](Ext.getCmp(filterCmpIdPrefix + alias + '-' + limits[j]));
                                Ext.getCmp(filterCmpIdPrefix + alias + '-' + limits[j]).noReload = false;
                            }

                            // Else we reset one filter component
                        } else if (control == 'color') {

                            // Resetted values for color multislider filter
                            var v = [0, 360];

                            // Set a value for each multislider thumb
                            for (var j = 0; j < v.length; j++) {
                                Ext.getCmp(filterCmpIdPrefix + alias).noReload = true;
                                loopA[l](Ext.getCmp(filterCmpIdPrefix + alias), control);
                                Ext.getCmp(filterCmpIdPrefix + alias).noReload = false;
                            }

                            // Else set by original way
                        } else {
                            Ext.getCmp(filterCmpIdPrefix + alias).noReload = true;
                            loopA[l](Ext.getCmp(filterCmpIdPrefix + alias));
                            Ext.getCmp(filterCmpIdPrefix + alias).noReload = false;
                        }
                    }
                }

                // Reload store for empty filter values to be picked up.
                // We do reload only in case if at least one filter was emptied by reset filter tool
                if (atLeastOneFilterIsUsed) me.filterChange({});

                // Otherwise we display a message box saying that filters cannot be emptied because
                // they are already empty
                else Ext.MessageBox.show({
                    title: Indi.lang.I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_TITLE,
                    msg: Indi.lang.I_ACTION_INDEX_FILTERS_ARE_ALREADY_EMPTY_MSG,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.INFO
                });
            }
        }
    },

    /**
     * Panel filter toolbar builder
     *
     * @return {Object}
     */
    panelDocked$Filter: function() {
        var me = this;

        // 'Filter' toolbar config
        return {
            xtype: 'toolbar',
            dock: 'top',
            hidden: !me.ti().filters.length &&
                (!me.panel.docked.inner || !me.panel.docked.inner.filter || !me.panel.docked.inner.filter.length),
            padding: '1 5 5 5',
            id: me.bid() + '-toolbar-filter',
            layout: 'auto',
            items: [{
                xtype:'fieldset',
                id: me.bid()+'-toolbar-filter-fieldset',
                padding: '0 0 1 3',
                title: Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_TITLE,
                width: '100%',
                layout: 'column',
                defaults: {
                    margin: '0 5 4 2',
                    labelSeparator: '',
                    labelPad: 6,
                    labelStyle: 'padding-left: 0'
                },
                items: me.panelDocked$FilterItemA(),
                listeners: {
                    afterrender: function(){
                        me.setFilterValues();
                    }
                }
            }]
        }
    },

    /**
     * Build and return array of filter toolbar items configs
     *
     * @return {Array}
     */
    panelDocked$FilterItemA: function() {

        // Declare toolbar filter panel items array, and some additional variables
        var me = this, itemA = [], itemI, itemICustom, moreItemA = [];

        // Fulfil items array
        for (var i = 0; i < me.ti().filters.length; i++) {
            itemI = me.panelDocked$Filter_Default(me.ti().filters[i]);
            itemICustom = 'panelDocked$Filter$' + Indi.ucfirst(me.ti().filters[i].foreign('fieldId').alias);
            if (typeof me[itemICustom] == 'function') itemI = me[itemICustom](itemI);
            if (itemI) {
                if (!itemI.name) itemI.name = me.ti().filters[i].foreign('fieldId').alias;
                itemA = itemA.concat(itemI.length ? itemI: [itemI]);
            }
        }

        // Setup non-regular filters
        if (me.panel.docked && me.panel.docked.inner && me.panel.docked.inner.filter && me.panel.docked.inner.filter.length)
        moreItemA = me.push(me.panel.docked.inner.filter, 'panelDocked$Filter', false, function(itemI){
            return Ext.merge({
                listeners: {
                    change: function(cmp) {
                        me.filterChange(cmp);
                    }
                }
            }, itemI);
        });

        // Append them
        itemA = itemA.concat(moreItemA);

        // Return filter toolbar items
        return itemA;
    },

    panelDocked$Filter$Customfilter: function() {
        return {
            xtype: 'textfield',
            name: 'table'
        }
    },

    /**
     * Builds and returns default/initial config for all filter panel items
     *
     * @return {Object}
     */
    panelDocked$Filter_Default: function(filter) {

        // Setup auxilliary variables
        var me = this, itemI, itemIDefault, control = filter.foreign('fieldId').foreign('elementId').alias;

        // Setup default filter config, builded upon filter field's xtype
        itemIDefault = 'panelDocked$FilterX' + Indi.ucfirst(control);
        if (typeof me[itemIDefault] == 'function') itemI = me[itemIDefault](filter);

        // Return default config
        return itemI;
    },

    /**
     * Combo-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXCombo: function(filter) {

        // Setup auxilliary variables/shortcuts
        var me = this, field = filter.foreign('fieldId'), alias = field.alias, filterCmpId = me.bid() + '-toolbar-filter$'
            + alias, fieldLabel = filter.alt || field.title, row = me.ti().filtersSharedRow;

        // Push the special extjs component data object to represent needed filter. Component consists of
        // two hboxed components. First is extjs label component, and second - is setup to pick up
        // a custom DOM node as it's contentEl property. This DOM node is already prepared by non-extjs
        // solution, implemented in IndiEngine
        return {
            id: filterCmpId,
            xtype: 'combo.filter',
            fieldLabel : fieldLabel,
            labelWidth: Indi.metrics.getWidth(fieldLabel),
            field: field,
            value: Ext.isNumeric(row[field.alias]) ? parseInt(row[field.alias]) : row[field.alias],
            subTplData: row.view(field.alias).subTplData,
            store: row.view(field.alias).store
        }
    },

    /**
     * Radio-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXRadio: function(filter) {
        return this.panelDocked$FilterXCombo(filter);
    },

    /**
     * Check-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXCheck: function(filter) {
        return this.panelDocked$FilterXCombo(filter);
    },

    /**
     * Multicheck-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXMulticheck: function(filter) {
        return this.panelDocked$FilterXCombo(filter);
    },

    /**
     * Keyword-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXString: function(filter) {

        // Setup auxilliary variables/shortcuts
        var me = this, alias = filter.foreign('fieldId').alias, fieldLabel = filter.alt || filter.foreign('fieldId').title;

        // 'String' item config
        return {
            xtype: 'textfield',
            id: me.bid() + '-toolbar-filter$' + alias,
            fieldLabel: fieldLabel,
            labelWidth: Indi.metrics.getWidth(fieldLabel),
            hiddenName: alias,
            width: 80 + Indi.metrics.getWidth(fieldLabel),
            margin: 0,
            listeners: {
                change: function(cmp){
                    if (!cmp.noReload) me.filterChange(cmp);
                }
            }
        }
    },

    /**
     * Textarea-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXTextarea: function(filter) {
        return this.panelDocked$FilterXString(filter);
    },

    /**
     * Html-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXHtml: function(filter) {
        return this.panelDocked$FilterXString(filter);
    },

    /**
     * Calendar-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXCalendar: function(filter) {

        // Setup auxilliary variables/shortcuts
        var me = this, alias = filter.foreign('fieldId').alias, filterCmpId = this.bid() + '-toolbar-filter$' + alias,
            dateFormat, fieldLabel, datefieldFrom, datefieldUntil;

        // Get date format
        dateFormat = filter.foreign('fieldId').params['display' +
            (filter.foreign('fieldId').foreign('elementId').alias == 'datetime' ? 'Date': '') + 'Format'] || 'Y-m-d';

        // Get the label for filter minimal value component
        fieldLabel = (filter.alt ?
            filter.alt :
            filter.foreign('fieldId').title) + ' ' +
            Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_DATE_FROM;

        // Prepare the data for extjs datefield component, for use as control for filter minimal value
        datefieldFrom = {
            xtype: 'datefield',
            id: filterCmpId + '-gte',
            name: alias + '-gte',
            fieldLabel: fieldLabel,
            labelWidth: Indi.metrics.getWidth(fieldLabel),
            width: 85 + Indi.metrics.getWidth(fieldLabel),
            startDay: 1,
            validateOnChange: false,
            listeners: {
                change: function(cmp){
                    if (!cmp.noReload) me.filterChange(cmp);
                }
            }
        };

        // Prepare the data for extjs datefield component, for use as control for filter maximal value
        datefieldUntil = {
            xtype: 'datefield',
            id: filterCmpId + '-lte',
            name: alias + '-lte',
            fieldLabel: Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO,
            labelWidth: Indi.metrics.getWidth(Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO),
            width: 85 + Indi.metrics.getWidth(Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_DATE_TO),
            startDay: 1,
            validateOnChange: false,
            listeners: {
                change: function(cmp){
                    if (!cmp.noReload) me.filterChange(cmp);
                }
            }
        };

        // Append a number of format-related properties to the data objects
        Ext.merge(datefieldFrom, {format: dateFormat, ariaTitleDateFormat: dateFormat, longDayFormat: dateFormat});
        Ext.merge(datefieldUntil, {format: dateFormat, ariaTitleDateFormat: dateFormat, longDayFormat: dateFormat});

        // Append the extjs datefield components to filters stack, for minimum and maximum
        return [datefieldFrom, datefieldUntil];
    },

    /**
     * Datetime-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXDatetime: function(filter) {
        return this.panelDocked$FilterXCalendar(filter);
    },

    /**
     * Number-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXNumber: function(filter) {

        // Setup auxilliary variables/shortcuts
        var me = this, alias = filter.foreign('fieldId').alias, filterCmpId = me.bid() + '-toolbar-filter$' + alias,
            fieldLabel, gte, lte;

        // Get the label
        fieldLabel = (filter.alt || filter.foreign('fieldId').title) + ' ' +
            Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_FROM;

        // Append the extjs numberfield component data object to filters stack, for minimum value
        gte = {
            xtype: 'numberfield',
            id: filterCmpId + '-gte',
            name: alias + '-gte',
            fieldLabel: fieldLabel,
            labelWidth: Indi.metrics.getWidth(fieldLabel),
            width: 50 + Indi.metrics.getWidth(fieldLabel),
            margin: 0,
            minValue: 0,
            listeners: {
                change: function(cmp){
                    if (!cmp.noReload) me.filterChange(cmp);
                }
            }
        };

        // Append the extjs numberfield component data object to filters stack, for maximum value
        lte = {
            xtype: 'numberfield',
            id: filterCmpId + '-lte',
            name: alias + '-lte',
            fieldLabel: Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO,
            labelWidth: Indi.metrics.getWidth(Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO),
            width: 50 + Indi.metrics.getWidth(Indi.lang.I_ACTION_INDEX_FILTER_TOOLBAR_NUMBER_TO),
            margin: 0,
            minValue: 0,
            listeners: {
                change: function(cmp){
                    if (!cmp.noReload) me.filterChange(cmp);
                }
            }
        };

        // Return pair of items
        return [gte, lte];
    },

    /**
     * Color-filters configurator function
     *
     * @param filter
     * @return {Object}
     */
    panelDocked$FilterXColor: function(filter) {

        // Setup auxilliary variables/shortcuts
        var me = this, alias = filter.foreign('fieldId').alias, filterCmpId = me.bid() + '-toolbar-filter$' + alias,
            fieldLabel = filter.alt || filter.foreign('fieldId').title;

        // Append the extjs multislider component data object to filters stack, as multislider will
        // be the approriate way to represent color hue range (0 to 360)
        return {
            xtype: 'multislider',
            id: filterCmpId,
            fieldLabel: fieldLabel,
            labelWidth: Indi.metrics.getWidth(fieldLabel),
            labelClsExtra: 'i-multislider-color-label',
            values: [0, 360],
            increment: 1,
            minValue: 0,
            maxValue: 360,
            constrainThumbs: false,
            // Hue bg width + label width + labelPad + thumb-overlap * number-of-thumbs
            width: 183 + Indi.metrics.getWidth(fieldLabel) + 5 + 7 * 2,
            margin: '1 0 0 0',
            cls: 'i-multislider-color',
            listeners: {
                changecomplete: function(cmp){
                    if (!cmp.noReload) me.filterChange(cmp);
                }
            }
        }
    },

    /**
     * Panel master toolbar builder
     *
     * @return {Object}
     */
    panelDocked$Master: function() {

        // Master toolbar cfg
        return {
            id: this.bid() + '-toolbar-master',
            items: this.panelDocked$MasterItemA()
        }
    },

    /**
     * Build and return array of master toolbar items configs
     *
     * @return {Array}
     */
    panelDocked$MasterItemA: function() {
        var merged = [], pushed = this.push(this.panel.docked.inner['master'], 'panelDockedInner', true);
        for (var i = 0; i < pushed.length; i++) merged = merged.concat(pushed[i]);
        return merged;
    },

    /**
     * Build and return array of configs of master toolbar items, that represent action-buttons
     *
     * @return {Array}
     */
    panelDockedInner$Actions: function() {

        // Setup auxillirary variables
        var me = this, actionItemA = [], actionItem, actionItemCustom, actionItemCreate = me.panelDockedInner$Actions$Create();

        // Append 'Create' action button
        if (actionItemCreate) actionItemA.push(actionItemCreate);

        // Append other action buttons
        for (var i = 0; i < me.ti().actions.length; i++) {
            actionItem = me.panelDockedInner$Actions_Default(me.ti().actions[i]);
            actionItemCustom = 'panelDockedInner$Actions$'+Indi.ucfirst(me.ti().actions[i].alias);
            if (typeof me[actionItemCustom] == 'function') actionItem = me[actionItemCustom](actionItem);
            if (actionItem) actionItemA.push(actionItem);
        }

        // Return
        return actionItemA;
    },

    /**
     * Builds and returns config for master toolbar 'Create' action-button item
     *
     * @return {Object}
     */
    panelDockedInner$Actions$Create: function(){

        // Check if 'save' and 'form' actions are allowed
        var me = this, canSave = false, canForm = false, canAdd = me.ti().section.disableAdd == '0';
        for (var i = 0; i < me.ti().actions.length; i++) {
            if (me.ti().actions[i].alias == 'save') canSave = true;
            if (me.ti().actions[i].alias == 'form') canForm = true;
        }

        // 'Create' button will be added only if it was not switched off
        // in section config and if 'save' and 'form' actions are allowed
        if (canForm && canSave && canAdd) {

            // Return cfg
            return {
                id: me.bid() + '-toolbar-master-button-create',
                tooltip: Indi.lang.I_CREATE,
                iconCls: 'i-btn-icon-create',
                actionAlias: 'form',
                handler: function(){
                    Indi.load(me.ti().section.href + this.actionAlias + '/ph/' + me.ti().section.primaryHash + '/');
                }
            }
        }
    },

    /**
     * Builds and returns default/initial config for all action-button master panel items
     *
     * @return {Object}
     */
    panelDockedInner$Actions_Default: function(action) {

        // If action is visible
        if (action.display == 1) {

            // Basic action object
            var actionItem = {
                id: this.bid() + '-toolbar-master-button-' + action.alias,
                text: action.title,
                action: action,
                actionAlias: action.alias,
                rowRequired: action.rowRequired,
                javascript: action.javascript,
                handler: function(){

                    // Get selection
                    var selection = Ext.getCmp('i-center-center-wrapper')
                        .getComponent(0)
                        .getSelectionModel()
                        .getSelection();

                    // Get first selected row and it's index, if selected
                    if (selection.length) {
                        var row = selection[0].data;
                        var aix = selection[0].index + 1;
                    }

                    // If there is no rows selected, but at elast one should
                    if (this.rowRequired == 'y' && !selection.length) {

                        // Display a message box with approriate warning
                        Ext.MessageBox.show({
                            title: Indi.lang.I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE,
                            msg: Indi.lang.I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });

                        // Run the handler
                    } else {
                        if (typeof this.javascript == 'function') this.javascript(); else eval(this.javascript);
                    }
                }
            }

            // Setup iconCls property, if need
            if (this.panel.toolbarMasterItemActionIconA.indexOf(action.alias) != -1) {
                actionItem.iconCls = 'i-btn-icon-' + action.alias;
                actionItem.text = '';
                actionItem.tooltip = action.title;
            }

            // Put to the actions stack
            return actionItem;
        }
    },

    /**
     * Master toolbar 'Nested' item, for ability to navigate to selected row's nested entries lists
     *
     * @return {Object}
     */
    panelDockedInner$Nested: function(){
        var me = this;

        // 'Nested' item config
        return {
            id: me.bid() + '-toolbar-master-nested',
            xtype: 'shrinklist',
            displayField: 'title',
            hidden: !me.ti().sections.length,
            tooltip: {
                html: Indi.lang.I_NAVTO_NESTED,
                hideDelay: 0,
                showDelay: 1000,
                dismissDelay: 2000,
                staticOffset: [0, 1]
            },
            store: {
                xtype: 'store',
                fields: ['alias', 'title'],
                data : me.ti().sections
            },
            listeners: {
                itemclick: function(sl, row) {

                    // Get selection
                    var selection = Ext.getCmp('i-center-center-wrapper').getComponent(0).getSelectionModel().getSelection();

                    // If no selection - show a message box
                    if (selection.length == 0) {
                        Ext.MessageBox.show({
                            title: Indi.lang.I_ACTION_INDEX_SUBSECTIONS_WARNING_TITLE,
                            msg: Indi.lang.I_ACTION_INDEX_SUBSECTIONS_WARNING_MSG,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING

                        });

                    // Else load the nested subsection contents
                    } else if (row.get('alias')) Indi.load(Indi.pre + '/' + row.get('alias') + '/index/id/'
                        + selection[0].data.id + '/' + 'ph/' + me.ti().scope.hash + '/aix/' + (selection[0].index + 1)+'/');
                }
            }
        }
    },

    /**
     * Master toolbar 'Keyword' item, for ability to use a keyword-search
     * within the currently available rows scope
     *
     * @return {Object}
     */
    panelDockedInner$Keyword: function() {
        var me = this;

        // 'Keyword' item config
        return {
            id: me.bid() + '-toolbar-master-keyword',
            xtype: 'textfield',
            fieldLabel: Indi.lang.I_ACTION_INDEX_KEYWORD_LABEL,
            labelWidth: Indi.metrics.getWidth(Indi.lang.I_ACTION_INDEX_KEYWORD_LABEL),
            labelClsExtra: 'i-action-index-keyword-toolbar-keyword-label',
            labelSeparator: '',
            value: me.ti().scope.keyword ? Indi.urldecode(me.ti().scope.keyword) : '',
            width: 100 + Indi.metrics.getWidth(Indi.lang.I_ACTION_INDEX_KEYWORD_LABEL),
            height: 19,
            cls: 'i-form-text',
            margin: '0 0 0 5',
            listeners: {
                change: function(tf){
                    clearTimeout(tf.timeout);
                    tf.timeout = setTimeout(function(){
                        me.filterChange({});
                    }, 500);
                }
            }
        }
    },

    /**
     * Build and return an array, containing definition of data, that will be got by this.store's request
     *
     * @return {Array}
     */
    storeFieldA: function (){

        // Setup auxilliary variables/shortcuts
        var me = this, fieldA = [], fieldI$Id = me.storeField$Id(), fieldI, fieldICustom;

        // Push 'id' store field to fields configs array
        if (fieldI$Id) fieldA.push(fieldI$Id);

        // Other fields
        for (var i = 0; i < me.ti().gridFields.length; i++) {
            fieldI = me.storeField_Default(me.ti().gridFields[i]);
            fieldICustom = 'storeField$' + Indi.ucfirst(me.ti().gridFields[i].alias);
            if (typeof me[fieldICustom] == 'function') fieldI = me[fieldICustom](fieldI);
            if (fieldI) fieldA.push(fieldI);
        }

        // Return array
        return fieldA;
    },

    /**
     * Builds and returns default/initial config for all store fields (except 'id' field)
     *
     * @return {Object}
     */
    storeField_Default: function(field) {
        return {
            name: field.alias,
            type: !parseInt(field.entityId) && [3,5].indexOf(field.columnTypeId) != -1 ? 'int' : 'string'
        }
    },

    /**
     * Builds and returns config for store 'id' field
     *
     * @return {Object}
     */
    storeField$Id: function() {
        return {name: 'id', type: 'int'}
    },

    /**
     * Set up and return store full request string (but without paging params)
     */
    storeLastRequest: function(){

        // Setup auxilliary variables/shortcuts
        var me = this, url = me.getStore().getProxy().url, get = [];

        // If filters were used during last store request, we retrieve info about, encode and append it to 'get'
        if (me.getStore().getProxy().extraParams.search)
            get.push('search=' + encodeURIComponent(me.getStore().getProxy().extraParams.search));

        // If keyword was used during last store request, we retrieve info about, encode and append it to 'get'
        if (me.getStore().getProxy().extraParams.keyword)
            get.push('keyword=' + encodeURIComponent(me.getStore().getProxy().extraParams.keyword));

        // If sorters were used during last store request, we retrieve info about, encode and append it to 'get'
        if (me.getStore().getSorters().length)
            get.push('sort=' + encodeURIComponent(JSON.stringify(me.getStore().getSorters())));

        // Return the full url string
        return url + (get.length ? '?' + get.join('&') : '');
    },

    /**
     * Prepare and return a sorters for this.store
     *
     * @return {Array}
     */
    storeSorters: function(){
        var me = this;

        // If we have sorting params, stored in scope - we use them
        if (me.ti().scope.order && eval(me.ti().scope.order).length)
            return eval(me.ti().scope.order);

        // Else we use current section's default sorting params, if specified
        else if (me.ti().section.defaultSortField)
            return [{
                property : me.ti().section.defaultSortFieldAlias,
                direction: me.ti().section.defaultSortDirection
            }];

        // Else no sorting at all
        return [];
    },

    /**
     * Internal callback for store load/reload
     */
    storeLoadCallbackDefault: function() {
        this.ti().scope = this.getStore().proxy.reader.jsonData.scope;
    },

    /**
     * Callback function for store load/reload. Is for use in subclasses
     */
    storeLoadCallback: Ext.emptyFn,

    /**
     * Determines this.store's current page. At first it will try to get it from this.ti().scope, at it it fails
     *  - return 1
     *
     * @return {*}
     */
    storeCurrentPage: function(){
        return this.ti().scope.page ? parseInt(this.ti().scope.page): 1;
    },

    /**
     * Gets a value, stored in scope for filter, by given filter alias
     *
     * @param alias
     * @return {*}
     */
    getScopeFilter: function(alias){

        // If there is no filters used at all - return
        if (this.ti().scope.filters == null) return;

        // Setup initial value for `value` variable as 'undefined'
        var value = undefined;

        // Filter values are stored in this.ti().scope as a stringified json array, so we need to convert it back,
        // to be able to find something there
        var values = eval(this.ti().scope.filters);

        // Find a filter value
        for (var i = 0; i < values.length; i++)
            if (values[i].hasOwnProperty(alias))
                value = values[i][alias];

        // Return value
        return value;
    },

    /**
     * Assign values to filters, before store load, for store to be loaded with respect to filter params.
     * These values will be got from this.ti().scope.filters, and if there is no value for some filter there - then
     * we'll try to get that in this.ti().filters[i].defaultValue. If there will no value too - then
     * filter will be empty.
     */
    setFilterValues: function(){
        var me = this, name, control, def, d, filterCmpId, already = [];

        // Foreach filter
        for (var i = 0; i < me.ti().filters.length; i++) {

            // Create a shortcut for filter field alias
            name = me.ti().filters[i].foreign('fieldId').alias;

            // Append name to `already` array. This will be needed bit later,
            // in the process of assigning values for non-regular filters,
            // Non-regular filters are those that are not in me.ti().filters array
            already.push(name);

            // Create a shortcut for filter field control element alias
            control = me.ti().filters[i].foreign('fieldId').foreign('elementId').alias;

            // At first, we check if current scope contain the value for the current filter, and if so - we use
            // that value instead of filter's own default value, whether it was defined or not. Also, we
            // implement a bit different behaviour for range-filters (number, calendar, datetime) and for other
            // types of filters
            if (['number', 'calendar', 'datetime'].indexOf(control) != -1) {

                // Object for default values
                def = {};

                // Assign the 'gte' and 'lte' properties to the object of default values
                if (['undefined', ''].indexOf(me.getScopeFilter(name + '-gte') + '') == -1)
                    def.gte = me.getScopeFilter(name + '-gte');
                if (['undefined', ''].indexOf(me.getScopeFilter(name + '-lte') + '') == -1)
                    def.lte = me.getScopeFilter(name + '-lte');

                // If at least 'gte' or 'lte' properies was set, we assing 'def' object as filter default value
                if (Object.getOwnPropertyNames(def).length) me.ti().filters[i].defaultValue = def;

            // Else current filter is not a range-filter
            } else if (me.getScopeFilter(name)) {

                // Just assign the value, got from scope as filter default value
                me.ti().filters[i].defaultValue = me.getScopeFilter(name);
            }

            // Finally, if filter has a non-null default value
            if (me.ti().filters[i].defaultValue) {

                // Setup a shortcut for filter's default value
                d = me.ti().filters[i].defaultValue;

                // Prepare the id for current filter component
                filterCmpId = me.bid() + '-toolbar-filter-' + me.ti().filters[i].foreign('fieldId').alias;

                // If current filter is a range filter - set up min and/or max separately
                if (['number', 'calendar', 'datetime'].indexOf(control) != -1) {

                    // If default value is a stringified javascript array of javascript object, we convert it back
                    if ((typeof d == 'string') && (d.match(/^\[.*\]$/) || d.match(/^\{.*\}$/))) d = eval('('+ d + ')');

                    // If default value is an object
                    if (typeof d == 'object')

                        // Foreach property in default value object (which nameds can be 'gte' and 'lte' only)
                        for (var j in d) {

                            // If there is actually no component for this filter - try next iteration
                            if (!Ext.getCmp(filterCmpId + '-' + j)) continue;

                            // Toggle 'noReload' property to 'true' to prevend store reload, because we do not need it
                            // to be reloaded at this time. We will need that later, after all values will be assigned
                            // to filters
                            Ext.getCmp(filterCmpId + '-' + j).noReload = true;

                            // Set filter value
                            Ext.getCmp(filterCmpId + '-' + j).setValue(d[j]);

                            // Revert back 'noReload' property to 'false'
                            Ext.getCmp(filterCmpId + '-' + j).noReload = false;
                        }

                // Else current filter is not a renge-filter
                } else {

                    // If there is actually no component for this filter - try next iteration
                    if (!Ext.getCmp(filterCmpId)) continue;

                    // Toggle 'noReload' property to 'true' to prevent store reload
                    Ext.getCmp(filterCmpId).noReload = true;

                    // If filter is for multiple combo - set value as array, joined by comma
                    if (me.ti().filters[i].foreign('fieldId').storeRelationAbility == 'many')
                        Ext.getCmp(filterCmpId).setValue(typeof d == 'string' ? d : d.join(','));

                    // Else if filter is for color field, that is represented by two-thumb multislider
                    // we set values separately for each thumb. According to Ext docs, there is a way to set
                    // value at once, but for some reason that way gives an error, so we need to use the same
                    // method (setValue) but with an alternative set of agruments
                    else if (control == 'color') {

                        // If color multislider default value is a stringified array e.g "[123, 234]", we should
                        // convert it back
                        if (typeof d == 'string') d = eval(d);

                        // Set a value for each multislider thumb
                        for (var j = 0; j < d.length; j++)
                            Ext.getCmp(filterCmpId).setValue(j, d[j], false);


                    // Else set by original way
                    } else
                        Ext.getCmp(filterCmpId).setValue(d);

                    // Revert back 'noReload' property to 'false'
                    Ext.getCmp(filterCmpId).noReload = false;
                }
            }
        }

        // Get all filter components
        var fieldsetCmpId = me.bid() + '-toolbar-filter-fieldset', value,
            filterCmpA = Ext.ComponentQuery.query('#' + fieldsetCmpId + ' > [name]');

        // Foreach filter component
        for (i = 0; i < filterCmpA.length; i++) {

            // Get it's name
            name = filterCmpA[i].name;

            // Ensure that value hasn't yet been assigned to component, and
            // current scope contains a value for that component, and if so - assign it
            if (already.indexOf(name) == -1 && (value = me.getScopeFilter(name))) {
                filterCmpA[i].noReload = true;
                filterCmpA[i].setValue(value);
                filterCmpA[i].noReload = false;
            }
        }
    }
});