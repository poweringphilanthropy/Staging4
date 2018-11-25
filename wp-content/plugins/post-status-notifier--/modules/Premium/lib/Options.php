<?php
/**
 * Premium options
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Options.php 430 2015-10-30 12:08:41Z timoreithde $
 */ 
class Psn_Module_Premium_Options 
{
    const LICENSE_CODE_SALT = 'psn.license.key';

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    protected $_module;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, IfwPsn_Wp_Module_Bootstrap_Abstract $module)
    {
        $this->_pm = $pm;
        $this->_module = $module;
    }

    public function load()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Section.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Checkbox.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Textarea.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Text.php';



        /**
         * License options
         */
        $this->_pm->getBootstrap()->getOptionsManager()->registerExternalOption('license_code');

        /*add_filter('pre_update_option_psn_options', array($this, 'handleLicenseKey'), 10, 2);

        $licenseOptions = new IfwPsn_Wp_Options_Section('license', __('License', 'psn'),
            Psn_Admin_Options_Handler::getOptionsDescriptionBox(
                '<span class="dashicons dashicons-info"></span> ' .
            sprintf(__('Please insert your PSN license code here. You get your license code in the <a %s>CodeCanyon "Downloads" section</a>. Click the "Download" button and select "License certificate & purchase code".', 'psn_prm'), 'href="http://codecanyon.net/downloads" target="_blank"') . ' ' .
            sprintf( '<img src="%s" class="options_teaser">', $this->_module->getEnv()->getUrlImg() . 'license.png')
            )
        );

        $licenseOptions->addField(new IfwPsn_Wp_Options_Field_Password(
            'license_code',
            __('Premium license code', 'psn_prm'),
            sprintf( __('Insert your CodeCanyon license code for this plugin here to be able to use the auto-update feature. Refer to the <a href="%s" target="_blank">documentation</a> for details.', 'psn_prm'),
                'http://docs.ifeelweb.de/post-status-notifier/options.html#premium-license-code')
        ));

        $this->_pm->getBootstrap()->getOptions()->addSection($licenseOptions, 11);*/


        /**
         * General options
         */

        $this->_pm->getBootstrap()->getOptionsManager()->addGeneralOption(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_deactivate_copied_rules',
            __('Deactivate rule on copy', 'psn'),
            __('Always deactivate copied rules', 'psn')
        ));

        $this->_pm->getBootstrap()->getOptionsManager()->addGeneralOption(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_late_execution',
            __('Late execution', 'psn'),
            __('Notifications will be processed on WordPress shutdown action. Try this if you are having issues with custom fields which get added after post save. May not work with webhosters who do not allow sending emails on PHP shutdown function.', 'psn')
        ));


        /**
         * Email options
         */
        $emailOptions = new IfwPsn_Wp_Options_Section('email', __('Email', 'psn'));
        $emailOptions->addField(new IfwPsn_Wp_Options_Field_Text(
            'psn_default_from',
            __('Default FROM', 'psn_prm'),
            __('Here you can define an email address which will be used as sender for the notifications. Leave this blank to use the e-mail address configured in Settings / General as sender. Note that you can define a custom sender for each rule.', 'psn_prm') . '<br>' .
            str_replace(array('<', '>'), array('&lt;', '&gt;'), __('Format: Sender Name <sender@domain.com>', 'psn_prm')) . '<br>' .
            __('Default', 'psn_rec') . ': ' . str_replace(array('<', '>'), array('&lt;', '&gt;'), IfwPsn_Wp_Proxy_Blog::getDefaultEmailFrom()),
            array(
                'placeholder' => 'Sender Name <sender@domain.com>',
                'sanitizer' => 'esc_attr'
            )
        ));

        $this->_pm->getBootstrap()->getOptions()->addSection($emailOptions, 30);


        /**
         * Blocker options
         */
        $blockerOptions = new IfwPsn_Wp_Options_Section('blocker', __('Blocking', 'psn'),
            sprintf( __('Options for the "Block notifications" feature in the post submit box.' . '<img src="%s" class="options_teaser">', 'psn'), $this->_module->getEnv()->getUrlImg() . 'blocker_feature.png')
        );

        $blockerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_disable_submitbox_block',
            __('Disable it completely', 'psn'),
            __('Disable the "Block notifications" feature in the Post submit box.', 'psn')
        ));

        $blockerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_submitbox_block_admins_only',
            __('For admins only', 'psn'),
            __('Enable the "Block notifications" feature for admins only.', 'psn')
        ));

        $blockerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_submitbox_block_selected_by_default',
            __('Selected by default', 'psn_prm'),
            sprintf( __('This will check the block feature by default meaning that <b %s>no notifications will be sent</b> unless you deselect the checkbox before saving or updating a post.', 'psn_prm'), ' style="color: red;"')
        ));

        $this->_pm->getBootstrap()->getOptions()->addSection($blockerOptions, 70);


        /**
         * Conditions options
         */
        $conditionsDoc = $this->_pm->getConfig()->plugin->docUrl . 'conditional_templates.html';

        $conditionsOptions = new IfwPsn_Wp_Options_Section('conditions', __('Conditions', 'psn_prm'),
            Psn_Admin_Options_Handler::getOptionsDescriptionBox(
                '<span class="dashicons dashicons-book"></span> ' .
                sprintf(__('Learn more about the "Conditions" feature in the <a %s>online documentation</a>.', 'psn_def'), 'href="'. $this->_pm->getConfig()->plugin->docUrl . 'conditional_templates.html" target="_blank"')) .

            sprintf(
                __('If you want to use <b>PSN\'s conditions syntax</b> in your notification texts, you can activate this feature here.', 'psn') . '<img src="%s" class="options_teaser">',
                $this->_module->getEnv()->getUrlImg() . 'conditions_example.png'
            )
        );

        $conditionsOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'psn_conditions_enable_subject',
                __('Enable for subject', 'psn_prm'),
                sprintf(__('Enables support for conditions, loops and filters for email subject. Check the <a href="%s" target="_blank">Conditions manual</a> to learn more about it.', 'psn_prm'), $conditionsDoc)
            )
        );
        $conditionsOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'psn_conditions_enable_body',
                __('Enable for body', 'psn_prm'),
                sprintf(__('Enables support for conditions, loops and filters for email body. Check the <a href="%s" target="_blank">Conditions manual</a> to learn more about it.', 'psn_prm'), $conditionsDoc)
            )
        );
        $conditionsOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'psn_conditions_enable_dyn_to',
                __('Enable dynamic recipients', 'psn_prm'),
                sprintf(__('Enables support for dynamic recipients. Adds a new text field in the rule form. Check the <a href="%s" target="_blank">Dynamic recipients manual</a> to learn more about it.', 'psn_prm'), $this->_pm->getConfig()->plugin->docUrl . 'dynamic_recipients.html')
            )
        );
        $conditionsOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'psn_conditions_log',
                __('Log errors', 'psn_prm'),
                __('Create error logs if an error occurs while parsing the template code. Helps debugging the conditions syntax.', 'psn_prm')
            )
        );

        $this->_pm->getBootstrap()->getOptions()->addSection($conditionsOptions, 50);

    }

    /**
     * @param $new
     * @param $old
     * @return mixed
     */
    public function handleLicenseKey($new, $old)
    {
        $optionName = 'psn_option_license_code';

        if (isset($new[$optionName]) && !empty($new[$optionName]) && ($new[$optionName] != $old[$optionName] || !IfwPsn_Util_Encryption::isEncryptedString($new[$optionName]))) {
            $new[$optionName] = IfwPsn_Util_Encryption::encrypt(trim($new[$optionName]), self::LICENSE_CODE_SALT);
        }

        return $new;
    }
}
