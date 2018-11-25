<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Manager.php 304 2014-07-27 17:29:16Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Options_Manager 
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    private $_pm;

    /**
     * @var array
     */
    private $_generalOptions = array();

    /**
     * @var array
     */
    private $_externalOptions = array();

    /**
     * @var array
     */
    private $_externalOptionsBuffer = array();



    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->_init();
    }

    protected function _init()
    {
        IfwPsn_Wp_Proxy_Action::add($this->_pm->getAbbrLower() . '_general_options_init', array($this, 'registerOptionsCallback'));
        IfwPsn_Wp_Proxy_Action::add($this->_pm->getAbbrLower() . '_external_options_init', array($this, 'registerOptionsCallback'));
    }

    /**
     * @param IfwPsn_Wp_Options_Field $option
     * @param int $priority
     * @return $this
     */
    public function addGeneralOption(IfwPsn_Wp_Options_Field $option, $priority = 100)
    {
        if (!isset($this->_generalOptions[$priority])) {
            $this->_generalOptions[$priority] = array();
        }

        array_push($this->_generalOptions[$priority], $option);

        return $this;
    }

    /**
     * Registered an option which should not appear on the options page
     *
     * @param string $id
     * @param int $priority
     * @return $this
     */
    public function registerExternalOption($id, $priority = 100)
    {
        if (!in_array($id, $this->_externalOptionsBuffer)) {

            array_push($this->_externalOptionsBuffer, $id);

            if (!isset($this->_externalOptions[$priority])) {
                $this->_externalOptions[$priority] = array();
            }

            array_push($this->_externalOptions[$priority], new IfwPsn_Wp_Options_Field_External($id, ''));
        }

        return $this;
    }

    /**
     * @param IfwPsn_Wp_Options_Section $section
     */
    public function registerOptionsCallback(IfwPsn_Wp_Options_Section $section)
    {
        switch ($section->getId()) {
            case 'general':
                $options = $this->_generalOptions;
                break;
            case 'external':
                $options = $this->_externalOptions;
                break;
        }

        ksort($options);

        foreach($options as $priority) {
            foreach ($priority as $option) {
                $section->addField($option);
            }
        }
    }

    /**
     * @param $id
     * @param $value
     * @return $this
     */
    public function updateOption($id, $value)
    {
        $options = IfwPsn_Wp_Proxy::getOption($this->_pm->getOptions()->getPageId());
        $options[$this->_pm->getOptions()->getOptionRealId($id)] = $value;
        IfwPsn_Wp_Proxy::updateOption($this->_pm->getOptions()->getPageId(), $options);

        return $this;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasOption($id)
    {
        $options = IfwPsn_Wp_Proxy::getOption($this->_pm->getOptions()->getPageId());
        return isset($options[$this->_pm->getOptions()->getOptionRealId($id)]);
    }

    /**
     * @param $id
     * @return bool
     */
    public function isEmptyOption($id)
    {
        $value = $this->getOption($id);
        return empty($value);
    }

    /**
     * @param $id
     * @return bool
     */
    public function isNotEmptyOption($id)
    {
        $value = $this->getOption($id);
        return !empty($value);
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public function getOption($id)
    {
        if ($this->hasOption($id)) {
            $options = IfwPsn_Wp_Proxy::getOption($this->_pm->getOptions()->getPageId());
            return $options[$this->_pm->getOptions()->getOptionRealId($id)];
        }

        return null;
    }
}
