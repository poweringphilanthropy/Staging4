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
 * @package    IfwPsn_Vendor_Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Hex.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Validate_Abstract
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Validate_Hex extends IfwPsn_Vendor_Zend_Validate_Abstract
{
    const INVALID = 'hexInvalid';
    const NOT_HEX = 'notHex';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given. String expected",
        self::NOT_HEX => "'%value%' has not only hexadecimal digit characters",
    );

    /**
     * Defined by IfwPsn_Vendor_Zend_Validate_Interface
     *
     * Returns true if and only if $value contains only hexadecimal digit characters
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        if (!ctype_xdigit((string) $value)) {
            $this->_error(self::NOT_HEX);
            return false;
        }

        return true;
    }

}
