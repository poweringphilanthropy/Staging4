<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Checks if a model name is unique
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: UniqueName.php 496 2015-12-12 19:47:08Z timoreithde $
 * @package  IfwPsn_Wp
 */
abstract class IfwPsn_Zend_Validate_UniqueName extends IfwPsn_Vendor_Zend_Validate_Abstract
{
    const MSG_INVALID_NAME = 'name';
    const MSG_EXISTING_NAME = 'nameExists';

    protected $_messageTemplates = array(
        self::MSG_INVALID_NAME => 'a',
        self::MSG_EXISTING_NAME => 'b',
    );

    public function __construct()
    {
        $this->_messageTemplates[self::MSG_INVALID_NAME] =
            __('Invalid name.', 'asa2') . ' ' . __('Allowed characters: alphanumeric including underscore (a-z, A-Z, 0-9, _)', 'ifw');
        $this->_messageTemplates[self::MSG_EXISTING_NAME] =
            __('An entry with this name already exists', 'ifw');
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Validate_Interface::isValid()
     */
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if ($this->_isValidName($value) == false) {
            $this->_error(self::MSG_INVALID_NAME);
            return false;
        }

        if (isset($context['id'])) {
            $id = $context['id'];
        } else {
            $id = null;
        }
        if ($this->_exists($value, $id)) {
            $this->_error(self::MSG_EXISTING_NAME);
            return false;
        }

        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    abstract protected function _isValidName($value);

    /**
     * @param $value
     * @param $id
     * @return bool
     */
    abstract protected function _exists($value, $id);
}
