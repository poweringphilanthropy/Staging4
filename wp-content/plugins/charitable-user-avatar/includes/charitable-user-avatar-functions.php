<?php 
/**
 * Charitable User Avatar Functions. 
 *
 * @author      Studio164a
 * @category    Core
 * @package     Charitable User Avatar/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Checks to see if the specified email address has a Gravatar image.
 *
 * @param   $email The email of the address of the user to check
 * @return  boolean
 * @since   1.0.2
 */
function charitable_user_has_gravatar( $email ) {
    $hash_key     = md5( strtolower( trim( $email ) ) );
    $has_gravatar = wp_cache_get( $hash_key, 'default', false, $uncached );

    if ( $uncached === $has_gravatar ) {
        if ( is_ssl() ) {
            $url = sprintf( 'https://secure.gravatar.com/avatar/%s?d=404', $hash_key );
        } else {
            $url = sprintf( 'http://www.gravatar.com/avatar/%s?d=404', $hash_key );
        }

        $headers      = @get_headers( $url );
        $has_gravatar = preg_match( '|200|', $headers[0] );
        
        wp_cache_set( $hash_key, $has_gravatar, '', 60 * 5 );
    }       

    return $has_gravatar;
}