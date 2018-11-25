<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Cron.php 427 2015-05-03 11:12:15Z timoreithde $
 */ 
class IfwPsn_Wp_Helper_Cron 
{
    /**
     * @var null|array
     */
    protected static $_allSchedules;

    /**
     * @return array
     */
    public static function getAllSchedules()
    {
        if (self::$_allSchedules === null) {
            $result = array();

            if (function_exists('wp_get_schedules')) {
                foreach (wp_get_schedules() as $k => $v) {
                    $result[$k] = $v['display'];
                }
            }

            self::$_allSchedules = $result;
        }

        return self::$_allSchedules;
    }

    /**
     * @param $key
     * @return null|string
     */
    public static function getScheduleDisplay($key)
    {
        $result = $key;
        foreach(self::getAllSchedules() as $k => $v) {
            if ($key == $k) {
                $result = $v;
                break;
            }
        }
        return $result;
    }
}
