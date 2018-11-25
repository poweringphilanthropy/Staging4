<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Encryption helper
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Encryption.php 419 2015-04-24 21:15:32Z timoreithde $
 * @package   
 */ 
class IfwPsn_Util_Encryption
{
    /**
     * @param $string
     * @param $salt
     * @return string
     */
    public static function encrypt($string, $salt)
    {
        if (self::_isMcrypt()) {
            $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $salt, utf8_encode($string), MCRYPT_MODE_ECB, $iv);
            // to prevent errors saving with update_options:
            $encrypted_string = base64_encode($encrypted_string);

        } else {
            $encrypted_string = base64_encode($string);
        }

        $encrypted_string = str_replace("\0", "", $encrypted_string);

        return $encrypted_string;
    }

    /**
     * @param $string
     * @param $salt
     * @return string
     */
    public static function decrypt($string, $salt)
    {
        if (self::_isMcrypt()) {
            $string = base64_decode($string);
            $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $salt, $string, MCRYPT_MODE_ECB, $iv);
        } else {
            $decrypted_string = base64_decode($string);
        }

        $decrypted_string = str_replace("\0", "", $decrypted_string);

        return $decrypted_string;
    }

    /**
     * @return bool
     */
    protected static function _isMcrypt()
    {
        return extension_loaded('mcrypt');
    }

    /**
     * @param $str
     * @return bool
     */
    public static function isEncryptedString($str)
    {
        if ( base64_encode(base64_decode($str, true)) === $str){
            return true;
        }
        return false;
    }
}
