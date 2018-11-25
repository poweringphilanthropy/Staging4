<?php
/**
 * Class responsible for defining the Campaign Events widget.
 *
 * @package     Philanthropy Project/Classes/PP_Widget_Campaign_Events
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Widget_Campaign_Events' ) ) : 

/**
 * A widget to display the downloads that are connected to the campaign. 
 * 
 * @see     WP_Widget
 * @since   1.0.0
 */
class PP_Widget_Campaign_Events extends WP_Widget {

    /**
     * Register the widget. 
     *
     * @access  public
     * @since   1.0.0
     */
    public function __construct() {
        parent::__construct(
            'pp_campaign_events', // Base ID
            __( 'Campaign Events', 'pp-toolkit'), // Name
            array( 
                'description' => __( 'Display the events associated with an event.', 'pp-toolkit' 
            )
        ) );
    }

    /**
     * Displays the widget on the frontend. 
     *
     * @param   array       $args
     * @param   array       $instance
     * @return  void
     * @access  public
     * @since   1.0.0    
     */
    public function widget( $args, $instance ) {        

        /* We have to have a campaign id */
        if ( !isset( $instance['campaign_id'] ) || $instance['campaign_id'] == '' ) {
            return;
        }

        $template = new PP_Toolkit_Template( 'widgets/widget-campaign-events.php', false );
        $template->set_view_args( array_merge( $args, $instance ) );
        $template->render();
    }

    /**
     * Displays the widget form. 
     *
     * @param   array       $instance
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function form( $instance ) {
        $defaults = array( 
            'title'                 => '', 
            'campaign_id'           => '', 
            'show_featured_image'   => 1, 
            'show_title'            => 1
        );
        
        $instance       = wp_parse_args( (array) $instance, $defaults );        
        $title          = $instance['title'];
        $campaign_id    = $instance['campaign_id'];
        $show_title     = $instance['show_title']; 
        $show_featured_image = $instance['show_featured_image'];
        $campaigns      = Charitable_Campaigns::query( array( 'posts_per_page' => -1 ) );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'pp-toolkit') ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p> 
        <p>
            <label for="<?php echo $this->get_field_id('campaign_id'); ?>"><?php _e('Campaign:', 'pp-toolkit') ?>        
                <select name="<?php echo $this->get_field_name('campaign_id') ?>">
                    <option value="current"><?php _e( 'Campaign currently viewed', 'pp-toolkit' ) ?></option>
                    <optgroup label="<?php _e( 'Specific campaigns', 'pp-toolkit' ) ?>">
                        <?php foreach ( $campaigns->posts as $campaign ) : ?>
                            <option value="<?php echo $campaign->ID ?>" <?php selected( $campaign->ID, $campaign_id ) ?>><?php echo $campaign->post_title ?></option>
                        <?php endforeach ?>
                    </optgroup>
                </select>    
            </label>      
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_featured_image'); ?>">
                <input id="<?php echo $this->get_field_id('show_featured_image'); ?>" name="<?php echo $this->get_field_name('show_featured_image'); ?>" type="checkbox" <?php checked( $show_featured_image ) ?> />
                <?php _e('Show Event Featured Image', 'pp-toolkit') ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_title'); ?>">
                <input id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" <?php checked( $show_title ) ?> />
                <?php _e('Show Event Title', 'pp-toolkit') ?>
            </label>
        </p> 
        <?php
    }

    /**
     * Update the widget settings. 
     *
     * @param   array       $new_instance
     * @param   array       $old_instance
     * @return  array
     * @access  public
     * @since   1.0.0
     */
    public function update( $new_instance, $old_instance ) {
        $instance                   = $old_instance;
        $instance['title']          = $new_instance['title'];
        $instance['campaign_id']    = $new_instance['campaign_id'];
        $instance['show_title']     = $new_instance['show_title'] == 'on';
        $instance['show_featured_image'] = $new_instance['show_featured_image'] == 'on';
        return $instance;
    }
}

endif;