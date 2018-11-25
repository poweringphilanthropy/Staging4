<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Options field text
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Select.php 346 2014-11-08 13:45:44Z timoreithde $
 */
require_once dirname(__FILE__) . '/../Field.php';

class IfwPsn_Wp_Options_Field_Select extends IfwPsn_Wp_Options_Field
{
    public function render(array $params)
    {
        /**
         * @var IfwPsn_Wp_Options
         */
        $options = $params[0];

        $id = $options->getOptionRealId($this->_id);
        $name = $options->getPageId() . '['. $id .']';

        $selectOptions = $this->_params['options'];
        $selectDefault = $this->_params['optionsDefault'];
        if (!is_array($selectOptions)) {
            $selectOptions = array($selectOptions);
        }

        $html = '<select id="'. $id .'" name="'. $name .'">';
        foreach ($selectOptions as $k => $v) {
            if ($selectDefault == $k) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            $html .= sprintf('<option value="%s"%s>%s</option>', $k, $selected, $v);
        }

        $html .= '</select>';

        if (!empty($this->_description)) {
            $html .= '<br><p class="description"> '  . $this->_description . '</p>';
        }
        echo $html;
    }
}
