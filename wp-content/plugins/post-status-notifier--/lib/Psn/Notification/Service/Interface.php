<?php
/**
 * Notification service interface
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: Interface.php 107 2014-01-08 01:39:03Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Notification
 */
interface Psn_Notification_Service_Interface
{
    public function execute(Psn_Model_Rule $rule, $post);
}
