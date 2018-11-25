<?php
/**
 * Display the post thumbnail, if there is one.
 *
 * @package Reach
 */

if ( has_post_thumbnail() ) :
	?> 
	<div class="featured-image">

		<?php if ( is_single() ) : ?>

			<?php the_post_thumbnail() ?> 
			
		<?php else : ?>

			<a href="<?php the_permalink() ?>" title="<?php printf( __( 'Go to %s', 'reach' ), get_the_title() ) ?>">
				<?php the_post_thumbnail() ?> 
			</a>

		<?php endif ?>

	</div>
<?php endif ?>
