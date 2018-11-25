<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Log.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_Logger_Model_Log extends IfwPsn_Wp_Plugin_Logger_Model
{
    /**
     * @var string
     */
    public static $_table = 'psn_log';

    /**
     * @return string
     */
    public static function getSingular()
    {
        return 'log';
    }

    /**
     * @return string
     */
    public static function getPlural()
    {
        return 'logs';
    }
}

