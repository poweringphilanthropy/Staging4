<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Twig extension for localized date filter
 * Uses strftime (http://www.php.net/manual/de/function.strftime.php) format syntax
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: DateLocale.php 233 2014-03-17 23:46:37Z timoreithde $
 */
require_once dirname(__FILE__) . '/../../../Vendor/Twig/ExtensionInterface.php';
require_once dirname(__FILE__) . '/../../../Vendor/Twig/Extension.php';

class IfwPsn_Wp_Tpl_Extension_DateLocale extends IfwPsn_Vendor_Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'date_locale';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        require_once dirname(__FILE__) . '/../../../Vendor/Twig/Filter/Method.php';

        return array(
            'date_locale' => new IfwPsn_Vendor_Twig_Filter_Method($this, 'dateLocale'),
        );
    }

    /**
     * @param string $date
     * @param string $format
     * @param null $locale
     * @return string
     */
    public function dateLocale($date, $format, $locale=null)
    {
        if ($locale === null && defined('WPLANG')) {
            $locale = WPLANG;
        }

        if (!empty($locale)) {
            setlocale(LC_TIME, $locale);
        }

        return strftime($format, strtotime($date));
    }
}
