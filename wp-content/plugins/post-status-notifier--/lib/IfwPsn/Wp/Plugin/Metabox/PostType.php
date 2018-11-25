<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: PostType.php 233 2014-03-17 23:46:37Z timoreithde $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

abstract class IfwPsn_Wp_Plugin_Metabox_PostType extends IfwPsn_Wp_Plugin_Metabox_Abstract
{
    /**
     * @var string
     */
    protected $_postType;

    /**
     * @var string
     */
    protected $_context;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $postType
     * @param $context
     * @internal param \IfwPsn_Wp_Plugin_Manager $pm
     */
    function __construct($postType, $context)
    {
        $this->_postType = $postType;
        $this->_context = $context;
        $this->_id = $this->_initId();
        $this->_title = $this->_initTitle();
        $this->_priority = $this->_initPriority();

        $this->_init();
    }

    protected function _init()
    {
        add_meta_box(
            $this->getId(),
            $this->getTitle(),
            array($this, 'render'),
            $this->_postType,
            $this->_context,
            $this->getPriority()
        );
    }
}
