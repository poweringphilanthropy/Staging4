<?php
/**
 *
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: Navigation.php 435 2015-11-04 16:44:55Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Admin
 */
class Psn_Admin_Navigation
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Vendor_Zend_Navigation
     */
    protected $_navigation;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    public function load()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Vendor/Zend/Navigation/Container.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Vendor/Zend/Navigation.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Vendor/Zend/Navigation/Page.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Navigation/Page/WpMvc.php';

        $this->_registerLiteNav();

        $this->_navigation = new IfwPsn_Vendor_Zend_Navigation();

        IfwPsn_Zend_Controller_Front::getInstance()->initRouter($this->_pm);

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Overview', 'psn'),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'action' => 'index',
            'controller' => 'index',
            'route' => 'requestVars'
        ));
        $page->set('exactActiveMatch', true);
        $this->_navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_admin_navigation_overview', $this->_navigation);

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Rules', 'psn'),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'action' => 'index',
            'controller' => 'rules',
            'route' => 'requestVars'
        ));
        $this->_navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_admin_navigation_rules', $this->_navigation);

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Options', 'psn'),
            'controller' => 'options',
            'action' => 'index',
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $this->_navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_admin_navigation_options', $this->_navigation);

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Service', 'psn'),
            'controller' => 'service',
            'action' => 'index',
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $this->_navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_admin_navigation_service', $this->_navigation);
    }

    protected function _registerLiteNav()
    {
        if (!$this->_pm->isPremium()) {
            IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_admin_navigation_rules', array($this, 'addMailTplNav'));
            IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_admin_navigation_htmlmails', array($this, 'addRecipientsListsNav'));
        }
    }

    /**
     * @param $navigation
     */
    public function addMailTplNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Mail templates', 'psn'),
            'controller' => 'index',
            'action' => 'adMailTpl',
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $page->set('exactActiveMatch', true);

        $navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_admin_navigation_htmlmails', $navigation);
    }

    /**
     * @param $navigation
     */
    public function addRecipientsListsNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Recipients lists', 'psn'),
            'controller' => 'index',
            'action' => 'adRecipientsLists',
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $page->set('exactActiveMatch', true);

        $navigation->addPage($page);
    }

    /**
     * @return IfwPsn_Vendor_Zend_Navigation
     */
    public function getNavigation()
    {
        if (empty($this->_navigation)) {
            $this->load();
        }

        return $this->_navigation;
    }

    /**
     * @return array
     */
    public function getPagesWithHrefAndLabel()
    {
        $result = array();
        $nav = $this->getNavigation();

        /**
         * @var IfwPsn_Zend_Navigation_Page_WpMvc $page
         */
        foreach ($nav->getPages() as $page) {
            $result[] = array(
                'href' => IfwPsn_Wp_Proxy_Admin::getMenuUrl(
                    $this->_pm, $page->getController(),
                    $page->getAction(),
                    null,
                    array('module' => $page->getModule())),
                'label' => $page->getLabel()
            );
        }

        return $result;
    }
}
