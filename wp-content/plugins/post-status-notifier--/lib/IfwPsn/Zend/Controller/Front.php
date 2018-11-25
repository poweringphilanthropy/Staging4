<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Front.php 269 2014-04-25 23:29:54Z timoreithde $
 */
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Front.php';

require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Dispatcher/Interface.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Dispatcher/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Dispatcher/Standard.php';

require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Router/Interface.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Router/Route/Interface.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Router/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Router/Route/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Router/Rewrite.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Router/Route/Module.php';

require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Request/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Request/Http.php';

require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Response/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Response/Http.php';

require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Plugin/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Plugin/Broker.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Plugin/ErrorHandler.php';

require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Action/Interface.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Action.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Action/HelperBroker.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Action/HelperBroker/PriorityStack.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Action/Helper/Abstract.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Controller/Action/Helper/ViewRenderer.php';

class IfwPsn_Zend_Controller_Front extends IfwPsn_Vendor_Zend_Controller_Front
{
    /**
     * Overwrite getInstance to use custom front controller
     *
     * @return IfwPsn_Zend_Controller_Front|IfwPsn_Vendor_Zend_Controller_Front|null
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function initRouter(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (!$this->getRouter()->hasRoute('requestVars')) {
            $this->getRouter()->addRoute('requestVars', new IfwPsn_Zend_Controller_Router_Route_RequestVars($pm));
        }
        return $this;
    }

    /**
     * Dispatch an HTTP request to a controller/action.
     *
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract|null $request
     * @param IfwPsn_Vendor_Zend_Controller_Response_Abstract|null $response
     * @throws Exception
     * @return void|IfwPsn_Vendor_Zend_Controller_Response_Abstract Returns response object if returnResponse() is true
     */
    public function dispatch(IfwPsn_Vendor_Zend_Controller_Request_Abstract $request = null, IfwPsn_Vendor_Zend_Controller_Response_Abstract $response = null)
    {
        if ($this->getDispatcher() instanceof IfwPsn_Zend_Controller_Dispatcher_Wp &&
            !($this->getDispatcher()->getController() instanceof IfwPsn_Vendor_Zend_Controller_Action_Interface)) {

            // skip if controller object already exists, already done by initController

            if (!$this->getParam('noErrorHandler') && !$this->_plugins->hasPlugin('IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler')) {
                // Register with stack index of 100
                //require_once 'IfwZend/Controller/Plugin/ErrorHandler.php';
                $this->_plugins->registerPlugin(new IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler(), 100);
            }

            if (!$this->getParam('noViewRenderer') && !IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
                //require_once 'IfwZend/Controller/Action/Helper/ViewRenderer.php';
                IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, new IfwPsn_Vendor_Zend_Controller_Action_Helper_ViewRenderer());
            }

            /**
             * Instantiate default request object (HTTP version) if none provided
             */
            if (null !== $request) {
                $this->setRequest($request);
            } elseif ((null === $request) && (null === ($request = $this->getRequest()))) {
                //require_once 'IfwZend/Controller/Request/Http.php';
                $request = new IfwPsn_Vendor_Zend_Controller_Request_Http();
                $this->setRequest($request);
            }

           /**
            * Set base URL of request object, if available
            */
            if (is_callable(array($this->_request, 'setBaseUrl'))) {
                if (null !== $this->_baseUrl) {
                    $this->_request->setBaseUrl($this->_baseUrl);
                }
            }

            /**
             * Instantiate default response object (HTTP version) if none provided
             */
            if (null !== $response) {
                $this->setResponse($response);
            } elseif ((null === $this->_response) && (null === ($this->_response = $this->getResponse()))) {
                //require_once 'IfwZend/Controller/Response/Http.php';
                $response = new IfwPsn_Vendor_Zend_Controller_Response_Http();
                $this->setResponse($response);
            }

            /**
             * Register request and response objects with plugin broker
             */
            $this->_plugins
                 ->setRequest($this->_request)
                 ->setResponse($this->_response);

        } // END: skip if controller object already exists

        // PROCEED with standard dispatch routine

        /**
         * Initialize router
         */
        $router = $this->getRouter();
        $router->setParams($this->getParams());

        /**
         * Initialize dispatcher
         */
        $dispatcher = $this->getDispatcher();
        $dispatcher->setParams($this->getParams())
            ->setResponse($this->_response);

        // Begin dispatch
        try {
            /**
             * Route request to controller/action, if a router is provided
             */

            /**
             * Notify plugins of router startup
             */
            $this->_plugins->routeStartup($this->_request);

            try {
                $router->route($this->_request);
            }  catch (Exception $e) {
                if ($this->throwExceptions()) {
                    throw $e;
                }

                $this->_response->setException($e);
            }

            /**
             * Notify plugins of router completion
             */
            $this->_plugins->routeShutdown($this->_request);

            /**
             * Notify plugins of dispatch loop startup
             */
            $this->_plugins->dispatchLoopStartup($this->_request);

            /**
             *  Attempt to dispatch the controller/action. If the $this->_request
             *  indicates that it needs to be dispatched, move to the next
             *  action in the request.
             */
            do {
                $this->_request->setDispatched(true);

                /**
                 * Notify plugins of dispatch startup
                 */
                $this->_plugins->preDispatch($this->_request);

                /**
                 * Skip requested action if preDispatch() has reset it
                 */
                if (!$this->_request->isDispatched()) {
                    continue;
                }

                /**
                 * Dispatch request
                 */
                try {
                    $dispatcher->dispatch($this->_request, $this->_response);
                } catch (Exception $e) {
                    if ($this->throwExceptions()) {
                        throw $e;
                    }
                    $this->_response->setException($e);
                }

                /**
                 * Notify plugins of dispatch completion
                 */
                $this->_plugins->postDispatch($this->_request);

            } while (!$this->_request->isDispatched());

        } catch (Exception $e) {
            if ($this->throwExceptions()) {
                throw $e;
            }

            $this->_response->setException($e);
        }

        /**
         * Notify plugins of dispatch loop completion
         */
        try {
            $this->_plugins->dispatchLoopShutdown();
        } catch (Exception $e) {
            if ($this->throwExceptions()) {
                throw $e;
            }

            $this->_response->setException($e);
        }

        if ($this->returnResponse()) {
            return $this->_response;
        }

        $this->_response->sendResponse();
    }

    /**
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract $request
     * @param IfwPsn_Vendor_Zend_Controller_Response_Abstract $response
     * @throws Exception
     */
    public function initController(IfwPsn_Wp_Plugin_Manager $pm, IfwPsn_Vendor_Zend_Controller_Request_Abstract $request = null, IfwPsn_Vendor_Zend_Controller_Response_Abstract $response = null)
    {
        if (!$this->getParam('noErrorHandler') && !$this->_plugins->hasPlugin('IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler')) {
            // Register with stack index of 100
            //require_once 'IfwZend/Controller/Plugin/ErrorHandler.php';
            $this->_plugins->registerPlugin(new IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler(), 100);
        }

        if (!$this->getParam('noViewRenderer') && !IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
            //require_once 'IfwZend/Controller/Action/Helper/ViewRenderer.php';
            IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, new IfwPsn_Vendor_Zend_Controller_Action_Helper_ViewRenderer());
        }

        /**
         * Instantiate default request object (HTTP version) if none provided
         */
        if (null !== $request) {
            $this->setRequest($request);
        } elseif ((null === $request) && (null === ($request = $this->getRequest()))) {
            //require_once 'IfwZend/Controller/Request/Http.php';
            $request = new IfwPsn_Vendor_Zend_Controller_Request_Http();

            $this->setRequest($request);
        }
        $request->setActionKey($pm->getConfig()->getActionKey());

        /**
         * Set base URL of request object, if available
         */
        if (is_callable(array($this->_request, 'setBaseUrl'))) {
            if (null !== $this->_baseUrl) {
                $this->_request->setBaseUrl($this->_baseUrl);
            }
        }

        /**
         * Instantiate default response object (HTTP version) if none provided
         */
        if (null !== $response) {
            $this->setResponse($response);
        } elseif ((null === $this->_response) && (null === ($this->_response = $this->getResponse()))) {
            //require_once 'IfwZend/Controller/Response/Http.php';
            $response = new IfwPsn_Vendor_Zend_Controller_Response_Http();
            $this->setResponse($response);
        }

        //IfwPsn_Wp_Proxy_Action::doPlugin($pm, 'before_controller_init', $this);

        /**
         * Register request and response objects with plugin broker
         */
        $this->_plugins
            ->setRequest($this->_request)
            ->setResponse($this->_response);

        IfwPsn_Wp_Proxy_Action::doPlugin($pm, 'before_controller_init', $this);

        /**
         * Initialize router
         */
        $router = $this->getRouter();
        $router->setParams($this->getParams());

        /**
         * Initialize dispatcher
         */
        $dispatcher = $this->getDispatcher();
        $dispatcher->setParams($this->getParams())
            ->setResponse($this->_response);

        // Begin dispatch
        try {
            /**
             * Route request to controller/action, if a router is provided
             */

            /**
             * Notify plugins of router startup
             */
            $this->_plugins->routeStartup($this->_request);

            try {
                $router->route($this->_request);
            }  catch (Exception $e) {
                throw $e;
            }

            /**
             * Needed for custom route RequestVars
             */
            $this->_plugins->routeShutdown($this->_request);

            /**
             * skip plugins dispatchLoopStartup on initController
             */
            //$this->_plugins->dispatchLoopStartup($this->_request);

            /**
             *  Attempt to dispatch the controller/action. If the $this->_request
             *  indicates that it needs to be dispatched, move to the next
             *  action in the request.
             */
            do {
                $this->_request->setDispatched(true);

                /**
                 * skip plugins preDispatch on initController
                 */
                $this->_plugins->preDispatch($this->_request);

                /**
                 * Skip requested action if preDispatch() has reset it
                 */
                //if (!$this->_request->isDispatched()) {
                //    continue;
                //}

                /**
                 * init controller
                 */
                try {
                    // this will add custom WP action to the controller object
                    $dispatcher->initController($this->_request, $this->_response);
                } catch (Exception $e) {
                    throw $e;
                }

                /**
                 * skip plugins postDispatch on initController
                 */
                //$this->_plugins->postDispatch($this->_request);
            } while (!$this->_request->isDispatched());
        } catch (Exception $e) {
            throw $e;
        }

    }
}
