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
 * @version    $Id: HeadTitle.php 481 2015-11-03 13:28:23Z timoreithde $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Standalone */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Container/Standalone.php';

/**
 * Helper for setting and retrieving title element for HTML head
 *
 * @uses       IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Standalone
 * @package    IfwPsn_Vendor_Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_View_Helper_HeadTitle extends IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Standalone
{
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'IfwPsn_Vendor_Zend_View_Helper_HeadTitle';

    /**
     * Whether or not auto-translation is enabled
     * @var boolean
     */
    protected $_translate = false;

    /**
     * Translation object
     *
     * @var IfwPsn_Vendor_Zend_Translate_Adapter
     */
    protected $_translator;

    /**
     * Default title rendering order (i.e. order in which each title attached)
     *
     * @var string
     */
    protected $_defaultAttachOrder = null;

    /**
     * Retrieve placeholder for title element and optionally set state
     *
     * @param  string $title
     * @param  string $setType
     * @return IfwPsn_Vendor_Zend_View_Helper_HeadTitle
     */
    public function headTitle($title = null, $setType = null)
    {
        if (null === $setType) {
            $setType = (null === $this->getDefaultAttachOrder())
                     ? IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract::APPEND
                     : $this->getDefaultAttachOrder();
        }
        $title = (string) $title;
        if ($title !== '') {
            if ($setType == IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract::SET) {
                $this->set($title);
            } elseif ($setType == IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract::PREPEND) {
                $this->prepend($title);
            } else {
                $this->append($title);
            }
        }

        return $this;
    }

    /**
     * Set a default order to add titles
     *
     * @param string $setType
     */
    public function setDefaultAttachOrder($setType)
    {
        if (!in_array($setType, array(
            IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract::APPEND,
            IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract::SET,
            IfwPsn_Vendor_Zend_View_Helper_Placeholder_Container_Abstract::PREPEND
        ))) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Exception.php';
            throw new IfwPsn_Vendor_Zend_View_Exception("You must use a valid attach order: 'PREPEND', 'APPEND' or 'SET'");
        }

        $this->_defaultAttachOrder = $setType;
        return $this;
    }

    /**
     * Get the default attach order, if any.
     *
     * @return mixed
     */
    public function getDefaultAttachOrder()
    {
        return $this->_defaultAttachOrder;
    }

    /**
     * Sets a translation Adapter for translation
     *
     * @param  IfwPsn_Vendor_Zend_Translate|IfwPsn_Vendor_Zend_Translate_Adapter $translate
     * @return IfwPsn_Vendor_Zend_View_Helper_HeadTitle
     */
    public function setTranslator($translate)
    {
        if ($translate instanceof IfwPsn_Vendor_Zend_Translate_Adapter) {
            $this->_translator = $translate;
        } elseif ($translate instanceof IfwPsn_Vendor_Zend_Translate) {
            $this->_translator = $translate->getAdapter();
        } else {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/View/Exception.php';
            $e = new IfwPsn_Vendor_Zend_View_Exception("You must set an instance of IfwPsn_Vendor_Zend_Translate or IfwPsn_Vendor_Zend_Translate_Adapter");
            $e->setView($this->view);
            throw $e;
        }
        return $this;
    }

    /**
     * Retrieve translation object
     *
     * If none is currently registered, attempts to pull it from the registry
     * using the key 'IfwPsn_Vendor_Zend_Translate'.
     *
     * @return IfwPsn_Vendor_Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if (null === $this->_translator) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Registry.php';
            if (IfwPsn_Vendor_Zend_Registry::isRegistered('IfwPsn_Vendor_Zend_Translate')) {
                $this->setTranslator(IfwPsn_Vendor_Zend_Registry::get('IfwPsn_Vendor_Zend_Translate'));
            }
        }
        return $this->_translator;
    }

    /**
     * Enables translation
     *
     * @return IfwPsn_Vendor_Zend_View_Helper_HeadTitle
     */
    public function enableTranslation()
    {
        $this->_translate = true;
        return $this;
    }

    /**
     * Disables translation
     *
     * @return IfwPsn_Vendor_Zend_View_Helper_HeadTitle
     */
    public function disableTranslation()
    {
        $this->_translate = false;
        return $this;
    }

    /**
     * Turn helper into string
     *
     * @param  string|null $indent
     * @param  string|null $locale
     * @return string
     */
    public function toString($indent = null, $locale = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $items = array();

        if($this->_translate && $translator = $this->getTranslator()) {
            foreach ($this as $item) {
                $items[] = $translator->translate($item, $locale);
            }
        } else {
            foreach ($this as $item) {
                $items[] = $item;
            }
        }

        $separator = $this->getSeparator();
        $output = '';
        if(($prefix = $this->getPrefix())) {
            $output  .= $prefix;
        }
        $output .= implode($separator, $items);
        if(($postfix = $this->getPostfix())) {
            $output .= $postfix;
        }

        $output = ($this->_autoEscape) ? $this->_escape($output) : $output;

        return $indent . '<title>' . $output . '</title>';
    }
}
