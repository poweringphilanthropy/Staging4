<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Parser.php 299 2014-07-06 15:41:08Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_WunderScript_Parser 
{
    /**
     * @var IfwPsn_Wp_WunderScript_Parser
     */
    protected static $_instance;

    /**
     * @var IfwPsn_Wp_WunderScript_Parser
     */
    protected static $_instanceFile;

    /**
     * @var IfwPsn_Vendor_Twig_Environment
     */
    protected $_env;

    /**
     * @var IfwPsn_Wp_Plugin_Logger
     */
    protected $_logger;

    /**
     * @var array
     */
    protected $_extensions = array();


    /**
     * Retrieves the default string loader instance
     *
     * @param array $twigOptions
     * @return IfwPsn_Wp_WunderScript_Parser
     */
    public static function getInstance($twigOptions=array())
    {
        if (self::$_instance === null) {
            require_once dirname(__FILE__) . '/../Tpl.php';
            $options = array_merge(array('twig_loader' => 'String'), $twigOptions);
            $env = IfwPsn_Wp_Tpl::factory($options);
            self::$_instance = new self($env);
        }
        return self::$_instance;
    }

    /**
     * Retrieves the file loader instance
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param array $twigOptions
     * @return IfwPsn_Wp_WunderScript_Parser
     */
    public static function getFileInstance(IfwPsn_Wp_Plugin_Manager $pm, $twigOptions=array())
    {
        if (self::$_instanceFile === null) {
            require_once dirname(__FILE__) . '/../Tpl.php';
            $env = IfwPsn_Wp_Tpl::getFilesytemInstance($pm, $twigOptions);
            self::$_instanceFile = new self($env);
        }
        return self::$_instanceFile;
    }

    /**
     * @param IfwPsn_Vendor_Twig_Environment $env
     */
    protected function __construct(IfwPsn_Vendor_Twig_Environment $env)
    {
        $this->_env = $env;

        $this->_init();
    }

    protected function _init()
    {
        $this->_loadExtensions();
    }

    protected function _loadExtensions()
    {
        $extensionsPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR;

        require_once $extensionsPath . 'TextFilters.php';
        require_once $extensionsPath . 'TextTests.php';
        require_once $extensionsPath . 'ListFilters.php';
        require_once $extensionsPath . 'Globals.php';

        array_push($this->_extensions, new IfwPsn_Wp_WunderScript_Extension_TextFilters());
        array_push($this->_extensions, new IfwPsn_Wp_WunderScript_Extension_TextTests());
        array_push($this->_extensions, new IfwPsn_Wp_WunderScript_Extension_ListFilters());
        array_push($this->_extensions, new IfwPsn_Wp_WunderScript_Extension_Globals());

        foreach ($this->_extensions as $extension) {
            if ($extension instanceof IfwPsn_Wp_WunderScript_Extension_Interface) {
                $extension->load($this->_env);
            }
        }
    }

    /**
     * @param $string
     * @param array $context
     * @param null|callable $exceptionHandler
     * @return string
     */
    public function parse($string, $context = array(), $exceptionHandler = null)
    {
        if (!empty($string)) {

            try {

                if ($this->_env->getLoader() instanceof IfwPsn_Vendor_Twig_Loader_String) {
                    $string = $this->_sanitzeString($string);
                }
                $string = $this->_env->render($string, $context);

            } catch (Exception $e) {
                // invalid filter handling
                if (is_callable($exceptionHandler)) {
                    call_user_func_array($exceptionHandler, array($e));
                } else {
                    if ($this->_logger instanceof IfwPsn_Wp_Plugin_Logger) {
                        $this->_logger->err($e->getMessage());
                    }
                }
            }
        }

        return $string;
    }

    /**
     * @param $string
     * @return string
     */
    protected function _sanitzeString($string)
    {
        $replace = array(
            '/{{(\s+)/' => '{{',
            '/{{&nbsp;/' => '{{',
            '/(\s+)}}/' => '}}',
        );

        $string = preg_replace(array_keys($replace), array_values($replace), $string);

        return $string;
    }

    /**
     * @param \IfwPsn_Wp_Plugin_Logger $logger
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    /**
     * @return \IfwPsn_Wp_Plugin_Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }


}
 