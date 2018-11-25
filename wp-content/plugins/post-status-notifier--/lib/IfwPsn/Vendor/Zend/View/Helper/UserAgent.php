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
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_View_Helper_Abstract */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Abstract.php';

/**
 * Helper for interacting with UserAgent instance
 *
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_UserAgent extends IfwPsn_Vendor_Zend_View_Helper_Abstract
{
    /**
     * UserAgent instance
     *
     * @var IfwPsn_Vendor_Zend_Http_UserAgent
     */
    protected $_userAgent = null;

    /**
     * Helper method: retrieve or set UserAgent instance
     *
     * @param  null|IfwPsn_Vendor_Zend_Http_UserAgent $userAgent
     * @return IfwPsn_Vendor_Zend_Http_UserAgent
     */
    public function userAgent(IfwPsn_Vendor_Zend_Http_UserAgent $userAgent = null)
    {
        if (null !== $userAgent) {
            $this->setUserAgent($userAgent);
        }
        return $this->getUserAgent();
    }

    /**
     * Set UserAgent instance
     *
     * @param  IfwPsn_Vendor_Zend_Http_UserAgent $userAgent
     * @return IfwPsn_Vendor_Zend_View_Helper_UserAgent
     */
    public function setUserAgent(IfwPsn_Vendor_Zend_Http_UserAgent $userAgent)
    {
        $this->_userAgent = $userAgent;
        return $this;
    }

    /**
     * Retrieve UserAgent instance
     *
     * If none set, instantiates one using no configuration
     *
     * @return IfwPsn_Vendor_Zend_Http_UserAgent
     */
    public function getUserAgent()
    {
        if (null === $this->_userAgent) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Http/UserAgent.php';
            $this->setUserAgent(new IfwPsn_Vendor_Zend_Http_UserAgent());
        }
        return $this->_userAgent;
    }
}
