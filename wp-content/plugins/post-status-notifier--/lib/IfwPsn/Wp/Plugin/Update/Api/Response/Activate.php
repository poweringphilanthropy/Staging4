<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Activate.php 469 2015-10-06 19:32:28Z timoreithde $
 */
class IfwPsn_Wp_Plugin_Update_Api_Response_Activate extends IfwPsn_Wp_Plugin_Update_Api_Response_Abstract
{
    protected $_multisite = true;

    /**
     * @return mixed
     */
    public function getActivationsLeft()
    {
        return $this->getData('activations_left');
    }

    /**
     * @param mixed $activationsLeft
     */
    public function setActivationsLeft($activationsLeft)
    {
        $this->setData('activations_left', (int)$activationsLeft);
    }

    /**
     * @return mixed
     */
    public function getLicenseLimit()
    {
        return $this->getData('activations_limit');
    }

    /**
     * @param mixed $license_limit
     */
    public function setLicenseLimit($license_limit)
    {
        $this->setData('activations_limit', (int)$license_limit);
    }

    /**
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getData('customer_email');
    }

    /**
     * @param mixed $customer_email
     */
    public function setCustomerEmail($customer_email)
    {
        $this->setData('customer_email', $customer_email);
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->getData('customer_name');
    }

    /**
     * @param mixed $customer_name
     */
    public function setCustomerName($customer_name)
    {
        $this->setData('customer_name', $customer_name);
    }

    /**
     * @return boolean
     */
    public function isMultisite()
    {
        return $this->hasData('multisite') && $this->getData('multisite') === true;
    }

    /**
     * @param boolean $multisite
     */
    public function setMultisite($multisite)
    {
        if (is_bool($multisite)) {
            $this->setData('multisite', $multisite);
        }
    }


}
