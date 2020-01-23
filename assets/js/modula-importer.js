jQuery(document).ready(function ($) {
    $('#modula_select_gallery_source').on('change', function () {
        var targetID = $(this).val();

        // Hide the response if user goes through sources again
        if($('body').find('.update-complete').length){
            $('body').find('.update-complete').hide();
        }

        $('#modula-' + targetID + '-importer').removeClass('hide');
        $('.modula-importer-row').not($('#modula-' + targetID + '-importer')).addClass('hide');

        if ('none' != targetID) {
            $('.select-all-wrapper').removeClass('hide');
        } else {
            $('.select-all-wrapper').addClass('hide');
        }

    });

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
});