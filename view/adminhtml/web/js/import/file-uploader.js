define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, uiAlert, Element) {
    'use strict';

    return Element.extend({

        /**
         * Handler of the file upload complete event.
         *
         * @param {Event} event
         * @param {Object} data
         */
        onFileUploaded: function (event, data) {
            this._super(event, data);

            let error = data.result.error ?? undefined;
            if (!error) {
                uiAlert({
                    title: $.mage.__('Import complete'),
                    content: $.mage.__('Template successfully imported. The page will be reloaded.'),
                    actions: {
                        always: function(){
                            $('body').loader('show');
                            location.reload();
                        }
                    }
                });
            }
        },
    });

});
