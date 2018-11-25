<?php
/**
 * PP_Users Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Users
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Users class.
 */
class PP_Users {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Users();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'show_user_profile', array($this, 'pp_add_user_profile_fields') );
        add_action( 'edit_user_profile', array($this, 'pp_add_user_profile_fields') );

        add_action( 'wp_login_failed', array($this, 'pp_front_end_login_fail')); 
        add_filter( 'lostpassword_url', array($this, 'pp_custom_login_lostpassword_url') );

        add_action( 'wp_authenticate', array($this, 'pp_catch_empty_user'), 1, 2 );

        add_action( 'charitable_after_insert_user', array($this, 'send_email_notification'), 10, 2 );
        // add_action( 'charitable_after_insert_user', array($this, 'auto_login_after_insert'), 20, 2 );
        
        add_filter( 'charitable_user_profile_fields', array($this, 'change_user_profile_fields'), 10, 2 );
		add_action('profile_update', array($this, 'update_crm_id'));
        
    }

    public function change_user_profile_fields($fields, Charitable_Profile_Form $form){
        if(isset($fields['user_fields'])):
            $fields['user_fields']['hide_header'] = true;
        endif;

        return $fields;
    }

    public function auto_login_after_insert($user_id, $values){
        wp_set_current_user($user_id); 
    }

    public function pp_add_user_profile_fields( $user ) {
        $skip = array( 'first_name', 'last_name', 'user_email', 'description' );

        $switch_keys = array(
            'address' => 'donor_address',
            'address_2' => 'donor_address_2',
            'city' => 'donor_city',
            'state' => 'donor_state',
            'postcode' => 'donor_postcode',
            'country' => 'donor_country',
            'phone' => 'donor_phone'
        );
        $profile_form = new Charitable_Profile_Form;
        ?>
        <h3><?php _e( 'Additional profile fields', 'pp-toolkit' ) ?></h3>
        <table class="widefat">
            <tbody>
            <?php foreach ( $profile_form->get_merged_fields() as $key => $field ) : ?>
                <?php if ( in_array( $key, $skip ) ) :
                    continue;
                endif;
                echo $meta_key = isset( $switch_keys[ $key ] ) ? $switch_keys[ $key ] : $key;
				if($meta_key == "crm_account_id"){ ?>
					<tr>
						<th><label for=""><?php echo $field[ 'label' ] ?></label></th>
						<td><input type="text" name="<?php echo $meta_key;?>" value="<?php echo $user->get( $meta_key ) ?>"></td>
					</tr>
					
				<?php }else{
                ?>
                <tr>
                    <th><label for=""><?php echo $field[ 'label' ] ?></label></th>
                    <td><?php echo $user->get( $meta_key ) ?></td>
                </tr>
				<?php } endforeach ?>
            </tbody>
        </table>
        <?php
    }

    public function pp_front_end_login_fail($username){
        $referrer = $_SERVER['HTTP_REFERER'];
        
        if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
            $redirect_url = site_url('/login/') . '?error=failed';
            wp_safe_redirect($redirect_url); 
        }
        
    }

    public function pp_catch_empty_user( $username, $pwd ) {
        $referrer = $_SERVER['HTTP_REFERER'];
        
        if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {

          if ( empty( $username ) ) {
                $redirect_url = site_url('/login/') . '?error=username';
                wp_safe_redirect($redirect_url); 
                exit();
          }
        
          if ( empty( $pwd ) ) {
                $redirect_url = site_url('/login/') . '?error=password';
                wp_safe_redirect($redirect_url); 
                exit();
          }
                    
        }
    }

    public function pp_custom_login_lostpassword_url(){
        return site_url('/recover-password/');
    }

    public function send_email_notification($user_id, $values){
        
        // wp_send_new_user_notifications($user_id, 'both');
        
        $from_name = wp_specialchars_decode( charitable_get_option( 'email_from_name', get_option( 'blogname' ) ) );
        $from_address = charitable_get_option( 'email_from_email', get_option( 'admin_email' ) );
        $bcc = get_option( 'admin_email' );

        $password = (isset($values['user_pass'])) ? $values['user_pass'] : '-';
        $login_url = home_url( 'profile' );
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $user = get_userdata( $user_id );

        $message = sprintf(__('Hi %s, thank you for registering on %s !'), $user->display_name, $blogname) . "\r\n\r\n";
        $message .= __('Here is your login info:') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n";
        $message .= sprintf(__('Password: %s'), $password) . "\r\n\r\n";
        $message .= __('To edit your profile, please visit the following address:') . "\r\n\r\n";

        $message .= sprintf( __('<a href="%s">%s</a>'), $login_url, $login_url ) . "\r\n";

        $headers  = "From: {$from_name} <{$from_address}>\r\n";
        $headers .= "Reply-To: {$from_address}\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        $headers .= "Bcc: {$bcc}\r\n";

        wp_mail($user->user_email, sprintf(__('[%s] Your username and password info'), $blogname), wpautop( $message ), $headers);
    }

    public function includes(){

    }
	public function update_crm_id($user_id){
		if ( current_user_can('edit_user',$user_id) )
			update_user_meta($user_id, 'crm_account_id', $_POST['crm_account_id']);
	}

}

PP_Users::init();