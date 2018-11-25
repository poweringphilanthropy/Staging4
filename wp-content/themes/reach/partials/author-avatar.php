<?php
/**
 * Partial template displaying the author's avatar.
 *
 * @package     Reach
 */

$size = 140;

if ( reach_has_charitable() ) :

	$avatar = reach_get_current_charitable_user()->get_avatar( $size );

else :

	$avatar = get_avatar( reach_get_current_author()->ID, $size );

endif;

?>
<div class="author-avatar">
	<?php echo $avatar ?>
</div><!-- .author-avatar -->
