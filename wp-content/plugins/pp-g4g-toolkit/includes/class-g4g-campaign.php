<?php
/**
 * G4G_Campaign Class.
 *
 * @class       G4G_Campaign
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_Campaign class.
 */
class G4G_Campaign {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new G4G_Campaign();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'charitable_campaign_content_loop_after', array($this, 'add_university_information'), 5, 1 );

    }

    public function add_university_information($campaign){
        g4g_template('campaign-loop/campaign-taxonomy.php', array(
            'campaign' => $campaign,
        ) );
    }


    public function includes(){

    }

}

G4G_Campaign::init();