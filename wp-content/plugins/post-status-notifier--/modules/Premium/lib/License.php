<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: License.php 433 2015-10-30 17:31:36Z timoreithde $
 * @package
 */

class Psn_Module_Premium_License
{
    const LICENSE_CODE_SALT = 'psn.license.key';

    /**
     * @var Psn_Module_Premium_License
     */
    protected static $_instance;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    private $_pm;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return Psn_Module_Premium_License
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($pm);
        }
        return self::$_instance;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    private function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    /**
     * @return mixed|null|string
     */
    public function getLicense()
    {
        $license_code = $this->getEncryptedLicense();

        if (!empty($license_code) && IfwPsn_Util_Encryption::isEncryptedString($license_code)) {
            $license_code = IfwPsn_Util_Encryption::decrypt($license_code, self::LICENSE_CODE_SALT);
        }

        return $license_code;
    }

    /**
     * Retrieves the raw encrypted licensed code
     * @return mixed|null
     */
    public function getEncryptedLicense()
    {
        if (IfwPsn_Wp_Proxy_Blog::isMultisite()) {
            $license_code = get_site_option('psn_license');
        } else {
            $license_code = $this->_pm->getOptionsManager()->getOption('license_code');
        }

        return $license_code;
    }

    /**
     * @param $license_code
     */
    public function saveLicense($license_code)
    {
        if (!IfwPsn_Util_Encryption::isEncryptedString($license_code)) {
            $license_code = IfwPsn_Util_Encryption::encrypt($license_code, self::LICENSE_CODE_SALT);
        }

        if (IfwPsn_Wp_Proxy_Blog::isMultisite()) {
            update_site_option('psn_license', $license_code);
        } else {
            $this->_pm->getOptionsManager()->updateOption('license_code', $license_code);
        }
    }
}
