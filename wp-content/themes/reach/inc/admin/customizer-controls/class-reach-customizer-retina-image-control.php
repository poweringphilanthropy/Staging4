<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'WP_Customize_Control' ) ) { return; }

if ( ! class_exists( 'Reach_Customizer_Retina_Image_Control' ) ) :

	/**
	 * Adds a new control for retina images.
	 *
	 * @author      Studio 164a
	 * @category    Admin
	 * @package     Reach/Admin/Customizer/Controls
	 * @since       1.0
	 */
	class Reach_Customizer_Retina_Image_Control extends WP_Customize_Image_Control {

		/**
		 * @access public
		 * @var    string
		 */
		public $type = 'image';

		/**
		 * @var    Reach_Theme
		 */
		private $theme;

		/**
		 * Constructor.
		 *
		 * If $args['settings'] is not defined, use the $id as the setting ID.
		 *
		 * @uses   WP_Customize_Image_Control::__construct()
		 * @param  WP_Customize_Manager $manager
		 * @param  string $id
		 * @param  array $args
		 * @since  1.0.0
		 */
		public function __construct( $manager, $id, $args ) {
			parent::__construct( $manager, $id, $args );

			$this->theme = reach_get_theme();

			/**
			 * Usually, this control will be instantiated during the customize_register
			 * event, so if that is the current filter, call the method directly.
			 */
			if ( current_filter() == 'customize_register' ) {
				$this->customize_register( $manager );
			} /**
			 * If the customize_register action has not taken place yet, register the
			 * hook.
			 */
			elseif ( ! did_action( 'customize_register' ) ) {
				add_action( 'customize_register', array( $this, 'customize_register' ) );
			} /**
			 * The customize_register action has come and gone, so this control will fail.
			 */
			else {
				wp_die( __( 'Reach_Customizer_Retina_Image_Control must be instantiated before or during the customize_register action.', 'reach' ) );
			}

			add_action( 'customize_save', array( $this, 'customize_save' ) );
			add_action( 'customize_save_after', array( $this, 'customize_save_after' ) );
		}

		/**
		 * Register an extra control & setting for any setting using this control.
		 *
		 * @see    customize_register
		 *
		 * @param  WP_Customize_Manager $wp_customize
		 * @return void
		 * @since  1.0.0
		 */
		public function customize_register( $wp_customize ) {
			$checkbox_id = $this->id . '_is_retina';

			$wp_customize->add_setting( $checkbox_id, array(
				'transport' => 'postMessage',
				'sanitize_callback' => 'absint',
			) );

			$wp_customize->add_control( $checkbox_id, array(
				'settings' => $checkbox_id,
				'label' => __( 'Is this image retina-ready?', 'reach' ),
				'section' => $this->section,
				'priority' => $this->priority + 0.2,
				'type' => 'checkbox',
			) );
		}

		/**
		 * Renders the control wrapper and calls $this->render_content() for the internals.
		 *
		 * @since   1.0.0
		 */
		protected function render() {
			$id    = 'customize-control-' . str_replace( array( '[', ']' ), array( '-', '' ), $this->id );
			$class = 'customize-control customize-control-image customize-control-retina-image';

			?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
				<?php $this->render_content(); ?>
			</li><?php
		}

		/**
		 * Before the customizer has finished saving each of the fields, store the current image state.
		 *
		 * @see customize_save hook
		 * @see Crafted_Customizer::set_current_image_state()
		 *
		 * @param WP_Customize_Manager $wp_customize
		 * @return void
		 * @access public
		 * @since   1.0.0
		 */
		public function customize_save( WP_Customize_Manager $wp_customize ) {
			$this->set_current_image_state();
		}

		/**
		 * After the customizer has finished saving each of the fields, check whether we're using a retina image.
		 *
		 * @see customize_save_after hook
		 *
		 * @param WP_Customize_Manager $wp_customize
		 * @return void
		 * @access public
		 * @since   1.0.0
		 */
		public function customize_save_after( WP_Customize_Manager $wp_customize ) {
			/**
			 * If the image has changed, update the meta we store for the image.
			 */
			if ( $this->is_image_changed() ) {
				$this->update_image( $this->theme->get_theme_setting( $this->id ) );
			}
		}

		/**
		 * Save the image's ID and dimensions.
		 *
		 * If the image is optimised for retina, create a non-retina copy.
		 *
		 * @param  string $image_url
		 * @return void
		 * @access private
		 * @since  1.0.0
		 */
		private function update_image( $image_url ) {

			if ( empty( $image_url ) ) {

				set_theme_mod( $this->id . '_id', 0 );
				set_theme_mod( $this->id . '_width', 0 );
				set_theme_mod( $this->id . '_height', 0 );
				return;

			}

			/**
			 * Save the image's post ID, width and height to the theme settings.
			 */
			$image_id   = attachment_url_to_postid( $image_url );
			$image_meta = wp_get_attachment_metadata( $image_id );

			set_theme_mod( $this->id . '_id', $image_id );

			/**
			 * If the image is retina-optimised, create a non-retina copy.
			 */
			$non_retina = false;

			if ( $this->theme->get_theme_setting( $this->id . '_is_retina' ) ) {
				$non_retina = $this->create_non_retina_copy( $image_id, $image_meta );
			}

			/**
			 * If the image is not retina optimised or something went wrong while trying
			 * to save the retina image, save the original image's dimensions.
			 */
			if ( false === $non_retina || is_wp_error( $non_retina ) ) {
				set_theme_mod( $this->id . '_width', $image_meta['width'] );
				set_theme_mod( $this->id . '_height', $image_meta['height'] );
			}
		}

		/**
		 * Create a non-retina version of the image.
		 *
		 * @param int $image_id The post ID of the original image.
		 * @param array image_meta An array containing the image's URL, width and height.
		 * @return WP_Error|array
		 * @access private
		 * @since   1.0.0
		 */
		private function create_non_retina_copy( $image_id, array $image_meta ) {

			/**
			 * Retrieve the path to the image.
			 */
			$image_path = get_attached_file( $image_id );

			/**
			 * Use the path to rewrite the filename.
			 */
			$filename = explode( '/', $image_path );
			$filename = $filename[ count( $filename ) - 1 ];
			$resized_path = str_replace( $filename, 'resized_' . $filename, $image_path );

			/**
			 * Create a WP_Image_Editor instance of the image.
			 */
			$resized_image = wp_get_image_editor( $image_path );

			/**
			 * Calculate the dimensions of the non-retina version.
			 *
			 * This will be half the retina version, rounded down and divided by two.
			 */
			$resized_width = floor( $image_meta['width'] / 2 );
			$resized_height = floor( $image_meta['height'] / 2 );

			/**
			 * Resize the image and save it.
			 */
			$resized_image->resize( $resized_width, $resized_height, true );
			$saved = $resized_image->save( $resized_path );

			/**
			 * If something went wrong during the save process, return the result, which
			 * will be an instance of WP_Error.
			 */
			if ( is_wp_error( $saved ) ) {
				return $saved;
			}

			/**
			 * Save the image's dimensions.
			 */
			set_theme_mod( $this->id . '_width', $resized_width );
			set_theme_mod( $this->id . '_height', $resized_height );

			/**
			 * Save the non-retina version's image URL
			 */
			$upload_dir = wp_upload_dir();
			$resized_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $saved['path'] );
			update_post_meta( $image_id, '_non_retina', $resized_url );

			/**
			 * Return the resized image.
			 */
			return $saved;
		}

		/**
		 * A image's state is simply the contatenation of two values: its retina setting and its path.
		 *
		 * This is used to determine whether the image is changed when the customizer's settings are saved.
		 *
		 * @see Crafted_Customizer::set_current_image_state()
		 * @see Crafted_Customizer::is_image_changed()
		 *
		 * @return  string The state as a simple string.
		 * @access  private
		 * @since   1.0.0
		 */
		private function get_image_state() {
			return $this->theme->get_theme_setting( $this->id . '_is_retina', false ) . '_' . $this->theme->get_theme_setting( $this->id , false );
		}

		/**
		 * This sets the private image_state property to the current image state.
		 *
		 * @see Crafted_Customizer::get_image_state()
		 *
		 * @return void
		 * @access private
		 * @since   1.0.0
		 */
		private function set_current_image_state() {
			$this->image_state = $this->get_image_state();
		}

		/**
		 * Checks whether the image state has changed since it was originally set.
		 *
		 * @see Crafted_Customizer::get_image_state()
		 *
		 * @return bool
		 * @access private
		 */
		private function is_image_changed() {
			return $this->get_image_state() != $this->image_state;
		}

		/**
		 * Sanitize the value for the "is retina" checkbox.
		 *
		 * @param   mixed $input
		 * @return  bool
		 * @access  public
		 * @since   1.0.5
		 */
		public function sanitize_checkbox( $input ) {
			if ( $input ) {
				return true;
			}

			return false;
		}
	}

endif; // End class_exists check.
