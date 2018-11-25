<?php
/**
 * MailTemplates model
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailTemplates.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_HtmlMails_Model_MailTemplates extends IfwPsn_Wp_ORM_Model
{
    const TYPE_PLAIN_TEXT = 0;
    const TYPE_HTML = 1;

    /**
     * @var string
     */
    public static $_table = 'psn_mail_templates';



    /**
     * @return string
     */
    public function getBody()
    {
        // see: http://stackoverflow.com/questions/6275380/does-html-entity-decode-replaces-nbsp-also-if-not-how-to-replace-it
        if (IfwPsn_Wp_Proxy_Blog::getCharset() == 'UTF-8') {
            $body = str_replace("\xC2\xA0", ' ', html_entity_decode($this->get('body'), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset()));
        } else {
            $body = str_replace("\xA0", ' ', html_entity_decode($this->get('body'), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset()));
        }
        return $body;
    }

    /**
     * @return string
     */
    public function getAltBody()
    {
        // see: http://stackoverflow.com/questions/6275380/does-html-entity-decode-replaces-nbsp-also-if-not-how-to-replace-it
        if (IfwPsn_Wp_Proxy_Blog::getCharset() == 'UTF-8') {
            $altbody = str_replace("\xC2\xA0", ' ', html_entity_decode($this->get('alt_body'), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset()));
        } else {
            $altbody = str_replace("\xA0", ' ', html_entity_decode($this->get('alt_body'), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset()));
        }

        return $altbody;
    }

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
              `type` tinyint(1) NOT NULL default "0" COMMENT "0: plain text, 1: html",
              `body` text collate utf8_unicode_ci NOT NULL,
              `altbody` text collate utf8_unicode_ci NOT NULL,
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

