<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Option which will not be displayed on options page
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: External.php 458 2015-08-24 22:17:27Z timoreithde $
 */
require_once dirname(__FILE__) . '/../Field.php';

class IfwPsn_Wp_Options_Field_External extends IfwPsn_Wp_Options_Field
{
    public function render(array $params)
    {
        /**
         * @var IfwPsn_Wp_Options
         */
        $options = $params[0];

        $id = $options->getOptionRealId($this->_id);
        $name = $options->getPageId() . '['. $id .']';

        $value = $options->getOption($this->_id);
        if (is_array($value)) {
            $html = '';
            foreach ($value as $val) {
                $html .= '<input type="hidden" id="'. $id .'" name="'. $name .'[]" value="'. esc_attr($val) .'" />';
            }
        } else {
            $html = '<input type="hidden" id="'. $id .'" name="'. $name .'" value="'. esc_attr($value) .'" />';
        }

        echo $html;
    }
}
