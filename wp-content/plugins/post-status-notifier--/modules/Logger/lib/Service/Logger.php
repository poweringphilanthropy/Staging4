<?php
/**
 * Logger service
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Logger.php 418 2015-09-18 10:25:48Z timoreithde $
 */ 
class Psn_Module_Logger_Service_Logger implements Psn_Notification_Service_Interface
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
     * @var Psn_Notification_Placeholders
     */
    protected $_replacer;



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
        $this->_replacer = clone $rule->getReplacer();
        $this->_replacer->revertTwigContext();

        if ($this->_pm->hasOption('psn_log_rule_matches')) {
            $this->_writeRuleMatchEntry();
        }
    }

    protected function _writeRuleMatchEntry()
    {
        $info = __('Rule settings', 'psn_log') . "\n";
        $info .= '---------------------------------' . "\n";

        $info .= __('Rule name', 'psn') . ': ' . $this->_rule->get('name') . "\n";
        $info .= __('Post type', 'psn') . ': ' . $this->_rule->get('posttype') . "\n";
        $info .= __('Status before', 'psn') . ': ' . $this->_rule->get('status_before') . "\n";
        $info .= __('Status after', 'psn') . ': ' . $this->_rule->get('status_after') . "\n\n";

        $info .= __('Placeholder details', 'psn_log') . "\n";
        $info .= '---------------------------------' . "\n";

        if ($this->_pm->getOptionsManager()->getOption('psn_log_array_details')) {
            $placeholders = $this->_replacer->getReplacementsFullyLoaded();
        } else {
            $placeholders = $this->_replacer->getReplacements('default', true, true);
        }

        ksort($placeholders);

        foreach ($placeholders as $k => $v) {
            if (is_scalar($v)) {
                if (is_bool($v)) {
                    $info .= $k . ' : ' . $v == true ? 'true' : 'false' . "\n";
                } else {
                    $info .= $k . ' : ' . $v . "\n";
                }
            } elseif (is_array($v) || is_object($v)) {
                if ($this->_pm->getOptionsManager()->getOption('psn_log_array_details')) {
                    $info .= $k . ' : ' . var_export($v, true) . "\n";
                } else {
                    if (is_array($v)) {
                        $info .= $k . ' : Array' . "\n";
                    } elseif (is_object($v)) {
                        $info .= $k . ' : Object' . "\n";
                    }
                }
            }
        }

        $this->_pm->getLogger(Psn_Logger_Bootstrap::LOG_NAME)->info('Rule matched: ' . $this->_rule->get('name'), array(
            'type' => Psn_Logger_Bootstrap::LOG_TYPE_INFO,
            'extra' => $info
        ));
    }

}
