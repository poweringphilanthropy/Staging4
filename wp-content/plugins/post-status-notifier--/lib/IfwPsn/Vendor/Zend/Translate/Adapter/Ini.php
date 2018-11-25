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
 * @package    IfwPsn_Vendor_Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Ini.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_Locale */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Locale.php';

/** IfwPsn_Vendor_Zend_Translate_Adapter */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Translate/Adapter.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Translate_Adapter_Ini extends IfwPsn_Vendor_Zend_Translate_Adapter
{
    private $_data = array();

    /**
     * Load translation data
     *
     * @param  string|array  $data
     * @param  string        $locale  Locale/Language to add data for, identical with locale identifier,
     *                                see IfwPsn_Vendor_Zend_Locale for more information
     * @param  array         $options OPTIONAL Options to use
     * @throws IfwPsn_Vendor_Zend_Translate_Exception Ini file not found
     * @return array
     */
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        $this->_data = array();
        if (!file_exists($data)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Translate/Exception.php';
            throw new IfwPsn_Vendor_Zend_Translate_Exception("Ini file '".$data."' not found");
        }

        $inidata = parse_ini_file($data, false);
        if (!isset($this->_data[$locale])) {
            $this->_data[$locale] = array();
        }

        $this->_data[$locale] = array_merge($this->_data[$locale], $inidata);
        return $this->_data;
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Ini";
    }
}
