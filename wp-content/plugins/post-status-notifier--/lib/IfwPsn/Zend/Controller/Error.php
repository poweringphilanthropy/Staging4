<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Error.php 276 2014-05-01 21:33:57Z timoreithde $
 */ 
class IfwPsn_Zend_Controller_Error extends IfwPsn_Vendor_Zend_Controller_Action
{
    /**
     * Application config
     * @var array
     */
    protected $_config;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;



    public function init()
    {
        parent::init();

        // set config
        $this->_config = $this->getInvokeArg('bootstrap')->getOptions();

        $this->_pm = $this->_config['pluginmanager'];

        $this->_pm->getLogger()->logPrefixed('Init controller '. get_class($this));
    }

    public function errorAction()
    {
        $this->view->pm = $this->_pm;

        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = __('You have reached the error page', 'ifw');
            return;
        }

        switch ($errors->type) {
            case IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                // $this->getResponse()->setHttpResponseCode(404);
                $priority = IfwPsn_Vendor_Zend_Log::NOTICE;
                $this->view->message = __('Page not found', 'ifw');
                break;
            default:
                // application error
                // $this->getResponse()->setHttpResponseCode(500);
                $priority = IfwPsn_Vendor_Zend_Log::CRIT;
                $this->view->message = __('Application error', 'ifw');
                break;
        }

        // Log exception
        $this->_pm->getLogger()->error($this->view->message);
        $this->_pm->getLogger()->error($errors->exception->getMessage());

        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
        $this->view->exception = $errors->exception;
        // conditionally display exceptions in dev env
        $this->view->displayExceptions = $this->getInvokeArg('displayExceptions');

        $this->view->langHeadline = __('An error occurred', 'ifw');
    }

}