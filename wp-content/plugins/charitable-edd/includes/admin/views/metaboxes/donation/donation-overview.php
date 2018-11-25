<?php 
/**
 * Renders the donation details meta box for the Donation post type.
 *
 * @author  Studio 164a
 * @since   1.1.0
 */
global $post;

$donation               = charitable_get_donation( $post->ID );
$donor                  = $donation->get_donor();
$edd_campaign_donations = get_post_meta( $post->ID, 'donation_from_edd_payment_log', true );

/* If we don't have this, load the default view. */
if ( ! $edd_campaign_donations || ! is_array( $edd_campaign_donations ) ) {
    include( charitable()->get_path( 'admin' ) . 'views/metaboxes/donation/donation-overview.php' );
    return;
}

$edd_payment_id = Charitable_EDD_Payment::get_payment_for_donation( $post->ID );

?>
<style>
#charitable-donation-overview-metabox .donation-source { color: #999; }
</style>
<div id="charitable-donation-overview-metabox" class="charitable-metabox">
    <div id="donor" class="charitable-media-block">
        <div class="donor-avatar charitable-media-image">
            <?php echo $donor->get_avatar( 80 ) ?>
        </div>
        <div class="donor-facts charitable-media-body">
            <h3 class="donor-name"><?php echo $donor->get_name() ?></h3>
            <span class="donor-email"><?php echo $donor->get_email() ?></span>
            <?php 
            /**
             * @hook charitable_donation_details_donor_facts
             */
            do_action( 'charitable_donation_details_donor_facts', $donor, $donation );
            ?>
        </div>
    </div>
    <div id="donation-summary">
        <h3 class="donation-number"><?php printf( '%s #%d', 
            __( 'Donation', 'charitable' ), 
            $donation->get_number() ) ?></h3>
        <span class="donation-date"><?php echo $donation->get_date() ?></span>
        <span class="donation-status"><?php printf( '%s: <span class="status">%s</span>', 
            __( 'Status', 'charitable' ), 
            $donation->get_status( true ) ) ?></span>
    </div>
    <table id="overview">
        <thead>
            <tr>
                <th class="col-campaign-name"><?php _e( 'Campaign', 'charitable' ) ?></th>
                <th class="col-campaign-donation-amount"><?php _e( 'Total', 'charitable' ) ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $edd_campaign_donations as $edd_campaign_donation ) : ?>
            <tr>
                <td class="campaign-name"><?php echo get_the_title( $edd_campaign_donation['campaign_id'] ) ?>
                    <?php if ( ! $edd_campaign_donation['edd_fee'] ) : ?>
                        <p class="donation-source">
                        <?php if ( array_key_exists( 'price_id', $edd_campaign_donation ) ) : 
                                printf( __( 'From purchase of %s - %s', 'charitable-edd' ),
    								get_the_title( $edd_campaign_donation['download_id'] ),
    								edd_get_price_option_name( $edd_campaign_donation['download_id'], $edd_campaign_donation['price_id'] )
    							);
                            else :
    							printf( __( 'From purchase of %s', 'charitable-edd' ),
    								get_the_title( $edd_campaign_donation['download_id'] )
    							);
    						endif;
    					    ?>
    					</p>
                    <?php endif ?>
                </td>
                <td class="campaign-donation-amount"><?php echo charitable_format_money( $edd_campaign_donation['amount'] ) ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <th><?php _e( 'Total', 'charitable' ) ?></th>
                <td><?php echo charitable_format_money( $donation->get_total_donation_amount() ) ?></td>
            </tr>
            <tr>
                <th><?php _e( 'Payment Method', 'charitable' ) ?></th>
                <td><?php echo $donation->get_gateway_label() ?></td>
            </tr>
            <tr>
                <th><?php _e( 'EDD Payment ID', 'charitable-edd' ) ?></th>
                <td><a href="<?php echo esc_url( add_query_arg( array(
                        'post_type' => 'download',
                        'page'      => 'edd-payment-history',
                        'view'      => 'view-order-details',
                        'id'        => $edd_payment_id,
                    ),
                    admin_url( 'edit.php' ) ) ) ?>">#<?php echo $edd_payment_id ?></a><br />
                    <?php //printf( __( 'Payment Total: %s', 'charitable-edd' ), edd_get_payment_amount( $edd_payment_id ) ) ?>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'EDD Payment Total', 'charitable-edd' ) ?></th>
                <td><?php echo edd_currency_filter( edd_format_amount( edd_get_payment_amount( $edd_payment_id ) ) ) ?></td>
            </tr>
            <tr>
                <th><?php _e( 'Change Status', 'charitable' ) ?></th>
                <td>
                    <select id="change-donation-status" name="post_status">
                    <?php foreach ( charitable_get_valid_donation_statuses() as $status => $label ) : ?>
                        <option value="<?php echo $status ?>" <?php selected( $status, $donation->get_status() ) ?>><?php echo $label ?></option>
                    <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr class="hide-if-js">
                <td colspan="2">
                    <input type="submit" name="update" class="button button-primary" value="<?php _e( 'Update Status', 'charitable' ) ?>" />
                </td>
            </tr>
        </tfoot>
    </table>
</div>
