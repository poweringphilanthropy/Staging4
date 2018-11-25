<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Form.php 450 2015-08-12 21:53:07Z timoreithde $
 */ 
class IfwPsn_Zend_Form extends IfwPsn_Vendor_Zend_Form
{
    const NONCE_KEY = 'nonce';

    /**
     * @var string
     */
    protected $_nonceAction;

    /**
     * @var bool
     */
    protected $_validNonce = true;


    /**
     * @param IfwPsn_Vendor_Zend_Form_Element|string $element
     * @param null $name
     * @param null $options
     * @return IfwPsn_Vendor_Zend_Form
     * @throws IfwPsn_Vendor_Zend_Form_Exception
     */
    public function addElement($element, $name = null, $options = null)
    {
        if ($element instanceof IfwPsn_Vendor_Zend_Form_Element) {
            $name = $element->getName();
        }

        IfwPsn_Wp_Proxy_Action::doAction($this->getName() . '_before_' . $name, $this);

        $result = parent::addElement($element, $name, $options);

        $decoratorHtml = $result->getElement($name)->getDecorator('HtmlTag');
        if ($decoratorHtml) {
            $decoratorHtml->setOption('id', 'form_element_' . $name);
        }

        IfwPsn_Wp_Proxy_Action::doAction($this->getName() . '_after_' . $name, $this);

        return $result;
    }

    /**
     * @return array
     */
    public function removeNonceAndGetValues()
    {
        if ($this->hasNonce()) {
            $this->removeElement(self::NONCE_KEY);
        }
        $values = parent::getValues();

        // sanitize values
        foreach ($values as $k => $v) {
            $v = IfwPsn_Util_Parser_Html::sanitize($v);
            $values[$k] = $v;
        }

        return $values;
    }

    /**
     * @param array $data
     * @return bool
     * @throws IfwPsn_Vendor_Zend_Form_Exception
     */
    public function isValid($data)
    {
        if ($this->hasNonce() && !$this->verifyNonce()) {
            $this->_validNonce = false;
            return false;
        }

        return parent::isValid($data);
    }

    /**
     * @return bool
     */
    public function isValidNonce()
    {
        return $this->_validNonce === true;
    }

    /**
     * @param $action
     */
    public function setNonce($action)
    {
        if (!empty($_REQUEST['id'])) {
            $action .= '-' . $_REQUEST['id'];
        }

        $this->_nonceAction = $action;

        $field = new IfwPsn_Vendor_Zend_Form_Element_Hidden(self::NONCE_KEY);
        $field->setValue(wp_create_nonce($this->_nonceAction));
        $field->setDecorators(array('ViewHelper'));

        $this->addElement($field);
    }

    /**
     * @return bool
     */
    public function verifyNonce()
    {
        $result = wp_verify_nonce($_REQUEST[self::NONCE_KEY], $this->getNonceAction());

        return $result;
    }

    /**
     * @return bool
     */
    public function hasNonce()
    {
        return $this->_nonceAction !== null;
    }

    /**
     * @return string
     */
    public function getNonceAction()
    {
        return $this->_nonceAction;
    }

    /**
     * @return string
     */
    public function hasValidationError()
    {
        return $this->_validationError !== null;
    }

    /**
     * @return string
     */
    public function getValidationError()
    {
        return $this->_validationError;
    }

}
