<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Css.php 480 2015-11-02 18:03:15Z timoreithde $
 */ 
class IfwPsn_Util_Parser_Css extends IfwPsn_Util_Parser_Abstract
{
    /**
     * @param $css
     * @return mixed
     */
    public static function sanitize($css)
    {
        $css = self::stripNullByte($css);

        return $css;
    }

    /**
     * @param $css
     * @return mixed
     */
    public static function compress($css)
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove space after colons
        $css = str_replace(': ', ':', $css);
        // Remove whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

        return $css;
    }
}
