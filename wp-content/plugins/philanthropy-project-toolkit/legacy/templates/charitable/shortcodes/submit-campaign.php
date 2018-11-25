<?php
/**
 * The template used to display the campaign submission form.
 *
 * Override this template by copying it to yourtheme/pp_toolkit/shortcodes/submit-campaign.php
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

$form 	= $view_args[ 'form' ];
$fields = $form->get_current_page_fields();
$donor	= new Charitable_User( get_current_user_id() );

// echo "<pre>";
// print_r($fields);
// echo "</pre>";

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
		
		<div id="form-wizard" class="charitable-form-fields cf">

			<ul>
				<?php 
				foreach ($fields as $key => $field) { 
				$key = str_replace('_fields', '', $key);
				?>
				<li><a href="#<?php echo $key; ?>"><?php echo $field['legend']; ?><br /><small>Step description</small></a></li>
				<?php } ?>
	        </ul>
	        <div>
	        	<?php
				$form->view()->render_notices();
				$form->view()->render_honeypot();
				$form->view()->render_hidden_fields();
				$i = 1;

				foreach ( $fields as $key => $field ) :
				$key = str_replace('_fields', '', $key);
				echo '<div id="'.$key.'" class="">';
					$form->view()->render_field( $field, $key, array(
						'index' => $i,
					) );

					$i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form , $i);
					// do_action( 'charitable_form_field', $field, $key, $form, $i );

					// $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form );

				echo '</div>';
				endforeach;

				?>
	    	</div>

	    	<div class="navbar btn-toolbar sw-toolbar sw-toolbar-bottom">
				<button class="button sw-btn-prev" type="button">Previous</button>
	            <button class="button sw-btn-next" type="button">Next</button>
				<?php echo $form->get_submit_buttons() ?>		
				
			</div>
		</div>

		<?php
		/**
		 * @hook 	charitable_form_after_fields
		 */
		do_action( 'charitable_form_after_fields', $form );

		?>
	</form>
	<?php

	/**
	 * @hook 	charitable_campaign_submission_after
	 */
	do_action('charitable_campaign_submission_after');

endif;