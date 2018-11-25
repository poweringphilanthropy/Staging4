<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 433 2015-06-21 21:39:19Z timoreithde $
 */
abstract class IfwPsn_Wp_Ajax_Response_Abstract
{
    /**
     * Output response header
     */
    abstract public function header();

    /**
     * Outputs the response data
     */
    abstract public function output();
}
