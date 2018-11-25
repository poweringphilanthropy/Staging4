<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: PluginInfoAjax.php 433 2015-06-21 21:39:19Z timoreithde $
 */ 
class IfwPsn_Wp_Plugin_Metabox_PluginInfoAjax extends IfwPsn_Wp_Ajax_Request
{
    public $action = 'load-plugin-info';

    /**
     * Stores info content blocks
     * @var array
     */
    protected $_infoBlocks = array();


    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->action .= '-' . $this->_pm->getAbbrLower();
    }

    /**
     * @return IfwPsn_Wp_Ajax_Response_Abstract
     */
    public function getResponse()
    {
        if ($this->_pm->hasPremium() && $this->_pm->isPremium()) {
            $this->_addPremiumBlock();
        }
        $this->_addConnectBlock();
        $this->_addHelpBlock();

        $tpl = IfwPsn_Wp_Tpl::getInstance($this->_pm);

        $html = '';
        foreach ($this->_infoBlocks as $block) {
            $params = array(
                'label' => $block['label'],
                'content' => $block['content'],
                'iconClass' => $block['iconClass'],
            );
            $html .= $tpl->render('metabox_plugininfo_block.html.twig', $params);
        }

        $html .= '<p class="ifw-made-with-heart">This plugin was made with <img src="'. $this->_pm->getEnv()->getSkinUrl().
            'icons/heart.png" /> by <a href="http://www.ifeelweb.de/" target="_blank">ifeelweb.de</a></p>';
        $success = true;

        $response = new IfwPsn_Wp_Ajax_Response_Json($success);
        $response->addData('html', $html);

        return $response;
    }


    /**
     * Adds a content block
     *
     * @param $id
     * @param string $label
     * @param string $content
     * @param string $iconClass
     */
    public function addBlock($id, $label, $content, $iconClass)
    {
        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_plugininfo_before_'. $id, $this);

        $this->_infoBlocks[] = array(
            'id' => $id,
            'label' => $label,
            'content' => $content,
            'iconClass' => $iconClass
        );

        IfwPsn_Wp_Proxy_Action::doAction($this->_pm->getAbbrLower() . '_plugininfo_after_'. $id, $this);
    }

    protected function _addPremiumBlock()
    {
        $content = __('You are using the premium version of this plugin.', 'ifw');

        $content = strtr($content, array(
            'target="_blank"' => 'target="_blank" class="ifw-external-link"',
        ));

        $this->addBlock('premium',
            __('Premium', 'ifw'),
            '<br>' . $content,
            'premium');
    }

    protected function _addConnectBlock()
    {
        $homepage = $this->_pm->getEnv()->getHomepage();
        $premiumUrl = $this->_pm->getConfig()->plugin->premiumUrl;

        $content = '';
        $content .= sprintf(__('Visit the <a href="%s" target="_blank">plugin homepage</a>', 'ifw'), $homepage);

        if (!empty($premiumUrl) && $premiumUrl != $homepage) {
            $content .= '<br>' . sprintf(__('Visit the <a href="%s" target="_blank">premium homepage</a> for the latest news.', 'ifw'), $premiumUrl);
        }

        $content .= '<br><a href="https://twitter.com/ifeelwebde" target="_blank">@ifeelweb ' . __('on Twitter', 'ifw') . '</a>';

        $this->addBlock('connect',
            __('Connect', 'ifw'),
            '<br>' . $content,
            'connect');
    }

    protected function _addHelpBlock()
    {
        $content = '';

        if (!empty($this->_pm->getConfig()->plugin->docUrl)) {
            $content .= sprintf(__('Read the <a href="%s" target="_blank">plugin documentation</a>', 'ifw'), $this->_pm->getConfig()->plugin->docUrl) . '<br>';
        }
        if (!empty($this->_pm->getConfig()->plugin->faqUrl)) {
            $content .= sprintf(__('Check the <a href="%s" target="_blank">FAQ</a>', 'ifw'), $this->_pm->getConfig()->plugin->faqUrl) . '<br>';
        }

        $content = strtr($content, array(
            'target="_blank"' => 'target="_blank" class="ifw-external-link"',
        ));

        $this->addBlock('help',
            __('Need help?', 'ifw'),
            '<br>' . $content,
            'help');
    }
}
