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
 * @version    $Id: NonPersistent.php 481 2015-11-03 13:28:23Z timoreithde $
 */


/**
 * @see IfwPsn_Vendor_Zend_Http_UserAgent_Storage_Interface
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Http/UserAgent/Storage.php';


/**
 * Non-Persistent Browser Storage
 *
 * Since HTTP Browserentication happens again on each request, this will always be
 * re-populated. So there's no need to use sessions, this simple value class
 * will hold the data for rest of the current request.
 *
 * @package    IfwPsn_Vendor_Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Http_UserAgent_Storage_NonPersistent
    implements IfwPsn_Vendor_Zend_Http_UserAgent_Storage
{
    /**
     * Holds the actual Browser data
     * @var mixed
     */
    protected $_data;

    /**
     * Returns true if and only if storage is empty
     *
     * @throws IfwPsn_Vendor_Zend_Http_UserAgent_Storage_Exception If it is impossible to determine whether storage is empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws IfwPsn_Vendor_Zend_Http_UserAgent_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */
    public function read()
    {
        return $this->_data;
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws IfwPsn_Vendor_Zend_Http_UserAgent_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $this->_data = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @throws IfwPsn_Vendor_Zend_Http_UserAgent_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear()
    {
        $this->_data = null;
    }
}
