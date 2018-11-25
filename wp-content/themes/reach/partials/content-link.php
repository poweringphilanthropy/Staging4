<?php
/**
 * Content of link format post.
 *
 * @package 	Reach
 */

?>
<article id="post-<?php the_ID() ?>" <?php post_class() ?>>	
	<?php

	get_template_part( 'partials/sticky' );

	get_template_part( 'partials/featured-image' );

	?>
	<div class="block entry-block">
		<?php

		reach_post_header();

		$content = reach_link_format_the_content( null, false, false );

		if ( strlen( $content ) ) :

		?>
			<div class="entry cf">
				<?php echo $content ?>
			</div><!-- .entry -->
		<?php

		endif;

		get_template_part( 'partials/meta', 'byline' );

		/* Display taxonomy meta on single posts. */
		if ( is_single() ) :
			get_template_part( 'partials/meta', 'taxonomy' );
		endif;

		?>
	</div><!-- .entry-block -->
</article><!-- post-<?php the_ID() ?> -->
