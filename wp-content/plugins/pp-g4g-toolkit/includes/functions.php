<?php
/**
 * Function collections of g4g.
 * Overrides plugin dependencies template
 *
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function g4g_get_primary_term($post_id, $term = 'campaign_group'){

    $groups = get_the_terms( $post_id, $term );
    if( empty($groups) || is_wp_error( $groups ) ){
        return false;
    }


    if(class_exists('WPSEO_Primary_Term') && (count($groups) > 1) ){
        $wpseo = new WPSEO_Primary_Term($term, $post_id);
        $primary = $wpseo->get_primary_term();
    } else {
        // get first
        $campaign_group = array_pop($groups);
        $primary = $campaign_group->term_id;
    }

    return $primary;
}



function g4g_get_parent_campaign_group_names(){
    $terms = get_terms( array(
        'taxonomy' => 'campaign_group',
        'hide_empty' => false,
        'fields' => 'names',
        'parent' => 0,
    ) );

    return $terms;
}

function g4g_get_college_names(){
    $terms = get_terms( array(
        'taxonomy' => 'college',
        'hide_empty' => false,
        'fields' => 'names',
        // 'parent' => 0,
    ) );

    return $terms;
}

function g4g_get_organization_options(){
	$options = array();

    $terms = get_terms( array(
        'taxonomy' => 'campaign_group',
        'hide_empty' => false,
    ) );

    // $args = array(
    //     'post_type'  => 'campaign', //or a post type of your choosing
    //     'posts_per_page' => -1,
    //     'meta_query' => array(
    //         array(
    //             'key' => '_campaign_group',
    //             'value' => 0,
    //             'type' => 'numeric',
    //             'compare' => '>'
    //         )
    //     )
    // );

    // $dashboards = get_posts( $args );
    
    if(!empty($terms)):
    foreach ($terms as $term) {
        $options[$term->term_id] = $term->name;
    }
    endif;

    return $options;
}