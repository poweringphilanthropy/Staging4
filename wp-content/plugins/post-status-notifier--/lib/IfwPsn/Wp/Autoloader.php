<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Autoloader
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Autoloader.php 313 2014-08-02 20:55:51Z timoreithde $
 */
class IfwPsn_Wp_Autoloader
{
    /**
     * Lib dir store
     * @var array
     */
    protected static $_libDir = array();

    protected static $_modules = array();

    /**
     * Initializes the autoloader
     * @param string $libDir
     * @return bool
     */
    public static function init($libDir)
    {
        if (!in_array($libDir, self::$_libDir) && is_dir($libDir)) {
            self::$_libDir[] = $libDir;
        }
        return spl_autoload_register(array('IfwPsn_Wp_Autoloader', 'autoload'));
    }
    
    /**
     * Loads a class file
     * @param string $className
     * @return bool
     */
    public static function autoload($className)
    {
        $result = false;
        $class_path = self::getClassPath($className);

        if ($class_path !== false) {
            if (!class_exists($className)) {
                $result = include_once $class_path;
            }
        }

        return $result !== false;
    }
    
    /**
     * Gets the path of a class
     * @param string $className
     * @return string|false
     */
    public static function getClassPath($className)
    {
        foreach (self::$_libDir as $libDir) {
            $path = self::_getPath($className, $libDir);
            if ($path !== null) {
                return $path;
            }
        }

        // search in modules
        if (count(self::$_modules) > 0) {
            foreach(self::$_modules as $prefix => $libDir) {
                if (strpos($className, $prefix) === 0) {
                    $path = self::_getPath(str_replace($prefix, '', $className), $libDir);
                    if ($path !== null) {
                        return $path;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $className
     * @param $dir
     * @return null|string
     */
    protected static function _getPath($className, $dir)
    {
        $path = $dir . implode(DIRECTORY_SEPARATOR, explode('_', $className)) . '.php';
        if (is_readable($path)) {
            return $path;
        }
        return null;
    }

    /**
     * @param $classNamePrefix
     * @param $libDir
     */
    public static function registerModule($classNamePrefix, $libDir)
    {
        if (!isset(self::$_modules[$classNamePrefix])) {
            self::$_modules[$classNamePrefix] = $libDir;
        }
    }

    /**
     * @return array
     */
    public static function getAllRegisteredAutoloadFunctions()
    {
        return spl_autoload_functions();
    }
}