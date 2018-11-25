<?php

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Campaigns_Export' ) ) :

	/**
	 * PP_Campaigns_Export
	 *
	 * @since       1.0.0
	 */
	class PP_Campaigns_Export {

		/**
		 * @var     string  The type of export.
		 */
		const EXPORT_TYPE = '';

		/**
		 * @var     string[] The CSV's columns.
		 * @access  protected
		 */
		protected $columns;

		/**
		 * @var     mixed[] Optional array of arguments.
		 * @access  protected
		 */
		protected $args;

		protected $defaults = array(
			'filename' => 'pp-campaigns-export',
			'columns' => array(),
			'data' => array(),
		);

		/**
		 * Create class object.
		 *
		 * @param   mixed[] $args
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args ) {
			$this->args = wp_parse_args( $args, $this->defaults );

			$this->columns = $this->args['columns'];

			$this->export();
		}

		protected function get_filename(){
			return $this->args['filename'];
		}

		/**
		 * Print the CSV document headers.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function print_headers() {
			ignore_user_abort( true );

			if ( ! charitable_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
				set_time_limit( 0 );
			}

			/* Check for PHP 5.3+ */
			if ( function_exists( 'get_called_class' ) ) {
				$class  = get_called_class();
				$export = $class::EXPORT_TYPE;
			} else {
				$export = '';
			}

			nocache_headers();
			header( "Content-Type: text/csv; charset=utf-8" );
			// header( "Content-Disposition: attachment; filename=Campaign Export - " . date( 'm-d-Y' ) . ".csv" );
			
			// fix filename and extension issue on firefox
			$filename = $this->get_filename();
			header( "Content-Disposition: attachment; filename=\"$filename.csv\";" );
			header( "Expires: 0" );
		}

		/**
		 * Export the CSV file.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function export() {

			/**
			 * TODO CHECK CAN EXPORT
			 */

			$data = array_map( array( $this, 'map_data' ), $this->get_data() );

	        // echo "<pre>";
	        // print_r($data);
	        // echo "</pre>";
	        // exit();

			$this->print_headers();

			/* Create a file pointer connected to the output stream */
			$output = fopen( 'php://output', 'w' );

			/* Print first row headers. */
			fputcsv( $output, array_values( $this->columns ) );

			/* Print the data */
			foreach ( $data as $row ) {
				fputcsv( $output, $row );
			}

			fclose( $output );

			exit();
		}

		/**
		 * Receives a row of data and maps it to the keys defined in the columns.
		 *
		 * @param   object|array $data
		 * @return  mixed
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function map_data( $data ) {
			/* Cast the data to array */
			if ( ! is_array( $data ) ) {
				$data = (array) $data;
			}

			$row = array();

			foreach ( $this->columns as $key => $label ) {
				$value = isset( $data[ $key ] ) ? $data[ $key ] : '';

				if($key == 'amount'){
					$value = number_format($value, 2);
				}

				$value = stripslashes( html_entity_decode($value, ENT_QUOTES, 'utf-8') );

				$value = apply_filters( 'pp_export_data_key_value', $value, $key, $data );
				$row[] = $value;
			}

			return $row;
		}

		protected function get_data(){
			
			$data = $this->args['data'];

			return apply_filters( 'pp_export_data', $data, $this->args );
		}
	}

endif; // End class_exists check
