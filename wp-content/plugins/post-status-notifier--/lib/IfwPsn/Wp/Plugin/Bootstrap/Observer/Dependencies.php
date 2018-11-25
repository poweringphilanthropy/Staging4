<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Dependencies.php 237 2014-03-21 01:18:02Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Bootstrap_Observer_Dependencies extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'dependencies';
    }

    protected function _preBootstrap()
    {
        if ($this->_pm->getAccess()->isPlugin() && $this->_pm->getPathinfo()->hasRootApplication() && !$this->_pm->getAccess()->isAjax()) {

            $lib = $this->_pm->getPathinfo()->getRootLib();

            // including controller related classes
            //require_once $lib . 'IfwPsn/Vendor/Zend/Controller/Front.php';
            require_once $lib . 'IfwPsn/Zend/Controller/Front.php';

            require_once $lib . 'IfwPsn/Vendor/Zend/Layout/Controller/Plugin/Layout.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/Layout/Controller/Action/Helper/Layout.php';

            require_once $lib . 'IfwPsn/Vendor/Zend/View/Interface.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/View/Abstract.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/View/Helper/Interface.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/View/Helper/Abstract.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Container/Abstract.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Container.php';
            require_once $lib . 'IfwPsn/Vendor/Zend/View/Helper/Placeholder/Registry.php';

            // script and style proxy are usually used in the application, so preload them:
            require_once $lib . 'IfwPsn/Wp/Proxy/Script.php';
            require_once $lib . 'IfwPsn/Wp/Proxy/Style.php';

            require_once $lib . 'IfwPsn/Wp/Proxy.php';
            require_once $lib . 'IfwPsn/Wp/Proxy/Action.php';
            require_once $lib . 'IfwPsn/Wp/Proxy/Filter.php';
            require_once $lib . 'IfwPsn/Wp/Proxy/Blog.php';

        }
    }

}
