<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Main.php 415 2015-04-19 13:55:52Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Interface.php';

abstract class IfwPsn_Wp_Plugin_Menu_Page_Main implements IfwPsn_Wp_Plugin_Menu_Page_Interface
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * The title which will be shown in the menu
     * @var string
     */
    protected $_menuTitle;

    /**
     * The HTML page title
     * @var string
     */
    protected $_pageTitle;

    /**
     * @var string
     */
    protected $_capability;

    /**
     * @var string
     */
    protected $_slug;

    /**
     * @var string|callable
     */
    protected $_callback = '';

    /**
     * @var string
     */
    protected $_iconUrl = '';

    /**
     * @var int|null
     */
    protected $_position;

    /**
     * @var string
     */
    protected $_pageHook;

    /**
     * @var array
     */
    protected $_subPages = array();

    /**
     * @var null|array
     */
    protected $_subPagesSorted;

    /**
     * @var bool
     */
    protected $_isMultisite = false;


    /**
     * @param IfwPsn_Wp_Plugin_Manager
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        if (method_exists($this, '_init')) {
            $this->_init();
        }
    }

    public function load()
    {
        if ($this->isIsMultisite()) {
            IfwPsn_Wp_Proxy_Action::addNetworkAdminMenu(array($this, '_load'));

        } else {
            IfwPsn_Wp_Proxy_Action::addAdminMenu(array($this, '_load'));
        }
    }

    /**
     * Loads the menu
     */
    public function _load()
    {
        if ($this->isIsMultisite()) {
            IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'before_load_multisite_menu_page', $this);
        } else {
            IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'before_load_menu_page', $this);
        }

        $this->_pageHook = add_menu_page(
            $this->getPageTitle(),
            $this->getMenuTitle(),
            $this->getCapability(),
            $this->getSlug(),
            array($this, 'handle'),
            $this->getIconUrl(),
            $this->getPosition()
        );

        /**
         * @var IfwPsn_Wp_Plugin_Menu_Page_Sub $subPage
         */
        foreach($this->getSubPagesSorted() as $subPage) {

            $this->_triggerAction('before_load_submenu_page');

            if ($subPage->isHidden()) {

                global $_registered_pages;
                $hookname = get_plugin_page_hookname( plugin_basename($subPage->getSlug()), $subPage->getSlug());
                add_action( $hookname, array($subPage, 'handle') );
                $subPage->setPageHook($hookname);
                $_registered_pages[$hookname] = true;

            } else {

                $subPageHook = add_submenu_page(
                    $this->getSlug(),
                    $subPage->getPageTitle(),
                    $subPage->getMenuTitle(),
                    $subPage->getCapability(),
                    $subPage->getSlug(),
                    array($subPage, 'handle')
                );

                $subPage->setPageHook($subPageHook);
            }

            $this->_triggerAction('after_load_submenu_page');

            if ($this->_pm->getAccess()->getPage() == $subPage->getSlug()) {
                $subPage->onLoad();
            }
        }

        $this->_triggerAction('after_load_menu_page');

        if ($this->_pm->getAccess()->getPage() == $this->getSlug()) {
            $this->onLoad();
        }
    }

    /**
     * @param $action
     */
    protected function _triggerAction($action)
    {
        if ($this->isIsMultisite()) {
            $action = strtr($action, array(
                'menu' => 'multisite_menu',
                'submenu' => 'multisite_submenu',
            ));
        }

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, $action, $this);
    }

    /**
     * @param string $callback
     * @return $this
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;
        return $this;
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * Decides which function to call
     */
    public function _callback()
    {
        if ($this->getCallback() instanceof IfwPsn_Wp_Plugin_Application_PageMapperInterface) {
            $this->getCallback()->handlePage($this);
        } elseif (is_callable($this->getCallback())) {
            call_user_func($this->getCallback());
        } else {
            IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'menu_page_callback', $this);
        }
    }

    /**
     * @param mixed $capability
     * @return $this
     */
    public function setCapability($capability)
    {
        $this->_capability = $capability;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapability()
    {
        if (empty($this->_capability)) {
            // set default to an administrator capability
            $this->_capability = 'activate_plugins';
        }
        return $this->_capability;
    }

    /**
     * @param string $iconUrl
     * @return $this
     */
    public function setIconUrl($iconUrl)
    {
        $this->_iconUrl = $iconUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconUrl()
    {
        return $this->_iconUrl;
    }

    /**
     * @param string $menuTitle
     * @return $this
     */
    public function setMenuTitle($menuTitle)
    {
        $this->_menuTitle = $menuTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getMenuTitle()
    {
        return $this->_menuTitle;
    }

    /**
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->_pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        if (empty($this->_pageTitle)) {
            return $this->getMenuTitle();
        }
        return $this->_pageTitle;
    }

    /**
     * @param mixed $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->_position = $position;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * @param mixed $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->_slug = $slug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->_slug;
    }

    /**
     * @return mixed
     */
    public function getPageHook()
    {
        return $this->_pageHook;
    }

    /**
     * @return array
     */
    public function getSubPages()
    {
        return $this->_subPages;
    }

    /**
     * @return array
     */
    public function getSubPagesSorted()
    {
        if ($this->_subPagesSorted === null) {
            $this->_subPagesSorted = array();

            /**
             * @var IfwPsn_Wp_Plugin_Menu_Page_Sub $subPage
             */
            foreach ($this->_subPages as $subPage) {
                $this->_subPagesSorted[] = array(
                    'subpage' => $subPage,
                    'priority' => $subPage->getPriority()
                );
            }

            uasort($this->_subPagesSorted, array($this, 'subPageCompare'));
            $this->_subPagesSorted = array_map(array($this, 'subPageCompareReduce'), $this->_subPagesSorted);
        }

        return $this->_subPagesSorted;
    }

    /**
     * @param $a
     * @param $b
     * @return mixed
     */
    public function subPageCompare($a, $b)
    {
        return $a['priority'] - $b['priority'];
    }

    /**
     * @param $i
     * @return mixed
     */
    public function subPageCompareReduce($i)
    {
        return $i['subpage'];
    }

    /**
     * @var IfwPsn_Wp_Plugin_Menu_Page_Sub $subPage
     * @return $this
     */
    public function registerSubPage(IfwPsn_Wp_Plugin_Menu_Page_Sub $subPage)
    {
        array_push($this->_subPages, $subPage);
        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_register_submenu_page', $subPage);
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsMultisite()
    {
        return $this->_isMultisite;
    }

    /**
     * @param boolean $isMultisite
     */
    public function setIsMultisite($isMultisite)
    {
        if (is_bool($isMultisite)) {
            $this->_isMultisite = $isMultisite;
        }
    }
}
