<?php
/**
 * Class responsible for defining the Sponsors widget.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Widget_Campaign_Sponsors' ) ) : 

/**
 * A widget to display the downloads that are connected to the campaign. 
 * 
 * @see     WP_Widget
 * @since   1.0.0
 */
class PP_Widget_Campaign_Sponsors extends WP_Widget {

    /**
     * Register the widget. 
     *
     * @access  public
     * @since   1.0.0
     */
    public function __construct() {
        parent::__construct(
            'pp_campaign_sponsors', // Base ID
            __( 'Campaign Sponsors', 'pp-toolkit'), // Name
            array( 
                'description' => __( 'Display the sponsors associated with an campaign.', 'pp-toolkit' 
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

        $campaign = charitable_get_current_campaign();
        $sponsors = (!empty($campaign->get('sponsors'))) ? $campaign->get('sponsors') : array();
        $sponsors = array_filter( array_map('array_filter', $sponsors) );
        if(!$campaign || empty( $sponsors ) )
            return;

        $instance['campaign'] = $campaign;
        $instance['sponsors'] = $sponsors;

        $template = new PP_Toolkit_Template( 'widgets/campaign-sponsors.php', false );
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
            'title'                 => __('Our Sponsors', 'pp-toolkit')
        );
        
        $instance       = wp_parse_args( (array) $instance, $defaults );        
        $title          = $instance['title'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'pp-toolkit') ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
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

        return $instance;
    }
}

endif;