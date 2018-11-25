<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Action.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_View_Helper_Abstract.php */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Abstract.php';

/**
 * Helper for rendering output of a controller action
 *
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_Action extends IfwPsn_Vendor_Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    public $defaultModule;

    /**
     * @var IfwPsn_Vendor_Zend_Controller_Dispatcher_Interface
     */
    public $dispatcher;

    /**
     * @var IfwPsn_Vendor_Zend_Controller_Request_Abstract
     */
    public $request;

    /**
     * @var IfwPsn_Vendor_Zend_Controller_Response_Abstract
     */
    public $response;

    /**
     * Constructor
     *
     * Grab local copies of various MVC objects
     *
     * @return void
     */
    public function __construct()
    {
        $front   = IfwPsn_Vendor_Zend_Controller_Front::getInstance();
        $modules = $front->getControllerDirectory();
        if (empty($modules)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Exception.php';
            $e = new IfwPsn_Vendor_Zend_View_Exception('Action helper depends on valid front controller instance');
            $e->setView($this->view);
            throw $e;
        }

        $request  = $front->getRequest();
        $response = $front->getResponse();

        if (empty($request) || empty($response)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Exception.php';
            $e = new IfwPsn_Vendor_Zend_View_Exception('Action view helper requires both a registered request and response object in the front controller instance');
            $e->setView($this->view);
            throw $e;
        }

        $this->request       = clone $request;
        $this->response      = clone $response;
        $this->dispatcher    = clone $front->getDispatcher();
        $this->defaultModule = $front->getDefaultModule();
    }

    /**
     * Reset object states
     *
     * @return void
     */
    public function resetObjects()
    {
        $params = $this->request->getUserParams();
        foreach (array_keys($params) as $key) {
            $this->request->setParam($key, null);
        }

        $this->response->clearBody();
        $this->response->clearHeaders()
                       ->clearRawHeaders();
    }

    /**
     * Retrieve rendered contents of a controller action
     *
     * If the action results in a forward or redirect, returns empty string.
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module Defaults to default module
     * @param  array $params
     * @return string
     */
    public function action($action, $controller, $module = null, array $params = array())
    {
        $this->resetObjects();
        if (null === $module) {
            $module = $this->defaultModule;
        }

        // clone the view object to prevent over-writing of view variables
        $viewRendererObj = IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::addHelper(clone $viewRendererObj);

        $this->request->setParams($params)
                      ->setModuleName($module)
                      ->setControllerName($controller)
                      ->setActionName($action)
                      ->setDispatched(true);

        $this->dispatcher->dispatch($this->request, $this->response);

        // reset the viewRenderer object to it's original state
        IfwPsn_Vendor_Zend_Controller_Action_HelperBroker::addHelper($viewRendererObj);


        if (!$this->request->isDispatched()
            || $this->response->isRedirect())
        {
            // forwards and redirects render nothing
            return '';
        }

        $return = $this->response->getBody();
        $this->resetObjects();
        return $return;
    }

    /**
     * Clone the current View
     *
     * @return IfwPsn_Vendor_Zend_View_Interface
     */
    public function cloneView()
    {
        $view = clone $this->view;
        $view->clearVars();
        return $view;
    }
}
