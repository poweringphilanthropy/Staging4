<?php
/**
 * Overwrites Zend_Application constructor for disabling the Zend Autoloader
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Application.php 276 2014-05-01 21:33:57Z timoreithde $
 */
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Bootstrap/Bootstrapper.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Bootstrap/ResourceBootstrapper.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Bootstrap/BootstrapAbstract.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Bootstrap/Bootstrap.php';
require_once dirname(__FILE__) . '/Application/Bootstrap/Bootstrap.php';
require_once dirname(__FILE__) . '/Controller/Default.php';

require_once dirname(__FILE__) . '/../Vendor/Zend/Application.php';

require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Resource/Resource.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Resource/ResourceAbstract.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Resource/Frontcontroller.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Resource/Layout.php';

require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Bootstrap/BootstrapAbstract.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Bootstrap/Bootstrap.php';

require_once dirname(__FILE__) . '/../Vendor/Zend/Application/Module/Autoloader.php';

require_once dirname(__FILE__) . '/../Vendor/Zend/Controller/Action/Interface.php';
require_once dirname(__FILE__) . '/../Vendor/Zend/Controller/Action.php';

require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application.php';

class IfwPsn_Zend_Application extends IfwPsn_Vendor_Zend_Application
{
    /**
     * Constructor
     *
     * Initialize application. Potentially initializes include_paths, PHP
     * settings, and bootstrap class.
     *
     * @param  string $environment
     * @param  string|array|IfwPsn_Vendor_Zend_Config $options String path to configuration file, or array/IfwPsn_Vendor_Zend_Config of configuration options
     * @throws IfwPsn_Vendor_Zend_Application_Exception
     * @return \IfwPsn_Zend_Application
     */
    public function __construct($environment, $options = null)
    {
        $this->_environment = (string) $environment;
    
//        require_once 'IfwZend/Loader/Autoloader.php';
//         $this->_autoloader = IfwPsn_Vendor_Zend_Loader_Autoloader::getInstance();
    
        if (null !== $options) {
            if (is_string($options)) {
                $options = $this->_loadConfig($options);
            } elseif ($options instanceof IfwPsn_Vendor_Zend_Config) {
                $options = $options->toArray();
            } elseif (!is_array($options)) {
                throw new IfwPsn_Vendor_Zend_Application_Exception('Invalid options provided; must be location of config file, a config object, or an array');
            }
    
            $this->setOptions($options);
        }
    }

    public function initController()
    {
        // init the controller
        return $this->getBootstrap()->initController();
    }

    public function run()
    {
        return $this->getBootstrap()->run();
    }

    /**
     * @return null|IfwPsn_Vendor_Zend_Controller_Action_Interface
     */
    public function getController()
    {
        $front = $this->getBootstrap()->getResource('FrontController');
        return $front->getDispatcher()->getController();
    }

    public function hasController()
    {
        $front = $this->getBootstrap()->getResource('FrontController');
        return $front->getDispatcher()->getController() instanceof IfwPsn_Vendor_Zend_Controller_Action_Interface;
    }
}
