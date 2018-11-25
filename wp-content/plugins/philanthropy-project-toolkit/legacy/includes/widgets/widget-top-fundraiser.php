<?php
/**
 * Campaign top fundraiser widget class.
 *
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'PP_Widget_Top_Fundraiser' ) ) :

	/**
	 * PP_Widget_Top_Fundraiser class.
	 *
	 * @since       1.0.0
	 */
	class PP_Widget_Top_Fundraiser extends WP_Widget {

		/**
		 * Instantiate the widget and set up basic configuration.
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct() {
			parent::__construct(
				'pp_top_fundraiser_widget',
				__( 'Top Fundraiser', 'pp-toolkit' ),
				array(
					'description' => __( 'Display a list of top fundraiser.', 'pp-toolkit' ),
					'customize_selective_refresh' => true,
				)
			);
		}

		/**
		 * Display the widget contents on the front-end.
		 *
		 * @param   array $args
		 * @param   array $instance
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function widget( $args, $instance ) {
			$instance 			 = $this->get_parsed_args( $instance );
			$view_args 			 = array_merge( $args, $instance );
			$view_args['fundraisers'] = $this->get_widget_fundraisers( $instance );

			pp_toolkit_template( 'widgets/top-fundraiser.php', $view_args );
		}

		/**
		 * Display the widget form in the admin.
		 *
		 * @param   array $instance The current settings for the widget options.
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function form( $instance ) {
			$args = $this->get_parsed_args( $instance );
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ) ?>"><?php _e( 'Title', 'pp-toolkit' ) ?>:</label>
				<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ) ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ) ?>" value="<?php echo esc_attr( $args['title'] ) ?>" class="widefat" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ) ?>"><?php _e( 'Number of fundraisers to display', 'pp-toolkit' ) ?>:</label>
				<input type="number" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ) ?>" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ) ?>" value="<?php echo intval( $args['number'] ) ?>" min="1" size="3" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'campaign_id' ) ) ?>"><?php _e( 'Show fundraisers by campaign', 'charitable' ) ?>:</label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'campaign_id' ) ) ?>">
					<option value="all" <?php selected( 'all', $args['campaign_id'] ) ?>><?php _e( 'Include all campaigns' ) ?></option>
					<option value="current" <?php selected( 'current', $args['campaign_id'] ) ?>><?php _e( 'Campaign currently viewed', 'charitable' ) ?></option>
					<optgroup label="<?php _e( 'Specific campaign', 'charitable' ) ?>">
						<?php foreach ( Charitable_Campaigns::query()->posts as $campaign ) : ?>
							<option value="<?php echo intval( $campaign->ID ) ?>" <?php selected( $campaign->ID, $args['campaign_id'] ) ?>><?php echo $campaign->post_title ?></option>
						<?php endforeach ?>
					</optgroup>
				</select>                
			</p>
			<p>            
				<input id="<?php echo esc_attr( $this->get_field_id( 'hide_if_no_fundraisers' ) ) ?>" type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'hide_if_no_fundraisers' ) ); ?>" <?php checked( $args['hide_if_no_fundraisers'] ) ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( 'hide_if_no_fundraisers' ) ) ?>"><?php _e( 'Hide if there are no fundraisers', 'pp-toolkit' ) ?></label>
			</p>
			<?php

			do_action( 'pp_top_fundraiser_widget_settings_bottom', $args, $this );
		}

		/**
		 * Update the widget settings in the admin.
		 *
		 * @param   array $new_instance         The updated settings.
		 * @param   array $new_instance         The old settings.
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title']             = isset( $new_instance['title'] ) ? $new_instance['title'] : $old_instance['title'];
			$instance['number']            = isset( $new_instance['number'] ) ? intval( $new_instance['number'] ) : $old_instance['number'];
			$instance['campaign_id']       = isset( $new_instance['campaign_id'] ) ? $new_instance['campaign_id'] : $old_instance['campaign_id'];
			$instance['hide_if_no_fundraisers'] = isset( $new_instance['hide_if_no_fundraisers'] ) && 'on' == $new_instance['hide_if_no_fundraisers'];
			
			return apply_filters( 'pp_top_fundraiser_widget_update_instance', $instance, $new_instance, $old_instance );
		}

		/**
		 * Return parsed array of arguments.
		 *
		 * @param   mixed[] $instance
		 * @return  mixed[]
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_parsed_args( $instance ) {
			$defaults = apply_filters( 'pp_top_fundraiser_widget_default_args', array(
				'title'         	=> __( 'Top Fundraiser', 'pp-toolkit' ),
				'number'        	=> 10,
				'campaign_id'   	=> 'current',
				'hide_if_no_fundraisers' => true,
			), $instance );

			return wp_parse_args( $instance, $defaults );
		}

		/**
		 * Return the donors to display in the widget.
		 *
		 * @param   mixed[] $instance
		 * @return  array
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_widget_fundraisers( $instance ) {
			$query_args = array(
				'number' => -1, // get all
				'output' => 'fundraisers',
				'orderby' => 'amount',
				'distinct_fundraisers' => true,
				'exclude_na' => true,
			);

			if ( 'current' == $instance['campaign_id'] ) {
				$query_args['campaign'] = charitable_get_current_campaign_id();
			} elseif ( 'all' != $instance['campaign_id'] ) {
				$query_args['campaign'] = intval( $instance['campaign_id'] );
			}

			// maybe need to merge with child campaign
			if(function_exists('pp_get_merged_team_campaign_ids')){
				$query_args['campaign'] = pp_get_merged_team_campaign_ids($query_args['campaign']);
			}

			$query_args = apply_filters( 'pp_top_fundraiser_widget_donor_query_args', $query_args, $instance );

			return new PP_Fundraisers_Query( $query_args );
		}
	}

endif; // End class_exists check
