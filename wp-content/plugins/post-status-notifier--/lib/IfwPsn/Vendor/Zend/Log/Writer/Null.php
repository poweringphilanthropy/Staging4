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
 * @version    $Id: Null.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/** IfwPsn_Vendor_Zend_Log_Writer_Abstract */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Log/Writer/Abstract.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Null.php 481 2015-11-03 13:28:23Z timoreithde $
 */
class IfwPsn_Vendor_Zend_Log_Writer_Null extends IfwPsn_Vendor_Zend_Log_Writer_Abstract
{
    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
    }

    /**
     * Create a new instance of IfwPsn_Vendor_Zend_Log_Writer_Null
     *
     * @param  array|IfwPsn_Vendor_Zend_Config $config
     * @return IfwPsn_Vendor_Zend_Log_Writer_Null
     */
    static public function factory($config)
    {
        return new self();
    }
}
