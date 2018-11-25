<?php
/**
 * The class that is responsible for augmenting the base profile form, adding the
 * avatar field and saving it correctly on form save.
 *
 * @package     Charitable User Avatar/Classes/Charitable_User_Avatar_Profile_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_User_Avatar_Profile_Form' ) ) :

	/**
	 * Charitable_User_Avatar_Profile_Form
	 *
	 * @since       1.0.0
	 */
	class Charitable_User_Avatar_Profile_Form {

		/**
		 * Create object instance.
		 *
		 * @return  Charitable_User_Avatar_Profile_Form
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function start( Charitable_User_Avatar $charitable_ua ) {
			if ( ! $charitable_ua->is_start() ) {
				return;
			}

			return new Charitable_User_Avatar_Profile_Form();
		}

		/**
		 * Create class object.
		 *
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function __construct() {
			add_filter( 'charitable_user_fields',           array( $this, 'add_avatar_field' ), 10, 2 );
			add_filter( 'charitable_profile_update_values', array( $this, 'save_avatar' ), 10, 3 );
		}

		/**
		 * Add avatar section to user profile form.
		 *
		 * @param   array[]                 $fields
		 * @param   Charitable_Profile_Form $form
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_avatar_field( $fields, $form ) {
			$user   = $form->get_user();
			$avatar = Charitable_User_Avatar::get_instance()->get_user_avatar( false, $user );

			if ( ! $avatar && charitable_user_has_gravatar( $user->user_email ) ) {
				$avatar = get_avatar( $user->ID, 100 );
			}

			$fields['avatar'] = apply_filters( 'charitable_user_avatar_field_args', array(
				'label'     => __( 'Your Profile Photo', 'charitable-user-avatar' ),
				'type'      => 'picture',
				'uploader'  => true,
				'size'      => 100,
				'value'     => $avatar,
				'priority'  => 14,
				'fullwidth' => true,
			) );

			return $fields;
		}

		/**
		 * Upload avatar and add file fields to the submitted fields.
		 *
		 * @param   array                   $submitted The values submitted by the user.
		 * @param   array[]                 $fields    The fields in the form.
		 * @param   Charitable_Profile_Form $form      The profile form object.
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_avatar( $submitted, $fields, $form ) {
			if ( isset( $_FILES ) && isset( $_FILES['avatar'] ) ) {

				$attachment_id = $form->upload_post_attachment( 'avatar', 0 );

				if ( ! is_wp_error( $attachment_id ) ) {

					$submitted['avatar'] = $attachment_id;

					/* Delete the previously upload avatar. */
					$old_avatar = get_user_meta( $form->get_user()->ID, 'avatar', true );

					if ( ! empty( $old_avatar ) ) {

						wp_delete_attachment( $old_avatar );

					}

					update_user_meta( $form->get_user()->ID, 'avatar', $attachment_id );

				} else {
					/**
					* Handle image upload error.
					*
					* @todo
					*/
				}//end if
			} elseif ( ! array_key_exists( 'avatar', $submitted ) ) {

				/* The picture has been removed. */
				$submitted['avatar'] = '';

			}//end if

			return $submitted;
		}
	}

endif;
