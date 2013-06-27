jQuery(document).ready(function ($) {
    $('#nggallery_archives a.update_archive').click(function (e) {
        var click_link = jQuery(this);

        if (click_link.attr('disabled') == 'disabled')
            return false;

        click_link.attr('disabled', true);

        click_link.hide();
        click_link.siblings('span').show();


        var data = {
            action: 'update_nggachive',
            nggachivator_nonce: nggachivator_vars.nggachivator_nonce,
            galery_id: click_link.attr('data-gallery-id')
        };

        jQuery.post(ajaxurl, data, function (response) {
            click_link.parent().siblings('td.ngg-gallery-link').html('<a href="' + response + '">Download</a>');
            click_link.siblings('span').hide();
            click_link.show();
            click_link.attr('disabled', false);
        });
    });

    $('#gallery_archives a.dwonload_archive').click(function (e) {
        if (jQuery(this).attr('href') == '#') {
            alert('Archive not exists');

            return false;
        }
    });

    $('#nggallery_archives a.remove_archive').click(function (e) {
        var click_link = jQuery(this);

        if (click_link.attr('disabled') == 'disabled')
            return false;

        if (click_link.parent().siblings('td.ngg-gallery-link').html() == 'No archive') {
            alert('Archive for this gallery not exists!');
            return false;
        }

        click_link.attr('disabled', true);

        click_link.hide();
        click_link.siblings('span').show();

        var data = {
            action: 'remove_nggachive',
            nggachivator_nonce: nggachivator_vars.nggachivator_nonce,
            galery_id: click_link.attr('data-gallery-id')
        };

        jQuery.post(ajaxurl, data, function (response) {
            click_link.parent().siblings('td.ngg-gallery-link').html('No archive');
            click_link.siblings('span').hide();
            click_link.show();
            click_link.attr('disabled', false);
        });
    });
});