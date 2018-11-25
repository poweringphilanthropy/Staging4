<?php

class IfwPsn_Zend_Form_Decorator_SimpleInput extends IfwPsn_Vendor_Zend_Form_Decorator_Abstract
{
    protected $_formatText = '<label for="%s">%s</label><input id="%s" name="%s" type="text" value="%s" %s autocomplete="off" />';
    protected $_formatPassword = '<label for="%s">%s</label><input id="%s" name="%s" type="password" value="%s" %s autocomplete="off" />';
    protected $_formatTextarea = '<label for="%s">%s</label><textarea id="%s" name="%s" cols="%s" rows="%s" autocomplete="off">%s</textarea>';
    protected $_formatAceEditor = '<label for="%s">%s</label><textarea name="%s" autocomplete="off" style="display: none;">%s</textarea><div id="%s"></div>';
    protected $_formatSelect = '<label for="%s">%s</label><select id="%s" name="%s" />%s</select>';
    protected $_formatMultiselect = '<label for="%s">%s</label><select id="%s" name="%s" size="%s" multiple />%s</select>';
    protected $_formatMulticheckbox = '<label>%s</label>%s';
    protected $_formatCheckbox = '<label for="%s">%s</label><input id="%s" name="%s" type="checkbox" value="%s" %s />';
    protected $_formatRadio = '<label for="%s"><input id="%s" name="%s" type="radio" value="%s" %s />%s</label>';

    public function render($content)
    {
        $element = $this->getElement();
        $name    = htmlentities($element->getFullyQualifiedName());
        $label   = $element->getLabel();
        $id      = htmlentities($element->getId());

        $value = $element->getValue();
        if (is_string($value)) {
            $value = htmlentities($element->getValue(), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset());
        }

        switch ($element->getType()) {

            case 'IfwPsn_Vendor_Zend_Form_Element_Textarea':

                $html_entity_decode = $element->getAttrib('html_entity_decode');

                if (!isset($html_entity_decode) || empty($html_entity_decode) || $html_entity_decode === true) {
                    $value = html_entity_decode($element->getValue(), ENT_COMPAT, IfwPsn_Wp_Proxy_Blog::getCharset());
                }

                if ($element->getAttrib('ace_editor') == true) {
                    $value = $element->getValue();
                    $format = $this->_formatAceEditor;
                    $markup = sprintf($format, $name, $label, $name, $value, $id);
                } else {
                    $format = $this->_formatTextarea;
                    $cols = $element->getAttrib('cols');
                    $rows = $element->getAttrib('rows');
                    $markup = sprintf($format, $name, $label, $id, $name, $cols, $rows, $value);
                }

                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_Select':

                $options = '';
                foreach($element->getAttrib('options') as $k => $v) {
                    $options .= sprintf('<option value="%s"%s>%s</option>',
                        $k,
                        $k == $value ? ' selected="selected"' : '',
                        $v);
                }
                $markup = sprintf($this->_formatSelect, $id, $label, $id, $name, $options);
                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_Multiselect':

                $defaults = $element->getValue();
                if (!is_array($defaults)) {
                    if (empty($defaults)) {
                        $defaults = array();
                    } else {
                        $defaults = array($defaults);
                    }
                }

                $options = '';
                foreach($element->getAttrib('options') as $k => $v) {
                    $options .= sprintf('<option value="%s"%s>%s</option>',
                        $k,
                        in_array($k, $defaults) ? ' selected="selected"' : '',
                        $v);
                }
                $markup = sprintf($this->_formatMultiselect, $id, $label, $id, $name, $element->getAttrib('size'), $options);
                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_MultiCheckbox':

                $options = '';
                foreach($element->getAttrib('options') as $k => $v) {
                    $options .= sprintf('<label><input type="checkbox" name="%s" value="%s">%s</label>',
                        $name,
                        $k,
                        $v);
                }

                $markup = sprintf($this->_formatMulticheckbox, $label, $options);

                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_Checkbox':

                $value = $element->getCheckedValue();

                $checked = $element->isChecked() ? 'checked="checked"' : '';
                $markup = sprintf($this->_formatCheckbox, $id, $label, $id, $name, $value, $checked);
                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_Radio':

                $markup = '<label>' . $label . '</label>';

                foreach($element->getAttrib('options') as $k => $v) {
                    $optid = $id . '-' . $k;
                    $checked = ($k == $value) ? 'checked="checked"': '';
                    $markup .= sprintf($this->_formatRadio, $optid, $optid, $name, $k, $checked, $v['label']);
                }

                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_Password':
                $additionalParams = '';
                if ($element->getAttrib('maxlength') != null) {
                    $additionalParams .= sprintf('maxlength="%s"', $element->getAttrib('maxlength'));
                }
                if ($element->getAttrib('class') != null) {
                    $additionalParams .= sprintf('class="%s"', htmlspecialchars($element->getAttrib('class')));
                }

                $markup  = sprintf($this->_formatPassword, $id, $label, $id, $name, $value, $additionalParams);
                break;

            case 'IfwPsn_Vendor_Zend_Form_Element_Text':
            default:
                $additionalParams = '';
                if ($element->getAttrib('maxlength') != null) {
                    $additionalParams .= sprintf('maxlength="%s"', $element->getAttrib('maxlength'));
                }
                if ($element->getAttrib('placeholder') != null) {
                    $additionalParams .= sprintf('placeholder="%s"', htmlspecialchars($element->getAttrib('placeholder')));
                }
                if ($element->getAttrib('class') != null) {
                    $additionalParams .= sprintf('class="%s"', htmlspecialchars($element->getAttrib('class')));
                }
                $markup  = sprintf($this->_formatText, $id, $label, $id, $name, $value, $additionalParams);
                break;
        }

        return $markup;
    }
    
}
