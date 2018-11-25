<?php
/**
 * RecipientsLists form
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: RecipientsList.php 418 2015-09-18 10:25:48Z timoreithde $
 * @package
 */

class Psn_Module_Recipients_Admin_Form_RecipientsList extends IfwPsn_Zend_Form
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
        $this->setMethod('post')->setName('psn_form_recipientslist')->setAttrib('accept-charset', 'utf-8');

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
            'label'          => __('Name', 'psn_rec'),
            'description'    => __('Internal identifier', 'psn_rec'),
            'required'       => true,
            'filters'        => array(new IfwPsn_Zend_Filter_SanitizeTextField()),
            'maxlength'      => 80,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 10
        ));

        $this->addElement('textarea', 'list', array(
            'label'          => __('List', 'psn_rec'),
            'description'    => __('A list of comma separated email addresses', 'psn_rec'),
            'required'       => true,
            'filters'        => array('StringTrim', 'HtmlEntities'),
            'cols'           => 80,
            'rows'           => 20,
            'decorators'     => $this->getFieldDecorators(),
            'order'          => 30
        ));

        $this->setNonce('psn-form-recipientslist');

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => __('Add recipients list', 'psn_rec'),
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
 