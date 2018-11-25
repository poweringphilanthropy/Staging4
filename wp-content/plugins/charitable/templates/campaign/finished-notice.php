<?php
/**
 * The template for displaying a notice at the top of the campaign 
 * summary to announce how long ago it finished.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/finished-notice.php
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Campaign
 * @since   1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$campaign = $view_args[ 'campaign' ];

$notice = $campaign->get_finished_notice();

if ( empty( $notice ) ) :
    return;
endif;

?>
<div class="campaign-finished">
    <?php echo $notice ?>
</div>