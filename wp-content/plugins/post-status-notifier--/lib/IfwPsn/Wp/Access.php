<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Access.php 495 2015-12-06 23:29:52Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Access 
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var null|bool
     */
    protected $_isPlugin;

    /**
     * @var null|bool
     */
    protected $_isPluginPage;

    /**
     * @var null|bool
     */
    protected $_isAjax;

    /**
     * @var null|bool
     */
    protected $_isPluginAjax;

    /**
     * @var
     */
    protected $_requestUri;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        $this->_init();
    }

    /**
     *
     */
    protected function _init()
    {
        $this->_requestUri = $_SERVER['REQUEST_URI'];
    }

    /**
     * Checks if it is a WP admin access
     *
     * @return bool
     */
    public function isAdmin()
    {
        if (!$this->isHeartbeat() && function_exists('is_admin')) {
            return is_admin();
        }
        return false;
    }

    /**
     * Checks if it is a WP network admin access
     *
     * @return bool
     */
    public function isNetworkAdmin()
    {
        if (!$this->isHeartbeat() && function_exists('is_network_admin')) {
            return is_network_admin();
        }
        return false;
    }

    /**
     * Checks if it is an exact access to this plugin's admin pages
     * @return bool
     */
    public function isPlugin()
    {
        if ($this->_isPlugin === null) {

            if ($this->isPluginPage() || $this->isPluginAjax()) {
                $this->_isPlugin = true;
            } else {
                $this->_isPlugin = false;
            }
        }

        return $this->_isPlugin;
    }

    /**
     * Checks if it is an ajax request
     *
     * @return bool
     */
    public function isAjax()
    {
        if ($this->_isAjax === null) {

            $requestInfo = pathinfo($this->_requestUri);
            if ($requestInfo['filename'] == 'admin-ajax') {
                $this->_isAjax = true;
            } else {
                $this->_isAjax  = false;
            }
        }

        return $this->_isAjax;
    }

    /**
     * Checks if it is an ajax request of the plugin
     *
     * @return bool|null
     */
    public function isPluginAjax()
    {
        if ($this->_isPluginAjax === null) {

            if ($this->isAjax() &&
                isset($_REQUEST['action']) &&
                strpos($_REQUEST['action'], 'load-'. $this->_pm->getAbbrLower()) === 0) {

                $this->_isPluginAjax = true;
            } else {
                $this->_isPluginAjax = false;
            }
        }

        return $this->_isPluginAjax;
    }

    /**
     * Is access to a plugin admin page
     *
     * @return bool|null
     */
    public function isPluginPage()
    {
        if ($this->_isPluginPage === null) {

            if (isset($_GET['page']) &&
                (strpos($_GET['page'], $this->_pm->getPathinfo()->getDirname()) !== false ||
                    strpos($_GET['page'], $this->_pm->getAbbrLower()) !== false)) {

                $this->_isPluginPage = true;
            } else {
                $this->_isPluginPage = false;
            }
        }

        return $this->_isPluginPage;
    }

    /**
     * Checks for dashboard access
     * @return bool
     */
    public function isDashboard()
    {
        $requestInfo = pathinfo($this->_requestUri);

        if ($this->isAdmin() && $requestInfo['filename'] == 'index') {
            return true;
        }
        return false;
    }

    /**
     * Checks for widget menu access
     * @return bool
     */
    public function isWidgetAdmin()
    {
        $requestInfo = pathinfo($this->_requestUri);

        if ($this->isAdmin() && $requestInfo['filename'] == 'widgets') {
            return true;
        }
        return false;
    }

    /**
     * Checks for post-new access
     * @return bool
     */
    public function isPostNew()
    {
        $requestInfo = pathinfo($this->_requestUri);

        if ($this->isAdmin() && $requestInfo['filename'] == 'post-new') {
            return true;
        }
        return false;
    }

    /**
     * Checks for post-edit access
     * @return bool
     */
    public function isPostEdit()
    {
        $requestInfo = pathinfo($this->_requestUri);

        if ($this->isAdmin() && $requestInfo['filename'] == 'post') {
            return true;
        }
        return false;
    }

    /**
     * Checks for admin edit page access
     * @return bool
     */
    public function isAdminEdit()
    {
        $requestInfo = pathinfo($this->_requestUri);

        if ($this->isAdmin() && $requestInfo['filename'] == 'edit') {
            return true;
        }
        return false;
    }

    /**
     * Checks for plugins administration page access
     * @return bool
     */
    public function isPluginsAdmin()
    {
        $requestInfo = pathinfo($this->_requestUri);

        if ($this->isAdmin() && $requestInfo['filename'] == 'plugins') {
            return true;
        }
        return false;
    }

    /**
     * @param $page
     * @return bool
     */
    public function isPage($page)
    {
        return isset($_GET['page']) && $_GET['page'] == $page;
    }

    /**
     * @param $mod
     * @return bool
     */
    public function isModule($mod)
    {
        return isset($_GET['mod']) && $_GET['mod'] == $mod;
    }

    /**
     * @return bool
     */
    public function getPage()
    {
        return isset($_GET['page']) ? $_GET['page'] : null;
    }

    /**
     * @param $postType
     * @return bool
     */
    public function isPostType($postType)
    {
        return isset($_GET['post_type']) && $_GET['post_type'] == $postType;
    }

    /**
     * Checks if request comes from internal heartbeat action
     *
     * @return bool
     */
    public function isHeartbeat()
    {
        return isset($_POST['action']) && $_POST['action'] == 'heartbeat';
    }

    /**
     * @param $action
     * @return bool
     */
    public function hasAction($action)
    {
        return isset($_REQUEST['action']) && $_REQUEST['action'] == $action;
    }

    /**
     * Checks if the request action contains plugin abbrevion
     * @return bool
     */
    public function hasPluginAbbrAction()
    {
        return isset($_REQUEST['action']) && (
            strpos(strtolower($_REQUEST['action']), '-'. $this->_pm->getAbbrLower()) !== false ||
            strpos(strtolower($_REQUEST['action']), $this->_pm->getAbbrLower() . '-') !== false
        );
    }

    /**
     * @return string|null
     */
    public function getController()
    {
        $key = $this->_pm->getConfig()->application->controller->key;
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        $key = $this->_pm->getConfig()->application->action->key;
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * @return string|null
     */
    public function getModule()
    {
        $key = 'module';
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * @return boolean
     */
    public function isDeactivation()
    {
        $requestInfo = parse_url($this->_requestUri);

        if (strstr($requestInfo['path'], 'plugins.php') !== false && $_GET['action'] == 'deactivate' && $_GET['plugin'] == $this->_pm->getSlugFilenamePath()) {
            return true;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function isActivation()
    {
        $requestInfo = parse_url($this->_requestUri);

        if (strstr($requestInfo['path'], 'plugins.php') !== false &&
            isset($_GET['action']) && $_GET['action'] == 'activate' &&
            isset($_GET['plugin']) && $_GET['plugin'] == $this->_pm->getSlugFilenamePath()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isOptionsSubmit()
    {
        return isset($_POST) && isset($_POST['option_page']) && $_POST['option_page'] == $this->_pm->getAbbrLower() . '_options';
    }

}
