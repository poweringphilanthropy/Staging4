<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: WooCommerce.php 477 2015-10-16 22:07:10Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Plugin_Update_Api_WooCommerce extends IfwPsn_Wp_Plugin_Update_Api_Abstract
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    protected $_allowedEndpoints = array('am-software-api', 'upgrade-api');

    protected $_productId;

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $productId)
    {
        $this->_pm = $pm;
        $this->_productId = $productId;
    }

    /**
     * Request for plugin information
     *
     * @param $def
     * @param $action
     * @param $args
     * @return mixed
     */
    public function getPluginInformation($def, $action, $args)
    {
        // slug
        $pluginSlug = $this->_pm->getSlugFilenamePath();

        if (!isset($args->slug) || ($args->slug != $this->_pm->getSlug())) {
            // IMPORTANT:
            // this plugin is not responsible for this request
            // return def to not break other plugins
            return $def;
        }

        $result = '';

        // Get the current version
        $plugin_info = get_site_transient('update_plugins');

        if (!empty($this->_pm->getConfig()->debug->update)) {
            $this->_pm->getLogger()->debug('Plugin info check:');
            $this->_pm->getLogger()->debug(var_export($plugin_info, true));
        }

        $current_version = $plugin_info->checked[$pluginSlug];

        if (apply_filters('ifw_update_api_is_slug_activated-' . $pluginSlug, false)) {

            $activationData = apply_filters('ifw_update_api_get_activation_data-'. $pluginSlug, array());

            $request = $this->_getRequest('upgrade-api');

            if ($request instanceof IfwPsn_Wp_Http_Request) {
                $request
                    ->addData('request', 'plugininformation')
                    ->addData('plugin_name', $pluginSlug)
                    ->addData('version', $current_version)
                    ->addData('software_version', $current_version)
                    ->addData('activation_email', $activationData['email'])
                    ->addData('api_key', $activationData['license'])
                    ->addData('domain', $this->_getPlatform())
                    ->addData('instance', $this->_getInstance($activationData['license'], $activationData['email']));
            }

            $response = $request->send();

            if ($response->isSuccess()) {

                $responseBody = $response->getBody();
                $result = unserialize($responseBody);

                if ($result === false) {
                    $result = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
                }

            } else {

                $result = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="javascript:void(0)" onclick="document.location.reload(); return false;">Try again</a>'), $response->getErrorMessage());
            }

            if (!empty($this->_pm->getConfig()->debug->update)) {
                $this->_pm->getLogger()->debug(' --- Plugin info check response --- ');
                $this->_pm->getLogger()->debug(var_export($response, true));
            }
        } else {

            // plugin license is not activated
            // workaround for retrieving update information as long as API Manager requires authentication for this

            $request = new IfwPsn_Wp_Http_Request();

            $request->setUrl('http://update.ifeelweb.de/');
            $request->addData('api-key', md5(IfwPsn_Wp_Proxy_Blog::getUrl()));
            $request->addData('referrer', IfwPsn_Wp_Proxy_Blog::getUrl());

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $request->addData('browser_user_agent', $_SERVER['HTTP_USER_AGENT']);
            }

            $request
                ->addData('action', $action)
                ->addData('slug', $this->_pm->getSlug())
                ->addData('version', $current_version)
                ->addData('lang', IfwPsn_Wp_Proxy_Blog::getLanguage())
            ;

            $response = $request->send();

            if ($response->isSuccess()) {

                $responseBody = $response->getBody();
                $result = unserialize($responseBody);

                if ($result === false) {
                    $result = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
                }

            } else {

                $result = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="javascript:void(0)" onclick="document.location.reload(); return false;">Try again</a>'), $response->getErrorMessage());
            }

            if (!empty($this->_pm->getConfig()->debug->update)) {
                $this->_pm->getLogger()->debug(' --- Plugin info check response --- ');
                $this->_pm->getLogger()->debug(var_export($response, true));
            }

        }

        return $result;
    }

    /**
     * @param $updateData
     * @return mixed
     */
    public function getUpdateData($updateData)
    {
        // slug
        $pluginSlug = $this->_pm->getSlugFilenamePath();

        if (!is_plugin_active($pluginSlug) ||
            !$this->_pm->isPremium() ||
            !property_exists($updateData, 'checked') ||
            empty($updateData->checked) ) {
            return $updateData;
        }

        if (!empty($this->_pm->getConfig()->debug->update)) {
            $this->_pm->getLogger()->debug(' --- Update check data '. $pluginSlug . ' --- ');
            $this->_pm->getLogger()->debug(var_export($updateData, true));
        }

        if ((!property_exists($updateData, 'checked') || empty($updateData->checked)) &&
            (int)$this->_pm->getConfig()->plugin->updateTest == 0) {
            return $updateData;
        }

        if (apply_filters('ifw_update_api_is_slug_activated-' . $pluginSlug, false)) {

            $activationData = apply_filters('ifw_update_api_get_activation_data-'. $pluginSlug, array());
            $localVersion = $updateData->checked[$pluginSlug];

            $request = $this->_getRequest('upgrade-api');

            if ($request instanceof IfwPsn_Wp_Http_Request) {
                $request
                    ->addData('request', 'pluginupdatecheck')
                    ->addData('plugin_name', $pluginSlug)
                    ->addData('version', $localVersion)
                    ->addData('software_version', $localVersion)
                    ->addData('activation_email', $activationData['email'])
                    ->addData('api_key', $activationData['license'])
                    ->addData('domain', $this->_getPlatform())
                    ->addData('instance', $this->_getInstance($activationData['license'], $activationData['email']));
            }

            $response = $request->send();

            if ($response->isSuccess()) {

                $responseBody = $response->getBody();
                $remoteData = unserialize($responseBody);

                if (!empty($this->_pm->getConfig()->debug->update)) {
                    $this->_pm->getLogger()->debug('Update check response:');
                    $this->_pm->getLogger()->debug(var_export($remoteData, true));
                }

                if (is_object($remoteData) && !empty($remoteData) && isset($remoteData->new_version) && !empty($remoteData->new_version)) {

                    $remoteVersion = new IfwPsn_Util_Version((string)$remoteData->new_version);

                    if ($remoteVersion->isGreaterThan($localVersion)) {
                        // Feed the update data into WP updater
                        $updateData->response[$pluginSlug] = $remoteData;

                        delete_transient($this->_pm->getAbbrLower() . '_auto_update');
                    }
                }
            }
        } else {

            // plugin license is not activated
            // workaround for retrieving update information as long as API Manager requires authentication for this

            $request = new IfwPsn_Wp_Http_Request();

            $request->setUrl('http://update.ifeelweb.de/');
            $request->addData('api-key', md5(IfwPsn_Wp_Proxy_Blog::getUrl()));
            $request->addData('referrer', IfwPsn_Wp_Proxy_Blog::getUrl());

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $request->addData('browser_user_agent', $_SERVER['HTTP_USER_AGENT']);
            }

            $request
                ->addData('action', 'plugin_update_check')
                ->addData('slug', $this->_pm->getSlug())
                ->addData('version', $updateData->checked[$this->_pm->getPathinfo()->getFilenamePath()])
                ->addData('lang', IfwPsn_Wp_Proxy_Blog::getLanguage())
            ;

            $response = $request->send();

            if ($response->isSuccess()) {

                $responseBody = $response->getBody();
                $responseBody = unserialize($responseBody);

                if (!empty($this->_pm->getConfig()->debug->update)) {
                    $this->_pm->getLogger()->debug('Update check response:');
                    $this->_pm->getLogger()->debug(var_export($responseBody, true));
                }

                if (is_object($responseBody) && !empty($responseBody)) {
                    // Feed the update data into WP updater
                    $updateData->response[$this->_pm->getPathinfo()->getFilenamePath()] = $responseBody;
                }
            }
        }

        return $updateData;
    }

    /**
     * Fires at the end of the update message container in each row of the plugins list table.
     *
     * @param array $plugin_data An array of plugin data.
     * @param $meta_data
     */
    public function getUpdateInlineMessage($plugin_data, $meta_data)
    {
        // slug
        $pluginSlug = $this->_pm->getSlugFilenamePath();

        if ($this->_pm->isPremium()) {
            if (!apply_filters('ifw_update_api_is_slug_activated-' . $pluginSlug, false)) {

                if ($this->_pm->getAccess()->isNetworkAdmin()) {
                    $licensePage = network_admin_url($this->_pm->getConfig()->plugin->licensePageNetwork);
                } else {
                    $licensePage = admin_url($this->_pm->getConfig()->plugin->licensePage);
                }

                $wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
                echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange">
                        <div style="padding: 10px; background-color: #fcf3ef;">';

                printf('<span class="dashicons dashicons-info"></span> %s</div>',
                    sprintf( __('<b>License issue:</b> Please <a href="%s">active your license</a> to be able to receive updates.', 'ifw'), $licensePage) );

                echo '</td></tr>';
            }
        }
    }

    public function afterPluginRow($plugin_data, $meta_data)
    {
        // not used
    }

    /**
     * @param $license
     * @param array $options
     * @return IfwPsn_Wp_Http_Response|string
     */
    public function getLicenseStatus($license, array $options = array())
    {
        $result = '';
        $request = $this->_getRequest();

        if ($request instanceof IfwPsn_Wp_Http_Request) {
            $request
                ->addData('request', 'status')
                ->addData('licence_key', $license)
                ->addData('platform', $this->_getPlatform())
            ;
            if (isset($options['email'])) {
                $request->addData('email', $options['email']);
                $request->addData('instance', $this->_getInstance($license, $options['email']));
            }

            $response = $request->send();

            if ($response->isSuccess()) {

                $responseBody = trim($response->getBody());
                $result = json_decode($responseBody, true);
            }
        }

        return $result;
    }

    /**
     * @param $license
     * @param array $options
     * @return bool
     */
    public function isActiveLicense($license, array $options = array())
    {
        $status = $this->getLicenseStatus($license, $options);

        if (isset($status['status_check']) && $status['status_check'] == 'active') {
            return true;
        }

        return false;
    }

    /**
     * @param $license
     * @param array $options
     * @return bool
     */
    public function getLicenseExpiryDate($license, array $options = array())
    {
        $expiryDate = '';

        $status = $this->getLicenseStatus($license, $options);

        if (isset($status['status_extra']['subscription_data'])) {
            $subscription_data = $status['status_extra']['subscription_data'];
            if (isset($subscription_data['trial_expiry_date'])) {
                $expiryDate = $subscription_data['trial_expiry_date'];
            } elseif (isset($subscription_data['expiry_date'])) {
                $expiryDate = $subscription_data['expiry_date'];
            } elseif (isset($subscription_data['end_date'])) {
                $expiryDate = $subscription_data['end_date'];
            }

            if (!empty($expiryDate)) {
                $expiryDate = IfwPsn_Wp_Date::format($expiryDate);
            }
        }

        return $expiryDate;
    }

    /**
     * @param $license
     * @param array $options
     * @return IfwPsn_Wp_Http_Response|string
     */
    public function activate($license, array $options = array())
    {
        $response = '';
        $request = $this->_getRequest();

        if ($request instanceof IfwPsn_Wp_Http_Request) {
            $request
                ->addData('request', 'activation')
                ->addData('licence_key', $license)
                ->addData('platform', $this->_getPlatform())
            ;
            if (isset($options['email'])) {
                $request->addData('email', $options['email']);
                $request->addData('instance', $this->_getInstance($license, $options['email']));
            }
            if (isset($options['version'])) {
                $request->addData('software_version', $options['version']);
            }

            $response = $request->send();
        }

        return $response;
    }

    /**
     * @param $licence_key
     * @param array $options
     * @return IfwPsn_Wp_Http_Response|string
     * @internal param $email
     */
    public function deactivate($licence_key, array $options = array())
    {
        $response = '';
        $request = $this->_getRequest();

        if ($request instanceof IfwPsn_Wp_Http_Request) {
            $request
                ->addData('request', 'deactivation')
                ->addData('licence_key', $licence_key)
                ->addData('platform', $this->_getPlatform())
            ;
            if (isset($options['email'])) {
                $request->addData('email', $options['email']);
                $request->addData('instance', $this->_getInstance($licence_key, $options['email']));
            }

            $response = $request->send();
        }

        return $response;
    }

    /**
     * @return IfwPsn_Wp_Http_Request
     */
    protected function _getRequest($endpoint = 'am-software-api')
    {
        $result = null;

        if ($this->_isAllowedEndpoint($endpoint)) {

            $url = $this->_pm->getConfig()->plugin->updateServer;
            $url = add_query_arg('wc-api', $endpoint, $url);
            $url = esc_url_raw($url);

            $request = new IfwPsn_Wp_Http_Request();
            $request->setSendMethod('get');

            $request->setUrl($url);
            $request->addData('product_id', $this->_getProductId());

            $result = $request;
        }

        return $result;
    }

    /**
     * @param $endpoint
     * @return bool
     */
    protected function _isAllowedEndpoint($endpoint)
    {
        return in_array($endpoint, $this->_allowedEndpoints);
    }

    /**
     * A unique, password like hash. Unique for one activation on one platform.
     *
     * @param $licence_key
     * @param $email
     * @return string
     */
    protected function _getInstance($licence_key, $email)
    {
        $format = '%s/%s@%s';
        return md5(sprintf($format, $licence_key, $email, $this->_getPlatform()));
    }

    /**
     * @return string|void
     */
    protected function _getPlatform()
    {
        return IfwPsn_Wp_Proxy_Blog::getUrl();
    }

    protected function _getProductId()
    {
        return urlencode($this->_productId);
    }
}
