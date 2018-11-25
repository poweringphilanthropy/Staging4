<?php
/**
 * PP_Metaboxes Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Metaboxes
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Metaboxes class.
 */
class PP_Metaboxes {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Metaboxes();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        // add_action( 'init', array($this, 'create_metaboxes'), 11 );

        add_filter( 'charitable_campaign_meta_boxes', array($this, 'add_metaboxes'), 10, 1 );
        add_filter( 'charitable_campaign_meta_keys', array( $this, 'register_additional_metakeys' ) );
        add_filter( 'charitable_admin_view_path', array( $this, 'admin_view_path' ), 10, 3 );

        add_action( 'charitable_campaign_save', array($this, 'custom_save_metaboxes'), 10, 1 );
        // add_action( 'charitable_campaign_save', array($this, 'save_downloads_benefactor'), 100, 1 );

        // add_action('add_meta_boxes', array($this, 'add_metabox_chapters'), 10);
        // add_action('save_post', array($this, 'save_metabox_chapters'));
    }

    public function add_metabox_chapters(){
        add_meta_box( 'chapters', 'Chapters', array($this, 'metabox_chapters_callback'), 'dashboard', 'normal', 'default');
    }

    public function metabox_chapters_callback(){
        global $post;

        wp_nonce_field( 'chapters_meta_box_nonce', 'chapters_meta_box_nonce' );
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function( $ ){
            $( '#add-row' ).on('click', function() {
                var row = $( '.empty-row.screen-reader-text' ).clone(true);
                row.removeClass( 'empty-row screen-reader-text' );
                row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
                return false;
            });
        
            $( '.remove-row' ).on('click', function() {
                $(this).parents('tr').remove();
                return false;
            });
        });
        </script>
      
        <table id="repeatable-fieldset-one" width="100%">
        <thead>
            <tr>
                <th width="">Chapter Name</th>
                <th width="70px;"></th>
            </tr>
        </thead>
        <tbody>
        <?php

        $chapters = pp_get_dashboard_chapters($post->ID);
        
        if ( $chapters ) :
        
        foreach ( $chapters as $id => $chapter ) {
        ?>
        <tr>
            <td>
                <input type="hidden" name="chapter_id[]" value="<?php echo $id; ?>">
                <input type="text" class="widefat" name="chapter_name[]" value="<?php echo esc_attr( $chapter ); ?>" />
            </td>
            <td><a class="button remove-row" href="#">Remove</a></td>
        </tr>
        <?php
        }
        else :
        // show a blank one
        ?>
        <tr>
            <td>
                <input type="hidden" name="chapter_id[]" value="">
                <input type="text" class="widefat" name="chapter_name[]" />
            </td>
            <td><a class="button remove-row" href="#">Remove</a></td>
        </tr>
        <?php endif; ?>
        
        <!-- empty hidden one for jQuery -->
        <tr class="empty-row screen-reader-text">
            <td>
                <input type="hidden" name="chapter_id[]" value="">
                <input type="text" class="widefat" name="chapter_name[]" />
            </td>
            <td><a class="button remove-row" href="#">Remove</a></td>
        </tr>
        </tbody>
        </table>
        
        <div class="actions">
            <a id="add-row" class="button" href="#">Add another</a>
            <?php  
            $url = add_query_arg( array(
                'pp_export_service_hours' => 'dashboard',
                'dashboard_id' => $post->ID
            ), get_edit_post_link($post->ID) );
            ?>
            <a class="button button-primary" href="<?php echo wp_nonce_url( $url, 'export-service-hours', 'pp_export_nonce'); ?>" style="float:right;">Export Service Hours</a>
        </div>
        <?php
    }

    public function save_metabox_chapters($post_id){
        if ( ! isset( $_POST['chapters_meta_box_nonce'] ) ||
        ! wp_verify_nonce( $_POST['chapters_meta_box_nonce'], 'chapters_meta_box_nonce' ) )
            return;
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        
        if (!current_user_can('edit_post', $post_id))
            return;
        
        $chapter_ids = $_POST['chapter_id'];
        $chapter_names = $_POST['chapter_name'];

        $old_data = pp_get_dashboard_chapters($post_id);
        $chapters = array();
        
        if(!empty($chapter_names)){
            foreach ($chapter_names as $key => $name) {
                if(empty($name))
                    continue;

                if(empty($chapter_ids[$key])){
                    $id = pp_dashboard_insert_chapter($post_id, $name);
                } else {
                    $id = $chapter_ids[$key];
                }

                $chapters[$id] = $name;
            }

        }

        // may need to remove
        $to_remove = array_diff($old_data, $chapters);
        if(!empty($to_remove)){
            foreach ($to_remove as $id => $name) {
                pp_remove_chapter($id);
            }
        }

        // echo "<pre>";
        // print_r($to_remove);
        // echo "</pre>";

        // echo "<pre>";
        // print_r($chapters);
        // echo "</pre>";
        // echo "<pre>";
        // print_r($old_data);
        // echo "</pre>";
        // exit();
        // if ( !empty( $new ) && $new != $old )
        //     update_post_meta( $post_id, 'repeatable_fields', $new );
        // elseif ( empty($new) && $old )
        //     delete_post_meta( $post_id, 'repeatable_fields', $old );
        // exit();
    }

    public function create_metaboxes(){

        /**
         * Seems not used for now
         */
        // register_cuztom_meta_box(
        //     'campaign',
        //     'campaign',
        //     array(
        //         'title'  => __('Campaign Info', 'pp-toolkit'),  
        //         'fields' => array(
        //             array(
        //                 'id'    => '_campaign_philanthropy_challenge',
        //                 'type'  => 'checkbox',
        //                 'label' => __('30 day Philanthropy Challenge', 'pp-toolkit'),
        //                 'html_attributes' => array('value' => '1', 'disabled' => 'disabled'),
        //             ),
        //             array(
        //                 'id'    => '_campaign_group_campaign',
        //                 'type'  => 'checkbox',
        //                 'label' => __('Group campaign?', 'pp-toolkit'),
        //                 'html_attributes' => array('disabled' => 'disabled')
        //             ),
        //             array(
        //                 'id'            => '_campaign_impact_goal',
        //                 'type'          => 'wysiwyg',
        //                 'label'         => __('Impact Target', 'pp-toolkit'),
        //             ),
        //         )
        //     ),
        //     'normal'
        // );
    }

    public function add_metaboxes($meta_boxes){

        $meta_boxes[] = array(
            'id'                => 'campaign-merchandises',
            'title'             => __( 'Merchandises', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-merchandises',
            'view_source'       => 'pp-toolkit',
        );

        $meta_boxes[] = array(
            'id'                => 'campaign-events',
            'title'             => __( 'Events', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-events',
            'view_source'       => 'pp-toolkit',
        );

        $meta_boxes[] = array(
            'id'                => 'campaign-tickets',
            'title'             => __( 'Tickets', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-tickets',
            'view_source'       => 'pp-toolkit',
        );

        $meta_boxes[] = array(
            'id'                => 'campaign-volunteers',
            'title'             => __( 'Volunteers', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-volunteers',
            'view_source'       => 'pp-toolkit',
        );

        $meta_boxes[] = array(
            'id'                => 'campaign-payout',
            'title'             => __( 'Payout Info', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-payout',
            'view_source'       => 'pp-toolkit',
        ); 
		
		$meta_boxes[] = array(
            'id'                => 'campaign-crm-id',
            'title'             => __( 'Crm Id', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-crm',
            'view_source'       => 'pp-toolkit',
        );

        return $meta_boxes;
    }

    /**
     * Set the admin view path to our views folder for any of our views.
     *
     * @param   string $path
     * @param   string $view
     * @param   array  $view_args
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function admin_view_path( $path, $view, $view_args ) {
        if ( ! isset( $view_args['view_source'] ) ) {
            return $path;
        }

        if ( 'pp-toolkit' == $view_args['view_source'] ) {
            $path = pp_toolkit()->directory_path . 'includes/admin/views/' . $view . '.php';
        }

        return $path;
    }

    public function custom_save_metaboxes($post){
        /**
         * Save volunteers
         */
        
        if(isset($_POST['_campaign_volunteers']) && is_array($_POST['_campaign_volunteers'])):
        $volunteers = array();
        foreach ($_POST['_campaign_volunteers'] as $key => $value) {
            if( $key === '{?}' )
                continue;

            if( empty($value['need']) )
                continue;

            $volunteers[] = array('need' => $value['need']);
        }

        update_post_meta( $post->ID, '_campaign_volunteers', $volunteers );
        endif;
    }

    /**
     * add additional metakeys
     */
    public function register_additional_metakeys( $meta_keys ) {
        $meta_keys[] = '_campaign_payout_options';
        $meta_keys[] = '_campaign_connected_stripe_id';
        $meta_keys[] = '_campaign_platform_fee';

        $meta_keys[] = '_campaign_payout_payable_name';
        $meta_keys[] = '_campaign_payout_email';
        $meta_keys[] = '_campaign_payout_first_name';
        $meta_keys[] = '_campaign_payout_last_name';
        $meta_keys[] = '_campaign_payout_address';
        $meta_keys[] = '_campaign_payout_address2';
        $meta_keys[] = '_campaign_payout_city';
        $meta_keys[] = '_campaign_payout_state';
        $meta_keys[] = '_campaign_payout_zipcode';
        $meta_keys[] = '_campaign_payout_country';
        
        return $meta_keys;
    }

    /**
     * Save downloads (Merchandise and Tickets)
     * hooked on charitable_campaign_save but very late, 
     * so we can get data after all saved to benefactor db
     * @param  [type] $post [description]
     * @return [type]       [description]
     */
    public function save_downloads_benefactor($post){

        /**
         * TODO | Get downloads from benefactor table and save to meta
         */
        $benefactors = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension( $post->ID, 'charitable-edd' );
        // or 
        $ret = charitable_get_table( 'edd_benefactors' )->get_campaign_benefactors( $campaign_id, false );
    }

    public function includes(){

    }

}

PP_Metaboxes::init();