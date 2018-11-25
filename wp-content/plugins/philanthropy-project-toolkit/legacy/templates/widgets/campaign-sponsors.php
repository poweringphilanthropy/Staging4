<?php
/**
 * Display a list of sponsors.
 *
 * Override this template by copying it to yourtheme/charitable/widgets/campaign-sponsors.php
 * @since   1.0.0
 */

// $campaign = $view_args['campaign'];

if ( !isset($view_args['sponsors']) || empty($view_args['sponsors']) ) :
	return;
endif;

echo $view_args['before_widget'];

if ( ! empty( $view_args['title'] ) ) :

	echo $view_args['before_title'] . $view_args['title'] . $view_args['after_title'];

endif;
?>
<div class="pp-campaign-sponsors widget-block">
	<div class="uk-grid">
	<?php foreach ( (array) $view_args['sponsors'] as $key => $sponsor) { ?>
		<div class="uk-width-1-3 sponsor-item">
			<a target="_blank" href="<?php echo (isset($sponsor['link'])) ? addhttp($sponsor['link']) : ''; ?>">
				<?php echo wp_get_attachment_image( $sponsor['img_id'] ); ?>
			</a>
		</div>
	<?php } ?>

	</div>
</div>

<?php
echo $view_args['after_widget'];