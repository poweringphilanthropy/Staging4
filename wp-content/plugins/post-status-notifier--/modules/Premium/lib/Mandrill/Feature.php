<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Feature.php 388 2015-05-26 18:44:33Z timoreithde $
 * @package
 */

class Psn_Module_Premium_Mandrill_Feature extends IfwPsn_Wp_Plugin_Feature_Abstract
{
    const API_KEY_SALT = 'mandrill.api.key';


    public function init()
    {
        if ($this->_pm->hasOption('psn_mandrill') && !$this->_pm->isEmptyOption('psn_mandrill_api_key')) {

            // init the Mandrill handler
            require_once $this->_module->getPathinfo()->getRootLib() . 'Mandrill/Handler.php';
            new Psn_Module_Premium_Mandrill_Handler($this->_pm);
        }

        add_filter('pre_update_option_psn_options', array($this, 'handleMandrillApiKey'), 10, 2);
    }

    public function load()
    {
        if ($this->_pm->getAccess()->isPlugin()) {
            add_action('psn_options_advanced', array($this, 'loadOptions'));
        }
    }

    /**
     * Register the Mandrill options
     * @param IfwPsn_Wp_Options_Section $sectionAdvanced
     */
    public function loadOptions(IfwPsn_Wp_Options_Section $sectionAdvanced)
    {
        $sectionAdvanced->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'psn_mandrill',
                __('Activate Mandrill', 'psn_prm') . sprintf('<br><a href="https://mandrill.com/" target="_blank"><img src="%s"></a>', $this->_module->getEnv()->getUrlImg() . 'mandrill_feature_small.png'),
                sprintf(__('Activate support for <a %s>Mandrill</a> API. All email notifications will be send via the Mandrill API.', 'psn_prm'), 'href="https://mandrill.com/" target="_blank"')
            )
        );

        $sectionAdvanced->addField(new IfwPsn_Wp_Options_Field_Password(
            'psn_mandrill_api_key',
            __('Mandrill API key', 'psn_prm'),
            __('Your Mandrill API key. Will be stored encrypted.', 'psn_prm')
        ));

        $sectionAdvanced->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'psn_mandrill_log',
                __('Mandrill log', 'psn_prm'),
                __('Log Mandrill API result. Creates log entries containing the Mandrill API response.', 'psn_prm')
            )
        );
    }

    /**
     * @param $new
     * @param $old
     * @return mixed
     */
    public function handleMandrillApiKey($new, $old)
    {
        if (!empty($new['psn_option_psn_mandrill_api_key']) && $new['psn_option_psn_mandrill_api_key'] != $old['psn_option_psn_mandrill_api_key']) {
            $new['psn_option_psn_mandrill_api_key'] = IfwPsn_Util_Encryption::encrypt(trim($new['psn_option_psn_mandrill_api_key']), self::API_KEY_SALT);
        }

        return $new;
    }
}
