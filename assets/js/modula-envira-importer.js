( function( $ ){
    "use strict";

    var modulaEnviraImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('form#modula_importer_envira').submit(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_importer_settings.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('form#modula_importer_envira :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaEnviraImporter.counts = id_array.length + 1;
                modulaEnviraImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){
            galleries_ids.forEach( function( gallery_id ){
                
                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_envira_gallery_import',
                        id: gallery_id,
                        nonce: modula_importer_settings.nonce
                    },
                    success: function( response ) {
                        if ( ! response.success ) {
                            return;
                        }

                        modulaEnviraImporter.completed = modulaEnviraImporter.completed + 1;
                        var status = $('form#modula_importer_envira label[data-id=' + gallery_id + ']');

                        // Display result from AJAX call
                        status.find('span').text(response.message);

                        // Remove one ajax from queue
                        modulaEnviraImporter.ajaxStarted = modulaEnviraImporter.ajaxStarted - 1;
                    }
                };
                modulaEnviraImporter.ajaxRequests.push( opts );
                // $.ajax(opts);

            });
            modulaEnviraImporter.runAjaxs();
        },

        runAjaxs: function() {
            var currentAjax;

            while( modulaEnviraImporter.ajaxStarted < 5 && modulaEnviraImporter.ajaxRequests.length > 0 ) {
                modulaEnviraImporter.ajaxStarted = modulaEnviraImporter.ajaxStarted + 1;
                currentAjax = modulaEnviraImporter.ajaxRequests.shift();
                $.ajax( currentAjax );
            }

            if ( modulaEnviraImporter.ajaxRequests.length > 0 ) {
                modulaEnviraImporter.ajaxTimeout = setTimeout(function() {
                    console.log( 'Delayed 1s' );
                    modulaEnviraImporter.runAjaxs();
                }, 1000);
            }else{
                $('form#modula_importer_envira :input').prop('disabled', false);
            }

        },

    };

    $( document ).ready(function(){
        modulaEnviraImporter.init();
    });

})( jQuery );