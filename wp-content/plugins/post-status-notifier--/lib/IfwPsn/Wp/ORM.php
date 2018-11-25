<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: ORM.php 161 2013-06-06 23:12:17Z timoreithde $
 */
class IfwPsn_Wp_ORM
{
    protected static $_is_init = false;

    public static function init($config)
    {
        if (!self::$_is_init) {

            require_once dirname(__FILE__) . '/ORM/Idiorm.php';
            require_once dirname(__FILE__) . '/ORM/ORM.php';
            require_once dirname(__FILE__) . '/ORM/Wrapper.php';
            require_once dirname(__FILE__) . '/ORM/Model.php';

            if ((int)$config->use_pdo === 1) {
                IfwPsn_Wp_ORM_ORM::configure('use_pdo', true);
                IfwPsn_Wp_ORM_ORM::configure(sprintf('mysql:host=%s;dbname=%s', DB_HOST, DB_NAME));
                IfwPsn_Wp_ORM_ORM::configure('username', DB_USER);
                IfwPsn_Wp_ORM_ORM::configure('password', DB_PASSWORD);
                // UTF8 setting, see: https://github.com/j4mie/idiorm/issues/2
                IfwPsn_Wp_ORM_ORM::configure('driver_options', array(
                        defined('PDO::MYSQL_ATTR_INIT_COMMAND') ? PDO::MYSQL_ATTR_INIT_COMMAND : 1002 => "SET NAMES utf8")
                );
            }

            self::$_is_init = true;
        }
    }
}
