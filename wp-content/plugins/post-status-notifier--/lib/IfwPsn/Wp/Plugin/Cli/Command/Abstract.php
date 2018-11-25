<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Abstract cli command
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 364 2014-12-15 23:19:12Z timoreithde $
 * @package  IfwPsn_Wp
 */
abstract class IfwPsn_Wp_Plugin_Cli_Command_Abstract
{
    /**
     * @var IfwPsn_Wp_Plugin_Manager
     */
    protected $_pm;
    
    /**
     * @var string
     */
    protected $_command;
    
    /**
     * @var array
     */
    protected $_params;
    
    /**
     * @var array
     */
    protected $_supportedParams = array();
    
    /**
     * @var string
     */
    protected $_executable = 'script';
    
    
    
    /**
     *
     */
    public function __construct ($command, $params, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->_pm = $pm;
        $this->_command = $command;
        $this->_params = $params;
    
        $this->_initParams();
    }

    /**
     * 
     */
    protected function _initParams()
    {
        $this->_params = $this->_prepareParams($this->_params);
        $this->_supportedParams = $this->_fetchSupportedParams();
        $this->_validateSupportedParams();
    }
    
    /**
     * May be overwritten by concrete command
     * 
     * @return array
     */
    protected function _fetchSupportedParams()
    {
        return array();
    }
    
    /**
     * @throws Coach_Cli_Exception_MissingOperand
     */
    protected function _validateSupportedParams()
    {
        $requiredParams = $this->_getSupportedRequiredParams();
    
        foreach ($requiredParams as $reqParam) {
    
            if (!isset($this->_params[$reqParam['name']]) && !isset($this->_params[$reqParam['shortName']])) {
                throw new IfwPsn_Wp_Plugin_Cli_Command_Exception_MissingOperand($this->_fetchUsage());
            }
        }
    }
    
    /**
     *
     */
    protected function _getSupportedRequiredParams()
    {
        $params = array();
        foreach ($this->_supportedParams as $param) {
            if ($param['required'] == true) {
                $params[] = $param;
            }
        }
        return $params;
    }
    
    /**
     * @return multitype:unknown
     */
    protected function _getSupportedOptionalParams()
    {
        $params = array();
        foreach ($this->_supportedParams as $param) {
            if ($param['required'] == false) {
                $params[] = $param;
            }
        }
        return $params;
    }
    
    /**
     *
     */
    protected function _getDefaultUsage()
    {
        $usage = 'Usage: '. $this->_executable . ' ' . $this->_command . ' ';
    
        $usageParams = array();
        $usageParamsDescription = array();
    
        $supportedParams = array_merge($this->_getSupportedRequiredParams(), $this->_getSupportedOptionalParams());
    
        foreach ($supportedParams as $param) {
            $paramUsage = $param['usage'];
            if ($param['required'] == false) {
                $paramUsage = '[' . $paramUsage . ']';
            }
            $usageParams[] = $paramUsage;
    
            if (!empty($param['description'])) {
                $description = '--' . $param['name'];
                if (!empty($param['shortName'])) {
                    $description = '-' . $param['shortName'] . ', ' . $description;
                }
                $description .= ': ' . $param['description'];
                if (($param['required'] == false)) {
                    $description .= ' (optional)';
                }
    
                $usageParamsDescription[] = $description;
            }
        }
    
        $usage .= implode(' ', $usageParams);
    
        if (count($usageParamsDescription) > 0) {
            $usage .= PHP_EOL . PHP_EOL . 'Options:' . PHP_EOL;
            $usage .= implode(PHP_EOL, $usageParamsDescription);
        }
    
        return $usage . PHP_EOL;
    }
    
    /**
     * @param string $paramname
     * @return bool
     */
    protected function _getParam($paramname)
    {
        $result = false;
    
        if (is_array($this->_supportedParams)) {
    
            foreach ($this->_supportedParams as $param) {
    
                if ($paramname == $param['name'] || $paramname == $param['shortName']) {
                    if (isset($this->_params[$param['name']])) {
                        $result = $this->_params[$param['name']];
                    } elseif (isset($this->_params[$param['shortName']])) {
                        $result = $this->_params[$param['shortName']];
                    }
                    break;
                }
            }
        }
    
        return $result;
    }
    
    /**
     * Retrieves the usage output, may be overwritten to customize by command
     */
    protected function _fetchUsage()
    {
        return $this->_getDefaultUsage();
    }

    /**
     * @param $output
     * @param null $foreground
     * @param null $background
     * @param bool $linebreak
     * @return $this
     */
    public function output($output, $foreground = null, $background = null, $linebreak = true)
    {
        if ($linebreak) {
            IfwPsn_Wp_Plugin_Cli_Outputter::outputWithLineBreak($output, $foreground, $background);
        } else {
            IfwPsn_Wp_Plugin_Cli_Outputter::output($output, $foreground, $background);
        }
        return $this;
    }

    /**
     * @param $output
     * @param null $foreground
     * @param null $background
     * @return $this
     */
    public function outputInline($output, $foreground = null, $background = null)
    {
        $this->output($output, $foreground, $background, false);
        return $this;
    }

    /**
     * @param string $status
     */
    public function outputStatusSuccess($status = 'OK')
    {
        $this->outputInline('Status: ')->output($status, 'green');
    }

    /**
     * @param string $status
     */
    public function outputStatusError($status = 'Error')
    {
        $this->outputInline('Status: ')->output($status, 'red');
    }

    /**
     * Prepares command line parameters for use
     * @param array $params
     * @return array
     */
    protected function _prepareParams($params)
    {
        $newparams = array();

        foreach ($params as $param) {
    
            if (!strstr($param, '=')) {

                if (stripos($param, '--') === 0) {
                    $param = substr($param, 2);
                } elseif (stripos($param, '-') === 0) {
                    $param = substr($param, 1);
                }

                $newparams[$param] = true;

            } else {
                $p = explode('=', $param);
    
                $paramKey = $p[0];
                $paramValue = $p[1];
    
                if (stripos($paramKey, '--') === 0) {
                    $paramKey = substr($paramKey, 2);
                } elseif (stripos($paramKey, '-') === 0) {
                    $paramKey = substr($paramKey, 1);
                }
    
                if (stripos($paramValue, '"') === 0) {
                    $paramValue = substr($paramValue, 1);
                }
                if (strrpos($paramValue, '"') === (strlen($paramValue)-1)) {
                    $paramValue = substr($paramValue, 0, -1);
                }
    
                if ($paramValue == 'true') {
                    $newparams[$paramKey] = true;
                } else if ($paramValue == 'false') {
                    $newparams[$paramKey] = false;
                } else if (preg_match('/^[0-9]*$/', $paramValue)) {
                    $newparams[$paramKey] = (int)$paramValue;
                } else {
                    $newparams[$paramKey] = $paramValue;
                }
            }
        }

        return $newparams;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasParam($key)
    {
        $result = null;

        if (is_array($this->_supportedParams)) {

            foreach ($this->_supportedParams as $param) {

                if ($key == $param['name'] || $key == $param['shortName']) {
                    if (isset($this->_params[$param['name']]) || array_key_exists($param['name'], $this->_params)) {
                        $result = true;
                    } elseif (isset($this->_params[$param['shortName']]) || array_key_exists($param['shortName'], $this->_params)) {
                        $result = true;
                    }
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isEmptyParam($key)
    {
        if (!$this->hasParam($key)) {
            return true;
        }

        $value = $this->getParam($key);

        return empty($value);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getParam($key)
    {
        $result = null;

        if (is_array($this->_supportedParams)) {

            foreach ($this->_supportedParams as $param) {

                if ($key == $param['name'] || $key == $param['shortName']) {
                    if (isset($this->_params[$param['name']])) {
                        $result = $this->_params[$param['name']];
                    } elseif (isset($this->_params[$param['shortName']])) {
                        $result = $this->_params[$param['shortName']];
                    }
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return the $_executable
     */
    public function getExecutable()
    {
        return $this->_executable;
    }

    /**
     * @param $executable
     * @internal param string $_executable
     */
    public function setExecutable($executable)
    {
        $this->_executable = $executable;
    }
    
    /**
     * Executes the command
     */
    public abstract function execute();

}
