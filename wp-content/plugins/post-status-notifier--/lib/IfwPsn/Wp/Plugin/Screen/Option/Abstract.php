<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 179 2013-07-08 21:00:14Z timoreithde $
 */ 
abstract class IfwPsn_Wp_Plugin_Screen_Option_Abstract
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var string
     */
    protected $_type;

    /**
     * @var
     */
    protected $_optionValue;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        if (IfwPsn_Wp_Proxy_Blog::isMinimumVersion('3.1')) {
            // screen functionality supported since WP 3.1
            $this->_init();
        }
    }

    protected function _init()
    {
        if (IfwPsn_Wp_Proxy_Screen::isLoadedCurrentScreen()) {
            $this->_pm->getLogger()->error('Screen option must be initialized before page load.');
        }

        $this->_type = $this->getType();

        IfwPsn_Wp_Proxy_Filter::addSetScreenOption(array($this, 'setScreenOptionCallback'), 10, 3);
        IfwPsn_Wp_Proxy_Action::addCurrentScreen(array($this, 'registerOption'));
    }

    /**
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function setScreenOptionCallback($status, $option, $value)
    {
        return $value;
    }

    /**
     * On WP action current_screen
     */
    public function registerOption()
    {
        $this->_register();
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        if ($this->_optionValue === null) {
            $option = IfwPsn_Wp_Proxy_Screen::getOption('per_page', 'option');

            $value = IfwPsn_Wp_Proxy_User::getCurrentUserMetaSingle($option);

            if (method_exists($this, '_getOptionCallback')) {
                $value = $this->_getOptionCallback($value);
            }

            $this->_optionValue = $value;
        }

        return $this->_optionValue;
    }

    /**
     * @return bool
     */
    public function hasOption()
    {
        return $this->getOption() !== null;
    }

    /**
     * @return mixed
     */
    abstract public function getType();

    /**
     * @return mixed
     */
    abstract protected function _register();
}
