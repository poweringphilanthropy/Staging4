<?php
/**
 * MailTemplates model mapper
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailTemplates.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_HtmlMails_Model_Mapper_MailTemplates extends IfwPsn_Wp_Model_Mapper_Abstract
{
    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getSingular()
    {
        return 'mail_template';
    }

    public function getPlural()
    {
        return 'mail_templates';
    }

    public function getPerPageId($prefix = '')
    {
        return $prefix . 'per_page_' . $this->getSingular();
    }
}
