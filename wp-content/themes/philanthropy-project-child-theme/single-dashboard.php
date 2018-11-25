<?php
/**
 * Default template to show dashboard content
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header('dashboard');
?>
<main id="main" class="site-main site-content cf" role="main">  
    <div class="layout-wrapper">
        <div id="primary" class="content-area no-sidebar">      
        <?php 

        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();

                get_template_part( 'partials/content', 'page' );

            endwhile;
        endif;
        
        ?>
        </div><!-- #primary -->      
    </div><!-- .layout-wrapper -->
</main><!-- #main -->           
<?php
get_footer('dashboard');