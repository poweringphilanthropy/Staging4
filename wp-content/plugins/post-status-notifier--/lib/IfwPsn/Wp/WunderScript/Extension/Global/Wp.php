<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Wp.php 423 2015-04-26 19:34:24Z timoreithde $
 * @package
 */ 
class IfwPsn_Wp_WunderScript_Extension_Global_Wp 
{
    /**
     * @var array
     */
    protected $_functionWhitelist = array(
        'home_url',
        'admin_url',
        'site_url',
        'network_home_url',
        'network_admin_url',
        'network_site_url',
    );

    public function __call($name, $arguments)
    {
        if (function_exists($name) && $this->_isAllowedFunction($name)) {
            return call_user_func_array($name, $arguments);
        }

        return '';
    }

    /**
     * @param $name
     * @return bool
     */
    protected function _isAllowedFunction($name)
    {
        if (strpos($name, 'get_') === 0 || strpos($name, 'wp_get_') === 0) {
            // allow all getter functions
            return true;
        } elseif (strpos($name, 'sanitize_') === 0) {
            // allow all sanitize functions
            return true;
        } elseif (strpos($name, 'is_') === 0 || strpos($name, 'wp_is_') === 0) {
            // allow all is functions
            return true;
        } elseif (strpos($name, 'has_') === 0) {
            // allow all has functions
            return true;
        } elseif (strpos($name, 'esc_') === 0) {
            // allow all escape functions
            return true;
        } elseif (in_array($name, $this->_functionWhitelist)) {
            return true;
        }

        return false;
    }
}
 