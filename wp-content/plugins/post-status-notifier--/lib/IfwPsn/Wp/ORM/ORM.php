<?php
/**
 * ifeelweb.de Wordpress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Extending Idiorm for using wpdb if use of pdo_mysql is not possible
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: ORM.php 345 2014-11-08 00:16:28Z timoreithde $
 */
class IfwPsn_Wp_ORM_ORM extends IfwPsn_Wp_ORM_Idiorm
{
    /**
     * Check if we want to use pdo_mysql and if it is available on our server
     *
     * @param $connection_name
     * @return bool
     */
    public static function usePdo($connection_name)
    {
        $usePdo = isset(self::$_config[$connection_name]['use_pdo']) ? self::$_config[$connection_name]['use_pdo'] : false;
        return $usePdo && IfwPsn_Wp_Server_Php::isPdoMysql();
    }

    /**
     * Set up the database connection used by the class
     * @param string $connection_name Which connection to use
     */
    protected static function _setup_db($connection_name = self::DEFAULT_CONNECTION)
    {
        if (!array_key_exists($connection_name, self::$_db) ||
            !is_object(self::$_db[$connection_name])) {
            self::_setup_db_config($connection_name);

            if (self::usePdo($connection_name)) {

                // set the pdo error mode
                self::$_config[$connection_name]['error_mode'] = PDO::ERRMODE_EXCEPTION;

                $db = new PDO(
                    self::$_config[$connection_name]['connection_string'],
                    self::$_config[$connection_name]['username'],
                    self::$_config[$connection_name]['password'],
                    self::$_config[$connection_name]['driver_options']
                );

                $db->setAttribute(PDO::ATTR_ERRMODE, self::$_config[$connection_name]['error_mode']);
                self::set_db($db, $connection_name);
            } else {
                // no pdo_mysql available, let's use the archaic WP db object ;)
                global $wpdb;

                // as we are using mysql by default, we can set the quote character here to avoid
                // overwriting some methods that determine it automatically
                self::$_config[$connection_name]['identifier_quote_character'] = '`';
                self::$_db[$connection_name] = $wpdb;
            }
        }
    }

    /**
     * Overwrite to avoid PDO use
     *
     * @param string $query
     * @param array $parameters An array of parameters to be bound in to the query
     * @param string $connection_name Which connection to use
     * @return bool Response of PDOStatement::execute()
     */
    protected static function _execute($query, $parameters = array(), $connection_name = self::DEFAULT_CONNECTION)
    {
        self::_log_query($query, $parameters, $connection_name);

        if (self::usePdo($connection_name)) {
            // the default with pdo_mysql
            $statement = self::$_db[$connection_name]->prepare($query);

            self::$_last_statement = $statement;

            $result = $statement->execute($parameters);

        } else {
            // with wpdb: nothing to prepare, no statement...
            $result = true;
        }

        return $result;
    }

    /**
     * Execute the SELECT query that has been built up by chaining methods
     * on this class. Return an array of rows as associative arrays.
     */
    protected function _run()
    {
        $query = $this->_build_select();

        $caching_enabled = self::$_config[$this->_connection_name]['caching'];

        if ($caching_enabled) {
            $cache_key = self::_create_cache_key($query, $this->_values);
            $cached_result = self::_check_query_cache($cache_key, $this->_connection_name);

            if ($cached_result !== false) {
                return $cached_result;
            }
        }

        $rows = array();

        if (self::usePdo($this->_connection_name)) {

            // with pdo
            self::_execute($query, $this->_values, $this->_connection_name);
            $statement = self::get_last_statement();

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = $row;
            }

        } else {

            // without pdo_mysql use the wpdp object to get_result of $query as associative array
            $query = $this->_prepareWpdbQuery($query, $this->_values);
            $rows = self::get_db($this->_connection_name)->get_results($query, ARRAY_A);
        }

        if ($caching_enabled) {
            self::_cache_query_result($cache_key, $rows, $this->_connection_name);
        }

        // reset Idiorm after executing the query
        $this->_values = array();
        $this->_result_columns = array('*');
        $this->_using_default_result_columns = true;

        return $rows;
    }

    /**
     * Overwrite to avoid PDO use
     *
     * Save any fields which have been modified on this object
     * to the database.
     */
    public function save()
    {
        $query = array();
        // remove any expression fields as they are already baked into the query
        $values = array_values(array_diff_key($this->_dirty_fields, $this->_expr_fields));

        if (!$this->_is_new) { // UPDATE
            // If there are no dirty values, do nothing
            if (empty($values) && empty($this->_expr_fields)) {
                return true;
            }
            $query = $this->_build_update();
            $values[] = $this->id();
        } else { // INSERT
            $query = $this->_build_insert();
        }

        if (self::usePdo($this->_connection_name)) {
            $success = self::_execute($query, $values, $this->_connection_name);
        } else {
            $query = $this->_prepareWpdbQuery($query, $values);
            $success = self::get_db($this->_connection_name)->query($query) > 0;
        }

        // If we've just inserted a new record, set the ID of this object
        if ($this->_is_new) {
            $this->_is_new = false;
            if (is_null($this->id())) {
                //if(self::$_db[$this->_connection_name]->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
                //    $this->_data[$this->_get_id_column_name()] = self::get_last_statement()->fetchColumn();
                //} else {
                    if (self::usePdo($this->_connection_name)) {
                        $this->_data[$this->_get_id_column_name()] = self::$_db[$this->_connection_name]->lastInsertId();
                    } else {
                        $this->_data[$this->_get_id_column_name()] = self::get_db($this->_connection_name)->insert_id;
                    }
                //}
            }
        }

        $this->_dirty_fields = array();
        return $success;
    }

    /**
     * Delete this record from the database
     */
    public function delete()
    {
        $query = join(" ", array(
            "DELETE FROM",
            $this->_quote_identifier($this->_table_name),
            "WHERE",
            $this->_quote_identifier($this->_get_id_column_name()),
            "= ?",
        ));

        if (self::usePdo($this->_connection_name)) {
            return self::_execute($query, array($this->id()), $this->_connection_name);
        } else {
            $query = $this->_prepareWpdbQuery($query, array($this->id()));
            return self::get_db($this->_connection_name)->query($query) > 0;
        }
    }

    /**
     * Delete many records from the database
     */
    public function delete_many()
    {
        // Build and return the full DELETE statement by concatenating
        // the results of calling each separate builder method.
        $query = $this->_join_if_not_empty(" ", array(
            "DELETE FROM",
            $this->_quote_identifier($this->_table_name),
            $this->_build_where(),
        ));

        if (self::usePdo($this->_connection_name)) {
            return self::_execute($query, $this->_values, $this->_connection_name);
        } else {
            $query = $this->_prepareWpdbQuery($query, $this->_values);
            return self::get_db($this->_connection_name)->query($query) > 0;
        }
    }

    /**
     * @param $query
     * @param $values
     * @return mixed
     */
    protected function _prepareWpdbQuery($query, $values)
    {
        if (!(self::get_db($this->_connection_name) instanceof wpdb)) {
            return $query;
        }

        $args = array();
        foreach ($values as $value) {

            switch(gettype($value)) {
                case 'integer':
                    $directive = '%d';
                    break;
                case 'double':
                    $directive = '%f';
                    break;
                case 'string':
                default:
                    $directive = '%s';
            }
            $query = preg_replace(array('/\?/'), $directive, $query, 1);
            //$value = str_replace('%', '%%', $value);
            array_push($args, $value);
        }

        if ( strpos( $query, '%' ) === false ) {
            // as of 3.9 return $query if not contains placeholder
            return $query;
        }
        return self::get_db($this->_connection_name)->prepare($query, $args);
    }

    /**
     * Overwrite to avoid PDO use
     *
     * Build an INSERT query
     */
    protected function _build_insert()
    {
        $query[] = "INSERT INTO";
        $query[] = $this->_quote_identifier($this->_table_name);
        $field_list = array_map(array($this, '_quote_identifier'), array_keys($this->_dirty_fields));
        $query[] = "(" . join(", ", $field_list) . ")";
        $query[] = "VALUES";

        $placeholders = $this->_create_placeholders($this->_dirty_fields);
        $query[] = "({$placeholders})";

        /*if (self::$_db[$this->_connection_name]->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
            $query[] = 'RETURNING ' . $this->_quote_identifier($this->_get_id_column_name());
        }*/

        return join(" ", $query);
    }

    /**
     * Overwrite to avoid PDO use
     *
     * Build LIMIT
     */
    protected function _build_limit()
    {
        if (!is_null($this->_limit)) {
            $clause = 'LIMIT';
            /*if (self::$_db[$this->_connection_name]->getAttribute(PDO::ATTR_DRIVER_NAME) == 'firebird') {
                $clause = 'ROWS';
            }*/
            return "$clause " . $this->_limit;
        }
        return '';
    }

    /**
     * Overwrite to avoid PDO use
     *
     * Build OFFSET
     */
    protected function _build_offset()
    {
        if (!is_null($this->_offset)) {
            $clause = 'OFFSET';
            /*if (self::$_db[$this->_connection_name]->getAttribute(PDO::ATTR_DRIVER_NAME) == 'firebird') {
                $clause = 'TO';
            }*/
            return "$clause " . $this->_offset;
        }
        return '';
    }

    /**
     * Overwrite to get an IfwPsn_Wp_ORM_ORM instance
     *
     * @param string $table_name
     * @param string $connection_name
     * @return IfwPsn_Wp_ORM_ORM|ORM
     */
    public static function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION)
    {
        self::_setup_db($connection_name);
        return new self($table_name, array(), $connection_name);
    }

    /**
     * Overwrite to get an IfwPsn_Wp_ORM_ORM instance from for_table
     *
     * @param $row
     * @return IfwPsn_Wp_ORM_ORM|ORM
     */
    protected function _create_instance_from_row($row)
    {
        $instance = self::for_table($this->_table_name, $this->_connection_name);
        $instance->use_id_column($this->_instance_id_column);
        $instance->hydrate($row);
        return $instance;
    }
}