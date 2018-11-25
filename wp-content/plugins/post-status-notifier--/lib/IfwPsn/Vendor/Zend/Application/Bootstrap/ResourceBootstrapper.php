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
 * @version    $Id: ResourceBootstrapper.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * Interface for bootstrap classes that utilize resource plugins
 *
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface IfwPsn_Vendor_Zend_Application_Bootstrap_ResourceBootstrapper
{
    /**
     * Register a resource with the bootstrap
     *
     * @param  string|IfwPsn_Vendor_Zend_Application_Resource_Resource $resource
     * @param  null|array|IfwPsn_Vendor_Zend_Config                     $options
     * @return IfwPsn_Vendor_Zend_Application_Bootstrap_ResourceBootstrapper
     */
    public function registerPluginResource($resource, $options = null);

    /**
     * Unregister a resource from the bootstrap
     *
     * @param  string|IfwPsn_Vendor_Zend_Application_Resource_Resource $resource
     * @return IfwPsn_Vendor_Zend_Application_Bootstrap_ResourceBootstrapper
     */
    public function unregisterPluginResource($resource);

    /**
     * Is the requested resource registered?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasPluginResource($resource);

    /**
     * Retrieve resource
     *
     * @param  string $resource
     * @return IfwPsn_Vendor_Zend_Application_Resource_Resource
     */
    public function getPluginResource($resource);

    /**
     * Get all resources
     *
     * @return array
     */
    public function getPluginResources();

    /**
     * Get just resource names
     *
     * @return array
     */
    public function getPluginResourceNames();

    /**
     * Set plugin loader to use to fetch resources
     *
     * @param  IfwPsn_Vendor_Zend_Loader_PluginLoader_Interface IfwPsn_Vendor_Zend_Loader_PluginLoader
     * @return IfwPsn_Vendor_Zend_Application_Bootstrap_ResourceBootstrapper
     */
    public function setPluginLoader(IfwPsn_Vendor_Zend_Loader_PluginLoader_Interface $loader);

    /**
     * Retrieve plugin loader for resources
     *
     * @return IfwPsn_Vendor_Zend_Loader_PluginLoader
     */
    public function getPluginLoader();
}
