<?php

// uncomment this line for testing
//set_site_transient( 'update_plugins', null );

/**
 * Allows plugins to use their own update API.
 *
 * This is part of the EDD Software Licensing suite. Just renamed here for Charitable.
 *
 * @author  Pippin Williamson
 * @version 1.6
 */
class Charitable_Plugin_Updater {
	private $api_url  = '';
	private $api_data = array();
	private $name     = '';
	private $slug     = '';
	private $version  = '';

	/**
	 * Class constructor.
	 *
	 * @uses  plugin_basename()
	 * @uses  hook()
	 *
	 * @param string $_api_url     The URL pointing to the custom API endpoint.
	 * @param string $_plugin_file Path to the plugin file.
	 * @param array  $_api_data    Optional data to send with API calls.
	 */
	public function __construct( $_api_url, $_plugin_file, $_api_data = array() ) {
		$this->api_url  = trailingslashit( $_api_url );
		$this->api_data = $_api_data;
		$this->name     = plugin_basename( $_plugin_file );
		$this->slug     = basename( $_plugin_file, '.php' );
		$this->version  = $_api_data['version'];

		// Set up hooks.
		$this->init();
	}

	/**
	 * Set up WordPress filters to hook into WP's update process.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'load-plugins.php', array( $this, 'setup_update_notification' ), 30 );
		add_action( 'admin_init', array( $this, 'show_changelog' ) );
	}

	/**
	 * This disables the default update row shown for Charitable extensions,
	 * and sets up our own callback to show the update notification.
	 *
	 * @since  1.4.20
     *
     * @return void
     */
	public function setup_update_notification() {
		remove_action( 'after_plugin_row_' . $this->name, 'wp_plugin_update_row', 10 );
		add_action( 'after_plugin_row_' . $this->name, array( $this, 'show_update_notification' ), 10, 2 );
	}

	/**
	 * Show update nofication row.
	 *
	 * This is a drop-in replacement for wp_plugin_update_row(),
	 * which is the function that will normally show the update row
	 * for a plugin. Replaced here to show our changelog and also
	 * prompt to the user to renew/set their license if it has
	 * expired or hasn't been added yet.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file
	 * @param array  $plugin
	 */
	public function show_update_notification( $file, $plugin ) {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( $this->name != $file ) {
			return;
		}

		$version_info = Charitable_Licenses::get_instance()->get_version_info( $this->name );

		if ( ! $version_info ) {
			return;
		}

		if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {

			// Build a plugin list row, with update notification.
			echo '<tr class="plugin-update-tr" id="' . $this->slug . '-update" data-slug="' . $this->slug . '" data-plugin="' . $this->slug . '/' . $file . '">';
			echo '<td colspan="3" class="plugin-update colspanchange">';
			echo '<div class="update-message notice inline notice-warning notice-alt">';

			$changelog_link = self_admin_url( 'index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->name . '&slug=' . $this->slug . '&TB_iframe=true&width=772&height=911' );

			switch ( $version_info->download_link ) {
				case 'missing_license' :
					printf(
						__( '<p>There is a new version of %1$s available but you have not activated your license. <a target="_top" href="%2$s">Activate your license</a> or <a target="_blank" class="thickbox" href="%3$s">view version %4$s details</a>.</p>', 'charitable' ),
						esc_html( $version_info->name ),
						admin_url( 'admin.php?page=charitable-settings&tab=licenses' ),
						esc_url( $changelog_link ),
						esc_html( $version_info->new_version )
					);
					break;

				case 'expired_license' :
					$base_renewal_url = isset( $version_info->renewal_link ) ? $version_info->renewal_link : 'https://www.wpcharitable.com/account';

					printf(
						__( '<p>There is a new version of %1$s available but your license has expired. <a target="_blank" href="%2$s">Renew your license</a> or <a target="_blank" class="thickbox" href="%3$s">view version %4$s details</a>.</p>', 'charitable' ),
						esc_html( $version_info->name ),
						esc_url( add_query_arg( array(
							'utm_source' => 'plugin-upgrades', 
							'utm_medium' => 'wordpress-dashboard',
							'utm_campaign' => 'expired-license',
						), $base_renewal_url ) ),
						esc_url( $changelog_link ),
						esc_html( $version_info->new_version )
					);
					break;

				default : 
					printf(
						__( '<p>There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.</p>', 'charitable' ),
						esc_html( $version_info->name ),
						esc_url( $changelog_link ),
						esc_html( $version_info->new_version ),
						esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->name, 'upgrade-plugin_' . $this->name ) )
					);
					break;
			}

			do_action( "in_plugin_update_message-{$file}", $plugin, $version_info );

			echo '</div></td></tr>';
		}
	}

	/**
	 * Display the changelog.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function show_changelog() {
		if ( empty( $_REQUEST['edd_sl_action'] ) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] ) {
			return;
		}

		if ( empty( $_REQUEST['plugin'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['slug'] ) ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_die( __( 'You do not have permission to install plugin updates', 'charitable' ), __( 'Error', 'charitable' ), array( 'response' => 403 ) );
		}

		$version_info = Charitable_Licenses::get_instance()->get_version_info( $_REQUEST['plugin'] );

		if ( $version_info && isset( $version_info->sections['changelog'] ) ) {
			echo '<div style="padding:10px;">' . $version_info->sections['changelog'] . '</div>';
		}

		exit;
	}
}
