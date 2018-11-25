<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Abstract.php 237 2014-03-21 01:18:02Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Interface.php';

abstract class IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract implements IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface
{
    /**
     * @var IfwPsn_Wp_Plugin_Bootstrap_Abstract
     */
    protected $_bootstrap;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var mixed
     */
    protected $_resource;


    /**
     * @param $notificationType
     * @param IfwPsn_Wp_Plugin_Bootstrap_Abstract $bootstrap
     * @return mixed
     */
    public function notify($notificationType, IfwPsn_Wp_Plugin_Bootstrap_Abstract $bootstrap)
    {
        $this->_bootstrap = $bootstrap;
        $this->_pm = $bootstrap->getPluginManager();

        switch($notificationType) {
            case IfwPsn_Wp_Plugin_Bootstrap_Abstract::OBSERVER_PRE_BOOTSTRAP:
                if (method_exists($this, '_preBootstrap')) {
                    $this->_preBootstrap();
                }
                break;

            case IfwPsn_Wp_Plugin_Bootstrap_Abstract::OBSERVER_POST_MODULES:
                if (method_exists($this, '_postModules')) {
                    $this->_postModules();
                }
                break;

            case IfwPsn_Wp_Plugin_Bootstrap_Abstract::OBSERVER_POST_BOOTSTRAP:
                if (method_exists($this, '_postBootstrap')) {
                    $this->_postBootstrap();
                }
                break;

            case IfwPsn_Wp_Plugin_Bootstrap_Abstract::OBSERVER_SHUTDOWN_BOOTSTRAP:
                if (method_exists($this, '_shutdownBootstrap')) {
                    $this->_shutdownBootstrap();
                }
                break;

        }
    }

    /**
     * @return IfwPsn_Wp_Plugin_Installer
     */
    public function getResource()
    {
        return $this->_resource;
    }
}
