<?php
/**
 * The template for displaying individual events.
 *
 * @package     Reach
 */

get_header() ?>
    
    <?php get_template_part( 'partials/banner' ) ?>

    <div id="primary" class="content-area">
        <main class="site-main content">        
        <?php 

        tribe_events_the_notices();

        while ( have_posts() ) : 

            the_post();

            get_template_part( 'partials/content', 'event' );

            if ( get_post_type() == TribeEvents::POSTTYPE && tribe_get_option( 'showComments', false ) ) :

                comments_template();

            endif;
            
        endwhile; // end of the loop. 

        ?>
        </main><!-- #main -->
    </div><!-- #primary -->

<?php //get_footer(); ?>
