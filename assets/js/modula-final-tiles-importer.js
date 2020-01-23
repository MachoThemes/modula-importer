( function( $ ){
    "use strict";

    var modulaFinalTilesImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('form#modula_importer_final_tiles').submit(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('form#modula_importer_final_tiles input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_ftg_importer_settings.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('form#modula_importer_final_tiles :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaFinalTilesImporter.counts = id_array.length;
                modulaFinalTilesImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){

            var delete_entries = 'keep';

            if($('#delete-old-entries').prop('checked')){
                delete_entries = 'delete';
            }

            galleries_ids.forEach( function( gallery_id ){

                var status = $('form#modula_importer_final_tiles label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_ftg_importer_settings.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_final_tiles',
                        id: gallery_id,
                        nonce: modula_ftg_importer_settings.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {
                        if ( ! response.success ) {
                            status.find('span').text(response.message);
                            return;
                        }

                        modulaFinalTilesImporter.completed = modulaFinalTilesImporter.completed + 1;

                        // Display result from AJAX call
                        status.find('span').html(response.message);

                        // Remove one ajax from queue
                        modulaFinalTilesImporter.ajaxStarted = modulaFinalTilesImporter.ajaxStarted - 1;
                        if(modulaFinalTilesImporter.counts == modulaFinalTilesImporter.completed){
                            modulaFinalTilesImporter.updateImported(galleries_ids,delete_entries);
                        }
                    }
                };
                modulaFinalTilesImporter.ajaxRequests.push( opts );
                // $.ajax(opts);

            });
            modulaFinalTilesImporter.runAjaxs();

        },

        runAjaxs: function() {
            var currentAjax;

            while( modulaFinalTilesImporter.ajaxStarted < 5 && modulaFinalTilesImporter.ajaxRequests.length > 0 ) {
                modulaFinalTilesImporter.ajaxStarted = modulaFinalTilesImporter.ajaxStarted + 1;
                currentAjax = modulaFinalTilesImporter.ajaxRequests.shift();
                $.ajax( currentAjax );

            }

            if ( modulaFinalTilesImporter.ajaxRequests.length > 0 ) {
                modulaFinalTilesImporter.ajaxTimeout = setTimeout(function() {
                    console.log( 'Delayed 1s' );
                    modulaFinalTilesImporter.runAjaxs();
                }, 1000);
            }else{
                $('form#modula_importer_final_tiles :input').prop('disabled', false);
            }

        },
        // Update imported galleries
        updateImported: function(galleries_ids,delete_entries){

            var data = {
                action: 'modula_importer_final_tiles_update_imported',
                galleries: galleries_ids,
                clean: delete_entries,
                nonce: modula_ftg_importer_settings.nonce,
            };

            $.post(ajaxurl,data,function(response){

            });
        }

    };

    $( document ).ready(function(){
        modulaFinalTilesImporter.init();
    });

})( jQuery );