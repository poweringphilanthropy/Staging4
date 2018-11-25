<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Options field text
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Text.php 458 2015-08-24 22:17:27Z timoreithde $
 */
require_once dirname(__FILE__) . '/../Field.php';

class IfwPsn_Wp_Options_Field_Text extends IfwPsn_Wp_Options_Field
{
    public function render(array $params)
    {
        /**
         * @var IfwPsn_Wp_Options
         */
        $options = $params[0];

        $id = $options->getOptionRealId($this->_id);
        $name = $options->getPageId() . '['. $id .']';

        $extra = '';
        if (isset($this->_params['placeholder'])) {
            $extra .= sprintf('placeholder="%s" ', htmlentities($this->_params['placeholder']));
        }
        if (isset($this->_params['length'])) {
            $extra .= sprintf('length="%s" ', (int)$this->_params['length']);
        }
        if (isset($this->_params['maxlength'])) {
            $extra .= sprintf('maxlength="%s" ', (int)$this->_params['maxlength']);
        }

        $html = '<input type="text" autocomplete="off" id="'. $id .'" name="'. $name .'" value="'. esc_attr($options->getOption($this->_id)) .'" '. $extra .' />';
        if (!empty($this->_params['error'])) {
            $html .= '<br><p class="error"> '  . $this->_params['error'] . '</p>';
        }
        if (!empty($this->_description)) {
            $html .= '<br><p class="description"> '  . $this->_description . '</p>';
        }
        echo $html;
    }
}
