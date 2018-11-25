<?php
/**
 * Default template to show dashboard content
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
$get_started_page = get_term_meta( $term->term_id, '_get_started_page', true );

get_header('dashboard');
?>
<main id="main" class="site-main site-content cf" role="main">  
    <div class="layout-wrapper">
        <div id="primary" class="content-area no-sidebar">      
        <?php 

        $post_object = get_post($get_started_page);

        setup_postdata( $GLOBALS['post'] =& $post_object );

        get_template_part( 'partials/content', 'page' );

        wp_reset_postdata();
        
        ?>
        </div><!-- #primary -->      
    </div><!-- .layout-wrapper -->
</main><!-- #main -->           
<?php
get_footer('dashboard');