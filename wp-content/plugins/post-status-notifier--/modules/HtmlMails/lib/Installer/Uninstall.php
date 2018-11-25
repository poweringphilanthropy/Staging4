<?php
/**
 * Executes on plugin uninstall
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Uninstall.php 176 2014-04-05 12:50:17Z timoreithde $
 */
class Psn_Module_HtmlMails_Installer_Uninstall implements IfwPsn_Wp_Plugin_Installer_UninstallInterface
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

    protected static function _dropTable($networkwide = false)
    {
        require_once dirname(__FILE__) . '/../Model/MailTemplates.php';
        
        $table = new Psn_Module_HtmlMails_Model_MailTemplates();
        $table->dropTable($networkwide);
    }
}
