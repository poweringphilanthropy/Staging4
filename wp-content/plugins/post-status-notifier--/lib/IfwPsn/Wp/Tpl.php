<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Template engine factory/singleton to retrieve twig environment
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Tpl.php 412 2015-04-02 22:11:08Z timoreithde $
 */
class IfwPsn_Wp_Tpl
{
    /**
     * Instance store
     * @var array
     */
    protected static $_instances = array();

    /**
     * 
     * @var array
     */
    protected static $_stringLoaderInstances = array();



    /**
     * Retrieves a Twig environment with filesystem loader
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param array $options
     * @return IfwPsn_Vendor_Twig_Environment
     */
    public static function getInstance(IfwPsn_Wp_Plugin_Manager $pm, $options=array())
    {
        return self::getFilesytemInstance($pm, $options);
    }

    /**
     *
     * @param array $options
     * @throws IfwPsn_Wp_Exception
     * @return IfwPsn_Vendor_Twig_Environment
     */
    public static function factory($options=array())
    {
        if (!isset($options['twig_loader']) || empty($options['twig_loader'])) {
            $options['twig_loader'] = 'Filesystem';
        }
        
        $twigOptions = array();
        if (isset($options['twig_options']) && is_array($options['twig_options'])) {
            $twigOptions = $options['twig_options'];
        }

        $twigOptions['debug'] = true;
       
        switch ($options['twig_loader']) {
            
            case 'String':
                require_once dirname(__FILE__) . '/../Vendor/Twig/Loader/String.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Extension/StringLoader.php';

                $tpl = new IfwPsn_Vendor_Twig_Environment(new IfwPsn_Vendor_Twig_Loader_String(), $twigOptions);
                $tpl->addExtension(new IfwPsn_Vendor_Twig_Extension_StringLoader());
                break;
                
            case 'Filesystem':
            default:
                if (!isset($options['plugin_manager']) || !($options['plugin_manager'] instanceof IfwPsn_Wp_Plugin_Manager)) {
                    throw new IfwPsn_Wp_Exception('Filesystem loader requires instance of IfwPsn_Wp_Plugin_Manager');
                }
                $pm = $options['plugin_manager'];
                require_once dirname(__FILE__) . '/../Vendor/Twig/LoaderInterface.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/ExistsLoaderInterface.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Loader/Filesystem.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/ExtensionInterface.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Extension.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Extension/Core.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Extension/Escaper.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Extension/Optimizer.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Extension/Staging.php';
                require_once dirname(__FILE__) . '/../Vendor/Twig/Environment.php';

                $loader = new IfwPsn_Vendor_Twig_Loader_Filesystem($pm->getPathinfo()->getRootTpl());
                $loader->addPath(dirname(__FILE__) . '/Tpl/built-in');
                $tpl = new IfwPsn_Vendor_Twig_Environment($loader, $twigOptions);
        }

        // load extensions
        require_once dirname(__FILE__) . '/Tpl/Text.php';
        require_once dirname(__FILE__) . '/Tpl/Extension/DateLocale.php';
        require_once dirname(__FILE__) . '/Tpl/Extension/Text.php';
        require_once dirname(__FILE__) . '/../Vendor/Twig/Extension/Debug.php';

        $tpl->addGlobal('text', new IfwPsn_Wp_Tpl_Text());
        $tpl->addExtension(new IfwPsn_Wp_Tpl_Extension_DateLocale());
        $tpl->addExtension(new IfwPsn_Wp_Tpl_Extension_Text());
        $tpl->addExtension(new IfwPsn_Wp_Tpl_Extension_PrintR());
        $tpl->addExtension(new IfwPsn_Vendor_Twig_Extension_Debug());

        return $tpl;
    }
    
    /**
     * Retrieves a Twig environment with string loader
     * 
     * 
     * @param array $twigOptions
     * @return IfwPsn_Vendor_Twig_Environment
     */
    public static function getStringLoaderInstance($twigOptions=array(), $instanceToken = null)
    {
        $options = array('twig_loader' => 'String');
        if (!empty($twigOptions) && is_array($twigOptions)) {
            $options['twig_options'] = $twigOptions;
        }
        if (is_null($instanceToken)) {
            $instanceToken = $options['twig_loader'];
        }

        if (!isset(self::$_stringLoaderInstances[$instanceToken])) {
            self::$_stringLoaderInstances[$instanceToken] = self::factory($options);
        }
        return self::$_stringLoaderInstances[$instanceToken];
    }

    /**
     * Retrieves a Twig environment with filesystem loader
     *
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @param array $twigOptions
     * @param null $instanceToken
     * @return IfwPsn_Vendor_Twig_Environment
     * @throws IfwPsn_Wp_Exception
     */
    public static function getFilesytemInstance(IfwPsn_Wp_Plugin_Manager $pm, $twigOptions=array(), $instanceToken = null)
    {
        $options = array(
            'twig_loader' => 'Filesystem',
            'plugin_manager' => $pm
        );
        if (!empty($twigOptions) && is_array($twigOptions)) {
            $options['twig_options'] = $twigOptions;
        }

        if (is_null($instanceToken)) {
            $instanceToken = $options['twig_loader'];
        }
        if (!isset(self::$_instances[$pm->getAbbr()][$instanceToken])) {
            self::$_instances[$pm->getAbbr()][$instanceToken] = self::factory($options);
        }
        
        return self::$_instances[$pm->getAbbr()][$instanceToken];
    }
    
    /**
     * Applies twig filters to string
     * 
     * @param string $string
     * @param string $filters
     * @param null|IfwPsn_Wp_Plugin_Logger $logger
     * @return string
     */
    public static function applyFilters($string, $filters, $logger=null)
    {
        if (!empty($filters)) {
            try {
                $tpl = self::getStringLoaderInstance();
                $string = $tpl->render('{{ value|'. $filters .' }}', array('value' => $string));
            } catch (Exception $e) {
                // invalid filter handling
                if ($logger instanceof IfwPsn_Wp_Plugin_Logger) {
                    $logger->err($e->getMessage());
                }
            }
        }
        
        return $string;
    }

    /**
     * Applies twig filters to string
     *
     * @param string $string
     * @param null|IfwPsn_Wp_Plugin_Logger $logger
     * @internal param string $filters
     * @return string
     */
    public static function renderString($string, $logger=null)
    {
        if (!empty($string)) {
            try {
                $tpl = self::getStringLoaderInstance();
                $string = strtr($string, array(
                    '{{ ' => '{{',
                    ' }}' => '}}'
                ));
                $string = $tpl->render($string);
            } catch (Exception $e) {
                // invalid filter handling
                if ($logger instanceof IfwPsn_Wp_Plugin_Logger) {
                    $logger->err($e->getMessage());
                }
            }
        }

        return $string;
    }
}
