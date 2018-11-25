<?php
/**
 * MailQueue model mapper
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailQueue.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_DeferredSending_Model_Mapper_MailQueue extends IfwPsn_Wp_Model_Mapper_Abstract
{
    protected static $_instance;

    /**
     * @return Psn_Module_DeferredSending_Model_Mapper_MailQueue
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
        return 'mailqueue';
    }

    /**
     * @return string
     */
    public function getPlural()
    {
        return 'mailqueues';
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
