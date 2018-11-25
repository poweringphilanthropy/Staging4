<?php
/**
 * Display the campaign creator profile.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/widget-campaign-creator.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! charitable_is_campaign_page() && 'current' == $view_args[ 'campaign_id' ] ) {
    return;
}

$widget_title   = apply_filters( 'widget_title', $view_args['title'] );
$campaign_id    = $view_args[ 'campaign_id' ] == 'current' ? get_the_ID() : $view_args[ 'campaign_id' ];
$campaign       = new Charitable_Campaign( $campaign_id );
$creator        = new Charitable_User( $campaign->get_campaign_creator() );
$campaigns      = $creator->get_campaigns();
$has_links      = $creator->user_url || $creator->twitter || $creator->facebook;

charitable_ambassadors_enqueue_styles();

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
    echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;
?>
    <div class="charitable-campaign-creator">
        <a href="<?php echo get_author_posts_url( $creator->ID ) ?>"><?php echo $creator->get_avatar() ?></a>
        <div class="creator-summary">
            <h6 class="creator-name">
                <a href="<?php echo get_author_posts_url( $creator->ID ) ?>" title="<?php echo esc_attr( sprintf( "%s's %s", $creator->get_name(), __( 'profile', 'charitable-ambassadors' ) ) ) ?>">
                    <?php echo $creator->display_name ?>
                </a>
            </h6>
            <p><?php printf( _n( '%d campaign', '%d campaigns', $campaigns->post_count, 'charitable-ambassadors' ), $campaigns->post_count ) ?></p>
        </div>
        <?php if ( strlen( $creator->description ) ) : ?>
            <div class="creator-bio">
                <?php echo apply_filters( 'the_excerpt', $creator->description ) ?>
            </div>
        <?php endif ?>
        <?php if ( $has_links ) : ?>
            <ul class="creator-links">
                <?php if ( $creator->user_url ) : ?>
                    <li>
                        <a href="<?php echo $creator->user_url ?>" title="<?php printf( __( "Visit %s's website", 'charitable-ambassadors' ), $creator->get_name() ) ?>" target="_blank"><?php _e( 'Website', 'charitable-ambassadors' ) ?></a>
                    </li>
                <?php endif ?>
                <?php if ( $creator->twitter ) : ?>
                    <li>
                        <a href="<?php echo $creator->twitter ?>" title="<?php printf( __("Visit %s's Twitter profile", 'charitable-ambassadors'), $creator->get_name() ) ?>" target="_blank"><?php _e( 'Twitter', 'charitable-ambassadors' ) ?></a>
                    </li>
                <?php endif ?>

                <?php if ( $creator->facebook ) : ?>
                    <li>
                        <a href="<?php echo $creator->facebook ?>" title="<?php printf( __("Visit %s's Facebook profile", 'charitable-ambassadors'), $creator->get_name() ) ?>" class="with-icon" data-icon="&#xf09a;" target="_blank"><?php _e( 'Facebook', 'charitable-ambassadors' ) ?>
                        </a>
                    </li>
                <?php endif ?>
            </ul>
        <?php endif ?>
    </div>    
<?php 

echo $view_args['after_widget'];