<?php 
/**
 * The template used to display submit buttons
 *
 * Override this template by copying it to yourtheme/charitable/form-fields/finished-button.php
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
    return;
}

$form           = $view_args[ 'form' ];
$field          = $view_args[ 'field' ];
$buttons        = isset( $field[ 'buttons' ] ) ? $field[ 'buttons' ] : array();

if ( empty( $buttons ) ) {
    return;
}
?>

<div class="uk-grid finished-buttons-container">
	<?php foreach ($buttons as $key => $data) { ?>
	<div class="uk-width-1-<?php echo count($buttons); ?> button-container <?php echo $key; ?>">
		<a class="trigger-submit" data-trigger="<?php echo $key; ?>">
			<div class="icon">
				<img src="<?php echo $data['icon']; ?>" alt="<?php echo $data['label']; ?>">
			</div>
			<div class="label"><?php echo $data['label']; ?></div>
		</a>
	</div>
	<?php } ?>
</div>