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
                type: $button.data( 'uploader-library' )
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
            view.unset( 'playlist' );
            view.unset( 'video-playlist' );

            // Initialize the views in our view object.
            view.set( views );
        } );

        // When an image is selected, run a callback.
        file_frame.on( 'insert', function() {

            var selection = file_frame.state().get('selection');
            selection.each( function( attachment, index ) {
                attachment = attachment.toJSON();

                var id_field_name = $button.data( 'uploader-attachment-id-field-name' ), 
                    src_field_name = $button.data( 'uploader-attachment-src-field-name' ),
                    display_field_id = $button.data( 'uploader-attachment-preview-field' );

                if ( 'undefined' !== typeof id_field_name ) {
                    var attachment_id_field = $( "[name='" + id_field_name + "']" );
                    attachment_id_field.val( attachment.id );
                }

                if ( 'undefined' !== typeof src_field_name ) {
                    var attachment_src_field = $( '[name="' + src_field_name + '"]' );
                    attachment_src_field.val( attachment.url );
                }

                if ( 'undefined' !== typeof display_field_id ) {
                    var display_field = $( '#' + display_field_id );
                    display_field.html( '<img src="' + attachment.url + '" alt="" style="max-width:100%;" />' );
                }
            } );
        } );

        // Finally, open the modal
        file_frame.open();
    });


    // WP 3.5+ uploader
    var file_frame;
    window.formfield = '';
})( jQuery);
