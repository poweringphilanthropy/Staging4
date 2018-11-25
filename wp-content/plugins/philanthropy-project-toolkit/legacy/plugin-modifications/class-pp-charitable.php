<?php
/**
 * PP_Charitable Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Charitable
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Charitable class.
 */
class PP_Charitable {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Charitable();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'charitable_start', array($this, 'remove_hooks') );

        add_filter( 'charitable_is_page_registration_page', array( $this, 'is_registration_page' ) );
        add_filter( 'charitable_donors_widget_donor_query_args', array( $this, 'filter_donors_widget_query_args' ) );

        add_filter( 'charitable_form_field_template', array($this, 'pp_use_our_form_field_template'), 11, 3 );

        /**
         * Frontend
         */
        add_filter( 'charitable_campaign_show_achievement_status_tag', '__return_false' );
        add_filter( 'charitable_campaign_finished_notice', array($this, 'pp_charitable_campaign_finished_notice'), 10, 2 );
        // add_filter( 'charitable_locate_template', array($this, 'change_modal_template'), 10, 2 );

        add_action( 'charitable_user_event_summary_before', array($this, 'pp_charitable_my_tribe_events_render_event_thumbnail'), 10, 2 );
        add_action( 'charitable_user_event_summary', array($this, 'pp_charitable_my_tribe_events_render_event_summary'), 10, 2 );
        add_action( 'charitable_user_event_summary_after', array($this, 'pp_charitable_my_tribe_events_render_event_actions'), 10, 2 );       
        add_action( 'charitable_user_product_summary_before', array($this, 'pp_charitable_my_edd_products_render_product_thumbnail'), 10, 2 );
        add_action( 'charitable_user_product_summary', array($this, 'pp_charitable_my_edd_products_render_product_summary'), 10, 2 );
        //add_action( 'charitable_user_campaign_summary_after', array($this, 'pp_charitable_my_edd_products_render_product_actions'), 10, 2 );     

        /* Impact target field */
        add_action( 'charitable_campaign_summary_after', 'reach_template_campaign_impact_summary', 8 );

        add_filter( 'charitable_edd_donation_form_fields', array($this, 'pp_charitable_donation_form_fields'), 10, 2 );
        add_action( 'charitable_before_process_donation_form', array($this, 'pp_donation_no_redirect_to_checkout'), 10, 2 );
        add_action( 'charitable_before_process_donation_amount_form', array($this, 'pp_donation_no_redirect_to_checkout'), 10, 2 );
        
        /**
         * User related
         */
        add_filter( 'charitable_user_registration_fields', array($this, 'pp_charitable_user_registration_fields') );
        add_filter( 'charitable_user_fields', array($this, 'pp_charitable_user_fields'), 20, 2 );
        add_filter( 'charitable_user_social_fields', array($this, 'pp_remove_charitable_user_social_fields'), 20 );
        // add_filter( 'charitable_user_profile_fields', array($this, 'pp_remove_address_social_sections') );
        add_filter( 'charitable_user_address_fields', array($this, 'pp_charitable_user_address_fields') );
        add_action( 'charitable_profile_updated', array($this, 'notice_success_profile_updated'), 10, 3 );

        add_action( 'template_redirect', array($this, 'maybe_add_notices') );

        // maybe need to clear related merchandise and event on delete campaign (still buggy)
        // add_action( 'before_delete_post', array($this, 'delete_related_products'), 10, 1 );
    }

    public function remove_hooks(){

        remove_filter( 'authenticate', array( Charitable_User_Management::get_instance(), 'maybe_redirect_at_authenticate' ), 101);
        add_filter( 'authenticate', array( $this, 'maybe_redirect_at_authenticate' ), 100, 2 );
    }

    /**
     * Use our templates for our custom field types.
     *
     * @param   Charitable_Template|false $template False unless another plugin/theme has already set this.
     * @param   array $field
     * @param   Charitable_Form $form 
     * @return  PP_Toolkit_Template|false 
     * @since   1.0.0
     */
    public function pp_use_our_form_field_template( $template, $field, Charitable_Form $form ) {
        if ( in_array( $field[ 'type' ], pp_get_override_charitable_form_field_templates() ) ) {
			$objj = new Charitable_Public_Form_View($form);
            $template = new PP_Toolkit_Template( $objj->get_template_name( $field ), false );
        }        

        return $template;
    }

    /**
     * Customize the finished campaign notice. 
     *
     * @param   string $message
     * @param   Charitable_Campaign $campaign
     * @return  string
     * @since   1.0.3
     */
    public function pp_charitable_campaign_finished_notice( $message, $campaign ) {
        if ( $campaign->has_goal() && $campaign->has_achieved_goal() ) {
            $message = __( 'This campaign successfully reached its funding goal and ended %s ago', 'pp-toolkit' );
        }
        else {
            $message = __( 'This campaign ended %s ago', 'pp-toolkit' );
        }

        return sprintf( $message, '<span class="time-ago">' . human_time_diff( $campaign->get_end_time() ) . '</span>' );
    } 

    public function change_modal_template($template, $template_names){
        $need_our_templates = array( 
            'campaign/donate-modal.php', 
            'campaign-loop/donate-modal.php',
            // 'donation-form/form-donation.php',
        );

        $found_modal_template = array_intersect($need_our_templates, $template_names);
        if(count($found_modal_template) > 0){
            $template = pp_toolkit()->directory_path . 'templates/' . $found_modal_template[0];
        }
        
        return $template;
    }

    /**
     * Display the event thumbnail template. 
     *
     * @param   WP_Post         $event
     * @param   Charitable_User $user
     * @return  void
     * @since   1.0.0
     */
    public function pp_charitable_my_tribe_events_render_event_thumbnail( $event, $user ) {
        $template = new PP_Toolkit_Template( 'shortcodes/my-events/event-thumbnail.php', false );
        $template->set_view_args( array( 
            'event' => $event, 
            'user' => $user 
        ) );
        $template->render();
    }

    /**
     * Display the event summary template. 
     *
     * @param   WP_Post         $event
     * @param   Charitable_User $user
     * @return  void
     * @since   1.0.0
     */
    public function pp_charitable_my_tribe_events_render_event_summary( $event, $user ) {
        $template = new PP_Toolkit_Template( 'shortcodes/my-events/event-summary.php', false );
        $template->set_view_args( array( 
            'event' => $event, 
            'user' => $user 
        ) );
        $template->render();
    }

    /**
     * Display the event actions template. 
     *
     * @param   WP_Post             $event
     * @param   Charitable_User     $user
     * @return  void
     * @since   1.0.0
     */
    public function pp_charitable_my_tribe_events_render_event_actions( $event, $user ) {
        $template = new PP_Toolkit_Template( 'shortcodes/my-events/event-actions.php', false );
        $template->set_view_args( array( 
            'event' => $event, 
            'user' => $user 
        ) );
        $template->render();
    }

    /**
     * Display the product thumbnail template. 
     *
     * @param   WP_Post         $product
     * @param   Charitable_User $user
     * @return  void
     * @since   1.0.0
     */
    public function pp_charitable_my_edd_products_render_product_thumbnail( $product, $user ) {
        $template = new PP_Toolkit_Template( 'shortcodes/my-products/product-thumbnail.php', false );
        $template->set_view_args( array( 
            'product' => $product, 
            'user' => $user 
        ) );
        $template->render();
    }

    /**
     * Display the product summary template. 
     *
     * @param   WP_Post         $product
     * @param   Charitable_User $user
     * @return  void
     * @since   1.0.0
     */
    public function pp_charitable_my_edd_products_render_product_summary( $product, $user ) {
        $template = new PP_Toolkit_Template( 'shortcodes/my-products/product-summary.php', false );
        $template->set_view_args( array( 
            'product' => $product, 
            'user' => $user 
        ) );
        $template->render();
    }

    /**
     * Display the product actions template. 
     *
     * @param   WP_Post             $product
     * @param   Charitable_User     $user
     * @return  void
     * @since   1.0.0
     */
    public function pp_charitable_my_edd_products_render_product_actions( $product, $user ) {
        die('here');
        $template = new PP_Toolkit_Template( 'shortcodes/my-products/product-actions.php', false );
        $template->set_view_args( array( 
            'product' => $product, 
            'user' => $user 
        ) );
        $template->render();
    }

    /**
     * Checks whether the current request is for the campaign editing page. 
     *
     * This is used when you call charitable_is_page( 'registration_page' ). 
     * In general, you should use charitable_is_page() instead since it will
     * take into account any filtering by plugins/themes.
     *
     * @see     charitable_is_page
     * @return  boolean
     * @since   1.0.0
     */
    public function is_registration_page( $r= false ) {
        return charitable_is_page( 'login_page' );
    }

    /**
     * Modify the donation form slightly. 
     *
     * @param   array[] $fields
     * @param   Charitable_EDD_Donation_Form $form
     * @return  array[] $fields
     * @since   1.0.2
     */
    public function pp_charitable_donation_form_fields( $fields, Charitable_EDD_Donation_Form $form ) {
        if ( isset( $fields[ 'download_fields' ] ) ) {
            unset( $fields[ 'download_fields' ][ 'legend' ] );
        }    

        return $fields;
    }

    /**
     * Fetch all donors for a donation, including duplicate donations. 
     *
     * @param   mixed[] $args
     * @return  mixed[]
     * @access  public
     * @since   1.0.0
     */
    public function filter_donors_widget_query_args( $args ) {
        $args[ 'distinct' ] = false;
        return $args;
    }    

    public function pp_donation_no_redirect_to_checkout( Charitable_Donation_Processor $processor, Charitable_Donation_Form_Interface $form ) {

        if ( ! $form->validate_submission() ) {
            return false;
        }

        //Compare the cart total against the donation amount field. Any excess in the donation amount field is added to the checkout as a fee.
         
        $cart_total = pp_add_downloads_to_cart();
        $amount = Charitable_Donation_Form::get_donation_amount();
        $campaign_id = $processor->get_campaign()->ID;

        if ( $amount ) {
            Charitable_EDD_Cart::add_donation_fee_to_cart( $campaign_id, $amount );
        }

        // Redirect to the checkout.
        //wp_redirect( edd_get_checkout_uri() );
        wp_redirect( get_permalink( $campaign_id ).'"?amt='.$amount) ;
        edd_die();
    }

    public function pp_charitable_user_registration_fields( $fields ) {

        $fields[ 'first_name' ] = array(
            'label'         => __( 'First Name', 'pp-toolkit' ),
            'type'          => 'text', 
            'required'      => true, 
            'priority'      => 3,
            'value'         => isset( $_POST[ 'first_name' ] ) ? $_POST[ 'first_name' ] : ''
        );

        $fields[ 'last_name' ] = array(
            'label'         => __( 'Last Name', 'pp-toolkit' ),
            'type'          => 'text', 
            'required'      => true, 
            'priority'      => 4,
            'value'         => isset( $_POST[ 'last_name' ] ) ? $_POST[ 'last_name' ] : ''
        );   

        $fields[ 'user_email' ][ 'priority' ] = 5;

        $fields[ 'user_confirmation' ] = array(
            'label'         => sprintf( '%s %s\'s %s + %s', 
                __( 'I agree to', 'pp-toolkit' ), 
                get_option( 'blogname' ),
                sprintf( '<a target="_blank" href="' . home_url( "terms-of-service" ) . '">%s</a>', __( 'Terms of Service', 'pp-toolkit' ) ), 
                sprintf( '<a target="_blank" href="' . home_url( "privacy-policy" ) . '">%s</a>', __( 'Privacy Policy', 'pp-toolkit' ) )
            ), 
            'type'          => 'checkbox',
            'required'      => true, 
            'priority'      => 9, 
            'fullwidth'     => true
        );
        
        return $fields;
    }

    public function pp_charitable_user_fields( $fields, $form ) {

        $fields[ 'first_name' ][ 'label' ] = __( 'First Name', 'pp-toolkit' );  
     
        $fields[ 'description' ][ 'label' ] = __( 'About Your Chapter', 'pp-toolkit' );
        $fields[ 'description' ][ 'required' ] = false;
        $fields[ 'description' ][ 'priority' ] = 18;
        $fields[ 'description' ][ 'fullwidth' ] = true;
        $fields[ 'description' ][ 'placeholder' ] = "";
        
        /* GUM Edit 
        $fields['chapter']['label'] = 'Fraternity/Sorority Name';
        $fields['chapter']['type'] = 'text';
        $fields['chapter']['required'] = true;
        $fields['chapter']['priority'] = 14;
        $fields['chapter']['value'] = $form->get_user_value('chapter');
        */
        
        /* GUM New */
        $fields[ 'chapter' ] = array(
            'label'         => __( 'Fraternity/Sorority Name', 'pp-toolkit' ),
            'type'          => 'text',
            'required'      => true, 
            'fullwidth'     => false,
            //'rows'          => 4,//use with textarea
            'priority'      => 13,
            'value'         => $form->get_user_value('chapter'),
            'content'       => __( 'Fraternity/Sorority Name', 'pp-toolkit' )
        );         

        $fields[ 'organisation' ][ 'label' ] = 'University/College';
        $fields[ 'organisation' ][ 'required' ] = true;
        $fields[ 'organisation' ][ 'priority' ] = 13;
        // $fields[ 'organisation' ][ 'fullwidth' ] = true;

        
                
                
         $fields[ 'referral_source' ] = array(
            'label'         => __( 'How did you hear about us?', 'pp-toolkit' ), 
            'type'          => 'text',
            'priority'      => 19, 
            'required'      => false,
            'value'         => $form->get_user_value( 'referral_source' )
        );

        if ( isset( $fields[ 'avatar' ] ) ) {
            $fields['avatar']['label'] = 'Your Profile Photo';
            $fields[ 'avatar' ][ 'priority' ] = 14;
            $fields[ 'avatar' ][ 'required' ] = false;
            $fields[ 'avatar' ][ 'uploader' ] = true;
        }

        return $fields;
    }

    /**
     * Remove Address & Social Profiles sections. 
     *
     * @param   array[] $sections
     * @return  array[]
     * @since   1.0.11
     * */
    public function pp_remove_charitable_user_social_fields( $sections ) {
        return array();
	}
    public function pp_remove_address_social_sections( $sections ) {
        unset( 
            $sections[ 'address_fields' ], 
            $sections[ 'social_fields' ] 
        );

        return $sections;
    }

    /**
     * Customize address fields. 
     *
     * @param   array[]   $fields
     * @return  array[]
     * @since   1.0.0
     */
    public function pp_charitable_user_address_fields( $fields ) {
        unset( $fields[ 'phone' ] );
        unset( $fields[ 'city' ] );
        unset( $fields[ 'state' ] );
        unset( $fields[ 'address' ] );
        unset( $fields[ 'address_2' ] );
        unset( $fields[ 'postcode' ] );
        unset( $fields[ 'country' ] );
        
        // $fields['address']['required'] = false;
        // $fields['city']['required'] = false;
        // $fields['state']['required'] = false;
        // $fields['postcode']['required'] = false;
        // $fields['venue_name']['required'] = false;
        
        
        return $fields;  
    }

    /**
     * Check if a failed user login attempt originated from Charitable login form. 
     *
     * If so redirect user to Charitable login page.
     *
     * @param   WP_User|WP_Error $user_or_error
     * @param   string           $username
     * @return  WP_User|void
     * @access  public
     * @since   1.4.0
     */
    public function maybe_redirect_at_authenticate( $user_or_error, $username ) {

        if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
            return $user_or_error;
        }
        
        if ( ! is_wp_error( $user_or_error ) ) {
            return $user_or_error;
        }

        if ( ! isset( $_POST['charitable'] ) || ! $_POST['charitable'] ) {
            return $user_or_error;
        }

        if(empty($user_or_error->errors)){
            return $user_or_error;
        }

        $errors = implode(',', array_keys($user_or_error->errors));

        $redirect_url = charitable_get_permalink( 'login_page' );

        if ( strlen( $username ) ) {
            $redirect_url = add_query_arg( 'username', $username, $redirect_url );
        }

        if ( strlen( $errors ) ) {
            $redirect_url = add_query_arg( 'errors', $errors, $redirect_url );
        }

        wp_safe_redirect( esc_url_raw( $redirect_url ) );

        exit();
    }

    public function maybe_add_notices(){
        if(!isset($_GET['errors']) || empty($_GET['errors']))
            return;

        $errors = explode(',', $_GET['errors']);
        // echo "<pre>";
        // print_r($errors);
        // echo "</pre>";
        // exit();

        if(!empty($errors)):
        foreach ( $errors as $error ) {

            /* Make sure the error messages link to our forgot password page, not WordPress' */
            switch ( $error ) {

                case 'invalid_username' :
                    
                    $error = __( '<strong>ERROR</strong>: Invalid username.', 'charitable' ) .
                        ' <a href="' . esc_url( charitable_get_permalink( 'forgot_password_page' ) ) . '">' .
                        __( 'Lost your password?' ) .
                        '</a>';
                    
                    break;

                case 'invalid_email' :
                    
                    $error = __( '<strong>ERROR</strong>: Invalid email address.', 'charitable' ) .
                        ' <a href="' . esc_url( charitable_get_permalink( 'forgot_password_page' ) ) . '">' .
                        __( 'Lost your password?' ) .
                        '</a>';
                    
                    break;

                case 'incorrect_password' : 
                    
                    $error = __( '<strong>ERROR</strong>: The password you entered is incorrect.' ) .
                    ' <a href="' . esc_url( charitable_get_permalink( 'forgot_password_page' ) ) . '">' .
                    __( 'Lost your password?' ) .
                    '</a>';
                    
                    break;

                default : 
                    $error = false;

            }

            if($error){
                charitable_get_notices()->add_error( $error );
            }

        }

        charitable_get_session()->add_notices();

        endif;

    }
    
    public function notice_success_profile_updated($submitted, $fields, $form){

	
	///die('tttttt');
        // echo "<pre>";
        // var_dump($form->is_changing_password());
        // echo "</pre>";
        // echo "string"; exit();

        if($form->is_changing_password()){
            charitable_get_notices()->add_error( 'Your password has been successfully changed!', 'charitable' );
        } else {
            // charitable_get_notices()->add_error( 'Profile updated.', 'charitable' );
        }
    }

    public function delete_related_products($post_id){
        $post_to_delete = get_post( $post_id );

        // Bail if it's not a campaign
        if ( get_post_type( $post_to_delete ) !== 'campaign' ) {
            return;
        }

        $log = array();

        // maybe force delete / bypass trash
        $force_delete = false;

        remove_action( 'before_delete_post', array($this, 'delete_related_products'), 10 );

        $events = philanthropy_get_campaign_event_ids($post_id);
        if(!empty($events) && is_array($downloads) ):
        foreach ($events as $key => $event) {
            try {
                // delete events
                wp_delete_post( $download->ID, $force_delete ); 

            } catch (Exception $e) {
                
            }
        }
        endif;

        /**
         * Delete connected downloads (tickets & merchandises)
         * @var Charitable_EDD_Campaign
         */
        if(class_exists('Charitable_EDD_Campaign')){

            $campaign = new Charitable_EDD_Campaign( $post_id );
            $downloads      = $campaign->get_connected_downloads( array('post_status' => 'any') );

            if( !empty($downloads) && is_array($downloads) ):
            foreach ($downloads as $download) {
                try {

                    // may need to remove benefactor relationship
                    $rels = charitable()->get_db_table('edd_benefactors')->get_benefactors_for_download($download->ID);
                    if(!empty($rels)):
                    foreach ($rels as $key => $rel) {
                        if(($rel->campaign_id != $post_id) || ($rel->edd_download_id != $download->ID) )
                            continue;

                        charitable_get_table( 'benefactors' )->delete( $rel->campaign_benefactor_id );
                    }
                    endif;

                    wp_delete_post( $download->ID, $force_delete ); 

                    $log[] = $download->ID;
                    
                } catch (Exception $e) {
                    
                }
            }
            endif;

        }

        // echo "<pre>";
        // print_r($downloads);
        // echo "</pre>";

        // exit();
        
        add_action( 'before_delete_post', array($this, 'delete_related_products'), 10, 1 );
    }

    public function includes(){

    }

}

PP_Charitable::init();