var mail_body_editor;

jQuery(document).ready( function($) {

    $('a.import_items_container_toggle').on('click', function(event) {
        event.stopPropagation();
        $('#import_items_container').fadeToggle( "slow", "linear" );
        return false;
    });

    if ($('#psn_form_mailtemplate').length > 0) {

        var help_tab_active = '';
        // init the placeholder help link
        $('a.placeholder_help').on('click', function(event) {
            $('#tab-link-placeholders a').click();
            if ($('#contextual-help-wrap').is(":visible") == false || help_tab_active == 'placeholders') {
                $('#contextual-help-link').click();
            }
            help_tab_active = 'placeholders';
            $('html, body').animate({scrollTop:0});
            return false;
        });
        $('a.conditions_help').on('click', function(event) {
            $('#tab-link-conditions a').click();
            if ($('#contextual-help-wrap').is(":visible") == false || help_tab_active == 'conditions') {
                $('#contextual-help-link').click();
            }
            help_tab_active = 'conditions';
            $('html, body').animate({scrollTop:0});
            return false;
        });



        /**
         * loads the HTML editor
         */
        function load_body_editor () {

            if (!CKEDITOR.env.isCompatible) {
                alert('CKEDITOR found an incompatible browser. HTML editor could not be loaded. Check the manual or ask the support.');
            }

            var cklang = 'en';
            if (typeof ckconfig != 'undefined') {
                cklang = ckconfig.lang;
            }

            mail_body_editor = $( '#body' ).ckeditor({
                height: 600,
                language: cklang,
                allowedContent: true,
                entities_latin: false,
                entities_greek: false,
                fullPage: true,
                extraPlugins: 'codemirror',
                startupMode : 'source',
                entities: false,
                basicEntities: false,
                codemirror: {
                }
            }).editor;
        }

        // load html editor if HTML is selected on page load
        if ($('input[name=type]:checked').attr('id') == 'type-1') {
            load_body_editor();
        }

        // listen on mail type radio buttons
        $("input[name='type']").on('change', function(event) {

            var type_id = $(this).attr('id');

            if (type_id == 'type-1') {
                load_body_editor();
            } else {
                mail_body_editor.destroy()
            }
        });

    }
});