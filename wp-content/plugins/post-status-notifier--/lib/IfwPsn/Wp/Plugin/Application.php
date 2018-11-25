<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Plugin application class
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Application.php 300 2014-07-06 22:22:07Z timoreithde $
 * @package   IfwPsn_Wp_Plugin_Application
 */ 
class IfwPsn_Wp_Plugin_Application
{
    /**
     * @var IfwPsn_Wp_Plugin_Application_Adapter_Interface
     */
    private $_adapter;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    private $_pm;



    /**
     * @param IfwPsn_Wp_Plugin_Application_Adapter_Interface $adapter
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected function __construct(IfwPsn_Wp_Plugin_Application_Adapter_Interface $adapter, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_adapter = $adapter;
        $this->_pm = $pm;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return IfwPsn_Wp_Plugin_Application
     */
    public static function factory(IfwPsn_Wp_Plugin_Manager $pm)
    {
        // so far ZendFw is default
        // this should get refactored if other frameworks will be supported
        require_once $pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Application/Adapter/ZendFw.php';
        return new self(new IfwPsn_Wp_Plugin_Application_Adapter_ZendFw($pm), $pm);
    }

    /**
     * Loads the application
     */
    public function load()
    {
        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_before_application_load', $this);

        $this->_pm->getLogger()->logPrefixed('Trying to load application...');

        $this->_adapter->load();

        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_after_application_load', $this);
    }

    public function render()
    {
        $this->_adapter->render();
    }

    public function display()
    {
        $this->_adapter->display();
    }

    /**
     * @return \IfwPsn_Wp_Plugin_Application_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

}
