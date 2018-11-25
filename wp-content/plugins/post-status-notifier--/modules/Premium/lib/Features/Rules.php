<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Rules.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_Premium_Features_Rules extends IfwPsn_Wp_Plugin_Feature_Abstract
{
    function init()
    {
        if ($this->_pm->getAccess()->isPlugin()) {

            // add toolbar features:
            IfwPsn_Wp_Proxy_Filter::add('psn_rules_toolbar', array($this, 'addAdminLinks'));
            IfwPsn_Wp_Proxy_Filter::add('psn_rules_toolbar_after', array($this, 'afterAdminLinks'));

            // handle premium controller actions:
            IfwPsn_Wp_Proxy_Action::add('psn_rules_action-export', array($this, 'export'));
            IfwPsn_Wp_Proxy_Action::add('psn_rules_action-bulk_export', array($this, 'bulkExport'));
            IfwPsn_Wp_Proxy_Action::add('psn_rules_action-import', array($this, 'import'));
            IfwPsn_Wp_Proxy_Action::add('psn_rules_action-copy', array($this, 'copy'));

            // list table options
            IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'rules_bulk_actions', array($this, 'addBulkActions'));
            IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'rules_col_name_actions', array($this, 'addColNameActions'));
        }

        IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'max_rules', array($this, 'unsetMax'));
    }

    function load()
    {
        // TODO: Implement load() method.
    }


    public function addAdminLinks()
    {
        echo IfwPsn_Zend_Controller_ModelBinding::getImportItemsButton(__('Import rules', 'psn'));
    }

    public function unsetMax($max)
    {
        return 0;
    }

    public function afterAdminLinks()
    {
        echo PsnApplicationController::getImportForm(
            Psn_Model_Mapper_Rule::getInstance()->getSingular(), // uid
            __('Import rules', 'psn'), // title
            'rules.html#importing-rules', // help url
            admin_url('options-general.php?page=post-status-notifier&controller=rules&appaction=import') // form action
        );
    }

    /**
     * @param PsnRulesController $contoller
     */
    public function import(PsnRulesController $contoller)
    {
        $contoller->handleImport(array(
            'item_callback' => array($contoller, 'importItemCallback')
        ));
    }

    /**
     * @param PsnRulesController $contoller
     */
    public function export(PsnRulesController $contoller)
    {
        $contoller->handleExport( (int)$contoller->getRequest()->get('id'));

        $contoller->gotoIndex();
    }

    /**
     * @param PsnRulesController $contoller
     */
    public function bulkExport(PsnRulesController $contoller)
    {
        $contoller->handleExport( $contoller->getRequest()->getPost($contoller->getSingular()));

        $contoller->gotoIndex();
    }

    /**
     * @param PsnRulesController $contoller
     */
    public function copy(PsnRulesController $contoller)
    {
        $contoller->handleCopy(array(
            'values_callback' => array($this, 'copyCallback')
        ));
    }

    /**
     * @param $values
     * @return mixed
     */
    public function copyCallback($values)
    {
        if ($this->_pm->getOptionsManager()->getOption('psn_deactivate_copied_rules') !== null) {
            $values['active'] = 0;
        }
        return $values;
    }

    /**
     * @param $actions
     * @return mixed
     */
    public function addBulkActions($actions)
    {
        $actions['bulk_export'] = __('Export', 'psn');
        return $actions;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function addColNameActions($data)
    {
        $actions = $data['actions'];
        $item = $data['item'];

        $newActions = array();
        $newActions['edit'] = $actions['edit'];
        $newActions['copy'] = sprintf('<a href="?page=%s&controller=rules&appaction=copy&nonce=%s&id=%s" class="copyConfirm">'. __('Copy', 'psn') .'</a>',
            $_REQUEST['page'],
            wp_create_nonce(IfwPsn_Zend_Controller_ModelBinding::getCopyNonceAction(Psn_Model_Mapper_Rule::getInstance()->getSingular(), $item['id'])),
            $item['id']);
        $newActions['export'] = sprintf('<a href="?page=%s&controller=rules&appaction=export&id=%s">'. __('Export', 'psn') .'</a>', $_REQUEST['page'], $item['id']);
        $newActions['delete'] = $actions['delete'];

        return array('actions' => $newActions);
    }

}
