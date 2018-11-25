<?php
/**
 * Displays the campaign description.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/description.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$campaign = $view_args['campaign'];

?>
<div class="campaign-description">  
	<?php echo $campaign->description ?>
</div>
