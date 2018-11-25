<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Interface for ListTable data
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Interface.php 177 2013-07-08 20:15:32Z timoreithde $
 * @package  IfwPsn_Wp
 */
interface IfwPsn_Wp_Plugin_ListTable_Data_Interface
{
    public function getItems($limit, $page, $order = null, $where = null);

    public function getTotalItems();
}