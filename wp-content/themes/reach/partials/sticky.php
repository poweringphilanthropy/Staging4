<?php
/**
 * Display the sticky notice, if the post is sticky.
 */

if ( ! is_sticky() ) :
	return;
endif;

?> 
<div class="sticky-tag"><?php _e( 'Sticky', 'reach' ) ?></div>
