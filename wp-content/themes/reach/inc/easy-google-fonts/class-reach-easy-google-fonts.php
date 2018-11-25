<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Reach_Easy_Google_Fonts' ) ) :

	/**
	 * Handles how Easy Google Fonts is integrated into the theme.
	 *
	 * @package 	Reach/Easy Google Fonts
	 * @author 		Studio 164a
	 * @since 		1.0.0
	 */
	class Reach_Easy_Google_Fonts {

		/**
		 * @var 	Reach_Theme
		 */
		private $theme;

		/**
		 * This creates an instance of this class.
		 *
		 * If the reach_theme_start hook has already run, this will not do anything.
		 *
		 * @param 	Reach_Theme 	$theme
		 * @static
		 * @access 	public
		 * @since 	1.0.0
		 */
		public static function start( Reach_Theme $theme ) {
			if ( ! $theme->is_start() ) {
				return;
			}

			new Reach_Easy_Google_Fonts();
		}

		/**
		 * Create object instance.
		 *
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function __construct() {
			add_filter( 'tt_default_body', array( $this, 'set_default_body_selector' ) );
			add_filter( 'tt_font_get_option_parameters', array( $this, 'set_default_font_options' ) );
		}

		/**
		 * Sets the body selector to 'body'.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_default_body_selector() {
			return 'body';
		}

		/**
		 * Set the default font options for the theme.
		 *
		 * @param 	array[]
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_default_font_options( $options ) {
			$options['tt_default_body']['title'] = __( 'Body Text', 'reach' );
			$options['tt_default_body']['description'] = __( 'Please select a font for the theme\'s body text.', 'reach' );

			$options['tt_default_site_title'] = array(
				'name'			=> 'tt_default_site_title',
				'title'			=> __( 'Site Title', 'reach' ),
				'description'	=> __( 'Please select a font for your site title.', 'reach' ),
				'properties'	=> array( 'selector' => '.site-title a' ),
			);

			$options['tt_default_site_tagline'] = array(
				'name'			=> 'tt_default_site_tagline',
				'title'			=> __( 'Site Tagline', 'reach' ),
				'description'	=> __( 'Please select a font for your site tagline.', 'reach' ),
				'properties'	=> array( 'selector' => '.site-tagline' ),
			);

			return $options;
		}
	}

endif;
