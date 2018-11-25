<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Directory scanner
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Scanner.php 153 2013-05-26 11:16:39Z timoreithde $
 */ 
class IfwPsn_Util_Directory_Scanner
{
    /**
     * @var string
     */
    protected $_dir;



    /**
     * @param string $dir pathname of dir to scan
     * @throws Exception
     */
    public function __construct($dir)
    {
        if (!is_dir(($dir))) {
            throw new Exception('Invalid dir: '. $dir);
        }
        $this->_dir = $dir;
    }

    /**
     * @param mixed $interface array or string with interface name(s)
     * @param bool $includeAbstracts
     * @return \IfwPsn_Util_Directory_Scanner_Result
     */
    public function getClassesImplementingInterface($interface, $includeAbstracts = false)
    {
        if (!is_array($interface)) {
            $interface = array($interface);
        }

        $result = array();

        foreach($this->_getDirContents() as $fileinfo) {
            $classname = $fileinfo->getClassname();

            if($classname && $this->_implements($classname, $interface)) {

                $r = new ReflectionClass($classname);
                if (!$r->isAbstract() ||
                    ($r->isAbstract() && $includeAbstracts == true)) {
                    array_push($result, $fileinfo);
                }
            }
        }

        return new IfwPsn_Util_Directory_Scanner_Result($result);
    }

    /**
     * @param $parentClass
     * @return IfwPsn_Util_Directory_Scanner_Result
     */
    public function getClassesExtending($parentClass)
    {
        $result = array();

        foreach($this->_getDirContents() as $fileinfo) {
            $classname = $fileinfo->getClassname();

            if($classname && get_parent_class($classname) == $parentClass) {
                array_push($result, $fileinfo);
            }

        }

        return new IfwPsn_Util_Directory_Scanner_Result($result);
    }

    /**
     * Returns an array of DirectoryIterator objects
     * @return array
     */
    protected function _getDirContents()
    {
        $contents = array();
        $dir = new IfwPsn_Util_Directory_Iterator($this->_dir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            array_push($contents, clone $fileinfo);
        }

        return $contents;
    }

    /**
     * @param $class
     * @param $interface
     * @return bool
     */
    protected function _implements($class, $interface)
    {
        return count(array_diff($interface, class_implements($class))) === 0;
    }
}
