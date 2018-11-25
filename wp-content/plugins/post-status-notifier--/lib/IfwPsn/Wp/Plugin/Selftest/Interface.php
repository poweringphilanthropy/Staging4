<?php
/**
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Interface.php 215 2014-01-08 01:37:51Z timoreithde $
 * @package
 */
interface IfwPsn_Wp_Plugin_Selftest_Interface
{
    /**
     * Gets the test name
     * @return mixed
     */
    public function getName();

    /**
     * Gets the test description
     * @return mixed
     */
    public function getDescription();

    /**
     * Runs the test
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return mixed
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm);

    /**
     * Gets the test result, true on success, false on failure
     * @return bool
     */
    public function getResult();

    /**
     * Gets the error message
     * @return mixed
     */
    public function getErrorMessage();

    /**
     * @return bool
     */
    public function canHandle();

    /**
     * Handles an error, should provide a solution for an unsuccessful test
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return mixed
     */
    public function handleError(IfwPsn_Wp_Plugin_Manager $pm);

}
