( function( $ ) {

    var file_frame;
    window.formfield = '';

    $( document.body ).on( 'click', '.charitable-upload-button', function(e) {

        e.preventDefault();

        var $button = $(this);

        window.formfield = $button;

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media( {
            frame: 'post',
            state: 'insert',
            title: $button.data( 'uploader-title' ),
            button: {
                text: $button.data( 'uploader-button-text' )
            },
            library: {
                type: 'video'
            },
            multiple: false
        } );

        file_frame.on( 'menu:render:default', function( view ) {
            // Store our views in an object.
            var views = {};

            // Unset default menu items
            view.unset( 'library-separator' );
            view.unset( 'gallery' );
            view.unset( 'featured-image' );
            view.unset( 'embed' );

            // Initialize the views in our view object.
            view.set( views );
        } );

        // When an image is selected, run a callback.
        file_frame.on( 'insert', function() {

            var selection = file_frame.state().get('selection');
            selection.each( function( attachment, index ) {
                attachment = attachment.toJSON();

                window.formfield.parent().children( '.charitable-upload-field-id' ).val( attachment.id );
                window.formfield.parent().children( '.charitable-upload-field' ).val( attachment.url );
            } );
        } );

        // Finally, open the modal
        file_frame.open();
    });


    // WP 3.5+ uploader
    var file_frame;
    window.formfield = '';
})( jQuery);
