<?php
/**
 * RecipientsLists model mapper
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: RecipientsLists.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_Recipients_Model_Mapper_RecipientsLists extends IfwPsn_Wp_Model_Mapper_Abstract
{
    protected static $_instance;

    /**
     * @return Psn_Module_Recipients_Model_Mapper_RecipientsLists
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
        return 'recipientslist';
    }

    /**
     * @return string
     */
    public function getPlural()
    {
        return 'recipientslists';
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
