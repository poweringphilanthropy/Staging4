<?php
/**
 * MailTemplates form
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: MailTemplate.php 418 2015-09-18 10:25:48Z timoreithde $
 */
class Psn_Module_HtmlMails_Admin_Form_MailTemplate extends IfwPsn_Zend_Form
{
    /**
     * @var array
     */
    protected $_fieldDecorators;



    /**
     * @return void
     */
    public function init()
    {
        $this->setMethod('post')->setName('psn_form_mailtemplate')->setAttrib('accept-charset', 'utf-8');

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
            'label'          => __('Name', 'psn_htm'),
            'description'    => __('Internal identifier', 'psn_htm'),
            'required'       => true,
            'filters'        => array(new IfwPsn_Zend_Filter_SanitizeTextField()),
            'maxlength'      => 80,
//            'validators'     => $_GET['appaction'] == 'create' ? array(new Psn_Admin_Form_Validate_Max()) : array(),
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 10
        ));

        $this->addElement('radio', 'type', array(
            'label'          => __('Mail type', 'psn_htm'),
            'id'             => 'type',
            'required'       => true,
            'filters'        => array(new IfwPsn_Zend_Filter_SanitizeTextField()),
            'decorators'     => $this->getFieldDecorators(),
            'multiOptions'=>array(
                '0' => array('label' => __('Plain text', 'psn_htm')),
                '1' => array('label' => __('HTML', 'psn_htm')),
            ),
            'validators'     => array(new IfwPsn_Vendor_Zend_Validate_InArray(array('0', '1'))),
            'order'          => 20
        ));


        $this->addElement('textarea', 'body', array(
            'label'          => __('Mail body', 'psn_htm'),
            'description'    => __('The main mail body. Insert your HTML source code here if you chose type HTML.', 'psn_htm') . ' ' .
                sprintf(__('Open the help menu in the upper right corner to see a list of all <a %s>supported placeholders</a>.', 'psn'), 'href="javascript:void(0)" class="placeholder_help"') . ' ' .
                sprintf( __('Supports <a %s>conditions</a> (if activated in the options).', 'psn'), 'href="javascript:void(0)" class="conditions_help"'),
            'required'       => true,
            'filters'        => array('StringTrim', 'HtmlEntities'),
            'cols'           => 80,
            'rows'           => 30,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 30
        ));
        $this->getElement('body')->getDecorator('Description')->setEscape(false);

        $this->addElement('textarea', 'altbody', array(
            'label'          => __('Alternative body', 'psn_htm'),
            'description'    => __('If you choose type HTML, insert an alternative plain text here for mail clients not capable of HTML mails.', 'psn_htm') . ' ' .
                sprintf(__('Open the help menu in the upper right corner to see a list of all <a %s>supported placeholders</a>.', 'psn'), 'href="javascript:void(0)" class="placeholder_help"') . ' ' .
                sprintf( __('Supports <a %s>conditions</a> (if activated in the options).', 'psn'), 'href="javascript:void(0)" class="conditions_help"'),
//            'required'       => true,
            'filters'        => array('StringTrim', 'HtmlEntities'),
            'cols'           => 80,
            'rows'           => 20,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 40
        ));
        $this->getElement('altbody')->getDecorator('Description')->setEscape(false);

        $this->setNonce('psn-form-tpl');

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => __('Add template', 'psn_htm'),
            'class'    => 'button-primary',
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'li')),
            ),
            'order' => 120
        ));

    }

    /**
     * @return array
     */
    public function getFieldDecorators()
    {
        return $this->_fieldDecorators;
    }
}
 