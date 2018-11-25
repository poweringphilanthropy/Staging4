<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Options.php 233 2014-03-17 23:46:37Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Interface.php';

abstract class IfwPsn_Wp_Plugin_Menu_Page_Options implements IfwPsn_Wp_Plugin_Menu_Page_Interface
{
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

    protected $_capability;

    protected $_slug;

    protected $_callback = '';

    protected $_pageHook;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    public function init()
    {
        IfwPsn_Wp_Proxy_Action::addAdminMenu(array($this, '_load'));

        if ($this->_pm->getAccess()->getPage() == $this->getSlug()) {
            IfwPsn_Wp_Proxy_Action::addInit(array($this, 'onInit'));
        }
    }

    /**
     * Loads the menu
     */
    public function _load()
    {
        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'before_load_options_page', $this);

        $this->_pageHook = add_options_page(
            $this->getPageTitle(),
            $this->getMenuTitle(),
            $this->getCapability(),
            $this->getSlug(),
            array($this, 'handle')
        );

        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'after_load_options_page', $this);
        if ($this->_pm->getAccess()->getPage() == $this->getSlug()) {
            $this->onLoad();
        }
    }

    public function onInit()
    {}

    public function onLoad()
    {}

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
     * @return mixed
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
            IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'options_page_callback', $this);
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

}
