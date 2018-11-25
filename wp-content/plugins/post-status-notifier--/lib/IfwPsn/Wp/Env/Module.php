<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Module.php 335 2014-09-13 21:55:50Z timoreithde $
 */
require_once dirname(__FILE__) . '/Abstract.php';
require_once dirname(__FILE__) . '/Exception.php';

class IfwPsn_Wp_Env_Module extends IfwPsn_Wp_Env_Abstract
{
    /**
     * @var IfwPsn_Wp_Pathinfo_Module
     */
    protected $_pathinfo;

    /**
     * @var string
     */
    protected $_id;

    /**
     * @var array
     */
    protected $_dependencies = array();

    /**
     * @var IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    protected $_module;

    /**
     * @var string
     */
    protected $_customLocationName;

    /**
     * Instance store
     * @var array
     */
    public static $_instances = array();


    /**
     * Retrieves singleton IfwPsn_Wp_Plugin_Config object
     *
     * @param IfwPsn_Wp_Pathinfo_Module $pathinfo
     * @param IfwPsn_Wp_Module_Bootstrap_Abstract $module
     * @param $customLocationName
     * @return IfwPsn_Wp_Plugin_Config
     */
    public static function getInstance(IfwPsn_Wp_Pathinfo_Module $pathinfo, IfwPsn_Wp_Module_Bootstrap_Abstract $module,
                                       $customLocationName)
    {
        $instanceToken = $pathinfo->getDirnamePath();

        if (!isset(self::$_instances[$instanceToken])) {
            self::$_instances[$instanceToken] = new self($pathinfo, $module, $customLocationName);
        }
        return self::$_instances[$instanceToken];
    }

    /**
     * @param IfwPsn_Wp_Pathinfo_Abstract $pathinfo
     * @param IfwPsn_Wp_Module_Bootstrap_Abstract $module
     * @param $customLocationName
     */
    protected function __construct(IfwPsn_Wp_Pathinfo_Abstract $pathinfo, IfwPsn_Wp_Module_Bootstrap_Abstract $module, $customLocationName)
    {
        $this->_module = $module;
        $this->_customLocationName = $customLocationName;

        parent::__construct($pathinfo);
    }

    /**
     * @return mixed
     */
    protected function _init()
    {
        if ($this->_module->isCustomModule()) {

            $uploadDir = IfwPsn_Wp_Proxy_Blog::getUploadDir();

            $this->_url = $uploadDir['baseurl'] .'/'. $this->_customLocationName .'/'. $this->_pathinfo->getDirname() . '/';

        } else {
            // built-in module
            $dirnamePathParts = array_reverse(explode(DIRECTORY_SEPARATOR, $this->_pathinfo->getDirnamePath()));

            $this->_url = plugins_url($dirnamePathParts[3]) . '/modules/' . $this->_pathinfo->getDirname() . '/';
        }

        $this->_urlFiles = $this->_url . 'files/';
        $this->_urlCss = $this->_urlFiles . 'css/';
        $this->_urlJs = $this->_urlFiles . 'js/';
        $this->_urlImg = $this->_urlFiles . 'img/';

        $this->_version = $this->_module->getVersion();
        $this->_name = $this->_module->getName();
        $this->_description = $this->_module->getDescription();
        $this->_textDomain = $this->_module->getTextDomain();
        $this->_homepage = $this->_module->getHomepage();
    }

    /**
     * Retrieves the module's id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->_dependencies;
    }

}
