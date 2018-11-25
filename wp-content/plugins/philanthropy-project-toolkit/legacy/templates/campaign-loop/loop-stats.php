<?php 
/**
 * New Campaign loop stats.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign-loop/loop-stats.php
 *
 * @package Reach
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign           = $view_args[ 'campaign' ];
$currency_helper    = charitable_get_currency_helper();
// $donors             = philanthropy_get_donors();

$color = (isset($view_args['color']) && !empty($view_args['color'])) ? $view_args['color'] : false;
$style_bg_color = '';
if(!empty($color)){
    $style_bg_color = ' style="background:'.$color.'"';
}
$style_color = '';
if(!empty($color)){
    $style_color = ' style="color:'.$color.'"';
}

$progress_stroke_color = (!empty($color)) ? $color : '#1e73be';

/**
 * Change donors count to be same as widget
 * (withoit distinc /  group donations by the same person)
 * @var array
 */
$campaign_ids = pp_get_merged_team_campaign_ids($campaign->ID);
$query_args = array(
    'number' => -1,
    'output' => 'donors',
    'campaign' => $campaign_ids,
    'distinct_donors' => false,
    'distinct' => false,
);
$donors = new Charitable_Donor_Query( $query_args );
$donate_amount = (!empty($campaign->get_donated_amount())) ? $campaign->get_donated_amount() : 0;
?>
<div class="loop-stats">
    <ul class="pp_ul">
        <li class="pp_li li_barometer">
            <div class="pp_li_left w_barometer" <?php echo $style_color; ?>>
                <span class="d"><span class="barometer" data-progress="<?php echo $campaign->get_percent_donated_raw(); ?>" 
                data-width="42" 
                data-height="42" 
                data-strokewidth="8" 
                data-stroke="#ccc"
                data-progress-stroke="<?php echo $progress_stroke_color; ?>">
                </span></span>
            </div>
            <div class="pp_li_right">
                <span class="percent" <?php echo $style_color; ?>><?php echo number_format( $campaign->get_percent_donated_raw(), 0 ); ?> %</span>
                <span class="text">FUNDED</span>
            </div>
        </li>
        <li class="pp_li">
            <div class="pp_li_left" <?php echo $style_color; ?>>
                <i class="fa fa-dollar"></i>
            </div>
            <div class="pp_li_right">
                <span class="percent" <?php echo $style_color; ?>><?php echo number_format($donate_amount, 0); ?></span>
                <span class="text">RAISED</span>
            </div>
        </li>
        <li class="pp_li">
            <div class="pp_li_left" <?php echo $style_color; ?>>
                <i class="fa fa-user"></i>
            </div>
            <div class="pp_li_right">
                <span class="percent" <?php echo $style_color; ?>><?php echo $donors->count(); ?></span>
                <span class="text"><?php echo ( $donors->count() > 1) ? 'DONORS' : 'DONOR'; ?></span>
            </div>
        </li>
        <li class="pp_li">
            <div class="pp_li_left" <?php echo $style_color; ?>>
                <i class="fa fa-hourglass-half"></i>
            </div>

            <?php  
            $hour = 3600;
            $day = 86400;

            $seconds_left = $campaign->get_seconds_left();

            /* Condition 1: The campaign has finished. */
            if ( 0 === $seconds_left ) {

                $remaining = '-';
                $type = __('Ended', 'philanthropy');

            } /* Condition 2: There is less than an hour left. */
            elseif ( $seconds_left <= $hour ) {

                $remaining = ceil( $seconds_left / 60 );
                $type = ($remaining > 1) ? __('Minutes Left', 'philanthropy') : __('Minute Left', 'philanthropy');

            } /* Condition 3: There is less than a day left. */
            elseif ( $seconds_left <= $day ) {

                $remaining = floor( $seconds_left / 3600 );
                $type = ($remaining > 1) ? __('Hours Left', 'philanthropy') : __('Hour Left', 'philanthropy');

            } /* Condition 4: There is more than a day left. */
            else {

                $remaining = floor( $seconds_left / 86400 );
                $type = ($remaining > 1) ? __('Days Left', 'philanthropy') : __('Day Left', 'philanthropy');

            }
            ?>
            <div class="pp_li_right">
                <span class="percent" <?php echo $style_color; ?>><?php echo $remaining; ?></span>
                <span class="text"><?php echo strtoupper($type); ?></span>
            </div>
        </li>
    </ul>
</div>