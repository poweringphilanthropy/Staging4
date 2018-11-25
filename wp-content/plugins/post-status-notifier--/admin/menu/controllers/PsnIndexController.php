<?php
/**
 * Index controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnIndexController.php 414 2015-09-17 10:06:02Z timoreithde $
 * @package  IfwPsn_Wp
 */
class PsnIndexController extends PsnApplicationController
{
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'index') {
            $this->enqueueScripts();
        }
    }

    public function onCurrentScreen()
    {
        if ($this->_request->getActionName() == 'index') {

            $pointer = new IfwPsn_Wp_Plugin_Menu_Pointer('psn_link_create_rule');
            $pointer->setHeader(__('Manage rules', 'psn'))
                ->setContent(sprintf(__('In the "Rules" section you can manage your post status notification rules.<br>Just try it and <a href="%s">create a new rule</a>.', 'psn'), IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'rules', 'create')))
                ->setEdge('top')->setAlign('left')
                ->renderTo('#nav-rules');

            if ($this->_pm->isPremium()) {
                // license input notice
                $license = $this->_pm->getOptionsManager()->getOption('license_code');
                if (empty($license)) {
                    $pointer = new IfwPsn_Wp_Plugin_Menu_Pointer('psn_license_notice');
                    $pointer->setHeader(__('Enter license code', 'ifw'))
                        ->setContent(sprintf(__('Please enter the plugin license code in the <a href="%s">options panel</a> to be able to receive <b>auto-updates</b> via the WordPress backend.', 'ifw'), IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'options')))
                        ->setEdge('top')->setAlign('left')
                        ->renderTo('#nav-options');
                }
            }
        }
    }

    /**
     * 
     */
    public function indexAction()
    {
        add_thickbox();

        $this->_pm->getLogger()->logPrefixed('Executing '. get_class($this) . ':indexAction()');

        $this->_pm->getBootstrap()->getSelftester()->performTests();

        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Metabox/Container.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Metabox/PremiumAd.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Metabox/PluginInfo.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Metabox/PluginStatus.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Metabox/IfwFeed.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Metabox/Rules.php';

        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Overview', 'psn'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar())
            ->load();

        // set up metaboxes
        $metaBoxContainerLeft = new IfwPsn_Wp_Plugin_Metabox_Container('column1', $this->_pageHook, 'left');
        IfwPsn_Wp_Proxy_Action::doAction('psn_admin_overview_before_metabox_left', $metaBoxContainerLeft);
        $metaBoxContainerLeft->addMetabox(new Psn_Admin_Metabox_Rules($this->_pm));
        IfwPsn_Wp_Proxy_Action::doAction('psn_admin_overview_after_metabox_left', $metaBoxContainerLeft);
        
        $metaBoxContainerRight = new IfwPsn_Wp_Plugin_Metabox_Container('column2', $this->_pageHook, 'right');
        IfwPsn_Wp_Proxy_Action::doAction('psn_admin_overview_before_metabox_right', $metaBoxContainerRight);
        if ($this->_pm->hasPremium() && $this->_pm->isPremium() == false) {
            $metaBoxContainerRight->addMetabox(new IfwPsn_Wp_Plugin_Metabox_PremiumAd($this->_pm));
        }
        $metaBoxContainerRight->addMetabox(new IfwPsn_Wp_Plugin_Metabox_PluginInfo($this->_pm));
        $metaBoxContainerRight->addMetabox(new IfwPsn_Wp_Plugin_Metabox_PluginStatus($this->_pm));
        $metaBoxContainerRight->addMetabox(new IfwPsn_Wp_Plugin_Metabox_IfwFeed($this->_pm));
        IfwPsn_Wp_Proxy_Action::doAction('psn_admin_overview_after_metabox_right', $metaBoxContainerRight);
        
        $this->view->metaBoxContainerLeft = $metaBoxContainerLeft;
        $this->view->metaBoxContainerRight = $metaBoxContainerRight;
    }

    public function admailtplAction()
    {
        $this->view->featureName = __('Mail templates', 'psn');

        $this->_helper->viewRenderer('premiumad');
    }

    public function adrecipientslistsAction()
    {
        $this->view->featureName = __('Recipients lists', 'psn');

        $this->_helper->viewRenderer('premiumad');
    }
    
    /**
     *
     * @return string
     */
    protected function _getHelpText()
    {
        return __('This is an overview of your plugin settings', 'psn');
    }
    
    public function enqueueScripts()
    {
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery-ui-dialog');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui-dialog');
    }
}
