Ext.define('Indi.Login', {
    extend: 'Ext.Panel',
    id: 'i-login-panel',
    renderTo: 'i-login-box',
    titleAlign: 'center',
    height: 125,
    width: 300,
    bodyPadding: 10,
    items: [
        {
            xtype: 'textfield',
            id: 'i-login-box-username',
            fieldLabel: Indi.lang.I_LOGIN_BOX_USERNAME,
            labelWidth: 90,
            value: Ext.util.Cookies.get('i-username'),
            width: 275
        },{
            xtype: 'textfield',
            id: 'i-login-box-password',
            inputType: 'password',
            fieldLabel: Indi.lang.I_LOGIN_BOX_PASSWORD,
            value: Ext.util.Cookies.get('i-password'),
            labelWidth: 90,
            width: 246,
            cls: 'i-inline-block'
        },{
            xtype: 'checkboxfield',
            id: 'i-login-box-remember',
            checked: Ext.util.Cookies.get('i-remember') !== null,
            margin: '0 0 2 8',
            cls: 'i-inline-block',
            tooltip: {
                html: 'Запомнить',
                anchor: 'left',
                staticOffset: [0, -3]
            }
        },{
            xtype: 'button',
            id: 'i-login-box-submit',
            text: Indi.lang.I_LOGIN_BOX_ENTER,
            margin: '4 0 0 20',
            width: 113,
            handler: function(){

                // Prepare the request data
                var data = {
                    username: Ext.getCmp('i-login-box-username').getValue(),
                    password: Ext.getCmp('i-login-box-password').getValue(),
                    remember: Ext.getCmp('i-login-box-remember').getValue(),
                    enter: true
                }

                // Make an authentication request
                $.post(Indi.pre + '/', data, function(response){

                    // If request returned an error, display a Ext.MessageBox
                    if (response.error) {
                        Ext.MessageBox.show({
                            title: Indi.lang.I_LOGIN_ERROR_MSGBOX_TITLE,
                            msg: response.error,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });

                        // Else signin was ok, we check if 'remember' checkbox was checked,
                        // and if so - set the cookies. After that we do a page reload
                    } else if (response.ok) {

                        // Delete 'enter' property from 'data' object for it to be ready to
                        // set or remove cookies for it's all remaining properties
                        delete data.enter;

                        // For each remaining property in 'data' object
                        for (var i in data)

                            // If 'remember' checkbox was checked, we create cookie
                            if (data.remember)
                                Ext.util.Cookies.set(
                                    'i-' + i,
                                    data[i],

                                    // We set cookie expire date as 1 month
                                    Ext.Date.add(new Date(), Ext.Date.MONTH, 1),
                                    Indi.pre
                                );

                            // Else we delete cookie
                            else Ext.util.Cookies.clear(i, Indi.pre);

                        // Reload window contents
                        window.location.replace(Indi.pre + '/');
                    }
                }, 'json');
            }
        },{
            xtype: 'button',
            id: 'i-login-box-reset',
            text: Indi.lang.I_LOGIN_BOX_RESET,
            margin: '4 0 0 10',
            width: 113,
            handler: function(){
                Ext.getCmp('i-login-box-username').setValue();
                Ext.getCmp('i-login-box-password').setValue();
                Ext.getCmp('i-login-box-remember').setValue(false);
            }
        }
    ],
    listeners: {
        afterRender: function(){
            this.keyNav = Ext.create('Ext.util.KeyNav', this.el, {
                enter: function(){
                    Ext.getCmp('i-login-box-submit').handler();
                }
            });
        }
    }
});