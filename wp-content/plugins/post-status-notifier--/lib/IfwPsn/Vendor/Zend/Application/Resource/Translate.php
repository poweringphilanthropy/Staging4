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
 * @version    $Id: Translate.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for setting translation options
 *
 * @uses       IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Application_Resource_Translate extends IfwPsn_Vendor_Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'IfwPsn_Vendor_Zend_Translate';

    /**
     * @var IfwPsn_Vendor_Zend_Translate
     */
    protected $_translate;

    /**
     * Defined by IfwPsn_Vendor_Zend_Application_Resource_Resource
     *
     * @return IfwPsn_Vendor_Zend_Translate
     */
    public function init()
    {
        return $this->getTranslate();
    }

    /**
     * Retrieve translate object
     *
     * @return IfwPsn_Vendor_Zend_Translate
     * @throws IfwPsn_Vendor_Zend_Application_Resource_Exception if registry key was used
     *          already but is no instance of IfwPsn_Vendor_Zend_Translate
     */
    public function getTranslate()
    {
        if (null === $this->_translate) {
            $options = $this->getOptions();

            if (!isset($options['content']) && !isset($options['data'])) {
                require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/Exception.php';
                throw new IfwPsn_Vendor_Zend_Application_Resource_Exception('No translation source data provided.');
            } else if (array_key_exists('content', $options) && array_key_exists('data', $options)) {
                require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/Exception.php';
                throw new IfwPsn_Vendor_Zend_Application_Resource_Exception(
                    'Conflict on translation source data: choose only one key between content and data.'
                );
            }

            if (empty($options['adapter'])) {
                $options['adapter'] = IfwPsn_Vendor_Zend_Translate::AN_ARRAY;
            }

            if (!empty($options['data'])) {
                $options['content'] = $options['data'];
                unset($options['data']);
            }

            if (isset($options['log'])) {
                if (is_array($options['log'])) {
                    $options['log'] = IfwPsn_Vendor_Zend_Log::factory($options['log']);
                }
            }

            if (isset($options['options'])) {
                foreach ($options['options'] as $key => $value) {
                    $options[$key] = $value;
                }
            }

            if (!empty($options['cache']) && is_string($options['cache'])) {
                $bootstrap = $this->getBootstrap();
                if ($bootstrap instanceof IfwPsn_Vendor_Zend_Application_Bootstrap_ResourceBootstrapper &&
                    $bootstrap->hasPluginResource('CacheManager')
                ) {
                    $cacheManager = $bootstrap->bootstrap('CacheManager')
                        ->getResource('CacheManager');
                    if (null !== $cacheManager &&
                        $cacheManager->hasCache($options['cache'])
                    ) {
                        $options['cache'] = $cacheManager->getCache($options['cache']);
                    }
                }
            }

            $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                 ? $options['registry_key']
                 : self::DEFAULT_REGISTRY_KEY;
            unset($options['registry_key']);

            if (IfwPsn_Vendor_Zend_Registry::isRegistered($key)) {
                $translate = IfwPsn_Vendor_Zend_Registry::get($key);
                if (!$translate instanceof IfwPsn_Vendor_Zend_Translate) {
                    require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Application/Resource/Exception.php';
                    throw new IfwPsn_Vendor_Zend_Application_Resource_Exception(
                        $key
                        . ' already registered in registry but is '
                        . 'no instance of IfwPsn_Vendor_Zend_Translate'
                    );
                }

                $translate->addTranslation($options);
                $this->_translate = $translate;
            } else {
                $this->_translate = new IfwPsn_Vendor_Zend_Translate($options);
                IfwPsn_Vendor_Zend_Registry::set($key, $this->_translate);
            }
        }

        return $this->_translate;
    }
}
