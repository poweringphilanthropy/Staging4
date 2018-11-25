<?php 
/**
 * Displays the modal content
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$campaign = $view_args[ 'campaign' ];
$form_id     = (isset($view_args[ 'form_id' ])) ? $view_args[ 'form_id' ] : $view_args[ 'id' ];
$user_id = $campaign->get_campaign_creator();
$user_email = get_userdata($user_id)->data->user_email;
$user_email_nonce = wp_create_nonce($user_email.$user_id);
?>
<div class="p_popup" data-p_popup="<?php echo $view_args[ 'id' ]; ?>">
    <div class="p_popup-inner">
        <a class="p_popup-close" data-p_popup-close="<?php echo $view_args[ 'id' ] ?>" href="#">x</a>
        
    	<div class="p_popup-header">
			<h2 class="p_popup-title"><span><?php echo $view_args[ 'label' ] ?></span></h2>
			<div class="p_popup-notices uk-width-1-1"></div>
        </div>
        <div class="p_popup-content" data-volunteers="<?php echo implode(',', $view_args['volunteers']); ?>" data-user="<?php echo $user_id; ?>" data-user-email="<?php echo $user_email; ?>" data-nonce="<?php echo $user_email_nonce; ?>">
        	<?php if (function_exists('ninja_forms_display_form')) {
                ninja_forms_display_form(1);
            } ?>
        </div>
        <div class="p_popup-footer">
			<button class="button button-primary philanthropy-add-to-cart uk-width-1-1 uk-width-small-5-10 uk-width-medium-4-10" id="submit-volunteers"><?php _e( 'Send', 'philanthropy' ); ?></button>
        </div>
    </div>
</div>