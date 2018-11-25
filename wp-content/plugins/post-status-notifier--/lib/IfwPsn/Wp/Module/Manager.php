<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Module Manager
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Manager.php 416 2015-04-19 21:53:46Z timoreithde $
 * @package  IfwPsn_Wp_Plugin_Admin
 */
class IfwPsn_Wp_Module_Manager
{
    const LOCATION_NAME_BUILTIN = 'built-in';

    const LOCATION_NAME_CUSTOM = 'custom';

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var array
     */
    protected $_modulesFilenames = array();

    /**
     * @var array
     */
    protected $_modules = array();

    /**
     * @var array
     */
    protected $_loaded = array();

    /**
     * @var array
     */
    protected $_bootstrapPath = array();

    /**
     * @var null|bool
     */
    protected $_hasModules;

    /**
     * @var array
     */
    protected $_locations = array();

    /**
     * @var array
     */
    protected $_nameBuffer = array();

    /**
     * @var array
     */
    protected $_whitelist = array();



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        $this->_initLocations();

        require_once dirname(__FILE__) . '/Activator.php';
        IfwPsn_Wp_Module_Activator::getInstance($this->_pm);
    }

    protected function _initLocations()
    {
        if ($this->_locations == null) {

            if ($this->_pm->getPathinfo()->hasModulesDir()) {
                $this->addLocation('built-in', $this->_pm->getPathinfo()->getRootModules());
            }

            $customLocation = $this->getCustomModulesLocation();
            if (!empty($customLocation) && is_dir($customLocation)) {
                $this->addLocation('custom', $customLocation);
            }
        }
    }

    /**
     * @param $name
     * @param $location
     */
    public function addLocation($name, $location)
    {
        if (!isset($this->_locations[$name])) {
            $this->_locations[$name] = $location;
        }
    }

    /**
     * Retrieves dir to search for custom modules
     * @return null|string
     */
    public function getCustomModulesLocation()
    {
        $uploadDir = IfwPsn_Wp_Proxy_Blog::getUploadDir();
        $dirName = $this->getCustomModulesLocationName();

        if (is_array($uploadDir) &&
            (isset($uploadDir['error']) && empty($uploadDir['error'])) &&
            (isset($uploadDir['basedir']) && !empty($uploadDir['basedir']))
            ) {

            return $uploadDir['basedir'] . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getCustomModulesLocationName()
    {
        return $this->_pm->getPathinfo()->getDirname();
    }

    /**
     * Module loading loop
     */
    public function load()
    {
        $modules = $this->getModules();
        $loop = 0;

        while(!empty($modules) && $loop < 10) {

            /**
             * @var IfwPsn_Wp_Module_Bootstrap_Abstract $module
             */
            foreach ($modules as $id => $module) {

                if (!$module->isActivated()) {
                    // module is not activated, skip
                    unset($modules[$id]);
                    continue;
                }

                $dependencies = $module->getDependencies();

                if (empty($dependencies) || count(array_diff($dependencies, $this->_loaded)) == 0) {
                    try {
                        // init the module's default logic
                        $module->init();
                        // try to bootstrap module's custom logic
                        $module->bootstrap();

                        array_push($this->_loaded, $id);

                        unset($modules[$id]);
                        //$modules = array_values($modules);

                    } catch (IfwPsn_Wp_Model_Exception $e) {
                        $this->_pm->getLogger()->err('Module error: '. $e->getMessage());
                    } catch (Exception $e) {
                        $this->_pm->getLogger()->err('Unexpected exception in module "'. $module->getId() .'":'. $e->getMessage());
                    }
                }
            }

            $loop++;
        }
    }

    /**
     *  Registers the module to front controller
     */
    public function registerModules()
    {
        foreach ($this->getModules() as $module) {
            $module->registerPath();
        }
    }

    /**
     * @return array
     */
    public function getModules()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Pathinfo/Module.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Env/Module.php';

        if (empty($this->_modules) && !empty($this->_locations)) {

            // loop through module locations
            foreach ($this->_locations as $locationName => $location) {

                // get all subdirs
                foreach ($this->_getModulesDirnames($location) as $moduleDir) {
                    try {
                        // check if it is a valid module
                        if ($this->_isValidModule($moduleDir, $location, $locationName)) {
                            // try to instantiate module object
                            $className = $this->_getModuleClassName($moduleDir);


                            $modulePathinfo = new IfwPsn_Wp_Pathinfo_Module($this->_getModuleBootstrapPath($moduleDir, $location));

                            $mod = new $className($modulePathinfo, $locationName, $this->_pm);

                            // init module environment
                            $moduleEnv = IfwPsn_Wp_Env_Module::getInstance($modulePathinfo, $mod, $this->getCustomModulesLocationName());
                            $mod->setEnv($moduleEnv);

                            if (!isset($this->_modules[$mod->getId()])) {
                                $this->_modules[$mod->getId()] = $mod;
                            }
                            //array_push($this->_modules, $mod);
                        } else {
                            $this->_pm->getLogger()->err('Invalid module: '. $moduleDir);
                        }
                    } catch (IfwPsn_Wp_Module_Exception $e) {
                        $this->_pm->getLogger()->err('Module error: '. $e->getMessage());
                    } catch (Exception $e) {
                        $this->_pm->getLogger()->err('Unexpected exception in module "'. $moduleDir .'":'. $e->getMessage());
                    }
                } // end inner foreach
            } // end outer foreach
        }

        return $this->_modules;
    }

    /**
     * Retrieves built-in modules
     * @return array
     */
    public function getBuiltinModules()
    {
        return $this->getModulesByLocationName(self::LOCATION_NAME_BUILTIN);
    }

    /**
     * Retrieves custom modules
     * @return array
     */
    public function getCustomModules()
    {
        return $this->getModulesByLocationName(self::LOCATION_NAME_CUSTOM);
    }

    /**
     * Retrieves modules by location name
     *
     * @param $locationName
     * @return array
     */
    public function getModulesByLocationName($locationName)
    {
        $result = array();

        /**
         * @var IfwPsn_Wp_Module_Bootstrap_Abstract $mod
         */
        foreach ($this->getModules() as $mod) {
            if ($mod->getLocationName() == $locationName) {
                array_push($result, $mod);
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getInitializedModules()
    {
        $result = array();

        /**
         * @var IfwPsn_Wp_Module_Bootstrap_Abstract $module
         */
        foreach ($this->getModules() as $module) {
            if ($module->isInitialized()) {
                array_push($result, $module);
            }
        }

        return $result;
    }

    /**
     * @param $location
     * @return array
     */
    protected function _getModulesDirnames($location)
    {
        $result = array();
        $modulesDir = new DirectoryIterator($location);

        foreach ($modulesDir as $fileinfo) {

            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }

            $filename = $fileinfo->getFilename();

            if (!$this->hasWhitelist() || ($this->hasWhitelist() && $this->isInWhitelist($filename))) {
                array_push($result, $fileinfo->getFilename());
            }
        }

        return $result;
    }

    /**
     * @param $module
     * @param $location
     * @param null $locationName
     * @return bool
     * @throws IfwPsn_Wp_Model_Exception
     * @throws IfwPsn_Wp_Module_Exception
     */
    protected function _isValidModule($module, $location, $locationName = null)
    {
        // check for duplicate names
        if (!in_array($module, $this->_nameBuffer)) {
            array_push($this->_nameBuffer, $module);
        } else {
            $error = 'Module name already exists'. $module;
            if ($locationName == self::LOCATION_NAME_BUILTIN) {
                throw new IfwPsn_Wp_Model_Exception($error);
            } else {
                $this->_pm->getLogger()->err($error);
            }
        }

        // check for bootstrap class
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Module/Bootstrap/Abstract.php';
        $path = $this->_getModuleBootstrapPath($module, $location);

        if (!file_exists($path)) {
            $this->_pm->getLogger()->err('Missing bootstrap class in module: '. $module);
            return false;
        }

        require_once $path;

        if (!class_exists($this->_getModuleClassName($module))) {
            $error = 'Invalid module class found for module "'. $module .'". Expecting: '.
                $this->_getModuleClassName($module);
            if ($locationName == self::LOCATION_NAME_BUILTIN) {
                throw new IfwPsn_Wp_Module_Exception($error);
            } else {
                $this->_pm->getLogger()->err($error);
            }
        }

        return true;
    }

    /**
     * @param $module
     * @param $location
     * @return string
     */
    protected function _getModuleBootstrapPath($module, $location)
    {
        if (!isset($this->_bootstrapPath[$module])) {
            $this->_bootstrapPath[$module] = $location . $module . DIRECTORY_SEPARATOR . 'bootstrap.php';
        }

        return $this->_bootstrapPath[$module];
    }

    /**
     * @param $module
     * @return string
     */
    protected function _getModuleClassName($module)
    {
        return $this->_pm->getAbbr() . '_' . $module . '_Bootstrap';
    }

    /**
     * Retrieve module by ID
     *
     * @param $id
     * @return IfwPsn_Wp_Module_Bootstrap_Abstract|null
     */
    public function getModule($id)
    {
        if (isset($this->_modules[$id])) {
            return $this->_modules[$id];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getWhitelist()
    {
        return $this->_whitelist;
    }

    /**
     * @return array
     */
    public function hasWhitelist()
    {
        return count($this->_whitelist) > 0;
    }

    /**
     * @param array $whitelist
     */
    public function addWhitelist($whitelist)
    {
        array_push($this->_whitelist, $whitelist);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isInWhitelist($value)
    {
        return in_array($value, $this->_whitelist);
    }
}
