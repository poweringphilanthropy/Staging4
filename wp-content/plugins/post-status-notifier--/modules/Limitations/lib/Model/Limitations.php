<?php
/**
 * Limitations model
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Limitations.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */

class Psn_Module_Limitations_Model_Limitations extends IfwPsn_Wp_ORM_Model
{

    /**
     * @var string
     */
    public static $_table = 'psn_limitations';



    /**
     * Installs the table
     *
     * @param bool $networkwide
     */
    public function createTable($networkwide = false)
    {
        global $wpdb;

        $query = '
            CREATE TABLE IF NOT EXISTS `%s` (
              `id` int(11) NOT NULL auto_increment,
              `rule_id` int(11) NOT NULL,
              `post_id` int(11) NOT NULL,
              `status_after` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `timestamp` datetime NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `rule_id` (`rule_id`),
              KEY `post_id` (`post_id`),
              KEY `status_after` (`status_after`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Plugin: Post Status Notifier";
        ';

        if (!$networkwide) {
            // single blog installation
            $wpdb->query(sprintf($query, $wpdb->prefix . self::$_table));
        } else {
            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();
            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {
                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $wpdb->query(sprintf($query, $wpdb->prefix . self::$_table));
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);
        }
    }

    /**
     * Uninstalls the table
     *
     * @param bool $networkwide
     */
    public function dropTable($networkwide = false)
    {
        global $wpdb;

        if (!$networkwide) {
            // single blog installation
            $wpdb->query('DROP TABLE IF EXISTS `'. $wpdb->prefix . self::$_table);

        } else {
            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();
            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {
                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $wpdb->query('DROP TABLE IF EXISTS `'. $wpdb->prefix . self::$_table);
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);
        }
    }

}

