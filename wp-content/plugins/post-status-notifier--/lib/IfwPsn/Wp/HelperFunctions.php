<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 * Helper functions
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Replacements.php 218 2014-01-19 23:24:17Z timoreithde $
 */

if (!function_exists('ifw_debug')) {

    /**
     * Writes debug info to debug.log
     *
     * @param $var
     * @param bool $backtrace
     */
    function ifw_debug ($var, $backtrace = false, $verbose = true) {

        if (WP_DEBUG === true) {

            $bt = debug_backtrace();
            $pathinfo = pathinfo($bt[0]['file']);

            $output = '';
            if ($verbose) {
                $output .= __FUNCTION__ . ' in ';
                $output .= $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['basename'] . ':' . $bt[0]['line'] . ':' .
                    ' ('. gettype($var) . ') ';
            }

            if (is_array($var) || is_object($var)) {
                $output .= print_r($var, true);
            } elseif (is_bool($var)) {
                $output .= var_export($var, true);
            } else {
                $output .= $var;
            }
            //error_log($output);
            $output .= PHP_EOL;

            file_put_contents(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'debug.log', $output, FILE_APPEND);

            if ($backtrace) {
                $backtrace = array_reverse(debug_backtrace());

                $backtrace_output = '';

                $counter = 0;

                foreach ($backtrace as $row) {
                    if ((count($backtrace)-1) == $counter) {
                        break;
                    }

                    $file = (isset($row['file'])) ? $row['file'] : '';
                    $line = (isset($row['line'])) ? $row['line'] : '';
                    $class = (isset($row['class'])) ? $row['class'] : '';
                    $function = (isset($row['function'])) ? $row['function'] : '';

                    $backtrace_output .= $counter .': '. $file .':'. $line .
                        ', class: '. $class .', function: '. $function . PHP_EOL;
                    $counter++;
                }
                error_log(__FUNCTION__ . ' backtrace:' . PHP_EOL . $backtrace_output);
            }
        }
    }
}

if (!function_exists('ifw_log_error')) {

    /**
     * Writes error message to debug.log
     * @param $error
     */
    function ifw_log_error ($error) {

        if (WP_DEBUG === true) {
            error_log($error);
        }
    }
}

if (!function_exists('ifw_unserialize_recursive')) {

    /**
     * @param $data
     * @return mixed|string
     */
    function ifw_unserialize_recursive ($data) {

        if (is_serialized($data)) {

            $data = trim($data);
            $result = unserialize($data);

            if (is_array($result)) {
                foreach($result as &$r) $r = ifw_unserialize_recursive($r);
            }
            return $result;

        } elseif (is_array($data)) {

            foreach ($data as &$r) {
                $r = ifw_unserialize_recursive($r);
            }
            return $data;

        } else {
            return $data;
        }
    }
}

if (!function_exists('ifw_serialize_array_values')) {

    /**
     * @param $arr
     * @return array
     */
    function ifw_serialize_array_values ($arr) {

        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                sort($v);
                $arr[$k] = serialize($v);
            }
        }

        return $arr;
    }
}

if (!function_exists('ifw_array_search_recursive_key')) {

    /**
     * @param array $array
     * @param $key
     * @return null
     */
    function ifw_array_search_recursive_key (array $array, $key) {

        $iterator  = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($recursive as $k => $value) {
            if ($key === $k) {
                return $value;
            }
        }

        return null;
    }
}

if (!function_exists('ifw_array_search_key_value')) {

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    function ifw_array_search_key_value($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, ifw_array_search_key_value($subarray, $key, $value));
            }
        }

        return $results;
    }
}

if (!function_exists('ifw_rrmdir')) {

    /**
     * @param $dir
     */
    function ifw_rrmdir ($dir) {

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}

if (!function_exists('ifw_xml_to_array')) {

    /**
     * @param DOMElement $root
     * @param bool $eagerAttributes
     * @return array|string
     */
    function ifw_xml_to_array(DOMElement $root, $eagerAttributes = false)
    {

        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {

            $children = $root->childNodes;

            if ($children->length == 1) {

                $child = $children->item(0);

                if ($child->nodeType == XML_TEXT_NODE) {

                    if (!$eagerAttributes) {
                        $result = $child->nodeValue;
                        return $result;
                    } else {
                        $result['_value'] = $child->nodeValue;
                        return count($result) == 1
                            ? $result['_value']
                            : $result;
                    }
                }
            }

            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = ifw_xml_to_array($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = ifw_xml_to_array($child);
                }
            }
        }

        return $result;
    }
}

if (!function_exists('ifw_log_rotate')) {
    /**
     * @param $text
     * @param $filepath
     * @param string|null $type options: daily
     * @param int $maxsize in MB
     * @return bool
     */
    function ifw_log_rotate($text, $filepath, $type = null, $maxsize = 20, $prefix = null)
    {

        if ($type == 'daily') {
            $filename = $filepath . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
        } else {
            $filename = $filepath;
        }

        // size check / rotation
        if (file_exists($filename) && filesize($filename) > $maxsize * 1024 * 1024) {

            $iteration = 1;
            while (file_exists($filename . '.' . $iteration)) {
                $iteration++;
            }
            rename($filename, $filename . '.' . $iteration);
        }

        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename, 0666);
        }

        if (!is_writable($filename)) {
            return false;
        }
        if (!$handle = fopen($filename, 'a')) {
            return false;
        }

        if ($prefix == null) {

            $prefix = date('Y/m/d H:i:s') . ': ';

        }

        $output = $prefix . $text . PHP_EOL;

        if (fwrite($handle, $output) === FALSE) {
            return false;
        }
        fclose($handle);

        return true;
    }
}

if (!function_exists('ifw_is_empty')) {
    function ifw_is_empty($var) {
        return empty($var);
    }
}

if (!function_exists('ifw_is_not_empty')) {
    function ifw_is_not_empty($var) {
        return !empty($var);
    }
}

if (!function_exists('ifw_raise_memory_limit')) {
    function ifw_raise_memory_limit($from = 64, $to = 128) {

        $memory_limit = ifw_return_bytes(ini_get('memory_limit'));
        if ($memory_limit <= ($from * 1024 * 1024)) {
            // Memory insufficient
            ini_set('memory_limit', $to . 'M');
        }
    }
}

if (!function_exists('ifw_return_bytes')) {
    function ifw_return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}