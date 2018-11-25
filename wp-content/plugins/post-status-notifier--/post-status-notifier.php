<?php
/*
Plugin Name: Post Status Notifier
Plugin URI: http://www.ifeelweb.de/wp-plugins/post-status-notifier/
Description: Lets you create individual notification rules to be informed about all post status transitions of your blog. Features custom email texts with many placeholders and custom post types.
Author: ifeelweb.de
Version: 1.8.8
Author URI: http://www.ifeelweb.de
Text Domain: psn
*/

include_once dirname(__FILE__) . '/custom.php';

if (!class_exists('IfwPsn_Wp_Plugin_Loader')) {
    require_once IFW_PSN_LIB_ROOT . '/IfwPsn/Wp/Plugin/Loader.php';
}

IfwPsn_Wp_Plugin_Loader::load(__FILE__);