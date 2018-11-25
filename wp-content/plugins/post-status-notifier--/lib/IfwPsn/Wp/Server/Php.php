<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Php.php 256 2014-04-12 19:30:31Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Server_Php 
{
    /**
     * @param $extension
     * @return bool
     */
    public static function isExtensionLoaded($extension)
    {
        return extension_loaded($extension);
    }

    /**
     * @return bool
     */
    public static function isPdoMysql()
    {
        return self::isExtensionLoaded('pdo_mysql');
    }

    /**
     * @return array
     */
    public static function getExtensions()
    {
        $extensions = get_loaded_extensions();
        natcasesort($extensions);
        return $extensions;
    }

    /**
     * @return mixed
     */
    public static function getServerSoftware()
    {
        return $_SERVER['SERVER_SOFTWARE'];
    }

    /**
     * @return mixed
     */
    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }
}
