define([
    'jquery',
    'jquery/ui'
], function($){
    $.widget('mage.templateImportExportRefreshToken', {
        options: {
            fieldArrayRowId: "",
        },
        /**
         * Widget initialization
         * @private
         */
        _create: function() {
            console.log(this.options.fieldArrayRowId);
        }
    });

    return $.mage.templateImportExportRefreshToken;
});
