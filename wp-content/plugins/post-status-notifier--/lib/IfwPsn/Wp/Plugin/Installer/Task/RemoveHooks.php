<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: RemoveHooks.php 401 2015-02-22 22:52:23Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Plugin_Installer_Task_RemoveHooks extends IfwPsn_Wp_Network_Task
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    /**
     * The task to be executed
     * @return mixed
     */
    protected function _execute()
    {
        $updateApi = IfwPsn_Wp_Plugin_Update_Api_Factory::get($this->_pm);

        remove_action('in_plugin_update_message-' . $this->_pm->getPathinfo()->getFilenamePath(), array($updateApi, 'getUpdateInlineMessage'));
        remove_filter('pre_set_site_transient_update_plugins', array($updateApi, 'getUpdateData'));
        remove_filter('plugins_api', array($updateApi, 'getPluginInformation'));
    }
}
