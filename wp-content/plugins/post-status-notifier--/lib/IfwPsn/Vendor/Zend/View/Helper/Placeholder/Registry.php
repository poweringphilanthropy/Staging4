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
 * @version    $Id: Registry.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_Registry */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Registry.php';

/** IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Container/Abstract.php';

/** IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Container.php';

/**
 * Registry for placeholder containers
 *
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_Placeholder_Registry
{
    /**
     * IfwPsn_Vendor_Zend_Registry key under which placeholder registry exists
     * @const string
     */
    const REGISTRY_KEY = 'IfwPsn_Vendor_Zend_View_Helper_Placeholder_Registry';

    /**
     * Default container class
     * @var string
     */
    protected $_containerClass = 'IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container';

    /**
     * Placeholder containers
     * @var array
     */
    protected $_items = array();

    /**
     * Retrieve or create registry instnace
     *
     * @return void
     */
    public static function getRegistry()
    {
        if (IfwPsn_Vendor_Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            $registry = IfwPsn_Vendor_Zend_Registry::get(self::REGISTRY_KEY);
        } else {
            $registry = new self();
            IfwPsn_Vendor_Zend_Registry::set(self::REGISTRY_KEY, $registry);
        }

        return $registry;
    }

    /**
     * createContainer
     *
     * @param  string $key
     * @param  array $value
     * @return IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract
     */
    public function createContainer($key, array $value = array())
    {
        $key = (string) $key;

        $this->_items[$key] = new $this->_containerClass($value);
        return $this->_items[$key];
    }

    /**
     * Retrieve a placeholder container
     *
     * @param  string $key
     * @return IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract
     */
    public function getContainer($key)
    {
        $key = (string) $key;
        if (isset($this->_items[$key])) {
            return $this->_items[$key];
        }

        $container = $this->createContainer($key);

        return $container;
    }

    /**
     * Does a particular container exist?
     *
     * @param  string $key
     * @return bool
     */
    public function containerExists($key)
    {
        $key = (string) $key;
        $return =  array_key_exists($key, $this->_items);
        return $return;
    }

    /**
     * Set the container for an item in the registry
     *
     * @param  string $key
     * @param  IfwPsn_Vendor_Zend_View_Placeholder_Container_Abstract $container
     * @return IfwPsn_Vendor_Zend_View_Placeholder_Registry
     */
    public function setContainer($key, IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract $container)
    {
        $key = (string) $key;
        $this->_items[$key] = $container;
        return $this;
    }

    /**
     * Delete a container
     *
     * @param  string $key
     * @return bool
     */
    public function deleteContainer($key)
    {
        $key = (string) $key;
        if (isset($this->_items[$key])) {
            unset($this->_items[$key]);
            return true;
        }

        return false;
    }

    /**
     * Set the container class to use
     *
     * @param  string $name
     * @return IfwPsn_Vendor_Zend_View_Helper_Placeholder_Registry
     */
    public function setContainerClass($name)
    {
        if (!class_exists($name)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Zend/Loader.php';
            IfwPsn_Zend_Loader::loadClass($name);
        }

        $reflection = new ReflectionClass($name);
        if (!$reflection->isSubclassOf(new ReflectionClass('IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract'))) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Registry/Exception.php';
            $e = new IfwPsn_Vendor_Zend_View_Helper_Placeholder_Registry_Exception('Invalid Container class specified');
            $e->setView($this->view);
            throw $e;
        }

        $this->_containerClass = $name;
        return $this;
    }

    /**
     * Retrieve the container class
     *
     * @return string
     */
    public function getContainerClass()
    {
        return $this->_containerClass;
    }
}
