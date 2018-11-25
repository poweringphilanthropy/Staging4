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
 * @package    IfwPsn_Vendor_Zend_Config
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Json.php 269 2014-04-25 23:29:54Z timoreithde $
 */

/**
 * @see IfwPsn_Vendor_Zend_Config_Writer
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Config/Writer/FileAbstract.php';

/**
 * @see IfwPsn_Vendor_Zend_Config_Json
 */
require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Config/Json.php';

/**
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_Config
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IfwPsn_Vendor_Zend_Config_Writer_Json extends IfwPsn_Vendor_Zend_Config_Writer_FileAbstract
{
    /**
     * If we need to pretty-print JSON data
     *
     * @var boolean
     */
    protected $_prettyPrint = false;

    /**
     * Get prettyPrint flag
     *
     * @return the prettyPrint flag
     */
    public function prettyPrint()
    {
        return $this->_prettyPrint;
    }

    /**
     * Set prettyPrint flag
     *
     * @param  bool $prettyPrint PrettyPrint flag
     * @return IfwPsn_Vendor_Zend_Config_Writer_Json
     */
    public function setPrettyPrint($flag)
    {
        $this->_prettyPrint = (bool) $flag;
        return $this;
    }

    /**
     * Render a IfwPsn_Vendor_Zend_Config into a JSON config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $data        = $this->_config->toArray();
        $sectionName = $this->_config->getSectionName();
        $extends     = $this->_config->getExtends();

        if (is_string($sectionName)) {
            $data = array($sectionName => $data);
        }

        foreach ($extends as $section => $parentSection) {
            $data[$section][IfwPsn_Vendor_Zend_Config_Json::EXTENDS_NAME] = $parentSection;
        }

        // Ensure that each "extends" section actually exists
        foreach ($data as $section => $sectionData) {
            if (is_array($sectionData) && isset($sectionData[IfwPsn_Vendor_Zend_Config_Json::EXTENDS_NAME])) {
                $sectionExtends = $sectionData[IfwPsn_Vendor_Zend_Config_Json::EXTENDS_NAME];
                if (!isset($data[$sectionExtends])) {
                    // Remove "extends" declaration if section does not exist
                    unset($data[$section][IfwPsn_Vendor_Zend_Config_Json::EXTENDS_NAME]);
                }
            }
        }

        $out = IfwPsn_Vendor_Zend_Json::encode($data);
        if ($this->prettyPrint()) {
             $out = IfwPsn_Vendor_Zend_Json::prettyPrint($out);
        }
        return $out;
    }
}
