<?php
/**
 * Display the main settings page wrapper.
 *
 * @author    Eric Daams
 * @package   Charitable/Admin View/Settings
 * @copyright Copyright (c) 2018, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 * @version   1.6.0
 */

$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
$group      = isset( $_GET['group'] ) ? $_GET['group'] : $active_tab;
$sections   = charitable_get_admin_settings()->get_sections();

ob_start();
?>
<div id="charitable-settings" class="wrap">
	<h1 class="screen-reader-text"><?php echo get_admin_page_title(); ?></ha>
	<h2 class="nav-tab-wrapper">
		<?php foreach ( $sections as $tab => $name ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $tab ), admin_url( 'admin.php?page=charitable-settings' ) ) ); ?>" class="nav-tab <?php echo $active_tab == $tab ? 'nav-tab-active' : ''; ?>"><?php echo $name; ?></a>
		<?php endforeach ?>
	</h2>
	<?php if ( $group != $active_tab ) : ?>
		<?php /* translators: %s: active settings tab label */ ?>
		<p><a href="<?php echo esc_url( add_query_arg( array( 'tab' => $active_tab ), admin_url( 'admin.php?page=charitable-settings' ) ) ); ?>"><?php printf( __( '&#8592; Return to %s', 'charitable' ), $sections[ $active_tab ] ); ?></a></p>
	<?php endif ?>
	<?php
		/**
		 * Do or render something right before the settings form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $group The settings group we are viewing.
		 */
		do_action( 'charitable_before_admin_settings', $group );
	?>
	<form method="post" action="options.php">
		<table class="form-table">
		<?php
			settings_fields( 'charitable_settings' );

			charitable_do_settings_fields( 'charitable_settings_' . $group, 'charitable_settings_' . $group );
		?>
		</table>
		<?php
			/**
			 * Filter the submit button at the bottom of the settings table.
			 *
			 * @since 1.6.0
			 *
			 * @param string $button The button output.
			 */
			echo apply_filters( 'charitable_settings_button_' . $group, get_submit_button( null, 'primary', 'submit', true, null ) );
		?>
	</form>
	<?php
		/**
		 * Do or render something right after the settings form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $group The settings group we are viewing.
		 */
		do_action( 'charitable_after_admin_settings', $group );
	?>
</div>
<?php
echo ob_get_clean();
