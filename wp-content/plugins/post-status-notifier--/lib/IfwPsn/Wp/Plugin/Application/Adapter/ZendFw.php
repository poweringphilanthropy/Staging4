<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Adapter to use ZendFramework as admin application
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: ZendFw.php 490 2015-11-20 22:17:07Z timoreithde $
 * @package   IfwPsn_Wp_Plugin_Application
 */
require_once dirname(__FILE__) . '/Interface.php';

class IfwPsn_Wp_Plugin_Application_Adapter_ZendFw implements IfwPsn_Wp_Plugin_Application_Adapter_Interface
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Zend_Application
     */
    protected $_application;

    /**
     * @var string
     */
    protected $_output;

    /**
     * The default error reporting level
     * @var int
     */
    protected $_errorReporting;

    /**
     * @var bool
     */
    protected $_isInit = false;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct (IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    /**
     * Loads the admin application
     */
    public function load()
    {
        $this->_registerAutostart();

        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Application.php';

        $this->_application = new IfwPsn_Zend_Application($this->_pm->getEnv()->getEnvironmet());

        // set the dynamic options from php config file
        $this->_application->setOptions($this->_getApplicationOptions());

        // run the application bootstrap
        $this->_pm->getLogger()->logPrefixed('Bootstrapping application...');
        $this->_application->bootstrap();
    }

    /**
     *
     */
    protected function _registerAutostart()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Application/Adapter/ZendFw/Autostart/EnqueueScripts.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Application/Adapter/ZendFw/Autostart/StripSlashes.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Application/Adapter/ZendFw/Autostart/ZendFormTranslation.php';

        $result = array(
            new IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_EnqueueScripts($this),
            new IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_StripSlashes($this),
            new IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_ZendFormTranslation($this),
        );

        foreach($result as $autostart) {
            $autostart->execute();
        }
    }

    /**
     * Retrieves the application options
     * @return array
     */
    protected function _getApplicationOptions()
    {
        $options = include $this->_pm->getPathinfo()->getRootAdminMenu() . 'configs/application.php';
        if ($this->_pm->getEnv()->getEnvironmet() == 'development') {
            $options['resources']['FrontController']['params']['displayExceptions'] = 1;
            $options['phpSettings']['error_reporting'] = 6143; // E_ALL & ~E_STRICT
            $options['phpSettings']['display_errors'] = 1;
            $options['phpSettings']['display_startup_errors'] = 1;
        }
        return $options;
    }

    /**
     * @param $controllerName
     * @param string $module
     */
    public function overwriteController($controllerName, $module = 'default')
    {
        $front = IfwPsn_Zend_Controller_Front::getInstance();
        $request = new IfwPsn_Vendor_Zend_Controller_Request_Http();

        $request->setParam('controller', $controllerName);
        $request->setParam('mod', $module);

        $front->setRequest($request);
    }

    /**
     * Inits the controller
     */
    public function init()
    {
        if ($this->_isInit) {
            return;
        }

        $this->_pm->getErrorHandler()->enableErrorReporting();

        try {
            // init the controller object to add actions before load-{page-id} action
            $this->_application->initController();
            $this->_isInit = true;

        } catch (Exception $e) {
            $this->_handleException($e);
        }

        $this->_pm->getErrorHandler()->disableErrorReporting();
    }

    /**
     * @return mixed|void
     */
    public function render()
    {
        $this->_pm->getErrorHandler()->enableErrorReporting();

        try {
            $this->_output = $this->_application->run();
        } catch (Exception $e) {
            $this->_handleException($e);
        }

        $this->_pm->getErrorHandler()->disableErrorReporting();
    }

    /**
     * @return mixed|void
     */
    public function display()
    {
        $this->_pm->getErrorHandler()->enableErrorReporting();

        try {
            echo $this->_output;

        } catch (Exception $e) {
            $this->_handleException($e);
        }

        $this->_pm->getErrorHandler()->disableErrorReporting();
    }

    /**
     * @param Exception $e
     */
    protected function _handleException(Exception $e)
    {
        $this->_pm->getLogger()->error($e->getMessage());

        $request = IfwPsn_Zend_Controller_Front::getInstance()->getRequest();

        // Set up the error handler
        $error = new IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler(array(
            'controller' => $this->_pm->getAbbrLower() . '-error'
        ));
        $error->type = IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER;
        if (is_object($request)) {
            $error->request = clone($request);
        }
        $error->exception = $e;

        $request->setParam('error_handler', $error);
    }

    /**
     * @return IfwPsn_Wp_Plugin_Manager
     */
    public function getPluginManager()
    {
        return $this->_pm;
    }
}
