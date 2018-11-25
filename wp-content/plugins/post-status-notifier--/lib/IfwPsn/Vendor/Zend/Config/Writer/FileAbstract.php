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
 * @package    Writer
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once IFW_PSN_LIB_ROOT . "IfwPsn/Vendor/Zend/Config/Writer.php";

/**
 * Abstract File Writer
 *
 * @category   Zend
 * @package    IfwPsn_Vendor_Zend_package
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FileAbstract.php 486 2015-11-11 22:30:55Z timoreithde $
 */
class IfwPsn_Vendor_Zend_Config_Writer_FileAbstract extends IfwPsn_Vendor_Zend_Config_Writer
{
    /**
     * Filename to write to
     *
     * @var string
     */
    protected $_filename = null;

    /**
     * Wether to exclusively lock the file or not
     *
     * @var boolean
     */
    protected $_exclusiveLock = false;

    /**
     * Set the target filename
     *
     * @param  string $filename
     * @return IfwPsn_Vendor_Zend_Config_Writer_Array
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;

        return $this;
    }

    /**
     * Set wether to exclusively lock the file or not
     *
     * @param  boolean     $exclusiveLock
     * @return IfwPsn_Vendor_Zend_Config_Writer_Array
     */
    public function setExclusiveLock($exclusiveLock)
    {
        $this->_exclusiveLock = $exclusiveLock;

        return $this;
    }

    /**
     * Write configuration to file.
     *
     * @param string $filename
     * @param IfwPsn_Vendor_Zend_Config $config
     * @param bool $exclusiveLock
     * @return void
     */
    public function write($filename = null, IfwPsn_Vendor_Zend_Config $config = null, $exclusiveLock = null)
    {
        if ($filename !== null) {
            $this->setFilename($filename);
        }

        if ($config !== null) {
            $this->setConfig($config);
        }

        if ($exclusiveLock !== null) {
            $this->setExclusiveLock($exclusiveLock);
        }

        if ($this->_filename === null) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Config/Exception.php';
            throw new IfwPsn_Vendor_Zend_Config_Exception('No filename was set');
        }

        if ($this->_config === null) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Config/Exception.php';
            throw new IfwPsn_Vendor_Zend_Config_Exception('No config was set');
        }

        $configString = $this->render();

        $flags = 0;

        if ($this->_exclusiveLock) {
            $flags |= LOCK_EX;
        }

        $result = @file_put_contents($this->_filename, $configString, $flags);

        if ($result === false) {
            require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Zend/Config/Exception.php';
            throw new IfwPsn_Vendor_Zend_Config_Exception('Could not write to file "' . $this->_filename . '"');
        }
    }

    /**
     * Render a IfwPsn_Vendor_Zend_Config into a config file string.
     *
     * @since 1.10
     * @todo For 2.0 this should be redone into an abstract method.
     * @return string
     */
    public function render()
    {
        return "";
    }
}
