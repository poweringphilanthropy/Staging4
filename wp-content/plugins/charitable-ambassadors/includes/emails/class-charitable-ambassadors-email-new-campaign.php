<?php
/**
 * Class that models the new donation email.
 *
 * @version     1.0.0
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Email_New_Campaign
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Email_New_Campaign' ) ) :

	/**
	 * New Donation Email
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Email_New_Campaign extends Charitable_Email {

		/**
		 * @var     string
		 */
		const ID = 'new_campaign';

		/**
		 * Sets whether the email allows you to define the email recipients.
		 *
		 * @var     boolean
		 * @access  protected
		 * @since   1.1.0
		 */
		protected $has_recipient_field = true;

		/**
		 * A list of supported object types (campaigns, donations, donors, etc).
		 *
		 * @var     string[]
		 * @access  protected
		 * @since   1.0.0
		 */
		protected $object_types = array( 'campaign' );

		/**
		 * Instantiate the email class, defining its key values.
		 *
		 * @param   mixed[]  $objects
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $objects = array() ) {
			parent::__construct( $objects );

			$this->name = apply_filters( 'charitable_email_new_campaign_name', __( 'Admin: New Campaign Notification', 'charitable-ambassadors' ) );
		}

		/**
		 * Returns the current email's ID.
		 *
		 * @return  string
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function get_email_id() {
			return self::ID;
		}

		/**
		 * Return the default recipient for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_default_recipient() {
			return get_option( 'admin_email' );
		}

		/**
		 * Return the default subject line for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_default_subject() {
			return __( 'A new campaign has been submitted', 'charitable-ambassadors' );
		}

		/**
		 * Return the default headline for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_default_headline() {
			return apply_filters( 'charitable_email_new_campaign_default_headline', __( 'New Campaign', 'charitable-ambassadors' ), $this );
		}

		/**
		 * Return the default body for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_default_body() {
			ob_start();
?>
<p><?php _e( '[charitable_email show=campaign_creator] has just submitted a new campaign: &ldquo;[charitable_email show=campaign_title]&rdquo;.', 'charitable-ambassadors' ) ?></p>
<p><?php _e( 'Check it out:', 'charitable-ambassadors' ) ?></p>
<p><a href="[charitable_email show=campaign_url]">[charitable_email show=campaign_url]</a></p>
<?php
			$body = ob_get_clean();

			return apply_filters( 'charitable_email_new_campaign_default_body', $body, $this );
		}

		/**
		 * Static method that is fired right after a campaign is created, sending the new campaign notification.
		 *
		 * @param   array $submitted   Submitted campaign data.
		 * @param   int   $campaign_id The campaign ID.
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.0.2
		 */
		public static function send_email( $submitted, $campaign_id ) {

			if ( ! charitable_get_helper( 'emails' )->is_enabled_email( self::get_email_id() ) ) {
				return false;
			}

			/**
			 * This email should only be sent on a particular status, depending on
			 * whether auto-approvals are turned on.
			 */
			$status = charitable_get_option( 'auto_approve_campaigns', 0 ) ? 'publish' : 'pending';
			$status = apply_filters( 'charitable_ambassadors_send_new_campaign_email_on_status', $status );
	
			if ( $status != get_post_status( $campaign_id ) ) {
				return false;
			}

			$email = new Charitable_Ambassadors_Email_New_Campaign( array(
				'campaign' => charitable_get_campaign( $campaign_id ),
			) );

			/**
			 * Don't resend the email.
			 */
			if ( $email->is_sent_already( $campaign_id ) ) {
				return false;
			}

			$sent = $email->send();

			/**
			 * Log that the email was sent.
			 */
			if ( apply_filters( 'charitable_log_email_send', true, self::get_email_id(), $email ) ) {
				$email->log( $campaign_id, $sent );
			}

			return true;
		}
	}

endif;
