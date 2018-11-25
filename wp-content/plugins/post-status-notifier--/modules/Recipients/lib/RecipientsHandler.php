<?php
/**
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2012-2013 ifeelweb.de
 * @version   $Id: RecipientsHandler.php 437 2015-11-04 17:06:34Z timoreithde $
 * @package   
 */ 
class Psn_Module_Recipients_RecipientsHandler 
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var Psn_Module_Premium_Conditions_Handler
     */
    protected $_conditionsHandler;


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
        $this->_conditionsHandler = Psn_Module_Premium_Conditions_Handler::getInstance($this->_pm);

        IfwPsn_Wp_Proxy_Action::add('psn_service_email_recipient_selection', array($this, 'handleServiceEmailRecipientSelection'));
        IfwPsn_Wp_Proxy_Action::add('psn_service_email_cc_selection', array($this, 'handleServiceEmailCcSelection'));
        IfwPsn_Wp_Proxy_Action::add('psn_service_email_bcc_selection', array($this, 'handleServiceEmailBccSelection'));

        IfwPsn_Wp_Proxy_Action::add('psn_before_notification_email_send', array($this, 'beforeNotificationEmailSend'));
        IfwPsn_Wp_Proxy_Action::add('psn_send_test_mail', array($this, 'beforeTestEmailSend'));

        // add placeholders
        IfwPsn_Wp_Proxy_Action::add('psn_notification_placeholders_loaded', array($this, 'extendPlaceholders'));
        IfwPsn_Wp_Proxy_Action::add('psn_notification_placeholders_loaded', array($this, 'extendArrayPlaceholders'));
    }

    /**
     * @param Psn_Notification_Service_Email $email
     * @internal param \Psn_Model_Rule $rule
     * @return string
     */
    public function handleServiceEmailRecipientSelection(Psn_Notification_Service_Email $email)
    {
        $rule = $email->getRule();
        $recipient = $rule->getRecipient();

        if (!is_array($recipient)) {
            $recipient = array($recipient);
        }

        foreach ($this->_getRecipientUserEmails($recipient) as $emailAdress) {
            if (!empty($emailAdress)) {
                // add the email addresses to the service
                $email->addTo($emailAdress);
            }
        }

        $this->_handleDynamicRecipients($email, $rule);
    }

    /**
     * @param Psn_Notification_Service_Email $email
     * @param Psn_Model_Rule $rule
     */
    protected function _handleDynamicRecipients(Psn_Notification_Service_Email $email, Psn_Model_Rule $rule)
    {
        if ($this->_conditionsHandler->isEnableConditionsDynamicRecipients()) {

            // handle dynamic recipients
            $toDyn = $rule->get('to_dyn');

            if (!empty($toDyn)) {
                $replacer = $rule->getReplacer();

                $conditionsParser = IfwPsn_Wp_WunderScript_Parser::getInstance();
                $conditionsParser->setLogger($this->_pm->getLogger());
                $context = $replacer->getTwigContext($toDyn);

                $result = $replacer->replace($toDyn);
                $result = $conditionsParser->parse($result, $context, $this->_conditionsHandler->getConditionsDebugger());

                if (!empty($result)) {
                    // split the result by comma and handle each item as email recipient
                    foreach (explode(',', $result) as $recipient) {
                        $recipient = trim($recipient);
                        if (is_email($recipient)) {
                            $email->addTo($recipient);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Psn_Notification_Service_Email $email
     * @internal param \Psn_Model_Rule $rule
     * @return string
     */
    public function handleServiceEmailCcSelection(Psn_Notification_Service_Email $email)
    {
        $rule = $email->getRule();
        $recipient = $rule->getCcSelect();

        if (!is_array($recipient)) {
            $recipient = array($recipient);
        }

        foreach ($this->_getRecipientUserEmails($recipient) as $emailAdress) {
            if (!empty($emailAdress)) {
                // add the email addresses to the service
                $email->addCc($emailAdress);
            }
        }
    }

    /**
     * @param Psn_Notification_Service_Email $email
     * @internal param \Psn_Model_Rule $rule
     * @return string
     */
    public function handleServiceEmailBccSelection(Psn_Notification_Service_Email $email)
    {
        $rule = $email->getRule();
        $recipient = $rule->getBccSelect();

        if (!is_array($recipient)) {
            $recipient = array($recipient);
        }

        foreach ($this->_getRecipientUserEmails($recipient) as $emailAdress) {
            if (!empty($emailAdress)) {
                // add the email addresses to the service
                $email->addBcc($emailAdress);
            }
        }
    }

    /**
     * @param array $recipient
     * @return array
     */
    protected function _getRecipientUserEmails(array $recipient)
    {
        $users = array();

        // check if all users is selected
        if (in_array('all_users', $recipient)) {
            $users = array_merge($users, IfwPsn_Wp_Proxy_User::getAllUsers());
        }

        // check if roles are selected
        foreach ($recipient as $rec) {
            if (strpos($rec, 'role_') === 0) {
                // role name
                $rolename = substr($rec, 5);
                $users = array_merge($users, IfwPsn_Wp_Proxy_User::getUsersByRoleName($rolename));
            }
        }

        $emails = IfwPsn_Wp_Proxy_User::getEmails($users);

        // check if recipients lists are selected
        foreach ($recipient as $rec) {
            if (strpos($rec, 'list_') === 0) {

                $listId = (int)substr($rec, 5);
                $list = IfwPsn_Wp_ORM_Model::factory('Psn_Module_Recipients_Model_RecipientsLists')->find_one($listId);

                if ($list instanceof Psn_Module_Recipients_Model_RecipientsLists) {
                    $emails = array_merge($emails, array_map('trim', explode(',', $list->get('list'))));
                }
            }
        }

        return array_unique($emails);
    }

    /**
     * @param Psn_Notification_Service_Email $serviceEmail
     */
    public function beforeNotificationEmailSend(Psn_Notification_Service_Email $serviceEmail)
    {
        $ruleFrom = $serviceEmail->getRule()->get('from');
        $optionsFrom = $this->_pm->getOptionsManager()->getOption('psn_default_from');

        if (empty($ruleFrom) && empty($optionsFrom)) {
            // no custom FROM
            return;
        }

        $from = $optionsFrom;

        if (!empty($ruleFrom)) {
            // custom FROM is set on rule
            $from = $ruleFrom;
        }

        $from = IfwPsn_Wp_Email::sanitizeEmail($from);

        // support for placeholders on from
        $from = $serviceEmail->getRule()->getReplacer()->replace($from);

        if (!empty($from)) {
            $serviceEmail->getEmail()->setFrom($from);
        }
    }

    /**
     * Adds default placeholders
     *
     * @param Psn_Notification_Placeholders $placeholders
     */
    public function extendPlaceholders(Psn_Notification_Placeholders $placeholders)
    {
        /**
         * all users
         * register empty [recipient_all_users] for lazy loading
         */
        $placeholders->addPlaceholder('recipient_all_users', '');

        IfwPsn_Wp_Proxy_Filter::add('psn_load_placeholder_value_[recipient_all_users]', array($this, 'lazyLoadPlaceholder'), 10, 3);


        /**
         * roles
         */
        foreach (IfwPsn_Wp_Proxy_Role::getAllNames() as $roleKey => $roleValue) {

            // register for lazy loading
            $rolePlaceholder = 'recipient_role_' . $roleKey;
            $placeholders->addPlaceholder($rolePlaceholder, '');

            IfwPsn_Wp_Proxy_Filter::add('psn_load_placeholder_value_['. $rolePlaceholder .']', array($this, 'lazyLoadPlaceholder'), 10, 3);
        }
    }

    /**
     * Lazy load expensive placeholder contents
     *
     * @param $contents
     * @param $placeholder
     * @param Psn_Notification_Placeholders $placeholders
     * @return string
     */
    public function lazyLoadPlaceholder($contents, $placeholder, Psn_Notification_Placeholders $placeholders)
    {
        if (strstr($placeholder, '[recipient_role_') !== false) {
            // role
            preg_match_all("/\[recipient_role_(.*)_array\]/", $placeholder, $matches);
            if (!empty($matches[1])) {
                // found role array
                $role = array_shift($matches[1]);
                $contents = IfwPsn_Wp_Proxy_User::getRoleMembersEmails($role);
            } else {
                preg_match_all("/\[recipient_role_(.*)\]/", $placeholder, $matches);
                if (!empty($matches[1])) {
                    // found no array role
                    $role = array_shift($matches[1]);
                    $usersEmail = IfwPsn_Wp_Proxy_User::getRoleMembersEmails($role);
                    $contents = implode(', ', $usersEmail);
                }
            }

        } else {

            switch ($placeholder) {
                case '[recipient_all_users]':
                    $allUsers = IfwPsn_Wp_Proxy_User::getAllUsersEmails();
                    $contents = implode(', ', $allUsers);
                    break;
                case '[recipient_all_users_array]':
                    $contents = IfwPsn_Wp_Proxy_User::getAllUsersEmails();
                    break;
            }
        }

        return $contents;
    }

    /**
     * Adds array placeholders
     *
     * @param Psn_Notification_Placeholders $placeholders
     */
    public function extendArrayPlaceholders(Psn_Notification_Placeholders $placeholders)
    {
        /**
         * all users
         * register empty [recipient_all_users_array] for lazy loading
         */
        $placeholders->addPlaceholder('recipient_all_users_array', '', 'arrays');

        IfwPsn_Wp_Proxy_Filter::add('psn_load_placeholder_value_[recipient_all_users_array]', array($this, 'lazyLoadPlaceholder'), 10, 3);

        /**
         * roles
         */
        foreach (IfwPsn_Wp_Proxy_Role::getAllNames() as $roleKey => $roleValue) {

            // register for lazy loading
            $rolePlaceholder = 'recipient_role_' . $roleKey . '_array';
            $placeholders->addPlaceholder($rolePlaceholder, '', 'arrays');

            IfwPsn_Wp_Proxy_Filter::add('psn_load_placeholder_value_['. $rolePlaceholder .']', array($this, 'lazyLoadPlaceholder'), 10, 3);
        }
    }

    /**
     * Lazy loading [recipient_all_users_array]
     * @param $contents
     * @return string
     */
    public function fillPlaceholderRecipientAllUsersArray($contents)
    {
        return IfwPsn_Wp_Proxy_User::getAllUsersEmails();
    }

    /**
     * @param IfwPsn_Wp_Email $email
     */
    public function beforeTestEmailSend(IfwPsn_Wp_Email $email)
    {
        $defaultFrom = $this->_pm->getOptionsManager()->getOption('psn_default_from');

        if (!empty($defaultFrom)) {
            $email->setFrom($defaultFrom);
        }
    }
}
 