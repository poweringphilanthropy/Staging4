<?php
/**
 * Template name: Stripped
 *
 * A custom template for pages where you do want to minimise any
 * distractions. Great for use with action-oriented pages, like
 * the checkout, login, campaign-creation, etc.
 *
 * @package Reach
 */

get_header( 'stripped' );

?>
<main id="main" class="site-main site-content cf">  
	<div class="layout-wrapper">    
		<div id="primary" class="content-area no-sidebar">
		<?php

		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				get_template_part( 'partials/content', 'stripped' );

				/* If comments are open or we have at least one comment, load up the comment template */
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			endwhile;
		endif;

		?>
		</div><!-- #primary -->
	</div><!-- .layout-wrapper -->
</main><!-- #main -->
<?php

get_footer( 'stripped' );
