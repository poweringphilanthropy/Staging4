<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: ErrorHandler.php 300 2014-07-06 22:22:07Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_ErrorHandler 
{
    /**
     * @var IfwPsn_Wp_ErrorHandler
     */
    protected static $_instance;

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * The default error reporting level
     * @var int
     */
    protected $_errorReportingLevel;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return IfwPsn_Wp_ErrorHandler
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (self::$_instance == null) {
            self::$_instance = new self($pm);
        }
        return self::$_instance;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    /**
     * Enables the error reporting in dev mode
     */
    public function enableErrorReporting()
    {
        if ($this->_pm->getEnv()->getEnvironmet() == 'development' || $this->_pm->getConfig()->debug->show_errors == '1') {

            // store the default error level
            $this->_errorReportingLevel = error_reporting();

            // E_ALL & ~E_STRICT
            error_reporting(6143);
        }
    }

    /**
     * Resets the error reporting to default in dev mode
     */
    public function disableErrorReporting()
    {
        if ($this->_pm->getEnv()->getEnvironmet() == 'development' || $this->_pm->getConfig()->debug->show_errors == '1') {
            error_reporting($this->_errorReportingLevel);
        }
    }
}
 