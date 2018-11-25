<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Handler.php 400 2015-08-18 20:15:45Z timoreithde $
 * @package
 */

class Psn_Admin_Options_Handler 
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
     *
     */
    public function load()
    {
        // set tabbed options page renderer
        $this->_pm->getBootstrap()->getOptions()->setRenderer(new IfwPsn_Wp_Options_Renderer_Tabs($this->_pm));

        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Section.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Checkbox.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Text.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Textarea.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Select.php';

        $this->_addOptions();
    }

    protected function _addOptions()
    {
        $this->_pm->getOptionsManager()->addGeneralOption(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_ignore_status_inherit',
            __('Ignore post status "inherit"', 'psn'),
            __('Status "inherit" is used when post revisions get created by WordPress automatically', 'psn')
        ));
        $this->_pm->getOptionsManager()->addGeneralOption(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_hide_nonpublic_posttypes',
            __('Hide non-public post types', 'psn'),
            __('When selected, non-public post types will be excluded from rule settings form', 'psn')
        ));

        if (!$this->_pm->isPremium()) {
            $smtpOptions = new IfwPsn_Wp_Options_Section('smtp', __('SMTP', 'psn_smtp'));
            $smtpOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'smtp_teaser',
                __('Activate SMTP', 'psn'),
                __('SMTP is a premium feature. You will get all configuration options to connect to your SMTP server.', 'psn')
            ));
            $this->_pm->getBootstrap()->getOptions()->addSection($smtpOptions, 12);
        }

        $placeholderFilterOptions = new IfwPsn_Wp_Options_Section('placeholders', __('Placeholders', 'psn'));

        $placeholderFilterOptions->addField(new IfwPsn_Wp_Options_Field_Textarea(
            'placeholders_filters',
            __('Placeholders filters', 'psn'),
            sprintf( __('Here you can define filters which will apply to the placeholders contents (One filter per line). You can use the <a href="%s" target="_blank">Twig filters</a>. Refer to the <a href="%s" target="_blank">documentation</a> for details.<br>Example: [post_date]|date("m/d/Y")', 'psn_smtp'),
                'http://twig.sensiolabs.org/doc/filters/index.html',
                'http://docs.ifeelweb.de/post-status-notifier/options.html#placeholders')
        ));

        $this->_pm->getBootstrap()->getOptions()->addSection($placeholderFilterOptions, 300);

        // Advanced
        $advancedOptions = new IfwPsn_Wp_Options_Section('advanced', __('Advanced', 'psn'));

        do_action('psn_options_advanced', $advancedOptions);

        if (function_exists('apc_clear_cache')) {
            // APC 502 bad gateway workaround
            $advancedOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'apc_clear_cache',
                __('APC clear cache', 'psn'),
                __('In case you are facing issues when updating rules or email templates (blank page, 502 Bad Gateway on nginx), please activate this option and try again. Or ask your webhost to deactivate APC. This option will deactivate the APC cache on PSN admin pages.', 'psn')
            ));
        }

        $this->_pm->getBootstrap()->getOptions()->addSection($advancedOptions, 400);
    }

    /**
     * @param $text
     * @return string
     */
    public static function getOptionsDescriptionBox($text)
    {
        $format = '<div class="psn_options_description">%s</div>';
        return sprintf($format, $text);
    }
}
