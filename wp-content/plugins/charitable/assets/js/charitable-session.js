CHARITABLE = window.CHARITABLE || {};

( function( exports ) {
    var Sessions = function() {
        this.session_id = Cookies.get( CHARITABLE_SESSION.cookie_name );

        // Set a cookie if none exists.
        if ( ! this.session_id ) {
            set_cookie();
        }

        // If a session ID is set and it matches the one we received, proceed no further.
        if ( this.session_id && ( this.session_id === CHARITABLE_SESSION.id ) ) {
            return;
        }

        exports.content_loading = true;

        if (document.readyState != 'loading') {
            init();
            exports.content_loading = false;
        } else if (document.addEventListener) {
            document.addEventListener('DOMContentLoaded', init);
            exports.content_loading = false;
        } else {
            document.attachEvent('onreadystatechange', function() {
                document.readyState != 'loading' && init();
                exports.content_loading = false;
            });
        }

        // Init.
        function init() {
            var elements = document.querySelectorAll('.charitable-session-content');
            var data = 'action=charitable_get_session_content';
            var element, template, args, key, value, i;

            if ( ! elements.length ) {
                return;
            }

            for (i = 0; i < elements.length; i++) {
                element = elements[i],
                template = element.getAttribute('data-template'),
                args = JSON.parse(element.getAttribute('data-args')),

                // Append template arguments to the data string.
                data += '&templates[' + i + '][template]=' + template;
                for (key in args) {
                    data += '&templates[' + i + '][' + key + ']=' + args[key];
                }
            }

            var request  = new XMLHttpRequest();
            request.open('POST', CHARITABLE_SESSION.ajaxurl, true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.onreadystatechange = function() {
                if (this.readyState === 4 && (this.status >= 200 && this.status < 400)) {
                    var response = JSON.parse( this.response );
                    if (!response.success) {
                        return;
                    }

                    for (i = 0; i < response.data.length; i += 1) {
                        if (!response.data[i].length) {
                            continue;
                        }

                        elements[i].innerHTML = response.data[i];
                        elements[i].style.display = 'block';
                    }
                }
            };
            request.send(data);
            request = null;
        }

        // Set cookie.
        function set_cookie() {
            Cookies.set( CHARITABLE_SESSION.cookie_name,
                CHARITABLE_SESSION.generated_id + '||' + CHARITABLE_SESSION.expiration + '||' + CHARITABLE_SESSION.expiration_variant, 
                {
                    expires: new Date( new Date().getTime() + ( parseInt( CHARITABLE_SESSION.expiration ) * 1000 ) ),
                    path: CHARITABLE_SESSION.cookie_path,
                    domain: CHARITABLE_SESSION.cookie_domain,
                    secure: CHARITABLE_SESSION.secure
                }
            );

            return CHARITABLE_SESSION.generated_id + '||' + CHARITABLE_SESSION.expiration + '||' + CHARITABLE_SESSION.expiration_variant;
        }
    }

    exports.Sessions = Sessions();

})( CHARITABLE );