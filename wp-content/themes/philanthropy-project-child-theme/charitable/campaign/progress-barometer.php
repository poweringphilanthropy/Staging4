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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign = $view_args[ 'campaign' ];

if(wp_is_mobile()):
?>
<div class="barometer" 
    data-progress="<?php echo $campaign->get_percent_donated_raw() ?>" 
    data-width="150" 
    data-height="150" 
    data-strokewidth="11" 
    data-stroke="#ccc"
    data-progress-stroke="<?php echo esc_attr( get_theme_mod( 'accent_colour', '#7bb4e0' ) ) ?>"
    >
    <span>
        <?php printf( _x( '%s Funded', 'x percent funded', 'reach' ), '<span>' . number_format( $campaign->get_percent_donated_raw(), 0 ) . '<sup>%</sup></span>' ) ?>
    </span>
</div>
<?php
else:
?>
<div class="barometer large" 
    data-progress="<?php echo $campaign->get_percent_donated_raw() ?>" 
    data-width="180" 
    data-height="180" 
    data-strokewidth="11" 
    data-stroke="#ccc"
    data-progress-stroke="<?php echo esc_attr( get_theme_mod( 'accent_colour', '#7bb4e0' ) ) ?>"
    >
    <span>
        <?php printf( _x( '%s Funded', 'x percent funded', 'reach' ), '<span>' . number_format( $campaign->get_percent_donated_raw(), 0 ) . '<sup>%</sup></span>' ) ?>
    </span>
</div>
<?php endif; ?>