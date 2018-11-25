<?php
/**
 * Rules controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $$Id: PsnRulesController.php 397 2015-08-16 20:09:46Z timoreithde $$
 * @package  IfwPsn_Wp
 */
class PsnRulesController extends PsnModelBindingController
{
    /**
     * @var IfwPsn_Zend_Form
     */
    protected $_form;
//
//    /**
//     * @var IfwPsn_Wp_Plugin_Screen_Option_PerPage
//     */
//    protected $_perPage;



    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        parent::preDispatch();
    }

    /**
     * @param $action
     */
    public function handleBulkAction($action)
    {
        if ( $action == 'deactivate' && is_array( $this->_request->getPost($this->getSingular()) ) ) {
            // bulk action deactivate
            $this->_bulkDeactivate($this->_request->getPost($this->getSingular()));

        } else if ( $action == 'activate' && is_array( $this->_request->getPost($this->getSingular()) ) ) {
            // bulk action activate
            $this->_bulkActivate( $this->_request->getPost($this->getSingular()) );
        }
    }

    public function onBootstrap()
    {
        if ($this->_request->getActionName() == 'index') {
            require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Screen/Option/PerPage.php';
            $this->_perPage = new IfwPsn_Wp_Plugin_Screen_Option_PerPage($this->_pm, __('Items per page', 'ifw'), $this->getModelMapper()->getPerPageId($this->getPluginAbbr() . '_'));
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->_pm->getLogger()->logPrefixed('Executing '. get_class($this) . ':indexAction()');

        IfwPsn_Wp_Proxy_Script::loadAdmin('psn_rules', $this->_pm->getEnv()->getUrlAdminJs() . 'rules.js', array(), $this->_pm->getEnv()->getVersion());
        IfwPsn_Wp_Proxy_Style::loadAdmin('hint', $this->_pm->getEnv()->getUrlAdminCss() . 'hint.min.css');

        // set up contextual help
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';

        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Rules', 'psn'))
            ->setHelp($this->_getDefaultHelpText())
            ->setSidebar($this->_getHelpSidebar('rules.html'))
            ->load();

        // init list table
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/ListTable/Rules.php';
        $this->_initListTable();
        $this->view->listTable = $this->_listTable;

        $this->view->langCreateNewRule = __('Create new rule', 'psn');
        $this->view->isPremium = $this->_pm->isPremium();

        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Patch/Database.php';
        $dbPatcher = new Psn_Patch_Database();
        $this->view->dbPatcher = $dbPatcher;
    }

    /**
     * Create new rule
     */
    public function createAction()
    {
        $this->_initFormView();

        if ($this->_request->isPost()) {

            if (!$this->_form->isValidNonce()) {

                $this->getAdminNotices()->persistError(__('Invalid access.', 'psn'));
                $this->_gotoRoute('rules');

            } elseif ($this->_form->isValid($this->_request->getPost())) {

                // request is valid, save the rule
                $rule = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->create($this->_getFormValues());
                $rule->save();

                $this->getAdminNotices()->persistUpdated(
                    sprintf(__('Rule <b>%s</b> has been saved successfully.', 'psn'), $rule->get('name')));

                if ($this->_form->getValue('submit_and_stay')) {
                    $this->_gotoRoute('rules', 'edit', null, array('id' => $rule->get('id')));
                } else {
                    $this->_gotoRoute('rules');
                }
            }
        }

        $this->view->form = $this->_form;
    }

    /**
     * Edit rules
     */
    public function editAction()
    {
        $this->_initFormView();

        $id = (int)$this->_request->get('id');

        $rule = IfwPsn_Wp_ORM_Model::factory('Psn_Model_Rule')->find_one($id);
        $ruleNameBefore = $rule->get('name');

        $categories = $rule->getCategories();
        if ($categories === null) {
            $categories = array();
        }

        IfwPsn_Wp_Proxy_Script::localize('psn_rule_form', 'psn_taxonomies_selected', $categories);

        $defaults = $rule->as_array();
        $defaults['recipient'] = $rule->getRecipient();
        $defaults['cc_select'] = $rule->getCcSelect();
        $defaults['bcc_select'] = $rule->getBccSelect();
        $defaults['editor_restriction'] = $rule->getEditorRestriction();

        $this->_form->setDefaults(IfwPsn_Wp_Proxy_Filter::apply('psn_rule_form_defaults', $defaults));

        if ($this->_request->isPost()) {

            // handle post request

            if (!$this->_form->isValidNonce()) {

                $this->getAdminNotices()->persistError(__('Invalid access.', 'psn'));
                $this->_gotoRoute('rules');

            } elseif ($this->_form->isValid($this->_request->getPost())) {

                // request is valid, save the changes
                $rule->hydrate($this->_getFormValues());
                $rule->id = $id;
                $rule->save();

                $this->getAdminNotices()->persistUpdated(
                    sprintf(__('Rule <b>%s</b> has been updated successfully.', 'psn'), $ruleNameBefore));

                if ($this->_form->getValue('submit_and_stay')) {
                    $this->_gotoRoute('rules', 'edit', null, array('id' => $id));
                } else {
                    $this->_gotoRoute('rules');
                }
            }
        }

        $this->view->form = $this->_form;
    }

    /**
     * Prepares the submitted values for saving
     * @return array
     */
    protected function _getFormValues()
    {
        $values = $this->_form->removeNonceAndGetValues();
        $posttype = $values['posttype'];


        // categories
        $categories = array();

        if ($this->_request->has('category_include_' . $posttype) && $this->_pm->isPremium()) {
            $categoriesInclude = $this->_request->get('category_include_' . $posttype);
            $categoriesInclude = array_map('intval', $categoriesInclude);
            if (count($categoriesInclude) > 0) {
                sort($categoriesInclude);
                $categories['include'] = $categoriesInclude;
            }
        }
        if ($this->_request->has('category_exclude_' . $posttype) && $this->_pm->isPremium()) {
            $categoriesExclude = $this->_request->get('category_exclude_' . $posttype);
            $categoriesExclude = array_map('intval', $categoriesExclude);
            if (count($categoriesExclude) > 0) {
                sort($categoriesExclude);
                $categories['exclude'] = $categoriesExclude;
            }
        }

        if (empty($categories)) {
            $values['categories'] = null;
        } else {
            $values['categories'] = serialize($categories);
        }

        // serialize recipients
        $values['recipient'] = serialize($values['recipient']);

        // serialize cc_select
        if (empty($values['cc_select'])) {
            $values['cc_select'] = null;
        } else {
            $values['cc_select'] = serialize($values['cc_select']);
        }

        // serialize bcc_select
        if (empty($values['bcc_select'])) {
            $values['bcc_select'] = null;
        } else {
            $values['bcc_select'] = serialize($values['bcc_select']);
        }

        // serialize editor_restriction
        if (empty($values['editor_restriction'])) {
            $values['editor_restriction'] = null;
        } else {
            $values['editor_restriction'] = serialize($values['editor_restriction']);
        }

        if (isset($values['limit_count']) && empty($values['limit_count'])) {
            $values['limit_count'] = null;
        }

        return IfwPsn_Wp_Proxy_Filter::apply('psn_rule_save_form_values', $values);
    }

    /**
     * Initializes commonly used properties
     */
    protected function _initFormView()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Patch/Database.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Admin/Form/NotificationRule.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'Psn/Notification/Placeholders.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Menu/Help.php';

        $dbPatcher = new Psn_Patch_Database();
        $this->view->dbPatcher = $dbPatcher;

        if (!$this->_pm->isPremium()) {
            IfwPsn_Wp_Proxy_Filter::add('psn_rule_form_description_cc', create_function('$var','return $var . " " .
                __("Limited to 1. Get the Premium version for unlimited Cc emails.", "psn");'));
            IfwPsn_Wp_Proxy_Filter::add('psn_rule_form_description_bcc', create_function('$var','return $var . " " .
                __("(Premium feature)", "psn");'));
        }

        $formOptions = array();
        if ($this->_pm->getOptionsManager()->getOption('psn_hide_nonpublic_posttypes') != null) {
            $formOptions['hide_nonpublic_posttypes'] = true;
        }

        $this->_form = new Psn_Admin_Form_NotificationRule($formOptions);

        if (!$this->_pm->isPremium()) {
            $this->_form->getElement('recipient')->setDescription(__('Get additional recipients like user roles (including custom roles) or all users with the Premium version.', 'psn'));
        }

        $this->_helper->viewRenderer('form');

        $placeholders = new Psn_Notification_Placeholders();
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Placeholders', 'psn'))
            ->setId('placeholders')
            ->setHelp($placeholders->getOnScreenHelp())
            ->setSidebar($this->_getHelpSidebar())
            ->load();
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Conditions', 'psn'))
            ->setId('conditions')
            ->setHelp(IfwPsn_Wp_Tpl::getFilesytemInstance($this->_pm)->render('admin_help_conditions.html.twig', array('pm' => $this->_pm)))
            ->setSidebar($this->_getHelpSidebar())
            ->load();

        $this->view->langListOfPlaceholdersLabel = __('Show list of placeholders available for subject and text', 'psn');
        $this->view->langListOfPlaceholdersLink = __('List of placeholders', 'psn');

        $this->view->langHelp = __('Help', 'ifw');
        if (Psn_Model_Rule::hasMax() && Psn_Model_Rule::reachedMax() && $this->getRequest()->getActionName() == 'create') {
            $this->view->maxReached = __(sprintf('You reached the maximum number of rules (%s) for the free version. Get the <a href="%s" target="_blank">Premium Version</a> for unlimmited rules and more features.', Psn_Model_Rule::getMax(), $this->_pm->getConfig()->plugin->premiumUrl), 'psn');
        }

        if ($this->_request->getActionName() == 'create') {
            $this->view->langHeadline = __('Create new rule', 'psn');

            IfwPsn_Wp_Proxy_Script::loadAdmin('psn_rule_examples', $this->_pm->getEnv()->getUrlAdminJs() . 'rule_examples.js', array(), $this->_pm->getEnv()->getVersion());
            IfwPsn_Wp_Proxy_Script::localize('psn_rule_examples', 'PsnExampleRule', array(
                'ThePendingPost' => __('The pending post', 'psn'),
                'ThePendingPostSubject' => __('[blog_name]: New post is waiting for review', 'psn'),
                'ThePendingPostBody' => str_replace('<br>', "\n", __('Howdy admin,<br>there is a new post by [author_display_name] waiting for review:<br>"[post_title]".<br><br>Here is the permalink: [post_permalink]<br><br>Here is the backend edit link: [post_editlink]<br><br>The author\'s email address is [author_email]<br><br>[blog_wpurl]', 'psn')),
                'TheHappyAuthor' => __('The happy author', 'psn'),
                'TheHappyAuthorSubject' => __('Your post on [blog_name] got published!', 'psn'),
                'TheHappyAuthorBody' => str_replace('<br>', "\n", __('Howdy [author_display_name],<br>we are happy to tell you that your post "[post_title]" got published.<br><br>Here is the permalink: [post_permalink]<br><br>Thanks for your good work,<br>your [blog_name]-Team<br><br>[blog_wpurl]', 'psn')),
                'ThePedanticAdmin' => __('The pedantic admin', 'psn'),
                'ThePedanticAdminSubject' => __('[blog_name]: Post status transition from [post_status_before] to [post_status_after]', 'psn'),
                'ThePedanticAdminBody' => str_replace('<br>', "\n", __('Howdy admin,<br>a post status transition was a detected on "[post_title]".<br><br>Status before: [post_status_before]<br>Status after: [post_status_after]<br><br>Post permalink: [post_permalink]', 'psn')),
            ));

            IfwPsn_Wp_Proxy_Style::loadAdmin('psn_rule_examples', $this->_pm->getEnv()->getUrlAdminCss() . 'rule_examples.css');

            $this->view->langExamples = __('Examples', 'psn');
            $this->view->langExamplesDesc = __('Click the buttons below to get an idea of how you can set up notification rules.', 'psn');
            $this->view->langExamplesRuleThePendingPost = __('The pending post', 'psn');
            $this->view->langExamplesRuleThePendingPostDesc = __('This rule sends a notification when a new post got submitted for review.', 'psn');
            $this->view->langExamplesRuleTheHappyAuthor = __('The happy author', 'psn');
            $this->view->langExamplesRuleTheHappyAuthorDesc = __('This rule sends an email to the author of a post when it got published.', 'psn');
            $this->view->langExamplesRuleThePedanticAdmin = __('The pedantic admin', 'psn');
            $this->view->langExamplesRuleThePedanticAdminDesc = __('This rule is for blog admins who want to be informed about every single post status change.', 'psn');
            $this->view->langExamplesRuleDebug = __('Debug rule', 'psn');
            $this->view->langExamplesRuleDebugDesc = __('This rule is just for creating log entries to monitor all available values when the rule matched. Remember to activate option Logger / Log rule matches.', 'psn') .
                ' ' . sprintf('<a href="%s" target="_blank">' . __('More details', 'psn') . '</a>', $this->_pm->getConfig()->plugin->docUrl . 'rules.html#debug-rule');

        } else {
            $this->view->langHeadline = __('Edit notification rule', 'psn');
            $this->_form->getElement('submit')->setLabel(__('Update', 'psn'));
            $this->_form->getElement('submit_and_stay')->setLabel(__('Update and stay on page', 'psn'));
        }

        $this->view->actionName = $this->_request->getActionName();

        IfwPsn_Wp_Proxy_Script::loadAdmin('psn_rule_form', $this->_pm->getEnv()->getUrlAdminJs() . 'rule_form.js', array(), $this->_pm->getEnv()->getVersion());
        IfwPsn_Wp_Proxy_Script::localize('psn_rule_form', 'psn', array('is_premium' => $this->_pm->isPremium()));
        IfwPsn_Wp_Proxy_Script::localize('psn_rule_form', 'psn_taxonomies', array_merge(
            IfwPsn_Wp_Proxy_Post::getAllTypesCategories(),
            array(
                'lang_Categories' => __('Categories', 'psn'),
                'lang_categories_help' => sprintf(__('To select multiple categories hold down the control button (ctrl) on Windows or command button (cmd) on Mac.<br>If nothing is selected, all categories get included.<br>Exclude is dominant. See the <a href="%s" target="_blank">docs</a> for more details.', 'psn'),
                    'http://docs.ifeelweb.de/post-status-notifier/rules.html#category-filter'),
                'lang_include_categories' => __('Include only these categories', 'psn'),
                'lang_exclude_categories' => __('Exclude categories', 'psn'),
                'lang_select_all' => __('select all', 'psn'),
                'lang_remove_all' => __('remove all', 'psn'),
                'lang_no_categories' => __('Post type "%s" has no categories.', 'psn'),
                'lang_premium_feature' => sprintf(__('This is a <a href="%s" target="_blank">Premium</a> feature.', 'psn'), $this->_pm->getConfig()->plugin->premiumUrl),
            ))
        );

        IfwPsn_Wp_Proxy_Action::doAction('psn_rule_form', $this->_form);
    }

    /**
     * @param $item
     * @internal param $values
     * @return mixed
     */
    public function importItemCallback($item)
    {
        $deactivate = $this->_request->get('import_deactivate');

        if ($deactivate != null) {
            $item['active'] = 0;
        }

        return $item;
    }

    /**
     * @param array $rules
     */
    protected function _bulkDeactivate(array $rules)
    {
        foreach($rules as $ruleId) {
            $rule = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one((int)$ruleId);
            $rule->active = 0;
            $rule->save();
        }
    }

    public function activateAction()
    {
        $id = (int)$this->_request->get('id');

        if (wp_verify_nonce($this->_request->get('_wpnonce'), 'activate' . $id)) {
            $rule = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($id);

            $ruleModelClass = $this->getModelName();
            if ($rule instanceof $ruleModelClass) {
                $rule->active = 1;
                $rule->save();
            }
        }

        $this->_gotoIndex();
    }

    public function deactivateAction()
    {
        $id = (int)$this->_request->get('id');

        if (wp_verify_nonce($this->_request->get('_wpnonce'), 'deactivate' . $id)) {
            $rule = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($id);

            $ruleModelClass = $this->getModelName();
            if ($rule instanceof $ruleModelClass) {
                $rule->active = 0;
                $rule->save();
            }
        }

        $this->_gotoIndex();
    }

    /**
     * @param array $rules
     */
    protected function _bulkActivate($rules)
    {
        foreach($rules as $ruleId) {
            $rule = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one((int)$ruleId);
            $rule->active = 1;
            $rule->save();
        }
    }

    /**
     * @return string
     */
    protected function _getDefaultHelpText()
    {
        return sprintf(__('Please consider the documentation page <a href="%s" target="_blank">%s</a> for more information.', 'ifw'),
            'http://docs.ifeelweb.de/post-status-notifier/rules.html',
            __('Rules', 'psn'));
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Model_Rule';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Model_Mapper_Rule::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Admin_ListTable_Rules($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('rules');
    }

}