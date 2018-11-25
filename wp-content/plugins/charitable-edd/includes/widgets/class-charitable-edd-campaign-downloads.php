<?php
/**
 * Class responsible for augmenting / decorating the core Charitable_Benefactor class. 
 *
 * @package		Charitable EDD/Classes/Charitable_EDD_Campaign_Dwonloads
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Campaign_Downloads' ) ) : 

	/**
	 * A widget to display the downloads that are connected to the campaign. 
	 * 
	 * @see 	WP_Widget
	 * @since 	1.0.0
	 */
	class Charitable_EDD_Campaign_Downloads extends WP_Widget {

		/**
		 * Register the widget. 
		 *
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function __construct() {
			parent::__construct(
				'charitable_edd_campaign_downloads', // Base ID
				__( 'Campaign EDD Downloads', 'charitable-edd'), // Name
				array( 
					'description' => __( 'Display the Easy Digital Downloads downloads that can be purchased to contribute to a campaign\'s goal.', 'charitable-edd' 
				)
			) );
		}

		/**
		 * Displays the widget on the frontend. 
		 *
		 * @param 	array 		$args
		 * @param 	array 		$instance
		 * @return 	void
		 * @access 	public
		 * @since 	1.0.0	 
		 */
		public function widget( $args, $instance ) {
			
			// We have to have a campaign id
			if ( !isset( $instance['campaign_id'] ) || $instance['campaign_id'] == '' ) {
				return;
			}

			charitable_edd_template( 'widget-campaign-downloads.php', array_merge( $args, $instance ) );
		}

		/**
		 * Displays the widget form. 
		 *
		 * @param 	array 		$instance
		 * @return 	void
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function form( $instance ) {
			$defaults = array( 
				'title' 				=> '', 
				'campaign_id' 			=> '', 
				'download_category'		=> '',
				'show_featured_image'	=> 1, 
				'show_title'			=> 1
			);
			
			$instance 		= wp_parse_args( (array) $instance, $defaults );		
			$title 			= $instance['title'];
			$campaign_id 	= $instance['campaign_id'];
			$download_category_id = $instance['download_category'];
			$show_title		= $instance['show_title']; 
			$show_featured_image = $instance['show_featured_image'];
			$campaigns 		= Charitable_Campaigns::query( array( 'posts_per_page' => -1 ) );
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'charitable-edd') ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</label>
			</p> 
			<p>
				<label for="<?php echo $this->get_field_id('campaign_id'); ?>"><?php _e('Campaign:', 'charitable-edd') ?>        
					<select name="<?php echo $this->get_field_name('campaign_id') ?>">
						<option value="current"><?php _e( 'Campaign currently viewed', 'charitable-edd' ) ?></option>
						<optgroup label="<?php _e( 'Specific campaigns', 'charitable-edd' ) ?>">
							<?php foreach ( $campaigns->posts as $campaign ) : ?>
								<option value="<?php echo $campaign->ID ?>" <?php selected( $campaign->ID, $campaign_id ) ?>><?php echo $campaign->post_title ?></option>
							<?php endforeach ?>
						</optgroup>
					</select>    
				</label>      
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('download_category') ?>"><?php _e( 'Category:', 'charitable-edd' ) ?>
					<select name="<?php echo $this->get_field_name('download_category') ?>">
						<?php foreach ( get_terms( 'download_category', array( 'hide_empty' => false, 'fields' => 'id=>name' ) ) as $term_id => $name ) : ?>
							<option value="<?php echo $term_id ?>" <?php selected( $term_id, $download_category_id ) ?>><?php echo $name ?></option>
						<?php endforeach ?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('show_featured_image'); ?>">
					<input id="<?php echo $this->get_field_id('show_featured_image'); ?>" name="<?php echo $this->get_field_name('show_featured_image'); ?>" type="checkbox" <?php checked( $show_featured_image ) ?> />
					<?php _e('Show Download Featured Image', 'charitable-edd') ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('show_title'); ?>">
					<input id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" <?php checked( $show_title ) ?> />
					<?php _e('Show Download Title', 'charitable-edd') ?>
				</label>
			</p> 
			<?php
		}

		/**
		 * Update the widget settings. 
		 *
		 * @param 	array 		$new_instance
		 * @param 	array 		$old_instance
		 * @return 	array
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function update( $new_instance, $old_instance ) {
			$instance 					     = $old_instance;
			$instance['title'] 			     = $new_instance['title'];
			$instance['campaign_id'] 	     = $new_instance['campaign_id'];    
			$instance['download_category']   = $new_instance['download_category'];
			$instance['show_title']		     = $new_instance['show_title'] == 'on';
			$instance['show_featured_image'] = $new_instance['show_featured_image'] == 'on';
			return $instance;
		}
	}

endif;
