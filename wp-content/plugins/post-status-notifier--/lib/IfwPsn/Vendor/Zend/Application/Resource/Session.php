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
 * @version    $Id: Session.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for setting session options
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Resource_Session extends IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
{
    /**
     * Save handler to use
     *
     * @var IfwPsn_Vendor_Zend_Session_SaveHandler_Interface
     */
    protected $_saveHandler = null;

    /**
     * Set session save handler
     *
     * @param  array|string|IfwPsn_Vendor_Zend_Session_SaveHandler_Interface $saveHandler
     * @return IfwPsn_Vendor_Zend_Application_Resource_Session
     * @throws IfwPsn_Vendor_Zend_Application_Resource_Exception When $saveHandler is not a valid save handler
     */
    public function setSaveHandler($saveHandler)
    {
        $this->_saveHandler = $saveHandler;
        return $this;
    }

    /**
     * Get session save handler
     *
     * @return IfwPsn_Vendor_Zend_Session_SaveHandler_Interface
     * @throws IfwPsn_Vendor_Zend_Application_Resource_Exception
     */
    public function getSaveHandler()
    {
        if (!$this->_saveHandler instanceof IfwPsn_Vendor_Zend_Session_SaveHandler_Interface) {
            if (is_array($this->_saveHandler)) {
                if (!array_key_exists('class', $this->_saveHandler)) {
                    throw new IfwPsn_Vendor_Zend_Application_Resource_Exception('Session save handler class not provided in options');
                }
                $options = array();
                if (array_key_exists('options', $this->_saveHandler)) {
                    $options = $this->_saveHandler['options'];
                }
                $this->_saveHandler = $this->_saveHandler['class'];
                $this->_saveHandler = new $this->_saveHandler($options);
            } elseif (is_string($this->_saveHandler)) {
                $this->_saveHandler = new $this->_saveHandler();
            }

            if (!$this->_saveHandler instanceof IfwPsn_Vendor_Zend_Session_SaveHandler_Interface) {
                throw new IfwPsn_Vendor_Zend_Application_Resource_Exception('Invalid session save handler');
            }
        }
        return $this->_saveHandler;
    }

    /**
     * @return bool
     */
    protected function _hasSaveHandler()
    {
        return ($this->_saveHandler !== null);
    }

    /**
     * Defined by IfwPsn_Vendor_Zend_Application_Resource_Resource
     *
     * @return void
     */
    public function init()
    {
        $options = array_change_key_case($this->getOptions(), CASE_LOWER);
        if (isset($options['savehandler'])) {
            unset($options['savehandler']);
        }

        if (count($options) > 0) {
            IfwPsn_Vendor_Zend_Session::setOptions($options);
        }

        if ($this->_hasSaveHandler()) {
            IfwPsn_Vendor_Zend_Session::setSaveHandler($this->getSaveHandler());
        }
    }
}
