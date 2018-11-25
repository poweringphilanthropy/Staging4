<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Html.php 433 2015-06-21 21:39:19Z timoreithde $
 */
class IfwPsn_Wp_Ajax_Response_Html extends IfwPsn_Wp_Ajax_Response_Abstract
{
    protected $_html;


    /**
     * @param $html
     */
    public function __construct($html = null)
    {
        if (!empty($html)) {
            $this->_html = $html;
        }
    }

    /**
     * Output response header
     */
    public function header()
    {
        header('Content-Type: text/html; charset=utf-8');
    }

    /**
     * Outputs the response data
     */
    public function output()
    {
        echo $this->_html;
    }

    /**
     * @return mixed
     */
    public function getHtml()
    {
        return $this->_html;
    }

    /**
     * @param mixed $html
     */
    public function setHtml($html)
    {
        $this->_html = $html;
    }

}
