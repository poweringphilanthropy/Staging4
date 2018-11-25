<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: BBCode.php 457 2015-08-23 12:59:23Z timoreithde $
 */ 
class IfwPsn_Util_Parser_BBCode extends IfwPsn_Util_Parser_Abstract
{
    /**
     * @param $text
     * @return mixed
     */
    public static function parse($text)
    {
        $replace = array(
            '[br]' => '<br>',
            '[b]' => '<b>',
            '[/b]' => '</b>',
        );

        return self::stripNullByte(strtr($text, $replace));
    }
}
