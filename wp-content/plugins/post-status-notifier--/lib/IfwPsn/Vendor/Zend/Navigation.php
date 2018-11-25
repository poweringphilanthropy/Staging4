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
 * @category  Zend
 * @package   IfwPsn_Vendor_Zend_Navigation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Navigation.php 481 2015-11-03 13:28:23Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Navigation_Container
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Navigation/Container.php';

/**
 * A simple container class for {@link IfwPsn_Vendor_Zend_Navigation_Page} pages
 *
 * @category  Zend
 * @package   IfwPsn_Vendor_Zend_Navigation
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Navigation extends IfwPsn_Vendor_Zend_Navigation_Container
{
    /**
     * Creates a new navigation container
     *
     * @param array|IfwPsn_Vendor_Zend_Config $pages    [optional] pages to add
     * @throws IfwPsn_Vendor_Zend_Navigation_Exception  if $pages is invalid
     */
    public function __construct($pages = null)
    {
        if (is_array($pages) || $pages instanceof IfwPsn_Vendor_Zend_Config) {
            $this->addPages($pages);
        } elseif (null !== $pages) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Navigation/Exception.php';
            throw new IfwPsn_Vendor_Zend_Navigation_Exception(
                    'Invalid argument: $pages must be an array, an ' .
                    'instance of IfwPsn_Vendor_Zend_Config, or null');
        }
    }
}
