<?php
/**
 * Premium recipients module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 390 2015-06-07 22:18:56Z timoreithde $
 */
class Psn_Recipients_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_recipients';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'Recipients';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Extends the choice of recipients';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_rec';

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
        if ($this->_pm->getAccess()->isPlugin()) {

            $this->_addPluginAdminActions();
            $this->addOptions();
        }

        if ($this->_pm->getAccess()->isAdmin()) {
            // add the installer activation and uninstall
            $this->_pm->getInstaller()->addActivation(new Psn_Module_Recipients_Installer_Activation());
            $this->_pm->getInstaller()->addUninstall(new Psn_Module_Recipients_Installer_Uninstall());
        }

        require_once $this->getPathinfo()->getRootLib() . 'RecipientsHandler.php';
        new Psn_Module_Recipients_RecipientsHandler($this->_pm);
    }

    protected function addOptions()
    {
    }

    protected function _addPluginAdminActions()
    {
        // extend the admin form
        IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'rule_form_recipients_options', array($this, 'extendRecipients'));
        IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'testmail_form_recipients_options', array($this, 'extendTestmailRecipients'));
        IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'db_patcher_rule_fields', array($this, 'addDbPatcherRuleFields'));

        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'rule_form', array($this, 'extendForm'));
        add_filter('psn_rule_form_defaults', array($this, 'filterRuleFormDefaults'));
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'testmail_form', array($this, 'extendTestmailForm'));

        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'selftester_activate', array($this, 'addSelftests'));

        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_admin_navigation_htmlmails', array($this, 'addNav'));
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'patch_db', array($this, 'patchDb'));
    }

    /**
     * @param $navigation
     */
    public function addNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Recipients lists', 'psn_rec'),
            'controller' => 'recipientslists',
            'action' => 'index',
            'module' => strtolower($this->_pathinfo->getDirname()),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $navigation->addPage($page);
    }

    /**
     * @param array $options
     * @return array
     */
    public function extendRecipients($options)
    {
        //$email = array('individual_email' => __('Custom recipient (text field below)', 'psn_rec'));

        $roles = array();
        foreach (IfwPsn_Wp_Proxy_Role::getAllNames() as $k => $v) {
            $roles['role_' . $k] = __('All members of role: ', 'psn_rec') . $v;
        }

        $lists = array();
        foreach (IfwPsn_Wp_ORM_Model::factory('Psn_Module_Recipients_Model_RecipientsLists')->find_array() as $row) {
            $lists['list_' . $row['id']] = __('Recipients list: ', 'psn_rec') . $row['name'];
        }

        return array_merge($options, $roles, array('all_users' => __('All users', 'psn_rec')), $lists);
    }

    /**
     * @param array $options
     * @return array
     */
    public function extendTestmailRecipients($options)
    {
        return array_merge($options, array('custom' => __('Custom recipient (text field below)', 'psn_rec')));
    }

    /**
     * @param $fields
     * @return mixed
     */
    public function addDbPatcherRuleFields($fields)
    {
        array_push($fields, 'to');
        array_push($fields, 'from');
        array_push($fields, 'editor_restriction');
        return $fields;
    }

    /**
     * @param $form
     */
    public function extendForm(IfwPsn_Zend_Form $form)
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Form/Validate/ToEmail.php';

        $to = $form->createElement('text', 'to');
        $to
            ->setLabel(__('Custom recipient', 'psn_rec'))
            ->setDescription(__('Enter an individual e-mail address as recipient here. Supports placeholders.', 'psn_rec'))
            ->setDecorators($form->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StringToLower'))
            //->setValidators(array(new Psn_Admin_Form_Validate_ToEmail()))
            ->setAllowEmpty(true)
            ->setOrder(71);
        $form->addElement($to);

        if ($this->_pm->hasOption('psn_conditions_enable_dyn_to')) {
            IfwPsn_Wp_Proxy_Script::loadAdmin('ace', $this->_pm->getEnv()->getUrlAdminJs() . 'lib/ace/ace.js', array(), $this->_pm->getEnv()->getVersion());
            IfwPsn_Wp_Proxy_Script::loadAdmin('rec-rules-form', $this->getEnv()->getUrlJs() . 'rules_form.js', array('jquery'), $this->_pm->getEnv()->getVersion());
            IfwPsn_Wp_Proxy_Style::loadAdmin('rec-rules-form-css', $this->getEnv()->getUrlCss() . 'admin.css', array(), $this->_pm->getEnv()->getVersion());

            $from = $form->createElement('textarea', 'to_dyn');
            $from
                ->setLabel(__('Dynamic recipients', 'psn_rec'))
                ->setDescription(__('Enter dynamic recipients code here.', 'psn_rec') . ' ' . sprintf(__('For more details please check the <a href="%s" target="_blank">documentation</a>.', 'psn'), $this->_pm->getConfig()->plugin->docUrl . 'dynamic_recipients.html'))
                ->setDecorators($form->getFieldDecorators())
                ->setFilters(array('StringTrim'))
                ->setAttrib('cols', 80)
                ->setAttrib('rows', 5)
                ->setAttrib('ace_editor', true)
                ->setAttrib('html_entity_decode', false)
                ->setOrder(73);
            $form->addElement($from);
            $form->getElement('to_dyn')->getDecorator('Description')->setEscape(false);
        }

        $editor_restriction = $form->createElement('multiselect', 'editor_restriction');
        $editor_restriction
            ->setLabel(__('Editor restriction', 'psn_rec'))
            ->setDescription(__('If you select one or more roles, a notification will only be generated, if the editor of the post is a member of one of the selected roles. Leave blank for no editor restriction. To select multiple roles hold down the control button (ctrl) on Windows or command button (cmd) on Mac.', 'psn_rec'))
            ->setDecorators($form->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StripTags'))
            ->setAttrib( 'size', 10 )
            ->addMultiOptions(IfwPsn_Wp_Proxy_Role::getAllNames())
            ->setOrder(97);
        $form->addElement($editor_restriction);

        $defaultFrom = $this->_pm->getOptionsManager()->getOption('psn_default_from');
        if (empty($defaultFrom)) {
            $defaultFrom = IfwPsn_Wp_Proxy_Blog::getDefaultEmailFrom();
        }

        $fromDesciption = __('Enter an e-mail address to use as sender. Leave blank for default sender. Supports placeholders.', 'psn_rec') . '<br>' .
            str_replace(array('<', '>'), array('&lt;', '&gt;'), __('Format: Sender Name <sender@domain.com>', 'psn_rec'))   . '<br>' .
            __('Default', 'psn_rec') . ': ' . str_replace(array('<', '>'), array('&lt;', '&gt;'), $defaultFrom);

        $from = $form->createElement('text', 'from');
        $from
            ->setLabel(__('FROM', 'psn_rec'))
            ->setDescription($fromDesciption)
            ->setDecorators($form->getFieldDecorators())
            ->setFilters(array('StringTrim'))
            ->setAllowEmpty(false)
            ->setAttrib('placeholder', 'Sender Name <sender@domain.com>')
            ->setOrder(98);
        $form->addElement($from);

        $form->getElement('from')->getDecorator('Description')->setEscape(false);
    }

    /**
     * @param array $defaults
     * @return array
     */
    public function filterRuleFormDefaults(array $defaults)
    {
        if (isset($defaults['to_dyn'])) {
            $defaults['to_dyn'] = htmlentities($defaults['to_dyn'], ENT_NOQUOTES, IfwPsn_Wp_Proxy_Blog::getCharset());
        }
        return $defaults;
    }

    /**
     * @param $form
     */
    public function extendTestmailForm(IfwPsn_Zend_Form $form)
    {
        $to = $form->createElement('text', 'custom_recipient');
        $to
            ->setLabel(__('Custom recipient', 'psn_rec'))
            ->setDescription(__('Enter an individual e-mail address to use as recipient.', 'psn_rec'))
            ->setDecorators($form->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StringToLower'))
            ->setAllowEmpty(true)
            ->setOrder(41);
        $form->addElement($to);
    }

    /**
     * Creates the mail templates table if not exists
     */
    public function patchDb()
    {
        $table = new Psn_Module_Recipients_Model_RecipientsLists();
        $table->createTable();
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Test/ToField.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/ToDynField.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/FromField.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/EditorRestrictionField.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/RecipientsListsModel.php';

        $selftester->addTestCase(new Psn_Module_Recipients_Test_ToField());
        $selftester->addTestCase(new Psn_Module_Recipients_Test_ToDynField());
        $selftester->addTestCase(new Psn_Module_Recipients_Test_FromField());
        $selftester->addTestCase(new Psn_Module_Recipients_Test_EditorRestrictionField());
        $selftester->addTestCase(new Psn_Module_Recipients_Test_RecipientsListsModel());
    }
}
