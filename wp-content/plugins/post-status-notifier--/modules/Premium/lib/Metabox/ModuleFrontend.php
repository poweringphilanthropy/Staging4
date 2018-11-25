<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: ModuleFrontend.php 299 2014-08-17 18:05:37Z timoreithde $
 * @package
 */

require_once IFW_PSN_LIB_ROOT . '/IfwPsn/Wp/Plugin/Metabox/Abstract.php';

class Psn_Module_Premium_Metabox_ModuleFrontend extends IfwPsn_Wp_Plugin_Metabox_Abstract
{
    /**
     * @var IfwPsn_Wp_Module_Frontend
     */
    protected $_moduleFrontend;

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initId()
     */
    protected function _initId()
    {
        return 'module-manager';
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initTitle()
     */
    protected function _initTitle()
    {
        return __('Module Manager', 'psn');
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::_initPriority()
     */
    protected function _initPriority()
    {
        return 'core';
    }

    public function init()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Module/Frontend.php';
        $this->_moduleFrontend = new IfwPsn_Wp_Module_Frontend('psn_modules', $this->_pm);

        $this->_moduleFrontend->setController('service')
            ->setOptionsUrl('options-general.php?page=post-status-notifier&controller=service&appaction=index')
            ->setExtendingDocUrl($this->_pm->getConfig()->plugin->docUrl . 'extending_index.html')
            ->init();
    }

    /**
     * @see IfwPsn_Wp_Plugin_Admin_Menu_Metabox_Abstract::render()
     */
    public function render()
    {
        $this->_moduleFrontend->render();
    }
}
 