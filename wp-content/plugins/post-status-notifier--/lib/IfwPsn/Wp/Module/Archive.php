<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Archive.php 307 2014-07-30 22:08:10Z timoreithde $
 * @package   IfwPsn_Wp_Module
 */ 
class IfwPsn_Wp_Module_Archive 
{
    /**
     * @var array
     */
    protected $_fileinfo;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var null|string
     */
    protected $_dirname;

    /**
     * @var ZipArchive
     */
    protected $_zip;



    /**
     * @param array $fileinfo
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @throws IfwPsn_Wp_Module_Exception
     */
    public function __construct(array $fileinfo, IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (!class_exists('ZipArchive')) {
            throw new IfwPsn_Wp_Module_Exception('Missing Zip support.');
        }
        if (empty($fileinfo)) {
            throw new IfwPsn_Wp_Module_Exception('Empty fileinfo.');
        }

        $this->_zip = new ZipArchive();

        if (!$this->_zip->open($fileinfo['file'])) {
            throw new IfwPsn_Wp_Module_Exception('Could not open archive.');
        }

        $this->_fileinfo = $fileinfo;
        $this->_pm = $pm;
    }

    /**
     * @return bool
     * @throws IfwPsn_Wp_Module_Exception
     */
    public function isValid()
    {
        if (!$this->hasBootstrap()) {
            throw new IfwPsn_Wp_Module_Exception('Bootstrap file not found.');
        }

        $expectedClassname = $this->getDirname() . '_Bootstrap';
        $bootstrap = $this->getBootstrap();

        if (!in_array($expectedClassname, $this->_getClasses($bootstrap))) {
            throw new IfwPsn_Wp_Module_Exception('Bootstrap class is not valid. Expected: ' . $expectedClassname);
        }

        if (strstr(get_class($this), 'IfwPsn_') === false) {
            $classHeaderFormat = 'class %s extends Ifw'. $this->_pm->getAbbr() .'_Wp_Module_Bootstrap_Abstract';
        } else {
            $classHeaderFormat = 'class %s extends IfwPsn_Wp_Module_Bootstrap_Abstract';
        }

        $expectedClassHeader = sprintf($classHeaderFormat, $expectedClassname);

        if (strstr($bootstrap, $expectedClassHeader) === false) {
            throw new IfwPsn_Wp_Module_Exception('Bootstrap class header is not valid. Expected: "' . $expectedClassHeader . '"');
        }

        return true;
    }

    /**
     * @param $destination
     * @throws IfwPsn_Wp_Module_Exception
     * @return bool
     */
    public function extractTo($destination)
    {
        if (!$this->_zip->extractTo($destination)) {
            throw new IfwPsn_Wp_Module_Exception('Could not extract archive.');
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getDirname()
    {
        if ($this->_dirname === null) {

            $firstEntry = $this->_zip->getNameIndex(0);

            $isDir = substr($firstEntry, -1) == DIRECTORY_SEPARATOR;

            if ($isDir) {
                $this->_dirname = str_replace(DIRECTORY_SEPARATOR, '', $firstEntry);
            }
        }

        return $this->_dirname;
    }

    /**
     * @return bool
     */
    public function hasBootstrap()
    {
        $dirname = $this->getDirname();

        if ($dirname) {
            return $this->_getBootstrapIndex() != false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getBootstrap()
    {
        if ($this->hasBootstrap()) {
            return $this->_zip->getFromIndex($this->_getBootstrapIndex());
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function _getBootstrapIndex()
    {
        return $this->_zip->locateName($this->getDirname() . DIRECTORY_SEPARATOR . 'bootstrap.php');
    }

    public function close()
    {
        $this->_zip->close();
    }

    /**
     * @param $php_code
     * @return array
     */
    protected function _getClasses($php_code)
    {
        $classes = array();

        $tokens = token_get_all($php_code);
        $count = count($tokens);

        for ($i = 2; $i < $count; $i++) {
            if (   $tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes;
    }
}
 