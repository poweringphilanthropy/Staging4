<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: ToLoopHandler.php 442 2015-11-28 09:19:14Z timoreithde $
 * @package
 */

class Psn_Module_ToLoop_ToLoopHandler
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var array
     */
    protected $_customPlaceholders = array(
        'recipient_first_name',
        'recipient_last_name',
        'recipient_user_email',
        'recipient_ID',
    );

    /**
     * @var array
     */
    protected $_emailContainsPlaceholdersStore = array();

    protected $_originalBody = array();
    protected $_originalSubject = array();



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
        IfwPsn_Wp_Proxy_Action::add('psn_before_notification_email_send', array($this, 'beforeNotificationEmailSend'));

        IfwPsn_Wp_Proxy_Action::add('ifwpsn_callback_email_loop_to', array($this, 'filterOnToLoop'), 10, 2);
        // add placeholders
        IfwPsn_Wp_Proxy_Action::add('psn_notification_placeholders_loaded', array($this, 'extendPlaceholders'));
    }

    /**
     * @param Psn_Notification_Service_Email $serviceEmail
     */
    public function beforeNotificationEmailSend(Psn_Notification_Service_Email $serviceEmail)
    {
        // check for TO loop
        if ($serviceEmail->getRule()->isLoopTo()) {
            $serviceEmail->setLoopTo(true);

            $timelimit = $this->_pm->getOptionsManager()->getOption('psn_to_loop_timelimit');
            if ($timelimit !== null && $timelimit != '') {
                $serviceEmail->setTimelimit((int)$timelimit);
            }
        }
    }

    /**
     * Adds placeholders
     *
     * @param Psn_Notification_Placeholders $placeholders
     */
    public function extendPlaceholders(Psn_Notification_Placeholders $placeholders)
    {
        // add custom placeholders to the backend list
        foreach ($this->_customPlaceholders as $placeholder) {
            $placeholders->addPlaceholder($placeholder, '');
            $placeholders->addSkipPlaceholder($placeholder);
        }
    }

    /**
     * @param $to
     * @param IfwPsn_Wp_Email $email
     */
    public function filterOnToLoop($to, IfwPsn_Wp_Email $email)
    {
        if ($this->_emailContainsPlaceholders($email)) {

            if (!isset($this->_originalBody[$email->getUniqueId()])) {
                $this->_originalBody[$email->getUniqueId()] = $email->getBody();
            }
            if (!isset($this->_originalSubject[$email->getUniqueId()])) {
                $this->_originalSubject[$email->getUniqueId()] = $email->getSubject();
            }

            $user = IfwPsn_Wp_Proxy_User::getByEmail($to);

            $replacements = $this->_getPopulatedPlaceholders($user);

            $email->setBody(strtr($this->_originalBody[$email->getUniqueId()], $replacements));
            $email->setSubject(strtr($this->_originalSubject[$email->getUniqueId()], $replacements));
        }
    }

    /**
     * @param $user
     * @return array
     */
    protected function _getPopulatedPlaceholders($user)
    {
        $replacements = array();

        foreach ($this->_customPlaceholders as $placeholder) {

            preg_match_all("/recipient_(.*)/", $placeholder, $matches);
            if (!empty($matches[1])) {
                $key = array_shift($matches[1]);

                $value = '';
                if ($user instanceof WP_User) {
                    $value = $user->get($key);
                }

                $replacements['[recipient_' . $key . ']'] = $value;
            }
        }

        return $replacements;
    }

    /**
     * @param IfwPsn_Wp_Email $email
     * @return bool
     */
    protected function _emailContainsPlaceholders(IfwPsn_Wp_Email $email)
    {
        if (!isset($this->_emailContainsPlaceholdersStore[$email->getUniqueId()])) {

            // only check once for identical email object
            $subject = $email->getSubject();
            $body = $email->getBody();
            $contains = false;

            foreach ($this->_customPlaceholders as $placeholder) {
                if (
                    strstr($body, '[' . $placeholder . ']') !== false ||
                    strstr($subject, '[' . $placeholder . ']') !== false
                ) {
                    $contains = true;
                    break;
                }
            }

            $this->_emailContainsPlaceholdersStore[$email->getUniqueId()] = $contains;
        }

        return $this->_emailContainsPlaceholdersStore[$email->getUniqueId()] === true;
    }
}
 