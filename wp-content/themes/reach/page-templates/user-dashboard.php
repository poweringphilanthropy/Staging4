<?php
/**
 * Template name: User Dashboard
 *
 * @package 	Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area">     
		<?php

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content', 'user-dashboard' );

			endwhile;
		endif;

		?>
		</div><!-- #primary -->
	</div><!-- .layout-wrapper -->
</main><!-- #main -->
<?php

get_footer();
