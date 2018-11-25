<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Pathinfo abstraction
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 237 2014-03-21 01:18:02Z timoreithde $
 * @package  IfwPsn_Wp_Plugin
 */
abstract class IfwPsn_Wp_Pathinfo_Abstract
{
    /**
     * @var string
     */
    protected $_path;
    
    /**
     * @var array
     */
    protected $_pathinfo;
    
    /**
     * @var string
     */
    protected $_dirname;
    
    /**
     * @var string
     */
    protected $_dirnamePath;
    
    /**
     * @var string
     */
    protected $_basename;
    
    /**
     * @var string
     */
    protected $_extension;
    
    /**
     * @var string
     */
    protected $_filename;
    
    /**
     * @var string
     */
    protected $_root;
    
    /**
     * @var string
     */
    protected $_rootFiles;
    
    /**
     * @var string
     */
    protected $_rootCss;
    
    /**
     * @var string
     */
    protected $_rootImg;

    /**
     * @var string
     */
    protected $_rootJs;
    
    /**
     * @var string
     */
    protected $_rootTpl;

    /**
     * @var string
     */
    protected $_rootLib;

    /**
     * @var string
     */
    protected $_rootLang;

    /**
     * @var IfwPsn_Wp_Plugin_Logger
     */
    protected $_logger;


    
    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
        $this->_init();
    }
    
    /**
     * Init the pathinfo properties
     */
    abstract protected function _init();

    /**
     * @return string $_path 
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @return array $_pathinfo 
     */
    public function getPathinfo()
    {
        return $this->_pathinfo;
    }

    /**
     * @return string $_dirname 
     */
    public function getDirname()
    {
        return $this->_dirname;
    }

    /**
     * @return string $_dirnamePath 
     */
    public function getDirnamePath()
    {
        return $this->_dirnamePath;
    }

    /**
     * Root filename with extension
     * 
     * @return string $_basename 
     */
    public function getBasename()
    {
        return $this->_basename;
    }

    /**
     * @return string $_extension 
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * Root filename without extension
     * 
     * @return string $_filename 
     */
    public function getFilename()
    {
        return $this->_filename;
    }
    
    /**
     * @return string the $_root
     */
    public function getRoot()
    {
        return $this->_root;
    }
    
    /**
     * @return string $_rootFiles
     */
    public function getRootFiles()
    {
        return $this->_rootFiles;
    }
    
    /**
     * @return string $_rootCss
     */
    public function getRootCss()
    {
        return $this->_rootCss;
    }
    
    /**
     * @return string $_rootImg
     */
    public function getRootImg()
    {
        return $this->_rootImg;
    }
    
    /**
     * @return string $_rootJs
     */
    public function getRootJs()
    {
        return $this->_rootJs;
    }
    
    /**
     * @return string $_rootTpl 
     */
    public function getRootTpl()
    {
        return $this->_rootTpl;
    }

    /**
     * @return bool
     */
    public function hasRootTpl()
    {
        return is_dir($this->_rootTpl);
    }

    /**
     * @return string $_libDir 
     */
    public function getRootLib()
    {
        return $this->_rootLib;
    }

    /**
     * @return bool
     */
    public function hasRootLib()
    {
        return is_dir($this->_rootLib);
    }

    /**
     * Checks if a class file exists in lib dir
     *
     * @param string $className eg "My_Lib_Class_Name"
     * @return bool
     */
    public function isClassInLib($className)
    {
        $result = false;

        if (is_string($className)) {
            $path = $this->getRootLib() . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            $result =  file_exists($path);
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getRootLang()
    {
        return $this->_rootLang;
    }

    /**
     * @return bool
     */
    public function hasRootLang()
    {
        return is_dir($this->_rootLang);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Logger $_logger
     */
    public function setLogger($_logger)
    {
        $this->_logger = $_logger;
    }

}