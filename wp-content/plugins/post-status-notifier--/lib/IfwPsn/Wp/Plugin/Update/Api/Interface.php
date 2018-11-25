<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Interface.php 471 2015-10-10 13:37:06Z timoreithde $
 * @package   
 */
interface IfwPsn_Wp_Plugin_Update_Api_Interface 
{
    /**
     * Request for plugin information
     *
     * @param $def
     * @param $action
     * @param $args
     * @return mixed
     */
    public function getPluginInformation($def, $action, $args);

    /**
     * @param $updateData
     * @return mixed
     */
    public function getUpdateData($updateData);

    /**
     * Fires at the end of the update message container in each row of the plugins list table.
     *
     * @param array $plugin_data An array of plugin data.
     * @param $meta_data
     */
    public function getUpdateInlineMessage($plugin_data, $meta_data);

    /**
     * Fires after plugin row in plugins manager
     *
     * @param array $plugin_data An array of plugin data.
     * @param $meta_data
     */
    public function afterPluginRow($plugin_data, $meta_data);

    /**
     * Activate license
     * @param $license
     * @param array $options
     * @return mixed
     */
    public function activate($license, array $options = array());

    /**
     * Deactivate license
     * @param $license
     * @param array $options
     * @return mixed
     */
    public function deactivate($license, array $options = array());

    /**
     * Get license status
     * @param $license
     * @param array $options
     * @return mixed
     */
    public function getLicenseStatus($license, array $options = array());

    /**
     * Get license expiry date
     * @param $license
     * @param array $options
     * @return mixed
     */
    public function getLicenseExpiryDate($license, array $options = array());

    /**
     * Checks if the license status is active
     * @param $license
     * @param array $options
     * @return mixed
     */
    public function isActiveLicense($license, array $options = array());
}
