<?php
/**
 * The template part for displaying results in search pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Reach
 */
?>
<article id="post-<?php the_ID() ?>" <?php post_class() ?>>
	<div class="block entry-block">
		<?php

		get_template_part( 'partials/sticky' );

		get_template_part( 'partials/featured-image' );

		reach_post_header();

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

		?>
	</div><!-- .entry-block -->
</article><!-- post-<?php the_ID() ?> -->
