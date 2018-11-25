<?php
/**
 * The template used to display the user's merchandise.
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

/**
 * @hook    charitable_user_campaigns_before
 */
do_action('charitable_user_campaigns_before');
?>

<header class="entry-header">
    <?php the_title( '<h1 class="entry-title">', '</h1>' ) ?>
</header><!-- .entry-header -->

<ul class="charitable-user-campaigns">

    <?php 
    if ( $campaigns->have_posts() ): ?>

        <?php while ( $campaigns->have_posts() ) : 
            $campaigns->the_post(); 

            $campaign = new Charitable_Campaign( get_the_ID() ); 
            ?>
            <li class="charitable-campaign charitable-campaign-<?php echo $campaign->get_status() ?>">
                <?php 

                /**
                 * @hook    charitable_user_campaign_summary_before
                 */
                do_action( 'charitable_user_campaign_summary_before', $campaign, $user );

                ?>             
                <div class="campaign-summary">
                    <h3 class="campaign-title"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title() ?></a></h3>
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

    else : ?>

        <p class="no-campaigns"><?php _e( 'You have not created any campaigns yet.', 'charitable-fes' ) ?></p>

    <?php endif ?>
    
</ul><!-- .charitable-user-campaigns -->
<p class="start-campaign">
    <a href="<?php echo esc_url( charitable_get_permalink( 'campaign_submission_page' ) ) ?>" class="button button-primary"><?php _e( 'Create a new campaign', 'charitable-fes' ) ?></a>
</p>

<?php

/**
 * @hook    charitable_user_campaigns_after
 */
do_action('charitable_user_campaigns_after');