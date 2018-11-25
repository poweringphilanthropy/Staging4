<?php
/**
 * Test for table field
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: MailTemplateField.php 143 2014-02-09 21:52:39Z timoreithde $
 * @package   
 */ 
class Psn_Module_HtmlMails_Test_MailTemplateField implements IfwPsn_Wp_Plugin_Selftest_Interface
{
    private $_result = false;



    /**
     * Gets the test name
     * @return mixed
     */
    public function getName()
    {
        return __('Mail template field', 'psn_htm');
    }

    /**
     * Gets the test description
     * @return mixed
     */
    public function getDescription()
    {
        return __('Checks if the field exists in the database', 'psn');
    }

    /**
     * Runs the test
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return mixed
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'mail_tpl')) {
            $this->_result = true;
        }
    }

    /**
     * Gets the test result, true on success, false on failure
     * @return bool
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Gets the error message
     * @return mixed
     */
    public function getErrorMessage()
    {
        return __('The database field could not be found', 'psn');
    }

    /**
     * @return bool
     */
    public function canHandle()
    {
        return true;
    }

    /**
     * Handles an error, should provide a solution for an unsuccessful test
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return mixed
     */
    public function handleError(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $patcher = new Psn_Patch_Database();
        $patcher->createRulesFieldMailTpl();

        return __('Trying to create the field...', 'psn');
    }

}
