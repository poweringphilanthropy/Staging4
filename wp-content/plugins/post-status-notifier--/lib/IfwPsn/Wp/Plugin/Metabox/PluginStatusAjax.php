<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PluginStatusAjax.php 461 2015-09-09 22:16:16Z timoreithde $
 */ 
class IfwPsn_Wp_Plugin_Metabox_PluginStatusAjax extends IfwPsn_Wp_Ajax_Request
{
    public $action = 'load-plugin-status';

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var null
     */
    protected $_iframeSrc;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->action .= '-' . $this->_pm->getAbbrLower();
    }

    /**
     * @return null
     */
    public function getIframeSrc()
    {
        return $this->_iframeSrc;
    }

    /**
     * @param null $iframeSrc
     */
    public function setIframeSrc($iframeSrc)
    {
        $this->_iframeSrc = $iframeSrc;
    }

    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    public function getResponse()
    {
        $tpl = IfwPsn_Wp_Tpl::getInstance($this->_pm);

        if ($this->_iframeSrc !== null) {
            $iframeSrc = $this->_iframeSrc;
        } elseif (strpos($this->_pm->getConfig()->plugin->optionsPage, 'options-general.php') !== false) {
            $iframeSrc = IfwPsn_Wp_Proxy_Admin::getUrl() . IfwPsn_Wp_Proxy_Admin::getMenuUrl($this->_pm, 'selftest');
            $iframeSrc = str_replace('wp-admin//', 'wp-admin/', $iframeSrc);
        } else {
            $iframeSrc = IfwPsn_Wp_Proxy_Admin::getAdminPageUrl($this->_pm, $this->_pm->getAbbrLower() . '_selftest', $this->_pm->getAbbrLower() . '_selftest');
        }

        $context = array(
            'ajax' => $this,
            'iframe_src' => $iframeSrc,
            'img_path' => $this->_pm->getEnv()->getUrlAdminImg()
        );

        $timestamp = $this->_pm->getBootstrap()->getSelftester()->getTimestamp();
        if (!empty($timestamp)) {
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Date.php';
            $timestamp = IfwPsn_Wp_Date::format($timestamp);
        }
        $context['timestamp'] = $timestamp;
        $status = $this->_pm->getBootstrap()->getSelftester()->getStatus();

        if ($status === true || $status === 'true' || $status == '1') {
            $context['status'] = 'true';
        } elseif ($status === false || $status === 'false' || $status == '0') {
            $context['status'] = 'false';
        } else {
            $context['status'] = 'null';
        }

        $html = $tpl->render('metabox_pluginstatus.html.twig', $context);
        $success = true;

        $response = new IfwPsn_Wp_Ajax_Response_Json($success);
        $response->addData('html', $html);

        return $response;
    }

}