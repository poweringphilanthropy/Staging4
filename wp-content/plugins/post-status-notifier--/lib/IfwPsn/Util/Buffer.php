<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Buffer helper
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Buffer.php 371 2014-12-20 21:46:34Z timoreithde $
 * @package   
 */ 
class IfwPsn_Util_Buffer 
{
    /**
     * @var array
     */
    protected static $_buffer = array();

    /**
     * @var bool
     */
    public static $debug = false;


    /**
     * @param $token
     * @param string $ns
     * @return bool
     */
    public static function exists($token, $ns = 'default')
    {
        $result = isset(self::$_buffer[$ns]) && array_key_exists($token, self::$_buffer[$ns]);
        if (self::$debug) {
            ifw_debug(sprintf('%s: $token: %s, $ns: %s, $result: %s', __METHOD__, $token, $ns, ($result ? 'true' : 'false')));
        }
        return $result;
    }

    /**
     * @param $token
     * @param string $ns
     * @return mixed|null
     */
    public static function get($token, $ns = 'default')
    {
        if (self::exists($token, $ns)) {
            return self::$_buffer[$ns][$token];
        }
        return null;
    }

    /**
     * @param $token
     * @param $data
     * @param string $ns
     */
    public static function set($token, $data, $ns = 'default')
    {
        if (!isset(self::$_buffer[$ns])) {
            self::$_buffer[$ns] = array();
        }
        self::$_buffer[$ns][$token] = $data;
    }
}
 