<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Manager.php 353 2014-12-14 16:55:04Z timoreithde $
 * @package
 */

class Psn_Module_Limitations_Manager
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var IfwPsn_Wp_Module_Bootstrap_Abstract
     */
    protected $_module;


    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm, IfwPsn_Wp_Module_Bootstrap_Abstract $module)
    {
        $this->_pm = $pm;
        $this->_module = $module;

        $this->_init();
    }

    protected function _init()
    {
        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_load_services', array($this, 'addService'));
        IfwPsn_Wp_Proxy_Filter::add('psn_do_match', array($this, 'filterDoMatch'), 10, 2);
        IfwPsn_Wp_Proxy_Action::add('psn_after_admin_navigation_log', array($this, 'addNav'));
        IfwPsn_Wp_Proxy_Action::add('psn_rule_form', array($this, 'extendRuleForm'));
        IfwPsn_Wp_Proxy_Filter::add('psn_rule_form_defaults', array($this, 'filterFormDefaults'));
    }

    /**
     * @param Psn_Notification_Manager $notificationManager
     */
    public function addService(Psn_Notification_Manager $notificationManager)
    {
        require_once $this->_module->getPathinfo()->getRootLib() . 'Service/Limitations.php';
        $notificationManager->addService(new Psn_Module_Limitations_Service_Limitations($this->_pm));
    }

    /**
     * @param $doMatch
     * @param array $data
     * @return bool
     */
    public function filterDoMatch($doMatch, array $data)
    {
        $rule = $data['rule'];
        $post = $data['post'];
        $status_before = $data['status_before'];
        $status_after = $data['status_after'];

        if (Psn_Module_Limitations_Mapper::isLimitReached($rule, $post)) {
            IfwPsn_Wp_Proxy_Action::doAction('psn_limitation_reached', $rule, $post);
            $doMatch = false;
        }

        return $doMatch;
    }

    /**
     * @param $navigation
     */
    public function addNav(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Zend/Navigation/Page/WpMvc.php';

        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('Limitations', 'psn_lmt'),
            'controller' => 'limitations',
            'action' => 'index',
            'module' => strtolower($this->_module->getPathinfo()->getDirname()),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $navigation->addPage($page);

        IfwPsn_Wp_Proxy_Action::doAction('psn_after_admin_navigation_limitations', $navigation);
    }

    /**
     * @param $form
     */
    public function extendRuleForm(IfwPsn_Zend_Form $form)
    {
        $limitType = $form->createElement('select', 'limit_type');

        $limitType
            ->setLabel(__('Limitation type', 'psn_lmt'))
            ->setDecorators($form->getFieldDecorators())
            ->setDescription(__('Select a type if you want to limit how often this rule may match a post status transition per post.', 'psn_lmt'))
            ->setFilters(array('StringTrim', 'StripTags'))
            ->addMultiOptions(array(
                '0' => '-- ' . __('None', 'psn') . ' --',
                Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE => Psn_Module_Limitations_Mapper::getLimitTypeLabel(Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE),
                Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE_STATUS_AFTER => Psn_Module_Limitations_Mapper::getLimitTypeLabel(Psn_Module_Limitations_Mapper::LIMIT_TYPE_POST_RULE_STATUS_AFTER),
            ))
            ->setOrder(130);
        $form->addElement($limitType);

        $form->addElement('text', 'limit_count', array(
            'label'          => __('Limit count', 'psn_lmt'),
            'description'    => __('Set the limit count. Numeric. Default: 1', 'psn_lmt'),
            'filters'        => array('StringTrim', 'StripTags'),
            'maxlength'      => 5,
            'decorators'     => $form->getFieldDecorators(),
            'order'          => 131
        ));

    }

    /**
     * @param $values
     */
    public function filterFormDefaults($values)
    {
        if (isset($values['limit_count']) && empty($values['limit_count'])) {
            $values['limit_count'] = null;
        }
        return $values;
    }
}
 