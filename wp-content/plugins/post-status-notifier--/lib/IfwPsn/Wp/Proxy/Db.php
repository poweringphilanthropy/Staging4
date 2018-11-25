<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Db.php 227 2014-01-27 23:32:16Z timoreithde $
 * @package   
 */ 
class IfwPsn_Wp_Proxy_Db 
{
    /**
     * @var array
     */
    protected static $_tableStore = array();



    /**
     * Convenience method to get code completion in IDE
     * @return wpdb
     */
    public static function getObject()
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * Retrieves the database name
     * @return string
     */
    public static function getName()
    {
        return DB_NAME;
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        global $table_prefix;
        return $table_prefix;
    }

    /**
     * Get the table name with prefix
     * @param $table
     * @return string
     */
    public static function getTableName($table)
    {
        if (strpos($table, self::getPrefix()) !== 0) {
            return self::getPrefix() . $table;
        }

        return $table;
    }

    /**
     * @param $table
     * @return array
     */
    public static function getTableFieldNames($table)
    {
        $result = array();

        $describeResult = self::describe($table);

        if (is_array($describeResult)) {
            foreach ($describeResult as $field) {
                array_push($result, $field->Field);
            }
        }

        return $result;
    }

    /**
     * Get the result of DESCRIBE $table
     *
     * @param $table
     * @return mixed
     */
    public static function describe($table)
    {
        if (!array_key_exists($table, self::$_tableStore)) {
            $sql = sprintf('DESCRIBE `%s`', self::getPrefix() . $table);
            self::$_tableStore[$table] = self::getObject()->get_results($sql);
        }
        return self::$_tableStore[$table];
    }

    /**
     * Checks if a column in a table exists
     *
     * @param $table
     * @param $column
     * @return bool
     */
    public static function columnExists($table, $column)
    {
        return in_array($column, self::getTableFieldNames($table));
    }
}
