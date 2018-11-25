<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: ActivationPresentVersion.php 233 2014-03-17 23:46:37Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/../ActivationInterface.php';

class IfwPsn_Wp_Plugin_Installer_Command_ActivationPresentVersion implements IfwPsn_Wp_Plugin_Installer_ActivationInterface
{
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $networkwide
     * @return mixed
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm, $networkwide = false)
    {
        if (IfwPsn_Wp_Proxy_Blog::isMultisite() && $networkwide == true) {

            // multisite installation
            $currentBlogId = IfwPsn_Wp_Proxy_Blog::getBlogId();

            foreach (IfwPsn_Wp_Proxy_Blog::getMultisiteBlogIds() as $blogId) {

                IfwPsn_Wp_Proxy_Blog::switchToBlog($blogId);
                $this->_refreshPresentVersion($pm);
            }
            IfwPsn_Wp_Proxy_Blog::switchToBlog($currentBlogId);

        } else {
            // single blog installation
            $this->_refreshPresentVersion($pm);
        }
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected function _refreshPresentVersion(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $pm->getBootstrap()->getUpdateManager()->refreshPresentVersion();
    }
}
