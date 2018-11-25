<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Handles the initial loading procedure and return the loader object
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Loader.php 395 2015-02-19 22:33:40Z timoreithde $
 * @package   IfwPsn_Wp_Plugin
 */ 
class IfwPsn_Wp_Plugin_Loader
{
    /**
     * @param $pathinfo
     * @param null $loader
     * @return \IfwPsn_Wp_Plugin_Loader_Default|null|object
     * @throws IfwPsn_Wp_Plugin_Loader_Exception
     */
    public static function load($pathinfo, $loader = null)
    {
        // Exit if accessed directly
        if ( ! defined( 'ABSPATH' ) ) {
            die('Invalid access.');
        }

        if (is_object($loader) && !($loader instanceof IfwPsn_Wp_Plugin_Loader_Abstract)) {
            require_once dirname(__FILE__) . '/Loader/Exception.php';
            throw new IfwPsn_Wp_Plugin_Loader_Exception('Invalid loader object provided. Loader must extend IfwPsn_Wp_Plugin_Loader_Abstract.');
        }

        try {

            require_once dirname(__FILE__) . '/../HelperFunctions.php';

            if (empty($loader)) {
                require_once dirname(__FILE__) . '/Loader/Default.php';
                $loader = new IfwPsn_Wp_Plugin_Loader_Default($pathinfo);
            }

            // load the plugin
            $loader->load();

            $pm = $loader->getPluginManager();

            if ($pm instanceof IfwPsn_Wp_Plugin_Manager) {
                $pm->getLogger()->logPrefixed('Bootstrapping plugin.');
                $pm->bootstrap();

                if ($loader->getEnv()->isCli() && defined('IFW_WP_CLI_CMD') && strpos(IFW_WP_CLI_CMD, $pm->getAbbr()) === 0) {
                    self::_loadCliCommand(IFW_WP_CLI_CMD, $pm);
                }
            }

        } catch (IfwPsn_Wp_Plugin_Loader_Exception $e) {
            $error = 'Error while loading plugin: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'General error: ' . $e->getMessage();
        }

        if (isset($error)) {
            self::_handleError($error, $loader);
        }

        return $loader;
    }

    /**
     * @param IfwPsn_Wp_Plugin_Manager $pm
     */
    protected static function _loadCliCommand($command, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $headline = '### %s %s CLI ###' . PHP_EOL . PHP_EOL;
        printf($headline, $pm->getEnv()->getName(), $pm->getEnv()->getVersion());

        $args = $_SERVER['argv'];
        $args = array_slice($args, 2);
        $executable = 'script';

        if ($pm->getEnv()->isWindows()) {
            $executable .= '.bat';
        } else {
            $executable .= '.sh';
        }

        try {
            // try to execute a command
            $cliCommand = IfwPsn_Wp_Plugin_Cli_Factory::getCommand($command, $args, $pm);
            $cliCommand->setExecutable($executable);

            // load the cli command after WP is completely loaded
            add_action('wp_loaded', array($cliCommand, 'execute'));

        } catch (IfwPsn_Wp_Plugin_Cli_Factory_Exception $e) {

            echo 'Initialization error: ' . $e->getMessage();

        } catch (IfwPsn_Wp_Plugin_Cli_Command_Exception_MissingOperand $e) {
            // fetch MissingOperand exception
            echo $executable . ' ' . $command . ': missing operand';
            echo PHP_EOL;
            echo $e->getMessage();

        } catch (IfwPsn_Wp_Plugin_Cli_Exception $e) {
            // fetch generell cli exception
            echo $e->getMessage();
        }
    }

    /**
     * @param $error
     * @param IfwPsn_Wp_Plugin_Loader_Abstract $loader
     */
    protected static function _handleError($error, IfwPsn_Wp_Plugin_Loader_Abstract $loader)
    {
        $logger = $loader->getLogger();
        if ($logger instanceof IfwPsn_Wp_Plugin_Logger) {
            $logger->err($error);
        }
        ifw_log_error($error);
    }
}
