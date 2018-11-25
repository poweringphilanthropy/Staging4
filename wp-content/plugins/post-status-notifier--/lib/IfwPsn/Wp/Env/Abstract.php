<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Sets the environment variables of a plugin
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 468 2015-10-05 21:16:43Z timoreithde $
 * @package  IfwPsn_Wp_Plugin
 */
abstract class IfwPsn_Wp_Env_Abstract
{
    /**
     * Plugin's root file PHP pathinfo() data
     * @var IfwPsn_Wp_Pathinfo_Abstract
     */
    protected $_pathinfo;
    
    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var string
     */
    protected $_description;

    /**
     * @var string
     */
    protected $_version;

    /**
     * @var string
     */
    protected $_homepage;

    /**
     * @var string
     */
    protected $_author;

    /**
     * @var string
     */    
    protected $_url;

    /**
     * @var string
     */    
    protected $_urlFiles;
    
    /**
     * @var string
     */    
    protected $_urlCss;

    /**
     * @var string
     */    
    protected $_urlJs;

    /**
     * @var string
     */    
    protected $_urlImg;

    /**
     * @var string
     */
    protected $_textDomain;


    /**
     * @param IfwPsn_Wp_Pathinfo_Abstract $pathinfo
     */
    protected function __construct(IfwPsn_Wp_Pathinfo_Abstract $pathinfo)
    {
        $this->_pathinfo = $pathinfo;

        $this->_init();
    }

    /**
     * @return mixed
     */
    abstract protected function _init();
    
    /**
     * @return string the $_name
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @return string the $_description
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @return string the $_url
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @return string the $_urlFiles
     */
    public function getUrlFiles()
    {
        return $this->_urlFiles;
    }

    /**
     * @return string the $_urlCss
     */
    public function getUrlCss()
    {
        return $this->_urlCss;
    }

    /**
     * @return string the $_urlJs
     */
    public function getUrlJs()
    {
        return $this->_urlJs;
    }

    /**
     * @return string the $_urlImg
     */
    public function getUrlImg()
    {
        return $this->_urlImg;
    }

    /**
     * @return string the $_textDomain
     */
    public function getTextDomain()
    {
        return $this->_textDomain;
    }

    /**
     * @return the $_pluginVersion
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->_author;
    }

    /**
     * @param $version
     * @return bool
     */
    public function isVersionGreaterThan($version)
    {
        return version_compare($this->getVersion(), $version) > 0;
    }

    /**
     * @return the $_pluginHomepage
     */
    public function getHomepage()
    {
        return $this->_homepage;
    }

    /**
     * @return int|null
     */
    public function getBuildNumber()
    {
        $buildFile = $this->_pathinfo->getRoot() . 'build.txt';

        if (file_exists($buildFile)) {
            $buildNumber = (int)file_get_contents($buildFile);
        } else {
            $buildNumber = null;
        }

        return $buildNumber;
    }

    /**
     * Returns the result of all getter methods
     * @return string
     */
    public function __toString()
    {
        $output = array();
        $output[] = '<b>Environment variables for plugin '. $this->_pathinfo->getDirname() . '</b>';
        
        $methods = get_class_methods($this);
        sort($methods);
        
        foreach ($methods as $method_name) {
            
            if ($method_name == 'getInstance') {
                continue;
            }
            
            if (strpos($method_name, 'get') === 0) {
                $output[] = '<b>' . $method_name . '():</b> ' . $this->$method_name();
            }
        }
        
        return implode('<br />', $output) . '<br />'; 
    }
}
