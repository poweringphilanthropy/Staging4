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
 * @package    IfwPsn_Vendor_Zend_Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Alpha.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Filter_Interface
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Filter/Interface.php';
/**
 * @see IfwPsn_Vendor_Zend_Locale
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Locale.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Filter_Alpha implements IfwPsn_Vendor_Zend_Filter_Interface
{
    /**
     * Whether to allow white space characters; off by default
     *
     * @var boolean
     * @deprecated
     */
    public $allowWhiteSpace;

    /**
     * Is PCRE is compiled with UTF-8 and Unicode support
     *
     * @var mixed
     **/
    protected static $_unicodeEnabled;

    /**
     * Locale in browser.
     *
     * @var IfwPsn_Vendor_Zend_Locale object
     */
    protected $_locale;

    /**
     * The Alphabet means english alphabet.
     *
     * @var boolean
     */
    protected static $_meansEnglishAlphabet;

    /**
     * Sets default option values for this instance
     *
     * @param  boolean $allowWhiteSpace
     * @return void
     */
    public function __construct($allowWhiteSpace = false)
    {
        if ($allowWhiteSpace instanceof IfwPsn_Vendor_Zend_Config) {
            $allowWhiteSpace = $allowWhiteSpace->toArray();
        } else if (is_array($allowWhiteSpace)) {
            if (array_key_exists('allowwhitespace', $allowWhiteSpace)) {
                $allowWhiteSpace = $allowWhiteSpace['allowwhitespace'];
            } else {
                $allowWhiteSpace = false;
            }
        }

        $this->allowWhiteSpace = (boolean) $allowWhiteSpace;
        if (null === self::$_unicodeEnabled) {
            self::$_unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
        }

        if (null === self::$_meansEnglishAlphabet) {
            $this->_locale = new IfwPsn_Vendor_Zend_Locale('auto');
            self::$_meansEnglishAlphabet = in_array($this->_locale->getLanguage(),
                                                    array('ja', 'ko', 'zh')
                                                    );
        }

    }

    /**
     * Returns the allowWhiteSpace option
     *
     * @return boolean
     */
    public function getAllowWhiteSpace()
    {
        return $this->allowWhiteSpace;
    }

    /**
     * Sets the allowWhiteSpace option
     *
     * @param boolean $allowWhiteSpace
     * @return IfwPsn_Vendor_Zend_Filter_Alpha Provides a fluent interface
     */
    public function setAllowWhiteSpace($allowWhiteSpace)
    {
        $this->allowWhiteSpace = (boolean) $allowWhiteSpace;
        return $this;
    }

    /**
     * Defined by IfwPsn_Vendor_Zend_Filter_Interface
     *
     * Returns the string $value, removing all but alphabetic characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $whiteSpace = $this->allowWhiteSpace ? '\s' : '';
        if (!self::$_unicodeEnabled) {
            // POSIX named classes are not supported, use alternative a-zA-Z match
            $pattern = '/[^a-zA-Z' . $whiteSpace . ']/';
        } else if (self::$_meansEnglishAlphabet) {
            //The Alphabet means english alphabet.
            $pattern = '/[^a-zA-Z'  . $whiteSpace . ']/u';
        } else {
            //The Alphabet means each language's alphabet.
            $pattern = '/[^\p{L}' . $whiteSpace . ']/u';
        }

        return preg_replace($pattern, '', (string) $value);
    }
}
