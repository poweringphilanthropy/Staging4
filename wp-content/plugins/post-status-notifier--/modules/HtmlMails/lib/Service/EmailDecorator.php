<?php
/**
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: EmailDecorator.php 418 2015-09-18 10:25:48Z timoreithde $
 */
class Psn_Module_HtmlMails_Service_EmailDecorator 
{
    /**
     * @var Psn_Notification_Service_Email
     */
    protected $_email;

    /**
     * @var Psn_Model_Rule
     */
    protected $_rule;

    /**
     * @var Psn_Module_HtmlMails_Model_MailTemplates
     */
    protected $_mailTpl;

    /**
     * @var Psn_Notification_Placeholders
     */
    protected $_replacer;

    /**
     * @var null|string
     */
    protected $_altBody;

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
     * @internal param null $logger
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;

        $this->_init();
    }

    /**
     * Init options and actions/filters
     */
    protected function _init()
    {
        $this->_conditionsHandler = Psn_Module_Premium_Conditions_Handler::getInstance($this->_pm);

        IfwPsn_Wp_Proxy_Action::add('psn_before_notification_email_send', array($this, 'beforeNotificationEmailSend'));

        IfwPsn_Wp_Proxy_Filter::add('psn_rule_notification_body', array($this, 'filterBody'), 10, 2);
        IfwPsn_Wp_Proxy_Filter::add('psn_rule_notification_subject', array($this, 'filterSubject'), 10, 2);

        IfwPsn_Wp_Proxy_Filter::add('psn_send_test_mail_body', array($this, 'filterTestMailBody'), 10, 2);
    }

    /**
     * @param Psn_Notification_Service_Email $serviceEmail
     */
    public function beforeNotificationEmailSend(Psn_Notification_Service_Email $serviceEmail)
    {
        /**
         * @var Psn_Model_Rule $rule
         */
        $rule = $serviceEmail->getRule();

        if ($rule->hasData('html')) {
            $serviceEmail->getEmail()->setHTML(true);

            if ($rule->hasData('altbody')) {
                $serviceEmail->getEmail()->setAltbody($rule->getData('altbody'));
            }
        }
    }

    /**
     * Check if a mail template is used
     *
     * @param $body
     * @param Psn_Model_Rule $rule
     * @internal param \Psn_Notification_Service_Email $serviceEmail
     * @return null|string
     */
    public function filterBody($body, Psn_Model_Rule $rule)
    {
        // check for mail template
        $mailTplId = $rule->get('mail_tpl');

        if (!empty($mailTplId)) {
            // mail template found
            $mailTpl = IfwPsn_Wp_ORM_Model::factory('Psn_Module_HtmlMails_Model_MailTemplates')->find_one((int)$mailTplId);

            if ($mailTpl instanceof Psn_Module_HtmlMails_Model_MailTemplates) {

                $body = $rule->getReplacer()->replace($mailTpl->get('body'));

                if ((int)$mailTpl->get('type') == Psn_Module_HtmlMails_Model_MailTemplates::TYPE_HTML) {

                    // is HTML
                    $rule->setData('html', true);

                    $rule->setData('altbody', $rule->getReplacer()->replace($mailTpl->get('altbody')));
                    $body = html_entity_decode($body);
                }
            }
        }

        // prepare conditions support
        if ($this->_conditionsHandler->isEnableConditionsBody()) {

            $replacer = $rule->getReplacer();

            $conditionsParser = IfwPsn_Wp_WunderScript_Parser::getInstance();
            $conditionsParser->setLogger($this->_pm->getLogger());
            $context = $replacer->getTwigContext($body);

            $body = $conditionsParser->parse($body, $context, $this->_conditionsHandler->getConditionsDebugger());

            // handle altbody
            if ($rule->hasData('altbody')) {
                $altbody = $rule->getData('altbody');
                $altbody = $conditionsParser->parse($altbody, $context, $this->_conditionsHandler->getConditionsDebugger());
                $rule->setData('altbody', $altbody);
            }
        }

        return $body;
    }

    /**
     * Apply filters to the subject
     *
     * @param $subject
     * @param Psn_Model_Rule $rule
     * @return null|string
     */
    public function filterSubject($subject, Psn_Model_Rule $rule)
    {
        // prepare conditions support
        if ($this->_conditionsHandler->isEnableConditionsSubject()) {

            $replacer = $rule->getReplacer();

            $conditionsParser = IfwPsn_Wp_WunderScript_Parser::getInstance();
            $conditionsParser->setLogger($this->_pm->getLogger());
            $context = $replacer->getTwigContext($subject);

            $subject = $conditionsParser->parse($subject, $context, $this->_conditionsHandler->getConditionsDebugger());
        }

        return $subject;
    }

    /**
     * @param $body
     * @param IfwPsn_Wp_Email $email
     * @return string
     */
    public function filterTestMailBody($body, IfwPsn_Wp_Email $email)
    {
        $mailTplId = (int)trim($_POST['mail_tpl']);

        if (!empty($mailTplId)) {

            $mailTpl = IfwPsn_Wp_ORM_Model::factory('Psn_Module_HtmlMails_Model_MailTemplates')->find_one($mailTplId);

            if ($mailTpl instanceof Psn_Module_HtmlMails_Model_MailTemplates) {

                $body = $mailTpl->get('body');

                if ((int)$mailTpl->get('type') == Psn_Module_HtmlMails_Model_MailTemplates::TYPE_HTML) {
                    // is HTML
                    $email->setHTML(true);
                    $email->setAltbody($mailTpl->get('altbody'));
                    $body = html_entity_decode($body);
                }
            }
        }

        return $body;
    }

    /**
     * @return bool
     */
    public function isSupportConditions()
    {
        return $this->_conditionsHandler->isEnableConditionsBody() || $this->_conditionsHandler->isEnableConditionsSubject();
    }

}
 