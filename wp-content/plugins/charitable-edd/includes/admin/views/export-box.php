<?php 
/**
 * Renders the export box in the EDD Export tab.
 *
 * @since       1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a 
 */

?>
<div class="postbox charitable-edd-export">
    <h3><span><?php _e( 'Export Campaign Purchases', 'charitable-edd' ) ?></span></h3>
    <div class="inside">
        <p><?php _e( 'Download a CSV of purchases for a Charitable campaign.', 'charitable-edd' ); ?></p>
        <p>
            <form id="charitable-edd-export-purchases" class="edd-export-form" method="post">
                <select name="campaign" id="edd_customer_export_download">
                    <option value="0"><?php _e( 'All Campaigns', 'charitable-edd' ) ?></option>
                    <?php
                    $campaigns = get_posts( array( 'post_type' => 'campaign', 'posts_per_page' => -1 ) );
                    if ( $campaigns ) :
                        foreach( $campaigns as $campaign ) : ?>
                            
                            <option value="<?php echo $campaign->ID ?>"><?php echo get_the_title( $campaign->ID ) ?></option>
                        
                        <?php 
                        endforeach;
                    endif;
                    ?>
                </select>
                <p>
                    <?php 
                    echo EDD()->html->date_field( array( 
                        'id' => 'charitable-edd-purchases-export-start', 
                        'name' => 'start', 
                        'placeholder' => __( 'Choose start date', 'charitable-edd' ) 
                    ) );
                    
                    echo EDD()->html->date_field( array( 
                        'id' => 'charitable-edd-purchases-export-end', 
                        'name' => 'end', 
                        'placeholder' => __( 'Choose end date', 'charitable-edd' ) 
                    ) );
                    ?>
                    <select name="status">
                    <option value="any"><?php _e( 'All Statuses', 'easy-digital-downloads' ); ?></option>
                    <?php
                    $statuses = edd_get_payment_statuses();
                    foreach( $statuses as $status => $label ) : ?>
                        
                        <option value="<?php echo $status ?>"><?php echo $label ?></option>
                    
                    <?php 
                    endforeach;
                    ?>
                </select>
                </p>
                <?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                <input type="hidden" name="edd-export-class" value="Charitable_EDD_Batch_Export_Campaign_Payments" />
                <input type="submit" value="<?php _e( 'Generate CSV', 'charitable-edd' ); ?>" class="button-secondary" />
            </form>
        </p>
    </div><!-- .inside -->
</div><!-- .postbox -->