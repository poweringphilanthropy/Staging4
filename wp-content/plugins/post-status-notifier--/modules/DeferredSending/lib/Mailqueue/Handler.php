<?php
/**
 * Mailqueue Handler
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Handler.php 334 2014-11-08 13:46:08Z timoreithde $
 * @package
 */

class Psn_Module_DeferredSending_Mailqueue_Handler 
{
    /**
     * Mailqueue DB model class name
     */
    const MODEL = 'Psn_Module_DeferredSending_Model_MailQueue';

    /**
     * Mailqueue log DB model class name
     */
    const MODEL_LOG = 'Psn_Module_DeferredSending_Model_MailQueueLog';

    /**
     * default recurrence for cron
     */
    const DEFAULT_RECURRENCE = 'hourly';

    const RECURRENCE_MANUALLY = 'manually';

    /**
     * cron hook name to be used in wp_schedule_event
     */
    const CRON_HOOK = 'psn_mailqueue_cron';

    /**
     * @var Psn_Module_DeferredSending_Mailqueue_Handler
     */
    protected static $_instance;

    /**
     * @var bool
     */
    protected $_isLoaded = false;

    /**
     * @var int
     */
    protected $_maxAmount = 10;

    /**
     * @var int
     */
    protected $_maxTries = 10;

    /**
     * @var string
     */
    protected $_recurrence;

    /**
     * @var int
     */
    protected $_addCounter = 0;

    /**
     * @var bool
     */
    protected $_logSent = false;

    /**
     * @var bool
     */
    protected $_runAfterAdd = false;



    /**
     * @return Psn_Module_DeferredSending_Mailqueue_Handler
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function load()
    {
        if (!$this->_isLoaded) {
            IfwPsn_Wp_Proxy_Filter::add('ifwpsn_callback_email_process', array($this, 'onEmailProcess'), 10, 3);

            IfwPsn_Wp_Proxy_Action::add('shutdown', array($this, 'onShutdown'));

            // register the cron hook
            IfwPsn_Wp_Proxy_Action::addInit(array($this, 'setupCronHook'));
            // add run method to cron hook
            IfwPsn_Wp_Proxy_Action::add(self::CRON_HOOK, array($this, 'run'));

            IfwPsn_Wp_Proxy_Filter::add('pre_update_option_psn_options', array($this, 'resetCronSetup'), 10, 2);

            $this->_isLoaded = true;
        }
    }

    /**
     * Runs the mailqueue
     */
    public function run()
    {
        $data = IfwPsn_Wp_ORM_Model::factory(self::MODEL)->filter('scheduled', $this->_maxAmount, $this->_maxTries);

        /**
         * @var IfwPsn_Wp_ORM_Model $row
         */
        foreach ($data->find_many() as $row) {

            $email = new IfwPsn_Wp_Email('psn_mailqueue');
            $email
                ->setTo($row->get('to'))
                ->setSubject($row->get('subject'))
                ->setMessage($row->get('message'))
                ;

            $headers = $row->get('headers');
            if (!empty($headers)) {
                $email->setAdjustedHeaders(unserialize($headers));
            }
            $options = $row->get('options');
            if (!empty($options)) {
                $email->setOptions(unserialize($options));
            }
            if ($row->get('html') == '1') {
                $email->setHTML(true);
                $email->setAltbody($row->get('altbody'));
            }


            if ($email->send()) {
                // email sent successfully
                if ($this->isLogSent()) {
                    $this->log($row);
                }
                $row->delete();

            } else {
                // problem in sending
                $row->set_expr('tries', 'tries + 1');
                $row->save();
            }

        }
    }

    /**
     * Creates mailqueue log entry
     *
     * @param IfwPsn_Wp_ORM_Model $model
     */
    public function log(IfwPsn_Wp_ORM_Model $model)
    {
        $values = $model->as_array();
        $values['sent'] = gmdate('Y-m-d H:i:s');
        $values['tries']++;
        unset($values['id']);

        IfwPsn_Wp_ORM_Model::factory(self::MODEL_LOG)->create($values)->save();
    }

    /**
     * @param $process
     * @param array $values
     * @param IfwPsn_Wp_Email $email
     * @return bool
     */
    public function onEmailProcess($process, array $values, IfwPsn_Wp_Email $email)
    {
        $result = $process;

        if ($email->getIdentifier() == 'psn_email_service') {

            if ($email->isHTML()) {
                $values['html'] = 1;
                $values['altbody'] = $email->getAltbody();
            } else {
                $values['html'] = 0;
                $values['altbody'] = null;
            }
            $options = $email->getOptions();
            if (!empty($options)) {
                $values['options'] = serialize($options);
            } else {
                $values['options'] = null;
            }
            $this->add($values);

            // stop default handling by return false
            $result = false;
        }

        return $result;
    }

    /**
     * @param array $values
     */
    public function add(array $values)
    {
        $values['added'] = gmdate('Y-m-d H:i:s');
        $values['scheduled'] = gmdate('Y-m-d H:i:s');
        $values['headers'] = serialize($values['headers']);
        $values['attachments'] = null;

        // save mailqueue entry
        $item = IfwPsn_Wp_ORM_Model::factory(self::MODEL)->create($values);
        $item->save();

        $this->_addCounter++;
    }

    public function setupCronHook()
    {
        if ($this->getRecurrence() != self::RECURRENCE_MANUALLY && !wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), $this->getRecurrence(), self::CRON_HOOK);
        }
    }

    public function resetCronSetup($value, $oldvalue)
    {
        if (array_key_exists('psn_option_psn_deferred_sending_recurrence', $value)) {

            $timestamp = wp_next_scheduled(self::CRON_HOOK);
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }

        return $value;
    }

    /**
     * @return int
     */
    public function getMaxAmount()
    {
        return $this->_maxAmount;
    }

    /**
     * @param int $maxAmount
     */
    public function setMaxAmount($maxAmount)
    {
        if (is_int($maxAmount) && $maxAmount > 0) {
            $this->_maxAmount = $maxAmount;
        }
    }

    /**
     * @return int
     */
    public function getMaxTries()
    {
        return $this->_maxTries;
    }

    /**
     * @param int $maxTries
     */
    public function setMaxTries($maxTries)
    {
        if (is_int($maxTries) && $maxTries > 0) {
            $this->_maxTries = $maxTries;
        }
    }

    /**
     * @return string
     */
    public function getRecurrence()
    {
        if ($this->_recurrence === null) {
            return self::DEFAULT_RECURRENCE;
        }
        return $this->_recurrence;
    }

    /**
     * @param string $recurrence
     */
    public function setRecurrence($recurrence)
    {
        $this->_recurrence = $recurrence;
    }

    /**
     * @return boolean
     */
    public function isLogSent()
    {
        return $this->_logSent === true;
    }

    /**
     * @param boolean $logSent
     */
    public function setLogSent($logSent)
    {
        if (is_bool($logSent)) {
            $this->_logSent = $logSent;
        }
    }

    /**
     * @return boolean
     */
    public function isRunAfterAdd()
    {
        return $this->_runAfterAdd;
    }

    /**
     * @param boolean $runAfterAdd
     */
    public function setRunAfterAdd($runAfterAdd)
    {
        if (is_bool($runAfterAdd)) {
            $this->_runAfterAdd = $runAfterAdd;
        }
    }

    public function onShutdown()
    {
        if ($this->_addCounter > 0) {
            if ($this->isRunAfterAdd()) {
                $this->run();
            }
            IfwPsn_Wp_Proxy_Action::doAction('psn_mailqueue_added', $this->_addCounter);
        }
    }
}
 