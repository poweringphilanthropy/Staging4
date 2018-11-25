<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Options.php 233 2014-03-17 23:46:37Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Bootstrap_Observer_Options extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'options';
    }

    protected function _preBootstrap()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options.php';
        $this->_resource = IfwPsn_Wp_Options::getInstance($this->_pm);
        $this->_resource->init();
    }

    protected function _postBootstrap()
    {
        $this->_resource->load();
    }

}
