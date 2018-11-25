<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * IfwPsn_Vendor_Zend_Http_UserAgent_Features_Adapter_Interface
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Http/UserAgent/Features/Adapter.php';

/**
 * Features adapter utilizing PHP's native browscap support
 *
 * Requires that you have a PHP-compatible version of the browscap.ini, per the
 * instructions at http://php.net/get_browser
 *
 * @package    IfwPsn_Vendor_Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Http_UserAgent_Features_Adapter_Browscap
    implements IfwPsn_Vendor_Zend_Http_UserAgent_Features_Adapter
{
    /**
     * Constructor
     *
     * Validate that we have browscap support available.
     *
     * @throws IfwPsn_Vendor_Zend_Http_UserAgent_Features_Exception
     */
    public function __construct()
    {
        $browscap = ini_get('browscap');
        if (empty($browscap) || !file_exists($browscap)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Http/UserAgent/Features/Exception.php';
            throw new IfwPsn_Vendor_Zend_Http_UserAgent_Features_Exception(sprintf(
                '%s requires a browscap entry in php.ini pointing to a valid browscap.ini; none present',
                __CLASS__
            ));
        }
    }

    /**
     * Get features from request
     *
     * @param  array $request $_SERVER variable
     * @param  array $config  ignored; included only to satisfy parent class
     * @return array
     */
    public static function getFromRequest($request, array $config)
    {
        $browscap = get_browser($request['http_user_agent'], true);
        $features = array();

        if (is_array($browscap)) {
            foreach ($browscap as $key => $value) {
                // For a few keys, we need to munge a bit for the device object
                switch ($key) {
                    case 'browser':
                        $features['mobile_browser'] = $value;
                        break;

                    case 'version':
                        $features['mobile_browser_version'] = $value;
                        break;

                    case 'platform':
                        $features['device_os'] = $value;
                        break;

                    default:
                        $features[$key] = $value;
                        break;
                }
            }
        }

        return $features;
    }
}
