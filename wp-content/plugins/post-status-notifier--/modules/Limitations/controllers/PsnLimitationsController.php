<?php
/**
 * Index controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnLimitationsController.php 400 2015-08-18 20:15:45Z timoreithde $
 * @package  IfwPsn_Wp
 */
class Limitations_PsnLimitationsController extends PsnModelBindingController
{
    /**
     * @param $action
     */
    public function handleBulkAction($action)
    {
        if ( $action == 'clear' ) {
            IfwPsn_Wp_ORM_Model::factory($this->getModelName())->delete_many();
            $this->gotoIndex();
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
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';

        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Limitations', 'psn_lmt'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar('log.html'))
            ->load();

        $this->_initListTable();
        $this->view->listTable = $this->_listTable;
    }

    /**
     * Deletes a rule
     */
//    public function deleteAction()
//    {
//        $tplId = (int)$this->_request->get('id');
//
//        $item = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one((int)$this->_request->get('id'));
//        $item->delete();
//
//        $this->gotoIndex();
//    }

    /**
     * @param array $items
     */
//    protected function _bulkDelete(array $items)
//    {
//        foreach($items as $id) {
//            IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one((int)$id)->delete();
//        }
//
//        $this->gotoIndex();
//    }

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
        return 'Psn_Module_Limitations_Model_Limitations';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Module_Limitations_Model_Mapper_Limitations::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Module_Limitations_ListTable_Limitations($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('limitations', 'index', 'post-status-notifier', array('mod' => 'limitations'));
    }
}
