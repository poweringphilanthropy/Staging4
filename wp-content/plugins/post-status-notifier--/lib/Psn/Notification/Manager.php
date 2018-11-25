<?php
/**
 * Notification manager
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @copyright   Copyright (c) ifeelweb.de
 * @version     $Id: Manager.php 426 2015-09-30 12:39:15Z timoreithde $
 * @package     Psn_Notification
 */ 
class Psn_Notification_Manager
{
    /**
     *
     */
    const POST_CUSTOM_FIELD_KEY_BLOCK_NOTIFICATIONS = 'psn-block-notifications';

    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var array
     */
    protected $_services = array();

    /**
     * @var string
     */
    protected $_statusBefore;

    /**
     * @var string
     */
    protected $_statusAfter;

    /**
     * @var array
     */
    protected $_replacerInstances = array();

    /**
     * @var bool
     */
    protected $_deferredExecution = false;

    /**
     * @var Psn_Notification_Postponed
     */
    protected $_postponedHandler;



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
        IfwPsn_Wp_Proxy_Filter::add('transition_post_status', array($this, 'handlePostStatusTransition'), 10, 3);
        // custom trigger. can be used to manually trigger the notification services on a post.
        IfwPsn_Wp_Proxy_Filter::add('psn_trigger_notification_manager', array($this, 'handlePostStatusTransition'), 10, 3);

        IfwPsn_Wp_Proxy_Filter::add('psn_service_email_body', array($this, 'filterEmailBody'), 10, 3);
        IfwPsn_Wp_Proxy_Filter::add('psn_service_email_subject', array($this, 'filterEmailSubject'), 10, 3);

        // add replacer filters

        IfwPsn_Wp_Proxy_Filter::add('psn_notification_placeholders', array($this, 'filterPlaceholders'));
        IfwPsn_Wp_Proxy_Filter::add('psn_notification_dynamic_placeholders', array($this, 'filterPlaceholders'));

        $this->_loadServices();

        /**
         * At last init postponed handler for frontend submissions
         */
        $this->_postponedHandler = new Psn_Notification_Postponed(clone $this);
    }

    /**
     * load default services
     */
    protected function _loadServices()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Notification/Service/Email.php';

        $this->addService(new Psn_Notification_Service_Email());
        IfwPsn_Wp_Proxy_Action::doAction('psn_after_load_services', $this);
    }

    /**
     * @param $statusAfter
     * @param $statusBefore
     * @param $post
     */
    public function handlePostStatusTransition($statusAfter, $statusBefore, $post)
    {
        /**
         * Block
         * check for block setting
         */
        if ($this->isBlockNotifications($post->ID)) {
            // skip if option to block notifications is set
            return;
        }

        /**
         * Postponed
         * check if post should be postponed
         */
        if ($this->_postponedHandler instanceof Psn_Notification_Postponed && $this->_postponedHandler->applies()) {
            $this->_postponedHandler->add($statusAfter, $statusBefore, $post);
            return;
        }



        $this->_pm->getErrorHandler()->enableErrorReporting();

        $this->_statusBefore = $statusBefore;
        $this->_statusAfter = $statusAfter;

        IfwPsn_Wp_Proxy_Filter::add('psn_notification_placeholders', array($this, 'addPlaceholders'));

        // get all active rules
        $activeRules = IfwPsn_Wp_ORM_Model::factory('Psn_Model_Rule')->filter('active')->find_many();
        
        if (Psn_Model_Rule::hasMax()) {
            $activeRules = array_slice($activeRules, 0, Psn_Model_Rule::getMax());
        }

        if ($this->isDeferredExecution()) {
            $deferredHandler = new Psn_Notification_Deferred_Handler();
        }

        /**
         * @var $rule Psn_Model_Rule
         */
        foreach($activeRules as $rule) {

            if ($this->_pm->hasOption('psn_ignore_status_inherit')) {
                $rule->setIgnoreInherit(true);
            }

            // to skip a rulematch return false
            $doMatch = IfwPsn_Wp_Proxy_Filter::apply('psn_do_match', true, array(
                'rule' => $rule,
                'post' => $post,
                'status_before' => $statusBefore,
                'status_after' => $statusAfter
            ));
            if (!is_bool($doMatch)) {
                // for safety:
                $doMatch = true;
            }

            if ($doMatch && $rule->matches($post, $statusBefore, $statusAfter)) {

                // rule matches

                /**
                 * Execute all registered notification services
                 *
                 * @var $service Psn_Notification_Service_Interface
                 */
                foreach($this->getServices() as $service) {
                    if ($this->isDeferredExecution()) {

                        // prepare for deferred execution
                        $deferredContainer = new Psn_Notification_Deferred_Container();
                        $deferredContainer->setService($service)->setRule($rule)->setPost($post);
                        $deferredHandler->addCotainer($deferredContainer);

                    } else {
                        // execute directly

                        // set the replacer
                        $rule->setReplacer(Psn_Notification_Placeholders::getInstance($post));

                        $service->execute($rule, $post);
                    }
                }
            }
        }

        $this->_pm->getErrorHandler()->disableErrorReporting();
    }

    /**
     * @param $placeholders
     * @return array
     */
    public function addPlaceholders(array $placeholders)
    {
        return array_merge($placeholders, array(
            'post_status_before' => $this->_statusBefore,
            'post_status_after' => $this->_statusAfter,
        ));
    }

    /**
     * @param $placeholders
     * @return array
     */
    public function filterPlaceholders(array $placeholders)
    {
        $filters = $this->_pm->getBootstrap()->getOptionsManager()->getOption('placeholders_filters');

        if (!empty($filters)) {

            $counter = 0;
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $filters) as $filter) {
                if (!$this->_pm->isPremium() && $counter >= 1) {
                    break;
                }

                preg_match_all('/\[([A-Za-z0-9_-]+?)\]/', $filter, $match);

                if (isset($match[0][0]) && isset($match[1][0])) {
                    $placeholder_tag = $match[0][0];
                    $placeholder_name = $match[1][0];

                    if (isset($placeholders[$placeholder_name])) {
                        $filter_string = str_replace($placeholder_tag, '"'. $placeholders[$placeholder_name] . '"', $filter);
                        if (!empty($filter_string)) {
                            if ($filter_string[0] != '{') {
                                $filter_string = '{{ '. $filter_string . ' }}';
                            }

                            $placeholders[$placeholder_name] = IfwPsn_Wp_Tpl::renderString($filter_string);
                        }
                    }
                }
                $counter++;
            }

        }
        
        return $placeholders;
    }

    /**
     * @param $subject
     * @param Psn_Notification_Service_Email $email
     * @return string
     */
    public function filterEmailSubject($subject, Psn_Notification_Service_Email $email)
    {
        $subject = $this->_handleSpecialChars($subject);

        return $subject;
    }

    /**
     * @param $body
     * @param Psn_Notification_Service_Email $email
     * @return string
     */
    public function filterEmailBody($body, Psn_Notification_Service_Email $email)
    {
        $body = $this->_handleSpecialChars($body);

        if (!$this->_pm->isPremium()) {
            // please respect my work and buy the premium version if you want this plugin to stay alive!
            $body .= PHP_EOL . PHP_EOL .
                sprintf(__('This email was sent by WordPress plugin "%s". Visit the plugin homepage: %s'),
                $this->_pm->getEnv()->getName(),
                $this->_pm->getEnv()->getHomepage()
                );
        }
        return $body;
    }

    /**
     * @param $string
     * @return string
     */
    protected function _handleSpecialChars($string)
    {
        return strtr($string, array(
            '&#039;' => '\'',
        ));
    }

    /**
     * @param Psn_Notification_Service_Interface $service
     */
    public function addService(Psn_Notification_Service_Interface $service)
    {
        array_push($this->_services, $service);
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->_services;
    }

    /**
     * @param null $postId
     * @return bool
     */
    public function isBlockNotifications($postId = null)
    {
        if ($postId == null) {
            global $post;
            $postId = $post->ID;
        } else {
            $postId = intval($postId);
        }

        if (!empty($postId)) {
            $result = get_post_meta($postId, self::POST_CUSTOM_FIELD_KEY_BLOCK_NOTIFICATIONS, true);
            return $result === '1';
        }

        return false;
    }

    /**
     * @param boolean $deferredExecution
     */
    public function setDeferredExecution($deferredExecution = true)
    {
        if (is_bool($deferredExecution)) {
            $this->_deferredExecution = $deferredExecution;
        }
    }

    /**
     * @return boolean
     */
    public function isDeferredExecution()
    {
        return $this->_deferredExecution === true;
    }

}
