<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Wp.php 340 2014-10-09 21:12:21Z timoreithde $
 */ 
class IfwPsn_Zend_Controller_Dispatcher_Wp extends IfwPsn_Vendor_Zend_Controller_Dispatcher_Standard
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Vendor_Zend_Controller_Action_Interface
     */
    protected $_controller;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        parent::__construct();
        $this->_pm = $pm;
    }


    /**
     * Dispatch to a controller/action
     *
     * By default, if a controller is not dispatchable, dispatch() will throw
     * an exception. If you wish to use the default controller instead, set the
     * param 'useDefaultControllerAlways' via {@link setParam()}.
     *
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract $request
     * @param IfwPsn_Vendor_Zend_Controller_Response_Abstract $response
     * @throws IfwPsn_Vendor_Zend_Controller_Dispatcher_Exception
     * @throws Exception
     * @return void
     */
    public function dispatch(IfwPsn_Vendor_Zend_Controller_Request_Abstract $request, IfwPsn_Vendor_Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        if (!($this->_controller instanceof IfwPsn_Vendor_Zend_Controller_Action_Interface) or
            !strpos(strtolower($request->getControllerName()), strtolower($request->get('controller')))) {

            // if controller is not initialized by initController already or on error/excption

            /**
             * Get controller class
             */
            if (!$this->isDispatchable($request)) {
                $controller = $request->getControllerName();

                if (!$this->getParam('useDefaultControllerAlways') && !empty($controller)) {
                    //require_once 'IfwZend/Controller/Dispatcher/Exception.php';
                    throw new IfwPsn_Vendor_Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
                }

                $className = $this->getDefaultControllerClass($request);
            } else {
                $className = $this->getControllerClass($request);
                if (!$className) {
                    $className = $this->getDefaultControllerClass($request);
                }
            }

            /**
             * Load the controller class file
             */
            $className = $this->loadClass($className);

            /**
             * Instantiate controller with request, response, and invocation
             * arguments; throw exception if it's not an action controller
             */
            $this->_controller = new $className($request, $this->getResponse(), $this->getParams());
            if (!($this->_controller instanceof IfwPsn_Vendor_Zend_Controller_Action_Interface) &&
                !($this->_controller instanceof IfwPsn_Vendor_Zend_Controller_Action)) {
                //require_once 'IfwZend/Controller/Dispatcher/Exception.php';
                throw new IfwPsn_Vendor_Zend_Controller_Dispatcher_Exception(
                    'Controller "' . $className . '" is not an instance of IfwPsn_Vendor_Zend_Controller_Action_Interface'
                );
            }
        }

        /**
         * Retrieve the action name
         */
        $action = $this->getActionMethod($request);

        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');

        $obLevel   = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            $this->_pm->getLogger()->logPrefixed(sprintf('Dispatching action %s on controller %s', $action, get_class($this->_controller)));
            $this->_controller->dispatch($action);
        } catch (Exception $e) {
            // Clean output buffer on error
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }
            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        // Destroy the page controller instance and reflection objects
        $this->_controller = null;
    }

    /**
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract $request
     * @param IfwPsn_Vendor_Zend_Controller_Response_Abstract $response
     * @throws IfwPsn_Vendor_Zend_Controller_Dispatcher_Exception
     */
    public function initController(IfwPsn_Vendor_Zend_Controller_Request_Abstract $request, IfwPsn_Vendor_Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (!$this->isDispatchable($request)) {

            $controller = $request->getControllerName();
            if (!$this->getParam('useDefaultControllerAlways') && !empty($controller)) {
                //require_once 'IfwZend/Controller/Dispatcher/Exception.php';
                throw new IfwPsn_Vendor_Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
            }

            $className = $this->getDefaultControllerClass($request);
        } else {

            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * Load the controller class file
         */
        $className = $this->loadClass($className);

        /**
         * Instantiate controller with request, response, and invocation
         * arguments; throw exception if it's not an action controller
         */
        $controller = new $className($request, $this->getResponse(), $this->getParams());
        
        if (!($controller instanceof IfwPsn_Vendor_Zend_Controller_Action_Interface) &&
            !($controller instanceof IfwPsn_Vendor_Zend_Controller_Action)) {
            //require_once 'IfwZend/Controller/Dispatcher/Exception.php';
            throw new IfwPsn_Vendor_Zend_Controller_Dispatcher_Exception(
                'Controller "' . $className . '" is not an instance of IfwPsn_Vendor_Zend_Controller_Action_Interface'
            );
        }

        if (method_exists($controller, 'onBootstrap')) {
            $controller->onBootstrap();
        }
        // add WP actions hooks
        IfwPsn_Wp_Proxy_Action::addAdminMenu(array($controller, 'onAdminMenu'));
        IfwPsn_Wp_Proxy_Action::addAdminInit(array($controller, 'onAdminInit'));
        IfwPsn_Wp_Proxy_Action::addCurrentScreen(array($controller, 'onCurrentScreen'));

        $this->_controller = $controller;
    }

    /**
     * @return IfwPsn_Vendor_Zend_Controller_Action_Interface
     */
    public function getController()
    {
        return $this->_controller;
    }

}
