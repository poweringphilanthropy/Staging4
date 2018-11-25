<?php
/**
 * Executes on plugin uninstall
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Uninstall.php 337 2014-11-09 14:27:46Z timoreithde $
 */
class Psn_Module_DeferredSending_Installer_Uninstall implements IfwPsn_Wp_Plugin_Installer_UninstallInterface
{
    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Installer_UninstallInterface::execute()
     */
    public static function execute($pm)
    {
        if (IfwPsn_Wp_Proxy_Blog::isMultisite()) {

            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();

            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {

                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                self::_dropTable();
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);

        } else {
            // single blog installation
            self::_dropTable();
        }
    }

    /**
     * @param bool $networkwide
     */
    protected static function _dropTable($networkwide = false)
    {
        require_once dirname(__FILE__) . '/../Model/MailQueue.php';
        require_once dirname(__FILE__) . '/../Model/MailQueueLog.php';

        $table = new Psn_Module_DeferredSending_Model_MailQueue();
        $table->dropTable($networkwide);
        $table = new Psn_Module_DeferredSending_Model_MailQueueLog();
        $table->dropTable($networkwide);
    }
}
