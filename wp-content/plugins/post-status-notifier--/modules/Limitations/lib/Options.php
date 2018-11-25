<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Options.php 405 2015-08-24 22:17:41Z timoreithde $
 * @package
 */

class Psn_Module_Limitations_Options 
{
    protected $_pm;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    public function loadOptions()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Section.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Checkbox.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Text.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Select.php';

        $limitationsOptions = new IfwPsn_Wp_Options_Section('limits', __('Limitations', 'psn_lmt'),
            Psn_Admin_Options_Handler::getOptionsDescriptionBox(
                '<span class="dashicons dashicons-book"></span> ' .
                sprintf(__('Learn more about the "Limitations" feature in the <a %s>online documentation</a>.', 'psn_def'), 'href="'. $this->_pm->getConfig()->plugin->docUrl . 'limitations.html" target="_blank"'))

        );

        $limitationsOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'use_limitations',
                __('Activate', 'psn_lmt'),
                __('Activates the notification limitations functionality.<br>A new section "Limitations" will be available which gives insight in the limitation records.', 'psn_lmt')
            )
        );

        $limitationsOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'global_limitations',
                __('Global limitations', 'psn_lmt'),
                __('The notification limitations settings on this page will be used for all rules that have no custom limitations settings.', 'psn_lmt')
            )
        );

        $typeDefault = 'file';
        if ($this->_pm->hasOption('global_limitations_type')) {
            $typeDefault = $this->_pm->getOption('global_limitations_type');
        }

        $limitationsOptions->addField(new IfwPsn_Wp_Options_Field_Select(
            'global_limitations_type',
            __('Type', 'psn_lmt'),
            __('Select the limitation type.', 'psn_lmt'),
            array(
                'options' => array(
                    '0' => '-- ' . __('None', 'psn') . ' --',
                    Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE => Psn_Module_Limitations_Mapper::getLimitTypeLabel(Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE),
                    Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE_STATUS_AFTER => Psn_Module_Limitations_Mapper::getLimitTypeLabel(Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE_STATUS_AFTER),
                ),
                'optionsDefault' => $typeDefault
            )
        ));

        $limitationsOptions->addField(new IfwPsn_Wp_Options_Field_Text(
            'global_limitations_count',
            __('Limit count', 'psn_lmt'),
            __('Set the limit count. Numeric. Default: 1', 'psn_lmt'), array(
                'sanitizer' => 'number'
            )
        ));

        $this->_pm->getBootstrap()->getOptions()->addSection($limitationsOptions, 100);
    }
}
 