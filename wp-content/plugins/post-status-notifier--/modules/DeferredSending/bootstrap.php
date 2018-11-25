<?php
/**
 * Premium DeferredSending module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 405 2015-08-24 22:17:41Z timoreithde $
 */
class Psn_DeferredSending_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_def';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'DeferredSending';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Handles the deferred sending feature';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_def';

    /**
     * The module version
     * @var string
     */
    protected $_version = '1.0';

    /**
     * The module author
     * @var string
     */
    protected $_author = 'Timo';

    /**
     * The author's homepage
     * @var string
     */
    protected $_authorHomepage = 'http://www.ifeelweb.de/';

    /**
     * The module homepage
     * @var string
     */
    protected $_homepage = 'http://www.ifeelweb.de/wp-plugins/post-status-notifier/';

    /**
     * The module dependencies
     * @var array
     */
    protected $_dependencies = array('psn_mod_prm');



    /**
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        if (!$this->_pm->getAccess()->isHeartbeat()) {

            if ($this->_pm->getAccess()->isPlugin()) {

                IfwPsn_Wp_Proxy_Action::addPluginsLoaded(array($this, 'addOptions'));

                $this->addPluginAdminActions();
            }

            if ($this->_pm->getAccess()->isAdmin()) {
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Activation.php';
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Uninstall.php';

                $this->_pm->getBootstrap()->getInstaller()->addActivation(new Psn_Module_DeferredSending_Installer_Activation());
                $this->_pm->getBootstrap()->getInstaller()->addUninstall(new Psn_Module_DeferredSending_Installer_Uninstall());
            }
        }

        IfwPsn_Wp_Proxy_Action::addPluginsLoaded(array($this, 'loadMailqueue'));
    }

    public function loadMailqueue()
    {
        if ($this->_pm->hasOption('psn_deferred_sending')) {

            // init the mailqueue handler
            require_once $this->getPathinfo()->getRootLib() . 'Mailqueue/Handler.php';
            $mailqueueHandler = Psn_Module_DeferredSending_Mailqueue_Handler::getInstance();
            $mailqueueHandler->load();

            if ($this->_pm->hasOption('psn_deferred_sending_max_amount')) {
                $mailqueueHandler->setMaxAmount((int)$this->_pm->getOption('psn_deferred_sending_max_amount'));
            }
            if ($this->_pm->hasOption('psn_deferred_sending_max_tries')) {
                $mailqueueHandler->setMaxTries((int)$this->_pm->getOption('psn_deferred_sending_max_tries'));
            }
            if ($this->_pm->hasOption('psn_deferred_sending_recurrence')) {
                $mailqueueHandler->setRecurrence($this->_pm->getOption('psn_deferred_sending_recurrence'));
            }
            if ($this->_pm->hasOption('psn_deferred_sending_log_sent')) {
                $mailqueueHandler->setLogSent(true);
            }
            if ($this->_pm->hasOption('psn_deferred_sending_run_after_add')) {
                $mailqueueHandler->setRunAfterAdd(true);
            }

        }
    }

    /**
     * @return array
     */
    protected function _registerAdminAjaxRequests()
    {
        $ajaxRequestDetails = new Psn_Module_DeferredSending_ListTable_Ajax_Details();
        $ajaxRequestDetails->register();
        $ajaxRequestDetailsLog = new Psn_Module_DeferredSending_ListTable_Ajax_DetailsLog();
        $ajaxRequestDetailsLog->register();
    }

    public function addOptions()
    {
        require_once $this->getPathinfo()->getRootLib() . 'Mailqueue/Handler.php';

        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Section.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Checkbox.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Text.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Options/Field/Select.php';

        /**
         * Deferred sending options
         */
        $deferredSendingOptions = new IfwPsn_Wp_Options_Section('deferred', __('Mail Queue', 'psn_def'),
            Psn_Admin_Options_Handler::getOptionsDescriptionBox(
                '<span class="dashicons dashicons-book"></span> ' .
                sprintf(__('Learn more about the "Mail Queue" in the <a %s>online documentation</a>.', 'psn_def'), 'href="'. $this->_pm->getConfig()->plugin->docUrl . 'mailqueue.html" target="_blank"')) .
            sprintf(
                __('If you are facing page loading issues on post updates due to large amounts of emails sent in realtime, use the deferred sending feature.', 'psn_def') . ' <img src="%s" class="options_teaser">',
                $this->getEnv()->getUrlImg() . 'deferred_options.jpg'
            )
        );
        $deferredSendingOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_deferred_sending',
            __('Activate', 'psn'),
            __('Activates the deferred sending functionallity (new section "Mail queue" will show up)', 'psn_def')
        ));
        $deferredSendingOptions->addField(new IfwPsn_Wp_Options_Field_Text(
            'psn_deferred_sending_max_amount',
            __('Max amount', 'psn_def'),
            sprintf(
                __('Determines how many emails should be processed on each mail queue run (Integer, default: 10).<br>Read the <a href="%s" target="_blank">manual page</a> for an example.', 'psn_def'),
                'http://docs.ifeelweb.de/post-status-notifier/mailqueue.html#max-amount'
                ),
            array(
                'sanitizer' => 'number'
            )
        ));

        if (!$this->_pm->hasOption('psn_deferred_sending_recurrence')) {
            $defaultRecurrence = Psn_Module_DeferredSending_Mailqueue_Handler::DEFAULT_RECURRENCE;
        } else {
            $defaultRecurrence = $this->_pm->getOption('psn_deferred_sending_recurrence');
        }

        $recurrenceOptions = array();
        foreach (wp_get_schedules() as $k => $v) {
            $recurrenceOptions[$k] = $v['display'];
        }
        $recurrenceOptions[Psn_Module_DeferredSending_Mailqueue_Handler::RECURRENCE_MANUALLY] = __('Manually', 'psn_def');

        $deferredSendingOptions->addField(new IfwPsn_Wp_Options_Field_Select(
            'psn_deferred_sending_recurrence',
            __('Recurrence', 'psn_def'),
            sprintf(__('How often the mail queue should be run. Uses WordPress\'s built-in cron API (default: Once Hourly). To create custom intervals (like every 5 minutes) please use a Cronjob plugin like <a href="%s" target="_blank">WP Crontrol</a> (read the <a href="%s" target="_blank">manual page</a>).<br>Select "%s" if you just want to run the mail queue manually by hitting the button.', 'psn_def'),
                'https://wordpress.org/plugins/wp-crontrol/',
                'http://docs.ifeelweb.de/post-status-notifier/mailqueue.html#creating-custom-recurrence-intervals',
                __('Manually', 'psn_def')
                ),
            array(
                'options' => $recurrenceOptions,
                'optionsDefault' => $defaultRecurrence
            )
        ));

        $deferredSendingOptions->addField(new IfwPsn_Wp_Options_Field_Text(
            'psn_deferred_sending_max_tries',
            __('Max tries', 'psn_def'),
            __('Determines how often the mail queue should try to send an email in case of an error (Integer, default: 10).', 'psn_def'),
            array(
                'maxlength' => 2,
                'sanitizer' => 'number'
            )
        ));

        $deferredSendingOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_deferred_sending_log_sent',
            __('Log sent emails', 'psn'),
            __('Successfully sent emails get deleted from the mail queue to keep the database table lean. You may activate this option to store successfully sent emails in another table to obtain insight in outgoing emails.', 'psn_def')
        ));

        $deferredSendingOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_deferred_sending_run_after_add',
            __('Run after add', 'psn'),
            __('If you want the mail queue to be run once immediately after emails got added, select this option. This will send the first bunch of the configured max amount directly without having to wait for the next scheduled cron run.', 'psn_def')
        ));

        $this->_pm->getBootstrap()->getOptions()->addSection($deferredSendingOptions, 35);
    }

    public function addPluginAdminActions()
    {
        if ($this->_pm->hasOption('psn_deferred_sending')) {
            IfwPsn_Wp_Proxy_Action::add('psn_after_admin_navigation_log', array($this, 'addNav'));
            IfwPsn_Wp_Proxy_Action::add('psn_selftester_activate', array($this, 'addSelftests'));
        }

        IfwPsn_Wp_Proxy_Action::add('psn_selftester_activate', array($this, 'addSelftests'));
        IfwPsn_Wp_Proxy_Action::add('psn_patch_db', array($this, 'patchDb'));
    }

    /**
     * @param $navigation
     */
    public function addNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Navigation/Page/WpMvc.php';

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Mail Queue', 'psn_def'),
            'controller' => 'deferredsending',
            'action' => 'index',
            'module' => strtolower($this->_pathinfo->getDirname()),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doAction('psn_after_admin_navigation_deferredsending', $navigation);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Test/MailQueueModel.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/MailQueueLogModel.php';

        $selftester->addTestCase(new Psn_Module_DeferredSending_Test_MailQueueModel());
        $selftester->addTestCase(new Psn_Module_DeferredSending_Test_MailQueueLogModel());
    }

    /**
     * Creates the mail templates table if not exists
     */
    public function patchDb()
    {
        require_once $this->getPathinfo()->getRootLib() . 'Installer/Activation.php';

        $activation = new Psn_Module_DeferredSending_Installer_Activation();
        $activation->execute($this->_pm);
    }
}
