<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Selftester.php 233 2014-03-17 23:46:37Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Bootstrap_Observer_Selftester extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'selftester';
    }

    protected function _preBootstrap()
    {
        if ( ($this->_pm->getAccess()->isPlugin() && !$this->_pm->getAccess()->isAjax()) ||
            ($this->_pm->getAccess()->isAjax() && $this->_pm->getAccess()->hasPluginAbbrAction()) ) {

            require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Plugin/Selftester.php';
            $this->_resource = new IfwPsn_Wp_Plugin_Selftester($this->_pm);
        }
    }

    protected function _shutdownBootstrap()
    {
        if ( ($this->_pm->getAccess()->isPlugin() && !$this->_pm->getAccess()->isAjax()) ||
            ($this->_pm->getAccess()->isAjax() && $this->_pm->getAccess()->hasPluginAbbrAction()) ) {

            $this->_resource->activate();
        }
    }
}
