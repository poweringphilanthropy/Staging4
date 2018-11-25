<?php
if (!defined('IFW_PSN_LIB_ROOT')) {
    define('IFW_PSN_LIB_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
}



// Check for simultaneous use of Premium and Lite version

if (is_admin()) {

    if (!function_exists('ifw_is_plugin_active')) {
        function ifw_is_plugin_active( $plugin ) {
            return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || ifw_is_plugin_active_for_network( $plugin );
        }
    }

    if (!function_exists('ifw_is_plugin_active_for_network')) {
        function ifw_is_plugin_active_for_network( $plugin ) {
            if ( !is_multisite() )
                return false;

            $plugins = get_site_option( 'active_sitewide_plugins');
            if ( isset($plugins[$plugin]) )
                return true;

            return false;
        }
    }

    if (!function_exists('ifw_psn_check_simultaneous_use')) {
        function ifw_psn_check_simultaneous_use () {

            if (basename(dirname(__FILE__)) == 'post-status-notifier' && ifw_is_plugin_active('post-status-notifier-lite/post-status-notifier-lite.php')) {
                // this is the Premium version and the Lite version is still activated
                $msg = sprintf('Please deactivate <b>Post Status Notifier Lite</b> before you activate the Premium version. Refer to the <a href="%s" target="_blank">Upgrade HowTo</a>.', 'http://docs.ifeelweb.de/post-status-notifier/upgrade_howto.html');
                die('<p style="font-family: \'Open Sans\', sans-serif;">' . $msg . '</p>');

            } elseif (basename(dirname(__FILE__)) == 'post-status-notifier-lite' && ifw_is_plugin_active('post-status-notifier/post-status-notifier.php')) {
                // this is the Lite version and the Premium version is still activated
                $msg = sprintf('You can not use both Post Status Notifier <b>Lite</b> and <b>Premium</b> versions simultaneously. Refer to the <a href="%s" target="_blank">Upgrade HowTo</a>.', 'http://docs.ifeelweb.de/post-status-notifier/upgrade_howto.html');
                die('<p style="font-family: \'Open Sans\', sans-serif;">' . $msg . '</p>');
            }
        }
    }

    register_activation_hook(basename(dirname(__FILE__)) . '/' . basename(dirname(__FILE__)) . '.php', 'ifw_psn_check_simultaneous_use');
}