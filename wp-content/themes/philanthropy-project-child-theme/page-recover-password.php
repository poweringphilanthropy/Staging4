<?php 
/*
 Template name: Reset Password
 */


get_header(); ?>

<?php get_template_part( 'partials/banner', 'page' ) ?>

<div class="content-wrapper">
	<div class="content">									
		<?php 
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if ( isset( $_POST['user_login'] ) ) {
				
				$user_login = trim($_POST['user_login']);
				$login_type = is_email( $user_login ) ? 'email' : 'login';
				$user_data = get_user_by( $login_type, $user_login );
				
				if ( ! $user_data ) {
					?> 
					<p class="message">Invalid username or e-mail.</p>		

					<form name="lostpasswordform" id="lostpasswordform" method="post">

						<p>
							<label for="user_login" ><?php _e('Username or E-mail:') ?></label>
								<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" required />
						</p>			
			
						<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Get New Password" /></p>
			
					</form>
					<?php
				}
				if ( $user_data ) {
					$user_login = $user_data->user_login;
					$user_email = $user_data->user_email;
					$key = wp_generate_password( 20, false );
				
					if ( empty( $wp_hasher ) ) {
						require_once ABSPATH . WPINC . '/class-phpass.php';
						$wp_hasher = new PasswordHash( 8, true );
					}
					$hashed = $wp_hasher->HashPassword( $key );
					$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
				
					$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
					$message .= network_home_url( '/' ) . "\r\n\r\n";
					$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
					$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
					$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
					$message .= '<' . network_site_url("/recover-password/?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
				
					if ( is_multisite() )
						$blogname = $GLOBALS['current_site']->site_name;
					else
						$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

					$title = sprintf( __('[%s] Password Reset'), $blogname );
				
					wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
					
					echo '<p>Check your e-mail for the link to reset your password.</p>';	
				}
				
			}
					
			if ( isset( $_POST['new_password_1'] ) && isset( $_POST['new_password_2'] ) )	{
				if ( $_POST['new_password_1'] != $_POST['new_password_2'] ) {
					?> 
						<p class="message">Password Mismatch.</p>		

						<form name="lostpasswordform" id="lostpasswordform" method="post">

							<p>
									<input type="password" name="new_password_1" id="new_password_1" class="input" value="" size="20" placeholder="New Password" required />
							</p>			
	
							<p>
									<input type="password" name="new_password_2" id="new_password_1" class="input" value="" size="20" placeholder="Confirm Password" required />
							</p>
							<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Update Password" /></p>
	
						</form>
					<?php
				}
				
				
				if ( $_POST['new_password_1'] == $_POST['new_password_2'] ) {					
					$user = get_user_by( 'login', trim( $_GET['login'] ) );
					reset_password($user, $_POST['new_password_1']);
					?>
					<p>Password updated successfully.</p>
					<?php
				}	
			}	 
			
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			
			if( ! isset( $_GET['action'] ) ) {
				?>
				<p class="message">Please enter your username or email address. You will receive a link to create a new password via email.</p>		

				<form name="lostpasswordform" id="lostpasswordform" method="post">

					<p>
						<label for="user_login" ><?php _e('Username or E-mail:') ?></label>
							<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" required />
					</p>			
			
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Get New Password" /></p>
			
				</form>				
			
		<?php }
		
		if( isset( $_GET['action'] ) && $_GET['action'] == 'rp' ) {
			?>
			<p class="message">Enter your new password below.</p>		

			<form name="lostpasswordform" id="lostpasswordform" method="post">

				<p>
						<input type="password" name="new_password_1" id="new_password_1" class="input" value="" size="20" placeholder="New Password" required />
				</p>			
		
				<p>
						<input type="password" name="new_password_2" id="new_password_1" class="input" value="" size="20" placeholder="Confirm Password" required />
				</p>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Update Password" /></p>
		
			</form>				
		
	<?php }
		
	} ?>
		
	</div>

	<?php get_sidebar() ?>

</div>

<?php get_footer() ?>