<?php
/**
 * PP_Team_Fundraising Class.
 *
 * @class       PP_Team_Fundraising
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Team_Fundraising class.
 */
class PP_Team_Fundraising {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Team_Fundraising();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        // add_filter( 'charitable_campaign_post_type', array($this, 'enable_hierarchical_for_campaign'), 10, 1 ); 
        // add_filter( 'request', array($this, 'assign_default_create_request'), 10, 1 );
        // add_action( 'template_redirect', array($this, 'check_if_page_create_child_campaign') );
        
        add_action( 'charitable_ambassadors_start', array($this, 'load_child_campaign_form_class'), 10, 1 );

        add_action( 'init', array($this, 'add_endpoint_for_create_child_campaign'), 10 );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 10, 1 );

        add_action( 'charitable_single_campaign_before', array($this, 'start_buffering_content'), 4 );
        add_action( 'charitable_single_campaign_after', array($this, 'display_create_child_campaign_form'), 10 );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );

        add_action( 'init', array($this, 'save_team_fundraising_form'), 10 );

        add_action( 'template_redirect', array($this, 'maybe_redirect_to_login') );

        add_filter( 'charitable_permalink_campaign_editing_page', array($this, 'change_charitable_permalink_campaign_editing_page'), 100, 2 );

        add_filter( 'charitable_edd_download_benefits', array($this, 'team_fundraising_benefits'), 10000, 5 );
        add_filter( 'charitable_edd_donation_values', array($this, 'remove_parent_campaign_donation_from_args'), 10, 3 );
        add_action( 'edd_insert_payment', array( $this, 'update_log_with_removed_parent' ), 11, 2 );

        add_filter( 'pp_get_account_menu', array($this, 'remove_profile_and_create_campaign_menu'), 10, 1 );
        // add_filter( 'charitable_user_fields', array($this, 'pp_charitable_user_fields'), 21, 2 );

        add_action( 'pre_get_posts', array($this, 'hide_child_campaigns_on_archive'), 10, 1 );
    
        add_action( 'after_wp_tiny_mce', array($this, 'remove_search_link'), 10, 1 );
        add_filter( 'wp_link_query', '__return_false', 10, 2 );
    
        add_action( 'child_campaign_created', array( 'PP_Email_Child_Campaign_Submission', 'send_email' ), 10, 3 );
    }

    public function remove_search_link(){
        ?>
        <script type="text/javascript">
            (function($){
                $('#wplink-link-existing-content, #search-panel').remove();
            })(jQuery);
        </script>
        <?php
    }

    public function hide_child_campaigns_on_archive($query){

        if ( is_admin() || ! $query->is_main_query() )
            return;

        if ( is_post_type_archive( 'campaign' ) ) {
            $query->set('post_parent', 0);
            // echo "<pre>";
            // print_r($query);
            // echo "</pre>";
        }
    }

    public function pp_charitable_user_fields( $fields, $form ) {

        if(!is_user_logged_in()){
            return $fields;
        }

        $user = new Charitable_User( get_current_user_id() );

        $campaigns = $user->get_campaigns( array( 
            'posts_per_page'    => -1, 
            'post_status'       => array( 'future', 'publish', 'pending', 'draft' ),
            'post_parent'       => 0,
        ) );

        if(!empty($campaigns->post_count)){
            return $fields;
        }

        // echo "<pre>";
        // print_r($fields);
        // echo "</pre>";

        $used = array('first_name', 'last_name', 'user_email');
        foreach ($fields as $key => $data) {
            if(!in_array($key, $used))
                unset($fields[$key]);
        }
        
        return $fields;
    }

    public function load_child_campaign_form_class(Charitable_Ambassadors $class){
        include_once( 'charitable-forms/team-fundraising/class-pp-team-fundraising-form.php' );
    }

    private function should_display_form(){

        if(!is_singular( 'campaign' ) || (get_query_var( 'create', false ) === false) )
            return false;

        $child_id = (!empty(get_query_var( 'create'))) ? get_query_var( 'create' ) : 0;

        if(!empty($child_id)){
            $user_id = get_current_user_id();
            $child = get_post( $child_id );
            if(!user_can( $user_id, 'edit_others_posts' ) && ($child->post_author != $user_id))  { 
                return false;
            }
        }

        return true;
    }

    public function start_buffering_content($campaign){

        if(!$this->should_display_form())
            return;

        if(!is_a($campaign, 'Charitable_Campaign')){
            $campaign = new Charitable_Campaign( $campaign ); 
        }

        if($campaign->get('team_fundraising') != 'on')
            return;

        ob_start();
    }

    public function display_create_child_campaign_form($campaign){

        if(!$this->should_display_form())
            return;

        if(!is_a($campaign, 'Charitable_Campaign')){
            $campaign = new Charitable_Campaign( $campaign ); 
        }

        if($campaign->get('team_fundraising') != 'on')
            return;

        ob_end_clean();

        // if(!is_user_logged_in()){
            // echo Charitable_Login_Shortcode::display();
        // } else {
        
            /**
             * Load create child page form
             */
            pp_toolkit_template( 'campaign/create-team-fundraising-form.php', array(
                'campaign' => $campaign,
                'form' => new PP_Team_Fundraising_Form( array('parent_campaign' => $campaign) ), // not used for now
                'child' => get_query_var( 'create', false ),
            ) );
        // }
        
    }

    public function maybe_redirect_to_login(){

        if(!$this->should_display_form())
            return;

        if(is_user_logged_in())
            return;

        $campaign = charitable_get_current_campaign();
        if($campaign->get('team_fundraising') != 'on')
            return;

        $create_url = trailingslashit( get_permalink( $campaign->ID ) ) . 'create';
        $url = charitable_get_permalink( 'login_page' );
        $url = add_query_arg( array( 'redirect_to' => $create_url ), $url );
        $url = esc_url_raw( apply_filters( 'charitable_campaign_submission_logged_out_redirect', $url ) );

        wp_safe_redirect( $url );
        exit();
    }

    public function enqueue_scripts(){

        if(!$this->should_display_form())
            return;

        wp_enqueue_script( 'cropit' );
        wp_enqueue_style( 'pp-toolkit-campaign-submission' );
        wp_enqueue_script( 'pp-toolkit-campaign-submission' );

        wp_localize_script( 'pp-toolkit-campaign-submission', 'CHARITABLE_AMBASSADORS_VARS', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
    }

    public function save_team_fundraising_form(){

        if(!isset($_POST['_save_team_fundraising']) || !isset($_POST['parent_campaign']))
            return;

        if(!wp_verify_nonce( $_POST['_save_team_fundraising'], 'save-team-fundraising' ) ){
            return;
        }

        try {

            $user_id = get_current_user_id();

            $update = false;
            $child_id = (isset($_POST['child_id']) && !empty($_POST['child_id'])) ? $_POST['child_id'] : false;
            if(!empty($child_id)){
                $update = true;
                $child = get_post( $child_id );
                if(!user_can( $user_id, 'edit_others_posts' ) && ($child->post_author != $user_id))  { 
                    throw new Exception("You are not authorized to update this campaign.", 1);
                }
            }

            if(!isset($_POST['goal']) || empty($_POST['goal'])){
                throw new Exception("Please set your fundraising goal.", 1);
            }
            
            $parent = get_post( $_POST['parent_campaign'] );
            if(empty($parent)){
                throw new Exception("Parent campaign not exists", 1);
            }

            $campaign = new Charitable_Campaign( $parent->ID ); 
            if($campaign->get('team_fundraising') != 'on'){
                throw new Exception("Team fundraising not activated on parent campaign", 1);
            }

             
			$campaign_crm_account_id = $_POST['campaign_crm_account_id'];
			if(is_user_logged_in()){
				$user_id = get_current_user_id();
				$user_crm_id = get_user_meta($user_id , 'crm_account_id' ,true);
				if($user_crm_id != ""){
					 $campaign_crm_account_id = $user_crm_id;
				}
			}

            if(isset($_POST['title']) && !empty($_POST['title'])){
                $campaign_title = sanitize_text_field( $_POST['title'] );
            } else {
                $user = get_userdata( $user_id );
                $_suffix_title = array();
                if(!empty($user->user_firstname)){
                    $_suffix_title[] = $user->user_firstname;
                }
                if(!empty($user->user_lastname)){
                    $_suffix_title[] = $user->user_lastname;
                }

                $campaign_title = (empty($_suffix_title)) ? $parent->post_title : $parent->post_title . ' - ' . implode(' ', $_suffix_title);
            }
            

            // use_parent_content
            $use_parent_content = 'on';
            if(!isset($_POST['use_parent_content']) || ($_POST['use_parent_content'] != 'on') ){
                $use_parent_content = 'off';
            } 

            $campaign_content = ( ($use_parent_content != 'on') && isset($_POST['campaign_content']) ) ? sanitize_text_field( $_POST['campaign_content'] ) : $parent->post_content;

            $args = array(
                'post_author'    => $user_id,
                'post_title'     => $campaign_title,
                'comment_status' => $parent->comment_status,
                'ping_status'    => $parent->ping_status,
                'post_content'   => $campaign_content,
                'post_excerpt'   => $parent->post_excerpt,
                'post_name'      => $parent->post_name,
                'post_parent'    => $parent->ID,
                'post_password'  => $parent->post_password,
                'post_status'    => $parent->post_status,
                'post_type'      => $parent->post_type,
                'to_ping'        => $parent->to_ping,
                'menu_order'     => $parent->menu_order
            );

            if($parent->post_date >= current_time( 'mysql' )){
                $args['post_date'] = $parent->post_date;
                $args['post_date_gmt'] = $parent->post_date_gmt;
            }

            if($update){
                $args['ID'] = $child_id;

                $post_id = wp_update_post( $args, true );
				update_post_meta($post_id,'post_sub_campaign_crm_id', $campaign_crm_account_id);
            
                if (is_wp_error($post_id)) {
                    $message = (is_array($post_id->get_error_messages())) ? current($post_id->get_error_messages()) : $post_id->get_error_messages();
                    throw new Exception( $message , 1);
                }
            } else {
			//	echo "<pre>";
			//	print_r($args);
				//die("ff");
                $post_id = wp_insert_post( $args );
				update_post_meta($post_id,'post_sub_campaign_crm_id', $campaign_crm_account_id);
            
                if(!$post_id){
                    throw new Exception("Failed to create campaign", 1);
                }
            }

            /**
             * Create benefactor for merchandises
             * @var [type]
             */
            $this->save_merchandise_benefactors($post_id, $parent->ID);


            /**
             * Create benefactor for tickets
             */
            $events = $this->save_tickets_benefactors($post_id, $parent->ID);

            /*
             * get all current post terms ad set them to the new post draft
             */
            $taxonomies = get_object_taxonomies($parent->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($parent->ID, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($post_id, $post_terms, $taxonomy, false);
            }

            $meta_keys_to_copy = array(
                '_campaign_recipient',
                '_campaign_suggested_donations',
                '_campaign_end_date',
                '_campaign_allow_custom_donations',
            );

            foreach ($meta_keys_to_copy as $meta_key) {
                $meta_value = get_post_meta( $parent->ID, $meta_key, true );
                update_post_meta( $post_id, $meta_key, $meta_value );
            }

            // log use parent content
            update_post_meta( $post_id, 'use_parent_content', $use_parent_content );

            // campaign goal
            $goal = Charitable_Campaign::sanitize_campaign_goal($_POST['goal']);
            update_post_meta( $post_id, '_campaign_goal', $goal );

            // use_parent_description
            $use_parent_description = 'on';
            if(!isset($_POST['use_parent_description']) || ($_POST['use_parent_description'] != 'on') ){
                $use_parent_description = 'off';
            } 

            $campaign_description = ( ($use_parent_description != 'on') && isset($_POST['campaign_description']) ) ? sanitize_text_field( $_POST['campaign_description'] ) : get_post_meta( $parent->ID, '_campaign_description', true );
            update_post_meta( $post_id, 'use_parent_description', $use_parent_description );
            update_post_meta( $post_id, '_campaign_description', $campaign_description );

            // use_parent_image
            $use_parent_image = 'on';
            if(!isset($_POST['use_parent_image']) || ($_POST['use_parent_image'] != 'on') ){
                $use_parent_image = 'off';
            } 

            $thumbnail_id = ( ($use_parent_image != 'on') && isset($_POST['campaign_image']) ) ? $_POST['campaign_image'] : get_post_meta( $parent->ID, '_thumbnail_id', true );
            update_post_meta( $post_id, 'use_parent_image', $use_parent_image );
            update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );


            // use_parent_video
            $use_parent_video = 'on';
            if(!isset($_POST['use_parent_video']) || ($_POST['use_parent_video'] != 'on') ){
                $use_parent_video = 'off';
            } 

            $campaign_video = ( ($use_parent_video != 'on') && isset($_POST['campaign_video']) ) ? $_POST['campaign_video'] : get_post_meta( $parent->ID, '_campaign_video', true );
            update_post_meta( $post_id, 'use_parent_video', $use_parent_video );
            update_post_meta( $post_id, '_campaign_video', $campaign_video );

            
            // use_parent_media_embed
            $use_parent_media_embed = 'on';
            if(!isset($_POST['use_parent_media_embed']) || ($_POST['use_parent_media_embed'] != 'on') ){
                $use_parent_media_embed = 'off';
            } 

            $campaign_media_embed = ( ($use_parent_media_embed != 'on') && isset($_POST['campaign_media_embed']) ) ? $_POST['campaign_media_embed'] : get_post_meta( $parent->ID, '_campaign_media_embed', true );
            update_post_meta( $post_id, 'use_parent_media_embed', $use_parent_media_embed );
            update_post_meta( $post_id, '_campaign_media_embed', $campaign_media_embed );
            
            if($update){
                do_action( 'child_campaign_updated', $post_id, $parent->ID, $_POST );
            } else {
                do_action( 'child_campaign_created', $post_id, $parent->ID, $_POST );
            }

            wp_redirect( get_permalink( $post_id ) );
            exit();

        } catch (Exception $e) {
            charitable_get_notices()->add_error( $e->getMessage() );
        }
    }

    private function save_merchandise_benefactors($post_id, $parent_id){
        $merchandise_ids = philanthropy_get_campaign_merchandise_ids($parent_id);

        if(empty($merchandise_ids))
            return;

        foreach ($merchandise_ids as $product_id) {
            // $rels = charitable()->get_db_table('edd_benefactors')->get_benefactors_for_download($merchandise_id);
            
            $has_relationship = charitable()->get_db_table('edd_benefactors')->count_campaign_download_benefactors($post_id, $product_id);
            if(empty($has_relationship)){

                $campaign_id = $parent_id;

                $benefactor_data = apply_filters( 'pp_toolkit_campaign_product_benefactor_data', array(
                    'campaign_id'           => $post_id, 
                    'contribution_amount'   => 100, 
                    'contribution_amount_is_percentage' => 1, 
                    'contribution_amount_is_per_item' => 1, 
                    'benefactor' => array(
                        'edd_download_id'               => $product_id, 
                        'edd_is_global_contribution'    => 0, 
                        'edd_download_category_id'      => 0
                    )
                ), $post_id, $product_id );

                /* The start and end date will be the same as the end date of the campaign. */
                $campaign_start_date = get_post_field( 'post_date', $campaign_id );
                $campaign_end_date = get_post_meta( $campaign_id, '_campaign_end_date', true );

                if ( !empty($campaign_start_date) ) {
                    $benefactor_data['date_created'] = $campaign_start_date;
                }
                
                if ( !empty($campaign_end_date) ) {
                    $benefactor_data['date_deactivated'] = $campaign_end_date;
                }

                // if
                $start_sell_date = get_post_meta( $product_id, 'merchandise_start_date', true );
                if(!empty($start_sell_date)){
                    $benefactor_data['date_created'] = $start_sell_date;
                }

                $end_sell_date = get_post_meta( $product_id, 'merchandise_end_date', true );
                if(!empty($end_sell_date)){
                    $benefactor_data['date_deactivated'] = $end_sell_date;
                }

                $insert_benefactor = charitable()->get_db_table( 'benefactors' )->insert( $benefactor_data );

            //     echo "<pre>";
            //     var_dump($insert_benefactor);
            //     echo "</pre>";
            
            }
        }
    }

    private function save_tickets_benefactors($post_id, $parent_id){
        $events = philanthropy_get_campaign_event_ids($parent_id);
        if(empty($events))
            return;

        foreach ($events as $key => $event_id) {
            $ticket_ids = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_tickets_ids( $event_id );
            if(empty($ticket_ids))
                continue;

            foreach ($ticket_ids as $ticket_id) {
                $has_relationship = charitable()->get_db_table('edd_benefactors')->count_campaign_download_benefactors($post_id, $ticket_id);

                if (!$has_relationship) {

                    $benefactor_data = apply_filters( 'pp_toolkit_campaign_ticket_benefactor_data', array(
                        'campaign_id'           => $post_id, 
                        'contribution_amount'   => 100, 
                        'contribution_amount_is_percentage' => 1, 
                        'contribution_amount_is_per_item' => 1, 
                        'benefactor' => array(
                            'edd_download_id'               => $ticket_id, 
                            'edd_is_global_contribution'    => 0, 
                            'edd_download_category_id'      => 0
                        )
                    ), $parent_id, $event_id );

                    /* The start and end date will be the same as the end date of the campaign. */
                    $campaign_start_date = get_post_field( 'post_date', $parent_id );
                    $campaign_end_date = get_post_meta( $parent_id, '_campaign_end_date', true );

                    if ( !empty($campaign_start_date)  ) {
                        $benefactor_data['date_created'] = $campaign_start_date;
                    }
                    
                    if ( !empty($campaign_end_date) ) {
                        $benefactor_data['date_deactivated'] = $campaign_end_date;
                    }

                    
                    $start_sell_date = get_post_meta( $ticket_id, '_ticket_start_date', true );
                    if(!empty($start_sell_date)){
                        // need to parse the date because the ticket date show as like this 'September 30, 2017'
                        try {
                            $dt = new DateTime( $start_sell_date );
                            $benefactor_data['date_created'] = $dt->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            // failed
                        }
                    }

                    $end_sell_date = get_post_meta( $ticket_id, '_ticket_end_date', true );
                    if(!empty($end_sell_date)){
                        // need to parse the date because the ticket date show as like this 'September 30, 2017'
                        try {
                            $dt = new DateTime( $end_sell_date );
                            $benefactor_data['date_deactivated'] = $dt->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            // failed
                        }
                    }

                    charitable()->get_db_table( 'benefactors' )->insert( $benefactor_data );

                }
            }
        }
    }

    public function change_charitable_permalink_campaign_editing_page($return, $args){

        $campaign_id = isset( $args[ 'campaign_id' ] ) ? $args[ 'campaign_id' ] : get_the_ID();
        $parent_id = wp_get_post_parent_id( $campaign_id );
        if(!empty($parent_id)){
            $return = trailingslashit( get_permalink( $parent_id ) ) . 'create/' . $campaign_id;
        }

        return $return;
    }

    public function team_fundraising_benefits($benefits, $campaigns_list, $download_id, $linked, Charitable_EDD_Cart $edd_cart){
        
        $campaigns_on_cart = self::pp_get_campaigns_on_cart();
        if(empty($campaigns_on_cart))
            return $benefits;

        $benefits_by_downloads = $edd_cart->get_benefits_by_downloads();

        if ( ! isset( $benefits_by_downloads[ $download_id ] ) ) {
            return '';
        }

        $campaigns_list = array();

        foreach ( $benefits_by_downloads[ $download_id ] as $campaign_id => $amounts ) {
            
            if(!array_key_exists($campaign_id, $campaigns_on_cart))
                continue;

            $campaign_name = get_the_title( $campaign_id );

            if ( $linked ) {
                $campaigns_list[] = sprintf( '<a href="%s" title="%s">%s</a>', get_permalink( $campaign_id ), $campaign_name, $campaign_name );
            }
            else {
                $campaigns_list[] = $campaign_name;
            }            
        }

        return implode( ', ', $campaigns_list );
    }

    public function remove_parent_campaign_donation_from_args($donation_args, $payment_id, $args){

        if(!isset($donation_args['campaigns']) || empty($donation_args['campaigns']))
            return $donation_args;

        $campaigns_on_cart = self::pp_get_campaigns_on_cart();
        if(empty($campaigns_on_cart))
            return $donation_args;

        $new_campaign_donations = array();
        $edd_cart = Charitable_EDD_Cart::create_with_payment( $payment_id );

        /* Add all campaign donations from benefactor relationships. */
        foreach ( $edd_cart->get_benefactor_benefits() as $benefactor_id => $benefits ) {

            foreach ( $benefits as $download_id => $benefit ) {

                if(!array_key_exists($benefit['campaign_id'], $campaigns_on_cart))
                    continue;

                $new_campaign_donations[] = array(
                    'campaign_id' => $benefit['campaign_id'],
                    'amount'      => $benefit['contribution'],
                );

            }
        }

        /* Add all campaign donations created as fees. */
        if ( $edd_cart->has_donations_as_fees() ) {

            foreach ( $edd_cart->get_fees() as $campaign_id => $fees ) {

                if(!array_key_exists($campaign_id, $campaigns_on_cart))
                    continue;

                $benefit_amount = array_sum( $fees );

                $new_campaign_donations[] = array(
                    'campaign_id' => $campaign_id,
                    'amount'      => $benefit_amount,
                );

            }
        }

        $donation_args['campaigns'] = $new_campaign_donations;

        return $donation_args;
    }

    public function update_log_with_removed_parent($payment_id, $payment_data){
        $donation_id = get_post_meta( $payment_id, 'charitable_donation_from_edd_payment', true );
        $log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
        if(empty($log))
            return;

        $campaigns_on_cart = self::pp_get_campaigns_on_cart();
        if(empty($campaigns_on_cart))
            return;

        $new_log = array();
        foreach ($log as $key => $l) {
            if(!isset($l['campaign_id']))
                continue;

            if(!array_key_exists($l['campaign_id'], $campaigns_on_cart))
                continue;

            $new_log[] = $l;
        }

        if(!empty($new_log)){
            update_post_meta( $donation_id, 'donation_from_edd_payment_log', $new_log );
            wp_cache_delete( 'campaign_donation_from_edd_payment_' . $payment_id );
        }
    }

    // public function enable_hierarchical_for_campaign($args){

    //     $args['hierarchical'] = true;

    //     return $args;
    // }

    // public function assign_default_create_request($vars){
    //     if( isset( $vars['create'] ) && empty($vars['create']) ){
    //         $vars['create'] = 'new';
    //     }

    //     return $vars;
    // }

    // public function check_if_page_create_child_campaign(){
    //     if( get_query_var( 'create' ) ) {
    //         echo 'var : ' . get_query_var( 'create' ) . '<br>';
    //         echo 'ID : ' . get_queried_object_id() . '<br>';
    //         // do stuff!
    //         exit();
    //     }
    // }
    
    public static function pp_get_campaigns_on_cart(){
        $cart_contents = edd_get_cart_contents();
        $cart_item_fees = edd_get_cart_fees( 'item' );

        $cache_key = md5(maybe_serialize( array_merge($cart_contents, $cart_item_fees) ));

        $pp_get_campaigns_on_cart = wp_cache_get( $cache_key, 'pp-toolkit' );

        if ( false === $pp_get_campaigns_on_cart ) {
            $pp_get_campaigns_on_cart = array();

            // loop cart contents
            foreach ($cart_contents as $k => $data) {
                if(!isset($data['options']) || !is_array($data['options']))
                    continue;

                $download_id = $data['id'];
                $price_id = (isset($data['options']['price_id'])) ? $data['options']['price_id'] : 0;
                foreach ($data['options'] as $k => $v) {
                    if($k != 'campaign_id')
                        continue;

                    $campaign_id = $v;
                    if(!isset($pp_get_campaigns_on_cart[$campaign_id])){
                        $pp_get_campaigns_on_cart[$campaign_id] = array();
                    }

                    $id_key = $download_id . '_' . $price_id;
                    $pp_get_campaigns_on_cart[$campaign_id]['items'][$id_key] = $data;
                }
            }

            // loop cart fees
            foreach ($cart_item_fees as $key => $fee) {
                if(!isset($fee['campaign_id']))
                    continue;

                if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
                    continue;
                }

                $campaign_id = $fee['campaign_id'];
                if(!isset($pp_get_campaigns_on_cart[$campaign_id])){
                    $pp_get_campaigns_on_cart[$campaign_id] = array();
                }

                $pp_get_campaigns_on_cart[$campaign_id]['donations'][] = $fee;
            }

            wp_cache_set( $cache_key, $pp_get_campaigns_on_cart, 'pp-toolkit' );
        }

        return (array) $pp_get_campaigns_on_cart;
    }
    
    public static function pp_get_campaigns_on_payment($payment_id){

        $cart_contents = edd_get_payment_meta_cart_details( $payment_id, true );
        $cart_item_fees = edd_get_payment_fees( $payment_id, 'item' );

        $cache_key = md5( $payment_id . maybe_serialize( array_merge($cart_contents, $cart_item_fees) ));

        $pp_get_campaigns_on_cart = wp_cache_get( $cache_key, 'pp-toolkit' );

        if ( false === $pp_get_campaigns_on_cart ) {
            $pp_get_campaigns_on_cart = array();

            // loop cart contents
            foreach ($cart_contents as $k => $data) {
                if(!isset($data['item_number']) || !is_array($data['item_number']))
                    continue;

                $item_number = $data['item_number'];
                if(!isset($item_number['options']) || !is_array($item_number['options']))
                    continue;

                $download_id = (isset($item_number['id'])) ? $item_number['id'] : $data['id'];
                $price_id = (isset($item_number['options']['price_id'])) ? $item_number['options']['price_id'] : 0;
                foreach ($item_number['options'] as $k => $v) {
                    if($k != 'campaign_id')
                        continue;

                    $campaign_id = $v;
                    if(!isset($pp_get_campaigns_on_cart[$campaign_id])){
                        $pp_get_campaigns_on_cart[$campaign_id] = array();
                    }

                    $id_key = $download_id . '_' . $price_id;
                    $pp_get_campaigns_on_cart[$campaign_id]['items'][$id_key] = $item_number;
                }
            }

            // loop cart fees
            foreach ($cart_item_fees as $key => $fee) {
                if(!isset($fee['campaign_id']))
                    continue;

                if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
                    continue;
                }

                $campaign_id = $fee['campaign_id'];
                if(!isset($pp_get_campaigns_on_cart[$campaign_id])){
                    $pp_get_campaigns_on_cart[$campaign_id] = array();
                }

                $pp_get_campaigns_on_cart[$campaign_id]['donations'][] = $fee;
            }

            wp_cache_set( $cache_key, $pp_get_campaigns_on_cart, 'pp-toolkit' );
        }

        return (array) $pp_get_campaigns_on_cart;
    }

    public static function remove_parent_from_campaign_benefits($campaigns){

        if(empty($campaigns) || !is_array($campaigns))
            return $campaigns;

        
        $pp_get_campaigns_on_cart = self::pp_get_campaigns_on_cart();

        foreach ($campaigns as $campaign_id => $benefits) {

            if(!array_key_exists($campaign_id, $pp_get_campaigns_on_cart))
                unset($campaigns[$campaign_id]);
        }

        return array_filter($campaigns);
    }

    public function remove_profile_and_create_campaign_menu($menu_items){

        $user = new Charitable_User( get_current_user_id() );

        $campaigns = $user->get_campaigns( array( 
            'posts_per_page'    => -1, 
            'post_status'       => array( 'future', 'publish', 'pending', 'draft' ),
            'post_parent'       => 0,
        ) );

        if(empty($campaigns->post_count)){
            if(isset($menu_items['profile'])){
                unset($menu_items['profile']);
            }
            if(isset($menu_items['create-campaign'])){
                unset($menu_items['create-campaign']);
            }
        }
        
        return $menu_items;
    }

    public function add_endpoint_for_create_child_campaign(){
        add_rewrite_endpoint('create', EP_PERMALINK);
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'create';
        return $vars;
    }

    public function includes(){

    }

}

PP_Team_Fundraising::init();