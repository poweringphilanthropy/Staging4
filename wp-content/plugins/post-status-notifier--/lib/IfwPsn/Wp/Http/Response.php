<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Response.php 396 2015-02-21 01:11:16Z timoreithde $
 */
class IfwPsn_Wp_Http_Response
{
    /**
     * @var
     */
    protected $_response;

    /**
     * @var int
     */
    protected $_statusCode;

    /**
     * The response body
     * @var string|null
     */
    protected $_body;

    /**
     * @var string
     */
    protected $_errorMessage;



    /**
     * @param $response
     */
    public function __construct($response)
    {
        $this->_response = $response;

        $this->_init();
    }

    protected function _init()
    {
        if (is_array($this->_response) && isset($this->_response['response'])) {

            // response is an array
            if (isset($this->_response['response']['code'])) {
                $this->_statusCode = $this->_response['response']['code'];
            }
            if (isset($this->_response['body'])) {
                $this->_body = $this->_response['body'];
            }

        } elseif (is_wp_error($this->_response)) {

            /**
             * is WP_Error
             * @var WP_Error $this->_response
             */
            $this->_errorMessage = $this->_response->get_error_message();
            $this->_statusCode = 404;
            
        } else {

            // unknown response
            // set to error status
            $this->_errorMessage = 'Invalid response';
            $this->_statusCode = 404; 
        }
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->_statusCode == 200;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->_statusCode == 404;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * @return array|mixed
     */
    public function getArray()
    {
        return json_decode($this->getBody(), true);
    }
}
 