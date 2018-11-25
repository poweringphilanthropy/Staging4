CHARITABLE = window.CHARITABLE || {};

( function( $, exports ){

    /**
     * Main Uploader object.
     */
    var Uploader = function( $dragdrop ) {
        var self = this,
            params = $dragdrop.data('params'), 
            uploader = new plupload.Uploader( params );

        this.$dragdrop = $dragdrop;
        this.$dropzone = $(uploader.settings.drop_element);
        this.$images = $('#' + $dragdrop.data('images'));
        this.$loader = this.$dragdrop.find('.charitable-drag-drop-image-loader').first();
        this.max_file_uploads = params.multipart_params.max_uploads;
        this.max_file_size = parseInt( this.$dragdrop.data( 'max-size' ), 10 );
        this.uploaded = this.$images.children().length;
        this.max_uploads_alert_shown = false;
        this.max_upload_size_alert_shown = false;

        uploader.init();

        uploader.bind( 'Init', function( uploader ) {
            self.Init( uploader );
        });

        uploader.bind( 'PostInit', function( uploader ) {
            uploader.refresh();
        });

        uploader.bind( 'FileFiltered', function( uploader, files ) {
            self.FileFiltered( uploader, files );
        });

        uploader.bind( 'FilesAdded', function( uploader, files ) {
            self.FilesAdded( uploader, files );            
        });

        uploader.bind( 'Error', function( uploader, e ){
            console.log( e );
        });

        uploader.bind( 'FileUploaded', function( uploader, file, r ){
            self.FileUploaded( uploader, file, r );            
        });
    };

    /**
     * PostInit event
     */
    Uploader.prototype.Init = function( uploader ) {
        var self = this;

        if ( ! this.$dropzone ) {
            return;
        }

        if ( uploader.features.dragdrop && ! this.$dragdrop.parent().hasClass('mobile') ) { 
            this.$dragdrop.parent().addClass( 'supports-drag-drop' );

            // We may need to enhance this to account for the issue noted
            // in https://core.trac.wordpress.org/ticket/21705
            this.$dropzone.bind( 'dragover', function(){
                self.$dropzone.addClass('drag-over');
            });

            this.$dropzone.bind( 'dragleave', function(){
                self.$dropzone.removeClass('drag-over');
            });
        }        

        // Set up image remove handler
        this.$dragdrop.on( 'click', '.remove-image', function( e ) {
            e.preventDefault();
            return self.remove_image( $(this), uploader );
        });
    };
    
    /**
     * FileFiltered event
     */
    Uploader.prototype.FileFiltered = function( uploader, file ) {
        var full = this.max_file_uploads && this.uploaded >= this.max_file_uploads;

        // Don't queue file if the max number of files have been uploaded.
        if ( full ) {
            if ( ! this.max_uploads_alert_shown ) {
                alert( this.get_max_uploads_message() );
            }
            
            this.max_uploads_alert_shown = true;
            return uploader.removeFile( file );
        }

        // If the file is too big, remove it and show a message.
        if ( file.size >= this.max_file_size ) {
            if ( ! this.max_upload_size_alert_shown ) {
                alert( file, CHARITABLE_UPLOAD_VARS.max_file_size.replace('%1$s', file.name).replace('%2$s', this.bytes_to_mb( this.max_file_size ) ) );
            }

            this.max_upload_size_alert_shown = true;
            return uploader.removeFile( file );
        }

        this.uploaded += 1;

        // Hide drag & drop section if we have reached the max number of file uploads.
        if ( this.uploaded === this.max_file_uploads ) {
            this.hide_dropzone();
        }

        this.add_image_loader( file );
    }
    
    /**
     * FilesAdded event
     */
    Uploader.prototype.FilesAdded = function( uploader, files ) {
        this.$dropzone.removeClass('drag-over');

        // If the user tries to upload too many files again, or too large a file, show the alert again.
        this.max_uploads_alert_shown = false;
        this.max_upload_size_alert_shown = false;

        uploader.refresh();
        uploader.start();
    };

    /**
     * FileUploaded event
     */
    Uploader.prototype.FileUploaded = function( uploader, file, r ) {
        var input, data;

        r = $.parseJSON( r.response );

        if ( ! r.success ) {

            this.add_image_error( file, CHARITABLE_UPLOAD_VARS.upload_problem.replace('%s', file.name) );
            return;
            
        }

        // Remove the image from the loader & possibly hide the loader.
        this.hide_image_loader( file );

        // Display the image
        this.$images.append( r.data );
    };

    /**
     * Return the message to be displayed when the max number of file uploads has been reach or exceeded.
     *
     * @param   int max_file_uploads
     * @return  string
     */
    Uploader.prototype.get_max_uploads_message = function() {
        var msg = this.max_file_uploads > 1 ? CHARITABLE_UPLOAD_VARS.max_file_uploads_plural : CHARITABLE_UPLOAD_VARS.max_file_uploads_single;

        return msg.replace( '%d', this.max_file_uploads );
    };

    /**
     * Add an image loader bar to indicate that an image is being uploaded.
     *
     * @param   $loader
     * @param   array file
     * @return  void
     */
    Uploader.prototype.add_image_loader = function( file ) {
        this.$loader.fadeIn( 300 )
        this.$loader.children('.images').append( '<li data-file-id="' + file.id + '" class="">' + file.name + '</li>' );        
    };

    /**
     * Hide the image loader.
     *
     * @param   $loader
     * @param   array file
     * @return  void
     */
    Uploader.prototype.hide_image_loader = function( file ) {
        this.$loader.find( '[data-file-id=' + file.id + ']' ).remove();

        if ( ! this.$loader.find('.images li').length ) {
            this.$loader.hide();
        }        
    };

    /**
     * Remove an image.
     *  
     * @return  void
     */
    Uploader.prototype.remove_image = function($anchor, uploader) {
        var $image = $anchor.parent();

        $image.fadeOut( 300, function(){
            this.remove();
        });

        this.$dropzone.fadeIn( 300 );

        this.uploaded -= 1;

        // Required to ensure browse button works on iOS Safari.
        uploader.refresh();

        return false;
    }

    /**
     * Hide the dropzone.
     *
     * @return  void
     */
    Uploader.prototype.hide_dropzone = function() {
        // this.$dropzone.removeClass('drag-over').fadeOut( 300 );
        this.$dropzone.removeClass('drag-over');
        this.$dropzone.hide();
    }

    /**
     * Return a readable filesize.
     */
    Uploader.prototype.bytes_to_mb = function( size ) {
        var i = Math.floor( Math.log(size) / Math.log(1024) );
        return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
    }

    /**
     * Dequeues the file and displays an error.
     *
     * @param   object $loader
     * @param   object $dropzone
     * @param   object file 
     * @param   string msg
     * @return  void
     */
    Uploader.prototype.add_image_error = function( file, msg ) {
        var self = this;

        self.$dropzone.fadeIn( 300 );

        self.$loader.find( '[data-file-id=' + file.id + ']' ).addClass( 'error' ).text( msg ).delay( 5000 ).fadeOut( 300, function(){
            self.hide_image_loader( file );            
        });
    }

    exports.Uploader = Uploader;

    /**
     * Load the Uploaders
     */
    $(document).ready( function() {
        $('.charitable-drag-drop').each( function() {
            new Uploader( $(this) );
        });
    });    

})( jQuery, CHARITABLE );