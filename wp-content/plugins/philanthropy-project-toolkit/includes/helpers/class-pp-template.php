<?php
/**
 * PP template
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Template' ) ) :

	/**
	 * PP_Template
	 *
	 * @since 		1.0.0
	 */
	class PP_Template {

		/**
		 * Theme template path.
		 *
		 * @var 	string
		 * @access 	protected
		 */
		protected $theme_template_path;

		/**
		 * Template names to be loaded.
		 *
		 * @var 	string[]
		 * @access 	protected
		 */
		protected $template_names;

		/**
		 * Whether to load template file if it is found.
		 *
		 * @var 	boolean
		 * @access 	protected
		 */
		protected $load;

		/**
		 * Whether to use require_once or require.
		 *
		 * @var 	boolean
		 * @access 	protected
		 */
		protected $require_once;

		/**
		 * The arguments available in the view.
		 *
		 * @var 	array
		 * @access  protected
		 */
		protected $view_args;

		/**
		 * Class constructor.
		 *
		 * @param 	string|array $template_name A single template name or an ordered array of template.
		 * @param 	bool 		 $load          If true the template file will be loaded if it is found.
	 	 * @param 	bool 		 $require_once  Whether to require_once or require. Default true. Has no effect if $load is false.
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function __construct( $template_name, $load = true, $require_once = true ) {
			$this->load                = $load;
			$this->theme_template_path = $this->get_theme_template_path();
	        $this->base_template_path  = $this->get_base_template_path();
			$this->template_names      = apply_filters( 'pp_template_name', (array) $template_name );
			$this->view_args           = array();

			if ( $this->load ) {
				$this->render( $require_once );
			}
		}

		/**
	     * Set theme template path.
	     *
	     * @return  string
	     * @access  public
	     * @since   1.0.0
	     */
	    public function get_theme_template_path() {
	        return trailingslashit( apply_filters( 'pp_theme_template_path', 'pp' ) );
	    }

	    /**
	     * Return the base template path.
	     *
	     * @return  string
	     * @access  public
	     * @since   1.0.0
	     */
	    public function get_base_template_path() {
	        return PP()->plugin_path() . '/templates/';
	    }

		/**
		 * Adds an array of view arguments.
		 *
		 * @param 	array $args Arguments to include in the view.
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function set_view_args( $args ) {
			$this->view_args = array_merge( $this->view_args, $args );
		}

		/**
		 * Adds an argument to be accessed within the view.
		 *
		 * @param 	string $key   The key of the argument.
		 * @param 	mixed  $value The vaalue of the argument.
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function set_view_arg( $key, $value ) {
			$this->view_args[ $key ] = $value;
		}

		/**
		 * Renders the template.
		 *
		 * @param 	boolean $require_once Whether the template should be loaded with include_once instead of include.
		 * @return 	false|string False if the template does not exist. Template file otherwise.
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function render( $require_once = true ) {
			/* Make sure that the template file exists. */
			$template = $this->locate_template();

			if ( ! $this->template_file_exists( $template ) ) {
				charitable_get_deprecated()->doing_it_wrong(
					__FUNCTION__,
					sprintf( '<code>%s</code> does not exist.', $template ),
					'1.0.0'
				);
				return false;
			}

			if ( $template ) {

				$view_args = $this->view_args;

				if ( $this->require_once ) {
					include_once( $template );
				} else {
					include( $template );
				}
			}

			return $template;
		}

		/**
		 * Locate the template file of the highest priority.
		 *
		 * @uses 	locate_template()
		 * @return 	string
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function locate_template() {
			/* Template options are first checked in the theme/child theme using locate_template. */
			$template = locate_template( $this->get_theme_template_options(), false );

			/* No templates found in the theme/child theme, so use the plugin's default template. */
			if ( ! $template ) {
				$template = $this->base_template_path . $this->template_names[0];
			}

			return apply_filters( 'pp_locate_template', $template, $this->template_names );
		}

		/**
		 * Checks whether the template file exists.
		 *
		 * @param 	string $template The template file to check for.
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function template_file_exists( $template = '' ) {
			if ( empty( $template ) ) {
				$template = $this->locate_template();
			}

			return file_exists( $template );
		}

		/**
		 * Return the theme template options for a specific template.
		 *
		 * @return 	string[]
		 * @access 	protected
		 * @since 	1.0.0
		 */
		protected function get_theme_template_options() {
			$options = array();

			foreach ( $this->template_names as $template_name ) {
				$options[] = $this->theme_template_path . $template_name;
				$options[] = $template_name;
			}

			return $options;
		}
	}

endif;
