<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2015 ifeelweb.de
 * @version   $Id: Handler.php 365 2015-04-02 22:10:47Z timoreithde $
 * @package
 */

class Psn_Module_Premium_Conditions_Handler
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var bool
     */
    protected $_enableConditionsSubject = false;

    /**
     * @var bool
     */
    protected $_enableConditionsBody = false;

    /**
     * @var bool
     */
    protected $_enableConditionsDynamicRecipients = false;

    /**
     * @var bool
     */
    protected $_enableConditionsErrorLogging = false;

    /**
     * @var Psn_Module_Premium_Conditions_Handler
     */
    protected static $_instance;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return Psn_Module_Premium_Conditions_Handler
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($pm);
        }
        return self::$_instance;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @internal param null $logger
     */
    protected function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        $this->_init();
    }

    /**
     * Init options and actions/filters
     */
    protected function _init()
    {
        if ($this->_pm->getBootstrap()->getOptionsManager()->isNotEmptyOption('psn_conditions_log')) {
            $this->setEnableConditionsErrorLogging(true);
        }
        if ($this->_pm->getBootstrap()->getOptionsManager()->isNotEmptyOption('psn_conditions_enable_subject')) {
            $this->setEnableConditionsSubject(true);
        }
        if ($this->_pm->getBootstrap()->getOptionsManager()->isNotEmptyOption('psn_conditions_enable_body')) {
            $this->setEnableConditionsBody(true);
        }
        if ($this->_pm->getBootstrap()->getOptionsManager()->isNotEmptyOption('psn_conditions_enable_dyn_to')) {
            $this->setEnableConditionsDynamicRecipients(true);
        }
    }

    /**
     * @param boolean $enableConditionsBody
     */
    public function setEnableConditionsBody($enableConditionsBody)
    {
        if (is_bool($enableConditionsBody)) {
            $this->_enableConditionsBody = $enableConditionsBody;
        }
    }

    /**
     * @return boolean
     */
    public function isEnableConditionsBody()
    {
        return $this->_enableConditionsBody === true;
    }

    /**
     * @param boolean $enableConditionsSubject
     */
    public function setEnableConditionsSubject($enableConditionsSubject)
    {
        if (is_bool($enableConditionsSubject)) {
            $this->_enableConditionsSubject = $enableConditionsSubject;
        }
    }

    /**
     * @return boolean
     */
    public function isEnableConditionsSubject()
    {
        return $this->_enableConditionsSubject === true;
    }

    /**
     * @param boolean $enableConditionsErrorLogging
     */
    public function setEnableConditionsErrorLogging($enableConditionsErrorLogging)
    {
        if (is_bool($enableConditionsErrorLogging)) {
            $this->_enableConditionsErrorLogging = $enableConditionsErrorLogging;
        }
    }

    /**
     * @return boolean
     */
    public function isEnableConditionsErrorLogging()
    {
        return $this->_enableConditionsErrorLogging === true;
    }

    /**
     * @return boolean
     */
    public function isEnableConditionsDynamicRecipients()
    {
        return $this->_enableConditionsDynamicRecipients;
    }

    /**
     * @param boolean $enableConditionsDynamicRecipients
     */
    public function setEnableConditionsDynamicRecipients($enableConditionsDynamicRecipients)
    {
        if (is_bool($enableConditionsDynamicRecipients)) {
            $this->_enableConditionsDynamicRecipients = $enableConditionsDynamicRecipients;
        }
    }

    /**
     * @return array|null
     */
    public function getConditionsDebugger()
    {
        if ($this->isEnableConditionsErrorLogging()) {
            return array($this, 'logConditionError');
        } else {
            return null;
        }
    }

    /**
     * @param Exception $e
     */
    public function logConditionError(Exception $e)
    {
        $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->err('Error while parsing conditions', array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_FAILURE,
            'extra' => $e->getMessage()
        ));
    }

}
