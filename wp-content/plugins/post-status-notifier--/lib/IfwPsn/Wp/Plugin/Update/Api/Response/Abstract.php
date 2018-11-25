<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 468 2015-10-05 21:16:43Z timoreithde $
 */
abstract class IfwPsn_Wp_Plugin_Update_Api_Response_Abstract
{
    /**
     * @var bool
     */
    protected $_success;

    /**
     * @var string
     */
    protected $_message;

    /**
     * @var array
     */
    protected $_data = array();


    /**
     * @param bool $success
     * @param string $message
     */
    public function __construct($success = false, $message = '')
    {
        if (is_bool($success)) {
            $this->setSuccess($success);
        }
        if (is_string($message)) {
            $this->setMessage($message);
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
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasData($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * @param $key
     * @return null
     */
    public function getData($key)
    {
        if ($this->hasData($key)) {
            return $this->_data[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(array(
            'success' => $this->_success,
            'message' => $this->_message
        ), $this->_data);
    }
}
