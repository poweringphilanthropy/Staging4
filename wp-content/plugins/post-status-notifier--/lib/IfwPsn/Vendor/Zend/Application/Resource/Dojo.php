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
 * @version    $Id: Dojo.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for settings Dojo options
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Resource_Dojo
    extends IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var IfwPsn_Vendor_Zend_Dojo_View_Helper_Dojo_Container
     */
    protected $_dojo;

    /**
     * Defined by IfwPsn_Vendor_Zend_Application_Resource_Resource
     *
     * @return IfwPsn_Vendor_Zend_Dojo_View_Helper_Dojo_Container
     */
    public function init()
    {
        return $this->getDojo();
    }

    /**
     * Retrieve Dojo View Helper
     *
     * @return IfwPsn_Vendor_Zend_Dojo_View_Dojo_Container
     */
    public function getDojo()
    {
        if (null === $this->_dojo) {
            $this->getBootstrap()->bootstrap('view');
            $view = $this->getBootstrap()->view;

            IfwPsn_Vendor_Zend_Dojo::enableView($view);
            $view->dojo()->setOptions($this->getOptions());

            $this->_dojo = $view->dojo();
        }

        return $this->_dojo;
    }
}
