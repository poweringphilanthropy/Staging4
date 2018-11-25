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
	
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
						
			get_template_part( 'partials/banner' ); 
			?>
			<div class="layout-wrapper">
				<main class="site-main content-area" role="main">
					<?php get_template_part( 'partials/content', 'page' ) ?>
				</main><!-- .site-main -->
				<?php get_sidebar() ?>
			</div><!-- .layout-wrapper -->
		<?php 
		endwhile;
	endif;

get_footer();