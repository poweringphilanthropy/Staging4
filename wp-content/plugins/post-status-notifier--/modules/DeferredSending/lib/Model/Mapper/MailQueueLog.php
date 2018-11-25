<?php
/**
 * MailQueueLog model mapper
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailQueueLog.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_DeferredSending_Model_Mapper_MailQueueLog extends IfwPsn_Wp_Model_Mapper_Abstract
{
    protected static $_instance;

    /**
     * @return Psn_Module_DeferredSending_Model_Mapper_MailQueueLog
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return string
     */
    public function getSingular()
    {
        return 'mailqueuelog';
    }

    /**
     * @return string
     */
    public function getPlural()
    {
        return 'mailqueuelogs';
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function getPerPageId($prefix = '')
    {
        return $prefix . 'per_page_' . $this->getSingular();
    }
}
