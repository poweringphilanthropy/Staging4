<?php
/**
 * PSN menu options
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Options.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package   
 */ 
class Psn_Menu_Options extends IfwPsn_Wp_Plugin_Menu_Page_Options
{
    public function onInit()
    {
        $application = $this->_pm->getBootstrap()->getApplication();

        if ($application->getAdapter() instanceof IfwPsn_Wp_Plugin_Application_Adapter_ZendFw) {
            $application->getAdapter()->init();
        }
    }

    public function onLoad()
    {
        $application = $this->_pm->getBootstrap()->getApplication();

        if ($application->getAdapter() instanceof IfwPsn_Wp_Plugin_Application_Adapter_ZendFw) {
            IfwPsn_Wp_Proxy_Action::addAdminInit(array($application->getAdapter(), 'init'));
            IfwPsn_Wp_Proxy_Action::add('load-'. $this->getPageHook(), array($application, 'render'));
        }
    }

    public function handle()
    {
        $application = $this->_pm->getBootstrap()->getApplication();

        if ($application->getAdapter() instanceof IfwPsn_Wp_Plugin_Application_Adapter_ZendFw) {
            $application->display();
        }
    }

}
