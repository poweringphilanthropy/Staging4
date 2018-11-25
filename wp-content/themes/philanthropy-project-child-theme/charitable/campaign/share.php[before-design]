<?php
/**
 * The template for displaying the campaign sharing icons on the campaign page.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign/summary.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign = $view_args[ 'campaign' ];
$permalink = urlencode( get_the_permalink( $campaign->ID ) );
$title = urlencode( get_the_title( $campaign->ID ) );

?>
<div class="uk-grid">
    <div class="uk-width-1-1 uk-width-medium-3-5">
        <ul class="campaign-sharing share horizontal rrssb-buttons">
            <li><h6><?php _e( 'Spread The Word:', 'reach' ) ?></h6></li>
            <li class="share-twitter">
                <a href="http://twitter.com/home?status=<?php echo $title ?>%20<?php echo $permalink ?>" class="popup icon" data-icon="&#xf099;"></a>
            </li>
            <li class="share-facebook">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $permalink ?>" class="popup icon" data-icon="&#xf09a;"></a>
            </li>
        <!--     <li class="share-pinterest">
                <a href="http://pinterest.com/pin/create/button/?url=<?php // echo $permalink ?>&amp;description=<?php // echo $title ?>" class="popup icon" data-icon="&#xf0d2;"></a>
            </li> -->
            <li class="share-email">
                <a title="Share via Email" href="mailto:?subject=Check out this campaign on Greeks4Good&body=<?= site_url($_SERVER['REQUEST_URI']) ?>" class="icon fa fa-envelope"></a>
            </li>
            <li class="share-widget">
                <a href="#campaign-widget-<?php the_ID() ?>" class="icon" data-icon="&#xf121;" data-trigger-modal></a>
                <div id="campaign-widget-<?php the_ID() ?>" class="modal">
                    <a class="modal-close"></a>
                    <h4 class="block-title"><?php _e( 'Share Campaign', 'reach' ) ?></h4>
                    <div class="uk-grid share-widget-grid">
                        <div class="uk-width-medium-1-2">
                            <?php echo apply_filters( 'the_excerpt', get_theme_mod( 'campaign_sharing_text', '' ) ) ?>
                            <p><strong><?php _e( 'Embed Code', 'reach' ) ?></strong></p>
                            <pre><?php echo htmlspecialchars( '<iframe src="' . charitable_get_permalink( 'campaign_widget_page' ) . '" width="275px" height="468px" frameborder="0" scrolling="no" /></iframe>' ) ?></pre>
                        </div>
                        <div class="uk-width-medium-1-2">
                            <p><strong><?php _e( 'Preview', 'reach' ) ?></strong></p>
                            <iframe src="<?php echo charitable_get_permalink( 'campaign_widget_page' ) ?>" width="275px" height="468px" frameborder="0" scrolling="no" /></iframe>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    
    <?php $parent_id = wp_get_post_parent_id( $campaign->ID ); ?>
    <?php if( ($campaign->get('team_fundraising') == 'on') && !$campaign->has_ended() ): ?>
    <div class="uk-width-1-1 uk-width-medium-2-5 wrapper-create-team-fundraising">
        <a href="<?php echo trailingslashit( get_permalink( $campaign->ID ) ) . 'create'; ?>" class="button create-team-fundraising">FUNDRAISE FOR THIS CAMPAIGN</a>
    </div>
    <?php elseif( !empty($parent_id) ) : ?>
    <div class="uk-width-1-1 uk-width-medium-2-5 wrapper-create-team-fundraising">
        <a href="<?php echo trailingslashit( get_permalink( $parent_id ) ); ?>" class="button create-team-fundraising">SEE MAIN CAMPAIGN</a>
    </div>
    <?php endif; ?>
</div>