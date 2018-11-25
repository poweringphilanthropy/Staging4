<?php
/**
 * Prepares custom controller name
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: WpRequestDispatcher.php 490 2015-11-20 22:17:07Z timoreithde $
 */
class IfwPsn_Zend_Controller_Plugin_WpRequestDispatcher extends IfwPsn_Vendor_Zend_Controller_Plugin_Abstract
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    
    
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }
    
    /**
     * Sets custom controller name
     * 
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract $request
     * @return bool
     */
    public function preDispatch(IfwPsn_Vendor_Zend_Controller_Request_Abstract $request)
    {
        $response = $this->getResponse();
        $response->headersSentThrowsException = false;

        try {

            if ($request->getControllerName() == $this->_pm->getAbbrLower() . '-error') {
                return;
            //} elseif ($this->_isPatchesRedirect($request)) {
            //    $request->setModuleName('default');
            //    $request->setControllerName($this->_pm->getAbbrLower() . '-patches');
            //    $request->setActionName('index');
            } else {
                $customController = $this->_getCustomController($request);

                if ($customController != false) {
                    // set the custom controller if exists
                    $request->setControllerName($customController);
                }
            }

        } catch (Exception $e) {

            // Repoint the request to the default error handler
            $request->setModuleName('default');
            $request->setControllerName($this->_pm->getAbbr() . '-error');
            $request->setActionName('error');

            // Set up the error handler
            $error = new IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler($this->_pm);
            $error->type = IfwPsn_Vendor_Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $error->request = clone($request);
            $error->exception = $e;
            $request->setParam('error_handler', $error);
        }
    }

    protected function _isPatchesRedirect(IfwPsn_Vendor_Zend_Controller_Request_Abstract $request)
    {
        return $this->_pm->getBootstrap()->getUpdateManager()->getPatcher()->isPatchesAvailable() &&
            $request->getControllerName() != 'patches' &&
            $request->getActionName() != 'execute';
    }
    
    /**
     * Retrieves custom controller name
     *  
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract $request
     * @return string|boolean
     */
    protected function _getCustomController($request)
    {
        $controllerName = $this->_pm->getAbbr() .'-'. $request->get('controller');
        return $controllerName;
    }
}