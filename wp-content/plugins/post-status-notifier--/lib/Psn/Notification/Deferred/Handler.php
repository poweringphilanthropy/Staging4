<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Handler.php 362 2015-02-03 22:22:50Z timoreithde $
 * @package
 */

class Psn_Notification_Deferred_Handler 
{
    /**
     * @var array
     */
    private $_container = array();

    private $_post;


    public function __construct()
    {
        // register the execute method to wp_insert_post action
        // to execute notification services after the post got saved completely
        // (including custom fields managed by plugins etc.)
        IfwPsn_Wp_Proxy_Action::addWpInsertPost(array($this, 'fetchPostOnInsert'), 1000000);
    }

    /**
     * Fetch a post on save / update and store it for later processing
     *
     * @param $post_ID
     * @param $post
     * @param $update
     */
    public function fetchPostOnInsert($post_ID, $post, $update = null)
    {
        // fetch post
        $this->_post = $post;

        if (IfwPsn_Wp_Plugin_Manager::getInstance('Psn')->hasOption('psn_late_execution')) {
            // register container execution for shutdown action
            IfwPsn_Wp_Proxy_Action::add('shutdown', array($this, 'execute'));
        } else {
            $this->execute();
        }
    }

    /**
     * Gets executed on shutdown to get all meanwhile added custom fields
     */
    public function execute()
    {
        /**
         * @var Psn_Notification_Deferred_Container $container
         */
        foreach ($this->_container as $container) {
            if ($container->matchesPost($this->_post)) {
                $container->execute($this->_post);
            }
        }
    }

    /**
     * @param Psn_Notification_Deferred_Container $container
     */
    public function addCotainer(Psn_Notification_Deferred_Container $container)
    {
        array_push($this->_container, $container);
    }
}
 