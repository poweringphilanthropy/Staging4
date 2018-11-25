<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Logs.php 394 2015-06-21 21:40:04Z timoreithde $
 */ 
class Psn_Module_Logger_Metabox_Logs extends IfwPsn_Wp_Plugin_Metabox_Ajax
{
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $ajaxRequest
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, $ajaxRequest = null)
    {
        $ajaxRequest = new Psn_Module_Logger_Metabox_LogsAjax();

        parent::__construct($pm, $ajaxRequest);
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initId()
     */
    protected function _initId()
    {
        return 'logs';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initTitle()
     */
    protected function _initTitle()
    {
        return __('Current log entries', 'ifw');
    }

    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initPriority()
     */
    protected function _initPriority()
    {
        return 'core';
    }
}
