<?php
/**
 * The template used for displaying page content in page-templates/user-dashboard.php
 *
 * @package Reach
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php

	get_template_part( 'partials/banner' );

	if ( function_exists( 'charitable_get_user_dashboard' ) ) :
		charitable_get_user_dashboard()->nav( array(
			'container_class' => 'user-dashboard-menu',
		) );
	endif;

	?>
	<div class="block entry-block">
		<div class="entry cf">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'reach' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry -->    
	</div><!-- .entry-block -->
</article><!-- post-<?php the_ID() ?> -->
