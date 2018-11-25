<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Handles update questions
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Manager.php 477 2015-10-16 22:07:10Z timoreithde $
 */ 
class IfwPsn_Wp_Plugin_Update_Manager
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Wp_Plugin_Update_Patcher
     */
    private $_patcher;

    /**
     * @var IfwPsn_Util_Version
     */
    private $_presentVersion;




    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Update/Patcher.php';
        $this->_patcher = new IfwPsn_Wp_Plugin_Update_Patcher($pm, $this->getPresentVersion());
    }

    public function init()
    {
        if ($this->_pm->getConfig()->plugin->autoupdate == 1) {

            $updateApi = IfwPsn_Wp_Plugin_Update_Api_Factory::get($this->_pm);

            // check for custom update message
            add_action('in_plugin_update_message-' . $this->_pm->getPathinfo()->getFilenamePath(), array($updateApi, 'getUpdateInlineMessage'), 10, 3);
            add_action('after_plugin_row_' . $this->_pm->getPathinfo()->getFilenamePath(), array($updateApi, 'afterPluginRow'), 10, 3);
            add_filter('pre_set_site_transient_update_plugins', array($updateApi, 'getUpdateData'));

            if ($this->_pm->isPremium()) {
                // check for premium get update info
                add_filter('plugins_api', array($updateApi, 'getPluginInformation'), 10, 3);
            }
        }

        $this->_pm->getBootstrap()->getOptionsManager()->registerExternalOption('present_version');
    }

    /**
     * @return IfwPsn_Wp_Plugin_Update_Patcher
     */
    public function getPatcher()
    {
        return $this->_patcher;
    }

    /**
     * @return IfwPsn_Util_Version
     */
    public function getPresentVersion()
    {
        if ($this->_presentVersion == null) {
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Util/Version.php';
            $this->_presentVersion = new IfwPsn_Util_Version($this->_pm->getBootstrap()->getOptionsManager()->getOption('present_version'));
        }

        return $this->_presentVersion;
    }

    /**
     * Updates the plugin's option "present_version" to current plugin version
     */
    public function refreshPresentVersion()
    {
        $this->_pm->getBootstrap()->getOptionsManager()->updateOption('present_version', $this->_pm->getEnv()->getVersion());
    }

}
