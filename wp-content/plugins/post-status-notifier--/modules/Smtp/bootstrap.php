<?php
/**
 * Premium Smtp module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 440 2015-11-18 18:32:29Z timoreithde $
 */
class Psn_Smtp_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_smtp';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'SMTP';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Makes it possible to send the notification emails via a SMTP server';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_smtp';

    /**
     * The module version
     * @var string
     */
    protected $_version = '1.0.1';

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
    protected $_dependencies = array('psn_mod_log');



    /**
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        if ($this->_pm->getAccess()->isPlugin()) {
            $this->addOptions();
        }

        $this->addActions();
    }

    /**
     * Add the SMTP actions
     */
    public function addActions()
    {
        if ($this->_pm->getBootstrap()->getOptionsManager()->getOption('smtp_activate')) {
            IfwPsn_Wp_Proxy_Action::add('phpmailer_init', array($this, 'configurePhpMailer'));

            IfwPsn_Wp_Proxy_Action::add('ifwpsn_callback_before_email_send', array($this, 'onBeforeEmailSend'), 10, 2);
            IfwPsn_Wp_Proxy_Action::add('ifwpsn_callback_after_email_send', array($this, 'onAfterEmailSend'), 10, 3);
        }
    }

    /**
     * Configures the PHPMailer object to use SMTP
     * @param PHPMailer $phpmailer
     */
    public function configurePhpMailer(PHPMailer $phpmailer)
    {
        // activate SMTP:
        $phpmailer->IsSMTP();

        $options = $this->_pm->getBootstrap()->getOptionsManager();

        $host = $options->getOption('smtp_host');
        $port = $options->getOption('smtp_port');
        $secure = $options->getOption('smtp_secure');
        $auth = $options->getOption('smtp_auth');
        $username = $options->getOption('smtp_username');
        $password = $options->getOption('smtp_password');
        $timeout = $options->getOption('smtp_timeout');
        $helo = $options->getOption('smtp_helo');
        $debug = $options->getOption('smtp_debug');

        if (!empty($host)) {
            $phpmailer->Host = $host;
        }
        if (!empty($port)) {
            $phpmailer->Port = $port;
        }
        if (!empty($secure) && in_array($secure, array("ssl", "tls"))) {
            $phpmailer->SMTPSecure = $secure;
        }
        if ($auth) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
        }
        if (!empty($timeout)) {
            $phpmailer->Timeout = (int)$timeout;
        }
        if (!empty($helo)) {
            $phpmailer->Helo = $helo;
        }
        if ($debug) {
            $phpmailer->SMTPDebug = true;
        }
    }

    /**
     * @param array $emailParams
     * @param IfwPsn_Wp_Email $email
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     */
    public function onBeforeEmailSend(array $emailParams, IfwPsn_Wp_Email $email)
    {
        if ($this->_pm->getBootstrap()->getOptionsManager()->getOption('smtp_debug') && !$this->_pm->getAccess()->isAjax()) {
            // start output buffering for smtp debug inforamtion
            ob_start();
        }
    }

    /**
     * @param $result
     * @param array $emailParams
     * @param IfwPsn_Wp_Email $email
     * @throws IfwPsn_Wp_Plugin_Bootstrap_Exception
     */
    public function onAfterEmailSend($result, array $emailParams, IfwPsn_Wp_Email $email)
    {
        if ($this->_pm->getBootstrap()->getOptionsManager()->getOption('smtp_debug') && !$this->_pm->getAccess()->isAjax()) {
            // end output buffering and store stmp debug info in log entry
            $smtpDebug = ob_get_flush();

            if ($smtpDebug != false) {
                $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->info(__('SMTP Debug information', 'psn_smtp'), array(
                    'type' => Psn_Logger_Bootstrap::LOG_TYPE_INFO,
                    'extra' => $smtpDebug
                ));
            }
        }
    }

    /**
     * Add the SMTP options
     */
    public function addOptions()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Options/Section.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Options/Field/Checkbox.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Options/Field/Text.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Options/Field/Password.php';

        $smtpOptions = new IfwPsn_Wp_Options_Section('smtp', __('SMTP', 'psn_smtp'));

        $smtpOptions
            ->addField(new IfwPsn_Wp_Options_Field_Checkbox(
            'smtp_activate',
            __('Activate SMTP', 'psn_smtp'),
            __('When activated the plugins tries to send the notification emails via an SMTP server using the following connection settings', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Text(
                'smtp_host',
                __('SMTP host', 'psn_smtp'),
                __('Sets the SMTP hosts. If left blank, default is "localhost". All hosts must be separated by a semicolon. You can also specify a different port for each host by using this format: [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com"). Hosts will be tried in order.', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Text(
                'smtp_port',
                __('SMTP port', 'psn_smtp'),
                __('Sets the SMTP port. If left blank, default is 25.', 'psn_smtp'), array(
                    'sanitizer' => 'number'
                )
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Text(
                'smtp_secure',
                __('Secure', 'psn_smtp'),
                __('Sets connection prefix. Options are "", "ssl" or "tls". Default is ""', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'smtp_auth',
                __('Authentication', 'psn_smtp'),
                __('Sets SMTP authentication. Utilizes the Username and Password variables.', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Text(
                'smtp_username',
                __('Username', 'psn_smtp'),
                __('SMTP username', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Password(
                'smtp_password',
                __('Password', 'psn_smtp'),
                __('SMTP password', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Text(
                'smtp_timeout',
                __('Timeout', 'psn_smtp'),
                __('Sets the SMTP server timeout in seconds. (Default is 10).', 'psn_smtp'), array(
                    'sanitizer' => 'number'
                )
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Text(
                'smtp_helo',
                __('HELO', 'psn_smtp'),
                __('Sets the SMTP HELO of the message (Default is "").', 'psn_smtp')
            ))
            ->addField(new IfwPsn_Wp_Options_Field_Checkbox(
                'smtp_debug',
                __('SMTP Debug', 'psn_smtp'),
                __('Stores SMTP debug information in a log entry.', 'psn_smtp')
            ))
        ;

        $this->_pm->getBootstrap()->getOptions()->addSection($smtpOptions, 200);
    }
}
