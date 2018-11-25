<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: User.php 153 2013-05-26 11:16:39Z timoreithde $
 */
class IfwPsn_Wp_User
{
    public static function isAdmin()
    {
        return current_user_can('install_plugins');
    }
}
