<?php
/**
 * Class that is responsible for augmenting the main Charitable Campaign Form with additional fields.
 *
 * @package     Philanthropy Project/Classes/PP_Charitable_Campaign_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('PP_Charitable_Campaign_Form')) :

    /**
     * PP_Charitable_Campaign_Form
     *
     * @since       1.0.0
     */
    class PP_Charitable_Campaign_Form {

        /**
         * The current user.
         *
         * @var     Charitable_User
         * @access  protected
         */
        protected $user;

        /**
         * The one and only way to create an object instance.
         *
         * @param   Philanthropy_Project $pp
         *
         * @return  PP_Charitable_Campaign_Form
         * @access  public
         * @static
         * @since   1.0.0
         */

        public static function start(Philanthropy_Project $pp) {
            if (!$pp->is_start()) {
                return;
            }
			
            charitable()->registry()->register_object(new PP_Charitable_Campaign_Form());
        }

        /**
         * Private constructor.
         *
         * @access  protected
         * @since   1.0.0
         */
        protected function __construct() {
            $this->includes();

            $this->attach_hooks_and_filters();
        }

        /**
         * Set up callback methods for actions & filters.
         *
         * @return  void
         * @access  protected
         * @since   1.0.0
         */
        protected function attach_hooks_and_filters() {

            /**
             * Ajax load template
             */
            add_action('wp_ajax_add_merchandise_form', [$this, 'render_merchandise_form']);
            add_action('wp_ajax_nopriv_add_merchandise_form', [$this, 'render_merchandise_form']);

            add_action('wp_ajax_add_event_form', [$this, 'render_event_form']);
            add_action('wp_ajax_nopriv_add_event_form', [$this, 'render_event_form']);

            add_action('wp_ajax_add_ticket_form', [$this, 'render_ticket_form']);
            add_action('wp_ajax_nopriv_add_ticket_form', [$this, 'render_ticket_form']);

            add_action('wp_ajax_save_imagedata', [$this, 'save_imagedata']);
            add_action('wp_ajax_nopriv_save_imagedata', [$this, 'save_imagedata']);

            add_action( 'wp_ajax_nopriv_login_on_create_campaign', array($this, 'do_login_on_create_campaign') );


            add_filter('charitable_campaign_submission_fields', [$this, 'custom_pp_toolkit_sections'], 100, 2);

            add_filter('charitable_campaign_submission_core_data', [$this, 'campaign_core_date'], 10, 3);
            // fix for end date not saving when update campaign from front end, because field length already unset
            add_filter( 'charitable_campaign_submission_fields_map', array( $this, 'save_end_date' ), 10, 2 );

            add_filter( 'charitable_form_missing_fields', array($this, 'check_form_missing_fields'), 10, 4 );
            /**
             * Custom forms actions
             */
            add_action( 'charitable_campaign_submission_save', array( 'PP_Merchandise_Form', 'save_merchandise' ), 10, 3 );
            add_action( 'charitable_campaign_submission_save', array( 'PP_Event_Form', 'save_event' ), 10, 3 );
            add_action( 'charitable_campaign_submission_save', array( $this, 'save_display_widget_options' ), 10, 3 );

            add_filter( 'charitable_form_missing_fields', ['PP_Event_Form', 'check_event_required_fields'], 10, 4);

            // change create user `function on charitable_campaign_submission_values`
            // add_action( 'charitable_campaign_submission_save_page_campaign_details', array($this, 'maybe_create_user'), 9, 1 );
            add_filter( 'charitable_campaign_submission_values', array($this, 'maybe_need_create_user'), 10, 3 );
            add_filter( 'charitable_campaign_submission_values', array($this, 'add_default_team_fundraising_value'), 10, 3 );
            add_filter( 'charitable_campaign_submission_values', array($this, 'add_missing_volunteers_value'), 10, 3 );

            add_filter( 'charitable_campaign_submission_fields_map', array( $this, 'merge_fields_map' ), 10, 3 );

            // add_filter( 'charitable_ambassadors_campaign_form_hidden_fields', array($this, 'auto_add_post_id'), 10, 2 );
            // add_action( 'charitable_campaign_submission_save', array($this, 'change_status_and_redirect'), 10, 4 );
            
        //     add_filter( 'charitable_campaign_submission_redirect_url', array($this, 'preview_redirect_to_edit'), 10, 4 );
        //     add_action( 'init', array($this, 'edit_redirect_to_preview') );
        
            add_action( 'wp_head', array($this, 'print_unload_page_scripts') );
            add_action( 'charitable_campaign_submission_after', array($this, 'add_popup_notifications_container'), 10 );
        
            add_action( 'charitable_form_before_fields', array($this, 'maybe_assign_role'), 10, 1 );
        }


        public function save_display_widget_options($submitted, $campaign_id, $user_id){
            if(isset($submitted['display_top_fundraiser_widget']) && ($submitted['display_top_fundraiser_widget'] == 'on')){
                update_post_meta( $campaign_id, 'display_top_fundraiser_widget', 'on' );
            } else {
                update_post_meta( $campaign_id, 'display_top_fundraiser_widget', 'off' );
            }
        }

        /**
         * Fix venue and  not saving because
         * `current_user_can( 'publish_tribe_venues' )`
         * and current_user_can( 'publish_tribe_organizers' ) still return false
         * because we change the role on same request
         * @param  [type] $form [description]
         * @return [type]       [description]
         */
        public function maybe_assign_role($form){

            if(!is_user_logged_in())
                return;

            $user = $form->get_user();

            $user->remove_role( 'subscriber' );
            $user->add_role( 'campaign_creator' );
        }

        public function print_unload_page_scripts(){
            // if(charitable_is_page('campaign_editing_page')){
            //     $campaign_id = get_query_var( 'campaign_id', false );
            //     // exit();
            // }

            $success_page = charitable_get_option( 'campaign_submission_success_page' );

            if( (is_page( $success_page ) && isset($_GET['campaign_id'])) || (is_singular( 'campaign' ) && ( isset($_GET['preview']) || isset($_GET['updated']) )) ){
                
                $campaign_id = (is_page( $success_page )) ? $_GET['campaign_id'] : get_the_ID();
                ?>
                <script language="javascript" type="text/javascript">
				jQuery(document).ready(function(){
                    // window.onload = function () {
                        if (typeof history.pushState === "function") {
                            history.pushState("jibberish", null, null);
                            window.onpopstate = function () {
                                history.pushState('newjibberish', null, null);
                                
                                // Handle the back (or forward) buttons here
                                // Will NOT handle refresh, use onbeforeunload for this.
                                // var r = confirm("Do you want to edit your recent campaign?");
                                // if(r == true){
                                //     window.location = "<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $campaign_id ) ) ); ?>";
                                // }
                                
                                swal({
                                    title: "Edit Campaign",
                                    text: "Do you want to continue editing your campaign?",
                                    type: "info",
                                    showCancelButton: true,
                                    closeOnConfirm: true,
                                    confirmButtonText: "Yes"
                                },
                                function(){
                                    window.location = "<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $campaign_id ) ) ); ?>";
                                });

                                return false;
                            };
                        }
                        else {
                            var ignoreHashChange = true;
                            window.onhashchange = function () {
                                if (!ignoreHashChange) {
                                    ignoreHashChange = true;
                                    window.location.hash = Math.random();
                                    // Detect and redirect change here
                                    // Works in older FF and IE9
                                    // * it does mess with your hash symbol (anchor?) pound sign
                                    // delimiter on the end of the URLvar r = confirm("Do you want to edit recently campaign?");
                                    // var r = confirm("Do you want to edit your recent campaign?");
                                    // if(r == true){
                                    //     window.location = "<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $campaign_id ) ) ); ?>";
                                    // }
                                    
                                    swal({
                                        title: "Edit Campaign",
                                        text: "Do you want to continue editing your campaign?",
                                        type: "info",
                                        showCancelButton: true,
                                        closeOnConfirm: true,
                                        confirmButtonText: "Yes"
                                    },
                                    function(){
                                        window.location = "<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $campaign_id ) ) ); ?>";
                                    });

                                    return false;
                                }
                                else {
                                    ignoreHashChange = false;   
                                }
                            };
                        }
                    // }
			})
                </script>
                <?php
            }
        }

        public function add_popup_notifications_container(){
            echo '<div id="submit-notices></div>"';
        }

        // public function auto_add_post_id($hidden_fields, Charitable_Ambassadors_Campaign_Form $form){
        //     if(!charitable_is_page( 'campaign_submission_page' ))
        //         return $hidden_fields;

        //     if(empty($hidden_fields['ID'])){
        //         $hidden_fields['ID'] = wp_insert_post( array( 'post_title' => __( 'Auto Draft' ), 'post_type' => 'campaign', 'post_status' => 'auto-draft' ) );
        //     }

        //     return $hidden_fields;
        // }

        // public function change_status_and_redirect($submitted, $campaign_id, $user_id, $form){
        //     if( !isset( $submitted['preview-campaign'] ) )
        //         return;

        //     $status = get_post_status( $campaign_id );
        //     if($status == 'auto-draft'){
        //         wp_update_post( array(
        //             'ID' => $campaign_id,
        //             'post_status' => 'draft'
        //         ) );

        //         $url = get_permalink( $campaign_id );
        //         $url = esc_url_raw( add_query_arg( array( 'preview' => true ), $url ) );
        //         wp_safe_redirect( $url );
        //         exit();
        //     }


        // }

        public function preview_redirect_to_edit($url, $submitted, $campaign_id, $user_id){

            if(isset( $submitted['preview-campaign'] )){
                // charitable_get_session()->set('new_campaign_preview', $campaign_id);
                $url = esc_url(charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $campaign_id ) ) );
            }

            $new_campaign_id = charitable_get_session()->get('new_campaign_preview');
            echo $new_campaign_id; exit();

            return $url;

            // $context = $form->get_submission_context();
            // if(!$context['is_preview'])
            //     return;

            // charitable_get_session()->set('new_campaign_preview', $campaign_id);

            // $new_campaign_id = charitable_get_session()->get('new_campaign_preview');
            // echo $new_campaign_id; exit();

            // $url = esc_url(charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $campaign_id ) ) );
            // wp_redirect( $url );
            // exit();
        }

        public function edit_redirect_to_preview(){
            $new_campaign_id = charitable_get_session()->get('new_campaign_preview');
            if(empty($new_campaign_id))
                return;

            echo $new_campaign_id; exit();
        }

        public function custom_pp_toolkit_sections($fields, Charitable_Ambassadors_Campaign_Form $form){

            $fields = apply_filters( 'pp_toolkit_campaign_submission_fields', array(
				'profile_fields' => array(
                    'legend'        => __( 'Profile', 'pp-toolkit' ),
                    // 'things_to_know'        => 'Details about section here',
                    'type'          => 'fieldset',
                    'fields'        => $this->get_account_options_fields($form),
                    'priority'      => 65,
                    'page'          => 'campaign_details',
                    'hide_header'   => true,
                ),
                'campaign_fields' => array(
                    'legend'        => __( 'Campaign Details', 'pp-toolkit' ),
                    // 'things_to_know'        => 'Details about section here',
                    'type'          => 'fieldset',
                    'fields'        => $this->get_campaign_fields($form),
                    'priority'      => 20,
                    'page'          => 'campaign_details',
                    'hide_header'   => true,
                ),
                'fundraising_fields' => array(
                    'legend'        => __( 'Fundraising Options', 'pp-toolkit' ),
                    // 'things_to_know'        => 'Details about section here',
                    'type'          => 'fieldset',
                    'fields'        => $this->get_fundraising_options_fields($form),
                    'priority'      => 40,
                    'page'          => 'campaign_details',
                    'hide_header'   => true,
                ),
                // 'account_fields' => array(
                //     'legend'        => __( 'Your Account', 'pp-toolkit' ),
                //     // 'things_to_know'        => 'Details about section here',
                //     'type'          => 'fieldset',
                //     'fields'        => $this->get_account_options_fields(),
                //     'priority'      => 60,
                //     'page'          => 'campaign_details',
                    // 'hide_header'   => true,
                // ),
                'payout_fields' => array(
                    'legend'        => __( 'Payout Info', 'pp-toolkit' ),
                    // 'things_to_know'        => 'Details about section here',
                    'type'          => 'fieldset',
                    'fields'        => $this->get_payout_options_fields($form),
                    'priority'      => 60,
                    'page'          => 'campaign_details',
                    'hide_header'   => true,
                ),
                'finished' => array(
                    'legend'        => __( 'Finished!', 'pp-toolkit' ),
                    // 'things_to_know'        => 'Details about section here',
                    'type'          => 'fieldset',
                    'fields'        => $this->get_finished_fields($form),
                    'priority'      => 70,
                    'page'          => 'campaign_details',
                    'hide_header'   => true,
                ),
            ), $this, $form );

            uasort( $fields, 'charitable_priority_sort' );

            // echo "<pre>";
            // print_r($fields);
            // echo "</pre>";

            return $fields;
        }

        public function get_campaign_fields(Charitable_Ambassadors_Campaign_Form $form){
            $fields = $form->get_campaign_fields();

            if(isset($fields['length'])) 
                unset($fields['length']);

            // $fields['campaign_category']['required'] = false;
            if(isset($fields['campaign_category'])) 
                unset($fields['campaign_category']);

            
            if(isset($fields['post_title'])){
                $fields['post_title']['priority'] = 1;
                // $fields['post_title']['placeholder'] = "Also used to generate your campaign page URL (greeks4good.com/campaigns/campaign-name).";
                $fields['post_title']['fullwidth'] = false;
            }

            if(isset($fields['goal'])){
                $fields['goal']['priority'] = 2;
                $fields['goal']['label'] = __('Fundraising Goal', 'pp-toolkit');
                $fields['goal']['required'] = true;
                $fields['goal']['fullwidth'] = false;
                $fields['goal']['editable'] = true;
            }

            $fields['post_date'] = [
                'priority'      => 3,
                'label'         => __('Start Date', 'pp-toolkit'),
                'type'          => 'datepicker',
                'required'      => true,
                'fullwidth'     => false,
                'value'         => $this->get_field_value('post_date'),
                'data_type'     => 'core',
                'editable'      => true
            ];

            $fields[ 'end_date' ] = [
                'priority'      => 4, 
                'label'         => __( 'Campaign End Date', 'pp-toolkit' ), 
                'type'          => 'datepicker',
                'required'      => true,
                'fullwidth'     => false,
                'value'         => $this->get_field_value('end_date'),
                //'value'       => date( 'F j, Y', strtotime($this->get_campaign()->get( 'end_date' ))), //works from db
                //'value'       => $this->get_campaign()->get( '_campaign_end_date' ),
                //'value'       => $form->get_campaign_value( 'end_date' ),
                'data_type'     => 'core',
                'editable'      => true
            ];

            if(isset($fields['description'])){
                $fields['description']['priority'] = 5;
                $fields['description']['placeholder'] = "A short description of your campaign that appears at the top of your campaign page (no paragraphs).";
            }

            if(isset($fields['post_content'])){
                $fields['post_content']['label'] = "Additional Details About Your Philanthropy";
                $fields['post_content']['value'] = $this->get_campaign_value( 'post_content' );
                // $fields['post_content']['editor']['tinymce'] = array(
                    // 'toolbar1'  => 'bold,italic,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink',
                // );
            }

            // if(isset($fields['image'])){
                $fields['image']['priority'] = 16;
                $fields['image']['required'] = true;
                $fields['image']['label'] = __('Featured Image (.jpg or .png)', 'pp-toolkit');
                $fields['image']['type'] = 'image-crop';
                $fields['image']['fullwidth'] = false;
                $fields['image']['editor-setting'] = array(
                    'expected-height' => 400,
                    'expected-width' => 1200,
                    'max-preview-width' => '1200px',
                    'placeholder' => '(Ideal size is 1200px wide by 400px high)',
                );
            // }

            if(isset($fields['video'])){
                $fields['video']['priority'] = 17;
                $fields['video']['label'] = __('Featured Video', 'pp-toolkit');
                $fields['video']['placeholder'] = 'Embed a featured video by entering the "share" URL from almost any source. (Examples: https://youtu.be/videoID or https://www.facebook.com/videoID)';
                $fields['video']['fullwidth'] = false;
            }

            $fields[ 'media_embed' ] = [
                'priority'      => 18, 
                'label'         => sprintf(__( 'Embed Additional Media', 'pp-toolkit' )),
                'placeholder'   => __('Embed a video, picture, music, document, etc. by entering the "share" URL from almost any source. (Examples: https://youtu.be/videoID or https://www.instagram.com/p/Bb8SRz9HGim/?taken-by=beyonce) ', 'pp-toolkit'),
                'type'          => 'textarea',
                'required'      => false,
                'fullwidth'     => true,
                'value'         => $this->get_field_value('media_embed'),
                'data_type'     => 'meta',
            ];
            
            
            if ( $form->get_campaign() ) {
                //$fields[ 'end_date' ][ 'value' ] = date( 'm/d/y', strtotime( $this->get_campaign()->get( 'end_date' ) ) );
            }

            // echo "<pre>";
            // print_r($fields);
            // echo "</pre>";

            $fields = apply_filters( 'pp_toolkit_campaign_submission_campaign_fields', $fields, $this, $form );

            uasort( $fields, 'charitable_priority_sort' );

            return $fields;
        }

        public function get_fundraising_options_fields(Charitable_Ambassadors_Campaign_Form $form){
            $donation_fields = $form->get_donation_options_fields();

            // remove explanation
            if(isset($donation_fields['donation_options']))
                unset($donation_fields['donation_options']);

            // change type / template
            if(isset($donation_fields['suggested_donations']['type'])){
                $donation_fields['suggested_donations']['type'] = 'donation-levels';
                $donation_fields['suggested_donations']['icon_url'] = pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-donation.png';
            }

            $fundraising_fields = array(
                'suggested_donations' => [
                    // 'legend'   => __('Donations', 'pp-toolkit'),
                    'type'     => 'fieldset',
                    'priority' => 49,
                    'page'     => 'campaign_details',
                    'fields'   => $donation_fields
                ],
                'event_fields' => [
                    // 'legend'   => __('Events', 'pp-toolkit'),
                    'type'     => 'fieldset',
                    'priority' => 50,
                    'page'     => 'campaign_details',
                    'fields'   => [
                        'event'       => [
                            'type'     => 'event',
                            'icon_url' => pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-event.png',
                            'priority' => 44,
                            'value'    => $this->get_events()
                        ]
                    ]
                ],
                'merchandise_fields' => [
                    // 'legend'   => __('Merchandise', 'pp-toolkit'),
                    'type'     => 'fieldset',
                    'priority' => 51,
                    'page'     => 'campaign_details',
                    'fields'   => [
                        'merchandise' => [
                            'type'     => 'merchandise',
                            'icon_url' => pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-merchandise.png',
                            'priority' => 34,
                            'value'    => $this->get_merchandise()
                        ]
                    ]
                ],
                'team_fundraising' => [
                    // 'legend'   => 'team_fundraising',
                    'type'     => 'fieldset',
                    'priority' => 52,
                    'page'     => 'campaign_details',
                    'fields'   => [
                        'referrer'  => [
                            'type'     => 'team-fundraising',
                            'icon_url' => pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-team-fundraising.png',
                            'priority' => 50,
                            'value'    => $this->get_field_value('team_fundraising'),
                            'data_type'     => 'meta',
                        ]
                    ]
                ],
                'referrer' => [
                    // 'legend'   => 'referrer',
                    'type'     => 'fieldset',
                    'priority' => 53,
                    'page'     => 'campaign_details',
                    'fields'   => [
                        'referrer'  => [
                            'type'     => 'referrer',
                            'icon_url' => pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-referrer.png',
                            'priority' => 50,
                            'value'    => $this->get_field_value('referrer'),
                            'data_type'     => 'meta',
                        ]
                    ]
                ],
                'sponsors' => [
                    // 'legend'   => 'Sponsors',
                    'type'     => 'fieldset',
                    'priority' => 54,
                    'page'     => 'campaign_details',
                    'fields'   => [
                        'sponsors'  => [
                            'type'     => 'sponsors',
                            'icon_url' => pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-sponsors.png',
                            'priority' => 50,
                            'value'    => $this->get_field_value('sponsors'),
                            'data_type'     => 'meta',
                        ]
                    ]
                ],
                'volunteers' => [
                    // 'legend'   => 'Volunteers',
                    'type'     => 'fieldset',
                    'priority' => 55,
                    'page'     => 'campaign_details',
                    'fields'   => [
                        'volunteers'  => [
                            'type'     => 'volunteers',
                            'icon_url' => pp_toolkit()->directory_url . 'assets/img/user-dashboard/icon-volunteers.png',
                            'priority' => 50,
                            'value'    => $this->get_field_value('volunteers'),
                            'data_type'     => 'meta',
                        ]
                    ]
                ]
            );

            // echo "<pre>";
            // print_r($fundraising_fields);
            // echo "</pre>";

            $fundraising_fields = apply_filters( 'pp_toolkit_campaign_submission_fundraising_options_fields', $fundraising_fields, $this, $form );

            uasort( $fundraising_fields, 'charitable_priority_sort' );

            return $fundraising_fields;
        }

        public function get_account_options_fields(){

            $user = new Charitable_User( get_current_user_id() );

            $fields_class = (is_user_logged_in()) ? '' : 'your-account-fields hidden';

            $account_fields = array(
                'first_name' => array(
                    'label'         => __( 'First name', 'charitable-ambassadors' ),
                    'type'          => 'text',
                    'priority'      => 42,
                    'required'      => true,
                    'value'         => $this->get_user_value( 'first_name' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                'last_name' => array(
                    'label'         => __( 'Last name', 'charitable-ambassadors' ),
                    'type'          => 'text',
                    'priority'      => 44,
                    'required'      => true,
                    'value'         => $this->get_user_value( 'last_name' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                'user_email' => array(
                    'label'         => __( 'Email', 'charitable-ambassadors' ),
                    'type'          => 'email',
                    'required'      => true,
                    'fullwidth'      => true,
                    'priority'      => 46,
                    'value'         => $this->get_user_value( 'user_email' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                // 'city' => array(
                //     'label'         => __( 'City', 'charitable-ambassadors' ),
                //     'type'          => 'text',
                //     'priority'      => 48,
                //     'required'      => false,
                //     'value'         => $this->get_user_value( 'donor_city' ),
                //     'data_type'     => 'user',
                // ),
                // 'state' => array(
                //     'label'         => __( 'State', 'charitable-ambassadors' ),
                //     'type'          => 'text',
                //     'priority'      => 50,
                //     'required'      => false,
                //     'value'         => $this->get_user_value( 'donor_state' ),
                //     'data_type'     => 'user',
                // ),
                // 'country' => array(
                //     'label'         => __( 'Country', 'charitable-ambassadors' ),
                //     'type'          => 'select',
                //     'options'       => charitable_get_location_helper()->get_countries(),
                //     'priority'      => 52,
                //     'required'      => false,
                //     'value'         => $this->get_user_value( 'donor_country', charitable_get_option( 'country' ) ),
                //     'data_type'     => 'user',
                // ),
                'chapter' => array(
                    'label'         => __( 'Fraternity/Sorority Name', 'pp-toolkit' ),
                    'type'          => 'text',
                    'priority'      => 50,
                    'required'      => true,
                    'fullwidth'     => false,
                    'value'         => $this->get_user_value( 'chapter' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                'organisation' => array(
                    'label'         => __( 'School Name', 'charitable-ambassadors' ),
                    'type'          => 'text',
                    'priority'      => 51,
                    'required'      => true,
                    'fullwidth'      => false,
                    'value'         => $this->get_user_value( 'organisation' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                'avatar' => array(
                    'label'         => __( 'Your Profile Photo', 'charitable-ambassadors' ),
                    'type'          => 'picture',
                    'uploader'          => true,
                    'size'          => 100,
                    'priority'      => 52,
                    'required'      => false,
                    'fullwidth'      => false,
                    'value'         => $this->get_user_value( 'avatar' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                'user_description' => array(
                    'label'         => __( 'About Your Chapter', 'charitable-ambassadors' ),
                    'type'          => 'textarea',
                    'priority'      => 53,
                    'required'      => false,
                    'value'         => $this->get_user_value( 'description' ),
                    'data_type'     => 'user',
                    'class'         => $fields_class,
                ),
                'agree_tos' => array(
                    'type'          => 'checkbox',
                    'label'         => sprintf(__('I agree to %s <a href="%s" target="_blank">%s</a> + <a href="%s" target="_blank">%s</a>', 'pp-toolkit'), get_bloginfo( 'name' ), home_url( 'terms-of-service' ), 'Terms of Service', home_url( 'privacy-policy' ), 'Privacy Policy' ),
                    'priority'      => 54,
                    'value'         => '1',
                    'checked'       => $this->get_field_value( 'agree_tos' ),
                    'data_type'     => 'meta',
                    'class'         => 'black_text',
                    'fullwidth'     => true,
                    'required'      => true,
                    'class'         => $fields_class,
                ),
            );

            if(!is_user_logged_in()){
                $account_fields['login'] = array(
                    'label'         => __('In order to save/preview or launch your campaign, you\'ll need to Sign Up or Login:', 'pp-toolkit'),
                    'type'          => 'login-signup-button',
                    'required'      => false, 
                    'fullwidth'     => true,
                    'priority'      => 9,
                    'value'         => get_current_user_id(),
                );
                $account_fields['not_login_type'] = array(
                    'type'          => 'hidden',
                    'required'      => false, 
                    'priority'      => 9,
                    'value'         => 'login',
                    'class'         => 'hidden',
                );
                $account_fields['explanation'] = array(
                    'type'          => 'paragraph',
                    'content'       => __('Already have an account? <a href="#" class="your-account-login">Login</a> ', 'pp-toolkit'),
                    'priority'      => 11,
                    'class'         => $fields_class . ' signup-form',
                );
                $account_fields['username'] = array(
                    'label'         => __('Username', 'pp-toolkit') ,
                    'type'          => 'text',
                    'priority'      => 12,
                    'required'      => true,
                    'fullwidth'     => true,
                    'class'         => $fields_class . ' signup-form',
                    'value'         => (isset( $_POST[ 'username' ] )) ? $_POST[ 'username' ] : '',
                    // 'data_type'     => 'meta',
                    // 'editable'      => true
                );
                $account_fields['password'] = array(
                    'label'         => __('Password', 'pp-toolkit') ,
                    'type'          => 'password',
                    'priority'      => 13,
                    'required'      => true,
                    'fullwidth'     => false,
                    'class'         => $fields_class . ' signup-form',
                    // 'value'         => (isset( $_POST[ 'password' ] )) ? $_POST[ 'password' ] : '',
                );
                $account_fields['repeat_password'] = array(
                    'label'         => __('Confirm Password', 'pp-toolkit') ,
                    'type'          => 'password',
                    'priority'      => 14,
                    'required'      => true,
                    'fullwidth'     => false,
                    'class'         => $fields_class . ' signup-form',
                    // 'value'         => (isset( $_POST[ 'repeat_password' ] )) ? $_POST[ 'repeat_password' ] : '',
                );
            }
            

            $account_fields = apply_filters( 'pp_toolkit_campaign_submission_account_options_fields', $account_fields, $this );

            // echo "<pre>";
            // print_r($account_fields);
            // echo "</pre>";

            uasort( $account_fields, 'charitable_priority_sort' );

            return $account_fields;
        }

        public function get_payout_options_fields(Charitable_Ambassadors_Campaign_Form $form){
            
            $payout_fields = [
                'payout_options' => [
                    'label'         => __('Payout Options', 'pp-toolkit') ,
                    'type'     => 'payout-options',
                    'priority' => 10,
                    'value'    => $this->get_field_value('payout_options'),
                    'required' => true,
                    'fullwidth' => true,
                    'class' => 'update_payout_fields',
                    'options'           => array(
                        'check' => array(
                            'label' => __( 'Check', 'pp-toolkit' ),
                            'payment-to' => 'YOU',
                            'icon'  => pp_toolkit()->directory_url . 'assets/img/user-dashboard/payment-check.png'
                        ),
                        'venmo' => array(
                            'label' => __( 'Venmo', 'pp-toolkit' ),
                            'payment-to' => 'YOU',
                            'icon'  => pp_toolkit()->directory_url . 'assets/img/user-dashboard/payment-venmo.png'
                        ),
                        'direct' => array(
                            'label' => __( 'Direct', 'pp-toolkit' ),
                            'payment-to' => 'NONPROFIT',
                            'icon'  => pp_toolkit()->directory_url . 'assets/img/user-dashboard/payment-directly.png'
                        )
                    ),
                    'data_type'     => 'meta',
                    // 'default'           => '0',
                ]
            ];

            $payout_fields = apply_filters( 'pp_toolkit_campaign_submission_payout_options_fields', $payout_fields, $this, $form );

            uasort( $payout_fields, 'charitable_priority_sort' );

            return $payout_fields;
        }

        public function get_finished_fields(Charitable_Ambassadors_Campaign_Form $form){

            if ( false === $form->get_campaign() || 'draft' == $form->get_campaign()->post_status ) {
                $primary_text = apply_filters( 'charitable_ambassadors_form_submission_buttons_primary_new_text', __( 'Submit Campaign', 'charitable-ambassadors' ), $this );
            } else {
                $primary_text = apply_filters( 'charitable_ambassadors_form_submission_buttons_primary_update_text', __( 'Update Campaign', 'charitable-ambassadors' ), $this );
            }

            $secondary_text = apply_filters( 'charitable_ambassadors_form_submission_buttons_preview_text', __( 'Save &amp; Preview', 'charitable-ambassadors' ) );

            $finished_fields = [
                'button' => [
                    'type'     => 'finished-button',
                    'priority' => 10,
                    'buttons'           => array(
                        'preview-campaign' => array(
                            'label' => $secondary_text,
                            'icon'  => pp_toolkit()->directory_url . 'assets/img/user-dashboard/preview.png'
                        ),
                        'submit-campaign' => array(
                            'label' => $primary_text,
                            'icon'  => pp_toolkit()->directory_url . 'assets/img/user-dashboard/launch.png'
                        ),
                    ),
                ]
            ];

            return $finished_fields;
        }

        /**
         * Add end_date to the campaign meta fields to be saved.
         * 
         */
        public function save_end_date( $fields, $submitted ) {

            /* If the campaign is already published and
             * a length is not submitted, we're not changing
             * the end date.
             */
            // if ( ! $context['is_already_published'] || array_key_exists( 'length', $submitted ) ) {
                $fields['meta']['end_date'] = 'date';
            // }

            return $fields;
        }

        public function check_form_missing_fields($missing, Charitable_Form $form, $fields, $submitted){
            
            if($form->get_form_action() != 'save_campaign'){
                return $missing;
            }

            foreach ( $form->get_required_fields( $fields ) as $key => $field ) {

                $label = isset( $field['label'] ) ? $field['label'] : $key;

                if($field['type'] == 'checkbox'){
                    $value  = (isset($submitted[ $key ])) ? $submitted[ $key ] : '';
                    if((empty($value) || ($value != $field['value']))){
                        $missing[] = $label;
                    }

                }
            }

            // check password
            if( !is_user_logged_in() && isset($submitted['not_login_type']) && ($submitted['not_login_type'] == 'register') ){
                if(username_exists($submitted['username'])){
                    $missing[] = __( 'Sorry, that username already exists!' );
                } elseif( email_exists( $submitted['user_email'] ) ){
                    $missing[] = __( 'Sorry, that email address is already used!' );
                } elseif( empty($submitted['password']) || empty($submitted['repeat_password']) || ($submitted['password'] != $submitted['repeat_password']) ){
                    $missing[] = __('Please confirm your password!', 'pp-toolkit');
                }
            }

            // if(!empty($missing)){
            //     echo "<pre>";
            //     print_r($submitted);
            //     exit();

            // }

            return $missing;
        }

        /**
         * Return the current user's Charitable_User object.
         *
         * @return  Charitable_User
         * @access  public
         * @since   1.0.0
         */
        public function get_user() {
            if (!isset($this->user)) {
                $user_id = (is_user_logged_in()) ? wp_get_current_user() : 0;
                $this->user = new Charitable_User($user_id);
            }

            return $this->user;
        }

        /**
         * Return the current campaign's Charitable_Campaign object.
         *
         * @return  Charitable_Campaign|false
         * @access  public
         * @since   1.0.0
         */
        public function get_campaign() {
            if (!isset($this->campaign)) {
                $campaign_id = get_query_var('campaign_id', false);
                $this->campaign = $campaign_id ? new Charitable_Campaign($campaign_id) : false;
            }

            return $this->campaign;
        }
        
        /**
         * Return any products that have been created that are linked to this campaign as "benefactors".
         *
         * @return  array       Empty array if no merchandise.
         * @access  public
         * @since   1.0.0
         */
        public function get_merchandise() {
            /**
             * If the submitted data was set in the session, grab it.
             */
            if (is_array(charitable_get_session()->get('submitted_merchandise'))) {
                $submitted = charitable_get_session()->get('submitted_merchandise');

                charitable_get_session()->set('submitted_merchandise', false);
            } elseif (isset($_POST['merchandise'])) {
                $submitted = $_POST['merchandise'];
            }

            if (isset($submitted)) {
                $merchandise = [];

                foreach ($submitted as $merch) {
                    $merchandise[] = ['POST' => $merch];
                }

                return $merchandise;
            }

            if (false === $this->get_campaign()) {
                return [];
            }

            $merchandise = [];
            
            $downloads = charitable()->get_db_table('edd_benefactors')->get_single_download_campaign_benefactors($this->get_campaign()->ID);
            $download_ids = wp_list_pluck($downloads, 'edd_download_id');

            foreach ($download_ids as $key => $download_id) {
                if (has_term('Merchandise', 'download_category', $download_id)) {
                    $merchandise[ $download_id ] = $downloads[ $key ];
                }
            }
           

            return $merchandise;
        }

        /**
         * Return any events that have been created that are linked to this campaign.
         *
         * @return  array       Empty array if no events.
         * @access  public
         * @since   1.0.0
         */
        public function get_events() {
            /**
             * If the submitted data was set in the session, grab it.
             */
            if (is_array(charitable_get_session()->get('submitted_events'))) {
                $submitted = charitable_get_session()->get('submitted_events');

                charitable_get_session()->set('submitted_events', false);
            } /**
             * Otherwise check in the $_POST array.
             */
            elseif (isset($_POST['event'])) {
                $submitted = $_POST['event'];
            }

            if (isset($submitted)) {
                $events = [];

                foreach ($submitted as $event) {
                    $events[] = ['POST' => $event];
                }

                return $events;
            }

            if (false === $this->get_campaign()) {
                return [];
            }

            $events = get_post_meta($this->get_campaign()->ID, '_campaign_events', true);

            if (!is_array($events)) {
                $events = [$events];
            }

            return $events;
        }

        /**
         * Return the value for a particular field.
         *
         * @param   string $key
         *
         * @return  mixed
         * @access  public
         * @since   1.0.0
         */
        public function get_field_value($key) {
            if (isset($_POST[ $key ])) {
                return $_POST[ $key ];
            }

            $value = "";

            switch ($key) {
                case 'post_date' :
                    if ($this->get_campaign()) {
                        $value = date('F j, Y', strtotime($this->get_campaign()->post_date));
                    }
                    break;

                case 'end_date' :
                    if ($this->get_campaign()) {
                        //$value = date('F j, Y', strtotime($this->get_campaign()->get('end_date')));
                         //* GUM code edit
                        //$value = date( 'F j, Y', strtotime($this->get_campaign()->get( 'end_date' )));
                        $end_date = get_post_meta( $this->get_campaign()->ID, '_campaign_end_date', true );
                        $value = date( 'F j, Y', strtotime($end_date));
                    }
                    break;

                case 'impact_goal' :
                    if ($this->get_campaign()) {
                        $value = $this->get_campaign()->get('impact_goal');
                    }
                    break;

                case 'team_fundraising' :
                    if ($this->get_campaign()) {
                        $value = (!empty($this->get_campaign()->get('team_fundraising'))) ? $this->get_campaign()->get('team_fundraising') : '';
                    }
                    break;

                case 'sponsors' :
                    if ($this->get_campaign()) {
                        $sponsors = (!empty($this->get_campaign()->get('sponsors'))) ? $this->get_campaign()->get('sponsors') : array();
                        $value = array_filter( array_map('array_filter', $sponsors ) );
                    }
                    break;

                case 'volunteers' :
                    if ($this->get_campaign()) {
                        $value = $this->get_campaign()->get('volunteers');
                    }
                    break;

                case 'referrer' :
                    if ($this->get_campaign()) {
                        $value = (!empty($this->get_campaign()->get('referrer'))) ? $this->get_campaign()->get('referrer') : '';
                    }
                    break;

                case 'agree_tos' :
                    if ($this->get_campaign()) {
                        $value = $this->get_campaign()->get('agree_tos');
                    } elseif(is_user_logged_in()){
                        // default to check if user logged in 
                        $value = 1;
                    }
                    break;

                case 'payout_options' :
                    if ($this->get_campaign()) {
                        $value = $this->get_campaign()->get('payout_options');
                    }
                    break;

                case 'media_embed' :
                    if ($this->get_campaign()) {
                        $value = $this->get_campaign()->get('media_embed');
                    }
                    break;

            }

            return $value;
        }

        /**
         * Filter core campaign data before saving.
         *
         * @param   array $values
         * @param   int   $user_id
         * @param   array $submitted
         *
         * @return  array
         * @access  public
         * @since   1.0.0
         */
        public function campaign_core_date($values, $user_id, $submitted) {
            if (isset($submitted['post_date'])) {
                $values['post_date'] = current_time( 'mysql' );
                // $values['post_date'] = date('Y-m-d H:i:s');
                $values['edit_date'] = $values['post_date'];
                //$values['end_date'] = date('Y-m-d 00:00:00', strtotime($submitted['end_date']));
                // $values['post_date_gmt'] = get_gmt_from_date($values['post_date']);
            }

            return $values;
        }


        /**
         * Display the merchandise form. This is called by an AJAX action.
         *
         * @return  void
         * @access  public
         * @since   1.0.0
         */
        public function render_merchandise_form() {
            /* Run a security check first to ensure we initiated this action. */
            check_ajax_referer('pp-merchandise-form', 'nonce');

            $template = new PP_Toolkit_Template('form-fields/merchandise-form.php', false);
            $template->set_view_args([
                                         'form'  => new PP_Merchandise_Form(),
                                         'index' => $_POST['index']
                                     ]);
            $template->render();

            wp_die();
        }

        /**
         * Display the event form. This is called by an AJAX action.
         *
         * @return  void
         * @access  public
         * @since   1.0.0
         */
        public function render_event_form() {
            /* Run a security check first to ensure we initiated this action. */
            check_ajax_referer('pp-event-form', 'nonce');

            $template = new PP_Toolkit_Template('form-fields/event-form.php', false);
            $template->set_view_args([
                                         'form'  => new PP_Event_Form(),
                                         'index' => $_POST['index']
                                     ]);
            $template->render();

            wp_die();
        }

        /**
         * Display the ticket form. This is called by an AJAX action.
         *
         * @return  void
         * @access  public
         * @since   1.0.0
         */
        public function render_ticket_form() {
            /* Run a security check first to ensure we initiated this action. */
            check_ajax_referer('pp-ticket-form', 'nonce');

            $template = new PP_Toolkit_Template('form-fields/ticket-form.php', false);
            $template->set_view_args([
                                         'form'  => new PP_Ticket_Form($_POST['namespace'], $_POST['event_id']),
                                         'index' => $_POST['index']
                                     ]);
            $template->render();

            wp_die();
        }

        public function save_imagedata(){

            $replace = false; // may prevent duplicate, to save storage
            $remove_old = true;

            $result = array(
                'success' => false,
                'message' => __('Failed to save image.', 'pp-toolkit'),
                'result' => array()
            );

            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'save_imagedata' ) ) {
                wp_send_json( $result );
            }

            // $fileName = $_FILES['imgfile']['name'];
            // $fileType = $_FILES['imgfile']['type'];
            $attach_id = absint( $_REQUEST['image_id'] );
            if($replace && !empty($attach_id)){
                $filename = basename( get_attached_file( $attach_id ) );
            } else {
                $filename = md5( $filename . microtime() ) . '_' . uniqid() . '.jpeg'; // add extension, we will upload later
            }
            
            $_FILES['imgfile']['name'] = $filename;

            require_once( ABSPATH . 'wp-admin/includes/file.php' );

            if($remove_old){
                // remove previous images
                wp_delete_attachment( $attach_id, true );
            }

            $args = array('test_form' => false );
            
            if($replace){
                $args['unique_filename_callback'] = 'pp_unique_filename_callback';
            }

            $uploaded      = wp_handle_upload(  $_FILES['imgfile'], $args ); 

            if ( isset( $uploaded['error'] ) ) {

                $result['success'] = false;
                $result['message'] = $uploaded['error'];
                $result['result'] = $_FILES['imgfile'];

            } else {
                $upload_dir = wp_upload_dir();
                $filename = $uploaded['file'];
                $attachment = array(
                    'post_mime_type' => $uploaded['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'guid' => $upload_dir['url'] . '/' . basename($filename)
                );

                if( empty($attach_id) || !$replace ){
                    $attach_id = wp_insert_attachment( $attachment, $filename, 0 );
                }
                
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                $result['success'] = true;
                $result['message'] = __('Image saved.', 'pp-toolkit');
                $result['result'] = array(
                    'id' => $attach_id,
                    'url' => $uploaded['url']
                );
            }

            wp_send_json( $result );
        }

        /**
         * Save image from datauri (not used now)
         * we changed to use blob and formdata to avoin error to long post data
         * @param  [type] $datauri  [description]
         * @param  [type] $filename [description]
         * @return [type]           [description]
         */
        private function save_image_from_datauri($datauri, $filename = false ){

            @ini_set( 'upload_max_size' , '64M' );
            @ini_set( 'post_max_size', '64M');
            @ini_set( 'max_execution_time', '300' );
            
            if(!$filename)
                $filename = uniqid();

            $filename         .= '.jpeg'; // add extension
            $img = str_replace('data:image/jpeg;base64,', '', $datauri);
            $img = str_replace(' ', '+', $img);
            $decoded          = base64_decode($img) ;

            $upload_dir       = wp_upload_dir();
            $upload_path      = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

            $hashed_filename  = md5( $filename . microtime() ) . '_' . $filename;

            $image_upload     = file_put_contents( $upload_path . $hashed_filename, $decoded );

            //HANDLE UPLOADED FILE
            if( !function_exists( 'wp_handle_sideload' ) ) {
              require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            // generate $file
            $file             = array();
            $file['error']    = '';
            $file['tmp_name'] = $upload_path . $hashed_filename;
            $file['name']     = $hashed_filename;
            $file['type']     = 'image/png';
            $file['size']     = filesize( $upload_path . $hashed_filename );

            // upload file to server
            $file_return      = wp_handle_sideload( $file, array( 'test_form' => false ) );

            $filename = $file_return['file'];
            $attachment = array(
                'post_mime_type' => $file_return['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit',
                'guid' => $upload_dir['url'] . '/' . basename($filename)
            );

            $attach_id = wp_insert_attachment( $attachment, $filename, 0 );

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            return $attach_id;
        }

        public function do_login_on_create_campaign(){

             $result = array(
                'success' => false,
                'data' => array()
            );

            if ( !isset($_REQUEST['nonce']) || ! wp_verify_nonce( $_REQUEST['nonce'], 'login_on_create_campaign' ) ){
                $result['message'] = __('Failed to login!', 'pp-toolkit');
                wp_send_json( $result );
            }

            $pp_user = PP_Users::init();
            remove_action( 'wp_authenticate', array($pp_user, 'pp_catch_empty_user'), 1 );
            remove_action( 'wp_login_failed', array($pp_user, 'pp_front_end_login_fail') );

            // $creds = array(
            //     'user_login' => $_REQUEST['user_login'],
            //     'user_password' => $_REQUEST['user_pass'],
            //     'remember' => true,
            // );
            // $signon = wp_signon( $creds, false );

            $signon = Charitable_User::signon( $_REQUEST['user_login'], $_REQUEST['user_pass'] );

            if ( is_wp_error( $signon ) ) {
                $result['message'] = '<div class="charitable-notice charitable-notice-error">'.$signon->get_error_message().'</div>';

            } elseif(!empty($signon->ID)) {

                wp_set_current_user($signon->ID); 

                $user = new Charitable_User($signon->ID);

                ob_start();

                $image = $user->get_avatar( 100 );
                $is_src = false !== strpos( $image, 'img' );
                ?>
                <li <?php if ( ! $is_src ) : ?>data-attachment-id="<?php echo $image ?>"<?php endif ?>>
                    <a href="#" class="remove-image button"><?php _e( 'Remove', 'charitable' ) ?></a>
                    <?php if ( $is_src ) : 
                        echo $image;
                    else : ?>
                        <input type="hidden" name="<?php echo $field[ 'key' ] . $multiple ?>" value="<?php echo $image ?>" />
                        <?php echo wp_get_attachment_image( $image, $size ) ?>
                    <?php endif ?>
                </li>
                <?php
                $avatar_html = ob_get_clean();

                // build data
                $user_data = array(
                    'user_id' => $user->ID,
                    'first_name' => ( $user->has_prop( 'first_name' ) ) ? $user->__get( 'first_name' ) : '',
                    'last_name' => ( $user->has_prop( 'last_name' ) ) ? $user->__get( 'last_name' ) : '',
                    'user_email' => ( $user->has_prop( 'user_email' ) ) ? $user->__get( 'user_email' ) : '',
                    'chapter' => ( $user->has_prop( 'chapter' ) ) ? $user->__get( 'chapter' ) : '',
                    'organisation' => ( $user->has_prop( 'organisation' ) ) ? $user->__get( 'organisation' ) : '',
                    'avatar_html' => $avatar_html,
                    'user_description' => ( $user->has_prop( 'user_description' ) ) ? $user->__get( 'user_description' ) : '',
                );

                $result['success'] = true;
                $result['data'] = $user_data;
            }

            wp_send_json( $result );
        }

        /**
         * Redirect the form submission to the edit page (used when some required fields are missing in the event/merchandise forms).
         *
         * @return  string
         * @access  public
         * @static
         * @since   1.1.0
         */
        public static function redirect_submission_to_edit_page($url, $submitted, $campaign_id) {
            /**
             * Set notices to the session so they persist to the next page load.
             */
            charitable_get_session()->add_notices();

            return charitable_get_permalink('campaign_editing_page', ['campaign_id' => $campaign_id]);
        }

        public function maybe_need_create_user($submitted, $fields, Charitable_Ambassadors_Campaign_Form $form){
            
            if( is_user_logged_in() ){
                return $submitted;
            }

            /**
             * Valdiation already done on `check_form_missing_fields`
             */

            if(!isset($_POST['not_login_type']) || ($_POST['not_login_type'] != 'register') )
                return $submitted;

            // user login
            if(isset($submitted['username'])){
                $submitted['user_login'] = $submitted['username'];
                // unset($submitted['username']);
            }

            // password
            if(isset($submitted['password'])){
                $submitted['user_pass'] = $submitted['password'];
                // unset($submitted['password']);

                // if(isset($submitted['repeat_password'])) 
                    // unset($submitted['repeat_password']);
            }

            add_filter( 'charitable_auto_login_after_registration', '__return_true', 100 );

            // echo "<pre>";
            // print_r($submitted);
            // echo "</pre>";
            // exit();



            return $submitted;
        }


        public function add_default_team_fundraising_value($submitted, $fields, Charitable_Ambassadors_Campaign_Form $form){

            if( !isset($submitted['team_fundraising']) ){
                $submitted['team_fundraising'] = 'off';
            }

            return $submitted;
        }


        public function add_missing_volunteers_value($submitted, $fields, Charitable_Ambassadors_Campaign_Form $form){

            // echo "<pre>";
            // print_r($submitted);
            // echo "</pre>";
            // exit();

            if( !isset($submitted['volunteers']) ){
                $submitted['volunteers'] = array();
            }

            return $submitted;
        }

        // public function maybe_create_user(Charitable_Ambassadors_Campaign_Form $form){

        //     if(is_user_logged_in())
        //         return;

        //     if(!isset($_POST['not_login_type']) || ($_POST['not_login_type'] != 'register') )
        //         return;

        //     /* Organize fields into multi-dimensional array of core, meta, taxonomy and user data. */
        //     // $fields = array();

        //     // foreach ( $form->get_merged_fields() as $key => $field ) {
        //     //     $fields = $form->sort_field_by_data_type( $key, $field, $fields );
        //     // }

        //     // /* Allow plugins/themes to filter the submitted values prior to saving. */
        //     // $submitted = apply_filters( 'charitable_campaign_submission_values', $_POST, $fields, $form );

        //     // /* Save user data */
        //     // // $user_id = $form->save_user_data( $fields, $submitted );

        //     // echo "<pre>";
        //     // print_r($fields);
        //     // echo "</pre>";
        //     // echo "<pre>";
        //     // print_r($submitted);
        //     // echo "</pre>";
        //     // exit();

        //     $user = new Charitable_User();
        //     $user_submitted = $_POST;
        //     $allow_register = true;
        //     $error = false;

        //     if( empty($_POST['password']) || empty($_POST['repeat_password']) || ($_POST['password'] != $_POST['repeat_password']) ){
        //         $allow_register = false;
        //         $error = __('Please confirm your password!', 'pp-toolkit');
        //     }

        //     if($allow_register){
        //         if(isset($_POST['username']) && !empty($_POST['username'])){
        //             $user_submitted['user_login'] = $_POST['username'];
        //         }
                
        //         $user_submitted['user_pass'] = $_POST['password'];

        //         add_filter( 'charitable_auto_login_after_registration', '__return_true', 100 );

        //         $user_id = $user->update_core_user($user_submitted);

        //         if(empty($user_id)){
        //             $allow_register = false;
        //             // $error = charitable_get_notices()->get_errors();
        //         } else {
        //             wp_set_current_user($user_id); 
        //         }
        //     }

        //     if(!$allow_register){
        //         if(!empty($error)){

        //             if(is_array($error)){
        //                 $error = implode('<br>', $error);
        //             }

        //             charitable_get_notices()->add_error($error);
        //         }

        //         remove_action( 'charitable_campaign_submission_save_page_campaign_details', array( 'Charitable_Ambassadors_Campaign_Form', 'save_campaign_details' ) );
        //         return;
        //     }
        // }

        /**
         * Merge field on fieldset
         * @param  [type]                               $fields    [description]
         * @param  [type]                               $submitted [description]
         * @param  Charitable_Ambassadors_Campaign_Form $form      [description]
         * @return [type]                                          [description]
         */
        public function merge_fields_map($fields, $submitted, Charitable_Ambassadors_Campaign_Form $form){

            $_fields = array();
            foreach ( $form->get_merged_fields() as $key => $section ) {

                if ( isset( $section['fields'] ) ) {
                    $_fields = array_merge( $_fields, $section['fields'] );
                }
            }

            $new_fields = array();
            foreach ( $_fields as $key => $field ) {
                $new_fields = $form->sort_field_by_data_type( $key, $field, $new_fields );
            }

            // echo "<pre>";
            // print_r($fields);
            // echo "</pre>";

            // echo "<pre>";
            // print_r($new_fields);
            // echo "</pre>";

            // echo "<pre>";
            // print_r(array_replace_recursive($fields, $new_fields));
            // echo "</pre>";

            // echo "<pre>";
            // print_r($submitted);
            // echo "</pre>";
            // exit();
            
            $fields = array_replace_recursive($fields, $new_fields);

            /**
             * Add empty user fields
             * so `save_user_data` function will be called for adding user role
             * @see  Charitable_Ambassadors_Campaign_Form::save_user_data [<description>]
             */
            if(!isset($fields['user'])){
                $fields['user'] = array();
            }

            return $fields;
        }

        /**
         * Returns the value of a campaign in particular key.
         * we need to format post content to remove backslash
         */
        public function get_campaign_value( $key ) {

            $campaign = $this->get_campaign();
            $value = '';

            if( isset( $_POST[ $key ] ) ){

                $value = $_POST[ $key ];

                if($key == 'post_content'){
                    $value = stripslashes($value);
                }

                
            } elseif ( $campaign ) {
                switch ( $key ) {
                    case 'post_content' :
                        $value = stripslashes($campaign->post_content);
                        break;

                    default :
                        $data = $campaign->get( 'submission_data' );

                        if ( ! is_array( $data ) ) {
                            $value = '';
                        } else {
                            // Fallback.
                            $value = array_key_exists( $key, $data ) ? $data[ $key ] : $campaign->get( $key );
                        }
                }//end switch
            }//end if

            return apply_filters( 'charitable_campaign_value', $value, $key, $campaign );
        }

        /**
         * Returns the value of a particular key.
         *
         * @param   string $key     The key to search for.
         * @param   mixed  $default The default value to return if none is found.
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

                    case 'avatar' :
                        $value = (is_user_logged_in()) ? $user->get_avatar( 100 ) : '';
                        break;

                    default :
                        if ( $user->has_prop( $key ) ) {
                            $value = $user->__get( $key );
                        }
                }
            }

            return apply_filters( 'charitable_campaign_submission_user_value', $value, $key, $user );
        }

        /**
         * Load all required custom campaign forms
         * @return [type] [description]
         */
        public function includes(){

            // Load Events
            include_once( 'charitable-forms/events/class-pp-event-form.php' );

            // Load Tickets
            include_once( 'charitable-forms/tickets/class-pp-ticket-form.php' );

            // Load Merchandise
            include_once( 'charitable-forms/merchandise/class-pp-merchandise-form.php' );

            // load sponsor options
            // include_once( 'charitable-forms/sponsors/class-pp-sponsors-form.php' );

            // load payout options
            include_once( 'charitable-forms/payout/class-pp-payout-options-form.php' );

            // Load EDD Downloads (not sure, because on old code it was not included, but the class called from shortcode)
            include_once( 'charitable-forms/edd-product/class-pp-edd-product-form.php' );
        }
    }
    
endif; // End class_exists check