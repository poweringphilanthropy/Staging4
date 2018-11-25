<?php
// Creating the widget 
class wpb_widget extends WP_Widget {

    function __construct() {

        parent::__construct(
            // Base ID of your widget
            'wpb_widget', 

            // Widget name will appear in UI
            __('Volunteer\'s Need', 'wpb_widget_domain'), 

            // Widget description
            array( 'description' => __( 'to view Volunteer\'s Needs', 'wpb_widget_domain' ), ) 
        );
    }

    public function widget($args, $instance) {

        $campaign = charitable_get_current_campaign();
        $needs = $campaign->volunteers;

        if ($needs == null)
            return false;


        $user_id = $campaign->get_campaign_creator();
        $user_email = get_userdata($user_id)->data->user_email;
        $user_email_nonce = wp_create_nonce($user_email.$user_id);


        echo $args['before_widget'];

        $title = apply_filters('widget_title', $instance['title']);

        ?>

        <section class="widget-volunteer">
            <?php if (!empty($title)) echo $args['before_title'].$title.$args['after_title']; ?>
            <p>Please consider signing up to help with any of the following projects:</p>

            <ul class="widget-volunteer__list">
                <?php foreach ($needs as $i => $need): ?>
                    <li><?= $need['need']; ?></li>
                <?php endforeach; ?>
            </ul>

            <a class="button button-alt accent" id="volunteerModalOpen" href="#volunteerModal">Volunteer</a>
        </section>


        <div id="volunteerModal" class="widget-volunteer__modal">
            <div class="widget-volunteer__button close-volunteerModal"><i class="fa fa-times" aria-hidden="true"></i>
            </div>

            <div class="modal-content">
                <h2>Volunteer Information</h2>

                <?php if (function_exists('ninja_forms_display_form')) {
                    ninja_forms_display_form(1);
                } ?>
            </div>
        </div>

        <script>
            jQuery('#volunteerModalOpen').animatedModal({
                modalTarget: 'volunteerModal',
                animatedIn:'fadeInDown',
                animatedOut:'flipOutY',
                color: '#fff'
            });

            jQuery('.cc_id').val('<?= $user_id ?>');
            jQuery('.cc_nonce').val('<?= $user_email_nonce ?>');

            var volunteerNeeds = {
            <?php foreach ( $needs as $i => $need ): ?>
            <?= $i ?>: '<?= $need['need']; ?>',
            <?php endforeach; ?>
            }
            ;

            var volunteerSelect = jQuery('#ninja_forms_field_14');
            jQuery.each(volunteerNeeds, function (val, text) {
                volunteerSelect.append(
                    jQuery('<option></option>').html(text)
                );
            });
        </script>


        <?php


        echo $args['after_widget'];

    }

    
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }

    public function form( $instance ) {
        //Set up some default widget settings.
        $defaults = array( 'title' => __('VOLUNTEERS & COLLABORATORS', 'example') );
        $instance = wp_parse_args( (array) $instance, $defaults );
        ?> 
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'example'); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
        </p>    
        <?php
    } 

} // Class wpb_widget ends here