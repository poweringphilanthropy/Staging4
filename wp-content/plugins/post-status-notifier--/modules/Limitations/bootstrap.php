<?php
/**
 * Premium Limitations module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 356 2014-12-14 17:06:32Z timoreithde $
 */
class Psn_Limitations_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_lmt';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'Limitations';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Enables limitations on notifications';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_lmt';

    /**
     * The module version
     * @var string
     */
    protected $_version = '0.9';

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
     * The module _dependencies
     * @var array
     */
    protected $_dependencies = array('psn_mod_prm');



    /**
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        if (!$this->_pm->getAccess()->isHeartbeat()) {

            if ($this->_pm->getAccess()->isPlugin()) {
                $this->addPluginAdminActions();

                $options = new Psn_Module_Limitations_Options($this->_pm);
                $options->loadOptions();
            }

            if ($this->_pm->getAccess()->isAdmin()) {
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Activation.php';
                require_once $this->getPathinfo()->getRootLib() . 'Installer/Uninstall.php';

                $this->_pm->getBootstrap()->getInstaller()->addActivation(new Psn_Module_Limitations_Installer_Activation());
                $this->_pm->getBootstrap()->getInstaller()->addUninstall(new Psn_Module_Limitations_Installer_Uninstall());
            }
        }

        if ($this->_pm->hasOption('use_limitations')) {
            require_once $this->getPathinfo()->getRootLib() . 'Manager.php';
            $limitationsManager = new Psn_Module_Limitations_Manager($this->_pm, $this);
        }
    }

    public function addPluginAdminActions()
    {
        IfwPsn_Wp_Proxy_Action::add('psn_selftester_activate', array($this, 'addSelftests'));
        IfwPsn_Wp_Proxy_Filter::add('psn_db_patcher_rule_fields', array($this, 'addDbPatcherRuleFields'));
        IfwPsn_Wp_Proxy_Action::add('psn_patch_db', array($this, 'patchDb'));
    }

    /**
     * @param $fields
     * @return mixed
     */
    public function addDbPatcherRuleFields($fields)
    {
        array_push($fields, 'limit_type');
        array_push($fields, 'limit_count');
        return $fields;
    }

    /**
     * Creates the mail templates table if not exists
     */
    public function patchDb()
    {
        $table = new Psn_Module_Limitations_Model_Limitations();
        $table->createTable();
    }

    /**
     * @param IfwPsn_Wp_Plugin_Selftester $selftester
     */
    public function addSelftests(IfwPsn_Wp_Plugin_Selftester $selftester)
    {
        require_once $this->getPathinfo()->getRootLib() . 'Test/LimitationsModel.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/LimitTypeField.php';
        require_once $this->getPathinfo()->getRootLib() . 'Test/LimitCountField.php';

        $selftester->addTestCase(new Psn_Module_Limitations_Test_LimitationsModel());
        $selftester->addTestCase(new Psn_Module_Limitations_Test_LimitCountField());
        $selftester->addTestCase(new Psn_Module_Limitations_Test_LimitTypeField());
    }
}
