<?php
/**
 * PP_Embed_Media Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Embed_Media
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Embed_Media class.
 */
class PP_Embed_Media {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Embed_Media();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'charitable_campaign_content_after', array($this, 'load_embed_media_template'), 5 );
        
    }

    public function load_embed_media_template($campaign){
        pp_toolkit_template( 'campaign/campaign-media.php', array( 'campaign' => $campaign ) );
    }

    public function includes(){

    }

}

PP_Embed_Media::init();