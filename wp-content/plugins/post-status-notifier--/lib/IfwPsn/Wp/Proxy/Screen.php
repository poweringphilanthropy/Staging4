<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Screen.php 206 2013-09-10 22:04:24Z timoreithde $
 */ 
class IfwPsn_Wp_Proxy_Screen
{
    /**
     * @param $label
     * @param $optionName
     * @param int $default
     */
    public static function addOptionPerPage($label, $optionName, $default = 10)
    {
        self::addOption('per_page', array(
            'label' => $label,
            'option' => $optionName,
            'default' => $default
        ));
    }

    public static function addOption($option, $args)
    {
        if (IfwPsn_Wp_Proxy_Blog::isMinimumVersion('3.1')) {
            add_screen_option($option, $args);
        }
    }

    /**
     * @param $option
     * @param bool $key
     * @return null
     */
    public static function getOption($option, $key = false)
    {
        if (IfwPsn_Wp_Proxy_Blog::isMinimumVersion('3.3')) {
            return self::getCurrent()->get_option($option, $key);
        }
        return null;
    }

    /**
     * Alias for get_current_screen()
     * @return WP_Screen
     */
    public static function getCurrent()
    {
        if (IfwPsn_Wp_Proxy_Blog::isMinimumVersion('3.1') && function_exists('get_current_screen')) {
            return get_current_screen();
        }
        return null;
    }

    /**
     * Get current screen id
     * @return string
     */
    public static function getCurrentId()
    {
        if (self::getCurrent() != null) {
            return self::getCurrent()->id;
        }
        return null;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isLoaded($id)
    {
        return IfwPsn_Wp_Proxy_Action::did('load-'. $id);
    }

    /**
     * @return bool
     */
    public static function isLoadedCurrentScreen()
    {
        return function_exists('get_current_screen') &&
            self::getCurrentId() != null &&
            IfwPsn_Wp_Proxy_Action::did('load-'. self::getCurrentId());
    }

    /**
     * @param $args
     */
    public static function addHelpTab($args)
    {
        self::getCurrent()->add_help_tab($args);
    }

    /**
     * @param $content
     */
    public static function setHelpSidebar($content)
    {
        self::getCurrent()->set_help_sidebar($content);
    }
}
