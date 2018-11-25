<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Translation.php 233 2014-03-17 23:46:37Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Bootstrap_Observer_Translation extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'translation';
    }

    protected function _preBootstrap()
    {
        if (!$this->_pm->getAccess()->isHeartbeat() && $this->_pm->getAccess()->isAdmin()) {

            // load the framework translation
            require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Proxy.php';
            IfwPsn_Wp_Proxy::loadTextdomain('ifw', false, $this->_pm->getPathinfo()->getDirname() . '/lib/IfwPsn/Wp/Translation');

            if (is_dir($this->_pm->getPathinfo()->getRootLang())) {
                $langRelPath = $this->_pm->getPathinfo()->getDirname() . '/lang';
                $result = IfwPsn_Wp_Proxy::loadTextdomain($this->_pm->getEnv()->getTextDomain(), false, $langRelPath);
            }
        }
    }

}
