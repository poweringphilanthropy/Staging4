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
 * @version    $Id: Router.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for initializing the locale
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Resource_Base
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Resource_Router
    extends IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var IfwPsn_Vendor_Zend_Controller_Router_Rewrite
     */
    protected $_router;

    /**
     * Defined by IfwPsn_Vendor_Zend_Application_Resource_Resource
     *
     * @return IfwPsn_Vendor_Zend_Controller_Router_Rewrite
     */
    public function init()
    {
        return $this->getRouter();
    }

    /**
     * Retrieve router object
     *
     * @return IfwPsn_Vendor_Zend_Controller_Router_Rewrite
     */
    public function getRouter()
    {
        if (null === $this->_router) {
            $bootstrap = $this->getBootstrap();
            $bootstrap->bootstrap('FrontController');
            $this->_router = $bootstrap->getContainer()->frontcontroller->getRouter();

            $options = $this->getOptions();
            if (!isset($options['routes'])) {
                $options['routes'] = array();
            }

            if (isset($options['chainNameSeparator'])) {
                $this->_router->setChainNameSeparator($options['chainNameSeparator']);
            }

            if (isset($options['useRequestParametersAsGlobal'])) {
                $this->_router->useRequestParametersAsGlobal($options['useRequestParametersAsGlobal']);
            }

            $this->_router->addConfig(new IfwPsn_Vendor_Zend_Config($options['routes']));
        }

        return $this->_router;
    }
}
