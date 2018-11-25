<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Interface.php 439 2015-07-16 21:16:06Z timoreithde $
 * @package   
 */
interface IfwPsn_Wp_Model_Mapper_Interface 
{
    public static function getInstance();
    public function getSingular();
    public function getPlural();
    public function getPerPageId($prefix = '');
}
