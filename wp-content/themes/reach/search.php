<?php
/**
 * The template for displaying search results pages.
 *
 * @package Reach
 */

get_header(); ?>

<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area <?php if ( ! is_active_sidebar( 'default' ) ) : ?>no-sidebar<?php endif ?>">
		<?php

		get_template_part( 'partials/banner' );

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				/**
				 * Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called content-search.php and that will be used instead.
				 */
				get_template_part( 'partials/content', 'search' );

			endwhile;

			reach_paging_nav();

		else :

			get_template_part( 'partials/content', 'none' );

		endif;

		?>
		</div><!-- #primary -->

		<?php get_sidebar() ?>
	</div><!-- .layout-wrapper -->
</main><!-- #main -->
<?php

get_footer();
