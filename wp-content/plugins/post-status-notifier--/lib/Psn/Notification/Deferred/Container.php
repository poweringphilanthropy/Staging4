<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Container.php 315 2014-09-10 21:25:13Z timoreithde $
 * @package
 */

class Psn_Notification_Deferred_Container 
{
    /**
     * @var Psn_Notification_Service_Interface
     */
    protected $_service;

    /**
     * @var Psn_Model_Rule
     */
    protected $_rule;

    /**
     * @var WP_Post
     */
    protected $_post;



    /**
     * Executes the service with submitted post object
     *
     * @param $post
     */
    public function execute($post)
    {
        $this->_rule->setReplacer(Psn_Notification_Placeholders::getInstance($post));

        $this->_service->execute($this->_rule, $post);
    }

    /**
     * @param $post
     * @return bool
     */
    public function matchesPost($post)
    {
        if (is_object($post) && isset($post->ID)) {
            if (is_object($this->_post) && isset($this->_post->ID) && $post->ID === $this->_post->ID) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param WP_Post $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->_post = $post;
        return $this;
    }

    /**
     * @param Psn_Model_Rule $rule
     * @return $this
     */
    public function setRule($rule)
    {
        $this->_rule = $rule;
        return $this;
    }

    /**
     * @param Psn_Notification_Service_Interface $service
     * @return $this
     */
    public function setService($service)
    {
        $this->_service = $service;
        return $this;
    }
}
 