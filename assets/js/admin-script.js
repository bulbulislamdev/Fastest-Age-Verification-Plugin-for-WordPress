jQuery(document).ready(function ($) {
    $('#age_verification_logo_upload_button').click(function (e) {
        e.preventDefault();

        // Open WordPress Media Uploader
        var mediaUploader = wp.media({
            title: 'Upload Logo',
            button: {
                text: 'Use this logo'
            },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#age_verification_logo').val(attachment.url); // Set the selected image URL
        });

        mediaUploader.open();
    });
});
