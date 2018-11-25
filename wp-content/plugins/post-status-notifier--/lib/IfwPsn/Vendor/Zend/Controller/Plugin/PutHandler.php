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
 * @subpackage IfwPsn_Vendor_Zend_Controller_Plugin
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PutHandler.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Controller_Plugin_Abstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Controller/Plugin/Abstract.php';

/**
 * @see IfwPsn_Vendor_Zend_Controller_Request_Http
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Controller/Request/Http.php';

/**
 * Plugin to digest PUT request body and make params available just like POST
 *
 * @package    IfwPsn_Vendor_Zend_Controller
 * @subpackage IfwPsn_Vendor_Zend_Controller_Plugin
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Controller_Plugin_PutHandler extends IfwPsn_Vendor_Zend_Controller_Plugin_Abstract
{
    /**
     * Before dispatching, digest PUT request body and set params
     *
     * @param IfwPsn_Vendor_Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(IfwPsn_Vendor_Zend_Controller_Request_Abstract $request)
    {
        if (!$request instanceof IfwPsn_Vendor_Zend_Controller_Request_Http) {
            return;
        }

        if ($this->_request->isPut()) {
            $putParams = array();
            parse_str($this->_request->getRawBody(), $putParams);
            $request->setParams($putParams);
        }
    }
}
