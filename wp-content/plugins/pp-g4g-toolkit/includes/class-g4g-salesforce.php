<?php
/**
 * G4G_Salesforce Class.
 *
 * @class       G4G_Salesforce
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use GuzzleHttp\Client;

/**
 * G4G_Salesforce class.
 */
class G4G_Salesforce {

    private $endpoint;
    private $options;
    private $access_token;
    private $instance_url;

    private $g4g_account_c;
    private $g4g_record_type_id;
    private $greater_cause_stripe_id;

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new G4G_Salesforce();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        $this->g4g_account_c = defined('G4G_ACCOUNT_C') ? G4G_ACCOUNT_C : '0010x00000Dhcwp';
        $this->g4g_record_type_id = defined('G4G_RECORD_TYPE_ID') ? G4G_RECORD_TYPE_ID : '012i00000016TZU';
        $this->greater_cause_stripe_id = defined('G4G_GREATER_CAUSE_STRIPE_ID') ? G4G_GREATER_CAUSE_STRIPE_ID : 'acct_1BtLuVGddygpDFC9';

        $this->endpoint = defined('G4G_SALESFORCE_ENDPOINT') ? G4G_SALESFORCE_ENDPOINT : 'https://test.salesforce.com/';

        $this->options = [
            'grant_type' => 'password',
            'client_id' => defined('G4G_SALESFORCE_CLIENT_ID') ? G4G_SALESFORCE_CLIENT_ID : '3MVG9Yb5IgqnkB4pxHSwmNGFq5WJrripcPzorLTnx5IziRphHXn3oSKVIkBbkcHvkNQm7BpuECcHhGmJSncle',
            'client_secret' => defined('G4G_SALESFORCE_CLIENT_SECRET') ? G4G_SALESFORCE_CLIENT_SECRET : '7745084759888864645',
            'username' => defined('G4G_SALESFORCE_USERNAME') ? G4G_SALESFORCE_USERNAME : 'greeks4good@hq.kappasigma.org',
            'password' => defined('G4G_SALESFORCE_PASSWORD') ? G4G_SALESFORCE_PASSWORD : 'philanthropy101'
        ];

        add_action( 'init', array($this, 'test') );

        add_filter( 'charitable_user_fields', array($this, 'salesforce_charitable_user_fields'), 30, 2 );


        // sync actions
        add_action( 'transition_post_status', array( $this, 'on_transition_post_status' ), 10, 3 );
        add_action( 'charitable_campaign_submission_save', array( $this, 'on_charitable_campaign_submission_save' ), 10, 3 );
        // add_action( 'charitable_after_save_donation', array($this, 'update_salesforce_agcamount'), 10, 2 );
        add_action( 'edd_payment_saved', array($this, 'update_salesforce_agcamount_on_payment_save'), 10, 2 );
        add_action( 'edd_payment_saved', array($this, 'create_salesforce_order_id_for_greater_cause'), 10, 2 );
        
        // campaign dashboard
        // add_action( 'pp_on_insert_chapter_service_hours', array($this, 'add_salesforce_additional_info'), 10, 1 );
       
        add_action( 'g4g_on_user_service_hours_added', array($this, 'add_salesforce_additional_info_for_user_service_hours'), 10, 1 );
        add_action( 'g4g_on_members_user_service_hours_added', array($this, 'update_agc_hours_on_event'), 10, 2 );
    }

    public function salesforce_charitable_user_fields($fields, $form){
        $fields[ 'crm_account_id' ] = array( 
            'label'     => __( 'CRM Account ID', 'philanthropy' ), 
            'type'      => 'hidden', 
            'priority'  => 14, 
            'required'  => false, 
            'value'     => $this->get_user_value( 'crm_account_id' )
        );

        return $fields;
    }

    /**
     * Return the current user's Charitable_User object.
     *
     * @return  Charitable_User
     * @access  public
     * @since   1.0.0
     */
    public function get_user() {
        if ( ! isset( $this->user ) ) {
            $this->user = new Charitable_User( wp_get_current_user() );
        }

        return $this->user;
    }

    /**
     * Returns the value of a particular key.
     *
     * @param   string $key
     * @param   string $default     Optional. The value that will be used if none is set.
     * @return  mixed
     * @access  public
     * @since   1.0.0
     */
    public function get_user_value( $key, $default = '' ) {
        if ( isset( $_POST[ $key ] ) ) {
            return $_POST[ $key ];
        }

        $user = $this->get_user();
        $value = $default;

        if ( $user ) {
            switch ( $key ) {
                case 'user_description' :
                    $value = $user->description;
                    break;

                default :
                    if ( $user->has_prop( $key ) ) {
                        $value = $user->get( $key );
                    }
            }
        }

        return apply_filters( 'charitable_campaign_submission_user_value', $value, $key, $user );
    }

    public function on_charitable_campaign_submission_save($submitted, $campaign_id, $user_id){
        $campaign = new Charitable_Campaign( $campaign_id );

        // @todo | check linked to Fraternity/Sorority = Kappa Sigma
        if(!has_term( 'kappa-sigma', 'campaign_group', $campaign_id )){
            return;
        }

        $this->create_salesforce_event_for_campaign($campaign);
    }

    public function on_transition_post_status($new_status, $old_status, $post){
        if ( Charitable::CAMPAIGN_POST_TYPE != $post->post_type ) {
            return;
        }

        /* If the status has not changed, there is no need to act. */
        if ( $new_status == $old_status ) {
            return;
        }

        if( $new_status != 'publish'){
            return;
        }

        // @todo | check linked to Fraternity/Sorority = Kappa Sigma
        if(!has_term( 'kappa-sigma', 'campaign_group', $post )){
            return;
        }

        $campaign = new Charitable_Campaign( $post );

        $this->create_salesforce_event_for_campaign($campaign);
    }

    public function create_salesforce_event_for_campaign($campaign){

        $update = false;

        $s_event_id = get_post_meta( $campaign->ID, 'salesforce_event_id', true );
        if(!empty($s_event_id)){
            $update = true;
        }


        $crm_account_id = get_user_meta( $campaign->get_campaign_creator() , 'crm_account_id', true );

        if(empty($crm_account_id)){
            return false; // crm id not found
        }

        $_notes = array(
            'description' => $campaign->get('description'),
            'link' => get_permalink( $campaign->ID ),
        );

        $event_name = $campaign->post_title;

        if(strlen($event_name) > 80){
            $last_space = strrpos(substr($event_name, 0, 80), ' ');
            $event_name = substr($event_name, 0, $last_space);
        }

        $data = [
           'Name' => $event_name,
           'Account__c' => $crm_account_id,
           'EventDate__c' => date('Y-m-d', $campaign->get_end_time() ),
           'Type__c' => 'Greater Cause',
           'Greeks4Good__c' => true,
           'CampaignGoal__c' => $campaign->get_goal(),
           'Notes__c' => implode(PHP_EOL, $_notes),
        ];

        if(!empty($s_event_id) && !is_wp_error( $s_event_id ) ){
            $this->update('Event__c', $s_event_id, $data);
        } else {
            $id = $this->create('Event__c', $data);  #returns id

            if( !empty($id) && !is_wp_error( $id ) ){
                update_post_meta( $campaign->ID, 'salesforce_event_id', $id );
            }

            // var_dump($id); exit();
        }

        return true;
    }

    // public function update_salesforce_agcamount($donation_id, Charitable_Donation_Processor $processor){
    //     $donation = charitable_get_donation( $donation_id );

    //     echo "<pre>";
    //     print_r($donation->get_campaign_donations());
    //     echo "</pre>";
    //     exit();
    // }

    public function update_salesforce_agcamount_on_payment_save($payment_id){
        $donation_id = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );
        $donation = charitable_get_donation( $donation_id );
        
        if(empty($donation))
            return;

        foreach ($donation->get_campaign_donations() as $donation_id => $donation) {
            if(!isset($donation->campaign_id))
                continue;

            $s_event_id = get_post_meta( $donation->campaign_id, 'salesforce_event_id', true );
            if(empty($s_event_id) || is_wp_error( $s_event_id ))
                continue;

            $campaign = new Charitable_Campaign( $donation->campaign_id );
            $data = [
                'AGCAmount__c' => $campaign->get_donated_amount()
            ];

            $this->update('Event__c', $s_event_id, $data);
        }

        // echo $donation_id;
        // echo "<pre>";
        // print_r($donation->get_campaign_donations());
        // echo "</pre>";

        // echo "<pre>";
        // print_r($payment->get_meta());
        // echo "</pre>";
        // exit();
    }

    public function calculate_stripe_fee($amount, $percent){

        $fee = ($amount * (floatval($percent) / 100) ) + 0.3;

        return round($fee, 2);
    }

    // public function calculate_net_amount($data){  

    //     $platform_fee = floatval($data['platform_fee']);
    //     $gross = floatval($data['total']);

    //     $amount_for_stripe = floatval($data['total']);

    //     if( !empty($data['covered_fee']) ){
    //         $gross += floatval($data['covered_fee']);
    //         $amount_for_stripe += $platform_fee;
    //     }

    //     $stripe_fee = $this->calculate_stripe_fee($amount_for_stripe, $data['stripe_percent_fee']);

    //     $_net_amount = $gross - ( $platform_fee + $stripe_fee);

    //     return round($_net_amount, 2);
    // }

    public function create_salesforce_order_id_for_greater_cause($payment_id, $payment){

        if($payment->status != 'publish')
            return;

        // cancel if already created?
        if(get_post_meta( $payment_id, 'salesforce_order_created', true ) == 'yes')
            return;

        // $donation_id = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );
        // $donation = charitable_get_donation( $donation_id );
        // $donor = $donation->get_donor();

        $charge_details = edd_get_payment_meta( $payment_id, 'charge_details' );
        if(empty($charge_details))
            return;

        $payment = new EDD_Payment($payment_id);

        $salesforce_order_ids = get_post_meta( $payment_id, 'salesforce_order_ids', true );
        if(empty($salesforce_order_ids)){
            $salesforce_order_ids = array();
        }

        // echo "<pre>";
        // print_r($charge_details);
        // echo "</pre>";

        if($charge_details['stripe-id'] != $this->greater_cause_stripe_id)
            return;

        $campaign_id = $charge_details['campaign-benefited'];

        $campaign = new Charitable_Campaign( $campaign_id );
        $crm_account_id = get_user_meta( $campaign->get_campaign_creator() , 'crm_account_id', true );
        if(empty($crm_account_id))
            return;

        $net_amount = $charge_details['net-amount'];

        // create rac__Order__c
        $data = [
            'Account__c' => $this->g4g_account_c,
            'RecordTypeId' => $this->g4g_record_type_id,
            'Order_Date__c' => date('Y-m-d', strtotime($payment->date) ),
            'rac__Status__c' => 'Paid in Full',
        ];

        $rac__Order__c = $this->create('rac__Order__c', $data);
        if( is_wp_error( $rac__Order__c ) ){
            // echo $rac__Order__c->get_error_message();
            return;
        }

        // create rac__Order_Item__c
        $data = [
            'rac__Order__c' => $rac__Order__c, // {Order Id / rac__Order__c of the order created that day}
            'Type__c' => 'Order Item', // static
            'rac__Date__c' => date('Y-m-d', strtotime($payment->date) ), // Today
            'rac__Status__c' => 'Paid', // static
            'rac__Description__c' => 'MH Donation', // static
            'Chapter_to_Receive_Soft_Credit__c' => $crm_account_id, // {Account Id related to the campaign}
            // 'RecordTypeId' => $this->g4g_record_type_id, // (this is the “Redable Order Item” record type), commented out since it causes an error Record Type ID: this ID value isn't valid for the user: 012i00000016TZUAA2
            // 'Gateway_Name__c' => '', // static, commented out since it read only
            'Order_Item_Donor_Account__c' => $this->g4g_account_c, // (this is the Account Id for Greeks4Good)
            'rac__SKU__c' => 'a4Ai00000008XcQ', // not sure
            'rac__Quantity__c' => 1, // static
            'rac__Unit_Price__c' => $net_amount, // (Total of donations that day), Gross Transaction including the covered fees (if applicable) minus Stripe fees of 2.2%+.30 applied to the gross amount including covered fees (if applicable) minus G4G fees of 2% on gross amount (does to get applied to the higher amount if donor covers fees)
            'rac__Total_Price__c' => $net_amount, // (Total of donations that day), Gross Transaction including the covered fees (if applicable) minus Stripe fees of 2.2%+.30 applied to the gross amount including covered fees (if applicable) minus G4G fees of 2% on gross amount (does to get applied to the higher amount if donor covers fees)
            'rac__Include_In_Order_Total__c' => TRUE, // static
            'rac__Create_Order_Item_Transactions__c' => TRUE, // static
        ];

        $rac__Order_Item__c = $this->create('rac__Order_Item__c', $data);

        if( is_wp_error( $rac__Order_Item__c ) ){
            // echo $rac__Order_Item__c->get_error_message();
            return;
        }

        // create rac__Transaction__c
        $data = [
            'rac__Order__c' => $rac__Order__c,
            'Date__c' => date('Y-m-d', strtotime($payment->date) ),
            'rac__Status__c' => 'Success',
            'Description__c' => 'MH Donation',
            'rac__Transaction_Type__c' => 'Payment',
            'rac__Amount__c' => $net_amount,
            'rac__Payment_Source__c' => 'Greeks4Good',
            'rac__First_Name__c' => $payment->first_name,
            'rac__Last_Name__c' => $payment->last_name,
            'Email__c' => $payment->email,
        ];

        $rac__Transaction__c = $this->create('rac__Transaction__c', $data);
        if( is_wp_error( $rac__Transaction__c ) ){
            // echo $rac__Transaction__c->get_error_message();
            return;
        }

        $salesforce_order_ids[$campaign_id] = array(
            'rac__Order__c' => $rac__Order__c,
            'rac__Order_Item__c' => $rac__Order_Item__c,
            'rac__Transaction__c' => $rac__Transaction__c,
        );

        // update to meta
        update_post_meta( $payment_id, 'salesforce_order_created', 'yes' );
        update_post_meta( $payment_id, 'salesforce_order_ids', $salesforce_order_ids );

        // echo "<pre>";
        // print_r($salesforce_order_ids);
        // echo "</pre>";
        // exit();
    }

    public function add_salesforce_additional_info($service_hours_data){

        $pp_chapters = PP_Chapters::init();

        $db_chapters = $pp_chapters->db_chapters;

        $chapter = $db_chapters->get($service_hours_data['chapter_id']);

        $name = implode(' ', array($service_hours_data['first_name'], $service_hours_data['last_name'] ) );
        $data = [
            'Student__c' => '', // skip
            'G4Gmembername__c' => $name,
            'InfoType__c' => 'Community Hours',
            'GreaterCauseAmount__c' => '', // skip
            'GreaterCauseCategory__c' => 'Personal',
            'GreaterCauseDate__c' => $service_hours_data['service_date'],
            'GreaterCauseHours__c' => $service_hours_data['service_hours'],
            'GreaterCauseOrganization__c' => $chapter->name,
            'GreaterCauseNotes__c' => $service_hours_data['description'],
        ];

        $AdditionalInfo__c = $this->create('AdditionalInfo__c', $data);

        // var_dump($AdditionalInfo__c);
        // exit();

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

        // echo "<pre>";
        // print_r($service_hours_data);
        // echo "</pre>";
        // exit();
    }

    public function add_salesforce_additional_info_for_user_service_hours($service_hours_data){

        // echo "<pre>";
        // print_r($service_hours_data);
        // echo "</pre>";
        // exit();

        try {
            if(!isset($service_hours_data['campaign_id'])){
                throw new Exception("Empty campaign ID");
            }

            $student_c = '';
            $member_name = isset($service_hours_data['member_name']) ? $service_hours_data['member_name'] : '';
            $member_email = isset($service_hours_data['member_email']) ? sanitize_email( $service_hours_data['member_email'] ) : '';
            
            if(!empty($member_email)){
                $student_c_data = $this->get_student_c_from_email($member_email);

                if( isset($student_c_data['id']) && !empty($student_c_data['id'])){
                    $student_c = $student_c_data['id'];
                    $member_name = ''; // $student_c_data['name']
                    $member_email = ''; // $student_c_data['email']
                }
            }


            $campaign_id = absint( $service_hours_data['campaign_id'] );
            $event__c = get_post_meta( $campaign_id, 'salesforce_event_id', true );
            if(empty($event__c)){
                throw new Exception("Event__c not exists"); // or we should create one?
                // $campaign = new Charitable_Campaign( $campaign_id );
                // $this->create_salesforce_event_for_campaign();
                // $event__c = get_post_meta( $campaign_id, 'salesforce_event_id', true );
            }

            $data = [
                'Student__c' => $student_c,
                'Event__c' => $event__c,
                'G4Gmembername__c' => $member_name,
                'G4Gmemberemail__c' => $member_email,
                'InfoType__c' => 'Community Hours',
                'GreaterCauseAmount__c' => '', // skip
                'GreaterCauseCategory__c' => 'Personal',
                'GreaterCauseDate__c' => $service_hours_data['service_date'],
                'GreaterCauseHours__c' => $service_hours_data['service_hours'],
                'GreaterCauseOrganization__c' => $service_hours_data['title'],
                'GreaterCauseNotes__c' => $service_hours_data['description'],
            ];

            $AdditionalInfo__c = $this->create('AdditionalInfo__c', $data);

            update_option( 'sdsad', $data );
            update_option( 'dsafasfas', $AdditionalInfo__c );


            // var_dump($AdditionalInfo__c);
            // exit();
                
        } catch (Exception $e) {
            // echo $e->getMessage();
        }
        

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit();
    }

    public function get_student_c_from_email($email){
        try {
            $query = "SELECT Id,Email,Name FROM Contact WHERE Email = '".$email."' LIMIT 1";
            $res_contact = $this->query( $query );
            $records = isset($res_contact['records']) ? current($res_contact['records']) : array();
            
            return array(
                'id' => $records['Id'],
                'name' => $records['Name'],
                'email' => $records['Email'],
            );

        } catch (Exception $e) {
            return false;
        }
    }

    public function update_agc_hours_on_event($user_service_hours_ids, $data){

        try {
            $campaign_id = absint( $data['campaign_id'] );
            $event__c = get_post_meta( $campaign_id, 'salesforce_event_id', true );
            if(empty($event__c)){
                throw new Exception("Event__c not exists"); // or we should create one?
                // $campaign = new Charitable_Campaign( $campaign_id );
                // $this->create_salesforce_event_for_campaign();
                // $event__c = get_post_meta( $campaign_id, 'salesforce_event_id', true );
            }

            // $val = $this->get( 'Event__c', $event__c, 'AGCHours__c' );
            // $current_hours = floatval($val['AGCHours__c']);

            // $total_hours_added = g4g()->user_service_hours_db->get_total_hours($user_service_hours_ids);
            // $updated_hours = $current_hours + floatval($total_hours_added);

            // or update from campaign id
            $updated_hours = g4g()->user_service_hours_db->get_campaign_total_hours($campaign_id);


            // update with salesforce api
            $this->update('Event__c', $event__c, array('AGCHours__c' => floatval($updated_hours)) );

        } catch (Exception $e) {
            
        }
    }

    public function create_salesforce_order_id($payment_id, $campaign_id, $donation_data){
        $data = [
            'Account__c' => '0010x00000Dhcwp',
            'RecordTypeId' => '012i00000016TZU',
            'Order_Date__c' => '2015-04-27',
            'rac__Status__c' => 'Paid in Full',
        ];

        $x = $this->create('rac__Order__cxxx', $data);
    }

    public function test(){

        if(!isset($_GET['salesforce']))
            return;

        // var_dump($this->g4g_account_c); exit();


        // $payment = new EDD_Payment(29475);

        // // create order id
        // $data = [
        //     'Account__c' => $this->g4g_account_c,
        //     'RecordTypeId' => $this->g4g_record_type_id,
        //     'Order_Date__c' => date('Y-m-d', strtotime($payment->date) ),
        //     'rac__Status__c' => 'Paid in Full',
        // ];

        // $rac__Order__c = $this->create('rac__Order__c', $data);
        // echo "rac__Order__c: " . $rac__Order__c  . '<br>';

        // $net_amount = 12;
        // $crm_account_id = '001i000000yoDhDAAU';

        // // create order_c
        // $data = [
        //     'rac__Order__c' => $rac__Order__c, // {Order Id / rac__Order__c of the order created that day}
        //     'Type__c' => 'Order Item', // static
        //     'rac__Date__c' => date('Y-m-d', strtotime($payment->date) ), // Today
        //     'rac__Status__c' => 'Paid', // static
        //     'rac__Description__c' => 'MH Donation', // static
        //     'Chapter_to_Receive_Soft_Credit__c' => $crm_account_id, // {Account Id related to the campaign}
        //     // 'RecordTypeId' => $this->g4g_record_type_id, // (this is the “Redable Order Item” record type)
        //     // 'Gateway_Name__c' => '', // static
        //     'Order_Item_Donor_Account__c' => $this->g4g_account_c, // (this is the Account Id for Greeks4Good)
        //     'rac__SKU__c' => 'a4Ai00000008XcQ', // not sure
        //     'rac__Quantity__c' => 1, // static
        //     'rac__Unit_Price__c' => $net_amount, // (Total of donations that day), Gross Transaction including the covered fees (if applicable) minus Stripe fees of 2.2%+.30 applied to the gross amount including covered fees (if applicable) minus G4G fees of 2% on gross amount (does to get applied to the higher amount if donor covers fees)
        //     'rac__Total_Price__c' => $net_amount, // (Total of donations that day), Gross Transaction including the covered fees (if applicable) minus Stripe fees of 2.2%+.30 applied to the gross amount including covered fees (if applicable) minus G4G fees of 2% on gross amount (does to get applied to the higher amount if donor covers fees)
        //     'rac__Include_In_Order_Total__c' => TRUE, // static
        //     'rac__Create_Order_Item_Transactions__c' => TRUE, // static
        // ];

        // $x = $this->create('rac__Order_Item__c', $data);

        // if(is_wp_error( $x )){
        //     echo "<pre>";
        //     print_r($x);
        //     echo "</pre>";
        // } else {
        //     echo "rac__Order_Item__c: " . $x  . '<br>';
        // }

        

        // exit();

        // $data = [
        //    'Account__C' => '001i000000yoDhgAAE',
        //    'EventDate__c' => '2018-03-01',
        // ];

        // $create = $this->create('Event__c', $data);  #returns id

        // var_dump($create);
        // exit();

        // $_SESSION['salesforce']['instance_url'] = 'https://test.salesforce.com';

        // $data = [
        //    'Name' => 'Test Insert',
        //    'Account__C' => '001i000000yoDhgAAE',
        // ];

        // $create = $this->create('Event__c', $data);  #returns id

        // var_dump($create);
        // exit();

        // $query = 'SELECT Name, Account__C, EventDate__c, Type__c, Notes__c, AGCAmount__c FROM Event__c LIMIT 100';

        // sample rac__Order__c = a0k0x000000FBDnAAO
        // sample rac__Order_Item__c = a0Zi000000SNzEyEAL

        $data = [
            'Account__c' => '0010x00000Dhcwp',
            'RecordTypeId' => '012i00000016TZU',
            'Order_Date__c' => '2015-04-27',
            'rac__Status__c' => 'Paid in Full',
        ];

        // $x = $this->create('rac__Order__cxxx', $data);

        // if(is_wp_error( $x )){
        //     echo $x->get_error_message();
        // } else {
        //     var_dump($x);
        // }
        

        // exit();
        

        $data = [
            'rac__Order__c' => 'a0k0x000000FBXNAA4', // {Order Id / rac__Order__c of the order created that day}
            'Type__c' => 'Order Item', // static
            'rac__Date__c' => '2015-04-27', // Today
            'rac__Status__c' => 'Paid', // static
            'rac__Description__c' => 'MH Donation', // static
            'Chapter_to_Receive_Soft_Credit__c' => '001i000000yoDhDAAU', // {Account Id related to the campaign}
            'RecordTypeId' => $this->g4g_record_type_id, // (this is the “Redable Order Item” record type)
            // 'Gateway_Name__c' => '', // static
            'Order_Item_Donor_Account__c' => '0010x00000Dhcwp', // (this is the Account Id for Greeks4Good)
            'rac__SKU__c' => 'a4Ai00000008XcQ', // not sure
            'rac__Quantity__c' => 1, // static
            'rac__Unit_Price__c' => 18, // (Total of donations that day), Gross Transaction including the covered fees (if applicable) minus Stripe fees of 2.2%+.30 applied to the gross amount including covered fees (if applicable) minus G4G fees of 2% on gross amount (does to get applied to the higher amount if donor covers fees)
            'rac__Total_Price__c' => 18, // (Total of donations that day), Gross Transaction including the covered fees (if applicable) minus Stripe fees of 2.2%+.30 applied to the gross amount including covered fees (if applicable) minus G4G fees of 2% on gross amount (does to get applied to the higher amount if donor covers fees)
            'rac__Include_In_Order_Total__c' => TRUE, // static
            'rac__Create_Order_Item_Transactions__c' => TRUE, // static
        ];

        // $x = $this->create('rac__Order_Item__c', $data);


        $data = [
            'Date__c' => '2015-04-27',
            'rac__Status__c' => 'Success',
            'Description__c' => 'MH Donation',
            'rac__Transaction_Type__c' => 'Payment',
            'rac__Amount__c' => 12,
            'rac__Payment_Source__c' => 'Greeks4Good',
            'rac__First_Name__c' => 'dasdasd',
            'rac__Last_Name__c' => 'ssss',
            'Email__c' => 'donor@email.com',
            'rac__Order__c' => 'a0k0x000000FBDnAAO',
        ];

        // $x = $this->create('rac__Transaction__c', $data);

        // $x = $this->get( 'Event__c', 'a0Ei000000ctqC5EAI', 'AGCHours__c' );
        // $x = $this->get('rac__Order__c', 'a0k0x000000FBDnAAO'); // a0k0x000000FBDnAAO
        // $x = $this->get('rac__Order_Item__c', 'a0Z0x000000BI2CEAW'); // a0Zi000000SNzEyEAL
        // $x = $this->get('rac__Transaction__c'); // a0Zi000000SNzEyEAL
        $x = $this->get('AdditionalInfo__c', 'a00i000000bZJuZ'); // a0Zi000000SNzEyEAL
        // $x = $this->get('Contact', '003i000001ti0vKAAQ'); // a0Zi000000SNzEyEAL
        
        echo "<pre>";
        print_r($x);
        echo "</pre>";
        exit();

        // $query = "SELECT Id,Email,Name FROM Contact WHERE Email = 'bbaird@baird.comx' LIMIT 1";
        // $res_contact = $this->query( $query );
        // $records = isset($res_contact['records']) ? current($res_contact['records']) : false;
        // echo "<pre>";
        // print_r($this->get_student_c_from_email('bbaird@baird.com') );
        // echo "</pre>";
        // echo "<br>";
        // exit();
    }

    private function authenticate(){

        $client = new Client();

        $request = $client->request('post', $this->endpoint . 'services/oauth2/token', ['form_params' => $this->options]);
        $response = json_decode($request->getBody(), true);

        if ($response) {
            $this->access_token = $response['access_token'];
            $this->instance_url = $response['instance_url'];

            $_SESSION['salesforce'] = $response;
        } else {
            return new WP_Error('failed_to_authenticate',  __( 'Failed to authenticate with salesforce api', 'philanthropy' ) );
        }

        return true;
    }

    public function query($query) {

        if(empty($this->instance_url) || empty($this->access_token)){
            $this->authenticate();
        }

        $url = "$this->instance_url/services/data/v34.0/query";

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ],
            'query' => [
                'q' => $query
            ]
        ]);

        return json_decode($request->getBody(), true);
    }

    public function get($object, $object_id = null, $fields = null) {

        if(empty($this->instance_url) || empty($this->access_token)){
            $this->authenticate();
        }

        $url = "$this->instance_url/services/data/v34.0/sobjects/$object/";

        if(!is_null($object_id)){
            $url .= $object_id;
        }

        if(!is_null($fields)){
            if(!is_array($fields)){
                $fields = explode(',', $fields);
            }

            $url .= '?fields=' . implode(',', $fields);
        }

        $client = new Client();
        $request = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token"
            ],
            // 'query' => [
            //     'q' => $query
            // ]
        ]);

        return json_decode($request->getBody(), true);
    }

    public function create($object, array $data) {

        if(empty($this->instance_url) || empty($this->access_token)){
            $this->authenticate();
        }

        $url = "$this->instance_url/services/data/v34.0/sobjects/$object/";

        $client = new Client();

        try {
            $request = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => "OAuth $this->access_token",
                    'Content-type' => 'application/json'
                ],
                'json' => $data
            ]);

            $status = $request->getStatusCode();

            if ($status != 201) {
                throw new Exception( $request->getReasonPhrase() );
            }

            $response = json_decode($request->getBody(), true);
            if(!isset($response['id'])){
                throw new Exception( 'ID not found' );
            }

            return $response['id'];

        } catch (Exception $e) {
            return new WP_Error('salesforce_api_failed', $e->getMessage() );
        }

        return false;

    }

    public function update($object, $id, array $data) {

        if(empty($this->instance_url) || empty($this->access_token)){
            $this->authenticate();
        }

        if(is_wp_error( $id )){
            return false;
        }

        $url = "$this->instance_url/services/data/v34.0/sobjects/$object/$id";

        $client = new Client();

        $request = $client->request('PATCH', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        return $status;
    }

    public function delete($object, $id) {

        if(empty($this->instance_url) || empty($this->access_token)){
            $this->authenticate();
        }

        $url = "$this->instance_url/services/data/v34.0/sobjects/$object/$id";

        $client = new Client();
        $request = $client->request('DELETE', $url, [
            'headers' => [
                'Authorization' => "OAuth $this->access_token",
            ]
        ]);

        $status = $request->getStatusCode();

        if ($status != 204) {
            die("Error: call to URL $url failed with status $status, response: " . $request->getReasonPhrase());
        }

        return true;
    }

    public function includes(){

    }

}

G4G_Salesforce::init();