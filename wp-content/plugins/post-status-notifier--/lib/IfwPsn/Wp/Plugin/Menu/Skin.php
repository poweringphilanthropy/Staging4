<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Admin skin loader
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Skin.php 180 2013-07-08 21:05:30Z timoreithde $
 */ 
class IfwPsn_Wp_Plugin_Menu_Skin
{
    public static function loadSkin(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if ($pm->getEnv()->hasSkin()) {
            IfwPsn_Wp_Proxy_Style::loadAdmin('admin-style', $pm->getEnv()->getSkinUrl() . 'style.css');
        }
    }
}
