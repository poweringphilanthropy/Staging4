<?php
/**
 * Display list of downloads that the donor can purchase which will
 * make a donation to the campaign.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

$campaign       = $view_args[ 'campaign' ];
$edd_campaign   = $view_args[ 'edd_campaign' ];
$form           = $view_args[ 'form' ];

$downloads      = $edd_campaign->get_connected_downloads();

if ( false === $downloads ) {
    return;
}

if ( $downloads->have_posts() ) :

    $groups     = pp_edd_organize_downloads( $downloads->posts );

    echo '<pre>'; var_dump( $groups ); echo '</pre>';
    die;

    /**
     * @hook    charitable_edd_donation_form_before_downloads
     */
    do_action( 'charitable_edd_donation_form_before_downloads', $campaign, $downloads );

    foreach ( $groups as $key => $items ) : ?>

        <h3 class="charitable-form-header"><?php echo ucfirst( $key ) ?></h3>

        <div class="charitable-connected-downloads">

            <?php 
            /**
             * @hook    charitable_edd_donation_form_before_first_download
             */
            do_action( 'charitable_edd_donation_form_before_first_download', $downloads ); 

            foreach ( $items as $item ) : 

                /**
                 * @hook    charitable_edd_donation_form_download
                 */
                do_action( 'charitable_edd_donation_form_download', $item->ID );

            endforeach; ?>

        </div>

    <?php 

    endforeach;        

    /**
     * @hook    charitable_edd_donation_form_after_downloads
     */
    do_action( 'charitable_edd_donation_form_after_downloads', $campaign, $downloads );
    
endif;