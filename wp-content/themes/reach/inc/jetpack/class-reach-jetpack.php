<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Reach_Jetpack' ) ) :

	/**
	 * Handles how Jetpack is integrated into the theme.
	 *
	 * @package 	Reach
	 * @subpackage 	Jetpack
	 * @author 		Studio 164a
	 * @since 		1.0.0
	 */
	class Reach_Jetpack {

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

			new Reach_Jetpack( $theme );
		}

		/**
		 * Create object instance.
		 *
		 * @return 	void
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function __construct( Reach_Theme $theme ) {
			$this->theme = $theme;

			add_action( 'after_setup_theme', array( $this, 'setup_jetpack' ) );
		}

		/**
		 * Define which Jetpack features the theme supports.
		 *
		 * @return 	void
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function setup_jetpack() {
			/**
			 * Add support for Infinite Scroll.
			 *
			 * @link 	http://jetpack.me/support/infinite-scroll/
			 */
			add_theme_support( 'infinite-scroll', array(
				'container' => 'main',
				'footer'    => 'page',
			) );
		}
	}

endif;
