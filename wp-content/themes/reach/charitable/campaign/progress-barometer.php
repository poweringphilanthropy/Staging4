<?php
/**
 * The template for displaying the campaign's progress barometer.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign/progress-barometer.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];

/**
 * Get the progress as a number. i.e. 66 = 66%.
 *
 * If the campaign does not have a goal, this will equal false.
 *
 * @see 	Charitable_Campaign::get_percent_donated_raw()
 */
$progress = $campaign->get_percent_donated_raw();

if ( false === $progress ) {
	return;
}

?>
<div class="barometer" 
	data-progress="<?php echo $progress ?>" 
	data-width="148" 
	data-height="148" 
	data-strokewidth="11" 
	data-stroke="#fff" 
	data-progress-stroke="<?php echo esc_attr( get_theme_mod( 'text_colour', '#7D6E63' ) ) ?>"
	>
	<span><?php printf(
		_x( '<span>%s<sup>&#37;</sup></span> Funded', 'x percent funded', 'reach' ),
		number_format( $progress, 0 )
	) ?></span>
</div>
