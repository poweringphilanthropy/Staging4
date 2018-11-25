<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Strip slashes from $_POST values
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: EnqueueScripts.php 376 2014-12-29 17:01:10Z timoreithde $
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_EnqueueScripts extends IfwPsn_Wp_Plugin_Application_Adapter_ZendFw_Autostart_Abstract
{
    public function execute()
    {
        $this->loadCss();
        $this->loadJs();
        $this->loadSkin();
    }

    /**
     *
     */
    public function loadJs()
    {
        $files = array('admin.js');

        if ($this->_pm->isProduction()) {
            $files = array_merge(array('admin.min.js'), $files);
        }

        foreach ($files as $adminJsFile) {
            if (file_exists($this->_pm->getPathinfo()->getRootAdminJs() . $adminJsFile)) {
                $handle = $this->_pm->getAbbrLower() . '-' . 'admin-js';
                IfwPsn_Wp_Proxy_Script::loadAdmin($handle, $this->_pm->getEnv()->getUrlAdminJs() . $adminJsFile, array(), $this->_pm->getEnv()->getVersion());
                break;
            }
        }
    }

    /**
     *
     */
    public function loadCss()
    {
        $files = array('admin.css');

        if ($this->_pm->isProduction()) {
            $files = array_merge(array('admin.min.css'), $files);
        }

        foreach ($files as $adminCssFile) {
            if (file_exists($this->_pm->getPathinfo()->getRootAdminCss() . $adminCssFile)) {
                $handle = $this->_pm->getAbbrLower() . '-' .'admin';
                IfwPsn_Wp_Proxy_Style::loadAdmin($handle, $this->_pm->getEnv()->getUrlAdminCss() . $adminCssFile, array(), $this->_pm->getEnv()->getVersion());
                break;
            }
        }
    }

    /**
     *
     */
    public function loadSkin()
    {
        if ($this->_pm->getEnv()->hasSkin()) {

            $files = array('style.css');

            if ($this->_pm->isProduction()) {
                $files = array_merge(array('style.min.css'), $files);
            }

            foreach ($files as $styleCssFile) {
                if (file_exists($this->_pm->getPathinfo()->getRootSkin() . 'default/' . $styleCssFile)) {
                    IfwPsn_Wp_Proxy_Style::loadAdmin('admin-style', $this->_pm->getEnv()->getSkinUrl() . $styleCssFile, array(), $this->_pm->getEnv()->getVersion());
                    if ($this->_pm->hasPremium() && $this->_pm->isPremium() == false) {
                        IfwPsn_Wp_Proxy_Style::loadAdmin('premiumad-style', $this->_pm->getEnv()->getSkinUrl() . 'premiumad.css', array(), $this->_pm->getEnv()->getVersion());
                    }
                    break;
                }
            }
        }
    }
}
