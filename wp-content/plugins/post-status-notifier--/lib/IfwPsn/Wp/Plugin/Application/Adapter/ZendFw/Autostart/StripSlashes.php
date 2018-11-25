<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Strip slashes from $_POST values
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: StripSlashes.php 233 2014-03-17 23:46:37Z timoreithde $
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_StripSlashes extends IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_Abstract
{
    public function execute()
    {
        IfwPsn_Wp_Proxy_Action::addWpLoaded(array($this, 'stripslashes'));
    }

    public function stripslashes()
    {
        $_POST = array_map('stripslashes_deep', $_POST);
    }

}
