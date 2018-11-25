<?php

/**
 * Charitable Videos Core Functions.
 *
 * General core functions.
 *
 * @author      Studio164a
 * @category    Core
 * @package     Charitable Videos
 * @subpackage  Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * This returns the original Charitable_Videos object.
 *
 * Use this whenever you want to get an instance of the class. There is no
 * reason to instantiate a new object, though you can do so if you're stubborn :)
 *
 * @return  Charitable_Videos
 * @since   1.0.0
 */
function charitable_videos() {
	return Charitable_Videos::get_instance();
}
