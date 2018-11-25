<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Json.php 433 2015-06-21 21:39:19Z timoreithde $
 */
class IfwPsn_Wp_Ajax_Response_Json extends IfwPsn_Wp_Ajax_Response_Abstract
{
    /**
     * @var bool
     */
    protected $_success = true;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var null|string
     */
    protected $_message;


    /**
     * @param bool $success
     * @param array $data
     */
    public function __construct($success = true, $data = array(), $message = null)
    {
        if (is_bool($success)) {
            $this->_success = $success;
        }
        if (is_array($data)) {
            $this->_data = $data;
        }
        if (!is_null($message)) {
            $this->_message = $message;
        }
    }

    /**
     * Output response header
     */
    public function header()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Outputs the response data
     */
    public function output()
    {
        $result = array(
            'success' => $this->_success,
            'data' => $this->_data
        );
        if (!empty($this->_message)) {
            $result['message'] = $this->_message;
        }

        echo json_encode($result);
    }

    /**
     * Adds data as key value pair
     * @param $key
     * @param $value
     */
    public function addData($key, $value)
    {
        if (!isset($this->_data[$key])) {
            $this->_data[$key] = $value;
        }
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->_success;
    }

    /**
     * @param boolean $success
     */
    public function setSuccess($success)
    {
        if (is_bool($success)) {
            $this->_success = $success;
        }
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param null|string $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }
}
