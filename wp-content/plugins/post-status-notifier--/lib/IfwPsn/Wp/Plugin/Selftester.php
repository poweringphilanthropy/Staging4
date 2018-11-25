<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Performs registered plugin tests
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Selftester.php 461 2015-09-09 22:16:16Z timoreithde $
 * @package   IfwPsn_Wp_Plugin
 */ 
class IfwPsn_Wp_Plugin_Selftester 
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    private $_pm;

    /**
     * @var array
     */
    private $_testCases = array();

    /**
     * @var bool
     */
    private $_status = true;

    /**
     * @var string
     */
    private $_timestampOptionName = 'selftest_timestamp';

    /**
     * @var string
     */
    private $_statusOptionName = 'selftest_status';



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->_init();
    }

    protected function _init()
    {
        $this->_registerBuiltinTests();
    }

    /**
     * Registers the built-in tests
     */
    protected function _registerBuiltinTests()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Selftest/Case/WpVersion.php';

        $this->addTestCase(new IfwPsn_Wp_Plugin_Selftest_Case_WpVersion());
    }

    public function activate()
    {
        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_selftester_activate', $this);

        $this->_pm->getBootstrap()->getOptionsManager()->registerExternalOption($this->_timestampOptionName);
        $this->_pm->getBootstrap()->getOptionsManager()->registerExternalOption($this->_statusOptionName);

        if (!$this->_skipAutorun()) {
            $this->_initAutorun();
        }
    }

    /**
     *
     */
    protected function _initAutorun()
    {
        $interval = $this->_pm->getConfig()->plugin->selftestInterval;

        if (!empty($interval)) {
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Date.php';

            if ($this->getTimestamp() == null ||
                IfwPsn_Wp_Date::isOlderThanSeconds($this->getTimestamp(), $interval)) {
                // perform a selftest if no one was run before or the selftest interval is exceeded
                IfwPsn_Wp_Proxy_Action::addPluginsLoaded(array($this, 'performTests'));
            }
        }
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftest_Interface $test
     */
    public function addTestCase(IfwPsn_Wp_Plugin_Selftest_Interface $test)
    {
        $this->_testCases[md5(get_class($test))] = $test;
    }

    /**
     * @return array
     */
    public function getTestCases()
    {
        return $this->_testCases;
    }

    /**
     * @param $key
     * @return null
     */
    public function getTest($key)
    {
        if (isset($this->_testCases[$key])) {
            return $this->_testCases[$key];
        }
        return null;
    }

    /**
     * Performs all registered tests
     */
    public function performTests()
    {
        /**
         * @var $test IfwPsn_Wp_Plugin_Selftest_Interface
         */
        foreach($this->_testCases as $test) {

            $test->execute($this->_pm);

            if (!$test->getResult()) {
                $this->_status = 'false';
            }
        }

        $this->_updateStatus();
        $this->_updateTimestamp();
    }

    /**
     * Updates the status of the last test
     */
    protected function _updateStatus()
    {
        $this->_pm->getBootstrap()->getOptionsManager()->updateOption($this->_statusOptionName, $this->_status);
    }

    /**
     * Retrieves the status of the last test
     * @return boolean
     */
    public function getStatus()
    {
        return $this->_pm->getBootstrap()->getOptionsManager()->getOption($this->_statusOptionName);
    }

    /**
     * Updates the timestamp of the last test
     */
    public function _updateTimestamp()
    {
        $this->_pm->getBootstrap()->getOptionsManager()->updateOption($this->_timestampOptionName, gmdate('Y-m-d H:i:s'));
    }

    /**
     * Retrieves the timestamp of the last test
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->_pm->getBootstrap()->getOptionsManager()->getOption($this->_timestampOptionName);
    }

    /**
     * @return bool
     */
    protected function _skipAutorun()
    {
        if ($this->_pm->getBootstrap()->getUpdateManager()->getPatcher()->isPatchesAvailable()) {
            return true;
        }

        return false;
    }
}
