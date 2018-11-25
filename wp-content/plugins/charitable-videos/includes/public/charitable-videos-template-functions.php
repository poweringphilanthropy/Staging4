<?php
/**
 * Charitable Video Template Functions.
 *
 * Functions used with template hooks.
 *
 * @package     Charitable Videos/Functions/Templates
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Displays a template.
 *
 * @param   string|array $template_name A single template name or an ordered array of template
 * @param   array        $args Optional array of arguments to pass to the view.
 * @return  Charitable_Videos_Template
 * @since   1.0.0
 */
function charitable_videos_template( $template_name, array $args = array() ) {

	if ( empty( $args ) ) {
		$template = new Charitable_Videos_Template( $template_name );
	} else {
		$template = new Charitable_Videos_Template( $template_name, false );
		$template->set_view_args( $args );
		$template->render();
	}

	return $template;

}

if ( ! function_exists( 'charitable_videos_template_campaign_video' ) ) :

	/**
	 * Output the campaign video.
	 *
	 * @param   Charitable_Campaign $campaign
	 * @return  void
	 * @since   1.0.0
	 */
	function charitable_videos_template_campaign_video( $campaign ) {
		charitable_videos_template( 'campaign-video.php', array( 'campaign' => $campaign ) );
	}

endif;
