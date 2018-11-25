<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Tries to reset the options set by the plugin
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: UninstallResetOptions.php 233 2014-03-17 23:46:37Z timoreithde $
 */
require_once dirname(__FILE__) . '/../UninstallInterface.php';

class IfwPsn_Wp_Plugin_Installer_Command_UninstallResetOptions implements IfwPsn_Wp_Plugin_Installer_UninstallInterface
{
    /**
     * @param IfwPsn_Wp_Plugin_Manager|null $pm
     * @return mixed|void
     */
    public static function execute($pm)
    {
        if (!($pm instanceof IfwPsn_Wp_Plugin_Manager)) {
            return;
        }

        if (IfwPsn_Wp_Proxy_Blog::isMultisite()) {

            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();

            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {

                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $pm->getOptions()->reset();
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);

        } else {
            // single blog installation
            $pm->getOptions()->reset();
        }

    }
}
