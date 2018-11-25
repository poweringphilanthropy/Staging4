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
 * @package    IfwPsn_Vendor_Zend_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/** @see IfwPsn_Vendor_Zend_Log_Filter_Interface */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Filter/Interface.php';

/** @see IfwPsn_Vendor_Zend_Log_FactoryInterface */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/FactoryInterface.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 481 2015-11-03 13:28:23Z timoreithde $
 */
abstract class IfwPsn_Vendor_Zend_Log_Filter_Abstract
    implements IfwPsn_Vendor_Zend_Log_Filter_Interface, IfwPsn_Vendor_Zend_Log_FactoryInterface
{
    /**
     * Validate and optionally convert the config to array
     *
     * @param  array|IfwPsn_Vendor_Zend_Config $config IfwPsn_Vendor_Zend_Config or Array
     * @return array
     * @throws IfwPsn_Vendor_Zend_Log_Exception
     */
    static protected function _parseConfig($config)
    {
        if ($config instanceof IfwPsn_Vendor_Zend_Config) {
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Exception.php';
            throw new IfwPsn_Vendor_Zend_Log_Exception('Configuration must be an array or instance of IfwPsn_Vendor_Zend_Config');
        }

        return $config;
    }
}
