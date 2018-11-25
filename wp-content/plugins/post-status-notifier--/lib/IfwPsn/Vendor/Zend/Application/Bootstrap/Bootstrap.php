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
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Bootstrap.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Bootstrap_BootstrapAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Bootstrap/BootstrapAbstract.php';

/**
 * Concrete base class for bootstrap classes
 *
 * Registers and utilizes IfwPsn_Vendor_Zend_Controller_Front by default.
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Bootstrap_Bootstrap
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Bootstrap_Bootstrap
    extends IfwPsn_Vendor_Zend_Application_Bootstrap_BootstrapAbstract
{
    /**
     * Application resource namespace
     * @var false|string
     */
    protected $_appNamespace = false;

    /**
     * Application resource autoloader
     * @var IfwPsn_Vendor_Zend_Loader_Autoloader_Resource
     */
    protected $_resourceLoader;

    /**
     * Constructor
     *
     * Ensure FrontController resource is registered
     *
     * @param  IfwPsn_Vendor_Zend_Application|IfwPsn_Vendor_Zend_Application_Bootstrap_Bootstrapper $application
     */
    public function __construct($application)
    {
        parent::__construct($application);

        if ($application->hasOption('resourceloader')) {
            $this->setOptions(
                array(
                    'resourceloader' => $application->getOption(
                        'resourceloader'
                    )
                )
            );
        }
        $this->getResourceLoader();

        if (!$this->hasPluginResource('FrontController')) {
            $this->registerPluginResource('FrontController');
        }
    }

    /**
     * Run the application
     *
     * Checks to see that we have a default controller directory. If not, an
     * exception is thrown.
     *
     * If so, it registers the bootstrap with the 'bootstrap' parameter of
     * the front controller, and dispatches the front controller.
     *
     * @return mixed
     * @throws IfwPsn_Vendor_Zend_Application_Bootstrap_Exception
     */
    public function run()
    {
        $front   = $this->getResource('FrontController');
        $default = $front->getDefaultModule();
        if (null === $front->getControllerDirectory($default)) {
            throw new IfwPsn_Vendor_Zend_Application_Bootstrap_Exception(
                'No default controller directory registered with front controller'
            );
        }

        $front->setParam('bootstrap', $this);
        $response = $front->dispatch();
        if ($front->returnResponse()) {
            return $response;
        }
    }

    /**
     * Set module resource loader
     *
     * @param  IfwPsn_Vendor_Zend_Loader_Autoloader_Resource $loader
     * @return IfwPsn_Vendor_Zend_Application_Module_Bootstrap
     */
    public function setResourceLoader(IfwPsn_Vendor_Zend_Loader_Autoloader_Resource $loader)
    {
        $this->_resourceLoader = $loader;
        return $this;
    }

    /**
     * Retrieve module resource loader
     *
     * @return IfwPsn_Vendor_Zend_Loader_Autoloader_Resource
     */
    public function getResourceLoader()
    {
        if ((null === $this->_resourceLoader)
            && (false !== ($namespace = $this->getAppNamespace()))
        ) {
            $r    = new ReflectionClass($this);
            $path = $r->getFileName();
            $this->setResourceLoader(
                new IfwPsn_Vendor_Zend_Application_Module_Autoloader(
                    array(
                        'namespace' => $namespace,
                        'basePath'  => dirname($path),
                    )
                )
            );
        }
        return $this->_resourceLoader;
    }

    /**
     * Get application namespace (used for module autoloading)
     *
     * @return string
     */
    public function getAppNamespace()
    {
        return $this->_appNamespace;
    }

    /**
     * Set application namespace (for module autoloading)
     *
     * @param  string
     * @return IfwPsn_Vendor_Zend_Application_Bootstrap_Bootstrap
     */
    public function setAppNamespace($value)
    {
        $this->_appNamespace = (string) $value;
        return $this;
    }
}
