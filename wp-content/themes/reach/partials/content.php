<?php
/**
 * @package Reach
 */
?>
<article id="post-<?php the_ID() ?>" <?php post_class() ?>>
	<?php

	/* Add the header banner if this is a single post. */
	if ( is_single() ) :
		get_template_part( 'partials/banner' );
	endif;

	?>
	<div class="block entry-block">
		<?php

		get_template_part( 'partials/sticky' );

		get_template_part( 'partials/featured-image' );

		/* If this is an archive, display the post title. */
		if ( ! is_single() ) :
			reach_post_header();
		endif;

		?>
		<div class="entry cf">				
			<?php

			the_content();

			wp_link_pages( array(
				'before' => '<p class="entry_pages">' . __( 'Pages: ', 'reach' ),
			) );

			?>
		</div><!-- .entry -->
		<?php

		get_template_part( 'partials/meta', 'byline' );

		/* Display taxonomy meta on single posts. */
		if ( is_single() ) :
			get_template_part( 'partials/meta', 'taxonomy' );
		endif;

		?>
	</div><!-- .entry-block -->
</article><!-- post-<?php the_ID() ?> -->
