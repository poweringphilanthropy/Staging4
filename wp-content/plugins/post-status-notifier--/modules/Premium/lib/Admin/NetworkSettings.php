<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: NetworkSettings.php 434 2015-10-30 18:09:36Z timoreithde $
 * @package
 */
class Psn_Module_Premium_Admin_NetworkSettings
{
    protected static $_instance;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    private $_pm;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return Psn_Module_Premium_Admin_NetworkSettings
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($pm);
        }
        return self::$_instance;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    private function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    public function registerPage()
    {
        add_submenu_page('settings.php', $this->_pm->getEnv()->getName(), $this->_pm->getEnv()->getName(), 'manage_options', $this->_pm->getSlug(), array($this, 'render'));
    }

    public function render()
    {
        $formNonce = 'psn_network_settings';

        if (count($_POST) > 0) {
            check_admin_referer($formNonce);

            if (isset($_POST['psn_license'])) {
                $license = sanitize_text_field($_POST['psn_license']);
                Psn_Module_Premium_License::getInstance($this->_pm)->saveLicense($license);
            }
        }

        $module = $this->_pm->getBootstrap()->getModuleManager()->getModule('psn_mod_prm');

        $licenseNotice = Psn_Admin_Options_Handler::getOptionsDescriptionBox(
            '<span class="dashicons dashicons-info"></span> ' .
            sprintf(__('Please insert your PSN license code here to be able to receive updates. You get your license code in the <a %s>CodeCanyon "Downloads" section</a>. Click the "Download" button and select "License certificate & purchase code".', 'psn_prm'), 'href="http://codecanyon.net/downloads" target="_blank"') . '<br><br>' .
            sprintf( '<img src="%s" class="options_teaser">', $module->getEnv()->getUrlImg() . 'license.png')
        );

        $options = array(
            'nonce_field' => wp_nonce_field($formNonce),
            'license_notice' => $licenseNotice,
            'license_default' => Psn_Module_Premium_License::getInstance($this->_pm)->getEncryptedLicense(),
            'license_help' => sprintf( __('Insert your CodeCanyon license code for this plugin here to be able to use the auto-update feature. Refer to the <a href="%s" target="_blank">documentation</a> for details.', 'psn_prm'),
                'http://docs.ifeelweb.de/post-status-notifier/licensing.html')
        );

        IfwPsn_Wp_Tpl::getFilesytemInstance($this->_pm)->display('network_settings.twig.html', $options);
    }
}
