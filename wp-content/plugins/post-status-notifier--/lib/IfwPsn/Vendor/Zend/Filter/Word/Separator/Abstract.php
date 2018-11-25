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
 * @version    $Id: Abstract.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Filter_PregReplace
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Filter/PregReplace.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Filter
 * @uses       IfwPsn_Vendor_Zend_Filter_PregReplace
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class IfwPsn_Vendor_Zend_Filter_Word_Separator_Abstract extends IfwPsn_Vendor_Zend_Filter_PregReplace
{

    protected $_separator = null;

    /**
     * Constructor
     *
     * @param  string $separator Space by default
     * @return void
     */
    public function __construct($separator = ' ')
    {
        $this->setSeparator($separator);
    }

    /**
     * Sets a new seperator
     *
     * @param  string  $separator  Seperator
     * @return $this
     */
    public function setSeparator($separator)
    {
        if ($separator == null) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Filter/Exception.php';
            throw new IfwPsn_Vendor_Zend_Filter_Exception('"' . $separator . '" is not a valid separator.');
        }
        $this->_separator = $separator;
        return $this;
    }

    /**
     * Returns the actual set seperator
     *
     * @return  string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

}
