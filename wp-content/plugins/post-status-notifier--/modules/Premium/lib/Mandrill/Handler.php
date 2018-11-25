<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Handler.php 375 2015-04-14 16:14:38Z timoreithde $
 * @package
 */

class Psn_Module_Premium_Mandrill_Handler
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;


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
        add_action('psn_after_notification_email_send', array($this, 'afterNotificationEmailSend'));
        add_action('psn_after_test_email_send', array($this, 'handleMandrillSend'));
        add_filter('ifwpsn_callback_email_process', array($this, 'stopEmailProcess'), 10, 3);
    }

    /**
     * @param Psn_Notification_Service_Email $serviceEmail
     */
    public function afterNotificationEmailSend(Psn_Notification_Service_Email $serviceEmail)
    {
        $this->handleMandrillSend($serviceEmail->getEmail());
    }

    /**
     * @param IfwPsn_Wp_Email $email
     */
    public function handleMandrillSend(IfwPsn_Wp_Email $email)
    {
        require_once dirname(__FILE__) . '/Mandrill.php';

        $apiKey = esc_attr(trim($this->_pm->getOption('psn_mandrill_api_key')));
        $apiKey = IfwPsn_Util_Encryption::decrypt($apiKey, Psn_Module_Premium_Mandrill_Feature::API_KEY_SALT);
        $mandrill = new Mandrill($apiKey);

        if ($email->isHTML()) {
            $html = $email->getBody();
            $text = $email->getAltbody();
        } else {
            $html = null;
            $text = $email->getBody();
        }

        $from = $email->getFrom();
        if (empty($from)) {
            $from = IfwPsn_Wp_Proxy_Blog::getDefaultEmailFrom();
        }

        if (strpos($from, '<') !== false) {
            $from = $this->_getEmailString($from);
            $fromEmail = $from['email'];
            $fromName = $from['name'];
        } else {
            $fromEmail = $from;
            $fromName = null;
        }

        $to = array();

        $to = array_merge($to, $this->_getRecipients($email->getTo()));
        $to = array_merge($to, $this->_getRecipients($email->getCc(), 'cc'));
        $to = array_merge($to, $this->_getRecipients($email->getBcc(), 'bcc'));

        $message = array(
            'html' => $html,
            'text' => $text,
            'subject' => $email->getSubject(),
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to' => $to,
        );

        try {
            $result = $mandrill->messages->send($message);

            if ($this->_pm->hasOption('psn_mandrill_log')) {
                $this->_logMessage($email);
                $this->_logResult($result);
            }
        } catch (Exception $e) {
            if ($this->_pm->hasOption('psn_mandrill_log')) {
                $this->_logException($e);
            }
        }
    }

    /**
     * @param $recipients
     * @param string $type
     * @return array
     */
    protected function _getRecipients($recipients, $type = 'to')
    {
        $result = array();
        $recipients = html_entity_decode($recipients);

        if (!empty($recipients)) {
            foreach (explode(',', $recipients) as $recipient) {
                $newRec = array();
                $newRec['type'] = $type;
                if (strpos($recipient, '<') !== false) {
                    $recipient = $this->_getEmailString($recipient);
                    $newRec['email'] = $recipient['email'];
                    $newRec['name'] = $recipient['name'];
                } else {
                    $newRec['email'] = $recipient;
                }
                array_push($result, $newRec);
            }
        }

        return $result;
    }

    /**
     * @param $str
     * @return mixed
     */
    protected function _getEmailString($str)
    {
        preg_match_all('/(.*?)\s+<\s*(.*?)\s*>/', $str, $matches);

        $result = array();
        for ($i=0; $i<count($matches[0]); $i++) {
            $result[] = array(
                'name' => $matches[1][$i],
                'email' => $matches[2][$i],
            );
        }

        if (isset($result[0])) {
            return $result[0];
        }
        return null;
    }

    /**
     * @param array $message
     */
    protected function _logMessage(IfwPsn_Wp_Email $email)
    {
        $logTitle = __('Email was passed to Mandrill', 'psn_prm') . ': ' . $email->getSubject();
        $logType = Psn_Logger_Bootstrap::LOG_TYPE_SENT_MAIL;

        $params = array(
            'to' => $email->getTo(),
            'subject' => $email->getSubject(),
            'message' => $email->getMessage(),
            'headers' => $email->getAdjustedHeaders()
        );

        $extra = json_encode($params);

        $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->info($logTitle, array(
            'extra' => $extra,
            'type' => $logType)
        );
    }

    /**
     * @param array $result
     */
    protected function _logResult(array $result)
    {
        $logExtra = '';
        foreach ($result as $row) {
            $logExtra .= 'email: ' . $row['email'] . PHP_EOL;
            $logExtra .= 'status: ' . $row['status'] . PHP_EOL;
            $logExtra .= '_id: ' . $row['_id'] . PHP_EOL;
            if (isset($row['reject_reason'])) {
                $logExtra .= 'reject_reason: ' . $row['reject_reason'] . PHP_EOL;
            }
            $logExtra .= PHP_EOL . PHP_EOL;
        }
        $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->info('Mandrill log', array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_INFO,
            'extra' => $logExtra
        ));
    }

    /**
     * @param Exception $e
     */
    protected function _logException(Exception $e)
    {
        $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->error('Mandrill error', array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_FAILURE,
            'extra' => $e->getMessage()
        ));
    }

    /**
     * @param $process
     * @param $emailParams
     * @param $email
     * @return bool
     */
    public function stopEmailProcess($process, $emailParams, IfwPsn_Wp_Email $email)
    {
        if ($email->getIdentifier() == Psn_Notification_Service_Email::EMAIL_IDENTIFIER) {
            // do not send via internal email
            return false;
        }
    }
}
