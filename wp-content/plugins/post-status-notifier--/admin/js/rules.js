jQuery(document).ready( function($) {

    $('a.import_items_container_toggle').click(function(event) {
        event.stopPropagation();
        $('#import_items_container').fadeToggle( "slow", "linear" );
        return false;
    });
});