/**
 * FFC Admin Submission Edit
 *
 * JavaScript for submission edit page
 *
 * @since 3.1.0
 */

jQuery(document).ready(function($) {
    // Copy magic link to clipboard
    $('.ffc-copy-magic-link').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var $btn = $(this);

        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(url).select();
        document.execCommand('copy');
        $temp.remove();

        var originalText = $btn.text();
        $btn.text(ffc_submission_edit.copied_text).prop('disabled', true);

        setTimeout(function() {
            $btn.text(originalText).prop('disabled', false);
        }, 2000);
    });
});
