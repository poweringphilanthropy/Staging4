<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: ActivationInterface.php 207 2013-09-11 20:40:36Z timoreithde $
 * @package  IfwPsn_Wp
 */
interface IfwPsn_Wp_Plugin_Installer_ActivationInterface
{
    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param bool $networkwide
     * @return mixed
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm, $networkwide = false);
}
