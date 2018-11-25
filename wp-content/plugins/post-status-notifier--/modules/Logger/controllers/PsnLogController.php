<?php
/**
 * Index controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnLogController.php 398 2015-08-17 21:50:58Z timoreithde $
 * @package  IfwPsn_Wp
 */
class Logger_PsnLogController extends PsnModelBindingController
{
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {

        if ($this->_request->getActionName() == 'index') {

            $this->_listTable = $this->getListTable();

            if ($this->_listTable->hasValidBulkRequest()) {

                if ($this->_request->getPost('action') == 'delete' && is_array( $this->_request->getPost($this->getSingular()) )) {

                    // bulk action delete
                    $this->_bulkDelete( $this->_request->getPost($this->getSingular()) );

                } else if ($this->_request->getPost('action') == 'clear') {

                    // bulk clear log
                    $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->clear();

                } else if ($this->_request->getPost('action') == 'clear_type_mail') {

                    // bulk clear type mail
                    $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->clear(array('type' => Psn_Logger_Bootstrap::LOG_TYPE_SENT_MAIL));

                } else if ($this->_request->getPost('action') == 'clear_type_log') {

                    // bulk clear type log
                    $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->clear(array('type' => Psn_Logger_Bootstrap::LOG_TYPE_INFO));

                }
            }
        }
    }

    public function onBootstrap()
    {
        if ($this->_request->getActionName() == 'index') {
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Screen/Option/PerPage.php';
            $this->_perPage = new IfwPsn_Wp_Plugin_Screen_Option_PerPage($this->_pm, __('Items per page', 'ifw'), $this->getModelMapper()->getPerPageId($this->getPluginAbbr() . '_'));
        }
    }

    /**
     * 
     */
    public function indexAction()
    {
        $this->enqueueScripts();

        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';

        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Log', 'psn_log'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar('log.html'))
            ->load();

        $this->_initListTable();
        $this->view->listTable = $this->_listTable;
    }

    /**
     * @param array $priority
     */
    protected function _bulkClear($priority = null)
    {
        $this->_pm->getLogger()->clear($priority);
    }
    
    /**
     *
     * @return string
     */
    protected function _getHelpText()
    {
        return sprintf(__('Please consider the documentation page <a href="%s" target="_blank">%s</a> for more information.', 'ifw'),
            'http://docs.ifeelweb.de/post-status-notifier/log.html',
            __('Log', 'psn_log'));
    }

    public function enqueueScripts()
    {
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery');
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery-ui-core');
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery-ui-dialog');

        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui-dialog');
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Module_Logger_Model_Log';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Module_Logger_Model_Mapper_Log::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Module_Logger_ListTable_Log($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('log', 'index', 'post-status-notifier', array('mod' => 'logger'));
    }
}
