<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Model.php 438 2015-07-14 20:54:16Z timoreithde $
 */ 
abstract class IfwPsn_Wp_Plugin_Logger_Model extends IfwPsn_Wp_ORM_Model
{
    /**
     * @var array
     */
    public static $eventItems = array(
        'priority',
        'message',
        'type',
        'timestamp',
        'extra'
    );

    /**
     * @param $tablename
     * @param bool $networkwide
     */
    public function createTable($tablename, $networkwide = false)
    {
        global $wpdb;

        $query = '
        CREATE TABLE IF NOT EXISTS `%s` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `priority` int(11) NOT NULL,
          `message` varchar(255) CHARACTER SET utf8 NOT NULL,
          `type` smallint(4) NOT NULL,
          `timestamp` datetime NOT NULL,
          `extra` longtext COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`)
        );
        ';

        if (!$networkwide) {
            // single blog installation
            $wpdb->query(sprintf($query, $wpdb->prefix . $tablename));
        } else {
            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();
            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {
                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $wpdb->query(sprintf($query, $wpdb->prefix . $tablename));
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);
        }
    }
}
