<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnSelftestController.php 436 2015-11-04 16:55:05Z timoreithde $
 */ 
class PsnSelftestController extends PsnApplicationController
{

    /**
     * Perfoms all tests
     */
    public function indexAction()
    {
        $selftester = $this->_pm->getBootstrap()->getSelftester();
        $selftester->performTests();

        $this->view->urlHandle = IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'selftest', 'handle');
        $this->view->tests = $selftester->getTestCases();
        $this->view->langPerfomingTest = __('Performing test', 'ifw');
        $this->view->langResult = __('Result', 'ifw');
        $this->view->langMessage = __('Message', 'ifw');
        $this->view->langOK = __('OK', 'ifw');
        $this->view->langERROR = __('ERROR', 'ifw');
        echo $this->view->render('psn-selftest/index.phtml');

        exit;
    }

    /**
     * Handles the error
     */
    public function handleAction()
    {
        $key = $this->_request->get('id');

        $test = $this->_pm->getBootstrap()->getSelftester()->getTest($key);

        $this->view->message = $test->handleError($this->_pm);
        $this->view->langPerformSelftestAgain = __('Perform selftest again', 'ifw');

        $this->view->urlIndex = IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'selftest');
        echo $this->view->render('psn-selftest/handle.phtml');

        exit;
    }

}

