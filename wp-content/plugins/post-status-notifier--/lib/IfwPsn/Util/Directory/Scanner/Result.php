<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Delivers the result of IfwPsn_Util_Directory_Scanner in various formats
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Result.php 153 2013-05-26 11:16:39Z timoreithde $
 */ 
class IfwPsn_Util_Directory_Scanner_Result
{
    /**
     * @var array
     */
    protected $_result;



    /**
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->_result = $result;
    }

    /**
     * @return array
     */
    public function getFilenames()
    {
        $result = array();
        foreach($this->_result as $fileinfo) {
            array_push($result, $fileinfo->getFilename());
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getBasenames()
    {
        $result = array();
        foreach($this->_result as $fileinfo) {
            array_push($result, $fileinfo->getBasename());
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getPathnames()
    {
        $result = array();
        foreach($this->_result as $fileinfo) {
            array_push($result, $fileinfo->getPathname());
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getClassnames()
    {
        $result = array();
        foreach($this->_result as $fileinfo) {
            array_push($result, $fileinfo->getClassname());
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        $result = array();

        $args = func_get_args();

        foreach($this->_result as $fileinfo) {
            $classname = $fileinfo->getClassname();
            if (count($args) == 0) {
                array_push($result, new $classname);
            } else {
                $r = new ReflectionClass($classname);
                array_push($result, $r->newInstanceArgs($args));
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function hasResult()
    {
        return count($this->_result) > 0;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }

}
