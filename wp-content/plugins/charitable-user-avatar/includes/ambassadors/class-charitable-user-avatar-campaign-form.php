<?php
/**
 * The class that is responsible for augmenting the campaign submission form, adding the 
 * avatar field when submitting or editing the campaign.
 *
 * @package     Charitable Simple Updates/Classes/Charitable_User_Avatar_Campaign_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Charitable_User_Avatar_Campaign_Form' ) ) : 

/**
 * Charitable_User_Avatar_Campaign_Form
 *
 * @since       1.0.0
 */
class Charitable_User_Avatar_Campaign_Form {

    /**
     * Create object instance.  
     *
     * @return  Charitable_User_Avatar_Campaign_Form
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function start( Charitable_User_Avatar $charitable_su ) {
        if ( ! $charitable_su->is_start() ) {
            return;
        }

        return new Charitable_User_Avatar_Campaign_Form();
    }

    /**
     * Create class object.
     * 
     * @access  protected
     * @since   1.0.0
     */
    protected function __construct() {
        add_filter( 'charitable_campaign_submission_user_fields', array( $this, 'add_avatar_field' ), 10, 2 );
        add_filter( 'charitable_campaign_submission_meta_data', array( $this, 'save_avatar' ), 10, 4 );
    }

    /**
     * Get the current value of the avatar.  
     *
     * @param   Charitable_User $user
     * @param   int $size
     * @return  string|int
     * @access  public
     * @since   1.0.2
     */
    public function get_avatar_value( $user, $size = 100 ) {
        if ( isset( $_POST[ 'avatar' ] ) ) {
            return $_POST[ 'avatar' ];
        }

        $avatar = Charitable_User_Avatar::get_instance()->get_user_avatar( false, $user );

        if ( ! $avatar  && charitable_user_has_gravatar( $user->user_email ) ) {
            $avatar = get_avatar( $user->ID, $size );
        }

        return $avatar;
    }

    /**
     * Add the avatar field to the campaign submission form.  
     *
     * @param   array[] $fields
     * @param   Charitable_Ambassadors_Campaign_Form $form
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function add_avatar_field( $fields, Charitable_Ambassadors_Campaign_Form $form ) {
        $fields[ 'avatar' ] = apply_filters( 'charitable_user_avatar_field_args', array(
            'label'     => __( 'Your Profile Photo', 'charitable-user-avatar' ),
            'type'      => 'picture',
            'uploader'  => true,
            'size'      => 100,
            'value'     => $this->get_avatar_value( $form->get_user(), 100 ),
            'priority'  => 58, 
            'fullwidth' => true, 
            'data_type' => 'user'
        ) );

        return $fields;
    }

    /**
     * Upload avatar and add file fields to the submitted fields. 
     *
     * @param   array $submitted
     * @param   int $campaign_id
     * @param   array[] $fields
     * @param   Charitable_Profile_Form $form
     * @return  array 
     * @access  public
     * @since   1.0.0
     */
    public function save_avatar( $submitted, $campaign_id, $ields, $form ) {

        if ( isset( $_FILES ) && isset( $_FILES[ 'avatar' ] ) ) {

            $attachment_id = $form->upload_post_attachment( 'avatar', 0 );

            if ( ! is_wp_error( $attachment_id ) ) {

                $submitted[ 'avatar' ] = $attachment_id;

                /* Delete the previously upload avatar. */
                $old_avatar = get_user_meta( $form->get_user()->ID, 'avatar', true );

                if ( ! empty( $old_avatar ) ) {

                    wp_delete_attachment( $old_avatar );

                }

                update_user_meta( $form->get_user()->ID, 'avatar', $attachment_id );

            }
            else {
                /** 
                 * @todo Handle image upload error.
                 */
            }
        }

        return $submitted;
    } 
}

endif; // End class_exists check