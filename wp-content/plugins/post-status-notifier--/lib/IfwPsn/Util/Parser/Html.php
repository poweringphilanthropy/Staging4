<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Html.php 450 2015-08-12 21:53:07Z timoreithde $
 */ 
class IfwPsn_Util_Parser_Html extends IfwPsn_Util_Parser_Abstract
{
    /**
     * @param $html
     * @return mixed
     */
    public static function sanitize($html)
    {
        $html = self::stripNullByte($html);
        $html = self::stripScript($html);

        return $html;
    }

    /**
     * @param $html
     * @return mixed
     */
    public static function stripScript($html)
    {
        do {
            if (isset($result)) {
                $html = $result;
            }
            $result = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);
        } while ($result != $html);

        return $result;
    }
}
