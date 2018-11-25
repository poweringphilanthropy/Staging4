<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Tries to reset the options set by the plugin
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: UninstallRemoveHooks.php 401 2015-02-22 22:52:23Z timoreithde $
 */
require_once dirname(__FILE__) . '/../UninstallInterface.php';

class IfwPsn_Wp_Plugin_Installer_Command_UninstallRemoveHooks implements IfwPsn_Wp_Plugin_Installer_UninstallInterface
{
    /**
     * @param IfwPsn_Wp_Plugin_Manager|null $pm
     * @return mixed|void
     */
    public static function execute($pm)
    {
        if (!($pm instanceof IfwPsn_Wp_Plugin_Manager)) {
            return;
        }

        require_once dirname(__FILE__) . '/../Task/RemoveHooks.php';
        $task = new IfwPsn_Wp_Plugin_Installer_Task_RemoveHooks($pm);
        $task->executeNetworkwide();
    }
}
