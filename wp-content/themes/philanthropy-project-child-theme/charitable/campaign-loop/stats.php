<?php 
/**
 * Campaign stats.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign-loop/stats.php
 *
 * @package Reach
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign           = $view_args[ 'campaign' ];
$currency_helper    = charitable_get_currency_helper();
?>
<ul class="campaign-stats">
    <li class="barometer" data-progress="<?php echo $campaign->get_percent_donated_raw() ?>" 
        data-width="42" 
        data-height="42" 
        data-strokewidth="8" 
        data-stroke="#ccc"
        data-progress-stroke="#1e73be">
    </li>
    <li class="campaign-raised">
        <span><?php echo number_format( $campaign->get_percent_donated_raw(), 2 ) ?><sup>%</sup></span>
        <?php _e( 'Funded', 'reach' ) ?>        
    </li>
    <li class="campaign-pledged">
        <span><?php echo $currency_helper->get_monetary_amount( $campaign->get_donated_amount() ) ?></span>
        <?php _e( 'Pledged', 'reach' ) ?>               
    </li>
    <li class="campaign-time-left">
        <?php echo $campaign->get_time_left() ?>                
    </li>       
</ul>