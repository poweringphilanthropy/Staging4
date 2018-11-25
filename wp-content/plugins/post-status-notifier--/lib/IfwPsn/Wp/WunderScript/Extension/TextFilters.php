<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: TextFilters.php 292 2014-05-29 15:04:25Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_WunderScript_Extension_TextFilters implements IfwPsn_Wp_WunderScript_Extension_Interface
{
    public function load(IfwPsn_Vendor_Twig_Environment $env)
    {
        require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Twig/SimpleFilter.php';

        $env->addFilter( new IfwPsn_Vendor_Twig_SimpleFilter('unserialize', array($this, 'filterUnserialize')) );
    }

    /**
     * @param $string
     * @return mixed
     */
    public function filterUnserialize($string)
    {
        return ifw_unserialize_recursive($string);
    }
}
 