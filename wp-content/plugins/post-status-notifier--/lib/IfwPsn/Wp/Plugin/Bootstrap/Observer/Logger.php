<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Logger.php 252 2014-04-07 19:57:34Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Bootstrap_Observer_Logger extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'logger';
    }

    protected function _preBootstrap()
    {
        $this->_pm->getLogger()->logPrefixed('Plugin bootstrap: ' . __METHOD__);
    }

    protected function _postModules()
    {
        $this->_pm->getLogger()->logPrefixed('Plugin bootstrap: ' . __METHOD__);
    }

    protected function _postBootstrap()
    {
        $this->_pm->getLogger()->logPrefixed('Plugin bootstrap: ' . __METHOD__);
    }

    protected function _shutdownBootstrap()
    {
        $this->_pm->getLogger()->logPrefixed('Plugin bootstrap: ' . __METHOD__);
    }

}
