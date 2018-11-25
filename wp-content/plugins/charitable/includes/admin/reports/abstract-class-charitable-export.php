<?php 
/**
 * In Charitable 1.5, we moved the location of the base Charitable_Export class.
 *
 * This file is kept purely for other extensions that load this file, expecting it to exist.
 *
 * @deprecated
 */
require_once( charitable()->get_path( 'includes' ) . 'abstracts/abstract-class-charitable-export.php' );
