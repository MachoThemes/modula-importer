( function( $ ){
    "use strict";

    var modulaWPCoreImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('form#modula_importer_wp_core').submit(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('form#modula_importer_wp_core input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_wp_core_gallery_importer_settings.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('form#modula_importer_wp_core :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaWPCoreImporter.counts = id_array.length + 1;
                modulaWPCoreImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){

            galleries_ids.forEach( function( gallery_id ){

                var status = $('form#modula_importer_wp_core label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_wp_core_gallery_importer_settings.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_wp_core_gallery_import',
                        id: gallery_id,
                        nonce: modula_wp_core_gallery_importer_settings.nonce
                    },
                    success: function( response ) {
                        if ( ! response.success ) {
                            return;
                        }

                        modulaWPCoreImporter.completed = modulaWPCoreImporter.completed + 1;

                        // Display result from AJAX call
                        status.find('span').text(response.message);

                        // Remove one ajax from queue
                        modulaWPCoreImporter.ajaxStarted = modulaWPCoreImporter.ajaxStarted - 1;
                    }
                };
                modulaWPCoreImporter.ajaxRequests.push( opts );
                // $.ajax(opts);

            });
            modulaWPCoreImporter.runAjaxs();
        },

        runAjaxs: function() {
            var currentAjax;

            while( modulaWPCoreImporter.ajaxStarted < 5 && modulaWPCoreImporter.ajaxRequests.length > 0 ) {
                modulaWPCoreImporter.ajaxStarted = modulaWPCoreImporter.ajaxStarted + 1;
                currentAjax = modulaWPCoreImporter.ajaxRequests.shift();
                $.ajax( currentAjax );

            }

            if ( modulaWPCoreImporter.ajaxRequests.length > 0 ) {
                modulaWPCoreImporter.ajaxTimeout = setTimeout(function() {
                    console.log( 'Delayed 1s' );
                    modulaWPCoreImporter.runAjaxs();
                }, 1000);
            }else{
                $('form#modula_importer_wp_core :input').prop('disabled', false);
            }

        },

    };

    $( document ).ready(function(){
        modulaWPCoreImporter.init();
    });

})( jQuery );