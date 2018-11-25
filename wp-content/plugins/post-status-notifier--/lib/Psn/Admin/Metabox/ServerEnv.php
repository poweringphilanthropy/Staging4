<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2012-2013 ifeelweb.de
 * @version   $Id: ServerEnv.php 204 2014-04-27 14:01:17Z timoreithde $
 * @package   
 */
require_once IFW_PSN_LIB_ROOT . '/IfwPsn/Wp/Plugin/Metabox/Abstract.php';

class Psn_Admin_Metabox_ServerEnv extends IfwPsn_Wp_Plugin_Metabox_Abstract
{
    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initId()
     */
    protected function _initId()
    {
        return 'server-env';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initTitle()
     */
    protected function _initTitle()
    {
        return __('Server environment', 'psn');
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initPriority()
     */
    protected function _initPriority()
    {
        return 'core';
    }

    /**
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::render()
     */
    public function render()
    {
        printf('<textarea id="ifw_server_env">%s</textarea>', IfwPsn_Wp_Proxy_Blog::getServerEnvironment($this->_pm));
    }
}
 