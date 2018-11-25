<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Pathinfo abstraction
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Plugin.php 237 2014-03-21 01:18:02Z timoreithde $
 * @package  IfwPsn_Wp_Plugin
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Pathinfo_Plugin extends IfwPsn_Wp_Pathinfo_Abstract
{
    /**
     * @var string
     */
    protected $_rootAdmin;
    
    /**
     * @var string
     */
    protected $_rootAdminMenu;
    
    /**
     * @var string
     */
    protected $_rootAdminCss;
    
    /**
     * @var string
     */
    protected $_rootAdminJs;

    /**
     * @var string
     */
    protected $_rootModules;
    
    /**
     * @var string
     */
    protected $_rootSkin;
    
    /**
     * @var string
     */
    protected $_filenamePath;

    /**
     * @var
     */
    protected $_hasModulesDir;


    
    /**
     * Init the pathinfo properties
     */
    protected function _init()
    {
        $this->_pathinfo = pathinfo($this->_path);
        
        // filename must be identical to dirname for convenience
        // (eg when symlinking pathinfo[dirname] will not be the production name)
        $this->_dirname = $this->_pathinfo['filename'];
        
        $this->_dirnamePath = $this->_pathinfo['dirname'] . DIRECTORY_SEPARATOR;
        
        // filename plus extension
        $this->_basename = $this->_pathinfo['basename'];
        // filename without extension
        $this->_filename = $this->_pathinfo['filename'];
        
        $this->_extension = $this->_pathinfo['extension'];
        
        
        if (defined('WP_PLUGIN_DIR')) {
            $this->_root = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->_dirname . DIRECTORY_SEPARATOR;
        } else {
            // for script usage
            $this->_root = $this->_dirnamePath;
        }
        
        $this->_rootFiles = $this->_root . 'files' . DIRECTORY_SEPARATOR;
        $this->_rootCss = $this->_rootFiles . 'css' . DIRECTORY_SEPARATOR;
        $this->_rootImg = $this->_rootFiles . 'img' . DIRECTORY_SEPARATOR;
        $this->_rootJs = $this->_rootFiles . 'js' . DIRECTORY_SEPARATOR;
        $this->_rootTpl = $this->_rootFiles . 'tpl' . DIRECTORY_SEPARATOR;
        
        $this->_rootLib = $this->_root . 'lib' . DIRECTORY_SEPARATOR;
        $this->_rootLang = $this->_root . 'lang' . DIRECTORY_SEPARATOR;
        $this->_rootModules = $this->_root . 'modules' . DIRECTORY_SEPARATOR;
        $this->_rootSkin = $this->_root . 'skin' . DIRECTORY_SEPARATOR;
        
        $this->_filenamePath = $this->_dirname . '/' . $this->_basename;
        
        $this->_rootAdmin = $this->_root . 'admin' . DIRECTORY_SEPARATOR;
        $this->_rootAdminMenu = $this->_rootAdmin . 'menu' . DIRECTORY_SEPARATOR;
        $this->_rootAdminCss = $this->_rootAdmin . 'css' . DIRECTORY_SEPARATOR;
        $this->_rootAdminJs = $this->_rootAdmin . 'js' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string $_rootAdmin 
     */
    public function getRootAdmin()
    {
        return $this->_rootAdmin;
    }

    /**
     * @return string $_rootAdmin
     */
    public function getRootApplication()
    {
        return $this->_rootAdmin;
    }

    /**
     * @return bool
     */
    public function hasRootApplication()
    {
        return is_dir($this->getRootApplication()) === true;
    }

    /**
     * @return string $_rootAdminMenu 
     */
    public function getRootAdminMenu()
    {
        return $this->_rootAdminMenu;
    }

    /**
     * @return string $_rootAdminCss 
     */
    public function getRootAdminCss()
    {
        return $this->_rootAdminCss;
    }

    /**
     * @return string $_rootAdminJs 
     */
    public function getRootAdminJs()
    {
        return $this->_rootAdminJs;
    }

    /**
     * @return string $_libDir
     */
    public function getRootModules()
    {
        return $this->_rootModules;
    }

    /**
     * @return bool
     */
    public function hasModulesDir()
    {
        if ($this->_hasModulesDir === null) {
            $this->_hasModulesDir = is_dir($this->getRootModules());
        }
        return $this->_hasModulesDir;
    }

    /**
     * @return string $_rootSkin
     */
    public function getRootSkin()
    {
        return $this->_rootSkin;
    }

    /**
     * The filename of the plugin including the path (my-great-plugin/my-great-plugin.php)
     * 
     * @return string $_filenamePath 
     */
    public function getFilenamePath()
    {
        return $this->_filenamePath;
    }

    /**
     * Returns the result of all getter methods
     * @return string
     */
    public function __toString()
    {
        $output = array();
        if ($this->_logger instanceof IfwPsn_Wp_Plugin_Logger) {
            $this->_logger->info('Start pathinfo vars for plugin '. $this->getDirName());
        } else {
            $output[] = '<b>Environment variables for plugin '. $this->getDirName() . '</b>';
        }

        $methods = get_class_methods($this);
        sort($methods);

        foreach ($methods as $method_name) {

            if ($method_name == 'getInstance') {
                continue;
            }

            if (strpos($method_name, 'get') === 0) {
                if ($this->_logger instanceof IfwPsn_Wp_Plugin_Logger) {
                    $this->_logger->info($method_name . '(): ' . $this->$method_name());
                } else {
                    $output[] = '<b>' . $method_name . '():</b> ' . $this->$method_name();
                }
            }
        }

        if ($this->_logger instanceof IfwPsn_Wp_Plugin_Logger) {
            $this->_logger->info('End pathinfo vars');
            $this->_logger->info('');
        } else {
            return implode('<br />', $output) . '<br />';
        }
    }
}