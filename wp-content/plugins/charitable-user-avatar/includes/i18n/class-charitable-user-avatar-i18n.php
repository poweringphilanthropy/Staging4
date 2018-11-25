<?php
/**
 * Sets up translations for Charitable User Avatar.
 *
 * @package     Charitable/Classes/Charitable_i18n
 * @version     1.0.2
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Charitable_User_Avatar_i18n' ) ) : 

/**
 * Charitable_User_Avatar_i18n
 *
 * @since       1.0.2
 */
class Charitable_User_Avatar_i18n extends Charitable_i18n {

    /**
     * The single instance of this class.  
     *
     * @var     Charitable_User_Avatar_i18n|null
     * @access  private
     * @static
     */
    private static $instance = null;    

    /**
     * @var     string
     */
    protected $textdomain = 'charitable-user-avatar';

    /**
     * Returns and/or create the single instance of this class.  
     *
     * @return  Charitable_User_Avatar_i18n
     * @access  public
     * @since   1.0.2
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new Charitable_User_Avatar_i18n();
        }

        return self::$instance;
    }

    /**
     * Set up the class. 
     *
     * @access  protected
     * @since   1.0.2
     */
    protected function __construct() {
        $this->languages_directory = apply_filters( 'charitable_user_avatar_languages_directory', 'charitable-user-avatar/languages' );
        $this->locale = apply_filters( 'plugin_locale', get_locale(), $this->textdomain );
        $this->mofile = sprintf( '%1$s-%2$s.mo', $this->textdomain, $this->locale );

        $this->load_textdomain();
    }
}

endif;