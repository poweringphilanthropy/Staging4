<?php
/**
 * Executes on plugin uninstall
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Activation.php 175 2014-04-05 12:44:39Z timoreithde $
 */
class Psn_Module_HtmlMails_Installer_Activation implements IfwPsn_Wp_Plugin_Installer_ActivationInterface
{
    /**
     * @var Psn_Patch_Database
     */
    protected $_dbPatcher;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param bool $networkwide
     * @return mixed|void
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm, $networkwide = false)
    {
        $this->_dbPatcher = new Psn_Patch_Database();

        if (IfwPsn_Wp_Proxy_Blog::isMultisite() && $networkwide == true) {

            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();

            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {

                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $this->_createTable(true);
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);

        } else {
            // single blog installation
            $this->_createTable();
        }
    }

    /**
     * @param bool $networkwide
     */
    protected static function _createTable($networkwide = false)
    {
        require_once dirname(__FILE__) . '/../Model/MailTemplates.php';

        $table = new Psn_Module_HtmlMails_Model_MailTemplates();
        $table->createTable($networkwide);
    }
}
