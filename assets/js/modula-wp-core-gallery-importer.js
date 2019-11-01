jQuery(document).ready(function ($) {

    $('form#modula_importer_wp_core_gallery').submit(function (e) {
        e.preventDefault();

        // Check if gallery was selected
        var galleries = $('input[name=gallery]:checked');
        if (0 == galleries.length) {
            alert(modula_wp_core_gallery_importer_settings.empty_gallery_selection);
            return false;
        }

        // Disable input
        $('form#modula_importer_wp_core_gallery :input').prop('disabled', true);

        // Get array of IDs
        var curIndex = -1;
        var id_array = [];
        $(galleries).each(function (i) {
            id_array[i] = $(this).val();
        });

        import_gallery_envira(id_array, curIndex);
    });


    /**
     * Imports Envira gallery
     *
     * @param id_array
     * @param curIndex
     */
    var import_gallery_envira = function (id_array, curIndex) {

        curIndex++;

        // Check if end of array
        if (id_array.length == curIndex) {

            $('form#modula_importer_wp_core_gallery :input').prop('disabled', false);
            return;
        }

        ajax_request_envira(id_array, curIndex);
    }

    /**
     * Performs an AJAX request to import envira gallery
     *
     * @param id_array
     * @param curIndex
     */
    var ajax_request_envira = function (id_array, curIndex) {

        // Get ID and status label on form
        var id = id_array[curIndex];
        var status = $('form#modula_importer_wp_core_gallery label[data-id=' + id + ']');

        $(status).removeClass().addClass('importing');
        $('span', $(status)).html(modula_wp_core_gallery_importer_settings.importing);

        // Do request
        $.ajax({
            url: modula_wp_core_gallery_importer_settings.ajax,
            type: 'post',
            async: true,
            cache: false,
            dataType: 'json',
            data: {
                action: 'modula_importer_wp_core_gallery_import',
                id: id,
                nonce: modula_wp_core_gallery_importer_settings.nonce
            },
            success: function (response) {
                status_update_envira(id_array, curIndex, response.success, response.message);
                import_gallery_envira(id_array, curIndex);
                return;
            },
            error: function (xhr, textStatus, e) {
                status_update_envira(id_array, curIndex, false, textStatus);
                import_gallery_envira(id_array, curIndex);
                return;
            }
        });
    }

    /**
     * Update the status of the import when completed
     *
     * @param id_array
     * @param curIndex
     * @param result
     * @param message
     */
    var status_update_envira = function (id_array, curIndex, result, message) {

        var id = id_array[curIndex];
        var status = $('form#modula_importer_wp_core_gallery label[data-id=' + id + ']');

        $(status).removeClass().addClass((result ? 'gallery has been imported' : 'it appears there has been an error'));

        // Display result from AJAX call
        $('span', $(status)).text(message);
    }

});