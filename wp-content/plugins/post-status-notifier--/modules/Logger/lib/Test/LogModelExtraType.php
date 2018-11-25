<?php
/**
 * Test for log table field type "extra"
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: LogModelExtraType.php 292 2014-08-13 20:36:13Z timoreithde $
 * @package   
 */ 
class Psn_Module_Logger_Test_LogModelExtraType implements IfwPsn_Wp_Plugin_Selftest_Interface
{
    private $_result = false;



    /**
     * Gets the test name
     * @return mixed
     */
    public function getName()
    {
        return __('Logger table field "extra"', 'psn');
    }

    /**
     * Gets the test description
     * @return mixed
     */
    public function getDescription()
    {
        return __('Checks the type of field "extra" and extends it if necessary', 'psn_log');
    }

    /**
     * Runs the test
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return mixed
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_result = true;

        $db = IfwPsn_Wp_Proxy_Db::getObject();

        $query = 'SHOW FIELDS FROM `'. $db->prefix . Psn_Module_Logger_Model_Log::$_table .'` WHERE Field = "extra"';
        $result = $db->get_row($query, ARRAY_A);

        if ($result != null) {
            if (isset($result['Type']) && strtolower($result['Type']) != 'longtext') {
                $this->_result = false;
            }
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
        return __('Field type is not up-to-date', 'psn');
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
        $db = IfwPsn_Wp_Proxy_Db::getObject();

        $query = 'ALTER TABLE `'. $db->prefix . Psn_Module_Logger_Model_Log::$_table .'` CHANGE  `extra`  `extra` LONGTEXT';
        $result = $db->query($query);

        return __('Trying to update field ...', 'psn');
    }

}
