<?php
/**
 * Index controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnDeferredsendingController.php 400 2015-08-18 20:15:45Z timoreithde $
 * @package  IfwPsn_Wp
 */
require_once 'PsnDeferredsendingAbstractController.php';

class DeferredSending_PsnDeferredsendingController extends DeferredSending_PsnDeferredsendingAbstractController
{
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

        if ($this->_pm->hasOption('psn_deferred_sending_log_sent')) {
            $this->view->isLog = true;
        } else {
            $this->view->isLog = false;
        }

        $this->view->dbModel = new Psn_Module_DeferredSending_Model_MailQueue();
    }

    public function runAction()
    {
        if ( !$this->_verifyNonce('mailqueue-run') ) {
            $this->getAdminNotices()->persistError(__('Invalid access.', 'psn'));
            $this->gotoIndex();
        }

        Psn_Module_DeferredSending_Mailqueue_Handler::getInstance()->run();

        $this->gotoIndex();
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
        return 'Psn_Module_DeferredSending_Model_MailQueue';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Module_DeferredSending_Model_Mapper_MailQueue::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Module_DeferredSending_ListTable_MailQueue($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('deferredsending', 'index', 'post-status-notifier', array('mod' => 'deferredsending'));
    }
}
