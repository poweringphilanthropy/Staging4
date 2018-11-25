<?php 
/**
 * Displays create child campaign form
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$campaign = $view_args[ 'campaign' ];
$child_id = (!empty($view_args[ 'child' ])) ? absint( $view_args[ 'child' ] ) : 0;
$button_title = (!empty($child_id)) ? __('Update Fundraising Page', 'pp-toolkit') : __('Start a Fundraising Page', 'pp-toolkit');

$height         = 400;
$width          = 1200;
$placeholder    = '(Ideal size is 1200px wide by 400px high)';
$preview_width_css  = 'max-width: 1200px;';
$nonce          = wp_create_nonce( 'save_imagedata' );

$user = get_userdata( get_current_user_id() );
$_suffix_title = array();
if(!empty($user->user_firstname)){
    $_suffix_title[] = $user->user_firstname;
}
if(!empty($user->user_lastname)){
    $_suffix_title[] = $user->user_lastname;
}

?>
<div class="layout-wrapper">
	<div id="primary" class="content-area <?php if ( ! is_active_sidebar( 'sidebar_campaign' ) ) : ?>no-sidebar<?php endif ?>">
		
		<div class="form-title">
			<h2 class="bolder"><?php echo sprintf(__('Start a fundraising page on behalf of - %s', 'pp-toolkit'), get_the_title( $campaign->ID )); ?></h2>
		</div>

		<?php charitable_template_notices(); ?>

		<form method="post" id="charitable-team-fundraising-form" class="charitable-form" enctype="multipart/form-data" novalidate>
			
			<?php  
			$title = sprintf('%s - %s', $campaign->post_title, implode(' ', $_suffix_title));
			if(!empty($child_id)){
				$title = get_post_field( 'post_title', $child_id );
			}
			
			?>
			<div class="uk-grid field-wrapper">
				<div class="uk-width-10-10">
					<div id="charitable_field_goal" class="charitable-form-field charitable-form-field-text required-field fullwidth">	
						<label for="campaign_title"> <?php _e('Title', 'pp-toolkit'); ?> <abbr class="required" title="required">*</abbr> </label>
						<input type="text" name="title" id="campaign_title" value="<?php echo $title; ?>" placeholder="<?php _e('Enter new text to show here', 'pp-toolkit'); ?>">
					</div>
				</div>
			</div>


			<?php  
			$campaign_goal = get_post_meta( $child_id, '_campaign_goal', true );
			
			$post_ID = get_the_ID();
			$auth = get_post($post_ID); // gets author from post
			$authid = $auth->post_author; // gets author id for the post
		    $crmaccount_id = get_usermeta($authid,'crm_account_id',true);
			?>
			<div class="uk-grid field-wrapper">
				<div class="uk-width-10-10">
					<div id="charitable_field_goal" class="charitable-form-field charitable-form-field-number required-field fullwidth">	
						<label for="charitable_field_goal_element"> <?php _e('Your Fundraising Goal', 'pp-toolkit'); ?> <abbr class="required" title="required">*</abbr> </label>
						<input type="number" name="goal" id="charitable_field_goal_element" input value="25" min="0" value="<?php echo $campaign_goal; ?>">
						<input type="hidden" name="campaign_crm_account_id" value="<?php echo $crmaccount_id; ?>">
					</div>
				</div>
			</div>


			<?php
			/**
			 * Campaign Image
			 * @var [type]
			 */
			$use_parent_image = get_post_meta( $child_id, 'use_parent_image', true ) != 'off';
			$value = ($use_parent_image) ? '' : get_post_meta( $child_id, '_thumbnail_id', true );
			$attachment     = (!empty($value)) ? wp_get_attachment_image_src($value, 'full') : array();
			$img_url        = (isset($attachment[0])) ? $attachment[0] : '';
			?>
			<div class="uk-grid field-container field_image">
				<div class="uk-width-7-10 uk-width-medium-8-10">
					<div id="charitable_field_image" class="charitable-form-field charitable-form-field-image-crop fullwidth">	
						<label for="charitable_field_image_element"> <?php _e('Use the image from the main campaign?', 'pp-toolkit'); ?></label>
						<div class="field-wrapper hidden">
							<div class="image-crop campaign_image_image-editor" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>" data-nonce="<?php echo $nonce; ?>">

						        <div class="cropit-placeholder inited initial <?php echo (empty($img_url)) ? '' : 'loaded'; ?>" style="<?php echo $preview_width_css; ?>">
						            <?php if(empty($img_url)){ ?>
						            <div class="placeholder-content">
						                <div class="input-file"><i class="fa fa-plus"></i> Add Photo</div>
						                <div class="placeholder-text"><?php echo $placeholder; ?></div>
						            </div>
						            <?php } else { ?>
						            <img src="<?php echo $img_url; ?>" class="cropit-result">
						            <?php } ?>
						        </div>

						        <div class="imgcrop-popup-wrapper">
						            <div class="imgcrop-content">
						                <div class="cancel-wrapper">
						                    <div class="imgcrop-title">Select Image</div>
						                    <a href="#" class="imgcrop-cancel">x</a>
						                </div>
						                <div class="image-editor">
						                    <div class="cropit-preview" style="<?php echo $preview_width_css; ?>"></div>
						                    <div class="controls-wrapper <?php echo (empty($img_url)) ? 'hidden' : ''; ?>">
						                        <div class="editor-wrapper">
						                        	<span class="fa fa-undo fa-flip-horizontal" editor-action="rotateCW"></span>
						                            <div class="slider-wrapper">
						                                <!-- <span class="dashicons dashicons-format-image small-icon"></span> -->
						                                <input type="range" class="cropit-image-zoom-input custom">
						                                <!-- <span class="dashicons dashicons-format-image"></span> -->
						                            </div>
						                            <span class="fa fa-undo" editor-action="rotateCCW"></span>
						                        </div>
						                        <div class="input-wrapper">
						                            <input type="file" class="cropit-image-input" accept="image/*">
						                            <input type="hidden" name="campaign_image" class="image-id" value="<?php echo $value; ?>">
						                            <input type="hidden" name="" class="image-url" value="<?php echo $img_url; ?>">
						                            <textarea name="" class="image-uri" id="" cols="30" rows="10" style="display: none;"></textarea>
						                            <a href="#" class="select-image editor-button"><?php _e('Select image', 'pp-toolkit'); ?></a>
						                            <a href="#" class="save-image editor-button disabled"><?php _e('Save image', 'pp-toolkit'); ?></a>
						                        </div>
						                    </div>
						                </div>
						            </div>
						        </div>
						        
						    </div>
						</div>
					</div>
				</div>
				<div class="uk-width-3-10 uk-width-medium-2-10">
					<div class="switch-button small-switch">
						<input type="checkbox" class="toggle-override" id="use_parent_image" name="use_parent_image" value="on" <?php checked( $use_parent_image ); ?>><label for="use_parent_image"></label>
					</div>
				</div>
			</div>

			<?php
			/**
			 * Campaign Desc
			 * @var [type]
			 */
			$use_parent_description = get_post_meta( $child_id, 'use_parent_description', true ) != 'off';
			$campaign_description = ($use_parent_description) ? '' : get_post_meta( $child_id, '_campaign_description', true );
			?>
			<div class="uk-grid field-container field_description">
				<div class="uk-width-7-10 uk-width-medium-8-10">
					<div id="charitable_field_description" class="charitable-form-field charitable-form-field-textarea fullwidth">	
						<label for="charitable_field_desc_element"> <?php _e('Use the description from the the main campaign?', 'pp-toolkit'); ?></label>
						<div class="field-wrapper hidden">
							<?php 
							wp_editor( $campaign_description, 'charitable_field_desc_element', array(
								'textarea_name' => 'campaign_description',
								'media_buttons' => false,
								'teeny' => false,
								'quicktags'     => false,
								'tinymce'       => array(
									'toolbar1'	=> 'bold,italic,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink',
									'toolbar2'	=> false,
									'toolbar3'	=> false,
									'toolbar4'	=> false,
								),
							) ); 
							?>
						</div>
					</div>
				</div>
				<div class="uk-width-3-10 uk-width-medium-2-10">
					<div class="switch-button">
						<input type="checkbox" class="toggle-override" id="use_parent_description" name="use_parent_description" value="on" <?php checked( $use_parent_description ); ?>><label for="use_parent_description"></label>
					</div>
				</div>
			</div>

			
			<?php
			/**
			 * Campaign Video
			 * @var [type]
			 */
			$use_parent_video = get_post_meta( $child_id, 'use_parent_video', true ) != 'off';
			$campaign_video = ($use_parent_video) ? '' : get_post_meta( $child_id, '_campaign_video', true );
			?>
			<div class="uk-grid field-container field_video">
				<div class="uk-width-7-10 uk-width-medium-8-10">
					<div id="charitable_field_video" class="charitable-form-field charitable-form-field-textarea fullwidth">	
						<label for="charitable_field_video_element"> <?php _e('Use the video from the main campaign?', 'pp-toolkit'); ?></label>
						<div class="field-wrapper hidden">
							<textarea name="campaign_video" id="charitable_field_video_element" cols="30" rows="10" placeholder="<?php _e('Embed a featured video by entering the &quot;share&quot; URL from almost any source. (Examples: https://youtu.be/videoID or https://vimeo.com/videoID or https://www.facebook.com/videoID) ', 'pp-toolkit'); ?>"><?php echo $campaign_video; ?></textarea>
						</div>
					</div>
				</div>
				<div class="uk-width-3-10 uk-width-medium-2-10">
					<div class="switch-button">
						<input type="checkbox" class="toggle-override" id="use_parent_video" name="use_parent_video" value="on" <?php checked( $use_parent_video ); ?>><label for="use_parent_video"></label>
					</div>
				</div>
			</div>

			
			<?php
			/**
			 * Campaign Content
			 * @var [type]
			 */
			$use_parent_content = get_post_meta( $child_id, 'use_parent_content', true ) != 'off';
			$campaign_content = ($use_parent_content) ? '' : get_post_field( 'post_content', $child_id, 'edit' );
			?>
			<div class="uk-grid field-container field_content">
				<div class="uk-width-7-10 uk-width-medium-8-10">
					<div id="charitable_field_content" class="charitable-form-field charitable-form-field-textarea fullwidth">	
						<label for="charitable_field_content_element"> <?php _e('Use the "About Our Philanthropy" from the main campaign?', 'pp-toolkit'); ?></label>
						<div class="field-wrapper hidden">
							<?php 
							wp_editor( $campaign_content, 'charitable_field_content_element', array(
								'textarea_name' => 'campaign_content',
								'media_buttons' => false,
								'teeny' => false,
								'quicktags'     => false,
								'tinymce'       => array(
									'toolbar1'	=> 'bold,italic,bullist,numlist,blockquote,justifyleft,justifycenter,justifyright,link,unlink',
									'toolbar2'	=> false,
									'toolbar3'	=> false,
									'toolbar4'	=> false,
								),
							) ); 
							?>
						</div>
					</div>
				</div>
				<div class="uk-width-3-10 uk-width-medium-2-10">
					<div class="switch-button">
						<input type="checkbox" class="toggle-override" id="use_parent_content" name="use_parent_content" value="on" <?php checked( $use_parent_content ); ?>><label for="use_parent_content"></label>
					</div>
				</div>
			</div>


			<?php
			/**
			 * Campaign Media
			 * @var [type]
			 */
			$use_parent_media_embed = get_post_meta( $child_id, 'use_parent_media_embed', true ) != 'off';
			$campaign_media_embed = ($use_parent_media_embed) ? '' : get_post_meta( $child_id, '_campaign_media_embed', true );
			?>
			<div class="uk-grid field-container field_media_embed">
				<div class="uk-width-7-10 uk-width-medium-8-10">
					<div id="charitable_field_media_embed" class="charitable-form-field charitable-form-field-textarea fullwidth">	
						<label for="charitable_field_media_embed_element"> <?php _e('Use the embedded media from the main campaign?', 'pp-toolkit'); ?></label>
						
												
						<div class="field-wrapper hidden">
							<textarea name="campaign_media_embed" id="charitable_field_media_embed_element" cols="30" rows="10" placeholder="<?php _e('Embed a video, picture, music, document, etc. by entering the &quot;share&quot; URL from almost any source. (Examples: https://youtu.be/videoID or https://www.instagram.com/p/Bb8SRz9HGim/?taken-by=beyonce) ', 'pp-toolkit'); ?>"><?php echo $campaign_media_embed; ?></textarea>
						</div>
					</div>
				</div>
				<div class="uk-width-3-10 uk-width-medium-2-10">
					<div class="switch-button">
						<input type="checkbox" class="toggle-override" id="use_parent_media_embed" name="use_parent_media_embed" value="on" <?php checked( $use_parent_media_embed ); ?>><label for="use_parent_media_embed"></label>
					</div>
				</div>
			</div>
			
			<div class="button-wrapper">
				<?php wp_nonce_field( 'save-team-fundraising', '_save_team_fundraising' ); ?>
				<input type="hidden" name="child_id" value="<?php echo $child_id; ?>">
				<input type="hidden" name="parent_campaign" value="<?php echo $campaign->ID; ?>">
				<button class="button" type="submit"><?php echo $button_title; ?></button>
				<a class="button cancel-create" href="<?php echo get_permalink( $campaign->ID ); ?>"><?php _e('Cancel', 'pp-toolkit'); ?></a>
			</div>
		</form>
	</div><!-- #primary -->

	<?php get_sidebar( 'campaign' ) ?>                  

</div><!-- .layout-wrapper -->