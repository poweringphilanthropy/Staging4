<?php
/**
 * The template for displaying the campaign heading call to action
 *
 * Override this template by copying it to your-child-theme/charitable/campaign-loop/heading-button.php
 *
 * @author lafif <[<email address>]>
 * @since 1.0 [<description>]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];
?>
<section class="section-heading-button">
	<?php
	/**
	 * @hook philanthropy_heading_content_before
	 */
	do_action( 'philanthropy_heading_button_before', $campaign );
	
	?>
	<div class="heading-button-container">
		<?php
		/**
		 * @hook philanthropy_heading_content
		 */
		do_action( 'philanthropy_heading_button', $campaign );

		?>
	</div>
	<?php
	/**
	 * @hook philanthropy_heading_button_after
	 */
	do_action( 'philanthropy_heading_button_after', $campaign );
	?>
</section>