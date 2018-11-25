<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Cli command factory
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Factory.php 153 2013-05-26 11:16:39Z timoreithde $
 * @package  IfwPsn_Wp
 */
class IfwPsn_Wp_Plugin_Cli_Factory
{
    protected function __construct()
    {
    }

    /**
     * Return the command class
     *
     * @param string $command
     * @param array $args
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @throws IfwPsn_Wp_Plugin_Cli_Factory_Exception
     * @return IfwPsn_Wp_Cli_Command_Abstract
     */
    public static function getCommand($command, $args, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $commandPath = IfwPsn_Wp_Autoloader::getClassPath($command);
    
        if ($commandPath == false) {
    
            throw new IfwPsn_Wp_Plugin_Cli_Factory_Exception('Unkown command: '. $command);
    
        } elseif (get_parent_class($command) != 'IfwPsn_Wp_Plugin_Cli_Command_Abstract') {
    
            throw new IfwPsn_Wp_Plugin_Cli_Factory_Exception('Command class must extend IfwPsn_Wp_Plugin_Cli_Command_Abstract');
    
        } else {
    
            return new $command($command, $args, $pm);
        }
    }
}
