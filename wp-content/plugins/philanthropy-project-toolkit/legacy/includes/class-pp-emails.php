<?php
/**
 * PP_Emails Class.
 *
 * @class       PP_Emails
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Emails class.
 */
class PP_Emails {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Emails();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'init', array($this, 'remove_hooks'), 11 );
        add_filter('wp_mail_from', array($this, 'change_mail_from'), 11, 1);
        add_filter('wp_mail_from_name', array($this, 'change_mail_from_name'), 11, 1);

        add_filter( 'charitable_email_content_field_value_donation_summary', array($this, 'get_donation_summary'), 11, 3 );
        add_filter( 'edd_email_tags', array($this, 'pp_edd_email_tags'), 10, 1 );

        add_filter( 'charitable_email_content_fields',         array( $this, 'add_campaign_id_fields' ), 10, 2 );
        add_filter( 'charitable_email_preview_content_fields', array( $this, 'add_preview_campaign_id_fields' ), 10, 2 );
        // add_action( 'eddtickets-send-tickets-email', array($this, 'add_staging_url'), 1 );
    
        add_filter( 'edd_settings_emails', array($this, 'add_setting_for_nonprofit_emails'), 10, 1 );
        add_filter( 'edd_purchase_receipt', array($this, 'maybe_use_nonprofit_emails'), 10, 3 );
        add_filter( 'edd_email_tags', array($this, 'nonprofit_email_tags'), 10, 1 );
        add_filter( 'edd_email_tags', array($this, 'covered_fees_email_tags'), 10, 1 );
    
        add_filter( 'charitable_emails', array($this, 'register_custom_charitable_emails') );
    }

    public function register_custom_charitable_emails($emails){
        $new_emails = array(
            'child_campaign_submission' => 'PP_Email_Child_Campaign_Submission'
        );

        $emails = array_merge( $emails, $new_emails );

        return $emails;
    }

    public function add_setting_for_nonprofit_emails($settings){

        $settings['purchase_receipts']['purchase_receipt_non_profit'] = array(
            'id'   => 'purchase_receipt_non_profit',
            'name' => __( 'Purchase Receipt for Direct', 'easy-digital-downloads' ),
            'desc' => __('Enter the text that is sent as purchase receipt email to users after completion of a successful purchase. HTML is accepted. Available template tags:','easy-digital-downloads' ) . '<br/>' . edd_get_emails_tags_list(),
            'type' => 'rich_editor',
            'std'  => __( "Dear", "easy-digital-downloads" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "easy-digital-downloads" ) . "\n\n{download_list}\n\n{sitename}",
        );

        // echo "<pre>";
        // print_r($settings);
        // echo "</pre>";
        // exit();

        return $settings;
    }

    public function maybe_use_nonprofit_emails($email_body, $payment_id, $payment_data){

        $payout_stripe_account = edd_get_option('payout_stripe_account');

        $charge_details = edd_get_payment_meta( $payment_id, 'charge_details', true );
        if( !isset($charge_details['stripe-id']) || ($charge_details['stripe-id'] == $payout_stripe_account) ){
            return $email_body;
        }

        $nonprofit_email = edd_get_option( 'purchase_receipt_non_profit', false );
        if(empty($nonprofit_email)){
            return $email_body;
        }

        return $nonprofit_email;
    }

    public function nonprofit_email_tags($email_tags){
        $non_profit_tags = array(
            array(
                'tag' => 'org_name',
                'description' => __('Non profit organization name', 'pp-toolkit'),
                'function' => array($this, 'non_profit_render_tag')
            ),
            array(
                'tag' => 'org_email',
                'description' => __('Non profit organization email', 'pp-toolkit'),
                'function' => array($this, 'non_profit_render_tag')
            ),
            array(
                'tag' => 'org_logo',
                'description' => __('Non profit organization logo image', 'pp-toolkit'),
                'function' => array($this, 'non_profit_render_tag')
            ),
            array(
                'tag' => 'org_url',
                'description' => __('Non profit organization url', 'pp-toolkit'),
                'function' => array($this, 'non_profit_render_tag')
            ),
            array(
                'tag' => 'org_tax_id',
                'description' => __('Non profit organization tax id', 'pp-toolkit'),
                'function' => array($this, 'non_profit_render_tag')
            ),
            array(
                'tag' => 'org_donor_message',
                'description' => __('Non profit organization donor message', 'pp-toolkit'),
                'function' => array($this, 'non_profit_render_tag')
            ),
        );

        // echo "<pre>";
        // print_r($email_tags);
        // echo "</pre>";

        // echo "<pre>";
        // print_r(array_merge($non_profit_tags, $email_tags));
        // echo "</pre>";
        // exit();

        return array_merge($non_profit_tags, $email_tags);
    }

    public function covered_fees_email_tags($email_tags){
        $covered_fee_tags = array(
            array(
                'tag' => 'covered_fee',
                'description' => __('Total covered fees', 'pp-toolkit'),
                'function' => array($this, 'covered_fees_render_tag')
            ),
            array(
                'tag' => 'covered_platform_fee',
                'description' => __('Covered platform fees', 'pp-toolkit'),
                'function' => array($this, 'covered_fees_render_tag')
            ),
            array(
                'tag' => 'covered_stripe_fee',
                'description' => __('Covered stripe fees', 'pp-toolkit'),
                'function' => array($this, 'covered_fees_render_tag')
            ),
            array(
                'tag' => 'price_including_fee',
                'description' => __('Total price including covered fees', 'pp-toolkit'),
                'function' => array($this, 'covered_fees_render_tag')
            ),
        );

        // echo "<pre>";
        // print_r($email_tags);
        // echo "</pre>";

        // echo "<pre>";
        // print_r(array_merge($non_profit_tags, $email_tags));
        // echo "</pre>";
        // exit();

        return array_merge($covered_fee_tags, $email_tags);
    }

    public function non_profit_render_tag($payment_id, $tag){

        $content = '';

        try {

            $charge_details = edd_get_payment_meta( $payment_id, 'charge_details', true );
            if(!isset($charge_details['stripe-id']) || empty($charge_details['stripe-id']) ){
                throw new Exception("Meta is empty.", 1);
            }

            $stripe_id = $charge_details['stripe-id'];

            $orgs = pp_get_connected_organizations();
            if(empty($orgs)){
                throw new Exception("No data on connected organizations", 1);
            }

            if(!isset($orgs[$stripe_id])){
                throw new Exception("Stripe id not registered", 1);
            }

            $org = $orgs[$stripe_id];

            $prop = str_replace('org_', '', $tag);

            if(!isset($org->$prop)){
                throw new Exception("Field not found", 1);
            }

            switch ($tag) {
                case 'org_logo':
                    $content = '<img src="'.$org->logo.'" alt="'.$org->name.'">';
                    break;
                
                default:
                    $content = $org->$prop;
                    break;
            }

        } catch (Exception $e) {
            $content = $e->getMessage();
        }

        return $content;
    }

    public function covered_fees_render_tag($payment_id, $tag){

        $content = '';

        $charge_details = edd_get_payment_meta( $payment_id, 'charge_details', true );
        $donor_covers_fee = isset($charge_details['donor-covers-fee']) && ($charge_details['donor-covers-fee'] == 'yes');

        switch ($tag) {
            case 'covered_platform_fee':
                $covered_platform_fee = $donor_covers_fee ? $charge_details['platform-fee-amount'] : 0;
                $fee = edd_currency_filter( edd_format_amount( $covered_platform_fee ) );
                $content = sprintf(__('Covered platform fee: %s', 'pp-toolkit'), html_entity_decode( $fee, ENT_COMPAT, 'UTF-8' ) );
                break;
                
            case 'covered_stripe_fee':
                $covered_stripe_fee = $donor_covers_fee ? $charge_details['stripe-fee-amount'] : 0;
                $fee = edd_currency_filter( edd_format_amount( $covered_stripe_fee ) );
                $content = sprintf(__('Covered stripe fee: %s', 'pp-toolkit'), html_entity_decode( $fee, ENT_COMPAT, 'UTF-8' ) );
                break;
                
            case 'covered_fee':
                $covered_fee = $donor_covers_fee ? $charge_details['total-fee-amount'] : 0;
                $fee = edd_currency_filter( edd_format_amount( $covered_fee ) );
                $content = sprintf(__('Covered Fees: %s', 'pp-toolkit'), html_entity_decode( $fee, ENT_COMPAT, 'UTF-8' ) );
                break;

            case 'price_including_fee':

                $total_amount = $donor_covers_fee ? floatval($charge_details['donation-amount']) + floatval($charge_details['total-fee-amount']) : $charge_details['donation-amount'];
                $formated = edd_currency_filter( edd_format_amount( $total_amount ) );
                $content = html_entity_decode( $formated, ENT_COMPAT, 'UTF-8' );
                break;
        }


        return $content;
    }

    /**
     * Fix wrong home_url and get_option('home')
     * on campaign image on ticket email
     */
    public function add_staging_url(){
        update_option( 'staging_url', home_url() );
    }

    public function remove_hooks(){

        // remove default ninja form message "Thank you for filling out this form."
        remove_action( 'ninja_forms_post_process', 'ninja_forms_email_user', 1000 );
    }

    public function change_mail_from($from_email){

        // only change if from wordpress@sitename
        if ( strpos( strtolower($from_email), 'wordpress') !== false ) {
            $from_email = get_option('admin_email');
        }

        return $from_email;
    }

    public function change_mail_from_name($from_name){

        // only change if from wordpress@sitename
        if ( strpos( strtolower($from_name), 'wordpress') !== false ) {
            $from_name = get_bloginfo( 'name' );
        }

        return $from_name;
    }
    
    /**
     * Change email content for Admin: New Donation Notification
     * we need to dsplay all data
     */
    public function get_donation_summary($value, $args, $email){
        $donation = $email->get_donation();
        $donation_id = $donation->get_donation_id();

        $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $donation_id );

        $payment = new EDD_Payment( $payment_id );

        $payment_data  = $payment->get_meta();
        $download_list = '<ul>';
        $cart_items    = $payment->cart_details;
        $cart_fees    = $payment->fees;
        $email         = $payment->email;

        $donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
        
        /**
         * Display Donations
         */
        if ( !empty($cart_fees) ) {

            foreach ($cart_fees as $fee) {
                if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) )
                    continue;

                $download_list .= '<li>' . sprintf(__('<strong>%s</strong> (%s)', 'pp-toolkit'), $fee['label'], charitable_format_money($fee['amount']) ) . '</li><br>';
            }
        }

        /**
         * Display downloads,
         * assume all downloads with 100% campaign benefactor relationship
         */
        if ( $cart_items ) {
            $show_names = apply_filters( 'edd_email_show_names', true );
            $show_links = apply_filters( 'edd_email_show_links', true );

            foreach ( $cart_items as $item ) {

                if ( edd_use_skus() ) {
                    $sku = edd_get_download_sku( $item['id'] );
                }

                if ( edd_item_quantities_enabled() ) {
                    $quantity = $item['quantity'];
                }

                $price_id = edd_get_cart_item_price_id( $item );
                if ( $show_names ) {

                    $title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

                    // if ( ! empty( $quantity ) && $quantity > 1 ) {
                    //  $title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
                    // }

                    if ( ! empty( $sku ) ) {
                        $title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
                    }

                    if ( edd_has_variable_prices( $item['id'] ) && isset( $price_id ) ) {
                        $title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
                    }

                    $download_list .= '<li>' . $item['quantity'] . 'x ' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . ' ('.charitable_format_money($item['price']).')<br/>';
                }

                $files = edd_get_download_files( $item['id'], $price_id );

                if ( ! empty( $files ) ) {

                    foreach ( $files as $filekey => $file ) {

                        if ( $show_links ) {
                            $download_list .= '<div>';
                            $file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
                            $download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
                            $download_list .= '</div>';
                        } else {
                            $download_list .= '<div>';
                            $download_list .= edd_get_file_name( $file );
                            $download_list .= '</div>';
                        }

                    }

                } elseif ( edd_is_bundled_product( $item['id'] ) ) {

                    $bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );

                    foreach ( $bundled_products as $bundle_item ) {

                        $download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

                        $files = edd_get_download_files( $bundle_item );

                        foreach ( $files as $filekey => $file ) {
                            if ( $show_links ) {
                                $download_list .= '<div>';
                                $file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
                                $download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
                                $download_list .= '</div>';
                            } else {
                                $download_list .= '<div>';
                                $download_list .= edd_get_file_name( $file );
                                $download_list .= '</div>';
                            }
                        }
                    }
                }


                // if ( '' != edd_get_product_notes( $item['id'] ) ) {
                //  $download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
                // }


                if ( $show_names ) {
                    $download_list .= '</li><br>';
                }
            }
        }

        if ( ( $fees = edd_get_payment_fees( $payment->ID, 'fee' ) ) ){
            foreach ($fees as $fee) {
                $download_list .= '<li>';
                $download_list .= sprintf(__('<strong>%s</strong> (%s)', 'pp-toolkit'), $fee['label'], charitable_format_money($fee['amount']));
                $download_list .= '</li><br>';
            }
        }


        $download_list .= '</ul>';

        return $download_list;
    }

    /**
     * Change charitable edd default download lists on email
     * @param  [type] $email_tags [description]
     * @return [type]             [description]
     */
    public function pp_edd_email_tags($email_tags){

        if(empty($email_tags) || !is_array($email_tags) || !class_exists('Charitable_EDD_Cart'))
            return $email_tags;

        foreach ($email_tags as $key => $email_tag) {
            if(isset($email_tag['tag']) && ($email_tag['tag'] != 'download_list') )
                continue;

            $email_tags[$key]['function'] = ('text/html' == EDD()->emails->get_content_type()) ? 'pp_edd_email_tag_download_list' : 'pp_edd_email_tag_download_list_plain';
        }

        // echo "<pre>";
        // print_r($email_tags);
        // echo "</pre>";
        // exit();

        return $email_tags;
    }

    public function add_campaign_id_fields( $fields, Charitable_Email $email ) {

        if ( !in_array($email->get_email_id(), array('creator_campaign_submission', 'new_campaign') ) ) {
            return $fields;
        }

        if ( ! in_array( 'campaign', $email->get_object_types() ) ) {
            return $fields;
        }

        $fields['campaign_id'] = array(
            'description'   => __( 'The ID of the campaign', 'charitable' ),
            'callback'      => array( $this, 'get_campaign_id' ),
        );

        return $fields;

    }

    public function add_preview_campaign_id_fields($fields, Charitable_Email $email) {

        if ( !in_array($email->get_email_id(), array('creator_campaign_submission', 'new_campaign') ) ) {
            return $fields;
        }

        if ( ! in_array( 'campaign', $email->get_object_types() ) ) {
            return $fields;
        }

        $fields['campaign_id'] = 1234;
        return $fields;
    }

    public function get_campaign_id($value, $args, Charitable_Email $email) {
        if ( ! $email->has_valid_campaign() ) {
            return '';
        }

        $campaign = $email->get_campaign();

        return $campaign->ID;
    }


    public function includes(){
        include_once( 'emails/class-pp-email-child-campaign-submitted.php');
    }

}

PP_Emails::init();