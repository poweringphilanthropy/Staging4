<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: License.php 434 2015-10-30 18:09:36Z timoreithde $
 * @package
 */
class Psn_Module_Premium_Admin_Form_License extends IfwPsn_Zend_Form
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
        $this->setMethod('post')->setName('psn_form_license')->setAttrib('accept-charset', 'utf-8');

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

        $this->addElement('password', 'license', array(
            'label'          => __('Premium license code', 'psn_prm'),
            'description'    => sprintf( __('Insert your CodeCanyon license code for this plugin here to be able to use the auto-update feature. Refer to the <a href="%s" target="_blank">documentation</a> for details.', 'psn_prm'),
                'http://docs.ifeelweb.de/post-status-notifier/licensing.html'),
            'filters'        => array(new IfwPsn_Zend_Filter_SanitizeTextField()),
            'maxlength'      => 80,
//            'validators'     => $_GET['appaction'] == 'create' ? array(new Psn_Admin_Form_Validate_Max()) : array(),
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 10
        ));
        $this->getElement('license')->getDecorator('Description')->setEscape(false);

        $this->setNonce('psn-form-license');

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => __('Save', 'psn'),
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
