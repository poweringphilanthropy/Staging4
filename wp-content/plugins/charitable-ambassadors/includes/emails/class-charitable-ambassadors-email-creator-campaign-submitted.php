<?php
/**
 * Class that models the email that is sent to campaign creators after they submit their campaign.
 *
 * @version     1.1.0
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Email_Creator_Campaign_Submission
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Email_Creator_Campaign_Submission' ) ) :

	/**
	 * New Donation Email
	 *
	 * @since       1.1.0
	 */
	class Charitable_Ambassadors_Email_Creator_Campaign_Submission extends Charitable_Email {

		/**
		 * @var     string
		 */
		const ID = 'creator_campaign_submission';

		/**
		 * Sets whether the email allows you to define the email recipients.
		 *
		 * @var     boolean
		 * @access  protected
		 * @since   1.1.0
		 */
		protected $has_recipient_field = false;

		/**
		 * A list of supported object types (campaigns, donations, donors, etc).
		 *
		 * @var     string[]
		 * @access  protected
		 * @since   1.1.0
		 */
		protected $object_types = array( 'campaign' );

		/**
		 * Instantiate the email class, defining its key values.
		 *
		 * @param   mixed[]  $objects
		 * @access  public
		 * @since   1.1.0
		 */
		public function __construct( $objects = array() ) {
			parent::__construct( $objects );

			$this->name = apply_filters( 'charitable_email_creator_campaign_submission_name', __( 'Campaign Creator: New Campaign', 'charitable-ambassadors' ) );
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
		 * Add campaign editing url.
		 *
		 * @param   array $fields
		 * @param   Charitable_Email $email
		 * @return  array
		 * @access  public
		 * @since   1.1.0
		 */
		public function add_campaign_content_fields( $fields, Charitable_Email $email ) {
			if ( $email->get_email_id() != $this->get_email_id() ) {
				return $fields;
			}

			if ( ! in_array( 'campaign', $this->object_types ) ) {
				return $fields;
			}

			$fields = parent::add_campaign_content_fields( $fields, $email );

			$fields['campaign_edit_url'] = array(
				'description' => __( 'The link to edit the campaign', 'charitable-ambassadors' ),
				'callback' => array( $this, 'get_campaign_edit_link' ),
			);

			return $fields;
		}

		/**
		 * Return the link to edit the campaign.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_campaign_edit_link() {
			if ( ! $this->has_valid_campaign() ) {
				return '';
			}

			return charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => $this->campaign->ID ) );
		}

		/**
		 * Add fake link to edit campaign.
		 *
		 * @param   array $fields
		 * @param   Charitable_Email $email
		 * @return  array
		 * @access  public
		 * @since   1.1.0
		 */
		public function add_preview_campaign_content_fields( $fields, Charitable_Email $email ) {
			if ( $email->get_email_id() != $this->get_email_id() ) {
				return $fields;
			}

			if ( ! in_array( 'campaign', $this->object_types ) ) {
				return $fields;
			}

			$fields = parent::add_preview_campaign_content_fields( $fields, $email );
			$fields['campaign_edit_url'] = 'http://www.example.com/submit-campaign/123/edit/';
			return $fields;
		}

		/**
		 * Return the default recipient for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_recipient() {
			return get_user_by( 'ID', $this->get_campaign()->post_author )->user_email;
		}

		/**
		 * Return the default subject line for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_subject() {
			return __( 'Thank you for submitting your campaign', 'charitable-ambassadors' );
		}

		/**
		 * Return the default headline for the email.
		 *
		 * @return  string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_headline() {
			return apply_filters( 'charitable_email_campaign_creator_default_headline', __( 'Thanks for Submitting Your Campaign', 'charitable-ambassadors' ), $this );
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
<p><?php _e( 'Thank you for submitting your campaign, &ldquo;[charitable_email show=campaign_title]&rdquo;.', 'charitable-ambassadors' ) ?></p>
<p><?php _e( 'You can view your campaign online at <a href="[charitable_email show=campaign_url]">[charitable_email show=campaign_url]</a>,', 'charitable-ambassadors' ) ?></p>
<p><?php _e( 'If you would like to edit it, go to <a href="[charitable_email show=campaign_edit_url]">[charitable_email show=campaign_edit_url]</a>,', 'charitable-ambassadors' ) ?></p>
<?php
			$body = ob_get_clean();

			return apply_filters( 'charitable_email_campaign_creator_default_body', $body, $this );
		}

		/**
		 * Static method that is fired right after a campaign is created, sending the email.
		 *
		 * @param   array $submitted   Submitted campaign data.
		 * @param   int   $campaign_id The campaign ID.
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.1.0
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
			$status = apply_filters( 'charitable_ambassadors_send_creator_campaign_submission_email_on_status', $status );

			if ( $status != get_post_status( $campaign_id ) ) {
				return false;
			}

			$email = new Charitable_Ambassadors_Email_Creator_Campaign_Submission( array(
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
