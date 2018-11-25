<?php
/**
 * PP_Importer Class.
 *
 * @class       PP_Importer
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Importer class.
 */
class PP_Importer {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Importer();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        add_action( 'admin_init', array($this, 'register_importer') );
    }

    public function register_importer(){
        register_importer('pp-importer', __('PP Importer', 'pp'), __('Import posts, categories, tags, custom fields from simple csv file.', 'pp'), array ($this, 'dispatch'));
    }

    public function dispatch(){
        echo '<div class="wrap">';
        echo '<h2>'.__('Import CSV', 'pp').'</h2>';

        if (empty ($_GET['step']))
            $step = 0;
        else
            $step = (int) $_GET['step'];

        switch ($step) {
            case 0 :
                $this->greet();
                break;
            case 1 :
                check_admin_referer('import-upload');
                set_time_limit(0);
                $result = $this->import();
                if ( is_wp_error( $result ) )
                    echo $result->get_error_message();
                break;
        }

        echo '</div>';
    }

    public function greet(){
        echo '<p>'.__( 'Choose a CSV (.csv) file to upload, then click Upload file and import.', 'pp' ).'</p>';
        wp_import_upload_form( add_query_arg('step', 1) );
    }

    public function import(){

        $file = wp_import_handle_upload();

        if ( isset( $file['error'] ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'really-simple-csv-importer' ) . '</strong><br />';
            echo esc_html( $file['error'] ) . '</p>';
            return false;
        } else if ( ! file_exists( $file['file'] ) ) {
            echo '<p><strong>' . __( 'Sorry, there has been an error.', 'really-simple-csv-importer' ) . '</strong><br />';
            printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'really-simple-csv-importer' ), esc_html( $file['file'] ) );
            echo '</p>';
            return false;
        }

        do_action( 'pp_import_before_import_file', $file );

        $this->id = (int) $file['id'];
        $this->file = get_attached_file($this->id);

        $basename = basename($file['file']);
        $name = substr($basename, 0, strpos($basename, '.'));
        $_names = explode('__', $name);

        $import_type = isset($_names[0]) ? $_names[0] : 'post';
        $type = isset($_names[1]) ? $_names[1] : 'post';

        // echo $type;

        // echo "<pre>";
        // print_r($_names);
        // echo "</pre>";

        // var_dump(taxonomy_exists( $type ));
        // exit();

        $result = $this->process($import_type, $type);
        
        if ( is_wp_error( $result ) )
            return $result;
    }

    private function process($import_type, $type){
        $data = $this->get_data();

        if(empty($data)){
            return new WP_Error('empty_date', __('Empty data.', 'pp') );
        }

        /**
         * Currently only support taxonomy
         */
        $taxonomy = $type;

        echo "<ul>";
        foreach ($data as $key => $data) {

            $term = current($data);

            if(empty($term))
                continue;

            $id = term_exists( $term, $taxonomy );
            if(!empty($id))
                continue;

            $insert = wp_insert_term( $term, $taxonomy );

            if(is_wp_error( $insert )){
                echo '<li>' . $insert->get_error_message() . '</li>';
            } else {
                echo '<li>' . sprintf(__('Success insert with id %d', 'pp'), $insert['term_id']) . '</li>';
            }
        }

        return true;

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit();
    }

    private function get_data() {
        if(!file_exists($this->file) || !is_readable($this->file))
            return FALSE;

        $delimiter = ',';
        
        $header = NULL;
        $data = array();
        if (($handle = fopen($this->file, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        wp_import_cleanup($this->id);

        return $data;
    }

    public function includes(){
        
    }

}

PP_Importer::init();