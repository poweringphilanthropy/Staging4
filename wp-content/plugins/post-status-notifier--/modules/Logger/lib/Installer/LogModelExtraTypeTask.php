<?php
/**
 * Executes on plugin activation
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: LogModelExtraTypeTask.php 292 2014-08-13 20:36:13Z timoreithde $
 */
class Psn_Module_Logger_Installer_LogModelExtraTypeTask extends IfwPsn_Wp_Network_Task
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;

    /**
     * @var Psn_Module_Logger_Test_LogModelExtraType
     */
    protected $_dbTest;



    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    public function __construct(IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->_dbTest = new Psn_Module_Logger_Test_LogModelExtraType();
    }

    /**
     * The task to be executed must be implemented by the concrete class
     * @return mixed
     */
    protected function _execute()
    {
        $this->_dbTest->execute($this->_pm);

        if ($this->_dbTest->getResult() === false) {
            $this->_dbTest->handleError($this->_pm);
        }
    }

}
