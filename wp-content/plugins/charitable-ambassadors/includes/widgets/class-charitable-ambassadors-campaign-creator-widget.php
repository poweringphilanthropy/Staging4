<?php
/**
 * The class responsible for setting up the campaign creator widget.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Campaign_Creator_Widget
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Charitable_Ambassadors_Campaign_Creator' ) ) : 

/**
 * A widget to display the campaign creator's details.
 * 
 * @see     WP_Widget
 * @since   1.0.0
 */
class Charitable_Ambassadors_Campaign_Creator_Widget extends WP_Widget {

    /**
     * Define basic widget parameters.
     *
     * @since   1.0.0
     */
    public function __construct() {
        parent::__construct(
            'campaign_creator_widget', // Base ID
            __( 'Campaign Creator', 'charitable-ambassadors'), // Name
            array( 'description' => __( 'Display the campaign creator\'s details.', 'charitable-ambassadors' ) ) // Args
        );
    }

    /**
     * Display widget. Uses the widget-campaign-creator template.
     *
     * @param   array   $args
     * @param   array   $instance
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function widget( $args, $instance ) {
        /* We have to have a campaign id */
        if ( !isset( $instance['campaign_id'] ) || $instance['campaign_id'] == '' ) {
            return;
        }

        charitable_ambassadors_template( 'widget-campaign-creator.php', array_merge( $args, $instance ) );
    }

    /**
     * Set up widget form.
     *
     * @param   array   $instance
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function form( $instance ) {
        $defaults = array( 
            'title' => '', 
            'campaign_id' => '' 
        );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $campaigns = Charitable_Campaigns::query( array( 'posts_per_page' => -1 ) );    
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'charitable-ambassadors') ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('campaign_id'); ?>"><?php _e('Campaign:', 'charitable-ambassadors') ?>
                <select name="<?php echo $this->get_field_name('campaign_id') ?>">
                    <option value="current"><?php _e( 'Campaign currently viewed', 'charitable-ambassadors' ) ?></option>
                    <optgroup label="<?php _e( 'Specific campaigns', 'charitable-ambassadors' ) ?>">
                        <?php foreach ( $campaigns->posts as $campaign ) : ?>
                            <option value="<?php echo $campaign->ID ?>" <?php selected( $campaign->ID, $instance['campaign_id'] ) ?>>
                                <?php echo $campaign->post_title ?>
                            </option>
                        <?php endforeach ?>
                    </optgroup>
                </select>
            </label>
        </p>

        <?php
    }

    /**
     * Save updated widget settings. 
     *
     * @param   array   $new_instance
     * @param   array   $old_instance
     * @return  array
     * @access  public
     * @since   1.0.0
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['campaign_id'] = $new_instance['campaign_id'];
        return $instance;
    }
}

endif;