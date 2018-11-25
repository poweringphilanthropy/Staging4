<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Admin.php 494 2015-12-03 10:52:30Z timoreithde $
 */ 
class IfwPsn_Wp_Proxy_Admin
{
    /**
     * Alias for get_admin_url()
     *
     * @param null|string $blog_id
     * @param string $path
     * @param string $scheme
     * @return string
     */
    public static function getUrl($blog_id = null, $path = '', $scheme = 'admin')
    {
        return get_admin_url($path, $path, $scheme);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $controller
     * @param string $action
     * @param null $page
     * @param array $extra
     * @return string
     */
    public static function getMenuUrl(IfwPsn_Wp_Plugin_Manager $pm, $controller, $action='index', $page=null, $extra = array())
    {
        if ($page == null) {
            $page = $pm->getPathinfo()->getDirname();
        }

        $urlOptions = array_merge(array(
            $pm->getConfig()->getControllerKey() => $controller,
            $pm->getConfig()->getActionKey() => $action,
            'page' => $page
        ), $extra);

        $router = IfwPsn_Zend_Controller_Front::getInstance()->initRouter($pm)->getRouter();
        return $router->assemble($urlOptions, 'requestVars');
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $controller
     * @param string $action
     * @param null $page
     * @param array $extra
     * @return string
     */
    public static function getAdminPageUrl(IfwPsn_Wp_Plugin_Manager $pm, $page, $controller, $action='index', $extra = array())
    {
        $urlOptions = array_merge(array(
            $pm->getConfig()->getControllerKey() => $controller,
            $pm->getConfig()->getActionKey() => $action,
            'adminpage' => $page
        ), $extra);

        $router = IfwPsn_Zend_Controller_Front::getInstance()->initRouter($pm)->getRouter();
        return $router->assemble($urlOptions, 'requestVars');
    }


    public static function getOptionsBaseUrl()
    {
        return 'options-general.php';
    }

    public static function getAdminPageBaseUrl()
    {
        return IfwPsn_Wp_Proxy_Blog::getSiteUrl() . '/wp-admin/admin.php';
    }
}
