<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Metabox container
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Container.php 303 2014-07-27 08:42:06Z timoreithde $
 * @package  IfwPsn_Wp_Plugin_Admin_Menu_Metabox
 */
class IfwPsn_Wp_Plugin_Metabox_Container
{
    /**
     * @var
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_screen;
    
    /**
     * @var string
     */
    protected $_context = 'advanced';
    
    /**
     * @var array
     */
    protected $_metaboxes = array();


    /**
     * @param $id
     * @param string $screen
     * @param string $context
     */
    function __construct ($id, $screen, $context = null)
    {
        $this->_id = $id;

        $this->_screen = $screen;

        if ($context != null) {
            $this->_context = $context;
        }
    }
    
    /**
     * Adds a metabox to a container
     * 
     * @param IfwPsn_Wp_Plugin_Metabox_Abstract $metabox
     */
    public function addMetabox(IfwPsn_Wp_Plugin_Metabox_Abstract $metabox)
    {
        if ($metabox instanceof IfwPsn_Wp_Plugin_Metabox_Abstract) {
            $this->_metaboxes[] = $metabox;
            
            add_meta_box(
                $metabox->getId(),
                $metabox->getTitle(),
                array($metabox, 'render'),
                $this->_screen,
                $this->_context,
                $metabox->getPriority()
            ); 
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getScreen()
    {
        return $this->_screen;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * @return array
     */
    public function getMetaboxes()
    {
        return $this->_metaboxes;
    }

    /**
     * @return bool
     */
    public function hasMetaboxes()
    {
        return count($this->_metaboxes) > 0;
    }
    
    /**
     * Renders the container
     */
    public function render()
    {
        if ($this->hasMetaboxes()) {
            do_meta_boxes($this->_screen, $this->_context, '');
        } else {
            echo '<div id="'. $this->_id .'-sortables" class="meta-box-sortables ui-sortable empty-container"></div>';
        }
    }
}
