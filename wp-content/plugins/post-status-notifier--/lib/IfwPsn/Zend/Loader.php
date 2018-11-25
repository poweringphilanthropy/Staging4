<?php
/**
 * Overwrites Zend_Loader method loadClass
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Loader.php 274 2014-04-28 22:01:43Z timoreithde $
 */
class IfwPsn_Zend_Loader extends IfwPsn_Vendor_Zend_Loader
{
    /**
     * 
     * @param string $class
     * @param unknown_type $dirs
     * @throws IfwPsn_Vendor_Zend_Exception
     */
    public static function loadClass($class, $dirs = null)
    {
        if (IfwPsn_Wp_Autoloader::autoload($class)) {
            return;
        }
        
        parent::loadClass($class, $dirs);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public static function isReadable($filename)
    {
        if (strpos($filename, 'Vendor_Zend_Translate_Adapter_Array') !== false) {
            return false;
        }

        $result = parent::isReadable($filename);
        return $result;
    }
}