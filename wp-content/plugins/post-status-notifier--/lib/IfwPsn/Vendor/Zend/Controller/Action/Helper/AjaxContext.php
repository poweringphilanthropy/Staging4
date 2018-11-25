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
 * @package    IfwPsn_Vendor_Zend_Controller
 * @subpackage IfwPsn_Vendor_Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AjaxContext.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Controller_Action_Helper_ContextSwitch
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Controller/Action/Helper/ContextSwitch.php';

/**
 * Simplify AJAX context switching based on requested format
 *
 * @uses       IfwPsn_Vendor_Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Controller
 * @subpackage IfwPsn_Vendor_Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Controller_Action_Helper_AjaxContext extends IfwPsn_Vendor_Zend_Controller_Action_Helper_ContextSwitch
{
    /**
     * Controller property to utilize for context switching
     * @var string
     */
    protected $_contextKey = 'ajaxable';

    /**
     * Constructor
     *
     * Add HTML context
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addContext('html', array('suffix' => 'ajax'));
    }

    /**
     * Initialize AJAX context switching
     *
     * Checks for XHR requests; if detected, attempts to perform context switch.
     *
     * @param  string $format
     * @return void
     */
    public function initContext($format = null)
    {
        $this->_currentContext = null;

        $request = $this->getRequest();
        if (!method_exists($request, 'isXmlHttpRequest') ||
            !$this->getRequest()->isXmlHttpRequest())
        {
            return;
        }

        return parent::initContext($format);
    }
}
