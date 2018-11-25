<?php
/**
 * Premium TO loop module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 405 2015-08-24 22:17:41Z timoreithde $
 */
class Psn_ToLoop_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_toloop';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'One Email per TO';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Adds the option to send one email per TO recipient disregarding CC and BCC';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_tol';

    /**
     * The module version
     * @var string
     */
    protected $_version = '1.0';

    /**
     * The module author
     * @var string
     */
    protected $_author = 'Timo';

    /**
     * The author's homepage
     * @var string
     */
    protected $_authorHomepage = 'http://www.ifeelweb.de/';

    /**
     * The module homepage
     * @var string
     */
    protected $_homepage = 'http://www.ifeelweb.de/wp-plugins/post-status-notifier/';

    /**
     * The module dependencies
     * @var array
     */
    protected $_dependencies = array('psn_mod_prm');



    /**
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        if ($this->_pm->getAccess()->isPlugin()) {

            $this->_addPluginAdminActions();
            $this->addOptions();
        }

        require_once $this->getPathinfo()->getRootLib() . 'ToLoopHandler.php';
        new Psn_Module_ToLoop_ToLoopHandler($this->_pm);
    }

    protected function addOptions()
    {
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Options/Field/Text.php';

        $this->_pm->getOptionsManager()->addGeneralOption(new IfwPsn_Wp_Options_Field_Text(
            'psn_to_loop_timelimit',
            __('TO loop timeout', 'psn_tol'),
            sprintf( __('If you are using the "One email per TO recipient" option, you may want to adjust the PHP maximum execution time limit value here. In seconds. It will only be used before the TO loop starts. Has no effect if PHP safe mode is active. If set to 0 (zero), no time limit is imposed. Use with caution! Default is 30 depending on your server configuration. See <a href="%s" target="_blank">PHP manual</a>', 'psn_tol'),
                'http://de3.php.net/manual/en/function.set-time-limit.php'), array(
                'sanitizer' => 'number'
            )
        ));
    }

    protected function _addPluginAdminActions()
    {
        // extend the admin form
        IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'db_patcher_rule_fields', array($this, 'addDbPatcherRuleFields'));

        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'rule_form', array($this, 'extendForm'));

        IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'selftester_activate', array($this, 'addSelftests'));
    }

    /**
     * @param $fields
     * @return mixed
     */
    public function addDbPatcherRuleFields($fields)
    {
        array_push($fields, 'to_loop');
        return $fields;
    }

    /**
     * @param $form
     */
    public function extendForm(IfwPsn_Zend_Form $form)
    {
        $toLoop = $form->createElement('checkbox', 'to_loop');
        $toLoop->setLabel(__('One email per TO recipient', 'psn_tol'))
            ->setDecorators($form->getFieldDecorators())
            ->setDescription(__('If activated, a single email will be sent to each TO recipient disregarding the CC and BCC settings.', 'psn_tol'))
            ->setChecked(false)
            ->setCheckedValue(1)
            ->setOrder(69)
        ;
        $form->addElement($toLoop);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Test/ToLoopField.php';

        $selftester->addTestCase(new Psn_Module_ToLoop_Test_ToLoopField());
    }

}
