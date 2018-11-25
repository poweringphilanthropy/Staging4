<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Plugin installer
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Installer.php 456 2015-08-21 21:29:41Z timoreithde $
 * @package   IfwPsn_Wp_Plugin
 */
class IfwPsn_Wp_Plugin_Installer
{
    /**
     * Instance store
     * @var array
     */
    public static $_instances = array();
    
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var array
     */
    protected $_activation = array();

    /**
     * @var array
     */
    protected $_deactivation = array();

    /**
     * @var array
     */
    protected static $_uninstall = array();



    /**
     * Retrieves singleton IfwPsn_Wp_Plugin_Admin object
     * 
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @return IfwPsn_Wp_Plugin_Installer
    */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm)
    {
        if (!isset(self::$_instances[$pm->getAbbr()])) {
            self::$_instances[$pm->getAbbr()] = new self($pm);
        }
        return self::$_instances[$pm->getAbbr()];
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->_initActivation();
        $this->_initDeactivation();
        $this->_initUninstall();
    }

    protected function _initActivation()
    {
        $this->registerActivation();

        ifw_raise_memory_limit();

        // add default activation commands
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Installer/Command/ActivationPresentVersion.php';

        $this->addActivation(new IfwPsn_Wp_Plugin_Installer_Command_ActivationPresentVersion());
    }

    protected function _initDeactivation()
    {
        $this->registerDeactivation();
    }

    protected function _initUninstall()
    {
        self::$_uninstall[$this->_pm->getPathinfo()->getFilenamePath()] = array();
        $this->registerUninstall();

        ifw_raise_memory_limit();

        // add default uninstall commands
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Installer/Command/UninstallDeleteLog.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Installer/Command/UninstallResetOptions.php';
        require_once $this->_pm->getPathinfo()->getRootLib() . 'IfwPsn/Wp/Plugin/Installer/Command/UninstallRemoveHooks.php';

        $this->addUninstall(new IfwPsn_Wp_Plugin_Installer_Command_UninstallDeleteLog());
        $this->addUninstall(new IfwPsn_Wp_Plugin_Installer_Command_UninstallResetOptions());
        $this->addUninstall(new IfwPsn_Wp_Plugin_Installer_Command_UninstallRemoveHooks());
    }

    /**
     * Add the register_activation_hook
     */
    public function registerActivation()
    {
        register_activation_hook($this->_pm->getPathinfo()->getFilenamePath(), array($this, 'activate'));
    }
    
    /**
     * 
     * @param IfwPsn_Wp_Plugin_Installer_ActivationInterface $activation
     */
    public function addActivation(IfwPsn_Wp_Plugin_Installer_ActivationInterface $activation)
    {
        array_push($this->_activation, $activation);
    }

    /**
     * Loop over all added activation objects
     */
    public function activate($networkwide)
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        /**
         * @var $activation IfwPsn_Wp_Plugin_Installer_ActivationInterface
         */
        foreach ($this->_activation as $activation) {
            $activation->execute($this->_pm, $networkwide);
        }
    }

    /**
     * Add the register_activation_hook
     */
    public function registerDeactivation()
    {
        register_deactivation_hook($this->_pm->getPathinfo()->getFilenamePath(), array($this, 'deactivate'));
    }

    /**
     * 
     * @param IfwPsn_Wp_Plugin_Installer_DeactivationInterface $deactivation
     */
    public function addDeactivation(IfwPsn_Wp_Plugin_Installer_DeactivationInterface $deactivation)
    {
        array_push($this->_deactivation, $deactivation);
    }

    /**
     * Loop over all added deactivation objects
     */
    public function deactivate($networkwide)
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        /**
         * @var $activaion IfwPsn_Wp_Plugin_Installer_DeactivationInterface
         */
        foreach ($this->_deactivation as $deactivaion) {
            $deactivaion->execute($this->_pm, $networkwide);
        }
    }

    /**
     *
     */
    public function registerUninstall()
    {
        register_uninstall_hook($this->_pm->getPathinfo()->getFilenamePath(), 'IfwPsn_Wp_Plugin_Installer::uninstall');
    }
    
    /**
     * 
     * @param IfwPsn_Wp_Plugin_Installer_UninstallInterface $uninstall
     */
    public function addUninstall(IfwPsn_Wp_Plugin_Installer_UninstallInterface $uninstall)
    {
        array_push(self::$_uninstall[$this->_pm->getPathinfo()->getFilenamePath()], $uninstall);
    }

    /**
     * @internal param \IfwPsn_Wp_Plugin_Installer_UninstallInterface $uninstall
     */
    public static function uninstall()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        $checked = array_values($_GET['checked']);
        $filenamePath = array_shift($checked);
        $pm = IfwPsn_Wp_Plugin_Manager::getInstanceFromFilenamePath($filenamePath);

        foreach(self::$_uninstall[$filenamePath] as $uninstall) {
            call_user_func(get_class($uninstall) . '::execute', $pm);
        }
    }
}