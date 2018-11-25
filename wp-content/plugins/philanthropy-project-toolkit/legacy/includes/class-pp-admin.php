<?php
/**
 * PP_Admin Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Admin
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Admin class.
 */
class PP_Admin {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Admin();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );

        add_filter( 'parent_file', array($this, 'change_menu'), 10, 1 );
        
    }

    public function enqueue_scripts($hook){
        global $post;

        $need_script = false;
        if ( $hook == 'post.php' && ('campaign' === $post->post_type) ) {
            $need_script = true;
        }
        
        if ( $hook == 'post.php' && ('dashboard' === $post->post_type) ) {
            $need_script = true;
        }

        if(!$need_script)
            return;

        wp_enqueue_style( 'selectize' );
        wp_enqueue_script( 'selectize' );

        wp_enqueue_style( 'pp-toolkit-admin' );
        wp_enqueue_script( 'pp-toolkit-admin' );
    }

    public function change_menu($parent_file){
        global $menu, $submenu;

        $charitable = array_search('charitable', array_column($menu, 2));
        $events = array_search('edit.php?post_type=tribe_events', array_column($menu, 2));

        move_menu($charitable, 7);
        move_menu($events, 8);

        // echo "<pre>";
        // print_r($menu);
        // echo "</pre>";

        return $parent_file;
    }

    public function includes(){
        include_once( 'admin/class-pp-metaboxes.php');
        include_once( 'admin/class-pp-settings.php');
    }

}

PP_Admin::init();