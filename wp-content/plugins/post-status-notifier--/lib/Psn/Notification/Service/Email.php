<?php
/**
 * This class handles the email sending process
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: Email.php 437 2015-11-04 17:06:34Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Notification
 */
require_once dirname(__FILE__) . '/Interface.php';

class Psn_Notification_Service_Email implements Psn_Notification_Service_Interface
{
    const EMAIL_IDENTIFIER = 'psn_email_service';

    /**
     * @var Psn_Model_Rule
     */
    protected $_rule;

    /**
     * @var object
     */
    protected $_post;

    /**
     * @var IfwPsn_Wp_Email
     */
    protected $_email;

    /**
     * @var string
     */
    protected $_body;

    /**
     * @var string
     */
    protected $_subject;

    /**
     * @var array
     */
    protected $_to = array();

    /**
     * @var array
     */
    protected $_cc = array();

    /**
     * @var array
     */
    protected $_bcc = array();




    /**
     * @param Psn_Model_Rule $rule
     * @param $post
     */
    public function execute(Psn_Model_Rule $rule, $post)
    {
        if ((int)$rule->get('service_email') !== 1) {
            return;
        }

        $this->_reset();

        $this->_rule = $rule;
        $this->_post = $post;

        // create email object
        $this->_email = new IfwPsn_Wp_Email(self::EMAIL_IDENTIFIER);

        // prepare recipients
        $this->_prepareRecipients($rule, $post);

        if(!empty($this->_to)) {
            // send email

            $this->_email->setTo($this->getFormattedEmails($this->_to))
                ->setSubject($this->_getPreparedSubject($rule))
                ->setMessage($this->_getPreparedBody($rule))
            ;

            if ($this->hasCc()) {
                $this->_email->setCc($this->getFormattedEmails($this->_cc));
            }
            if ($this->hasBcc()) {
                $this->_email->setBcc($this->getFormattedEmails($this->_bcc));
            }

            if ((int)$this->_rule->get('service_log') === 1) {
                $this->_email->setOption('service_log', true);
            } else {
                $this->_email->setOption('service_log', false);
            }

            IfwPsn_Wp_Proxy_Action::doAction('psn_before_notification_email_send', $this);

            $this->_email->send();

            IfwPsn_Wp_Proxy_Action::doAction('psn_after_notification_email_send', $this);
        }
    }

    /**
     * Resets the email properties buffer variables
     */
    protected function _reset()
    {
        $this->_body = null;
        $this->_subject = null;
        $this->_to = array();
        $this->_cc = array();
        $this->_bcc = array();
    }

    /**
     * @param Psn_Model_Rule $rule
     * @return mixed|void
     */
    protected function _getPreparedBody(Psn_Model_Rule $rule)
    {
        $body = $rule->getNotificationBody();

        /**
         * Email service body filter
         * @param string the email body
         * @param Psn_Notification_Service_Email the email service
         */
        return IfwPsn_Wp_Proxy_Filter::apply('psn_service_email_body', $body, $this);
    }

    /**
     * @param Psn_Model_Rule $rule
     * @return mixed|void
     */
    protected function _getPreparedSubject(Psn_Model_Rule $rule)
    {
        $subject = $rule->getNotificationSubject();

        /**
         * Final subject filter
         * @param string the email subject
         */
        return IfwPsn_Wp_Proxy_Filter::apply('psn_service_email_subject', $subject, $this);
    }

    /**
     * Prepares TO, CC and BCC recipients
     *
     * @param Psn_Model_Rule $rule
     * @param $post
     */
    protected function _prepareRecipients(Psn_Model_Rule $rule, $post)
    {
        // recipient handling (To, Cc, Bcc)
        $recipientSelections = array(
            array(
                'name' => 'recipient_selection',
                'modelGetter' => 'getRecipient',
                'serviceAdder' => 'addTo',
                'custom_field_name' => 'to'
            ),
            array(
                'name' => 'cc_selection',
                'modelGetter' => 'getCcSelect',
                'serviceAdder' => 'addCc',
                'custom_field_name' => 'cc'
            ),
            array(
                'name' => 'bcc_selection',
                'modelGetter' => 'getBccSelect',
                'serviceAdder' => 'addBcc',
                'custom_field_name' => 'bcc'
            ),
        );

        foreach ($recipientSelections as $recSel) {

            $recipient = $rule->$recSel['modelGetter']();
            if (in_array('admin', $recipient)) {
                $this->$recSel['serviceAdder'](IfwPsn_Wp_Proxy_Blog::getAdminEmail());
            }
            if (in_array('author', $recipient)) {
                $this->$recSel['serviceAdder'](IfwPsn_Wp_Proxy_User::getEmail($post->post_author));
            }

            // handle dynamic recipients managed by modules
            IfwPsn_Wp_Proxy_Action::doAction('psn_service_email_'. $recSel['name'], $this);

            // check for custom recipient
            $custom_recipient = $rule->get($recSel['custom_field_name']);
            if (!empty($custom_recipient)) {
                $custom_recipient = $rule->getReplacer()->replace($custom_recipient);

                $customRecipientStack = explode(',', $custom_recipient);
                foreach ($customRecipientStack as $customRecipientEmail) {
                    $this->$recSel['serviceAdder'](trim($customRecipientEmail));
                }
            }
        }


        if ($rule->isExcludeCurrentUser()) {
            // exclude current user from recipients
            $this->_to = $this->_removeCurrentUserEmail($this->_to);
            $this->_cc = $this->_removeCurrentUserEmail($this->_cc);
            $this->_bcc = $this->_removeCurrentUserEmail($this->_bcc);
        }
    }

    /**
     * @param array $recipients
     * @return array
     */
    protected function _removeCurrentUserEmail(array $recipients)
    {
        $currentUserEmail = strtolower(trim(IfwPsn_Wp_Proxy_User::getCurrentUserEmail()));

        foreach ($recipients as $k => $v) {
            if (strpos($v, '<') !== false) {
                preg_match('/<(.*?)>/', $v, $match);
                if (isset($match[1]) && !empty($match[1]) && strtolower(trim($match[1])) === $currentUserEmail) {
                    unset($recipients[$k]);
                }
            } else {
                if (strtolower(trim($v)) === $currentUserEmail) {
                    unset($recipients[$k]);
                }
            }
        }

        return $recipients;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to)
    {
        if (is_array($to)) {
            $this->_to = $to;
        }
    }

    /**
     * @param string $to
     */
    public function addTo($to)
    {
        array_push($this->_to, IfwPsn_Wp_Email::sanitizeEmail($to));
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * @param mixed $cc
     */
    public function setCc($cc)
    {
        if (is_array($cc)) {
            $this->_cc = $cc;
        }
    }

    /**
     * @param string $cc
     */
    public function addCc($cc)
    {
        array_push($this->_cc, IfwPsn_Wp_Email::sanitizeEmail($cc));
    }

    /**
     * @return mixed
     */
    public function getCc()
    {
        return $this->_cc;
    }

    /**
     * @return bool
     */
    public function hasCc()
    {
        return count($this->_cc) > 0;
    }

    /**
     * @param mixed $bcc
     */
    public function setBcc($bcc)
    {
        if (is_array($bcc)) {
            $this->_bcc = $bcc;
        }
    }

    /**
     * @param string $bcc
     */
    public function addBcc($bcc)
    {
        array_push($this->_bcc, IfwPsn_Wp_Email::sanitizeEmail($bcc));
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->_bcc;
    }

    /**
     * @return bool
     */
    public function hasBcc()
    {
        return count($this->_bcc) > 0;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * @return object
     */
    public function getPost()
    {
        return $this->_post;
    }

    /**
     * @return Psn_Model_Rule
     */
    public function getRule()
    {
        return $this->_rule;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @return \IfwPsn_Wp_Email
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @param array $emails
     * @return string
     */
    public function getFormattedEmails(array $emails)
    {
        $emails = array_unique($emails);
        return implode(',' , $emails);
    }

    /**
     * @param bool $set
     * @return $this
     */
    public function setLoopTo($set = true)
    {
        if (is_bool($set) && $this->_email instanceof IfwPsn_Wp_Email) {
            $this->_email->setLoopTo($set);
        }
        return $this;
    }

    /**
     * @param $secs
     * @return $this
     */
    public function setTimelimit($secs)
    {
        if (is_int($secs)) {
            $this->_email->setTimelimit($secs);
        }
        return $this;
    }
}
