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
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Currency.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/** IfwPsn_Vendor_Zend_View_Helper_Abstract.php */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Abstract.php';

/**
 * Currency view helper
 *
 * @category  Zend
 * @package   IfwPsn_Vendor_Zend_View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_Currency extends IfwPsn_Vendor_Zend_View_Helper_Abstract
{
    /**
     * Currency object
     *
     * @var IfwPsn_Vendor_Zend_Currency
     */
    protected $_currency;

    /**
     * Constructor for manually handling
     *
     * @param  IfwPsn_Vendor_Zend_Currency $currency Instance of IfwPsn_Vendor_Zend_Currency
     * @return void
     */
    public function __construct($currency = null)
    {
        if ($currency === null) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Registry.php';
            if (IfwPsn_Vendor_Zend_Registry::isRegistered('IfwPsn_Vendor_Zend_Currency')) {
                $currency = IfwPsn_Vendor_Zend_Registry::get('IfwPsn_Vendor_Zend_Currency');
            }
        }

        $this->setCurrency($currency);
    }

    /**
     * Output a formatted currency
     *
     * @param  integer|float            $value    Currency value to output
     * @param  string|IfwPsn_Vendor_Zend_Locale|array $currency OPTIONAL Currency to use for
     *                                            this call
     * @return string Formatted currency
     */
    public function currency($value = null, $currency = null)
    {
        if ($value === null) {
            return $this;
        }

        if (is_string($currency) || ($currency instanceof IfwPsn_Vendor_Zend_Locale)) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Locale.php';
            if (IfwPsn_Vendor_Zend_Locale::isLocale($currency)) {
                $currency = array('locale' => $currency);
            }
        }

        if (is_string($currency)) {
            $currency = array('currency' => $currency);
        }

        if (is_array($currency)) {
            return $this->_currency->toCurrency($value, $currency);
        }

        return $this->_currency->toCurrency($value);
    }

    /**
     * Sets a currency to use
     *
     * @param  IfwPsn_Vendor_Zend_Currency|String|IfwPsn_Vendor_Zend_Locale $currency Currency to use
     * @throws IfwPsn_Vendor_Zend_View_Exception When no or a false currency was set
     * @return IfwPsn_Vendor_Zend_View_Helper_Currency
     */
    public function setCurrency($currency = null)
    {
        if (!$currency instanceof IfwPsn_Vendor_Zend_Currency) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Currency.php';
            $currency = new IfwPsn_Vendor_Zend_Currency($currency);
        }
        $this->_currency = $currency;

        return $this;
    }

    /**
     * Retrieve currency object
     *
     * @return IfwPsn_Vendor_Zend_Currency|null
     */
    public function getCurrency()
    {
        return $this->_currency;
    }
}
