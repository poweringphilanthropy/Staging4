<?php
/**
 * A helper class to retrieve Campaign Donation Reports.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Campaign_Donation_Reports' ) ) :

	/**
	 * PP_Campaign_Donation_Reports
	 *
	 * @since 	1.4.0
	 */
	class PP_Campaign_Donation_Reports extends Charitable_Query {

		private $campaign_ids;

		private $donations;

		private $data;

		private $currency_helper;

		/**
		 * Create class object.
		 *
		 * @param 	array $args Arguments used in query.
		 * @access  public
		 * @since   1.4.0
		 */
		public function __construct( $campaign_ids ) {

			// TODO | Maybe merge with child campaigns
			$this->campaign_ids = $campaign_ids;

			$this->currency_helper = charitable_get_currency_helper();
		}

		private function merge_with_childs($campaign_ids){

			if(empty($campaign_ids))
				return $campaign_ids;

			$merged_ids = array();
			foreach ($campaign_ids as $campaign_id) {
				$with_children = pp_get_merged_team_campaign_ids($campaign_id);
				$merged_ids = array_merge( $merged_ids, $with_children );
			}

			return array_unique($merged_ids);
		}

		public function get_campaign_ids($with_child = false){

			if($with_child){
				return $this->merge_with_childs($this->campaign_ids);
			}

			return $this->campaign_ids;
		}

		public function get_donations($with_child = true){

			if(is_null($this->donations)){

				$query_args = array(
			        'campaign_id' => $this->get_campaign_ids($with_child),
			        'status' => 'charitable-completed',
			    );

			    $this->donations = charitable_get_table( 'campaign_donations' )->get_donations_report( $query_args );

			}

		    return $this->donations;
		}

		public function set_data($data){
			$this->data = $data;
		}

		public function get_data(){

			// TODO | Maybe use cache
			if(is_null($this->data)){

				if ( ! charitable_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
					set_time_limit( 0 );
				}

				$donations = $this->get_donations();
				$campaign_benefactor_downloads = $this->get_downloads_benefactor();

				$donation_ids = wp_list_pluck( $donations, 'donation_id' );

				$payments = array();
    
        		if(!empty($donation_ids)):
	    
		        $query_args = array('number'   => -1); // all data
		        $query_args['meta_query'] = array(
		            array(
		                'key' => 'charitable_donation_from_edd_payment',
		                'value' => $donation_ids,
		                'compare' => 'IN',
		            ),
		        );

		        $payments = edd_get_payments( $query_args );
		        
		        endif;


		        $report_data = array();

		        if(!empty($payments)):
		        foreach ($payments as $payment) {

		            $edd_payment    = new EDD_Payment( $payment->ID );
		            
		            $downloads      = edd_get_payment_meta_cart_details( $payment->ID );
		            $fees           = edd_get_payment_fees( $payment->ID, 'item' );
		            $user_info      = edd_get_payment_meta_user_info( $payment->ID );
		            $payment_meta   = edd_get_payment_meta( $payment->ID );

		            $donation_id    = get_post_meta( $payment->ID, 'charitable_donation_from_edd_payment', true );
		            if ( ! $donation_id || ( get_post_status ( $donation_id ) != 'charitable-completed' ) ) {
		                continue;
		            }
		            $donation       = charitable_get_donation( $donation_id );
		            $donor = $donation->get_donor();

		            $default_data = array(
		            	'campaign_id' => 0,
		            	'campaign_url' => 0,
		                'campaign_name' => '',
		                'type' => '',
		                'unique_key' => '',
		                'donation_id' => $donation_id,
		                'email' => $payment_meta['email'],
		                'first_name' => isset( $user_info['first_name'] ) ? $user_info['first_name'] : '',
		                'last_name' => isset( $user_info['last_name'] ) ? $user_info['last_name'] : '',
		                'post_date' => $donation->post_date,
		                'post_content' => $donation->post_content,
		                'post_status' => $donation->post_status,
		                'payment_status' => $edd_payment->status,
		                'date' => mysql2date( 'l, F j, Y', $donation->post_date ),
		                'time' => mysql2date( 'H:i A', $donation->post_date ),
		                'chapter' => (isset($payment_meta['chapter']) && !empty($payment_meta['chapter'])) ? $payment_meta['chapter'] : 'na',
		                'referral' => (isset($payment_meta['referral']) && !empty($payment_meta['referral'])) ? pp_get_referrer_name($payment_meta['referral'], false) : 'na',
		                'payment_gateway' => $edd_payment->gateway,
		                'amount' => 0,
		                'purchase_detail' => '',
		                'shipping' => 0,
		                'ticket_holder' => '-',
		                'qty' => 1,
		                'type' => '',
		            );

		            /**
		             * DONATION
		             */
		            if ( $fees ) {
		                foreach ( $fees as $key => $fee ) {
		                    if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
		                        continue;
		                    }

		                    if(!isset($fee['campaign_id']))
		                        continue;

		                    $campaign_id = absint( $fee['campaign_id'] );

		                    $title = $this->get_campaign_title($campaign_id);

		                    $donation_data = array(
				                'campaign_id' => $campaign_id,
		                    	'campaign_url' => get_permalink( $campaign_id ),
				                'campaign_name' => $title,
		                        'type' => 'donation',
		                        'amount' => $this->format_amount( $fee['amount'], 0 ),
		                        'purchase_detail' => sprintf(__('Donation for %s', 'pp-toolkit'), $title ),
		                    );

		                    /**
		                     * Display as Mobile donation if donation coming from rest api
		                     * @var [type]
		                     */
		                    if($edd_payment->gateway == 'rest-api'){
		                        $default_data['purchase_detail'] = sprintf(__('Mobile Donation for %s', 'pp-toolkit'), $title );
		                    }

		                    $parsed = wp_parse_args( $donation_data, $default_data );
		                    $report_data[] = $this->maybe_use_parent_data($parsed);
		                }
		            }

		            /**
		             * DOWNLOADS
		             */
		            if ( $downloads ) {
		                foreach ( $downloads as $key => $download ) {
		                    // Download ID
		                    $id = isset($download['id']) ? $download['id'] : $download;

		                    // @todo | uncomment after fix benefactor removed from duplicate
		                    // if( empty($id) || !in_array($id, $campaign_benefactor_downloads))
		                    //     continue;

		                    // unique key for grouping
		                    $download_unique_key = $id;

		                    $download_description = get_the_title( $id );
		                    $options = ( isset( $download['item_number'] ) && isset( $download['item_number']['options'] ) ) ? $download['item_number']['options'] : array();

		                    if ( !empty($options) ) {
		                        $price_id   = isset( $options['price_id'] ) ? $options['price_id'] : null;
		                        if ( edd_has_variable_prices( $id ) && isset( $price_id ) ) {
		                            // append unique key
		                            $download_unique_key .= '-' . $price_id;

		                            $download_description .= ' - ' . edd_get_price_option_name( $id, $price_id, $payment->ID );
		                        }
		                    }

		                    $campaign_id = $this->get_campaign_id_by_donation( $donation_id );

		                    $download_data = array(
		                    	'campaign_id' => $campaign_id,
		                    	'campaign_url' => get_permalink( $campaign_id ),
				                'campaign_name' => $this->get_campaign_title($campaign_id),
		                        'amount' => $this->format_amount($download['subtotal'], 0),
		                        'purchase_detail' => $download_description,
		                        'qty' => $download['quantity'],
		                        'unique_key' => $download_unique_key,
		                        'options' => $options
		                    );

		                    // ticket holder
		                    if (isset($options['tribe-tickets-meta']) 
		                        && is_array($options['tribe-tickets-meta']) 
		                        && !empty($options['tribe-tickets-meta']))
		                    {
		                        $_ticket_holder = wp_list_pluck( $options['tribe-tickets-meta'], 'ticket-holder-name' );
		                        $download_data['ticket_holder'] = implode(', ', array_filter($_ticket_holder) );
		                    }

		                    $shiping_cost = 0;
		                    if($download['fees']):
		                    foreach ( $download[ 'fees' ] as $fee_id => $fee ) {
		                        if ( false === strpos( $fee_id, 'simple_shipping' ) ) {
		                            continue;
		                        }

		                        $download_data['shipping'] = (isset($fee['amount'])) ? $this->format_amount($fee['amount'], 0) : 0;
		                        break;
		                    }
		                    endif;

		                    // ticket
		                    if (has_term('ticket', 'download_category', $id )) {
		                        $download_data['type'] = 'ticket';
		                    } else {
		                        $download_data['type'] = 'merchandise';
		                    }

		                    $parsed = wp_parse_args( $download_data, $default_data );
		                    $report_data[] = $this->maybe_use_parent_data($parsed);
		                }
		            }
		        }

		    	endif; // !empty($payments)

		        $this->data = $report_data;
			}

			return $this->data;

		}

		private function maybe_use_parent_data($data){
			$parent_id = wp_get_post_parent_id( absint( $data['campaign_id'] ) );
			if(!empty($parent_id)){
				$data['campaign_id'] = $parent_id;
				$data['campaign_url'] = get_permalink( $parent_id );
				$data['campaign_name'] = $this->get_campaign_title($parent_id);
			}

			return $data;
		}

		private function format_amount($amount){

			return floatval($amount);
		}

		public function get_all_donation_data($type = ''){

			switch ($type) {
				case 'campaigns':
					$report_data = $this->get_data_by('campaign_id');
					break;
				
				case 'fundraisers':
					$report_data = $this->get_data_by('referral');
					break;
				
				case 'donations':
					$report_data = $this->get_data_by('type', 'donation');
					break;
				
				case 'tickets':
					$report_data = $this->get_data_by('type', 'ticket');
					break;
				
				case 'merchandises':
					$report_data = $this->get_data_by('type', 'merchandise');
					break;
				
				default:
					$report_data = $this->get_data();
					break;
			}

			usort($report_data, array(__CLASS__, "sort_by_amount"));

			return $report_data;
		}

		public function get_data_by($key, $value = false){

			$results = array();

			foreach ($this->get_data() as $data) {

		    	$_k = $data[$key];

		    	if( ($value !== false) && ($_k != $value) ){
		    		continue;
		    	}

		    	$results[] = $data;
		    }

		    return $results;
		}

		public function get_campaign_data(){
			$report_data = $this->get_data();
			return $this->group_data_by($report_data, 'campaign_id');
		}

		public function get_referral_data(){
			$report_data = $this->get_data();
			return $this->group_data_by($report_data, 'referral');
		}

		public function get_donation_data(){

			$report_data = $this->get_data();

			return $this->group_data_by($report_data, 'type', 'donation');
		}

		public function get_merchandise_data(){

			$report_data = $this->get_data();

			$merchandise = $this->group_data_by($report_data, 'type', 'merchandise');

			$data = $merchandise['data'];

			$unique_keys = array_unique( wp_list_pluck( $data, 'campaign_id' ) );
			
			$details = array();
			foreach ($unique_keys as $unique_key ) {

				$details[$unique_key] = $this->group_data_by($data, 'campaign_id', $unique_key);
			
			}

			// unset($merchandise['data']);

			$merchandise['data'] = $details;

			return $merchandise;
		}

		public function get_ticket_data(){
			$report_data = $this->get_data();

			$tickets = $this->group_data_by($report_data, 'type', 'ticket');

			$data = $tickets['data'];

			$unique_keys = array_unique( wp_list_pluck( $data, 'campaign_id' ) );
			
			$details = array();
			foreach ($unique_keys as $unique_key ) {

				$details[$unique_key] = $this->group_data_by($data, 'campaign_id', $unique_key);
			
			}

			// unset($tickets['data']);

			$tickets['data'] = $details;

			return $tickets;
		}

		private function get_data_with_total($data, $item_name = ''){

			$qtys = array_column( $data, 'qty' );
			$amounts = array_column($data, 'amount');

			if(!empty($amounts)){
				usort($data, array(__CLASS__, "sort_by_amount"));
			}

			$formatted = array(
				'item_name' => $item_name,
				'count' => count($data),
				'qty' => !empty($qtys) ? array_sum( array_map('intval', $qtys) ) : 0,
				'amount' => !empty($amounts) ? $this->format_amount( array_sum( array_map('floatval', $amounts) ), 0) : 0,
				'data' => $data,
			);

			$_first = current($data);
			if(isset($_first['campaign_id'])){
				$campaign_id = $_first['campaign_id'];
				$formatted['campaign_id'] = $campaign_id;
				$formatted['campaign_name'] = $this->get_campaign_title( $campaign_id );
				$formatted['campaign_url'] = get_permalink( $campaign_id );
			}

			return $formatted;
		}

		private function group_data_by($data, $key, $value = false ){
			$_results = array();

		    foreach ($data as $element) {

		    	$_k = $element[$key];

		    	if( ($value !== false) && ($_k != $value) ){
		    		continue;
		    	}

		    	if($value === false){
		    		$_results[$_k][] = $element;
		    	} else {
		    		$_results[] = $element;
		    	}
		    }


	    	if($value === false){
		    	$r_child = array();
		    	foreach ($_results as $k_res => $res) {
		    		$r_child[] = $this->get_data_with_total($res, apply_filters( 'pp_campaign_report_item_name', $k_res, $key, $value ) );
		    	}
		    	$results = $this->get_data_with_total($r_child, apply_filters( 'pp_campaign_report_item_name', $key, $key, $value ) );
		    } else {
	    		$results = $this->get_data_with_total($_results, apply_filters( 'pp_campaign_report_item_name', $value, $key, $value ) );
		    }

		    return $results;
		}

		public function get_campaign_title($campaign_id){
			return html_entity_decode(get_the_title( $campaign_id ));
		}

		public function get_campaign_id_by_donation($donation_id){
			$donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
		
			if(empty($donation_log))
				return 0;

			$first = current($donation_log);

			// echo "<pre>";
			// print_r($first);
			// echo "</pre>";

			return isset($first['campaign_id']) ? absint( $first['campaign_id'] ) : 0;
		}

		public function get_downloads_benefactor(){

			$download_ids = array();

			$extension = 'charitable-edd';
			foreach ( $this->get_donations() as $donation) {
				$benefactors = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension($donation->campaign_id, $extension );

				if(!empty($benefactors)){
	                $download_ids = array_merge($download_ids, wp_list_pluck( $benefactors, 'edd_download_id' ) );
	            }
			}

			return $download_ids;
		}

	    public static function sort_by_amount($a, $b) {

	    	if(is_array($a) && isset( $a["amount"] )){
	            return $b["amount"] - $a["amount"];
	        } 

	        if(is_object($a) && isset($a->amount)){
	            return $b->amount - $a->amount;
	        }

	        return 0;
	    }

	    public static function sort_by_last_name($a, $b) {

	    	if(is_array($a)){
	            return strcmp($a["last_name"], $b["last_name"]);
	        } 

	        if(is_object($a)){
	            return strcmp($a->last_name, $b->last_name);
	        }
	    }
	}

endif;
