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
<ul class="charitable-user-campaigns charitable-user-posts">

    <?php 
    if ( $campaigns->have_posts() ): ?>

        <?php while ( $campaigns->have_posts() ) : 
            $campaigns->the_post(); 

            $campaign = new Charitable_Campaign( get_the_ID() ); 
            ?>
            <li class="charitable-campaign charitable-user-post charitable-campaign-<?php echo $campaign->get_status() ?>">
                <?php 

                /**
                 * @hook    charitable_user_campaign_summary_before
                 */
                do_action( 'charitable_user_campaign_summary_before', $campaign, $user );

                ?>             
                <div class="campaign-summary user-post-summary">
                    <h3 class="campaign-title user-post-title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title() ?></a></h3>
                    <?php 

                    /**
                     * @hook    charitable_user_campaign_summary
                     */
                    do_action( 'charitable_user_campaign_summary', $campaign, $user );

                    ?>
                </div><!-- .campaign-summary -->
                <?php 

                /**
                 * @hook    charitable_user_campaign_summary_after
                 */
                do_action( 'charitable_user_campaign_summary_after', $campaign, $user );

                ?> 
            </li> <!-- .charitable-campaign -->       

        <?php endwhile;    

        /**
         * Give the $post data back to the globals.
         */
        wp_reset_postdata();

    else : ?>

        <p class="no-campaigns"><?php _e( 'You have not created any campaigns yet.', 'charitable-ambassadors' ) ?></p>

    <?php endif ?>
    
</ul><!-- .charitable-user-campaigns -->
<p class="start-campaign">
    <a href="<?php echo esc_url( charitable_get_permalink( 'campaign_submission_page' ) ) ?>" class="button button-primary"><?php echo apply_filters( 'charitable_ambassadors_my_campaigns_button_text', __( 'Create a new campaign', 'charitable-ambassadors' ) ); ?></a>
</p>

<?php

/**
 * @hook    charitable_user_campaigns_after
 */
do_action( 'charitable_user_campaigns_after', $campaigns );
