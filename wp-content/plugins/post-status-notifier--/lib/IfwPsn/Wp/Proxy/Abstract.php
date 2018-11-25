<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * The abstract proxy class every proxy must extend
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 153 2013-05-26 11:16:39Z timoreithde $
 */
abstract class IfwPsn_Wp_Proxy_Abstract
{
    /**
     * If one proxy method needs another proxy
     * @var IfwPsn_Wp_Proxy
     */
    protected $_wpProxy;
    
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     *
     * @param IfwPsn_Wp_Proxy $wpProxy
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @internal param \IfwPsn_Wp_Proxy $wp_proxy
     */
    public function __construct (IfwPsn_Wp_Proxy $wpProxy, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_wpProxy = $wpProxy;
        $this->_pm = $pm;
    }
}
