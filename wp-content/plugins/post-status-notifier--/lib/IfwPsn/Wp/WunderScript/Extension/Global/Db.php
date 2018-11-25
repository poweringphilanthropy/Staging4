<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Db.php 412 2015-04-02 22:11:08Z timoreithde $
 * @package
 */ 
class IfwPsn_Wp_WunderScript_Extension_Global_Db
{
    /**
     * @param $query
     * @return mixed|null
     */
    public function get_results($query)
    {
        return $this->_get('get_results', $query);
    }

    /**
     * @param $query
     * @return mixed|null
     */
    public function get_results_assoc($query)
    {
        return $this->_get('get_results', $query, ARRAY_A);
    }

    /**
     * @param $query
     * @return mixed|null
     */
    public function get_row($query)
    {
        return $this->_get('get_row', $query);
    }

    /**
     * @param $query
     * @return mixed|null
     */
    public function get_row_assoc($query)
    {
        return $this->_get('get_row', $query, ARRAY_A);
    }

    /**
     * @param null $query
     * @param int $x
     * @param int $y
     * @return null|string
     */
    public function get_var($query = null, $x = 0, $y = 0)
    {
        /**
         * @var wpdb $wpdb
         */
        global $wpdb;

        $result = null;
        $query = esc_sql($this->_filterQuery($query));

        if ($this->_isValid($query)) {
            $result = $wpdb->get_var($query, $x, $y);
        }
        return $result;
    }

    /**
     * @param null $query
     * @param int $x
     * @return null|string
     */
    public function get_col($query = null, $x = 0)
    {
        /**
         * @var wpdb $wpdb
         */
        global $wpdb;

        $result = null;
        $query = esc_sql($this->_filterQuery($query));

        if ($this->_isValid($query)) {
            $result = $wpdb->get_col($query, $x);
        }
        return $result;
    }

    /**
     * @param $method
     * @param $query
     * @param string $output
     * @return mixed|null
     */
    protected function _get($method, $query, $output = OBJECT)
    {
        /**
         * @var wpdb $wpdb
         */
        global $wpdb;

        $result = null;
        $query = esc_sql($this->_filterQuery($query));

        if ($this->_isValid($query)) {
            $result = $wpdb->$method($query);
        }
        return $result;
    }

    /**
     * @param $query
     * @return array|mixed
     */
    protected function _filterQuery($query)
    {
        $query = explode(';', $query);
        $query = array_shift($query);
        return $query;
    }

    /**
     * @param $query
     * @return bool
     */
    protected function _isValid($query)
    {
        return stripos($query, 'select') === 0;
    }
}
 