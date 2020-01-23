( function( $ ){
    "use strict";

    var modulaNextgenImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('form#modula_importer_nextgen').submit(function (e) {
                e.preventDefault();
                modulaNextgenImporter.completed = 0;

                // Check if gallery was selected
                var galleries = $('form#modula_importer_nextgen input[name=gallery]:checked');

                if (0 == galleries.length) {
                    alert(modula_nextgen_importer_settings.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('form#modula_importer_nextgen :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaNextgenImporter.counts = id_array.length;
                modulaNextgenImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){

            var delete_entries = 'keep';

            if($('#delete-old-entries').prop('checked')){
                delete_entries = 'delete';
            }

            galleries_ids.forEach( function( gallery_id ){

                var status = $('form#modula_importer_nextgen label[data-id=' + gallery_id + ']');
                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_nextgen_importer_settings.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_nextgen_gallery_import',
                        id: gallery_id,
                        nonce: modula_nextgen_importer_settings.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {

                        if ( ! response.success ) {
                            status.find('span').text(response.message);
                            return;
                        }

                        modulaNextgenImporter.completed = modulaNextgenImporter.completed + 1;

                        // Display result from AJAX call
                        status.find('span').html(response.message);

                        // Remove one ajax from queue
                        modulaNextgenImporter.ajaxStarted = modulaNextgenImporter.ajaxStarted - 1;

                        if(modulaNextgenImporter.counts == modulaNextgenImporter.completed ){
                            modulaNextgenImporter.updateImported(galleries_ids);
                        }
                    },
                };
                modulaNextgenImporter.ajaxRequests.push( opts );


            });

            modulaNextgenImporter.runAjaxs();
        },

        runAjaxs: function() {
            var currentAjax;

            while( modulaNextgenImporter.ajaxStarted < 5 && modulaNextgenImporter.ajaxRequests.length > 0 ) {
                modulaNextgenImporter.ajaxStarted = modulaNextgenImporter.ajaxStarted + 1;
                currentAjax = modulaNextgenImporter.ajaxRequests.shift();
                $.ajax( currentAjax );
            }

            if ( modulaNextgenImporter.ajaxRequests.length > 0 ) {
                modulaNextgenImporter.ajaxTimeout = setTimeout(function() {
                    console.log( 'Delayed 1s' );
                    modulaNextgenImporter.runAjaxs();
                }, 1000);
            }else{
                $('form#modula_importer_nextgen :input').prop('disabled', false);
            }

        },
        // Update imported galleries
        updateImported: function(galleries_ids){

            var data = {
                action: 'modula_importer_nextgen_gallery_imported_update',
                galleries: galleries_ids,
                nonce: modula_nextgen_importer_settings.nonce,
            };

            $.post(ajaxurl,data,function(response){

            });
        },
    };

    $( document ).ready(function(){
        modulaNextgenImporter.init();
    });
})( jQuery );
