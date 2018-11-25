<?php
/**
 *
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: NotificationRule.php 427 2015-10-29 19:42:20Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Admin
 */
class Psn_Admin_Form_NotificationRule extends IfwPsn_Zend_Form
{
    /**
     * @var array
     */
    protected $_fieldDecorators;

    /**
     * @var bool
     */
    protected $_hideNonPublicPostTypes = false;



    /**
     * @param null $options
     */
    public function __construct($options = null)
    {
        if (isset($options['hide_nonpublic_posttypes']) && $options['hide_nonpublic_posttypes'] === true) {
            $this->setHideNonPublicPostTypes(true);
            unset($options['hide_nonpublic_posttypes']);
        }
        parent::__construct($options);
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->setMethod('post')->setName('psn_form_rule')->setAttrib('accept-charset', 'utf-8');

        $this->setAttrib('class', 'ifw-wp-zend-form-ul');

        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));

        $this->_fieldDecorators = array(
            new IfwPsn_Zend_Form_Decorator_SimpleInput(),
            array('HtmlTag', array('tag' => 'li')),
            'Errors',
            'Description'
        );

        $this->addElement('text', 'name', array(
            'label'          => __('Rule name', 'psn'),
            'description'    => __('Name of the rule', 'psn'),
            'required'       => true,
            'filters'        => array(new IfwPsn_Zend_Filter_SanitizeTextField()),
            'maxlength'      => 80,
            'validators'     => $_GET['appaction'] == 'create' ? array(new Psn_Admin_Form_Validate_Max()) : array(),
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 10
        ));


        $postTypeOptions = array();
        if ($this->isHideNonPublicPostTypes()) {
            $postTypeOptions['public'] = true;
        }

        /**
         * Post Type
         */
        $postType = $this->createElement('select', 'posttype');
        $postTypeOptions = array_merge(array('all' => __('all types', 'psn')), IfwPsn_Wp_Proxy_Post::getAllTypesWithLabels($postTypeOptions));
        unset($postTypeOptions['attachment']);
        unset($postTypeOptions['nav_menu_item']);

        $postType
            ->setLabel(__('Post type', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array(new IfwPsn_Zend_Filter_SanitizeTextField()))
            ->addMultiOptions($postTypeOptions)
            ->setOrder(20);
        $this->addElement($postType);

        /**
         * Post status
         */
        $statusValues = array_merge(
            array(
                'new' => __('New', 'ifw'),
                'not_published' => __('Not published', 'psn'),
                'not_private' => __('Not private', 'psn'),
                'not_pending' => __('Not pending', 'psn'),
                'not_trash' => __('Not trash', 'psn'),
            ),
            IfwPsn_Wp_Proxy_Post::getAllStatusesWithLabels(array('show_domain' => true))
        );
        natcasesort($statusValues);

        $statusValues = array_merge(array('anything' => __('anything', 'psn')), $statusValues);

        /**
         * Status before
         */
        $statusBefore = $this->createElement('select', 'status_before');
        $statusBefore
            ->setLabel(__('Status before', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array(new IfwPsn_Zend_Filter_SanitizeTextField()))
            ->addMultiOptions($statusValues)
            ->setOrder(30);
        $this->addElement($statusBefore);

        /**
         * Status after
         */
        $statusAfter = $this->createElement('select', 'status_after');
        $statusAfter
            ->setLabel(__('Status after', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array(new IfwPsn_Zend_Filter_SanitizeTextField()))
            //->setValidators(array(new Psn_Admin_Form_Validate_StatusTransition()))
            ->addMultiOptions($statusValues)
            ->setOrder(40);
        $this->addElement($statusAfter);

        /**
         * Subject
         */
        $this->addElement('text', 'notification_subject', array(
            'label'          => __('Subject', 'psn'),
            'description'    => sprintf(__('Open the help menu in the upper right corner to see a list of all <a %s>supported placeholders</a>.', 'psn'), 'href="javascript:void(0)" class="placeholder_help"') . ' ' .
                sprintf( __('Supports <a %s>conditions</a> (if activated in the options).', 'psn'), 'href="javascript:void(0)" class="conditions_help"' ),
            'required'       => true,
            'filters'        => array('StringTrim', 'StripTags'),
            'maxlength'      => 200,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 50
        ));
        $this->getElement('notification_subject')->getDecorator('Description')->setEscape(false);

        /**
         * Body
         */
        $this->addElement('textarea', 'notification_body', array(
            'label'          => __('Text', 'psn'),
            'description'    => sprintf(__('Open the help menu in the upper right corner to see a list of all <a %s>supported placeholders</a>.', 'psn'), 'href="javascript:void(0)" class="placeholder_help"') . ' ' .
                sprintf( __('Supports <a %s>conditions</a> (if activated in the options).', 'psn'), 'href="javascript:void(0)" class="conditions_help"'),
            'validators'     => array(new Psn_Admin_Form_Validate_MailBody()),
            'required'       => false,
            'filters'        => array('StringTrim', 'HtmlEntities'),
            'cols'           => 80,
            'rows'           => 10,
            'allowempty'     => false,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 60
        ));
        $this->getElement('notification_body')->getDecorator('Description')->setEscape(false);



        /**
         * Recipients
         */
        $recipients = IfwPsn_Wp_Proxy_Filter::apply('psn_rule_form_recipients_options', array(
            'admin'  => __('Blog admin', 'psn'),
            'author' => __('Post author', 'psn'),
        ));

        /**
         * TO
         */
        $recipient = $this->createElement('multiselect', 'recipient');
        $recipient
            ->setLabel(__('Recipient', 'psn'))
            ->setDescription(__('To select multiple recipients hold down the control button (ctrl) on Windows or command button (cmd) on Mac.', 'psn'))
            ->setRequired(false)
            ->setValidators(array(new Psn_Admin_Form_Validate_ToEmail()))
            ->setAllowEmpty(false)
            ->setRegisterInArrayValidator(false)
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StripTags'))
            ->setAttrib( 'size', 10 )
            ->addMultiOptions($recipients)
            ->setOrder(70);
        $this->addElement($recipient);

        /**
         * CC
         */
        $cc_select = $this->createElement('multiselect', 'cc_select');
        $cc_select
            ->setLabel(__('CC', 'psn'))
            ->setDescription(__('To select multiple cc recipients hold down the control button (ctrl) on Windows or command button (cmd) on Mac.', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StripTags'))
            ->setAttrib( 'size', 10 )
            ->addMultiOptions($recipients)
            ->setOrder(80);
        $this->addElement($cc_select);

        $this->addElement('textarea', 'cc', array(
            'label'          => __('Custom CC', 'psn'),
            'description'    => IfwPsn_Wp_Proxy_Filter::apply('psn_rule_form_description_cc',
                sprintf(__('Add additional recipient emails. Comma separated. Supports placeholders like [author_email], [blog_admin_email], [current_user_email] or the dynamic [recipient_*] placeholders. Check the <a %s>placeholders help</a>.', 'psn'), 'href="javascript:void(0)" class="placeholder_help"')
                ),
            'filters'        => array('StringTrim', 'HtmlEntities',
                new Psn_Admin_Form_Filter_Cc(IfwPsn_Wp_Plugin_Manager::getInstance('Psn')->isPremium())),
            'cols'           => 80,
            'rows'           => 1,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 85
        ));

        $this->getElement('cc')->getDecorator('Description')->setEscape(false);

        /**
         * BCC
         */
        $bcc_select = $this->createElement('multiselect', 'bcc_select');
        $bcc_select
            ->setLabel(__('BCC', 'psn'))
            ->setDescription(__('To select multiple bcc recipients hold down the control button (ctrl) on Windows or command button (cmd) on Mac.', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StripTags'))
            ->setAttrib( 'size', 10 )
            ->addMultiOptions($recipients)
            ->setOrder(90);
        $this->addElement($bcc_select);

        $this->addElement('textarea', 'bcc', array(
            'label'          => __('Custom BCC', 'psn'),
            'description'    => IfwPsn_Wp_Proxy_Filter::apply('psn_rule_form_description_bcc',
                sprintf(__('Add bcc recipient emails. Comma separated. Supports placeholders like [author_email], [blog_admin_email], [current_user_email] or the dynamic [recipient_*] placeholders. Check the <a %s>placeholders help</a>.', 'psn'), 'href="javascript:void(0)" class="placeholder_help"')
                ),
            'escape'         => false,
            'filters'        => array('StringTrim', 'HtmlEntities',
                new Psn_Admin_Form_Filter_Bcc(IfwPsn_Wp_Plugin_Manager::getInstance('Psn')->isPremium())),
            'cols'           => 80,
            'rows'           => 1,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 95
        ));

        $this->getElement('bcc')->getDecorator('Description')->setEscape(false);


        $excludeCurrentUser = $this->createElement('checkbox', 'exclude_current_user');
        $excludeCurrentUser->setLabel(__('Exclude current user', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setDescription(__('If set, the current user who saves / updates the post will be excluded from all recipients.', 'psn'))
            ->setCheckedValue(1)
            ->setOrder(96)
        ;
        $this->addElement($excludeCurrentUser);

        /**
         * Active
         */
        $active = $this->createElement('checkbox', 'active');
        $active->setLabel(__('Active', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setDescription(__('Only active rules take affect on post transition changes', 'psn'))
            ->setChecked(true)
            ->setCheckedValue(1)
            ->setOrder(100)
            ;
        $this->addElement($active);

        /**
         * Service email
         */
        $email = $this->createElement('checkbox', 'service_email');
        $email->setLabel(__('Email', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setDescription(__('When the rule matches, an email will be send to the recipient with subject and text', 'psn'))
            ->setChecked(true)
            ->setCheckedValue(1)
            ->setOrder(110)
        ;
        $this->addElement($email);

        if (isset($_REQUEST['id'])) {
            $this->addElement('hidden', 'id', array(
                'value' => $_REQUEST['id'],
                'decorators' => array('ViewHelper')
            ));
        }

        $this->setNonce('psn-form-rule');

        $this->addElement('note', 'p', array(
            'label' => '-',
            'value' => '&nbsp;',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'p')),
            ),
            'order' => 999
        ));

        /**
         * Submit button
         */
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => __('Save', 'psn'),
            'class'    => 'button-primary',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span')),
            ),
            'order' => 1000
        ));

        $this->addElement('note', 'divider', array(
            'label' => '-',
            'value' => '&nbsp;&mdash;&nbsp;',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span')),
            ),
            'order' => 1001
        ));

        $this->addElement('submit', 'submit_and_stay', array(
            'ignore'   => true,
            'label'    => __('Save and stay on page', 'psn'),
            'class'    => 'button',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'span')),
            ),
            'order' => 1002
        ));

    }

    /**
     * @return array
     */
    public function getFieldDecorators()
    {
        return $this->_fieldDecorators;
    }

    /**
     * @param boolean $hideNonPublicPostTypes
     */
    public function setHideNonPublicPostTypes($hideNonPublicPostTypes)
    {
        if (is_bool($hideNonPublicPostTypes)) {
            $this->_hideNonPublicPostTypes = $hideNonPublicPostTypes;
        }
    }

    /**
     * @return boolean
     */
    public function isHideNonPublicPostTypes()
    {
        return $this->_hideNonPublicPostTypes === true;
    }


}
