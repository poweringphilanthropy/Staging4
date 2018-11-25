<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Logger
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Logger.php 454 2015-08-17 21:50:47Z timoreithde $
 * @package   IfwPsn_Wp_Plugin
 */
require_once dirname(__FILE__) . '/../../Vendor/Zend/Log.php';
require_once dirname(__FILE__) . '/../../Vendor/Zend/Log/FactoryInterface.php';

class IfwPsn_Wp_Plugin_Logger extends IfwPsn_Vendor_Zend_Log
{
    /**
     * Instance store to separate objects for multiple active plugins
     * @var array
     */
    public static $_instances = array();

    /**
     * @var array
     */
    public static $priorityInfo = array(
        IfwPsn_Vendor_Zend_Log::EMERG => 'Emergency',
        IfwPsn_Vendor_Zend_Log::ALERT => 'Alert',
        IfwPsn_Vendor_Zend_Log::CRIT => 'Critical',
        IfwPsn_Vendor_Zend_Log::ERR => 'Error',
        IfwPsn_Vendor_Zend_Log::WARN => 'Warning',
        IfwPsn_Vendor_Zend_Log::NOTICE => 'Notice',
        IfwPsn_Vendor_Zend_Log::INFO => 'Informational',
        IfwPsn_Vendor_Zend_Log::DEBUG => 'Debug',
    );

    /**
     * @var string
     */
    protected static $_defaultName = 'Default';


    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var
     */
    protected $_internalName;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $name
     * @throws IfwPsn_Wp_Plugin_Logger_Exception
     * @internal param \IfwPsn_Wp_Pathinfo_Plugin $pluginPathinfo
     * @internal param \IfwPsn_Wp_Plugin_Config $config
     * @return IfwPsn_Wp_Plugin_Logger
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm, $name = null)
    {
        if ($name === null) {
            $name = self::$_defaultName;
        }

        if (!isset(self::$_instances[$pm->getAbbr()][$name])) {

            if ($pm->getConfig()->log->file != '') {
                // custom log file
                $logFile = $pm->getConfig()->log->file;
            } else {
                // default log file
                $logFile = $pm->getPathinfo()->getRoot() . 'log'. DIRECTORY_SEPARATOR . 'plugin.log';
            }

            if (is_writable($logFile)) {
                // writable log file found
                require_once $pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Plugin/Logger.php';
                require_once $pm->getPathinfo()->getRootLib() . '/IfwPsn/Vendor/Zend/Log/Writer/Stream.php';
                $writer = new IfwPsn_Vendor_Zend_Log_Writer_Stream($logFile);

            } else {
                // no log file found
                require_once $pm->getPathinfo()->getRootLib() . '/IfwPsn/Vendor/Zend/Log/Writer/Null.php';
                $writer = new IfwPsn_Vendor_Zend_Log_Writer_Null();
            }

            self::create($pm, $writer, $name);
        }

        return self::$_instances[$pm->getAbbr()][$name];
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param null $name
     * @return bool
     */
    public static function hasInstance(IfwPsn_Wp_Plugin_Manager $pm, $name = null)
    {
        return isset(self::$_instances[$pm->getAbbr()][$name]);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param \IfwPsn_Vendor_Zend_Log_FactoryInterface|\IfwPsn_Vendor_Zend_Log_FactoryInterface $writer
     * @param null $name Loggername
     * @return IfwPsn_Wp_Plugin_Logger
     */
    public static function create(IfwPsn_Wp_Plugin_Manager $pm, IfwPsn_Vendor_Zend_Log_FactoryInterface $writer, $name = null)
    {
        if ($name === null) {
            $name = self::$_defaultName;
        }

        if (!isset(self::$_instances[$pm->getAbbr()][$name])) {
            // create logger
            $logger = new self($writer);
            $logger->setPluginManager($pm);
            $logger->setInternalName($name);
            self::$_instances[$pm->getAbbr()][$name] = $logger;
        } else {
            $logger = self::$_instances[$pm->getAbbr()][$name];
            if (!$logger->hasWriter($writer)) {
                $logger->addWriter($writer);
            }
        }

        switch (get_class($writer)) {
            case 'IfwPsn_Zend_Log_Writer_WpDb':
                $logger->setTimestampFormat('Y-m-d H:i:s');
                break;
        }

        return $logger;
    }

    /**
     * @param mixed $writer
     * @return bool
     */
    public function hasWriter($writer)
    {
        if (is_object($writer)) {
            $writer = get_class($writer);
        }

        foreach($this->_writers as $w) {
            if (get_class($w) == $writer) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function setPluginManager(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    /**
     * @param string $message
     * @param null $priority
     * @param null $extras
     * @see IfwPsn_Vendor_Zend_Log::log()
     */
    public function log($message, $priority=null, $extras=null)
    {
        if ($priority === null) {
            $priority = IfwPsn_Vendor_Zend_Log::INFO;
        }
        parent::log($message, $priority, $extras);
    }

    /**
     * Writes log entry with plugin abbreviation prefix
     *
     * @param $message
     * @param null $priority
     * @param null $extras
     */
    public function logPrefixed($message, $priority = null, $extras = null)
    {
        $message = $this->_pm->getAbbr() . ': ' . $message;
        $this->log($message, $priority, $extras);
    }

    /**
     * @param $message
     * @param null $extras
     * @param bool $append_backtrace
     * @throws IfwPsn_Vendor_Zend_Log_Exception
     */
    public function error($message, $extras = null, $append_backtrace = false)
    {
        if ($append_backtrace) {
            $message = $this->_appendBacktrace($message);
        }

        parent::log($message, IfwPsn_Vendor_Zend_Log::ERR, $extras);
    }

    /**
     * @param $message
     * @param null $extras
     * @param bool $append_backtrace
     * @throws IfwPsn_Vendor_Zend_Log_Exception
     */
    public function debug($message, $extras = null, $append_backtrace = false)
    {
        if ($append_backtrace) {
            $message = $this->_appendBacktrace($message);
        }

        parent::log($message, IfwPsn_Vendor_Zend_Log::DEBUG, $extras);
    }

    /**
     * @param $message
     * @return string
     */
    protected function _appendBacktrace($message)
    {
        $bt = debug_backtrace();

        $format = ' (file: %s, line: %s)';
        return $message . sprintf($format, $bt[1]['file'], $bt[0]['line']);
    }

    /**
     * Only supported by IfwPsn_Zend_Log_Writer_WpDb
     * @param array $options
     */
    public function clear($options = array())
    {
        foreach($this->_writers as $writer) {
            if (get_class($writer) == 'IfwPsn_Zend_Log_Writer_WpDb') {
                $logs = IfwPsn_Wp_ORM_Model::factory($writer->getModelName());

                if (isset($options['priority']) && !empty($options['priority'])) {
                    $logs->where_equal('priority', (int)$options['priority']);
                }
                if (isset($options['type']) && !empty($options['type'])) {
                    $logs->where_equal('type', (int)$options['type']);
                }
                $logs->delete_many();
            }
        }
    }

    /**
     * Checks if the table for IfwPsn_Zend_Log_Writer_WpDb is installed
     * @return bool
     */
    public function isInstalled()
    {
        $result = false;

        foreach($this->_writers as $writer) {
            if (get_class($writer) == 'IfwPsn_Zend_Log_Writer_WpDb') {
                // install the log table
                global $wpdb, $table_prefix;
                $r = new ReflectionProperty($writer->getModelName(), '_table');
                $query = sprintf('SHOW TABLES LIKE "%s"', $table_prefix . $r->getValue());
                if ($wpdb->get_row($query) !== null) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Installs log writers
     */
    public function install($networkwide = false)
    {
        foreach($this->_writers as $writer) {
            if (get_class($writer) == 'IfwPsn_Zend_Log_Writer_WpDb') {
                // install the log table
                $classname = $writer->getModelName();
                $logModel = new $classname();

                // get the table name of the model using reflection to support PHP 5.2
                $r = new ReflectionProperty($classname, '_table');
                $tableName = $r->getValue();

                $logModel->createTable($tableName, $networkwide);
            }
        }
    }

    /**
     * @param mixed $internalName
     */
    public function setInternalName($internalName)
    {
        $this->_internalName = $internalName;
    }

    /**
     * @return mixed
     */
    public function getInternalName()
    {
        return $this->_internalName;
    }

}

