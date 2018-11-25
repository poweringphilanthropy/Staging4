<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Importer.php 465 2015-09-27 20:36:12Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Data_Importer 
{
    /**
     * @var string
     */
    protected $_file;

    /**
     * @var array (item_name_plural, item_name_singular)
     */
    protected $_xmlOptions = array();

    /**
     * @var string
     */
    protected $_error;


    /**
     * @param $file
     * @param $xmlOptions
     */
    public function __construct($file, $xmlOptions)
    {
        $this->_file = $file;

        if (is_array($xmlOptions)) {
            $this->_xmlOptions = $xmlOptions;
        }
    }

    /**
     * @param $modelname
     * @param $options
     * @return bool|int
     */
    public function import($modelname, $options = array())
    {
        if (empty($this->_file)) {
            $this->_error = __('Please select a valid import file.', 'ifw');
            return false;
        }

        $xml = simplexml_load_file($this->_file);

        // check for valid xml
        if (!$xml) {
            $this->_error = __('Please select a valid import file.', 'ifw');
            return false;
        }

        if (!isset($this->_xmlOptions['item_name_singular']) && !isset($this->_xmlOptions['node_name_singular'])) {
            $this->_error = __('Missing item singular name.', 'ifw');
            return false;
        }

        if (isset($this->_xmlOptions['node_name_singular'])) {
            $nodeNameSingular = $this->_xmlOptions['node_name_singular'];
        } else {
            $nodeNameSingular = $this->_xmlOptions['item_name_singular'];
        }


        $items = $this->_getItems($xml, $nodeNameSingular);

        if (count($items) == 0) {
            $this->_error = __('No items found in import file.', 'ifw');
            return;
        }

        // import
        return IfwPsn_Wp_ORM_Model::import($modelname, $items, $options);
    }

    /**
     * @param $xml
     * @param $itemNodeName
     * @return array
     */
    protected function _getItems($xml, $itemNodeName, $itemNameCol = 'name')
    {
        $items = array();

        // check if xml contains items
        if (count($xml->{$itemNodeName}) == 0) {
            // no items found
            return $items;
        }

        foreach($xml->{$itemNodeName} as $item) {

            $tmpItem = array();

            /**
             * @var SimpleXMLElement $col
             */
            foreach($item as $col) {

                $attr = $col->attributes();

                if (isset($attr[$itemNameCol])) {
                    $tmpItem[(string)$col[$itemNameCol]] = (string)$col;
                } else {
                    foreach (get_object_vars($col) as $colVar => $colVal) {

                        if (is_array($colVal) && !empty($colVal)) {
                            $tmpItem[$colVar] = $colVal;
                        }
                        elseif (is_a($colVal, 'SimpleXMLElement') && !empty($colVal)) {
                            $tmpItem[$colVar] = array($colVal);
                        }
                    }
                }
            }

            if (is_array($tmpItem)) {
                array_push($items, $tmpItem);
            }
        }

        return $items;
    }

    /**
     * @param string $itemNameCol
     * @return string|null
     */
    public function getItemName($itemNameCol = 'name')
    {
        $xml = simplexml_load_file($this->_file);

        if (isset($this->_xmlOptions['node_name_singular'])) {
            $itemNodeName = $this->_xmlOptions['node_name_singular'];
        } else {
            $itemNodeName = $this->_xmlOptions['item_name_singular'];
        }

        $items = $this->_getItems($xml, $itemNodeName, $itemNameCol);

        if (isset($items[0][$itemNameCol])) {
            return $items[0][$itemNameCol];
        }

        return null;
    }

    /**
     * @param string $itemNameCol
     * @return array|null
     */
    public function getItemValues($itemNameCol = 'name')
    {
        $xml = simplexml_load_file($this->_file);

        if (isset($this->_xmlOptions['node_name_singular'])) {
            $itemNodeName = $this->_xmlOptions['node_name_singular'];
        } else {
            $itemNodeName = $this->_xmlOptions['item_name_singular'];
        }

        $items = $this->_getItems($xml, $itemNodeName, $itemNameCol);

        if (isset($items[0]) && !empty($items[0])) {
            return $items[0];
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param $identifier
     * @param array $options
     * @return string
     */
    public static function getForm(IfwPsn_Wp_Plugin_Manager $pm, $identifier, $options = array())
    {
        $context = array(
            'identifier' => $identifier,
            'headline' => $options['headline'],
            'help_text' => $options['help_text'],
            'action_url' => $options['action_url'],
            'import_file_label' => $options['import_file_label'],
            'import_file_description' => $options['import_file_description'],
            'import_prefix_label' => $options['import_prefix_label'],
            'import_prefix_description' => $options['import_prefix_description'],
            'wait_text_headline' => $options['wait_text_headline'],
            'wait_text_description' => $options['wait_text_description'],
            'nonce' => wp_create_nonce(IfwPsn_Zend_Controller_ModelBinding::getImportNonceAction($identifier))
        );

        return IfwPsn_Wp_Tpl::getFilesytemInstance($pm)->render('import_form.html.twig', $context);
    }
}
 