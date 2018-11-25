
jQuery(document).ready( function($) {
    $('#example_pending_post').click(function(link) {
        $('#name').val(PsnExampleRule.ThePendingPost);

        $('#posttype').val('post');
        $('#status_before').val('not_pending');
        $('#status_after').val('pending');

        $('#notification_subject').val(PsnExampleRule.ThePendingPostSubject);
        $('#notification_body').val(PsnExampleRule.ThePendingPostBody);

        $('#recipient').val('admin');
        $('#cc').val('reviewer@yourdomain.com');
        $('#posttype').trigger('change');
        return false;
    });
    $('#example_happy_author').click(function(link) {
        $('#name').val(PsnExampleRule.TheHappyAuthor);

        $('#posttype').val('post');
        $('#status_before').val('not_published');
        $('#status_after').val('publish');

        $('#notification_subject').val(PsnExampleRule.TheHappyAuthorSubject);
        $('#notification_body').val(PsnExampleRule.TheHappyAuthorBody);

        $('#recipient').val('author');
        $('#cc').val('');
        $('#posttype').trigger('change');
        return false;
    });

    $('#example_pedantic_admin').click(function(link) {
        $('#name').val(PsnExampleRule.ThePedanticAdmin);

        $('#posttype').val('post');
        $('#status_before').val('anything');
        $('#status_after').val('anything');

        $('#notification_subject').val(PsnExampleRule.ThePedanticAdminSubject);
        $('#notification_body').val(PsnExampleRule.ThePedanticAdminBody);

        $('#recipient').val('admin');
        $('#cc').val('');
        $('#posttype').trigger('change');
        return false;
    });

    $('#example_debug').click(function(link) {
        $('#name').val('_Debug');

        $('#posttype').val('all');
        $('#status_before').val('anything');
        $('#status_after').val('anything');

        $('#notification_subject').val('Debug');
        $('#notification_body').val('Debug');

        $('#recipient').val('admin');
        $('#cc').val('');
        $('#active').attr('checked', true);
        $('#service_email').removeAttr('checked');
        $('#service_log').attr('checked', true);
        $('#posttype').trigger('change');
        return false;
    });

});
