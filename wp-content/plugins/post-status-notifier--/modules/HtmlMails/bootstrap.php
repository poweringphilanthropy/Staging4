<?php
/**
 * Premium HtmlMails module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 356 2014-12-14 17:06:32Z timoreithde $
 */
class Psn_HtmlMails_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_htm';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'HtmlMails';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Responsible for the HTML mail feature';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_htm';

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
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        if (!$this->_pm->getAccess()->isHeartbeat()) {

            if ($this->_pm->getAccess()->isPlugin()) {
                $this->addPluginAdminActions();
            }

            if ($this->_pm->getAccess()->isAdmin()) {
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Activation.php';
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Uninstall.php';

                $this->_pm->getBootstrap()->getInstaller()->addActivation(new Psn_Module_HtmlMails_Installer_Activation());
                $this->_pm->getBootstrap()->getInstaller()->addUninstall(new Psn_Module_HtmlMails_Installer_Uninstall());
            }
        }

        // init email decorator
        require_once $this->getPathinfo()->getRootLib() . 'Service/EmailDecorator.php';
        $emailDecorator = new Psn_Module_HtmlMails_Service_EmailDecorator($this->_pm);

    }

    public function addPluginAdminActions()
    {
        IfwPsn_Wp_Proxy_Action::add('psn_after_admin_navigation_rules', array($this, 'addNav'));
        IfwPsn_Wp_Proxy_Action::add('psn_rule_form', array($this, 'extendForm'));
        IfwPsn_Wp_Proxy_Action::add('psn_testmail_form', array($this, 'extendForm'));
        IfwPsn_Wp_Proxy_Filter::add('psn_db_patcher_rule_fields', array($this, 'addDbPatcherRuleFields'));
        IfwPsn_Wp_Proxy_Action::add('psn_selftester_activate', array($this, 'addSelftests'));
        IfwPsn_Wp_Proxy_Action::add('psn_patch_db', array($this, 'patchDb'));
    }

    /**
     * @param $form
     */
    public function extendForm(IfwPsn_Zend_Form $form)
    {
        $mailTemplates = IfwPsn_Wp_ORM_Model::factory('Psn_Module_HtmlMails_Model_MailTemplates')->select('id')->select('name')->order_by_asc('name')->find_array();

        $values = array('0' => __('None', 'psn'));

        foreach ($mailTemplates as $key => $tpl) {
            $values[$tpl['id']] = $tpl['name'];
        }

        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Form/Validate/ToEmail.php';

        $mailTemplate = $form->createElement('select', 'mail_tpl');
        $mailTemplate
            ->setLabel(__('Mail template', 'psn_htm'))
            ->setDescription(__('Select mail template to be used as text.', 'psn_htm'))
            ->setDecorators($form->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StringToLower'))
            ->setAllowEmpty(false)
            ->addMultiOptions($values)
            ->setOrder(59);
        $form->addElement($mailTemplate);

    }

    /**
     * @param $navigation
     */
    public function addNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Navigation/Page/WpMvc.php';

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Mail templates', 'psn_htm'),
            'controller' => 'htmlmails',
            'action' => 'index',
            'module' => strtolower($this->_pathinfo->getDirname()),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doAction('psn_after_admin_navigation_htmlmails', $navigation);
    }

    /**
     * @param $fields
     * @return mixed
     */
    public function addDbPatcherRuleFields($fields)
    {
        array_push($fields, 'to');
        array_push($fields, 'from');
        array_push($fields, 'mail_tpl');
        return $fields;
    }

    /**
     * Creates the mail templates table if not exists
     */
    public function patchDb()
    {
        $table = new Psn_Module_HtmlMails_Model_MailTemplates();
        $table->createTable();
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Test/MailTemplateField.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/MailTemplateModel.php';

        $selftester->addTestCase(new Psn_Module_HtmlMails_Test_MailTemplateField());
        $selftester->addTestCase(new Psn_Module_HtmlMails_Test_MailTemplateModel());
    }
}
