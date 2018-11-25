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

		?>
		<div class="entry">            
			<?php the_content() ?>
		</div><!-- .entry -->        
	</div><!-- .entry-block -->
</article><!-- post-<?php the_ID() ?> -->
