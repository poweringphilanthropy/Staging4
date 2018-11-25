<?php
/**
 *
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: TestMail.php 400 2015-08-18 20:15:45Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Admin
 */
class Psn_Admin_Form_TestMail extends IfwPsn_Zend_Form
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
        $this->setMethod('post')->setName('psn_test_mail')->setAttrib('accept-charset', 'utf-8');

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

        $recipients = IfwPsn_Wp_Proxy_Filter::apply('psn_testmail_form_recipients_options', array(
            'admin'  => __('Blog admin', 'psn'),
        ));

        $recipient = $this->createElement('select', 'recipient');
        $recipient
            ->setLabel(__('Recipient', 'psn'))
            ->setDecorators($this->getFieldDecorators())
            ->setFilters(array('StringTrim', 'StripTags'))
            ->addMultiOptions($recipients)
            ->setOrder(40);
        $this->addElement($recipient);

        $this->setNonce('psn-form-test-mail');

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => __('Send test email', 'psn'),
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
