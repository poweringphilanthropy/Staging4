<?php
/**
 * This class is responsible for adding the message field to the donation form.
 *
 * @package   Charitable Donor Comments/Classes/Charitable_Donor_Comments_Donation 
 * @author    Eric Daams
 * @copyright Copyright (c) 2017, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 * @version   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Donor_Comments_Donation' ) ) :

	/**
	 * Charitable_Donor_Comments_Donation
	 *
	 * @since 1.0.0
	 */
	class Charitable_Donor_Comments_Donation {

		/**
		 * The one class instance.
		 *
		 * @since 1.0.0
		 *
		 * @var   Charitable_Donor_Comments_Donation
		 */
		private static $instance = null;

		/**
		 * Boolean flag noting whether comment styles have been added.
		 *
		 * @since 1.0.0
		 *
		 * @var   boolean
		 */
		private $comment_styles_added = false;

		/**
		 * Array for storing comment IDs related to donation IDs.
		 *
		 * @since 1.0.0
		 *
		 * @var   array
		 */
		private $donation_comments_map = array();

		/**
		 * Create class object. Private constructor.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Create and return the class object.
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Donor_Comments_Donation();
			}

			return self::$instance;
		}

		/**
		 * Add the donor comments field to the "Your Details" section of the donation form.
		 *
		 * @since  1.0.0
		 *
		 * @param  Charitable_Donation_Form $form The form object.
		 * @return boolean False if the field was not rendered. True otherwise.
		 */
		public function add_comment_field( Charitable_Donation_Form $form ) {
			$value       = array_key_exists( 'donor_comment', $_POST ) ? $_POST['donor_comment'] : '';
			$label       = charitable_get_option(
				'donor_comment_label',
				__( 'Leave a Comment', 'charitable-donor-comments' )
			);
			$placeholder = charitable_get_option(
				'donor_comment_placeholder',
				__( 'Share why you are donating, your story, or just a word of encouragement. Your comment will be displayed publicly.', 'charitable-donor-comments' )
			);

			return $form->view()->render_field( array(
				'label'       => $label,
				'type'        => 'textarea',
				'required'    => false,
				'value'       => $value,
				'placeholder' => $placeholder,
			), 'donor_comment', $form );
		}

		/**
		 * Add the comment value to the values to save.
		 *
		 * @since  1.0.0
		 *
		 * @param  array $values    The donation values to be saved.
		 * @param  array $submitted Posted values.
		 * @return array
		 */
		public function add_comment_value( $values, $submitted ) {
			if ( ! array_key_exists( 'donor_comment', $submitted ) ) {
				return $values;
			}

			$values['donor_comment'] = trim( $submitted['donor_comment'] );

			return $values;
		}

		/**
		 * Save the comment.
		 *
		 * @since  1.0.0
		 *
		 * @param  int                           $donation_id The donation ID.
		 * @param  Charitable_Donation_Processor $processor   The donation processor.
		 * @return array|false An array of the inserted comments, or false if no comment was left.
		 */
		public function save_donor_comment( $donation_id, Charitable_Donation_Processor $processor ) {
			$comment = $processor->get_donation_data_value( 'donor_comment' );

			if ( empty( $comment ) ) {
				return false;
			}

			$donor     = $processor->get_donation_data_value( 'user' );
			$campaigns = $processor->get_donation_data_value( 'campaigns' );
			$comments  = array();

			/* If this donation was made anonymously, store the comment with an empty author name. */
			if ( $processor->get_donation_data_value( 'anonymous_donation' ) ) {
				$comment_author = '';
			} else {
				$comment_author = trim( sprintf( '%s %s', $donor['first_name'], $donor['last_name'] ) );
			}

			/**
			 * Generally there will only be one campaign per donation, but the data
			 * structure does support a single donation being for multiple campaigns.
			 */
			foreach ( $campaigns as $campaign ) {

				/**
				 * Filter the comment data that will be passed to wp_insert_comment.
				 *
				 * @see   wp_insert_comment
				 *
				 * @since 1.0.0
				 *
				 * @param array                         $data      The comment data.
				 * @param Charitable_Donation_Processor $processor Donation Processor object.
				 */
				$comment_data = apply_filters( 'charitable_donor_comment_data', array(
					'comment_post_ID'      => $campaign['campaign_id'],
					'comment_author'       => $comment_author,
					'comment_author_email' => $donor['email'],
					'comment_author_IP'    => preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] ),
					'comment_author_url'   => '',
					'comment_date'         => get_post_field( 'post_date', $donation_id, 'db' ),
					'comment_date_gmt'     => get_post_field( 'post_date_gmt', $donation_id, 'db' ),
					'comment_content'      => $comment,
					'comment_type'         => 'charitable_comment',
					'comment_meta'         => array(
						'donation_id'  => $donation_id,
						'is_anonymous' => $processor->get_donation_data_value( 'anonymous_donation' ),
					),
					'user_id'              => $processor->get_donation_data_value( 'user_id' ),
				), $processor );

				$comment_id = wp_new_comment( $comment_data, true );

				if ( $comment_id ) {
					add_post_meta( $donation_id, '_donor_comment', $comment_id );

					$comments[] = $comment_id;
				}
			}//end foreach

			return $comments;
		}

		/**
		 * Display the comment author as the donor name.
		 *
		 * @since  1.0.0
		 *
		 * @param  string $name      The name to be displayed.
		 * @param  array  $view_args View arguments.
		 * @return string
		 */
		public function set_donor_comment_author_as_name_in_widget( $name, $view_args ) {
			if ( false == $view_args['donor']->donation_id ) {
				return $name;
			}

			$comment = $this->get_validated_donor_comment( $view_args['donor']->donation_id );

			if ( ! $comment ) {
				return $name;
			}

			return get_comment_author( $comment );
		}

		/**
		 * Display the donor comment in the widget.
		 *
		 * @since  1.0.0
		 *
		 * @param  Charitable_Donor $donor     The donor object.
		 * @param  array            $view_args The view args.
		 * @return boolean Whether a comment was displayed.
		 */
		public function show_donor_comment_in_widget( $donor, $view_args ) {
			$comment = $this->get_donor_comment_for_widget( $donor, $view_args );

			if ( ! $comment ) {
				return false;
			}

			$comment_text = get_comment_text( $comment );

			if ( ! $comment_text ) {
				return false;
			}

			$this->add_comment_styles();

			printf( '<div class="donor-comment">%s</div>', $comment_text );

			return true;
		}

		/**
		 * Return a donor's comment for a specific donation.
		 *
		 * @since  1.0.0
		 *
		 * @param  int $donation_id The donation ID.
		 * @return WP_Comment|false False if there is no donor comment.
		 */
		public function get_donor_comment( $donation_id ) {
			if ( ! array_key_exists( $donation_id, $this->donation_comments_map ) ) {
				$comment_id = get_post_meta( $donation_id, '_donor_comment', true );
				$comment    = $comment_id ? get_comment( $comment_id ) : false;

				if ( $comment && is_a( $comment, 'WP_Comment' ) ) {
					$this->donation_comments_map[ $donation_id ] = $comment;
				} else {
					$this->donation_comments_map[ $donation_id ] = false;
				}
			}

			return $this->donation_comments_map[ $donation_id ];
		}

		/**
		 * Returns a donor's comment if one exists and it is approved.
		 *
		 * @since  1.0.0
		 *
		 * @param  int $donation_id The donation ID.
		 * @return WP_Comment|false False if there is no donor comment or it has not been approved.
		 */
		public function get_validated_donor_comment( $donation_id ) {
			$comment = $this->get_donor_comment( $donation_id );

			if ( ! $this->validate_comment( $comment ) ) {
				return false;
			}

			return $comment;
		}

		/**
		 * Display the comment author as Anonymous if it's blank, even if a logged in user made the comment.
		 *
		 * @since  1.0.0
		 *
		 * @param  string     $author     The comment author's username.
		 * @param  int        $comment_id The comment ID.
		 * @param  WP_Comment $comment    The comment object.
		 * @return string
		 */
		public function set_anonymous_donor_comment_author_name( $author, $comment_id, $comment ) {
			if ( 'charitable_comment' != $comment->comment_type ) {
				return $author;
			}

			if ( empty( $comment->comment_author ) ) {
				$author = __( 'Anonymous', 'charitable-donor-comments' );
			}

			return $author;
		}

		/**
		 * Register the email tag.
		 *
		 * @since  1.0.0
		 *
		 * @param  array $fields The email tags.
		 * @return array
		 */
		public function add_email_tag( $fields ) {
			return array_merge( $fields, array(
				'comment' => array(
					'description' => __( 'The donor\'s comment', 'charitable-donor-comments' ),
					'preview'     => __( 'This is my comment.', 'charitable-donor-comments' ),
					'callback'    => array( $this, 'get_email_tag_value' ),
				),
			) );
		}

		/**
		 * Return the email tag value.
		 *
		 * @since  1.0.0
		 *
		 * @param  string           $value The field value.
		 * @param  array            $args  Mixed arguments.
		 * @param  Charitable_Email $email The Email object.
		 * @return string
		*/
		public function get_email_tag_value( $value, $args, $email ) {
			$comment = $this->get_donor_comment( $email->get_donation()->ID );

			if ( $comment ) {
				$value = get_comment_text( $comment );
			}

			return $value;
		}

		/**
		 * Add the comment styles if they haven't been added yet.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function add_comment_styles() {
			if ( ! $this->comment_styles_added ) {
				charitable_donor_comments_template( 'comment-styles.css.php' );

				$this->comment_styles_added = true;
			}
		}

		/**
		 * Validate a comment.
		 *
		 * @since  1.0.0
		 *
		 * @param  WP_Comment|false $comment The comment object, or false.
		 * @return boolean
		 */
		private function validate_comment( $comment ) {
			return $comment && $comment->comment_approved;
		}

		/**
		 * Get the comment to display in the widget for the current donor.
		 *
		 * @since  1.0.0
		 *
		 * @param  Charitable_Donor $donor     The donor object.
		 * @param  array            $view_args The view args.
		 * @return WP_Comment|false False if there is no donor comment or it has not been approved.
		 */
		private function get_donor_comment_for_widget( $donor, $view_args ) {
			if ( $this->donations_are_grouped( $view_args ) ) {
				return $this->get_donor_last_comment( $donor, $view_args );
			}

			return $this->get_validated_donor_comment( $donor->donation_id );
		}

		/**
		 * Returns whether donations are grouped by donor.
		 *
		 * @since  1.0.0
		 *
		 * @param  array $view_args The view args.
		 * @return boolean
		 */
		private function donations_are_grouped( $view_args ) {
			foreach ( array( 'show_distinct', 'distinct_donors' ) as $key ) {
				if ( array_key_exists( $key, $view_args ) ) {
					return $view_args[ $key ];
				}
			}

			return false;
		}

		/**
		 * Get the most recent comment by the donor.
		 *
		 * This is used when donations are grouped to still show a comment.
		 *
		 * @since  1.0.0
		 *
		 * @param  Charitable_Donor $donor     The donor object.
		 * @param  array            $view_args The view args.
		 * @return WP_Comment|false False if there is no donor comment or it has not been approved.
		 */
		private function get_donor_last_comment( $donor, $view_args ) {
			$comment_args = array(
				'author_email' => $donor->get_email(),
				'post_type'    => Charitable::CAMPAIGN_POST_TYPE,
				'status'       => 'approve',
				'number'       => 1,
				'comment_type' => 'charitable_comment',
				'meta_query'   => array(
					array(
						'key'   => 'is_anonymous',
						'value' => $donor->is_anonymous,
					),
				),
			);

			/**
			 * When we have Anonymous Donations, we should make sure to only
			 * show the last comment made while anonymous/not anonymous.
			 */
			if ( isset( $donor->is_anonymous ) ) {
				$comment_args['meta_query'] = array(
					array(
						'key'   => 'is_anonymous',
						'value' => $donor->is_anonymous,
					),
				);

				if ( ! $donor->is_anonymous ) {
					$comment_args['meta_query'][] = array(
						'key'     => 'is_anonymous',
						'compare' => 'NOT EXISTS',
					);
					$comment_args['meta_query']['relation'] = 'OR';
				}
			}

			if ( false !== $view_args['campaign'] ) {
				$comment_args['post_id'] = $view_args['campaign'];
			}

			$comments = get_comments( $comment_args );

			return empty( $comments ) ? false : $comments[0];
		}
	}

endif;
