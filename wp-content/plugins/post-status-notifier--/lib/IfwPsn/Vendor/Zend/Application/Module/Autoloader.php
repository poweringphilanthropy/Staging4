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
 * @subpackage Module
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Autoloader.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see IfwPsn_Vendor_Zend_Loader_Autoloader_Resource */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Loader/Autoloader/Resource.php';

/**
 * Resource loader for application module classes
 *
 * @uses       IfwPsn_Vendor_Zend_Loader_Autoloader_Resource
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Module
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Module_Autoloader extends IfwPsn_Vendor_Zend_Loader_Autoloader_Resource
{
    /**
     * Constructor
     *
     * @param  array|IfwPsn_Vendor_Zend_Config $options
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->initDefaultResourceTypes();
    }

    /**
     * Initialize default resource types for module resource classes
     *
     * @return void
     */
    public function initDefaultResourceTypes()
    {
        $basePath = $this->getBasePath();
        $this->addResourceTypes(
            array(
                'dbtable'    => array(
                    'namespace' => 'Model_DbTable',
                    'path'      => 'models/DbTable',
                ),
                'mappers'    => array(
                    'namespace' => 'Model_Mapper',
                    'path'      => 'models/mappers',
                ),
                'form'       => array(
                    'namespace' => 'Form',
                    'path'      => 'forms',
                ),
                'model'      => array(
                    'namespace' => 'Model',
                    'path'      => 'models',
                ),
                'plugin'     => array(
                    'namespace' => 'Plugin',
                    'path'      => 'plugins',
                ),
                'service'    => array(
                    'namespace' => 'Service',
                    'path'      => 'services',
                ),
                'viewhelper' => array(
                    'namespace' => 'View_Helper',
                    'path'      => 'views/helpers',
                ),
                'viewfilter' => array(
                    'namespace' => 'View_Filter',
                    'path'      => 'views/filters',
                ),
            )
        );
        $this->setDefaultResourceType('model');
    }
}
