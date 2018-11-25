<?php
/**
 * Executes on plugin activation 
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @copyright   Copyright (c) ifeelweb.de
 * @version     $Id: Activation.php 432 2015-10-30 12:18:57Z timoreithde $
 * @package     Psn_Installer
 */
class Psn_Installer_Activation implements IfwPsn_Wp_Plugin_Installer_ActivationInterface
{
    /**
     * @var Psn_Patch_Database
     */
    protected $_dbPatcher;



    /**
     * (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Installer_ActivationInterface::execute()
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm, $networkwide = false)
    {
        if ($pm->isPremium() &&
            IfwPsn_Wp_Proxy_Blog::isPluginActive('post-status-notifier-lite/post-status-notifier-lite.php')) {
            trigger_error(sprintf( __('The Lite version of this plugin is still activated. Please deactivate it! Refer to the <a href=\"%s\">Upgrade Howto</a>.', 'psn'), 'http://docs.ifeelweb.de/post-status-notifier/upgrade_howto.html'));
        }

        $this->_dbPatcher = new Psn_Patch_Database();

        if (IfwPsn_Wp_Proxy_Blog::isMultisite() && $networkwide == true) {

            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();

            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {

                // give every site in the network the default time limit of 30 seconds
                set_time_limit(30);

                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $this->_createTable();
                $this->_presetOptions($pm);
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);

        } else {
            // single blog installation
            $this->_createTable();
            $this->_presetOptions($pm);
        }
    }

    /**
     * Handles options presetting on first install / activation
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected function _presetOptions(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (!$pm->hasOption('selftest_timestamp')) {
            // on first install (no selftest timestam could be found)
            $pm->getOptions()->updateOption('psn_ignore_status_inherit', true);
        }
    }

    /**
     * Creates table and checks for new fields since version 1.0
     */
    protected function _createTable()
    {
        global $wpdb;

        $wpdb->query('
            CREATE TABLE IF NOT EXISTS `'. $wpdb->prefix .'psn_rules` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
              `posttype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `status_before` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `status_after` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `notification_subject` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
              `notification_body` text COLLATE utf8_unicode_ci NOT NULL,
              `recipient` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
              `to` varchar(255) COLLATE utf8_unicode_ci NULL,
              `to_dyn` text COLLATE utf8_unicode_ci,
              `cc_select` text COLLATE utf8_unicode_ci,
              `cc` text COLLATE utf8_unicode_ci,
              `bcc_select` text COLLATE utf8_unicode_ci,
              `bcc` text COLLATE utf8_unicode_ci,
              `active` tinyint(1) NOT NULL DEFAULT "1",
              `service_email` tinyint(1) NOT NULL DEFAULT "0",
              `service_log` tinyint(1) NOT NULL DEFAULT "0",
              `categories` text COLLATE utf8_unicode_ci,
              `from` varchar(255) COLLATE utf8_unicode_ci NULL,
              `mail_tpl` int(11) NULL,
              `editor_restriction` text COLLATE utf8_unicode_ci,
              `to_loop` tinyint(1) NOT NULL DEFAULT "0",
              `limit_type` tinyint(1) NULL,
              `limit_count` int(11) NULL,
              `exclude_current_user` tinyint(1) NOT NULL DEFAULT "0",
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Plugin: Post Status Notifier";
        ');

        // if the table already existed (eg on update) this will check if all new fields are present
        $this->_dbPatcher->updateRulesTable();
    }
}
