<?php
/**
 * The main archive template.
 *
 * @package Reach
 */

get_header();

?>
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">
		<div id="primary" class="content-area <?php if ( ! is_active_sidebar( 'default' ) ) : ?>no-sidebar<?php endif ?>">
		<?php

		get_template_part( 'partials/banner' );

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content', get_post_format() );

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
