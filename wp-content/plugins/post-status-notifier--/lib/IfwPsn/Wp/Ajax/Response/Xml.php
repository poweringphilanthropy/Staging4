<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Xml.php 433 2015-06-21 21:39:19Z timoreithde $
 */
class IfwPsn_Wp_Ajax_Response_Xml extends IfwPsn_Wp_Ajax_Response_Abstract
{
    protected $_xml;


    /**
     * @param $xml
     */
    public function __construct($xml = null)
    {
        if (!empty($xml)) {
            $this->_xml = $xml;
        }
    }

    /**
     * Output response header
     */
    public function header()
    {
        header('Content-Type: text/xml; charset=utf-8');
    }

    /**
     * Outputs the response data
     */
    public function output()
    {
        echo $this->_xml;
    }

    /**
     * @return mixed
     */
    public function getXml()
    {
        return $this->_xml;
    }

    /**
     * @param mixed $xml
     */
    public function setXml($xml)
    {
        $this->_xml = $xml;
    }
}
