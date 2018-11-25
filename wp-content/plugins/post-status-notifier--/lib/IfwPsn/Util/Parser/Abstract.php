<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 450 2015-08-12 21:53:07Z timoreithde $
 */ 
abstract class IfwPsn_Util_Parser_Abstract
{
    /**
     * @param $string
     * @return mixed
     */
    public static function stripNullByte($string)
    {
        return str_replace(chr(0), '', $string);
    }
}
