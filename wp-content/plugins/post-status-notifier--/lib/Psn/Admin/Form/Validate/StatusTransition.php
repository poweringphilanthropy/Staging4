<?php
/**
 *
 *
 * @author      Timo Reith <timo@ifeelweb.de>
 * @version     $Id: StatusTransition.php 149 2014-03-17 23:56:36Z timoreithde $
 * @copyright   Copyright (c) ifeelweb.de
 * @package     Psn_Admin
 */
class Psn_Admin_Form_Validate_StatusTransition extends IfwPsn_Vendor_Zend_Validate_Abstract
{
    const MSG_INVALID_TRANSITION_ANYTHING_ALL = 'invalidTransitionAnythingAll';
    const MSG_INVALID_TRANSITION_COMBINATION = 'invalidTransitionCombination';

    protected $_messageTemplates = array(
        self::MSG_INVALID_TRANSITION_ANYTHING_ALL => 'a',
        self::MSG_INVALID_TRANSITION_COMBINATION => 'Invalid status combination',
    );

    public function __construct()
    {
        $this->_messageTemplates[self::MSG_INVALID_TRANSITION_ANYTHING_ALL] =
            __('Invalid status combination: before and after set to "anything" is not allowed', 'psn');
    }

    /**
     * (non-PHPdoc)
     * @see IfwPsn_Vendor_Zend_Validate_Interface::isValid()
     */
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if ($context['status_before'] == 'anything' && $context['status_after'] == 'anything') {
            $this->_error(self::MSG_INVALID_TRANSITION_ANYTHING_ALL);
            return false;
        }

        return true;
    }
}
