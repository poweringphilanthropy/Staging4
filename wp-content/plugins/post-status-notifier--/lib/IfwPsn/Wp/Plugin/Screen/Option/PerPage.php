<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PerPage.php 373 2014-12-28 23:59:13Z timoreithde $
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Screen_Option_PerPage extends IfwPsn_Wp_Plugin_Screen_Option_Abstract
{
    /**
     * @var array
     */
    public static $_instances = array();

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var string
     */
    protected $_optionName;

    /**
     * @var string
     */
    protected $_default;

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $label
     * @param null $optionName
     * @param null $default
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $label = null, $optionName = null, $default = null)
    {
        if ($label !== null) {
            $this->_label = $label;
        }
        if ($optionName !== null) {
            $this->_optionName = $optionName;
        }
        if ($default !== null) {
            $this->_default = $default;
        }

        parent::__construct($pm);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $label
     * @param null $optionName
     * @param null $default
     * @return mixed
     */
    public static function init(IfwPsn_Wp_Plugin_Manager $pm, $label = null, $optionName = null, $default = null)
    {
        if (!isset(self::$_instances[$optionName])) {
            self::$_instances[$optionName] = new self($pm, $label, $optionName, $default);
        }
        return self::$_instances[$optionName];
    }

    /**
     * @param $optionName
     * @return IfwPsn_Wp_Plugin_Screen_Option_PerPage|null
     */
    public static function getInstance($optionName)
    {
        if (isset(self::$_instances[$optionName])) {
            return self::$_instances[$optionName];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return 'per_page';
    }

    /**
     * Registers the option
     * @throws IfwPsn_Wp_Plugin_Screen_Exception
     */
    protected function _register()
    {
        $label = $this->getLabel();
        if (empty($label)) {
            throw new IfwPsn_Wp_Plugin_Screen_Exception('Missing screen option label');
        }
        $optionName = $this->getOptionName();
        if (empty($optionName)) {
            throw new IfwPsn_Wp_Plugin_Screen_Exception('Missing screen option name');
        }

        if ($this->getDefault() !== null) {
            IfwPsn_Wp_Proxy_Screen::addOptionPerPage($label, $optionName, $this->getDefault());
        } else {
            IfwPsn_Wp_Proxy_Screen::addOptionPerPage($label, $optionName);
        }
    }

    /**
     * @param $value
     * @return null
     */
    protected function _getOptionCallback($value)
    {
        if (empty($value) || $value < 1) {
            $value = IfwPsn_Wp_Proxy_Screen::getOption('per_page', 'default');
        }
        return (int)$value;
    }

    public function setDefault($default)
    {
        $this->_default = $default;
    }

    public function getDefault()
    {
        return $this->_default;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setOptionName($optionName)
    {
        $this->_optionName = $optionName;
    }

    public function getOptionName()
    {
        return $this->_optionName;
    }

}
