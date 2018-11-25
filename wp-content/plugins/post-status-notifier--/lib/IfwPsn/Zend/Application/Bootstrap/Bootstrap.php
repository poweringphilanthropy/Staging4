<?php
/**
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Bootstrap.php 276 2014-05-01 21:33:57Z timoreithde $
 */
class IfwPsn_Zend_Application_Bootstrap_Bootstrap extends IfwPsn_Vendor_Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    /**
     *
     * @param IfwPsn_Vendor_Zend_Application $application
     */
    public function __construct($application)
    {
        $options = array(
            'IfwPsn_Vendor_Zend_Application_Resource' => IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource',
        );

        $this->_pluginLoader = new IfwPsn_Vendor_Zend_Loader_PluginLoader($options);

        parent::__construct($application);
    
        $this->_pm = $this->getApplication()->getOption('pluginmanager');



        // init the custom front controller instance
        $this->_pm->getLogger()->logPrefixed('Initializing front controller.');
        $front = IfwPsn_Zend_Controller_Front::getInstance();

        IfwPsn_Vendor_Zend_Controller_Front::getInstance()->returnResponse(true);

        // set dispatcher
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Controller/Dispatcher/Wp.php';
        $dispatcher = new IfwPsn_Zend_Controller_Dispatcher_Wp($this->_pm);

        IfwPsn_Vendor_Zend_Controller_Front::getInstance()->setDispatcher($dispatcher);
    }

    /**
     * Inits the controller object on bootstrap to place WP actions on before page load
     *
     * @throws IfwPsn_Vendor_Zend_Application_Bootstrap_Exception
     */
    public function initController()
    {
        $front = $this->getResource('FrontController');
        $default = $front->getDefaultModule();
        if (null === $front->getControllerDirectory($default)) {
            throw new IfwPsn_Vendor_Zend_Application_Bootstrap_Exception(
                'No default controller directory registered with front controller'
            );
        }

        $front->registerPlugin(new IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler(array(
            'controller' => $this->_pm->getAbbrLower() . '-error'
        )));

        $front->setParam('bootstrap', $this);
        $front->initController($this->_pm);
    }

    /**
     * Dispatches response on already initialized controller
     *
     * @return string
     */
    public function run()
    {
        // execute controller action on load-page
        $front = $this->getResource('FrontController');
        $response = $front->dispatch();
        if ($front->returnResponse()) {
            return $response;
        }
    }
    
    /**
     * Init custom router and plugins
     */
    protected function _initPlugin()
    {
        if ($this->_pm->getAccess()->isPlugin()) {

            $this->bootstrap('FrontController');
            $front = $this->getResource('FrontController');
//            $front = IfwPsn_Zend_Controller_Front::getInstance();

            // set custom router
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Controller/Router/WpRewrite.php';
            $front->setRouter(new IfwPsn_Zend_Controller_Router_WpRewrite());
            
            // launch the custom router to support request vars for controller / action
            $router = $front->getRouter();
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Controller/Router/Route/RequestVars.php';
            $router->addRoute('requestVars', new IfwPsn_Zend_Controller_Router_Route_RequestVars($this->_pm));
    
            // launch the wp request dispatcher
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Controller/Plugin/WpRequestDispatcher.php';
            $front->registerPlugin(new IfwPsn_Zend_Controller_Plugin_WpRequestDispatcher($this->_pm));
        }
    }
}