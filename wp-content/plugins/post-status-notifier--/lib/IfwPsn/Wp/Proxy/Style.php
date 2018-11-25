<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Wp Style Proxy
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Style.php 374 2014-12-29 15:36:43Z timoreithde $
 */
class IfwPsn_Wp_Proxy_Style
{
    /**
     * Container for styles to enqueue
     * @var array
     */
    private static $_styles = array();

    /**
     * Container for admin styles to enqueue
     * @var array
     */
    private static $_stylesAdmin = array();

    /**
     * Container for inline styles to enqueue
     * @var array
     */
    private static $_inline = array();

    /**
     * If enqueue function is set
     * @var bool
     */
    private static $_enqueueSet = false;

    /**
     * If admin enqueue function is set
     * @var bool
     */
    private static $_enqueueAdminSet = false;



    /**
     * @see wp_register_style() for parameter information
     */
    public static function register($handle, $src, $deps=array(), $ver=false, $media='all')
    {
        wp_register_style($handle, $src, $deps, $ver, $media);
    }

    /**
     * @see wp_deregister_style() for parameter information
     */
    public static function deregister($handle)
    {
        wp_deregister_style($handle);
    }
    
    /**
     * @see wp_enqueue_style() for parameter information
     */
    public static function enqueue($handle, $src=false, $deps=array(), $ver=false, $media='all')
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }

    /**
     * @see wp_dequeue_style() for parameter information
     */
    public static function denqueue($handle)
    {
        wp_dequeue_style($handle);
    }

    /**
     * @param $handle
     * @param $data
     */
    public static function addInline($handle, $data)
    {
        if (!isset(self::$_inline[$handle])) {
            self::$_inline[$handle] = array(
                'data' => $data
            );
        }
    }

    /**
     * Registers a style
     *
     * @param string $handle
     * @param bool|string $src
     * @param array $deps
     * @param bool $ver
     * @param string $media
     * @return void
     */
    public static function load($handle, $src=false, $deps=array(), $ver=false, $media='all')
    {
        if (!isset(self::$_styles[$handle])) {
            self::$_styles[$handle] = array(
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'media' => $media
            );
        }

        if (self::$_enqueueSet == false) {
            IfwPsn_Wp_Proxy_Action::addEnqueueStyles(array('IfwPsn_Wp_Proxy_Style', '_enqueueStyles'));
            self::$_enqueueSet = true;
        }
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $handle
     * @param bool $src
     * @param array $deps
     * @param bool $ver
     * @param string $media
     */
    public static function loadMinimized(IfwPsn_Wp_Plugin_Manager $pm, $handle, $src=false, $deps=array(), $ver=false, $media='all')
    {
        if ($pm->isProduction()) {
            $src = self::getMinimizedName($src);
        }
        self::load($handle, $src, $deps, $ver, $media);
    }

    /**
     * Registers a style for admin only
     *
     * @param $handle
     * @param bool $src
     * @param array $deps
     * @param bool $ver
     * @param string $media
     * @return void
     */
    public static function loadAdmin($handle, $src=false, $deps=array(), $ver=false, $media='all')
    {
        if (!isset(self::$_stylesAdmin[$handle])) {
            self::$_stylesAdmin[$handle] = array(
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'media' => $media
            );
        }


        if (self::$_enqueueAdminSet == false) {
            IfwPsn_Wp_Proxy_Action::addAdminEnqueueScripts(array('IfwPsn_Wp_Proxy_Style', '_enqueueAdminStyles'));
            self::$_enqueueAdminSet = true;
        }
    }

    /**
     * Registers a style for admin only
     *
     * @param $handle
     * @param bool $src
     * @param array $deps
     * @param bool $ver
     * @param string $media
     * @return void
     */
    public static function loadAdminMinimized(IfwPsn_Wp_Plugin_Manager $pm, $handle, $src=false, $deps=array(), $ver=false, $media='all')
    {
        if ($pm->isProduction()) {
            $src = self::getMinimizedName($src);
        }
        self::loadAdmin($handle, $src, $deps, $ver, $media);
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getMinimizedName($name)
    {
        return str_replace('.css', '.min.css', $name);
    }

    /**
     * Finally enqueues the style at the right moment (action)
     */
    public static function _enqueueStyles()
    {
        foreach (self::$_styles as $handle => $data) {
            self::enqueue($handle, $data['src'], $data['deps'], $data['ver'], $data['media']);
            if (isset(self::$_inline[$handle])) {
                wp_add_inline_style($handle, self::$_inline[$handle]['data']);
            }
        }
    }

    /**
     * Finally enqueues the style at the right moment (action)
     */
    public static function _enqueueAdminStyles()
    {
        foreach (self::$_stylesAdmin as $handle => $data) {
            self::enqueue($handle, $data['src'], $data['deps'], $data['ver'], $data['media']);
        }
    }
}
