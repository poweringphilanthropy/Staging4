<?php
/**
 * PP_Campaign Class.
 *
 * @class       PP_Campaign
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Campaign class.
 */
class PP_Campaign {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Campaign();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'charitable_campaign_get_meta_value', array($this, 'change_campaign_end_date'), 10, 4 );
        add_action( 'charitable_campaign_content_after', array($this, 'add_tax_deductible_information'), 10, 1 );
        add_action( 'edd_purchase_form_top', array($this, 'add_tax_deductible_information_on_checkout'), 9 );
		add_action( 'template_redirect', array( $this, 'maybe_redirect_campaign_submission_template' ), 1 );
    }

    /**
     * Use 23:59:59 for campaign end date
     * @param  [type]              $value     [description]
     * @param  [type]              $meta_name [description]
     * @param  [type]              $single    [description]
     * @param  Charitable_Campaign $campaign  [description]
     * @return [type]                         [description]
     */
    public function change_campaign_end_date($meta_value, $meta_name, $single, Charitable_Campaign $campaign){

        if( $meta_name != '_campaign_end_date' ){
            return $meta_value;
        }

        if(empty($meta_value)){
            return $meta_value;
        }

        if($single){
            $date = new DateTime($meta_value);
            $date->setTime(23, 59, 59);
            $meta_value = $date->format('Y-m-d H:i:s');
        } else {
            foreach ($meta_value as $key => $value) {
                $date = new DateTime($value);
                $date->setTime(23, 59, 59);
                $meta_value[$key] = $date->format('Y-m-d H:i:s');
            }
        }

        return $meta_value;
    }

    public function add_tax_deductible_information($campaign){
        global $post;
		$final_id = $campaign->ID;
		if($post->post_parent >0 ){
			$final_id = $post->post_parent;
		}
        if(pp_is_campaign_tax_deductible($final_id)){
			$connected_stripe_id = get_post_meta( $final_id, '_campaign_connected_stripe_id', true );
			if(empty($connected_stripe_id)){
				$message = "Donations to this campaign are not tax-deductible";
			}else{
				$message = "Donations to this campaign are 100% tax-deductible";
			}
        } else {
            $message = "Donations to this campaign are not tax-deductible";
        }
        echo '<div class="tax-deductible-info">';
        echo sprintf('<p><strong>PLEASE NOTE:</strong> %s</p>', strtoupper($message));
        echo '</div>';
    }

    public function add_tax_deductible_information_on_checkout(){

        if ( class_exists( 'Charitable_EDD_Cart' ) ) :
            $cart      = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );
            $campaigns = $cart->get_benefits_by_campaign();
        else :
            $campaigns = array();
        endif;

        if(class_exists('PP_Team_Fundraising')){
            $campaigns = PP_Team_Fundraising::remove_parent_from_campaign_benefits($campaigns);
        }

        if(empty($campaigns))
            return;

        reset($campaigns);
        $campaign_id = key($campaigns);

         $final_id = $campaign_id;
		$postt = get_post( $campaign_id );
		if($postt->post_parent >0 ){
			$final_id = $postt->post_parent;
		}
        if(pp_is_campaign_tax_deductible($final_id)){
            $connected_stripe_id = get_post_meta( $final_id, '_campaign_connected_stripe_id', true );
			if(empty($connected_stripe_id)){
				$message = "Donations to this campaign are not tax-deductible";
			}else{
				$message = "Donations to this campaign are 100% tax-deductible";
			}
        } else {
            $message = "Donations to this campaign are not tax-deductible";
        }
        echo '<fieldset style="font-size: 20px;">';
        echo sprintf('<p><strong>PLEASE NOTE:</strong> %s</p>', strtoupper($message));
        echo '</fieldset>';
    }

    public function includes(){

    }
	  /**
     * Redirect to the login page if we are on the campaign submission page but we're not logged in.  
     *
     * @return  void|false      Void when redirected. False when not redirected.
     * @access  public
     * @since   version
     */
    public function maybe_redirect_campaign_submission_template() {
		/** 
		 * Only redirect if three things are true: 
		 * 1. The user is not logged in.
		 * 2. We require the user to be logged in to submit a campaign.
		 * 3. The user is currently trying to access the campaign submission page.
		 */
        if ( ! is_user_logged_in() 
			&& charitable_get_option( 'require_user_account_for_campaign_submission', 1 )
			&& charitable_is_page( 'campaign_submission_page' ) ) {            

			$url = charitable_get_permalink( 'login_page' );
			$url = add_query_arg( array( 'redirect_to' => charitable_get_permalink( 'campaign_submission_page' ) ), $url );
			$url = esc_url_raw( apply_filters( 'charitable_campaign_submission_logged_out_redirect', $url ) );

			wp_safe_redirect( $url );

			exit();
		}

    	return false;
    }

}

PP_Campaign::init();