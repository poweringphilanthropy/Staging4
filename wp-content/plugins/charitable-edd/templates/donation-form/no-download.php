<?php
/**
 * Display option to allow the donor to not select a donation and 
 * simply proceed to checkout. This will be checked by default.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

?>
<div class="charitable-edd-connected-download no-download">

	<input type="checkbox" class="charitable-edd-download-select select-no-download" name="downloads[no-reward]" value="1" checked />

	<span class="charitable-option-no-download"><?php _e( 'No Reward', 'charitable-edd' ) ?></span>

	<div class="charitable-edd-connected-download-description">

		<?php _e( 'I just want to make a donation.', 'charitable-edd' ) ?>	

	</div><!--.charitable-edd-connected-download-description-->
</div><!--.charitable-edd-connected-download-->