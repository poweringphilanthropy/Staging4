<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Limitations.php 352 2014-12-13 19:34:18Z timoreithde $
 * @package
 */

class Psn_Module_Limitations_Service_Limitations implements Psn_Notification_Service_Interface
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var Psn_Model_Rule
     */
    protected $_rule;

    /**
     * @var object|WP_Post
     */
    protected $_post;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
    }

    /**
     * @param Psn_Model_Rule $rule
     * @param WP_Post $post
     */
    public function execute(Psn_Model_Rule $rule, $post)
    {
        $this->_rule = $rule;
        $this->_post = $post;

        if (Psn_Module_Limitations_Mapper::isLimited($rule)) {
            // limitation is activated and the rule matched, so write the match
            Psn_Module_Limitations_Mapper::writeMatch($rule, $post);
        }
    }

}
 