<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Activator.php 416 2015-04-19 21:53:46Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Module_Activator
{
    /**
     * @var IfwPsn_Wp_Module_Activator
     */
    protected static $_instance;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var string
     */
    protected $_optionName;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return IfwPsn_Wp_Module_Activator
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($pm);
        }
        return self::$_instance;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        $this->_init();
    }

    protected function _init()
    {
        $this->_optionName = $this->_pm->getAbbrLower() . '-custom-modules';
        // init custom modules activation storage
        $this->_pm->getBootstrap()->getOptionsManager()->registerExternalOption($this->_optionName);
    }

    /**
     * @return array
     */
    protected function _getStorage()
    {
        $storage = $this->_pm->getBootstrap()->getOptionsManager()->getOption($this->_optionName);

        if (empty($storage) && !is_array($storage)) {
            $storage = array();
        }

        return $storage;
    }

    /**
     * @param $module
     * @return bool
     */
    protected function _inStorage($module)
    {
        $storage = $this->_getStorage();
        return in_array($module, $storage);
    }

    /**
     * @param $module
     */
    protected function _addStorage($module)
    {
        $storage = $this->_getStorage();

        array_push($storage, $module);

        $this->_pm->getBootstrap()->getOptionsManager()->updateOption($this->_optionName, $storage);
    }

    /**
     * @param $module
     */
    protected function _removeStorage($module)
    {
        $storage = $this->_getStorage();

        $key = array_search($module, $storage);
        if ($key !== false) {
            unset($storage[$key]);
            $this->_pm->getBootstrap()->getOptionsManager()->updateOption($this->_optionName, $storage);
        }
    }

    /**
     * @param IfwPsn_Wp_Module_Bootstrap_Abstract $module
     */
    public function activate(IfwPsn_Wp_Module_Bootstrap_Abstract $module)
    {
        $moduleId = $module->getId();

        if (!$this->_inStorage($moduleId)) {
            $this->_addStorage($moduleId);
        }
    }

    /**
     * @param IfwPsn_Wp_Module_Bootstrap_Abstract $module
     */
    public function deactivate(IfwPsn_Wp_Module_Bootstrap_Abstract $module)
    {
        $moduleId = $module->getId();

        if ($this->_inStorage($moduleId)) {
            $this->_removeStorage($moduleId);
        }
    }

    /**
     * Resets the storage / deactivates all custom modules
     */
    public function reset()
    {
        $this->_pm->getBootstrap()->getOptionsManager()->updateOption($this->_optionName, array());
    }

    /**
     * @param IfwPsn_Wp_Module_Bootstrap_Abstract $module
     * @return bool
     */
    public function isActivated(IfwPsn_Wp_Module_Bootstrap_Abstract $module)
    {
        // built-in modules are always activated
        return $module->getLocationName() == IfwPsn_Wp_Module_Manager::LOCATION_NAME_BUILTIN || $this->_inStorage($module->getId());
    }
}
 