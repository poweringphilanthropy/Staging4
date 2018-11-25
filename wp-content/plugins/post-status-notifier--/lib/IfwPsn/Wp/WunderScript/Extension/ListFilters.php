<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: ListFilters.php 295 2014-06-08 19:25:15Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_WunderScript_Extension_ListFilters implements IfwPsn_Wp_WunderScript_Extension_Interface
{
    public function load(IfwPsn_Vendor_Twig_Environment $env)
    {
        require_once IFW_PSN_LIB_ROOT . 'IfwPsn/Vendor/Twig/SimpleFilter.php';

        $env->addFilter( new IfwPsn_Vendor_Twig_SimpleFilter('get_key', array($this, 'getKey')) );
        $env->addFilter( new IfwPsn_Vendor_Twig_SimpleFilter('divide', array($this, 'divide')) );
    }

    /**
     * @param $data
     * @param $key
     * @internal param $string
     * @return mixed
     */
    public function getKey($data, $key)
    {
        $result = ifw_array_search_recursive_key($data, $key);

        if (!empty($result)) {
            return $result;
        }

        return '';
    }

    /**
     * @param $array
     * @param int $segmentCount
     * @return array
     */
    public function divide($array, $segmentCount = 1)
    {
        if (!is_array($array)) {
            return $array;
        }

        $dataCount = count($array);

        if ($dataCount == 0) {
            return $array;
        }

        $segmentLimit = ceil($dataCount / $segmentCount);

        $outputArray = array();

        while($dataCount > $segmentLimit) {
            $outputArray[] = array_splice($array,0,$segmentLimit);
            $dataCount = count($array);
        }

        if ($dataCount > 0) {
            $outputArray[] = $array;
        }

        return $outputArray;
    }
}
 