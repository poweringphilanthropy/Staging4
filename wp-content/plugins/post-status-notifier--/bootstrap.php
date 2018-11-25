<?php
/**
 * Plugin bootstrap
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: bootstrap.php 449 2015-12-13 14:11:09Z timoreithde $
 */
class Psn_Bootstrap extends IfwPsn_Wp_Plugin_Bootstrap_Abstract
{
    /**
     * @var Psn_Notification_Manager
     */
    protected $_notificationManager;

    /**
     * Attach bootstrap observers
     */
    protected function _attachObservers()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Bootstrap/Observer/MenuPage.php';
        $this->addObserver(new Psn_Bootstrap_Observer_MenuPage());
    }

    /**
     * @return array
     */
    public function registerAdminAjaxRequests()
    {
        $ajaxRequestPluginInfo = new IfwPsn_Wp_Plugin_Metabox_PluginInfo($this->_pm);
        $ajaxRequestPluginInfo->getAjaxRequest()->register();
        $ajaxRequestPluginStatus = new IfwPsn_Wp_Plugin_Metabox_PluginStatus($this->_pm);
        $ajaxRequestPluginStatus->getAjaxRequest()->register();
        $ajaxRequestIfwFeed = new IfwPsn_Wp_Plugin_Metabox_IfwFeed($this->_pm);
        $ajaxRequestIfwFeed->getAjaxRequest()->register();
        $ajaxRequestRules = new Psn_Admin_Metabox_Rules($this->_pm);
        $ajaxRequestRules->getAjaxRequest()->register();


        return array();
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        $featureLoader = new Psn_Feature_Loader($this->_pm);
        $featureLoader->load();

        if ($this->_pm->getAccess()->isAdmin()) {
            // on admin access

            // add plugin menu links
            IfwPsn_Wp_Proxy_Filter::addPluginActionLinks($this->_pm, array($this, 'addPluginActionLinks'));

            // set installer / uninstaller
            require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Installer/Activation.php';
            require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Installer/Uninstall.php';

            $this->getInstaller()->addActivation(new Psn_Installer_Activation());
            $this->getInstaller()->addUninstall(new Psn_Installer_Uninstall());

            // load options handler
            require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Options/Handler.php';
            $optionsHandler = new Psn_Admin_Options_Handler($this->_pm);
            $optionsHandler->load();
        }

        if ($this->_pm->getAccess()->isAdmin() || $this->_pm->getAccess()->isNetworkAdmin()) {
            // register patches
            require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Patch/Database.php';
            $this->getUpdateManager()->getPatcher()->addPatch(new Psn_Patch_Database());
        }

        if ($this->_pm->getAccess()->isPlugin()) {

            // register selftests
            IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'selftester_activate', array($this, 'addSelftests'));
        }

        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Notification/Manager.php';
        $this->_notificationManager = new Psn_Notification_Manager($this->_pm);
        $this->_notificationManager->setDeferredExecution();

        //IfwPsn_Wp_Proxy_Action::addInit(array($this, 'test'));
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Test/RuleModel.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Test/BccField.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Test/BccSelectField.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Test/CcSelectField.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Test/CategoriesField.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/Psn/Test/ExcludeCurrentUserField.php';

        $selftester->addTestCase(new Psn_Test_RuleModel());
        $selftester->addTestCase(new Psn_Test_BccField());
        $selftester->addTestCase(new Psn_Test_BccSelectField());
        $selftester->addTestCase(new Psn_Test_CcSelectField());
        $selftester->addTestCase(new Psn_Test_CategoriesField());
        $selftester->addTestCase(new Psn_Test_ExcludeCurrentUserField());
    }

    /**
     * 
     */
    public function addPluginActionLinks($links, $file)
    {
        $links[] = '<a href="' . substr(IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'index'), 1) . '">' . __('Settings', 'psn') . '</a>';
        return $links;
    }

    /**
     * @return \Psn_Notification_Manager
     */
    public function getNotificationManager()
    {
        return $this->_notificationManager;
    }

    public function test()
    {
    }
}
