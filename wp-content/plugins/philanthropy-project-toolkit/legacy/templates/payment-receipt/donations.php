<?php
/**
 * Display a notice on the EDD checkout page showing the amount to be donated.
 *
 * @since       1.0.0
 * @author      Eric Daams 
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */
global $edd_receipt_args;

$payment_id = $edd_receipt_args['id'];

$edd_cart = Charitable_EDD_Cart::create_with_payment( $payment_id );

$campaigns_on_payment = PP_Team_Fundraising::pp_get_campaigns_on_payment($payment_id);

// echo "<pre>";
// print_r($campaigns_on_payment);
// echo "</pre>";

$at_date    = new DateTime( get_post_field( 'post_date', $payment_id ) );

$meta       = edd_get_payment_meta( $payment_id );
$email      = edd_get_payment_user_email( $payment_id );

/* If there are no benefactors or fees for this payment, return. */
if ( ! $edd_cart->has_benefactors( $at_date ) && ! $edd_cart->has_donations_as_fees() ) {
    return;
}


$campaign_donations = array();

/* Add all campaign donations created as fees. */
if ( $edd_cart->has_donations_as_fees() ) {

    foreach ( $edd_cart->get_fees() as $campaign_id => $fees ) {

        if(!array_key_exists($campaign_id, $campaigns_on_payment))
            continue;

        $benefit_amount = array_sum( $fees );

        $campaign_donations[$campaign_id][] = array(
            'donation_type' => 'donation',
            'campaign_id' => $campaign_id,
            'amount'      => $benefit_amount,
        );

    }
}

/* Add all campaign donations from benefactor relationships. */
foreach ( $edd_cart->get_benefactor_benefits() as $benefactor_id => $benefits ) {

    foreach ( $benefits as $b_download_id => $benefit ) {
        
        $campaign_id = $benefit['campaign_id'];

        if(!array_key_exists($campaign_id, $campaigns_on_payment))
            continue;

        /**
         * parse actual download id, since charitable edd concate download id with price id
         * @see  Charitable_EDD_Cart::filter_download() [<description>]
         * @var [type]
         */
        $_download_id = explode('_', $b_download_id);
        $download_id = $_download_id[0];
        $price_id = ( edd_has_variable_prices( $download_id ) && isset($_download_id[1]) ) ? $_download_id[1] : null;

        /* Unless a download is a ticket, we will classify it as "merchandise". */
        $download_type = has_term( 'ticket', 'download_category', $download_id ) ? 'tickets' : 'merchandise';

        $campaign_donations[$campaign_id][] = array(
            'donation_type' => $download_type,
            'campaign_id' => $benefit['campaign_id'],
            'download_id' => $download_id,
            'price_id'      => $price_id,
            'price' => $benefit['price'],
            'quantity' => $benefit['quantity'],
            'download_files'      => edd_get_download_files( $download_id, $price_id ),
            'amount'      => $benefit['contribution'],
        );

    }
}

?>
<h3><?php _e( 'Donation Details', 'charitable-edd' ) ?></h3>
<table id="charitable-edd-donations" class="edd-table">

<?php 
foreach ( $campaign_donations as $campaign_id => $data ){ ?>
    <thead>
        <th colspan="2">Campaign: <a target="_blank" href="<?php echo get_permalink( $campaign_id ); ?>"><?php echo get_the_title( $campaign_id ) ?></a></th>
    </thead>
    <tbody>
    <?php 
    foreach ($data as $key => $donation_data){

        $donation_title = (isset($donation_data['download_id'])) ? get_the_title( $donation_data['download_id'] ) : ucfirst($donation_data['donation_type']); // default

        ?>
        <tr>
            <td><strong>
            <?php 
            // echo title
            echo $donation_title;

            if( isset($donation_data['download_id']) && edd_has_variable_prices( $donation_data['download_id'] ) && ! is_null( $price_id ) ) : ?>
            <span class="edd_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo edd_get_price_option_name( $donation_data['download_id'], $price_id, $payment_id ); ?></span>
            <?php endif;

            // ticket print / download url
            if(!empty($donation_data['download_files']) && is_array($donation_data['download_files']) ):
            foreach ($donation_data['download_files'] as $filekey => $file){
                $download_url = edd_get_download_file_url( $meta['key'], $email, $filekey, $donation_data['download_id'], $donation_data['price_id'] );
                $donation_title .= '<br>';
                $donation_title .= '<a href="'. esc_url( $download_url ) .'" class="edd_download_file_link">'. edd_get_file_name( $file ) .'</a>';

                do_action( 'edd_receipt_files', $filekey, $file, $donation_data['download_id'], $payment_id, $meta );
            
            } // $donation_data['download_files']
            endif;
            ?>
            </strong></td>
            <td class="donation-amount"><?php echo charitable_format_money($donation_data['amount']); ?></td>
        </tr>
    <?php 
    } // $data ?>
    </tbody>
    <tfoot>
        <tr>
            <td><?php echo sprintf(__('Total donation for %s', 'pp-toolkit'), get_the_title( $campaign_id ) ); ?></td>
            <td class="donation-amount"><?php echo charitable_format_money( $edd_cart->get_total_campaign_benefit_amount( $campaign_id ) ) ?></td>
        </tr>
    </tfoot>
<?php 
} // $campaign_donations ?> 
</table>

<?php


// echo $payment_id;
// echo "<pre>";
// print_r($campaign_donations);
// echo "</pre>";