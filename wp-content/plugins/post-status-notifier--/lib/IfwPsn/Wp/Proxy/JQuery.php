<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Wp Proxy for jQuery
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: JQuery.php 171 2013-06-29 16:01:58Z timoreithde $
 */
class IfwPsn_Wp_Proxy_JQuery
{
    /**
     * List of all effects
     * @var array
     */
    protected static $_effects = array('blind', 'bounce', 'clip', 'drop', 'explode', 'fade', 'fold', 'highlight',
            'pulsate', 'scale', 'shake', 'slide');
    
    /**
     * Retrieves list of all jQuery effects
     * @return array
     */
    public static function getEffects()
    {
        return self::$_effects;
    }
    
    /**
     * Enqueues a jQuery effect
     * @param string $effect
     */
    public static function loadEffect($effect)
    {
        if (in_array($effect, self::getEffects())) {
            IfwPsn_Wp_Proxy_Script::load('jquery-effects-'. $effect);
        }
    }

    /**
     * Retrieve the available jQuery UI themes
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return array
     */
    public static  function getUiThemes(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $themes = array();
        
        $themes_dir = self::getUiThemeRoot($pm);
        
        foreach (new DirectoryIterator($themes_dir) as $fileinfo) {
        
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
        
            $theme_name = $fileinfo->getFilename();
            if (is_dir($themes_dir . $theme_name) && ($theme_file = self::getUiThemeFile($theme_name, $pm)) != '') {
                $themes[] = $theme_name;
            }
        }
        
        return $themes;
    }
    
    /**
     * Retrieves the root UI theme directory (where all theme folders are)
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return string
     */
    public static function getUiThemeRoot(IfwPsn_Wp_Plugin_Manager $pm)
    {
        return $pm->getPathinfo()->getRootCss() . 'jqueryui' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieves the URL of a UI theme css file
     *
     * @param $theme_name
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return string
     */
    public static function getUiThemeUrl($theme_name, IfwPsn_Wp_Plugin_Manager $pm)
    {
        return $pm->getEnv()->getUrlCss() . 'jqueryui/themes/' . $theme_name . '/' . self::getUiThemeFile($theme_name, $pm);
    }
    
    /**
     * Retrieves the name of the default UI theme
     * 
     * @return string
     */
    public static function getUiDefaultTheme()
    {
        return 'ui-lightness';
    }

    /**
     * Checks if a ui theme exists
     *
     * @param $theme_name
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return bool
     */
    public static function isValidUiTheme($theme_name, IfwPsn_Wp_Plugin_Manager $pm)
    {
        return self::getUiThemeFile($theme_name, $pm) != '';
    }
    
    /**
     * Loads a ui theme if it exists
     * 
     * @param string $theme_name
     * @param string $handle
     * @return false
     */
    public static function loadUiTheme($theme_name, IfwPsn_Wp_Plugin_Manager $pm, $handle='jquery-ui-custom')
    {
        if (self::isValidUiTheme($theme_name, $pm)) {
            IfwPsn_Wp_Proxy_Style::load($handle, self::getUiThemeUrl($theme_name, $pm));
            return true;
        }
        return false;
    }

    /**
     * Retrieves the root css file of a theme directory
     *
     * @param string $theme_name
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return string empty string on failure
     */
    public static function getUiThemeFile($theme_name, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $file_name = '';
        $theme_root = self::getUiThemeRoot($pm) . $theme_name;
        
        if (is_dir($theme_root)) {
            foreach(scandir($theme_root) as $item) {
                if (strstr($item, 'custom.css')) {
                    $file_name = $item;
                    break;
                }
            }
        }
        
        return $file_name;
    }
}