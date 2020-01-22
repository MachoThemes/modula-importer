( function( $ ){
    "use strict";

    var modulaPhotoblocksImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('form#modula_importer_photoblocks').submit(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('form#modula_importer_photoblocks input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_pb_importer_settings.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('form#modula_importer_photoblocks :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaPhotoblocksImporter.counts = id_array.length + 1;
                modulaPhotoblocksImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){

            var delete_entries = 'keep';

            if($('#delete-old-entries').prop('checked')){
                delete_entries = 'delete';
            }

            galleries_ids.forEach( function( gallery_id ){

                var status = $('form#modula_importer_photoblocks label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_pb_importer_settings.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_photoblocks',
                        id: gallery_id,
                        nonce: modula_pb_importer_settings.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {
                        if ( ! response.success ) {
                            return;
                        }

                        modulaPhotoblocksImporter.completed = modulaPhotoblocksImporter.completed + 1;

                        // Display result from AJAX call
                        status.find('span').text(response.message);

                        // Remove one ajax from queue
                        modulaPhotoblocksImporter.ajaxStarted = modulaPhotoblocksImporter.ajaxStarted - 1;
                    }
                };
                modulaPhotoblocksImporter.ajaxRequests.push( opts );
                // $.ajax(opts);

            });
            modulaPhotoblocksImporter.runAjaxs();
        },

        runAjaxs: function() {
            var currentAjax;

            while( modulaPhotoblocksImporter.ajaxStarted < 5 && modulaPhotoblocksImporter.ajaxRequests.length > 0 ) {
                modulaPhotoblocksImporter.ajaxStarted = modulaPhotoblocksImporter.ajaxStarted + 1;
                currentAjax = modulaPhotoblocksImporter.ajaxRequests.shift();
                $.ajax( currentAjax );

            }

            if ( modulaPhotoblocksImporter.ajaxRequests.length > 0 ) {
                modulaPhotoblocksImporter.ajaxTimeout = setTimeout(function() {
                    console.log( 'Delayed 1s' );
                    modulaPhotoblocksImporter.runAjaxs();
                }, 1000);
            }else{
                $('form#modula_importer_photoblocks :input').prop('disabled', false);
            }

        },

    };

    $( document ).ready(function(){
        modulaPhotoblocksImporter.init();
    });

})( jQuery );