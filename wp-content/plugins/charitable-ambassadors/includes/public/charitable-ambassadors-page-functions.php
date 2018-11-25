<?php 
/**
 * Charitable Ambassadors Template Functions. 
 *
 * @author 		Studio164a
 * @category 	Functions
 * @package 	Charitable Ambassadors/Functions/Template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Returns the URL for the user login page. 
 *
 * This is used when you call charitable_get_permalink( 'campaign_submission_page' ). In
 * general, you should use charitable_get_permalink() instead since it will
 * take into account permalinks that have been filtered by plugins/themes.
 *
 * @see     charitable_get_permalink
 * @global  WP_Rewrite  $wp_rewrite
 * @param   string      $url
 * @param   array       $args
 * @return  string
 * @since   1.0.0
 */
function charitable_get_campaign_submission_page_permalink( $url, $args = array() ) {     
    $page = charitable_get_option( 'campaign_submission_page', false );

    if ( $page ) {
        $url = get_permalink( $page );
    }
        
    return $url;
}   

add_filter( 'charitable_permalink_campaign_submission_page', 'charitable_get_campaign_submission_page_permalink', 2, 2 );      


/**
 * Returns the URL for the campaign editing page. 
 *
 * This is used when you call charitable_get_permalink( 'campaign_editing_page' ). In
 * general, you should use charitable_get_permalink() instead since it will
 * take into account permalinks that have been filtered by plugins/themes.
 *
 * @global  WP_Rewrite  $wp_rewrite
 * @param   string      $url
 * @param   array       $args
 * @return  string
 * @since   1.0.0
 */
function charitable_get_campaign_editing_page_permalink( $url, $args = array() ) {
    global $wp_rewrite;
    
    $campaign_id = isset( $args[ 'campaign_id' ] ) ? $args[ 'campaign_id' ] : get_the_ID();
    $base_url = charitable_get_permalink( 'campaign_submission_page' );

    if ( $base_url ) {

        if ( $wp_rewrite->using_permalinks() ) {
            $url = trailingslashit( $base_url ) . $campaign_id . '/edit/';
        }
        else {
            $url = esc_url_raw( add_query_arg( array( 'campaign_id' => $campaign_id ), $base_url ) );   
        }

    }    

    return $url;
}   

add_filter( 'charitable_permalink_campaign_editing_page', 'charitable_get_campaign_editing_page_permalink', 2, 2 );       

/**
 * Returns the URL for the user login page. 
 *
 * This is used when you call charitable_get_permalink( 'campaign_submission_success_page' ). In
 * general, you should use charitable_get_permalink() instead since it will
 * take into account permalinks that have been filtered by plugins/themes.
 *
 * @see     charitable_get_permalink
 * @global  WP_Rewrite  $wp_rewrite
 * @param   string      $url
 * @param   array       $args
 * @return  string
 * @since   1.0.0
 */
function charitable_get_campaign_submission_success_page_permalink( $url, $args = array() ) {     
    $page = charitable_get_option( 'campaign_submission_success_page', 'home' );
    $url = 'home' == $page ? home_url() : get_permalink( $page );
    $url = esc_url_raw( add_query_arg( array( 'campaign_id' => $args[ 'campaign_id' ] ), $url ) );
    return $url;
}   

add_filter( 'charitable_permalink_campaign_submission_success_page', 'charitable_get_campaign_submission_success_page_permalink', 2, 2 );      

/**
 * Checks whether the current request is for the campaign submission page.
 *
 * This is used when you call charitable_is_page( 'campaign_submission_page' ). 
 * In general, you should use charitable_is_page() instead since it will
 * take into account any filtering by plugins/themes.
 *
 * @global  WP_Post     $post
 * @param   string      $page
 * @return  boolean
 * @since   1.0.0
 */
function charitable_is_campaign_submission_page( $ret = false ) {
    global $post;   
    
    return is_a( $post, 'WP_Post') && $post->ID == charitable_get_option( 'campaign_submission_page', false );
}

add_filter( 'charitable_is_page_campaign_submission_page', 'charitable_is_campaign_submission_page', 2 );

/**
 * Checks whether the current request is for the campaign editing page. 
 *
 * This is used when you call charitable_is_page( 'campaign_editing_page' ). 
 * In general, you should use charitable_is_page() instead since it will
 * take into account any filtering by plugins/themes.
 *
 * @see     charitable_is_page
 * @return  boolean
 * @since   1.0.0
 */
function charitable_is_campaign_editing_page( $ret = false ) {
    global $post;

    return get_query_var( 'campaign_id', false ) && charitable_is_page( 'campaign_submission_page' );
}

add_filter( 'charitable_is_page_campaign_editing_page', 'charitable_is_campaign_editing_page' );