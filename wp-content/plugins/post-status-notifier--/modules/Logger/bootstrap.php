<?php
/**
 * Premium logger module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 394 2015-06-21 21:40:04Z timoreithde $
 */
class Psn_Logger_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * custom logger name
     */
    const LOG_NAME = 'ModLog';

    const LOG_TYPE_INFO = 2101;
    const LOG_TYPE_SENT_MAIL = 2102;
    const LOG_TYPE_SUCCESS = 2103;
    const LOG_TYPE_FAILURE = 2104;

    /**
     * @var Psn_Module_Logger_LogHandler
     */
    protected $_logHandler;

    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_log';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'Logger';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Logs rule matches and sent mails';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_log';

    /**
     * The module version
     * @var string
     */
    protected $_version = '1.1';

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
     * @return array
     */
    protected function _registerAdminAjaxRequests()
    {
        $metaboxLogs = new Psn_Module_Logger_Metabox_Logs($this->_pm);
        $metaboxLogs->getAjaxRequest()->register();

        $ajaxRequestLogDetails = new Psn_Module_Logger_ListTable_Ajax_Details();
        $ajaxRequestLogDetails->register();

        return array();
    }

    /**
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        if (!$this->_pm->getAccess()->isHeartbeat()) {

            require_once $this->getPathinfo()->getRootLib() . 'LogHandler.php';
            $this->_logHandler = new Psn_Module_Logger_LogHandler($this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME));
            $this->_logHandler->load();

            if ($this->_pm->getAccess()->isPlugin()) {
                $this->addOptions();
                $this->addPluginAdminActions();
                IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'selftester_activate', array($this, 'addSelftests'));
            }

            if ($this->_pm->getAccess()->isAdmin()) {

                require_once $this->getPathinfo()->getRootLib() . 'Installer/Activation.php';
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Uninstall.php';

                $this->_pm->getInstaller()->addActivation(new Psn_Module_Logger_Installer_Activation());
                // uninstall log table on uninstall
                $this->_pm->getInstaller()->addUninstall(new Psn_Module_Logger_Installer_Uninstall());
            }

            if ($this->_pm->getAccess()->isDashboard()) {
                // load dashboard widget
                IfwPsn_Wp_Proxy_Action::addAdminInit(array($this, 'loadDashboardWidget'));
            }

            // since some themes support frontend editing we need to init the logger always
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Logger.php';
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Log/Writer/WpDb.php';
            $logger = IfwPsn_Wp_Plugin_Logger::create(
                $this->_pm,
                new IfwPsn_Zend_Log_Writer_WpDb($this->_pm, 'Psn_Module_Logger_Model_Log'),
                self::LOG_NAME
            );

            $this->addGlobalActions();
        }
    }

    /**
     * Adds actions used in global scope
     */
    public function addGlobalActions()
    {
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_load_services', array($this, 'addLoggerService'));
    }

    /**
     * Adds actions used in plugin admin scope
     */
    public function addPluginAdminActions()
    {
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'admin_overview_after_metabox_left', array($this, 'addMetabox'));
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_admin_navigation_rules', array($this, 'addNav'));
        //IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'form_rule_after_service_email', array($this, 'addFormFieldLog'));
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'rule_form', array($this, 'extendForm'));

        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'send_test_mail_success', array($this->_logHandler, 'onSendTestMailSuccess'));
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'send_test_mail_failure', array($this->_logHandler, 'onSendTestMailFailure'));

        IfwPsn_Wp_Proxy_Action::add('psn_patch_db', array($this, 'patchDb'));
    }

    /**
     * Loads the dashboard widget
     */
    public function loadDashboardWidget()
    {
        $optionWidgetAdminOnly = $this->_pm->getOptionsManager()->getOption('psn_log_widget_admin_only');
        $optionWidgetDisable = $this->_pm->getOptionsManager()->getOption('psn_log_widget_disable');

        if ($optionWidgetDisable == null &&
            (($optionWidgetAdminOnly !== null && IfwPsn_Wp_User::isAdmin()) || $optionWidgetAdminOnly == null)) {

            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Widget/Dashboard.php';
            require_once $this->getPathinfo()->getRootLib() . 'Widget/Dashboard.php';

            $widget = new Psn_Module_Logger_Widget_Dashboard($this->_pm, 'psn_logger_dashboard');
            $widget->setModule($this);
            $widget->setTitle($this->_pm->getEnv()->getName() . ' Log');
        }
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Test/LogModel.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/LogModelExtraType.php';

        $selftester->addTestCase(new Psn_Module_Logger_Test_LogModel());
        $selftester->addTestCase(new Psn_Module_Logger_Test_LogModelExtraType());
    }

    /**
     * @param Psn_Notification_Manager $notificationManager
     */
    public function addLoggerService(Psn_Notification_Manager $notificationManager)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Service/Logger.php';
        $notificationManager->addService(new Psn_Module_Logger_Service_Logger($this->_pm));
    }

    /**
     * @param IfwPsn_Wp_Plugin_Metabox_Container $container
     */
    public function addMetabox(IfwPsn_Wp_Plugin_Metabox_Container $container)
    {
        $container->addMetabox(new Psn_Module_Logger_Metabox_Logs($this->_pm));
    }

    /**
     * @param IfwPsn_Zend_Form $form
     */
    public function extendForm(IfwPsn_Zend_Form $form)
    {
        $fieldDecorators = array(
            new IfwPsn_Zend_Form_Decorator_SimpleInput(),
            array('HtmlTag', array('tag' => 'li')),
            'Errors',
            'Description'
        );

        $log = $form->createElement('checkbox', 'service_log');
        $log->setLabel(__('Log', 'psn'))
            ->setDecorators($fieldDecorators)
            ->setDescription(__('A log entry will be written using subject as log title', 'psn_log'))
            ->setChecked(true)
            ->setCheckedValue(1)
            ->setOrder(111)
        ;
        $form->addElement($log);
    }

    /**
     * @param $navigation
     */
    public function addNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Log', 'psn_log'),
            'controller' => 'log',
            'action' => 'index',
            'module' => strtolower($this->_pathinfo->getDirname()),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doAction('psn_after_admin_navigation_log', $navigation);
    }

    public function addOptions()
    {
        $loggerOptions = new IfwPsn_Wp_Options_Section('logger', __('Logger', 'psn_log'),
            Psn_Admin_Options_Handler::getOptionsDescriptionBox(
                '<span class="dashicons dashicons-book"></span> ' .
                sprintf(__('Learn more about the "Logger" feature in the <a %s>online documentation</a>.', 'psn_log'), 'href="'. $this->_pm->getConfig()->plugin->docUrl . 'log.html" target="_blank"')) .

            sprintf('<img src="%s" class="options_teaser">',
                $this->getEnv()->getUrlImg() . 'logger_options.png'
            )
        );

        $loggerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_log_rule_matches',
            __('Log rule matches', 'psn_log'),
            __('Create informational log entry when a rule matches a status transition', 'psn_log')
            )
        );
        $loggerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_log_array_details',
            __('Array details', 'psn_log'),
            __('Show array contents in log entries instead of just "Array". Use only when you need it as this can litter up your log table e.g. in case of placeholder [recipient_all_users].', 'psn_log')
            )
        );

        $loggerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_log_widget_disable',
            __('Disable widget', 'psn_log'),
            __('Disable the log widget on the dashboard', 'psn_log')
            )
        );

        $loggerOptions->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'psn_log_widget_admin_only',
            __('Widget for admins only', 'psn_log'),
            __('Show the dashboard widget for admins only', 'psn_log')
            )
        );

        $this->_pm->getBootstrap()->getOptions()->addSection($loggerOptions, 100);
    }

    /**
     * Patch the database
     */
    public function patchDb()
    {
        $dbTest = new Psn_Module_Logger_Test_LogModelExtraType();
        $dbTest->execute($this->_pm);

        if ($dbTest->getResult() === false) {
            $dbTest->handleError($this->_pm);
        }
    }
}
