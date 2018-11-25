<div class="layout-wrapper">
	<div class="uk-grid">
		<div class="uk-width-1-1 uk-width-medium-1-2 join-email-list left-content">
			<span class="big-text"><?php _e('Join our email list:', 'pp-theme'); ?></h3>
		</div>
		<div class="uk-width-1-1 uk-width-medium-1-2 right-content"></div>
	</div>
	<div class="uk-grid grid-email-list">
		<div class="uk-width-1-1 uk-width-medium-1-2 join-email-list left-content">
			<?php if( function_exists( 'ninja_forms_display_form' ) ){ 
				$form_id = get_theme_mod( 'footer_form_id' );
				if(!empty($form_id)){
					ninja_forms_display_form( $form_id );
				}
			} ?>
		</div>
		<div class="uk-width-1-1 uk-width-medium-1-2 right-content">
			<span class="big-text follow-us-text">Follow US:</span>
			<ul class="social-link">
				<li><a href="https://www.facebook.com/Greeks4Good/"><img src="<?php echo PPR()->get_path('media', true); ?>/facebook.png" class="icon" alt="facebook"></a></li>
				<li><a href="https://twitter.com/greeks4good"><img src="<?php echo PPR()->get_path('media', true); ?>/twitter.png" class="icon" alt="twitter"></a></li>
				<li><a href="#"><img src="<?php echo PPR()->get_path('media', true); ?>/instagram.png" class="icon" alt="instagram"></a></li>
				<li><a href="#"><img src="<?php echo PPR()->get_path('media', true); ?>/snapchat.png" class="icon" alt="snapchat"></a></li>
			</ul>
		</div>
	</div>
</div>