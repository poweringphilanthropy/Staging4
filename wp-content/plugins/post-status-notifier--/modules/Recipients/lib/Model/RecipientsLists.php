<?php
/**
 * RecipientsLists model
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: RecipientsLists.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */

class Psn_Module_Recipients_Model_RecipientsLists extends IfwPsn_Wp_ORM_Model
{
    /**
     * @var string
     */
    public static $_table = 'psn_recipients_lists';



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
              `name` varchar(255) collate utf8_unicode_ci NOT NULL,
              `list` text collate utf8_unicode_ci NOT NULL,
              PRIMARY KEY  (`id`)
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

