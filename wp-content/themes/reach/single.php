<?php
/**
 * The template for displaying all single posts.
 *
 * @package     Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area <?php if ( ! is_active_sidebar( 'default' ) ) : ?>no-sidebar<?php endif ?>">  
			<?php

			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content', get_post_format() );

				/* If comments are open or we have at least one comment, load up the comment template */
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; /* end of the loop. */

			?>  
		</div><!-- #primary -->

		<?php get_sidebar() ?>
	</div><!-- .layout-wrapper -->
</main><!-- #main -->

<?php get_footer(); ?>
