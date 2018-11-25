<?php
/**
 * Class that models the email that is sent to campaign creators whenever their campaign receives a donation.
 *
 * @version     1.1.0
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Email_Creator_Donation_Notification
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Email_Creator_Donation_Notification' ) ) :

	/**
	 * New Donation Email
	 *
	 * @since       1.1.0
	 */
	class Charitable_Ambassadors_Email_Creator_Donation_Notification extends Charitable_Email {

		/* The ID of this email. */
		const ID = 'creator_donation_notification';

		/**
		 * Sets whether the email allows you to define the email recipients.
		 *
		 * @since 1.1.0
		 *
		 * @var   boolean
		 */
		protected $has_recipient_field = false;

		/**
		 * Object types (campaigns, donations, donors, etc) supported by this email.
		 *
		 * @var     string[]
		 * @since   1.1.0
		 */
		protected $object_types = array( 'donation', 'creator' );

		/**
		 * The campaign creator who will be receiving the email.
		 *
		 * @var     Charitable_User
		 * @since   1.1.0
		 */
		protected $creator;

		/**
		 * The campaign donations received by the campaign creator in this donation.
		 *
		 * @var     object[]
		 * @since   1.1.0
		 */
		protected $campaign_donations;

		/**
		 * Instantiate the email class, defining its key values.
		 *
		 * @param   mixed[] $objects The objects used to create the email.
		 * @since   1.1.0
		 */
		public function __construct( $objects = array() ) {
			parent::__construct( $objects );

			$this->creator = isset( $objects['creator'] ) ? $objects['creator'] : null;

			$this->campaign_donations = isset( $objects['campaign_donations'] ) ? $objects['campaign_donations'] : array();

			$this->name = apply_filters( 'charitable_email_creator_donation_notification_name', __( 'Campaign Creator: Donation Notification', 'charitable-ambassadors' ) );

			add_filter( 'charitable_email_content_fields', array( $this, 'add_creator_content_fields' ), 10, 2 );

			add_filter( 'charitable_email_preview_content_fields', array( $this, 'add_preview_creator_content_fields' ), 10, 2 );
		}

		/**
		 * Returns the current email's ID.
		 *
		 * @return  string
		 * @static
		 * @since   1.0.3
		 */
		public static function get_email_id() {
			return self::ID;
		}

		/**
		 * Static method that is fired right after a donation is completed, sending the donation receipt.
		 *
		 * @param   int $donation_id The donation ID.
		 * @return  boolean
		 * @static
		 * @since   1.1.0
		 */
		public static function send_with_donation_id( $donation_id ) {
			if ( ! charitable_get_helper( 'emails' )->is_enabled_email( self::get_email_id() ) ) {
				return false;
			}

			if ( ! charitable_is_approved_status( get_post_status( $donation_id ) ) ) {
				return false;
			}

			$donation = charitable_get_donation( $donation_id );

			/**
			 * Since a single donation may include donations to multiple
			 * campaigns, it is possible that multiple creator notifications
			 * need to be sent.
			 *
			 * We send an email to every creator who just received a donation.
			 */
			foreach ( self::get_campaign_creators( $donation_id ) as $creator_id => $campaign_donations ) {
				$email = new Charitable_Ambassadors_Email_Creator_Donation_Notification( array(
					'donation' 			 => $donation,
					'creator' 			 => new Charitable_User( $creator_id ),
					'campaign_donations' => $campaign_donations,
				) );

				/* Don't resend the email. */
				if ( $email->is_sent_already( $donation_id ) ) {
					return false;
				}

				$sent = $email->send();
			}

			/* Log that the email was sent. */
			if ( isset( $email ) && apply_filters( 'charitable_log_email_send', true, self::get_email_id(), $email ) ) {
				$email->log( $donation_id, $sent );
			}

			return true;
		}

		/**
		 * Sends the email.
		 *
		 * @return  boolean
		 * @since   1.1.0
		 */
		public function send() {
			if ( is_null( $this->creator ) ) {
				charitable_get_deprecated()->doing_it_wrong(
					__METHOD__,
					__( 'You cannot send a creator donation notification without a Charitable_User object for the campaign creator.', 'charitable-ambassadors' ),
					'1.1.0'
				);
				return false;
			}

			if ( empty( $this->campaign_donations ) ) {
				charitable_get_deprecated()->doing_it_wrong(
					__METHOD__,
					__( 'You cannot send a creator donation notification without an array of campaign donations.', 'charitable-ambassadors' ),
					'1.1.0'
				);
				return false;
			}

			return parent::send();
		}

		/**
		 * Resend an email.
		 *
		 * @since  1.1.17
		 *
		 * @param  int   $object_id An object ID.
		 * @param  array $args      Mixed set of arguments.
		 * @return boolean
		 */
		public static function resend( $object_id, $args = array() ) {
			$donation = charitable_get_donation( $object_id );

			if ( ! is_object( $donation ) || 0 == count( $donation->get_campaign_donations() ) ) {
				return false;
			}

			foreach ( self::get_campaign_creators( $object_id ) as $creator_id => $campaign_donations ) {
				$email = new Charitable_Ambassadors_Email_Creator_Donation_Notification( array(
					'donation' 			 => $donation,
					'creator' 			 => new Charitable_User( $creator_id ),
					'campaign_donations' => $campaign_donations,
				) );

				$success = $email->send();
			}

			/* Log that the email was sent. */
			if ( isset( $email ) && apply_filters( 'charitable_log_email_send', true, self::get_email_id(), $email ) ) {
				$email->log( $object_id, $success );
			}

			return $success;
		}

		/**
		 * Checks whether an email can be resent.
		 *
		 * @since  1.1.17
		 *
		 * @param  int   $object_id An object ID.
		 * @param  array $args      Mixed set of arguments.
		 * @return boolean
		 */
		public static function can_be_resent( $object_id, $args = array() ) {
			return charitable_is_approved_status( get_post_status( $object_id ) );
		}

		/**
		 * Return the correct email ID for a particular log.
		 *
		 * @since  1.1.17
		 *
		 * @param  string $email The email ID.
		 * @return string
		 */
		public static function get_email_id_from_log( $email ) {
			if ( 0 === strpos( $email, 'creator_donation_notification' ) ) {
				$email = 'creator_donation_notification';
			}

			return $email;
		}

		/**
		 * Add creator content fields for this email.
		 *
		 * @param   array $fields
		 * @param   Charitable_Email $email
		 * @return  array
		 * @since   1.1.0
		 */
		public function add_creator_content_fields( $fields, Charitable_Email $email ) {
			if ( ! $this->is_current_email( $email ) ) {
				return $fields;
			}

			if ( ! in_array( 'creator', $this->object_types ) ) {
				return $fields;
			}

			$fields['campaign_creator'] = array(
				'description'   => __( 'The name of the campaign creator', 'charitable-ambassadors' ),
				'callback'      => array( $this, 'get_campaign_creator' ),
			);

			return $fields;
		}

		/**
		 * Add creator content fields for this email.
		 *
		 * @param   array $fields
		 * @param   Charitable_Email $email
		 * @return  array
		 * @since   1.1.0
		 */
		public function add_preview_creator_content_fields( $fields, Charitable_Email $email ) {
			if ( ! $this->is_current_email( $email ) ) {
				return $fields;
			}

			if ( ! in_array( 'creator', $this->object_types ) ) {
				return $fields;
			}

			$fields['campaign_creator'] = 'Harry Ferguson';

			return $fields;
		}

		/**
		 * Return the campaign creator's name.
		 *
		 * @return  string
		 * @since   1.1.0
		 */
		public function get_campaign_creator() {
			if ( is_null( $this->creator ) ) {
				return '';
			}

			return $this->creator->get( 'display_name' );
		}

		/**
		 * Returns a summary of the donation, including only the campaigns that were donated to that belong to the campaign creator.
		 *
		 * @param   string $value
		 * @param   mixed[] $args
		 * @param   Charitable_Email $email
		 * @return  string
		 * @since   1.1.0
		 */
		public function get_donation_summary( $value, $args, $email ) {
			if ( ! $this->is_current_email( $email ) ) {
				return parent::get_donation_summary( $value, $args, $email );
			}

			if ( empty( $this->campaign_donations ) ) {
				return $value;
			}

			$output = '';

			foreach ( $this->campaign_donations as $campaign_donation ) {

				$line_item = sprintf( '%s: %s%s', $campaign_donation->campaign_name, charitable_format_money( $campaign_donation->amount ), PHP_EOL );

				$output .= apply_filters( 'charitable_creator_donation_summary_line_item_email', $line_item, $campaign_donation, $args, $email );

			}

			return $output;
		}

		/**
		 * Return the meta key used for the log.
		 *
		 * @return  string
		 * @since   1.1.0
		 */
		protected function get_log_key() {
			return '_email_' . $this->get_email_id() . '_' . $this->creator->ID . '_log';
		}

		/**
		 * Return the default recipient for the email.
		 *
		 * @return  string
		 * @since   1.1.0
		 */
		protected function get_default_recipient() {
			return $this->creator->get( 'user_email' );
		}

		/**
		 * Return the default subject line for the email.
		 *
		 * @return  string
		 * @since   1.1.0
		 */
		protected function get_default_subject() {
			return __( 'You have received a new donation', 'charitable-ambassadors' );
		}

		/**
		 * Return the default headline for the email.
		 *
		 * @return  string
		 * @since   1.1.0
		 */
		protected function get_default_headline() {
			return apply_filters( 'charitable_email_creator_donation_notification_default_headline', __( 'New Donation', 'charitable-ambassadors' ), $this );
		}

		/**
		 * Return the default body for the email.
		 *
		 * @return  string
		 * @since   1.1.0
		 */
		protected function get_default_body() {
			ob_start();
?>
<p><?php _e( 'Dear [charitable_email show=campaign_creator],', 'charitable-ambassadors' ) ?></p>
<p><?php _e( 'Congratulations! [charitable_email show=donor] has just made a donation to your campaign.', 'charitable-ambassadors' ) ?></p>
<p><?php _e( '<strong>Summary</strong>', 'charitable-ambassadors' ) ?></p>
<p>[charitable_email show=donation_summary]</p>
<?php
			$body = ob_get_clean();

			return apply_filters( 'charitable_email_new_donation_default_body', $body, $this );
		}

		/**
		 * Return all the campaign creators for a particular donation.
		 *
		 * @since  1.1.17
		 *
		 * @param  int $donation_id The donation ID.
		 * @return array
		 */
		protected static function get_campaign_creators( $donation_id ) {
			$donation = charitable_get_donation( $donation_id );

			if ( ! $donation ) {
				return array();
			}

			$creators = array();

			foreach ( $donation->get_campaign_donations() as $campaign_donation ) {
				$creator_id = get_post_field( 'post_author', $campaign_donation->campaign_id );

				if ( ! isset( $creators[ $creator_id ] ) ) {
					$creators[ $creator_id ] = array();
				}

				$creators[ $creator_id ][] = $campaign_donation;
			}

			return $creators;
		}
	}

endif;
