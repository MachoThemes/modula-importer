( function( $ ){
    "use strict";


    /**
     * Modula Importer for Envira
     *
     * @type {{init: init, runAjaxs: runAjaxs, ajaxTimeout: null, counts: number, processAjax: processAjax, ajaxRequests: [], completed: number, updateImported: updateImported, ajaxStarted: number}}
     */
    var modulaEnviraImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('#modula_importer_envira input[type="submit"]').click(function (e) {
                e.preventDefault();
                modulaEnviraImporter.completed = 0;

                // Check if gallery was selected
                var galleries = $('#modula_importer_envira input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_importer.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('#modula_importer_envira :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaEnviraImporter.counts = id_array.length;
                modulaEnviraImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){

            var delete_entries = 'keep';

            if($('#delete-old-entries').prop('checked')){
                delete_entries = 'delete';
            }

            galleries_ids.forEach( function( gallery_id ){

                var status = $('#modula_importer_envira label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_importer.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_envira_gallery_import',
                        id: gallery_id,
                        nonce: modula_importer.nonce,
                        clean: delete_entries
                    },
                    success: function( response ) {
                        modulaEnviraImporter.completed = modulaEnviraImporter.completed + 1;
                        if ( ! response.success ) {
                            status.find('span').text(response.message);

                            if(modulaEnviraImporter.counts == modulaEnviraImporter.completed){

                                modulaEnviraImporter.updateImported(galleries_ids,delete_entries);
                            }
                            return;
                        }

                        /* modulaEnviraImporter.completed = modulaEnviraImporter.completed + 1;*/

                        // Display result from AJAX call
                        status.find('span').html(response.message);

                        // Remove one ajax from queue
                        modulaEnviraImporter.ajaxStarted = modulaEnviraImporter.ajaxStarted - 1;

                        if(modulaEnviraImporter.counts == modulaEnviraImporter.completed){
                            modulaEnviraImporter.updateImported(galleries_ids,delete_entries);
                        }
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
                $('#modula_importer_envira :input').prop('disabled', false);
            }

        },
        // Update imported galleries
        updateImported: function(galleries_ids,delete_entries){

            var  data = {
                action: 'modula_importer_envira_gallery_imported_update',
                galleries: galleries_ids,
                clean: delete_entries,
                nonce: modula_importer.nonce,
            };

            $.post(ajaxurl,data,function(response){
                window.location.href = response;
            });
        }

    };


    /**
     * Modula Importer for NextGEN
     *
     * @type {{init: init, runAjaxs: runAjaxs, ajaxTimeout: null, counts: number, processAjax: processAjax, ajaxRequests: [], completed: number, updateImported: updateImported, ajaxStarted: number}}
     */
    var modulaNextgenImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('#modula_importer_nextgen input[type="submit"]').click(function (e) {
                e.preventDefault();
                modulaNextgenImporter.completed = 0;

                // Check if gallery was selected
                var galleries = $('#modula_importer_nextgen input[name=gallery]:checked');

                if (0 == galleries.length) {
                    alert(modula_importer.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('#modula_importer_nextgen :input').prop('disabled', true);

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

                var status = $('#modula_importer_nextgen label[data-id=' + gallery_id + ']');
                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_importer.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_nextgen_gallery_import',
                        id: gallery_id,
                        nonce: modula_importer.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {

                        modulaNextgenImporter.completed = modulaNextgenImporter.completed + 1;
                        if ( ! response.success ) {
                            status.find('span').text(response.message);
                            if(modulaNextgenImporter.counts == modulaNextgenImporter.completed){

                                modulaNextgenImporter.updateImported(galleries_ids,delete_entries);
                            }
                            return;
                        }

                        /*modulaNextgenImporter.completed = modulaNextgenImporter.completed + 1;*/

                        // Display result from AJAX call
                        status.find('span').html(response.message);

                        // Remove one ajax from queue
                        modulaNextgenImporter.ajaxStarted = modulaNextgenImporter.ajaxStarted - 1;

                        if(modulaNextgenImporter.counts == modulaNextgenImporter.completed ){
                            modulaNextgenImporter.updateImported(galleries_ids,delete_entries);
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
                $('#modula_importer_nextgen :input').prop('disabled', false);
            }

        },
        // Update imported galleries
        updateImported: function(galleries_ids,delete_entries){

            var data = {
                action: 'modula_importer_nextgen_gallery_imported_update',
                galleries: galleries_ids,
                clean:delete_entries,
                nonce: modula_importer.nonce,
            };

            $.post(ajaxurl,data,function(response){
                window.location.href = response;
            });
        },
    };


    /**
     * Modula Importer for Final Tiles
     *
     * @type {{init: init, runAjaxs: runAjaxs, ajaxTimeout: null, counts: number, processAjax: processAjax, ajaxRequests: [], completed: number, updateImported: updateImported, ajaxStarted: number}}
     */
    var modulaFinalTilesImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('#modula_importer_final_tiles input[type="submit"]').click(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('#modula_importer_final_tiles input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_importer.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('#modula_importer_final_tiles :input').prop('disabled', true);

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

                var status = $('#modula_importer_final_tiles label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_importer.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_final_tiles',
                        id: gallery_id,
                        nonce: modula_importer.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {

                        modulaFinalTilesImporter.completed = modulaFinalTilesImporter.completed + 1;
                        if ( ! response.success ) {
                            status.find('span').text(response.message);
                            if(modulaFinalTilesImporter.counts == modulaFinalTilesImporter.completed){

                                modulaFinalTilesImporter.updateImported(galleries_ids,delete_entries);
                            }
                            return;
                        }

                        /* modulaFinalTilesImporter.completed = modulaFinalTilesImporter.completed + 1;*/

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
                $('#modula_importer_final_tiles :input').prop('disabled', false);
            }

        },
        // Update imported galleries
        updateImported: function(galleries_ids,delete_entries){

            var data = {
                action: 'modula_importer_final_tiles_update_imported',
                galleries: galleries_ids,
                clean: delete_entries,
                nonce: modula_importer.nonce,
            };

            $.post(ajaxurl,data,function(response){
                window.location.href = response;
            });
        }

    };


    /**
     * Modula Importer For Photoblocks
     *
     * @type {{init: init, runAjaxs: runAjaxs, ajaxTimeout: null, counts: number, processAjax: processAjax, ajaxRequests: [], completed: number, updateImported: updateImported, ajaxStarted: number}}
     */
    var modulaPhotoblocksImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('#modula_importer_photoblocks input[type="submit"]').click(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('#modula_importer_photoblocks input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_importer.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('#modula_importer_photoblocks :input').prop('disabled', true);

                // Get array of IDs
                var id_array = [];
                $(galleries).each(function (i) {
                    id_array[i] = $(this).val();
                });

                modulaPhotoblocksImporter.counts = id_array.length;
                modulaPhotoblocksImporter.processAjax( id_array );

            });

        },

        processAjax: function( galleries_ids ){

            var delete_entries = 'keep';

            if($('#delete-old-entries').prop('checked')){
                delete_entries = 'delete';
            }

            galleries_ids.forEach( function( gallery_id ){

                var status = $('#modula_importer_photoblocks label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_importer.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_photoblocks',
                        id: gallery_id,
                        nonce: modula_importer.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {
                        modulaPhotoblocksImporter.completed = modulaPhotoblocksImporter.completed + 1;
                        if ( ! response.success ) {
                            status.find('span').text(response.message);
                            if(modulaPhotoblocksImporter.counts == modulaPhotoblocksImporter.completed){

                                modulaPhotoblocksImporter.updateImported(galleries_ids,delete_entries);
                            }
                            return;
                        }


                        // Display result from AJAX call
                        status.find('span').html(response.message);

                        // Remove one ajax from queue
                        modulaPhotoblocksImporter.ajaxStarted = modulaPhotoblocksImporter.ajaxStarted - 1;
                        if(modulaPhotoblocksImporter.counts == modulaPhotoblocksImporter.completed){
                            modulaPhotoblocksImporter.updateImported(galleries_ids,delete_entries);
                        }
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
                $('#modula_importer_photoblocks :input').prop('disabled', false);
            }

        },
        // Update imported galleries
        updateImported: function(galleries_ids,delete_entries){

            var data = {
                action: 'modula_importer_photoblocks_update_imported',
                galleries: galleries_ids,
                clean: delete_entries,
                nonce: modula_importer.nonce,
            };

            $.post(ajaxurl,data,function(response){
                window.location.href = response;
            });
        }

    };


    /**
     * Modula Importer for WP Core Galleries
     *
     * @type {{init: init, runAjaxs: runAjaxs, ajaxTimeout: null, counts: number, processAjax: processAjax, ajaxRequests: [], completed: number, ajaxStarted: number}}
     */
    var modulaWPCoreImporter = {
        counts: 0,
        completed: 0,
        ajaxRequests: [],
        ajaxStarted: 0,
        ajaxTimeout: null,


        init: function(){

            $('#modula_importer_wp_core input[type="submit"]').click(function (e) {
                e.preventDefault();

                // Check if gallery was selected
                var galleries = $('#modula_importer_wp_core input[name=gallery]:checked');
                if (0 == galleries.length) {
                    alert(modula_importer.empty_gallery_selection);
                    return false;
                }

                // Disable input
                $('#modula_importer_wp_core :input').prop('disabled', true);

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

            var delete_entries = false;

            if($('#delete-old-entries').prop('checked')){
                delete_entries = true;
            }

            galleries_ids.forEach( function( gallery_id ){

                var status = $('#modula_importer_wp_core label[data-id=' + gallery_id + ']');

                $(status).removeClass().addClass('importing');
                $('span', $(status)).html(modula_importer.importing);

                var opts = {
                    url:      ajaxurl,
                    type:     'post',
                    async:    true,
                    cache:    false,
                    dataType: 'json',
                    data: {
                        action: 'modula_importer_wp_core_gallery_import',
                        id: gallery_id,
                        nonce: modula_importer.nonce,
                        clean:delete_entries
                    },
                    success: function( response ) {
                        if ( ! response.success ) {
                            return;
                        }

                        modulaWPCoreImporter.completed = modulaWPCoreImporter.completed + 1;

                        // Display result from AJAX call
                        status.find('span').html(response.message);

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
                $('#modula_importer_wp_core :input').prop('disabled', false);
            }

        },

    };


    $(document).ready(function () {

        // Get galleries from sources
        $('#modula_select_gallery_source').on('change', function () {
            var targetID = $(this).val();

            // Hide the response if user goes through sources again
            if($('body').find('.update-complete').length){
                $('body').find('.update-complete').hide();
            }

            var data = {
                action : 'modula_importer_get_galleries',
                nonce : modula_importer.nonce,
                source : targetID
            };

            $.post(ajaxurl,data,function(response){

                if ( ! response ) {
                    return;
                }

                $('#modula-' + targetID + '-importer').removeClass('hide');
                $('#modula-' + targetID + '-importer').find('.modula-found-galleries').html(response);
                $('.modula-importer-row').not($('#modula-' + targetID + '-importer')).addClass('hide');
                if ('none' != targetID && $('#modula-' + targetID + '-importer').find('input[type="checkbox"]').not('#select-all-'+targetID).length > 0) {
                    $('.select-all-wrapper').removeClass('hide');
                } else {
                    $('#modula-' + targetID + '-importer .select-all-checkbox').addClass('hide');
                    $('#modula-' + targetID + '-importer').find('input[type="submit"]').addClass('hide');
                    $('.select-all-wrapper').addClass('hide');
                }
            });

        });

        // Select all galleries from respective source
        $('body').on('change','.select-all-checkbox', function () {

            var checkboxes = $(this).parents('td').find('input[type="checkbox"]').not($(this));

            if ($(this).prop('checked')) {
                checkboxes.each(function () {
                    if ($(this).is(':visible')) {
                        checkboxes.prop('checked', true);
                    }
                });
            } else {
                checkboxes.each(function () {
                    if ($(this).is(':visible')) {
                        checkboxes.prop('checked', false);
                    }
                });
            }
        });

        // Init importers
        modulaWPCoreImporter.init();
        modulaPhotoblocksImporter.init();
        modulaFinalTilesImporter.init();
        modulaNextgenImporter.init();
        modulaEnviraImporter.init();
    });

})( jQuery );

