<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area <?php if ( ! is_active_sidebar( 'default' ) ) : ?>no-sidebar<?php endif ?>">
		<?php

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content', 'page' );

				/* If comments are open or we have at least one comment, load up the comment template */
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile;
		endif;

		?>
		</div><!-- #primary -->
				
		<?php get_sidebar() ?>
	</div><!-- .layout-wrapper -->
</main><!-- #main -->           

<?php get_footer();
