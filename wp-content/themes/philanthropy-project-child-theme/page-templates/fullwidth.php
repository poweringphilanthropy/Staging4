<?php
/**
 * Template name: Fullwidth
 *
 * @package Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf" role="main">  
    <div class="layout-wrapper">
        <div id="primary" class="content-area no-sidebar">      
        <?php 

        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();

                get_template_part( 'partials/content', 'page' );

                /* If comments are open or we have at least one comment, load up the comment template */
                if ( ( comments_open() || get_comments_number() ) && !is_front_page() ) :
                    comments_template();
                endif;            

            endwhile;
        endif;
        
        ?>
        </div><!-- #primary -->      
    </div><!-- .layout-wrapper -->
</main><!-- #main -->           

<?php get_footer();