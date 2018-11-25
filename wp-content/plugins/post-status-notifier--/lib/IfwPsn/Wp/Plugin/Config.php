<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Plugin config based in Zend_Config_Ini
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Config.php 394 2015-02-18 01:02:00Z timoreithde $
 * @package   IfwPsn_Wp_Plugin
 */
require_once dirname(__FILE__) . '/../../Vendor/Zend/Config.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Config/Ini.php';

class IfwPsn_Wp_Plugin_Config extends IfwPsn_Vendor_Zend_Config_Ini
{
    /**
     * Instance store
     * @var array
     */
    public static $_instances = array();

    /**
     * Retrieves singleton IfwPsn_Wp_Plugin_Config object
     *
     * @param \IfwPsn_Wp_Pathinfo_Plugin|\IfwPsn_Wp_Plugin_Pathinfo $pluginPathinfo
     * @return IfwPsn_Wp_Plugin_Config
     */
    public static function getInstance(IfwPsn_Wp_Pathinfo_Plugin $pluginPathinfo)
    {
        $instanceToken = $pluginPathinfo->getDirname();
        
        if (!isset(self::$_instances[$instanceToken])) {
            $iniPath = $pluginPathinfo->getDirnamePath() . 'config.ini';
            $env = getenv('IFW_WP_ENV') ? getenv('IFW_WP_ENV') : 'production';
            self::$_instances[$instanceToken] = new self($iniPath, $env);
        }
        return self::$_instances[$instanceToken];
    }

    /**
     * @return string
     */
    public function getActionKey()
    {
        return $this->application->action->key;
    }
    /**
     * @return string
     */
    public function getControllerKey()
    {
        return $this->application->controller->key;
    }
}
