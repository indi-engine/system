/**
 * Here we override Ext.Component component, to provide an ability for 'tooltip' config properties to be used for
 * creating Ext.tip.ToolTip objects instead of standart Ext.tip.QuickTip objects
 */
Ext.override(Ext.Component, {

    /**
     * Get the context of a component. Context here mean the current action object fired within certain controller object
     * @return {*}
     */
    ctx: function() {
        var trailLevel = this.trailLevel != undefined ? this.trailLevel : Ext.getCmp('i-center-center-wrapper').trailLevel;
        var trailItem = Indi.trail(trailLevel - (Indi.trail(true).store.length - 1));
        return Indi.app.getController(trailItem.section.alias).actions[trailItem.action.alias];
    },

    /**
     * Get the current trail item, that was in power at the monent of component instantiation
     * @return {*}
     */
    ti: function(){
        var trailLevel = this.trailLevel != undefined ? this.trailLevel : Ext.getCmp('i-center-center-wrapper').trailLevel;
        return Indi.trail(trailLevel - (Indi.trail(true).store.length - 1));
    },

    // @inheritdoc
    afterRender: function() {
        var me = this;

        // Define tooltip getter
        me.getToolTip = function() {
            return Ext.getCmp(me.id + '-tooltip');
        };

        /*me.ctx = function() {
            var trailLevel = Ext.getCmp('i-center-center-wrapper').trailLevel;
            var trailItem = Indi.trail(trailLevel - (Indi.trail(true).store.length - 1));
            return Indi.app.getController(trailItem.section.alias).actions[trailItem.action.alias];
        }*/

        // If 'tooltip' property was defined, create the tooltip object
        if (me.tooltip) Ext.tip.ToolTip.create(me);

        // Call parent
        me.callParent();

        // Set position on the page
        if (!(me.x && me.y) && (me.pageX || me.pageY)) {
            me.setPagePosition(me.pageX, me.pageY);
        }
    },

    /**
     * This property's name is an abbreviation that stands for 'Merge Config Object-Properties With Superclass Ones'.
     * Property represents the list of properties, that should be merged through all superclass hierarchy, starting
     * from current component instance and up to it's most top superclass, instead of simple overwriting that properties
     */
    mcopwso: [],

    /**
     * Provide taking in effect for `mcopwso` property
     *
     * @param config Config object, passed to `constructor` method call
     */
    mergeParent: function(config) {
        var initialMcopwso = this.mcopwso.join(',').split(',');
        var obj = this;
        while (obj.superclass) {
            if (obj.superclass.mcopwso && obj.superclass.mcopwso.length)
                for (var i = 0; i < obj.superclass.mcopwso.length; i++)
                    if (this.mcopwso.indexOf(obj.superclass.mcopwso[i]) == -1)
                        this.mcopwso.push(obj.superclass.mcopwso[i]);
            obj = obj.superclass;
        }
        obj = this;
        if (this.mcopwso.length) while (obj.superclass) {
            for (var i = 0; i < this.mcopwso.length; i++)
                if (this[this.mcopwso[i]] && obj.superclass && obj.superclass[this.mcopwso[i]])
                    this[this.mcopwso[i]]
                        = Ext.merge(Ext.clone(obj.superclass[this.mcopwso[i]]), this[this.mcopwso[i]]);

            obj = obj.superclass;
        }
        for (var i = 0; i < initialMcopwso.length; i++) {
            if (typeof config == 'object' && typeof config[initialMcopwso[i]] == 'object') {
                this[initialMcopwso[i]] = Ext.merge(this[initialMcopwso[i]], config[initialMcopwso[i]]);
                delete config[initialMcopwso[i]];
            }
        }
    },

    // @inheritdoc
    constructor: function(config){
        this.mergeParent(config);
        this.callParent(arguments);
    },

    /**
     * Allows addition of behavior to the 'destroy' operation.
     * After calling the superclass’s onDestroy, the Component will be destroyed.
     *
     * @template
     * @protected
     */
    onDestroy: function() {
        var me = this;

        // Destroy the tooltip, if exists
        if (me.tooltip && me.getToolTip()) {
            if (me.getToolTip().getEl() && me.getToolTip().getEl().getActiveAnimation())
                me.getToolTip().getEl().getActiveAnimation().end();
            me.getToolTip().destroy();
        }

        // Call parent
        me.callParent();
    }
});