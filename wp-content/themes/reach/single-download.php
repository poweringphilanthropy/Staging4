<?php
/**
 * The template for displaying Download posts.
 *
 * This template is only used if Easy Digital Downloads is active.
 *
 * @package     Reach
 */

get_header();

?>  
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area">
			<?php

			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content' );

				/* If comments are open or we have at least one comment, load up the comment template */
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // end of the loop.

			?>
		</div><!-- #primary -->

		<?php get_sidebar( 'download' ); ?>
	</div><!-- .layout-wrapper -->
</main><!-- #main -->

<?php get_footer();
