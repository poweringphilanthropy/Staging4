<div class="campaigns-grid-wrapper">                                

    <nav class="campaigns-navigation" role="navigation">
        <a class="menu-toggle menu-button toggle-button" aria-controls="menu" aria-expanded="false"></a>
        <?php reach_crowdfunding_campaign_nav() ?>              
    </nav>

    <h3 class="section-title"><?php _e( 'Latest Projects', 'reach' ) ?></h3>

    <?php 
    $campaigns = Charitable_Campaigns::query( array( 
        'post__not_in' => array( PHILANTHROPY_PROJECT_CAMPAIGN_ID )
    ) );

    charitable_template_campaign_loop( $campaigns, 3 ) ?>

    <?php 
    wp_reset_postdata();

    if ( $campaigns->max_num_pages > 1 ) : ?> 

        <p class="center">
            <a class="button button-alt" href="<?php echo site_url( apply_filters( 'reach_previous_campaigns_link', '/campaigns/page/2/' ) ) ?>">
                <?php echo apply_filters( 'reach_previous_campaigns_text', __( 'Previous Campaigns', 'reach' ) ) ?>
            </a>
        </p>

    <?php endif ?>

</div>