<?php
/**
 * Test for table
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: MailTemplateModel.php 144 2014-02-09 22:03:24Z timoreithde $
 * @package   
 */ 
class Psn_Module_HtmlMails_Test_MailTemplateModel implements IfwPsn_Wp_Plugin_Selftest_Interface
{
    private $_result = false;



    /**
     * Gets the test name
     * @return mixed
     */
    public function getName()
    {
        return __('Mail template table', 'psn_htm');
    }

    /**
     * Gets the test description
     * @return mixed
     */
    public function getDescription()
    {
        return __('Checks if the database table exists', 'psn');
    }

    /**
     * Runs the test
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return mixed
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $model = new Psn_Module_HtmlMails_Model_MailTemplates();
        if ($model->exists()) {
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
        return __('The database table could not be found', 'psn');
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
        $activation = new Psn_Module_HtmlMails_Installer_Activation();
        $activation->execute($pm);

        return __('Trying to create the table...', 'psn');
    }

}
