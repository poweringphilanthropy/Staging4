<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Abstract Bootstrap
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 456 2015-08-21 21:29:41Z timoreithde $
 */
require_once dirname(__FILE__) . '/Interface.php';

abstract class IfwPsn_Wp_Plugin_Bootstrap_Abstract implements IfwPsn_Wp_Plugin_Bootstrap_Interface
{
    const OBSERVER_PRE_BOOTSTRAP = 'pre_bootstrap';
    const OBSERVER_POST_MODULES = 'post_modules';
    const OBSERVER_POST_BOOTSTRAP = 'post_bootstrap';
    const OBSERVER_SHUTDOWN_BOOTSTRAP = 'shutdown_bootstrap';

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var array
     */
    protected $_observers = array();

    /**
     * @var IfwPsn_Wp_Module_Manager
     */
    protected $_moduleManager;

    /**
     * @var IfwPsn_Wp_Plugin_Application
     */
    protected $_application;

    /**
     * @var bool
     */
    private $_wasRun = false;



    /**
     * 
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        $this->_attachBuiltinObservers();
    }

    /**
     * Factorys the plugin bootstrap class
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm the plugin manager
     * @throws IfwPsn_Wp_Plugin_Exception
     * @return IfwPsn_Wp_Plugin_Bootstrap_Abstract
     */
    public static function factory(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $bootstrapClass = $pm->getAbbr() . '_Bootstrap';
        $bootstrapFile = $pm->getPathinfo()->getRoot() . 'bootstrap.php';

        if ((require_once $bootstrapFile) == false) {
            throw new IfwPsn_Wp_Plugin_Exception('Bootstrap class '. $bootstrapClass.' not found');
        }

        $bootstrap = new $bootstrapClass($pm);

        if (!($bootstrap instanceof IfwPsn_Wp_Plugin_Bootstrap_Abstract)) {
            throw new IfwPsn_Wp_Plugin_Exception('Bootstrap class '. $bootstrapClass.' must extend IfwPsn_Wp_Plugin_Bootstrap_Abstract');
        }

        return $bootstrap;
    }

    /**
     * Attaches the built-in observers
     */
    private function _attachBuiltinObservers()
    {
        require_once dirname(__FILE__) . '/Observer/Dependencies.php';
        require_once dirname(__FILE__) . '/Observer/Translation.php';
        require_once dirname(__FILE__) . '/Observer/Ajax.php';
        require_once dirname(__FILE__) . '/Observer/Installer.php';
        require_once dirname(__FILE__) . '/Observer/Options.php';
        require_once dirname(__FILE__) . '/Observer/OptionsManager.php';
        require_once dirname(__FILE__) . '/Observer/UpdateManager.php';
        require_once dirname(__FILE__) . '/Observer/Selftester.php';
        require_once dirname(__FILE__) . '/Observer/Logger.php';

        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Dependencies());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Translation());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Ajax());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Installer());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Options());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_OptionsManager());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_UpdateManager());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Selftester());
        $this->addObserver(new IfwPsn_Wp_Plugin_Bootstrap_Observer_Logger());

        // call a custom _attachObservers method
        if (method_exists($this, '_attachObservers')) {
            $this->_attachObservers();
        }
    }

    /**
     * @param IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface $observer
     */
    public function addObserver(IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface $observer)
    {
        if (!isset($this->_observers[$observer->getId()])) {
            $this->_observers[$observer->getId()] = $observer;
        }
    }

    /**
     * @return array
     */
    public function getObservers()
    {
        return $this->_observers;
    }

    /**
     * Handles the plugin bootstrap sequence
     */
    public function run()
    {
        if ($this->_wasRun) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Bootstrap was already run. Exiting.');
        }

        // Pre bootstrap
        $this->_preBootstrap();

        $this->_moduleBootstrap();

        $this->_applicationBootstrap();

        // Run the plugin bootstrap
        $this->bootstrap();

        // Post bootstrap
        $this->_postBootstrap();

        $this->_shutdownBootstrap();

        $this->_wasRun = true;
    }

    /**
     * Runs before the plugin bootstrap
     */
    private function _preBootstrap()
    {
        $this->_notifyObservers(self::OBSERVER_PRE_BOOTSTRAP);

        // trigger action before_bootstrap
        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_before_bootstrap', $this);
    }

    /**
     * Loads the modules after preBootstrap objects are initialized
     */
    private function _moduleBootstrap()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Module/Manager.php';

        $this->_moduleManager = new IfwPsn_Wp_Module_Manager($this->_pm);
        IfwPsn_Wp_Proxy_Action::doPlugin($this->_pm, 'before_modules_load', $this->_moduleManager);

        $pluginConfig = $this->_pm->getConfig()->plugin;
        if (!isset($pluginConfig->simulateLiteVersion) || empty($pluginConfig->simulateLiteVersion)) {
            $this->_moduleManager->load();
        }

        $this->_notifyObservers(self::OBSERVER_POST_MODULES);

        // register module controller path before controller init
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'before_controller_init', array($this->_moduleManager, 'registerModules'));
    }

    private function _applicationBootstrap()
    {
        if ($this->_pm->getAccess()->isPlugin() &&
            $this->_pm->getPathinfo()->hasRootApplication() &&
            !$this->_pm->getAccess()->isAjax()) {

            // it's an access to the plugin settings and no AJAX request
            // start the admin application
            ifw_raise_memory_limit();
            require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Plugin/Application.php';

            $this->_application = IfwPsn_Wp_Plugin_Application::factory($this->_pm);
            $this->_application->load();
        }
    }

    /**
     * Runs after the plugin bootstrap
     */
    private function _postBootstrap()
    {
        // trigger action after_bootstrap
        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_after_bootstrap', $this);

        $this->_notifyObservers(self::OBSERVER_POST_BOOTSTRAP);
    }

    /**
     * Final bootstrap action
     */
    private function _shutdownBootstrap()
    {
        $this->_notifyObservers(self::OBSERVER_SHUTDOWN_BOOTSTRAP);
    }

    /**
     * @param $notificationType
     */
    private function _notifyObservers($notificationType)
    {
        foreach($this->_observers as $observer) {
            $observer->notify($notificationType, $this);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getResource($id)
    {
        if (isset($this->_observers[$id])) {
            return $this->_observers[$id]->getResource();
        }
        return null;
    }

    /**
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     * @return IfwPsn_Wp_Widget_Manager
     */
    public function getWidgetManager()
    {
        if (!($this->_observers['widgets'] instanceof IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface)) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Invalid observer');
        }
        return $this->_observers['widgets']->getResource();
    }

    /**
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     * @return \IfwPsn_Wp_Options
     */
    public function getOptions()
    {
        if (!($this->_observers['options'] instanceof IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface)) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Invalid options observer');
        }
        return $this->_observers['options']->getResource();
    }

    /**
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     * @return \IfwPsn_Wp_Options_Manager
     */
    public function getOptionsManager()
    {
        if (!($this->_observers['options_manager'] instanceof IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface)) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Invalid options_manager observer');
        }
        return $this->_observers['options_manager']->getResource();
    }

    /**
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     * @return \IfwPsn_Wp_Plugin_Installer
     */
    public function getInstaller()
    {
        if (!($this->_observers['installer'] instanceof IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface)) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Invalid installer observer');
        }
        return $this->_observers['installer']->getResource();
    }

    /**
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     * @return \IfwPsn_Wp_Plugin_Selftester
     */
    public function getSelftester()
    {
        if (!($this->_observers['selftester'] instanceof IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface)) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Invalid selftester observer');
        }
        return $this->getResource('selftester');
    }

    /**
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     * @return \IfwPsn_Wp_Plugin_Update_Manager
     */
    public function getUpdateManager()
    {
        if (!($this->_observers['update_manager'] instanceof IfwPsn_Wp_Plugin_Bootstrap_Observer_Interface)) {
            throw new IfwPsn_Wp_Plugin_Bootstrap_Exception('Invalid update_manager observer');
        }
        return $this->_observers['update_manager']->getResource();
    }

    /**
     * @return \IfwPsn_Wp_Module_Manager
     */
    public function getModuleManager()
    {
        return $this->_moduleManager;
    }

    /**
     * @return \IfwPsn_Wp_Plugin_Application
     */
    public function getApplication()
    {
        return $this->_application;
    }

    /**
     * @return \IfwPsn_Wp_Plugin_Manager
     */
    public function getPluginManager()
    {
        return $this->_pm;
    }
}
