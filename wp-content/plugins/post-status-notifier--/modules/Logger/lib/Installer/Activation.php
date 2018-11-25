<?php
/**
 * Executes on plugin activation
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Activation.php 307 2014-08-25 19:38:39Z timoreithde $
 */
class Psn_Module_Logger_Installer_Activation implements IfwPsn_Wp_Plugin_Installer_ActivationInterface
{
    /** (non-PHPdoc)
     * @see IfwPsn_Wp_Plugin_Installer_ActivationInterface::execute()
     */
    public function execute(IfwPsn_Wp_Plugin_Manager $pm, $networkwide = false)
    {
        $logger = IfwPsn_Wp_Plugin_Logger::create(
            $pm,
            new IfwPsn_Zend_Log_Writer_WpDb($pm, 'Psn_Module_Logger_Model_Log'),
            Psn_Logger_Bootstrap::LOG_NAME
        );

        $logger->install($networkwide);

        $this->_checkFieldTypeExtra($pm, $networkwide);
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param bool $networkwide
     */
    protected function _checkFieldTypeExtra(IfwPsn_Wp_Plugin_Manager $pm, $networkwide = false)
    {
        require_once dirname(__FILE__) . '/LogModelExtraTypeTask.php';

        $task = new Psn_Module_Logger_Installer_LogModelExtraTypeTask($pm);
        $task->execute($networkwide);
    }
}
