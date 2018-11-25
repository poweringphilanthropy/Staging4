<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Options section
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Section.php 302 2014-07-25 21:34:41Z timoreithde $
 */
class IfwPsn_Wp_Options_Section
{
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var string
     */
    protected $_description = '';

    /**
     * @var null|string
     */
    protected $_pageId;

    /**
     * @var array
     */
    protected $_fields = array();


    /**
     * @param $id
     * @param $label
     * @param null $description
     */
    public function __construct($id, $label, $description=null)
    {
        $this->_id = $id;
        $this->_label = $label;
        if ($description !== null) {
            $this->_description = $description;
        }
    }

    /**
     * @param IfwPsn_Wp_Options_Field $field
     * @return $this
     */
    public function addField(IfwPsn_Wp_Options_Field $field)
    {
        $this->_fields[] = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return bool
     */
    public function hasFields()
    {
        return count($this->_fields) > 0;
    }

    /**
     *
     */
    public function render()
    {
        echo $this->_description;
    }

    /**
     * @param null|string $pageId
     */
    public function setPageId($pageId)
    {
        $this->_pageId = $pageId;
    }

    /**
     * @return null|string
     */
    public function getPageId()
    {
        return $this->_pageId;
    }

    /**
     * @return null|string
     */
    public function hasPageId()
    {
        return !empty($this->_pageId);
    }
}
