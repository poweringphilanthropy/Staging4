<?php
/**
 * The template used to display the user's campaigns.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/shortcodes/my-campaigns.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$user = new Charitable_User( wp_get_current_user() );

$campaigns = $user->get_campaigns( array( 
    'posts_per_page'    => -1, 
    'post_status'       => array( 'future', 'publish', 'pending', 'draft' )
) );

$currency_helper = charitable_get_currency_helper();

charitable_ambassadors_enqueue_styles();

/**
 * @hook    charitable_user_campaigns_before
 */
do_action( 'charitable_user_campaigns_before', $campaigns );

?>
<div class="charitable-user-campaigns charitable-user-posts pp-my-campaigns">
    <?php if ( $campaigns->have_posts() ): ?>

        <?php while ( $campaigns->have_posts() ) : 
            $campaigns->the_post(); 

            $campaign = new Charitable_Campaign( get_the_ID() ); 
        ?>
        <div class="campaign-wrapper campaign-summary">
            <div class="my-campaign-title">
                <?php the_title(); ?>
            </div>
            <?php if ( has_post_thumbnail() ): ?>
            <a href="<?php the_permalink(); ?>">
            <div class="image-wrapper">
                <?php  
                $thumb_url = get_the_post_thumbnail_url( $campaign->ID, 'full' );
                $thumb_url = aq_resize( $thumb_url, 1900, 600, true, true, true );
                ?>
                <img src="<?php echo $thumb_url; ?>" alt="">
            </div>
            </a>
            <?php endif; ?>

            <div class="content-wrapper uk-grid">
                <div class="uk-width-1-1 uk-width-medium-6-10">
                    <h3 class="campaign-id">Campaign ID : 
                        <?php if($campaign->post_status == 'publish') : ?>
                            <a class="link" href="<?php the_permalink(); ?>"><?php echo $campaign->ID; ?></a>
                        <?php else : ?>
                            <?php echo $campaign->ID; ?>
                        <?php endif; ?>
                    </h3>
                    <div class="description">
                        <div class="text">
                            <p>Make sure to download our mobile app so you can accept donations on the go!</p>
                        </div>
                        <a class="download-app" target="_blank" href="https://itunes.apple.com/US/app/id1265173327?mt=8">
                            <img src="<?php echo pp_toolkit()->directory_url . 'assets/img/download-app.png'; ?>" alt="">
                        </a>
                    </div>
                    <div class="ctas">
                        <div class="cta-link">
                            <div class="icon">
                                <img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-edit-campaign.png'; ?>" alt="">
                            </div>
                            <a href="<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => get_the_ID() ) ) ) ?>">Edit Campaign</a>
                        </div>
                        <div class="cta-link">
                            <div class="icon">
                                <img src="<?php echo pp_toolkit()->directory_url . 'assets/img/my-campaigns/icon-view-reports.png'; ?>" alt="">
                            </div>
                            <a href="<?php echo esc_url( charitable_get_permalink( 'campaign_report_page', array( 'campaign_id' => get_the_ID() ) ) ) ?>">View Reports</a>
                        </div>
                    </div>
                </div>
                <div class="uk-width-1-1 uk-width-medium-4-10">
                    <div class="cf">
                        <?php charitable_template( 'campaign/progress-barometer.php', array( 'campaign' => $campaign ) ); ?>
                        <?php charitable_template( 'campaign/stats.php', array( 'campaign' => $campaign ) ); ?>
                        <?php charitable_template_campaign_time_left($campaign); ?>
                    </div>
                </div>
            </div>
        </div>    

        <?php endwhile;    

        /**
         * Give the $post data back to the globals.
         */
        wp_reset_postdata();
        ?>

    <?php else : ?>

        <p class="no-campaigns"><?php _e( 'You have not created any campaigns yet.', 'charitable-ambassadors' ) ?></p>

    <?php endif ?>
</div>
<?php

/**
 * @hook    charitable_user_campaigns_after
 */
do_action( 'charitable_user_campaigns_after', $campaigns );
