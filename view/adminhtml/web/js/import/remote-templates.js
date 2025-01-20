define([
    'jquery',
    'uiComponent',
    'mage/template',
    'underscore'
], function ($, Component, template, _) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            //TODO move template xhr call at modal opening (add also a loader)
            this.reloadTemplates();
            return this;
        },

        /**
         * @public
         */
        reloadTemplates: function () {
            let self = this;
            $.ajax({
                url: this.templateListUrl,
                showLoader: true,
                data: {
                    'form_key': FORM_KEY
                },
                type: 'POST',
                success: $.proxy(function (response) {
                    $(self.containerSelector).html();
                    let remoteTemplateTpl = template('#pagebuilder-remote-template-item');
                    _.each(response.templates.items, function(template) {
                        console.log(template);
                        let templateItem = remoteTemplateTpl({
                            data: {
                                name: template['name'],
                                id: template['id'],
                                thumb: "data:image/jpeg;base64," + template['preview']
                            }
                        });
                        $(self.containerSelector).append(templateItem);
                    });
                }, this)
            });
        }
    });

});
