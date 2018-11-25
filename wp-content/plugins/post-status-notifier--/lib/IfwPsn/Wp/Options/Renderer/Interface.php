<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Interface.php 414 2015-04-12 14:44:06Z timoreithde $
 * @package   
 */
interface IfwPsn_Wp_Options_Renderer_Interface 
{
    public function init();
    public function render(IfwPsn_Wp_Options $options, $pageId = null);
}
 