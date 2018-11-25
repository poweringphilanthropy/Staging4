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
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Log.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for initializing logger
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Resource_Log
    extends IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var IfwPsn_Vendor_Zend_Log
     */
    protected $_log;

    /**
     * Defined by IfwPsn_Vendor_Zend_Application_Resource_Resource
     *
     * @return IfwPsn_Vendor_Zend_Log
     */
    public function init()
    {
        return $this->getLog();
    }

    /**
     * Attach logger
     *
     * @param  IfwPsn_Vendor_Zend_Log $log
     * @return IfwPsn_Vendor_Zend_Application_Resource_Log
     */
    public function setLog(IfwPsn_Vendor_Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }

    /**
     * Retrieve logger object
     *
     * @return IfwPsn_Vendor_Zend_Log
     */
    public function getLog()
    {
        if (null === $this->_log) {
            $options = $this->getOptions();
            $log = IfwPsn_Vendor_Zend_Log::factory($options);
            $this->setLog($log);
        }
        return $this->_log;
    }
}
