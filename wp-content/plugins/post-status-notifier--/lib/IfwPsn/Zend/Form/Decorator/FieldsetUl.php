<?php

class IfwPsn_Zend_Form_Decorator_FieldsetUl extends IfwPsn_Vendor_Zend_Form_Decorator_Abstract
{
 
    public function render($content)
    {
        $displayGroup = $this->getElement();
        
        $html = '<ul>';
        foreach ($displayGroup as $el) {
            $html .= $el->render();
        }
        $html .= '</ul>';
        return $html;
    }
    
}
