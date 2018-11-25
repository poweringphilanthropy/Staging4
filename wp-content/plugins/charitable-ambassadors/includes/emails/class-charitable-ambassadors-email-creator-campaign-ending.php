<?php
/**
 * Class that models the email that is sent to campaign creators after they submit their campaign.
 *
 * @version     1.1.0
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Email_Creator_Campaign_Ending
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Charitable_Ambassadors_Email_Creator_Campaign_Ending' ) ) : 

    /**
     * New Donation Email 
     *
     * @since       1.1.0
     */
    class Charitable_Ambassadors_Email_Creator_Campaign_Ending extends Charitable_Ambassadors_Email_Creator_Campaign_Submission {

        /**
         * @var     string
         */
        CONST ID = 'creator_campaign_ending';

        /**
         * Instantiate the email class, defining its key values.
         *
         * @param   mixed[]  $objects
         * @access  public
         * @since   1.1.0
         */
        public function __construct( $objects = array() ) {
            parent::__construct( $objects );

            $this->name = apply_filters( 'charitable_email_campaign_creator_name', __( 'Campaign Creator: Campaign Ending', 'charitable-ambassadors' ) );        
        }

        /**
         * Returns the current email's ID.  
         *
         * @return  string
         * @access  public
         * @static
         * @since   1.1.0
         */
        public static function get_email_id() {
            return self::ID;
        }

        /**
         * Add extra settings for the campaign ending email. 
         *
         * @param   array $settings
         * @return  array
         * @access  public
         * @static
         * @since   1.1.0
         */
        public static function add_email_settings( $settings ) {
            return array_merge( $settings, array(
                'days_before_end' => array(
                    'type'     => 'number',
                    'title'    => __( 'Number of Days Before Campaign Ends', 'charitable-ambassadors' ), 
                    'help'     => __( 'The email will be sent this number of days <em>before</em> the end of the campaign. Set it to 0 to send it on the day the campaign ends.', 'charitable-ambassadors' ),
                    'min'      => 0,
                    'priority' => 3,  
                    'default'  => 0
                ),
            ) );
        }

        /**
         * Return the default subject line for the email.
         *
         * @return  string
         * @access  protected
         * @since   1.1.0
         */
        protected function get_default_subject() {
            return __( 'Your campaign is ending soon', 'charitable-ambassadors' );   
        }

        /**
         * Return the default headline for the email.
         *
         * @return  string
         * @access  protected
         * @since   1.1.0
         */
        protected function get_default_headline() {
            return apply_filters( 'charitable_email_creator_campaign_ending_default_headline', __( 'Your Campaign is Ending Soon', 'charitable-ambassadors' ), $this );    
        }

        /**
         * Return the default body for the email.
         *
         * @return  string
         * @access  protected
         * @since   1.1.0
         */
        protected function get_default_body() {
            ob_start();
?>
<p><?php _e( 'Dear [charitable_email show=campaign_creator],', 'charitable-ambassadors' ) ?></p>
<p><?php _e( 'Your campaign, &ldquo;[charitable_email show=campaign_title]&rdquo;, is ending on [charitable_email show=campaign_end_date].', 'charitable-ambassadors' ) ?></p>
<p><?php _e( 'So far, you have raised [charitable_email show=campaign_donated_amount].', 'charitable-ambassadors' ) ?> [charitable_email show=campaign_achieved_goal success="<?php _e( 'Well done for already surpassing your fundraising target!', 'charitable-ambassadors' ) ?>" failure="<?php _e( "You haven't quite reached your fundraising target yet, but there's still time!", 'charitable-ambassadors' ) ?>"]</p>
<p><?php printf( __( 'Give your campaign a final push by sharing it with your network. Your campaign is online at %s.', 'charitable-ambassadors' ), '<a href="[charitable_email show=campaign_url]">[charitable_email show=campaign_url]</a>' ) ?></p>
<?php
            $body = ob_get_clean();

            return apply_filters( 'charitable_email_campaign_creator_default_body', $body, $this );
        }

        /**
         * Send emails for any campaigns ending X days from now, where X is the `days_before_end` setting.  
         *
         * @return  int The number of emails sent.
         * @access  public
         * @static
         * @since   1.1.0
         */
        public static function send_ending_campaign_emails() {      
            if ( ! charitable_get_helper( 'emails' )->is_enabled_email( self::get_email_id() ) ) {
                return 0;
            }        

            $days = charitable_get_option( array( 'emails_' . Charitable_Ambassadors_Email_Creator_Campaign_Ending::get_email_id(), 'days_before_end' ), 0 );

            $ending_date = date( 'Y-m-d', strtotime( '+' . $days . ' days' ) );

            $campaigns = Charitable_Campaigns::query( array(
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key'       => '_campaign_end_date',
                        'value'     => $ending_date,
                        'compare'   => 'LIKE',
                        'type'      => 'datetime'
                    )
                ), 
                'fields' => 'ids'
            ) );
			
            if ( ! $campaigns->have_posts() ) {
                return 0;
            }

            $count = 0;
            foreach ( $campaigns->posts as $campaign_id ) {

                $email = new Charitable_Ambassadors_Email_Creator_Campaign_Ending( array (
                    'campaign' => charitable_get_campaign( $campaign_id )
                ) );

                /**
                 * Don't resend the email.
                 */
                if ( $email->is_sent_already( $campaign_id ) ) {
                    continue;
                }

                $sent = $email->send();
                
                /**
                 * Log that the email was sent.
                 */
                if ( apply_filters( 'charitable_log_email_send', true, self::get_email_id(), $email ) ) {
                    $email->log( $campaign_id, $sent );
                }

                if ( $sent ) {
                    $count += 1;
                }

            }

            return $count;
        }
    }

endif;
