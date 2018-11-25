<?php 
/**
 * Campaign summary template. Shows the campaign short description and stats, as well as sharing buttons.
 *
 * @package reach
 */

$campaign = charitable_get_current_campaign();

if ( $campaign->has_goal() && $campaign->has_achieved_goal() ) :
    
    $message = __( 'This campaign successfully reached its funding goal and ended %s ago', 'reach' );
    
else :

    $message = __( 'This campaign ended %s ago', 'reach' );

endif;
?>
<h3 class="campaign-ended">
    <?php printf( $message, '<span class="time-ago">' . human_time_diff( $campaign->get_end_time() ) . '</span>' ) ?>
</h3>