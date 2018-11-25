<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: PsnRecipientslistsController.php 400 2015-08-18 20:15:45Z timoreithde $
 * @package
 */
class Recipients_PsnRecipientslistsController extends PsnModelBindingController
{
    /**
     * DB model class name
     */
    const MODEL = 'Psn_Module_Recipients_Model_RecipientsLists';

    /**
     * @var string
     */
    protected $_itemPostId = 'recipientslist';


    /**
     * @var IfwPsn_Vendor_Zend_Form
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
        $this->_initHelp();

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
                    sprintf(__('Recipient list <b>%s</b> has been saved successfully.', 'psn_rec'), $rule->get('name')));

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

        $item = IfwPsn_Wp_ORM_Model::factory($this->getModelName())->find_one($id);
        $nameBefore = $item->get('name');

        $this->_form->setDefaults($item->as_array());

        if ($this->_request->isPost()) {

            if (!$this->_form->isValidNonce()) {

                $this->getAdminNotices()->persistError(__('Invalid access.', 'psn'));
                $this->gotoIndex();

            } elseif ($this->_form->isValid($this->_request->getPost())) {

                // request is valid, save the changes
                $item->hydrate($this->_form->removeNonceAndGetValues());
                $item->id = $id;
                $item->save();

                $this->getAdminNotices()->persistUpdated(
                    sprintf(__('Recipient list <b>%s</b> has been updated successfully.', 'psn_rec'), $nameBefore));

                $this->gotoIndex();
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
        if ($this->_isInUse($item->get('id'))) {
            $this->getAdminNotices()->persistError(__('Recipients list could not be deleted. It is still in use by a notification rule.', 'psn_rec'));
            $item  = false;
        }

        return $item;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function _isInUse($id)
    {
        $result = IfwPsn_Wp_ORM_Model::factory('Psn_Model_Rule')->find_many();

        foreach ($result as $r) {

            $ruleRecipient = $r->getRecipient();
            if (!is_array($ruleRecipient)) {
                $ruleRecipient = array($ruleRecipient);
            }
            $ruleCc = $r->getCcSelect();
            if (!is_array($ruleCc)) {
                $ruleCc = array($ruleCc);
            }
            $ruleBcc = $r->getBccSelect();
            if (!is_array($ruleBcc)) {
                $ruleBcc = array($ruleBcc);
            }

            $listToken = 'list_' . $id;

            if (in_array($listToken, $ruleRecipient) ||
                in_array($listToken, $ruleCc) ||
                in_array($listToken, $ruleBcc)) {

                return true;
            }
        }

        return false;
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

    protected function _initFormView()
    {
        $this->_initHelp();

        $this->_form = new Psn_Module_Recipients_Admin_Form_RecipientsList();

        $this->_helper->viewRenderer('form');

        if ($this->_request->getActionName() == 'create') {
            $this->view->langHeadline = __('Create new recipients list', 'psn_rec');
        } else {
            $this->view->langHeadline = __('Edit recipients list', 'psn_rec');
            $this->_form->getElement('submit')->setLabel(__('Update', 'psn'));
        }
    }

    protected function _initHelp()
    {
        // set up contextual help
        $help = new IfwPsn_Wp_Plugin_Menu_Help($this->_pm);
        $help->setTitle(__('Recipients lists', 'psn_rec'))
            ->setHelp($this->_getHelpText())
            ->setSidebar($this->_getHelpSidebar('recipients_lists.html'))
            ->load();
    }

    /**
     *
     * @return string
     */
    protected function _getHelpText()
    {
        return sprintf(__('Please consider the documentation page <a href="%s" target="_blank">%s</a> for more information.', 'ifw'),
            'http://docs.ifeelweb.de/post-status-notifier/recipients_lists.html',
            __('Recipients lists', 'psn_rec'));
    }

    public function enqueueScripts()
    {
        IfwPsn_Wp_Proxy_Script::loadAdmin('jquery-ui-dialog');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui');
        IfwPsn_Wp_Proxy_Style::loadAdmin('wp-jquery-ui-dialog');
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return 'Psn_Module_Recipients_Model_RecipientsLists';
    }

    /**
     * @return IfwPsn_Wp_Model_Mapper_Abstract
     */
    public function getModelMapper()
    {
        return Psn_Module_Recipients_Model_Mapper_RecipientsLists::getInstance();
    }

    /**
     * @return IfwPsn_Wp_Plugin_ListTable_Abstract
     */
    public function getListTable()
    {
        return new Psn_Module_Recipients_ListTable_RecipientsLists($this->_pm);
    }

    /**
     * Redirects to index page
     * @return mixed
     */
    public function gotoIndex()
    {
        $this->_gotoRoute('recipientslists', 'index', 'post-status-notifier', array('mod' => 'recipients'));
    }
}

 