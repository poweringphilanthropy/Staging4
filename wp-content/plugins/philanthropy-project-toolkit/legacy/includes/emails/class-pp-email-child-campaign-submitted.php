<?php
/**
 * Class that models the email that is sent to campaign creators after they submit their campaign.
 *
 * @version     1.1.0
 * @package     Charitable Ambassadors/Classes/PP_Email_Child_Campaign_Submission
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Email_Child_Campaign_Submission' ) ) :

	/**
	 * New Donation Email
	 *
	 * @since       1.1.0
	 */
	class PP_Email_Child_Campaign_Submission extends Charitable_Email {

		/**
		 * @var     string
		 */
		const ID = 'child_campaign_submission';

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

			$this->name = apply_filters( 'charitable_email_child_campaign_submission_name', __( 'Child Campaign Creator: New Campaign', 'charitable-ambassadors' ) );
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
		 * Get the email headers.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_headers() {
			if ( ! isset( $this->headers ) ) {
				$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
				$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
				$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
				
				$bcc_emails = $this->get_option( 'bcc', '' );
				if(!empty($bcc_emails)){
					foreach ( explode(',', $bcc_emails ) as $key => $email) {
						$this->headers .= "Bcc: {$email}\r\n";
					}
				}
				
			}

			/**
			 * Filter the email headers.
			 *
			 * @since 1.0.0
			 *
			 * @param string           $headers The default email headers.
			 * @param Charitable_Email $email   The email object.
			 */
			return apply_filters( 'charitable_email_headers', $this->headers, $this );
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
		 * @param   string $new_status New post status.
		 * @param   string $old_status Old post status.
		 * @param   WP_Post $post Post object.
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function send_email( $post_id, $parent_id, $post_data ) {

			$post = get_post($post_id);

			if ( Charitable::CAMPAIGN_POST_TYPE != $post->post_type ) {
				return false;
			}

			if ( ! charitable_get_helper( 'emails' )->is_enabled_email( self::get_email_id() ) ) {
				return false;
			}

			/**
			 * If default email from charitable already sent return
			 * @var Charitable_Ambassadors_Email_Creator_Campaign_Submission
			 */
			// $default_email = new Charitable_Ambassadors_Email_Creator_Campaign_Submission( array(
			// 	'campaign' => charitable_get_campaign( $post->ID ),
			// ) );

			// /**
			//  * Don't resend the email.
			//  */
			// if ( $default_email->is_sent_already( $post->ID ) ) {
			// 	return false;
			// }

			$email = new PP_Email_Child_Campaign_Submission( array(
				'campaign' => charitable_get_campaign( $post->ID ),
			) );

			/**
			 * Don't resend the email.
			 */
			if ( $email->is_sent_already( $post->ID ) ) {
				return false;
			}

			// echo "<pre>";
			// print_r($post);
			// echo "</pre>";
			// exit();

			$sent = $email->send();

			/**
			 * Log that the email was sent.
			 */
			if ( apply_filters( 'charitable_log_email_send', true, self::get_email_id(), $email ) ) {
				$email->log( $post->ID, $sent );
			}

			return true;
		}

		/**
		 * Register email settings.
		 *
		 * @param   array $settings Default email settings.
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function email_settings(  ) {
			$email_settings = apply_filters( 'charitable_settings_fields_emails_email_' . $this->get_email_id(), array(
				'section_email' => array(
					'type'      => 'heading',
					'title'     => $this->get_name(),
					'priority'  => 2,
				),
				'subject' => array(
					'type'      => 'text',
					'title'     => __( 'Email Subject Line', 'charitable' ),
					'help'      => __( 'The email subject line when it is delivered to recipients.', 'charitable' ),
					'priority'  => 6,
					'class'     => 'wide',
					'default'   => $this->get_default_subject(),
				),
				'headline' => array(
					'type'      => 'text',
					'title'     => __( 'Email Headline', 'charitable' ),
					'help'      => __( 'The headline displayed at the top of the email.', 'charitable' ),
					'priority'  => 10,
					'class'     => 'wide',
					'default'   => $this->get_default_headline(),
				),
				'bcc' => array(
					'type'      => 'text',
					'title'     => __( 'BCC', 'charitable' ),
					'help'      => __( 'Bcc emails, separate by comma', 'charitable' ),
					'priority'  => 11,
					'class'     => 'wide',
					'default'   => get_option( 'admin_email' ),
				),
				'body' => array(
					'type'      => 'editor',
					'title'     => __( 'Email Body', 'charitable' ),
					'help'      => sprintf( '%s <div class="charitable-shortcode-options">%s</div>',
						__( 'The content of the email that will be delivered to recipients. HTML is accepted.', 'charitable' ),
						$this->get_shortcode_options()
					),
					'priority'  => 14,
					'default'   => $this->get_default_body(),
				),
				'preview' => array(
					'type'      => 'content',
					'title'     => __( 'Preview', 'charitable' ),
					'content'   => sprintf( '<a href="%s" target="_blank" class="button">%s</a>',
						esc_url(
							add_query_arg( array(
								'charitable_action' => 'preview_email',
								'email_id' => $this->get_email_id(),
							), home_url() )
						),
						__( 'Preview email', 'charitable' )
					),
					'priority'  => 18,
					'save'      => false,
				),
			) );

			return wp_parse_args( $settings, $email_settings );
		}
	}

endif; // End class_exists check
