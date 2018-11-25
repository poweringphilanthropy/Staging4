<?php
/**
 * Index controller
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PsnHtmlmailsController.php 398 2015-08-17 21:50:58Z timoreithde $
 * @package  IfwPsn_Wp
 */
class Htmlmails_PsnHtmlmailsController extends PsnModelBindingController
{
    /**
     * @var IfwPsn_Zend_Form
     */
    protected $_form;



    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        parent::preDispatch();
    }

    public function onBootstrap()
    {
        if ($this->_request->getActionName() == 'index') {
            $this->_perPage = new IfwPsn_Wp_Plugin_Screen_Option_PerPage($this->_pm, __('Items per page', 'ifw'), $this->getModelMapper()->getPerPageId($this->getPluginAbbr() . '_'));
        }
    }

    /**
     * 
     */
    public function indexAction()
    {
        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Mail templates', 'psn_htm'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar('mail_templates.html'))
            ->load();

        $this->_initListTable();

        $this->view->listTable = $this->_listTable;
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
                $this->gotoIndex();

            } elseif ($this->_form->isValid($this->_request->getPost())) {

                // request is valid, save the rule
                $rule = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->create($this->_form->removeNonceAndGetValues());
                $rule->save();

                $this->getAdminNotices()->persistUpdated(
                    sprintf(__('Mail template "%s" has been saved successfully.', 'psn_htm'), $rule->get('name')));

                $this->gotoIndex();
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

        $template = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($id);
        $templateNameBefore = $template->get('name');


        $this->_form->setDefaults($template->as_array());

        if ($this->_request->isPost()) {

            if (!$this->_form->isValidNonce()) {

                $this->getAdminNotices()->persistError(__('Invalid access.', 'psn'));
                $this->gotoIndex();

            } elseif ($this->_form->isValid($this->_request->getPost())) {

                // request is valid, save the changes
                $template->hydrate($this->_form->removeNonceAndGetValues());
                $template->id = $id;
                $template->save();

                $this->getAdminNotices()->persistUpdated(
                    sprintf(__('Mail template "%s" has been updated successfully.', 'psn_htm'), $templateNameBefore));

                $this->_gotoIndex();
            }
        }

        $this->view->form = $this->_form;
    }

    /**
     * @param IfwPsn_Wp_ORM_Model $item
     * @return bool|IfwPsn_Wp_ORM_Model
     */
    public function deleteCallback(IfwPsn_Wp_ORM_Model $item)
    {
        if ($this->_isTemplateInUse($item->get('id'))) {
            $this->getAdminNotices()->persistError(__('Mail template could not be deleted. It is still in use by a notification rule.', 'psn_htm'));
            $item  = false;
        }

        return $item;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function _isTemplateInUse($id)
    {
        $result = IfwPsn_Wp_ORM_Model::factory('Psn_Model_Rule')->where_equal('mail_tpl', $id)->find_array();

        if (!is_array($result)) {
            $result = array();
        }

        return count($result) > 0;
    }

    /**
     * Copies a template
     */
    public function copyAction()
    {
        $this->handleCopy();
    }

    /**
     * Imports templates
     */
    public function importAction()
    {
        $this->handleImport();
    }

    /**
     *
     */
    public function exportAction()
    {
        $this->handleExport( (int)$this->getRequest()->get('id'));

        $this->gotoIndex();
    }

    /**
     * @param array $items
     */
    protected function _bulkExport($items)
    {
        $this->handleExport($items);

        $this->gotoIndex();
    }

    /**
     * Initialize common form settings
     */
    protected function _initFormView()
    {
        $mod = $this->_pm->getBootstrap()->getModuleManager()->getModule('psn_mod_htm');

        IfwPsn_Wp_Proxy_Script::loadAdmin('ckeditor', $mod->getEnv()->getUrlJs() . 'ckeditor/ckeditor.js', array('jquery'), $mod->getEnv()->getVersion());
        IfwPsn_Wp_Proxy_Script::localize('ckeditor', 'ckconfig', array(
            'lang' => IfwPsn_Wp_Proxy_Blog::getLanguageShort()
        ));
        IfwPsn_Wp_Proxy_Script::loadAdmin('ckeditor-adapter', $mod->getEnv()->getUrlJs() . 'ckeditor/adapters/jquery.js', array('ckeditor'), $mod->getEnv()->getVersion());


        $this->_form = new Psn_Module_HtmlMails_Admin_Form_MailTemplate();

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

        if ($this->_request->getActionName() == 'create') {
            $this->view->langHeadline = __('Create new mail template', 'psn_htm');
        } else {
            $this->view->langHeadline = __('Edit mail template', 'psn_htm');
            $this->_form->getElement('submit')->setLabel(__('Update', 'psn'));
        }
    }

    /**
     * @return string
     */
    protected function _getHelpText()
    {
        return sprintf(__('Please consider the documentation page <a href="%s" target="_blank">%s</a> for more information.', 'ifw'),
            'http://docs.ifeelweb.de/post-status-notifier/mail_templates.html',
            __('Mail templates', 'psn_htm'));
    }
    
    public function enqueueScripts()
    {
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery-ui-dialog');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui-dialog');
    }

    protected function _gotoIndex()
    {
        $this->gotoIndex();
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Module_HtmlMails_Model_MailTemplates';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Module_HtmlMails_Model_Mapper_MailTemplates::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Module_HtmlMails_ListTable_MailTemplates($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('htmlmails', 'index', 'post-status-notifier', array('mod' => 'htmlmails'));
    }
}
