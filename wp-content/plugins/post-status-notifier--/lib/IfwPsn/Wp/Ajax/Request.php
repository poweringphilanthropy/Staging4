<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Request.php 448 2015-07-31 21:52:57Z timoreithde $
 */
abstract class IfwPsn_Wp_Ajax_Request
{
    const ACCESS_LEVEL_PRIVATE = 1;
    const ACCESS_LEVEL_PUBLIC = 2;
    const ACCESS_LEVEL_PRIVATE_AND_PUBLIC = 4;

    /**
     * @var null|string
     */
    public $action;

    /**
     * @var int
     */
    protected $_accessLevel = self::ACCESS_LEVEL_PRIVATE;


    /**
     * Register the AJAX request
     */
    final public function register()
    {
        if (empty($this->action)) {
            trigger_error(sprintf('cannot register ajax request because of empty action in %s', __METHOD__));
            return false;
        }

        switch ($this->_accessLevel) {
            case self::ACCESS_LEVEL_PRIVATE_AND_PUBLIC:
                $this->_registerPrivate();
                $this->_registerPublic();
                break;
            case self::ACCESS_LEVEL_PUBLIC:
                $this->_registerPublic();
                break;
            default:
                $this->_registerPrivate();
        }
    }

    /**
     * Registers admin request
     */
    protected function _registerPrivate()
    {
        add_action('wp_ajax_' . $this->action, array($this, 'actionCallback'));
    }

    /**
     * Registers user request
     */
    protected function _registerPublic()
    {
        add_action('wp_ajax_nopriv_' . $this->action, array($this, 'actionCallback'));
    }

    /**
     * Calls the getResponse method and handles the response
     */
    final public function actionCallback()
    {
        if (empty($this->action) || !check_ajax_referer($this->_getNonceName(), 'nonce', false)) {
            // invalid nonce
            $response = new IfwPsn_Wp_Ajax_Response_Json(false, array('html' => ''), 'invalid request');
            trigger_error(sprintf('invalid nonce for ajax request %s', $this->action));
        } else {
            ifw_raise_memory_limit();
            $response = $this->getResponse();
        }

        if ($response instanceof IfwPsn_Wp_Ajax_Response_Abstract) {
            $response->header();
            $response->output();
        } else {
            trigger_error(sprintf('invalid ajax response in %s', __METHOD__));
        }

        wp_die();
    }

    /**
     * @return null|string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return bool|string
     */
    public function getNonce()
    {
        if (function_exists('wp_create_nonce')) {
            return wp_create_nonce($this->_getNonceName());
        }
        return false;
    }

    /**
     * @return string
     */
    protected function _getNonceName()
    {
        return $this->action . '-nonce';
    }

    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    abstract public function getResponse();
}
