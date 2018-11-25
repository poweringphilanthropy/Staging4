<?php
/**
 * Class that is responsible for generating a CSV export of service hours.
 *
 * @package     Charitable/Classes/Philanthropy_Export_Service_Hours
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2015, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Philanthropy_Export_Service_Hours' ) ) :

	/* Include Charitable_Export base class. */
	if ( ! class_exists( 'Charitable_Export' ) ) {
		require_once( charitable()->get_path( 'admin' ) . 'reports/abstract-class-charitable-export.php' );
	}

	/**
	 * Philanthropy_Export_Service_Hours
	 *
	 * @since       1.0.0
	 */
	class Philanthropy_Export_Service_Hours extends Charitable_Export {

		/**
		 * @var     string  The type of export.
		 */
		const EXPORT_TYPE = 'service_hours';

		/**
		 * @var     mixed[] Array of default arguments.
		 * @access  protected
		 */
		protected $defaults = array(
			'export_type'   => 'chapter',
			'chapter_id'   => 'all',
			'dashboard_id' => 'all',
		);

		/**
		 * Create class object.
		 *
		 * @param   mixed[] $args
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args ) {

			parent::__construct( $args );
		}

		/**
		 * Return the CSV column headers.
		 *
		 * The columns are set as a key=>label array, where the key is used to retrieve the data for that column.
		 *
		 * @return  string[]
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_csv_columns() {
			$columns = array(
				'first_name'       => __( 'First Name', 'pp-toolkit' ),
				'last_name'       => __( 'Last Name', 'pp-toolkit' ),
				'chapter_name'       => __( 'Chapter Name', 'pp-toolkit' ),
				'service_hours'       => __( 'Service Hours', 'pp-toolkit' ),
				'service_date'       => __( 'Service Date', 'pp-toolkit' ),
				'description'       => __( 'Service Description', 'pp-toolkit' ),
				'additional_hours'       => __( 'Additional Hours', 'pp-toolkit' ),
			);

			return $columns;
		}

		protected function get_filename(){

			$filename = "Service Hours Export - " . date( 'm-d-Y' );

			if( ($this->args['export_type'] == 'dashboard') && ($this->args['dashboard_id'] != 'all') ){
				$filename = "Service Hours for ". get_the_title( $this->args['dashboard_id'] ) ." - " . date( 'm-d-Y' );
			}

			return $filename;
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

			$data = $this->get_data();

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

		protected function get_data(){
			switch ($this->args['export_type']) {
				case 'dashboard':
					$data = $this->get_dashboard_service_hours();
					break;
				
				default:
					$data = $this->get_chapter_service_hours();
					break;
			}

			return apply_filters( 'pp_export_service_hours_data', $data, $this->args );
		}

		/**
		 * Get dashboard service hours
		 * @return [type] [description]
		 */
		protected function get_dashboard_service_hours() {
			global $wpdb;

			$data = array();

			$dashboard_id = $this->args['dashboard_id'];
			$sql = "SELECT hr.id service_id, ch.id chapter_id, ch.dashboard_id, ch.name chapter_name, hr.first_name, hr.last_name, hr.description, hr.service_hours, hr.service_date, hr.parent 
					FROM {$wpdb->prefix}chapter_service_hours hr
					INNER JOIN {$wpdb->prefix}chapters ch ON hr.chapter_id = ch.id
					WHERE ch.dashboard_id = '{$dashboard_id}'
					ORDER BY parent ASC";

			$results = $wpdb->get_results($sql);

			if(!empty($results)):
			foreach ($results as $chapter_data) {

				if(!empty($chapter_data->parent)){
					if(!isset($data[$chapter_data->parent]['additional_hours'])){
						$data[$chapter_data->parent]['additional_hours'] = '';
					}

					$data[$chapter_data->parent]['additional_hours'] .= (!empty($chapter_data->service_date)) ? '['.$chapter_data->service_date.'] ' : ''; // date
					$data[$chapter_data->parent]['additional_hours'] .= stripslashes($chapter_data->description) . " (".$chapter_data->service_hours.") \n";
				} else {
					$data[$chapter_data->service_id] = array(
						'first_name'       => $chapter_data->first_name,
						'last_name'       => $chapter_data->last_name,
						'chapter_name'       => $chapter_data->chapter_name,
						'service_hours'       => $chapter_data->service_hours,
						'service_date'       => (!empty($chapter_data->service_date)) ? $chapter_data->service_date : '-',
						'description'       => stripslashes($chapter_data->description),
						'additional_hours'       => '',
					);
				}
			}
			endif;

			// echo "<pre>";
			// print_r($data);
			// echo "</pre>";
			// exit();

			return $data;
		}

		/**
		 * @return [type] [description]
		 */
		protected function get_chapter_service_hours() {

			$data = array(
				array(
					'chapter_id' => 'dasdasd'
				),
				array(
					'chapter_id' => 'sss'
				),

			);

			return $data;
		}
	}

endif; // End class_exists check
