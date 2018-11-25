<?php
/**
 * Log controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnDeferredsendinglogController.php 400 2015-08-18 20:15:45Z timoreithde $
 * @package  IfwPsn_Wp
 */
require_once 'PsnDeferredsendingAbstractController.php';

class DeferredSending_PsnDeferredsendingLogController extends DeferredSending_PsnDeferredsendingAbstractController
{
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $page = $this->_navigation->findOneByModule('deferredsending'); /* @var $page Zend_Navigation_Page */
        if ( $page ) {
            $page->setActive();
        }
    }

    /**
     * 
     */
    public function indexAction()
    {
        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Mail queue', 'psn_def'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar('mailqueue.html'))
            ->load();

        $this->_initListTable();
        $this->view->listTable = $this->_listTable;

        $this->view->dbModel = new Psn_Module_DeferredSending_Model_MailQueueLog();
    }

    /**
     *
     * @return string
     */
    protected function _getHelpText()
    {
        return sprintf(__('Please consider the documentation page <a href="%s" target="_blank">%s</a> for more information.', 'ifw'),
            'http://docs.ifeelweb.de/post-status-notifier/mailqueue.html',
            __('Mailqueue', 'psn_def'));
    }
    
    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Module_DeferredSending_Model_MailQueueLog';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Module_DeferredSending_Model_Mapper_MailQueueLog::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Module_DeferredSending_ListTable_MailQueueLog($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('deferredsendinglog', 'index', 'post-status-notifier', array('mod' => 'deferredsending'));
    }
}
