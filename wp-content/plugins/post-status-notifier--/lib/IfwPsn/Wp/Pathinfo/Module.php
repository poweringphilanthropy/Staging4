<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Module.php 247 2014-04-05 12:31:15Z timoreithde $
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Pathinfo_Module extends IfwPsn_Wp_Pathinfo_Abstract
{
    /**
     * @var string
     */
    protected $_metaDataPath;



    /**
     * Init the pathinfo properties
     */
    protected function _init()
    {
        $this->_pathinfo = pathinfo($this->_path);

        // filename must be identical to dirname for convenience
        // (eg when symlinking pathinfo[dirname] will not be the production name)
        $dirnameParts = explode(DIRECTORY_SEPARATOR, $this->_pathinfo['dirname']);
        $this->_dirname = array_pop($dirnameParts);

        $this->_dirnamePath = $this->_pathinfo['dirname'] . DIRECTORY_SEPARATOR;

        // filename plus extension
        $this->_basename = $this->_pathinfo['basename'];
        // filename without extension
        $this->_filename = $this->_pathinfo['filename'];

        $this->_extension = $this->_pathinfo['extension'];

        $this->_root = $this->_dirnamePath;

        $this->_rootFiles = $this->_root . 'files' . DIRECTORY_SEPARATOR;
        $this->_rootCss = $this->_rootFiles . 'css' . DIRECTORY_SEPARATOR;
        $this->_rootImg = $this->_rootFiles . 'img' . DIRECTORY_SEPARATOR;
        $this->_rootJs = $this->_rootFiles . 'js' . DIRECTORY_SEPARATOR;
        $this->_rootTpl = $this->_rootFiles . 'tpl' . DIRECTORY_SEPARATOR;

        $this->_rootLib = $this->_root . 'lib' . DIRECTORY_SEPARATOR;
        $this->_rootLang = $this->_root . 'lang' . DIRECTORY_SEPARATOR;
        $this->_langRelPath = '/modules/' . $this->_dirname . '/lang';

        $this->_metaDataPath = $this->_root . 'module.xml';
    }

    /**
     * @return string
     */
    public function getMetaDataPath()
    {
        return $this->_metaDataPath;
    }

}
