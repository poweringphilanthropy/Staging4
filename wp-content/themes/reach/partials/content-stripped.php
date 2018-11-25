<?php
/**
 * The template used for displaying page content with the Stripped page template.
 *
 * @package Reach
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php

	get_template_part( 'partials/banner' );

	?>
	<div class="entry cf">
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'reach' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry -->
</article><!-- post-<?php the_ID() ?> -->
