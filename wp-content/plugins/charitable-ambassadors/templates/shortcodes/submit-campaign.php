<?php
/**
 * The template used to display the campaign submission form.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/shortcodes/submit-campaign.php
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

$form 	= $view_args[ 'form' ];
$fields = $form->get_current_page_fields();
$donor	= new Charitable_User( get_current_user_id() );

if ( ! $form->current_user_can_edit_campaign() ) : ?>

	<p><?php _e( 'You do not have permission to edit this campaign.', 'charitable-ambassadors' ) ?></p>

<?php 

else :

	/**
	 * @hook 	charitable_campaign_submission_before
	 */
	do_action('charitable_campaign_submission_before');

	?>
	<form method="post" id="charitable-campaign-submission-form" class="charitable-form" enctype="multipart/form-data">
		<?php 
		/**
		 * @hook 	charitable_form_before_fields
		 */
		do_action( 'charitable_form_before_fields', $form ) ?>
		
		<div class="charitable-form-fields cf">
			<?php 	
			$form->view()->render_notices();
			$form->view()->render_honeypot();
			$form->view()->render_hidden_fields();
			$form->view()->render_fields( $form->get_current_page_fields() ) ?>
		</div>

		<?php
		/**
		 * @hook 	charitable_form_after_fields
		 */
		do_action( 'charitable_form_after_fields', $form );

		?>
		<div class="charitable-form-field charitable-submit-field">

			<?php echo $form->get_submit_buttons() ?>		
			
		</div>
	</form>
	<?php

	/**
	 * @hook 	charitable_campaign_submission_after
	 */
	do_action('charitable_campaign_submission_after');

endif;