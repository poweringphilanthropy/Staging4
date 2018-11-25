<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Request.php 470 2015-10-07 21:42:37Z timoreithde $
 */
class IfwPsn_Wp_Http_Request
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @var string
     */
    protected $_userAgent;

    /**
     * @var int
     */
    protected $_timeout;

    /**
     * @var bool
     */
    protected $_sslverify;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var string
     */
    protected $_sendMethod = 'post';



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct($pm = null)
    {
        if ($pm instanceof IfwPsn_Wp_Plugin_Manager) {
            $this->_pm = $pm;
        }

        $this->_init();
    }

    /**
     *
     */
    protected function _init()
    {
    }

    /**
     * @return IfwPsn_Wp_Http_Response
     */
    public function send()
    {
        $args = array();

        if ($this->getUserAgent() !== null) {
            $args['user-agent'] = $this->getUserAgent();
        }
        if ($this->getTimeout() !== null) {
            $args['timeout'] = $this->getTimeout();
        }
        if ($this->getSslverify() !== null) {
            $args['sslverify'] = $this->getSslverify();
        }

        if ($this->getSendMethod() == 'post') {

            $args['body'] = $this->getData();

            $response = wp_remote_post($this->getUrl(), $args);

        } elseif ($this->getSendMethod() == 'get') {

            $url = add_query_arg($this->getData(), $this->getUrl());
            $url = esc_url_raw($url);

            $response = wp_remote_get($url, $args);
        }

        if (isset($response)) {
            return new IfwPsn_Wp_Http_Response($response);
        }

        return null;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->_userAgent = $userAgent;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->_userAgent;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSslverify()
    {
        return $this->_sslverify;
    }

    /**
     * @param boolean $sslverify
     * @return $this
     */
    public function setSslverify($sslverify)
    {
        if (is_bool($sslverify)) {
            $this->_sslverify = $sslverify;
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param string $sendMethod
     * @return $this
     */
    public function setSendMethod($sendMethod)
    {
        if (in_array($sendMethod, array('get', 'post'))) {
            $this->_sendMethod = $sendMethod;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getSendMethod()
    {
        return $this->_sendMethod;
    }

}
 