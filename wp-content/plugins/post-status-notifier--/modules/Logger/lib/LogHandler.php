<?php
/**
 * Log Handler
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: LogHandler.php 358 2014-12-14 17:20:47Z timoreithde $
 * @package
 */
class Psn_Module_Logger_LogHandler
{
    /**
     * @var IfwPsn_Wp_Plugin_Logger
     */
    protected $_logger;



    /**
     * @param $logger
     */
    public function __construct($logger)
    {
        $this->_logger = $logger;
    }

    public function load()
    {
        IfwPsn_Wp_Proxy_Action::add('ifwpsn_callback_after_email_send', array($this, 'logMailSend'), 10, 3);

        IfwPsn_Wp_Proxy_Action::add('psn_mailqueue_added', array($this, 'logMailqueueAdd'));
        IfwPsn_Wp_Proxy_Action::add('psn_limitation_reached', array($this, 'logLimitationReached'), 10, 2);
    }

    /**
     * @param $result
     * @param array $emailParams
     * @param IfwPsn_Wp_Email $email
     */
    public function logMailSend($result, array $emailParams, IfwPsn_Wp_Email $email)
    {
        if (!in_array($email->getIdentifier(), array('psn_email_service', 'psn_mailqueue')) ||
            !$email->hasOption('service_log') ||
            $email->getOption('service_log') === false) {

            return;
        }

        if ($result === true) {
            $logTitle = __('Email was sent', 'psn_log') . ': ' . $email->getSubject();
            $logType = Psn_Logger_Bootstrap::LOG_TYPE_SENT_MAIL;
        } else {
            $logTitle = __('Email could not be sent', 'psn_log') . ': ' . $email->getSubject();
            $logType = Psn_Logger_Bootstrap::LOG_TYPE_FAILURE;
        }

        $emailParams['html'] = $email->isHTML();
        $extra = json_encode($emailParams);

        $this->_logger->info($logTitle, array(
            'extra' => $extra,
            'type' => $logType)
        );
    }

    /**
     * @param $addCounter
     */
    public function logMailqueueAdd($addCounter)
    {
        $this->_logger->info(sprintf(__('%s emails added to mailqueue', 'psn_log'), $addCounter), array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_INFO)
        );
    }

    /**
     * @param Psn_Model_Rule $rule
     * @param $post
     */
    public function logLimitationReached(Psn_Model_Rule $rule, $post)
    {
        $titleFormat = __('Notification limit reached on Post "%s" with rule "%s"', 'psn_log');

        $extra = '';
        $extra .= __('Limitations settings', 'psn_log') . ':' . PHP_EOL . PHP_EOL;

        $type = Psn_Module_Limitations_Mapper::getLimitType();
        $trigger = Psn_Module_Limitations_Mapper::getLimitationTrigger();

        $extra .= __('Trigger', 'psn_lmt') . ': ' . (($trigger == 'global') ? __('Global settings') : '') . PHP_EOL;
        $extra .= __('Type', 'psn_lmt') . ': ' . Psn_Module_Limitations_Mapper::getLimitTypeLabel($type) . PHP_EOL;
        $extra .= __('Limit count', 'psn_lmt') . ': ' . Psn_Module_Limitations_Mapper::getLimitCount();

        $extra .= PHP_EOL . PHP_EOL;

        $extra .= __('Post data', 'psn_log') . ':' . PHP_EOL . PHP_EOL;
        $extra .= __('Title', 'psn') . ': ' . $post->post_title . PHP_EOL;
        $extra .= __('Status after', 'psn') . ': ' . $post->post_status . PHP_EOL;

        $extra .= PHP_EOL . PHP_EOL;

        $extra .= __('Rule', 'psn') . ': ' . $rule->get('name');

        $this->_logger->info(sprintf($titleFormat, $post->post_title, $rule->get('name')), array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_INFO,
            'extra' => $extra
            )
        );
    }

    /**
     * @param $controller
     */
    public function onSendTestMailSuccess($controller)
    {
        $this->_logger->info(__('Test email has been sent successfully.', 'psn'), array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_SUCCESS)
        );
    }

    /**
     * @param $controller
     */
    public function onSendTestMailFailure($controller)
    {
        $this->_logger->info(__('Test email could not be sent.', 'psn'), array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_FAILURE
        ));
    }
}
 