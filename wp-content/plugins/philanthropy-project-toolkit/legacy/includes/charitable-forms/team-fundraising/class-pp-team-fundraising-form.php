<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Team_Fundraising_Form' ) ) :

	/**
	 * PP_Team_Fundraising_Form
	 *
	 * @since       1.0.0
	 */
	class PP_Team_Fundraising_Form extends Charitable_Form {

		/**
		 * Shortcode parameters.
		 *
		 * @var     array
		 * @access  protected
		 */
		protected $args;

		/**
		 * Page number.
		 *
		 * @var     int
		 * @access  protected
		 */
		protected $page;

		/**
		 * The nonce action identifier.
		 *
		 * @var     string
		 */
		protected $nonce_action = 'pp_team_fundraising_submission';

		/**
		 * The nonce name.
		 *
		 * @var     string
		 */
		protected $nonce_name = '_pp_team_fundraising_submission_nonce';

		/**
		 * Action to be executed upon form submission.
		 *
		 * @var     string
		 * @access  protected
		 */
		protected $form_action = 'save_campaign';

		/**
		 * The current user.
		 *
		 * @var     Charitable_User
		 * @access  protected
		 */ 
		protected $user;

		/**
		 * The context of the submission.
		 *
		 * @var     string
		 * @access  protected
		 */
		protected $submission_context;

		/**
		 * Create class object.
		 *
		 * @param   array $args User-defined shortcode attributes.
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args = array() ) {
			$this->id = uniqid();
			$this->args = $args;
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
			// parent::attach_hooks_and_filters();
		}
	}

endif; // End class_exists check
