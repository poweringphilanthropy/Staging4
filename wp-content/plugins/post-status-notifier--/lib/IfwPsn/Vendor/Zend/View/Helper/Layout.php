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
 * @version    $Id: Layout.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_View_Helper_Abstract.php */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Abstract.php';

/**
 * View helper for retrieving layout object
 *
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_Layout extends IfwPsn_Vendor_Zend_View_Helper_Abstract
{
    /** @var IfwPsn_Vendor_Zend_Layout */
    protected $_layout;

    /**
     * Get layout object
     *
     * @return IfwPsn_Vendor_Zend_Layout
     */
    public function getLayout()
    {
        if (null === $this->_layout) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Layout.php';
            $this->_layout = IfwPsn_Vendor_Zend_Layout::getMvcInstance();
            if (null === $this->_layout) {
                // Implicitly creates layout object
                $this->_layout = new IfwPsn_Vendor_Zend_Layout();
            }
        }

        return $this->_layout;
    }

    /**
     * Set layout object
     *
     * @param  IfwPsn_Vendor_Zend_Layout $layout
     * @return IfwPsn_Vendor_Zend_Layout_Controller_Action_Helper_Layout
     */
    public function setLayout(IfwPsn_Vendor_Zend_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Return layout object
     *
     * Usage: $this->layout()->setLayout('alternate');
     *
     * @return IfwPsn_Vendor_Zend_Layout
     */
    public function layout()
    {
        return $this->getLayout();
    }
}
