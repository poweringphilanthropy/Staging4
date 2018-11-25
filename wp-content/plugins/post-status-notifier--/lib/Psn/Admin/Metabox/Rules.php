<?php
/**
 *
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: Rules.php 394 2015-06-21 21:40:04Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Admin
 */
class Psn_Admin_Metabox_Rules extends IfwPsn_Wp_Plugin_Metabox_Ajax
{
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $ajaxRequest
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $ajaxRequest = null)
    {
        $ajaxRequest = new Psn_Admin_Metabox_RulesAjax();

        parent::__construct($pm, $ajaxRequest);
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initId()
     */
    protected function _initId()
    {
        return 'rules';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initTitle()
     */
    protected function _initTitle()
    {
        return __('Your notification rules', 'psn');
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initPriority()
     */
    protected function _initPriority()
    {
        return 'core';
    }
}
