<?php
/**
 * Premium module
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: bootstrap.php 433 2015-10-30 17:31:36Z timoreithde $
 */
class Psn_Premium_Bootstrap extends IfwPsn_Wp_Module_Bootstrap_Abstract
{
    /**
     * The module ID
     * @var string
     */
    protected $_id = 'psn_mod_prm';

    /**
     * The module name
     * @var string
     */
    protected $_name = 'Premium';

    /**
     * The module description
     * @var string
     */
    protected $_description = 'Activates premium version';

    /**
     * The module text domain
     * @var string
     */
    protected $_textDomain = 'psn_prm';

    /**
     * The module version
     * @var string
     */
    protected $_version = '1.1';

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
    protected $_dependencies = array();



    /**
     * @see IfwPsn_Wp_Module_Bootstrap_Abstract::bootstrap()
     */
    public function bootstrap()
    {
        $this->addGlobalCallbacks();

        if ($this->_pm->getAccess()->isPlugin()) {
            $this->addPluginCallbacks();
        }

        if ($this->_pm->getAccess()->isAdmin()) {
            require_once $this->getPathinfo()->getRootLib() . 'Options.php';
            $options = new Psn_Module_Premium_Options($this->_pm, $this);
            $options->load();

            add_filter('psn_license_code', array($this, 'getEnvatoLicenseCode'));

            IfwPsn_Wp_Proxy_Filter::addNetworkAdminPluginActionLinks($this->_pm, array($this, 'addNetworkAdminPluginActionLinks'));
        }

        if (!IfwPsn_Wp_Proxy_Blog::isMultisite() && $this->_pm->getAccess()->isPlugin()) {
            IfwPsn_Wp_Proxy_Action::addPlugin($this->_pm, 'after_admin_navigation_service', array($this, 'addLicenseTab'));
        } elseif (IfwPsn_Wp_Proxy_Blog::isMultisite()) {
            add_action( 'network_admin_menu', array($this, 'registerNetworkSettingsPage') );
        }

        require_once $this->getPathinfo()->getRootLib() . 'PostSubmitboxHandler.php';
        new Psn_Module_Premium_PostSubmitboxHandler($this);
    }

    public function addGlobalCallbacks()
    {
        IfwPsn_Wp_Proxy_Filter::addPlugin($this->_pm, 'is_premium', array($this, 'setPremium'));

        add_action('psn_add_feature', array($this, 'addFeatures'));
    }

    /**
     * Load premium features
     *
     * @param Psn_Feature_Loader $loader
     */
    public function addFeatures(Psn_Feature_Loader $loader)
    {
        require_once $this->getPathinfo()->getRootLib() . '/Mandrill/Feature.php';
        $loader->addFeature(new Psn_Module_Premium_Mandrill_Feature($this->_pm, $this));

        require_once $this->getPathinfo()->getRootLib() . '/Features/Rules.php';
        $loader->addFeature(new Psn_Module_Premium_Features_Rules($this->_pm, $this));
    }

    public function addPluginCallbacks()
    {
        IfwPsn_Wp_Proxy_Action::add('psn-service-metabox-col3', array($this, 'addServiceCol3Metabox'));
        IfwPsn_Wp_Proxy_Action::add('PsnServiceController_init', array($this, 'initPsnController'));
        IfwPsn_Wp_Proxy_Action::add('PsnOptionsController_init', array($this, 'initPsnController'));
    }

    /**
     * @param $navigation
     */
    public function addLicenseTab(IfwPsn_Vendor_Zend_Navigation $navigation)
    {
        $page = new IfwPsn_Zend_Navigation_Page_WpMvc(array(
            'label' => __('License', 'psn_prm'),
            'controller' => 'license',
            'action' => 'index',
            'module' => strtolower($this->_pathinfo->getDirname()),
            'page' => $this->_pm->getPathinfo()->getDirname(),
            'route' => 'requestVars'
        ));
        $navigation->addPage($page);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Metabox_Container $container
     */
    public function addServiceCol3Metabox(IfwPsn_Wp_Plugin_Metabox_Container $container)
    {
        require_once $this->getPathinfo()->getRootLib() . '/Metabox/ModuleFrontend.php';

        $container->addMetabox(new Psn_Module_Premium_Metabox_ModuleFrontend($this->_pm));
    }

    /**
     * @param IfwPsn_Zend_Controller_Default $controller
     */
    public function initPsnController(IfwPsn_Zend_Controller_Default $controller)
    {
        IfwPsn_Wp_Proxy_Style::loadAdmin('psn-service-prm', $this->getEnv()->getUrlCss() . 'admin.css');
    }

    /**
     * Sets plugin to premium
     * @param $premium
     * @return bool
     */
    public function setPremium($premium)
    {
        return true;
    }

    /**
     * @param $max
     * @return int
     */
    public function unsetMaxRules($max)
    {
        return 0;
    }

    /**
     * @param $license_code
     * @return string
     */
    public function getEnvatoLicenseCode($license_code)
    {
        return Psn_Module_Premium_License::getInstance($this->_pm)->getLicense();
    }

    /**
     *
     */
    public function addNetworkAdminPluginActionLinks($links, $file)
    {
        $links[] = '<a href="' . network_admin_url('settings.php?page=post-status-notifier') . '">' . __('Settings', 'psn') . '</a>';
        return $links;
    }

    public function registerNetworkSettingsPage()
    {
        $networkSettings = Psn_Module_Premium_Admin_NetworkSettings::getInstance($this->_pm);
        $networkSettings->registerPage();
    }
}