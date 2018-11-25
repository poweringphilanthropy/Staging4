<?php
/**
 * Custom display for edd downloads
 * @author lafif <[<email address>]>
 * @since 1.0 [<description>]
 */

$form = $view_args[ 'form' ];
$campaign = $form->get_campaign();
$edd_campaign = $form->get_edd_campaign();
$downloads = $edd_campaign->get_connected_downloads();

if ( false === $downloads ) {
    return;
}

if ( $downloads ) :
    /**
     * @hook    charitable_edd_donation_form_before_downloads
     */
    do_action( 'charitable_edd_donation_form_before_downloads', $campaign, $downloads );

    $philanthropy_downloads = philanthropy_get_connected_downloads($edd_campaign);

    ?>

    <div class="philanthropy-charitable-connected-downloads">

    <?php 
    /**
     * @hook    charitable_edd_donation_form_before_first_download
     */
    do_action( 'charitable_edd_donation_form_before_first_download', $downloads );  


    foreach ( $philanthropy_downloads as $key => $items ) : 

        if(!isset($items['posts']) || empty($items['posts']))
            continue;
        ?>

        <div class="philanthropy-group-connected-downloads">

            <?php if(isset($items['term']->name)){ // display header ?>
            <div class="charitable-form-header"><?php echo $items['term']->name; ?></div>
            <?php } ?>

            <?php  
            foreach ( $items['posts'] as $item_key => $item ) : 

                switch ($key) {
                    case 'ticket':
                        $event_id = absint( $item_key );
                        $event = get_post($event_id);
                        setup_postdata( $GLOBALS['post'] =& $event );
                        ?>

                        <div class="cart">

                            <?php Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->front_end_tickets_form(''); ?>

                        </div>
                        <?php

                        wp_reset_postdata();
                        break;
                    
                    default:
                        /**
                         * @hook    charitable_edd_donation_form_download
                         */
                        do_action( 'charitable_edd_donation_form_download', $item );

                        break;
                }

            endforeach; // end $items['posts']
            ?>
            
        </div> <!-- philanthropy-group-connected-downloads -->
    <?php
    endforeach;   
    ?>

    </div>

    <?php 
    /**
     * @hook    charitable_edd_donation_form_after_downloads
     */
    do_action( 'charitable_edd_donation_form_after_downloads', $campaign, $downloads );
    
endif;