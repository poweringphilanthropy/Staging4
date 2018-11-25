<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 *
 *
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Model.php 466 2015-09-29 19:38:44Z timoreithde $
 */
abstract class IfwPsn_Wp_ORM_Model extends IfwPsn_Wp_ORM_ModelParis
{
    /**
     * Checks if table exists
     * @return bool
     */
    public function exists()
    {
        global $wpdb, $table_prefix;
        $result = false;

        $r = new ReflectionProperty($this, '_table');
        $query = sprintf('SHOW TABLES LIKE "%s"', $table_prefix . $r->getValue());
        if ($wpdb->get_row($query) !== null) {
            $result = true;
        }

        return $result;
    }

    public function existsByData()
    {
    }

    /**
     * @param $modelName
     * @param $id
     * @param array $options
     * @return bool
     */
    public static function duplicate($modelName, $id, $options = array())
    {
        if (isset($options['name_format'])) {
            $newNameFormat = $options['name_format'];
        } else {
            $newNameFormat = '%s [%s%s]';
        }
        if (isset($options['name_col'])) {
            $nameCol = $options['name_col'];
        } else {
            $nameCol = 'name';
        }
        if (isset($options['id_col'])) {
            $idCol = $options['id_col'];
        } else {
            $idCol = 'id';
        }


        $item = self::factory($modelName)->find_one((int)$id);
        $values = $item->as_array();

        unset($values[$idCol]);

        // if a values callback is set, call it
        if (isset($options['values_callback']) && is_callable($options['values_callback'])) {
            $values = call_user_func($options['values_callback'], $values);
        }

        $values[$nameCol] = self::getUniqueName($modelName, $values[$nameCol], $nameCol, sprintf($newNameFormat, '%s', __('Duplicate', 'ifw'), '%s'), 1);

        $model = IfwPsn_Wp_ORM_Model::factory($modelName);
        $result = $model->create($values)->save();

        if (isset($options['save_callback']) && is_callable($options['save_callback'])) {
            call_user_func($options['save_callback'], $item, $model);
        }

        return $result;
    }

    /**
     * Checks if a model with the given name already exists and if so returns a new unique name
     * @param $modelName
     * @param $name
     * @return string
     */
    public static function getUniqueName($modelName, $name, $nameCol = 'name', $newNameFormat = '%s [%s]', $counterStart = 2)
    {
        $count = self::factory($modelName)->where($nameCol, $name)->count();

        if ($count == 0) {
            // no model with the given name exists
            return $name;
        }

        $counter = $counterStart;
        $newName = sprintf($newNameFormat, $name, $counter);

        while (self::factory($modelName)->where($nameCol, $newName)->count() > 0) {
            $counter++;
            $newName = sprintf($newNameFormat, $name, $counter);
        }

        return $newName;
    }

    /**
     * @param string $nameCol
     * @return string
     */
    public function getName($nameCol = 'name')
    {
        return $this->get($nameCol);
    }

    /**
     * @return mixed
     */
    public function getSanitizedName($nameCol = 'name')
    {
        $name = $this->get($nameCol);
        $name = str_replace(' ', '_', $name);
        $name = preg_replace("/[^a-zA-Z0-9_]+/", "", $name);
        return $name;
    }

    /**
     * @param $modelName
     * @param $items
     * @param array $options
     * @return int
     */
    public static function import($modelName, $items, $options = array())
    {
        $counter = 0;

        if (!is_array($items)) {
            $items = array($items);
        }

        if (isset($options['id_col'])) {
            $idCol = $options['id_col'];
        } else {
            $idCol = 'id';
        }
        if (isset($options['name_col'])) {
            $nameCol = $options['name_col'];
        } else {
            $nameCol = 'name';
        }
        if (isset($options['prefix'])) {
            $prefix = $options['prefix'];
        }
        if (isset($options['skip_col'])) {
            $skip_col = $options['skip_col'];
            if (!is_array($skip_col)) {
                $skip_col = array($skip_col);
            }
        } else {
            $skip_col = null;
        }

        // create imported items
        foreach ($items as $item) {

            // unset the primary key
            unset($item[$idCol]);

            if (!empty($prefix)) {
                $item[$nameCol] = $prefix . $item[$nameCol];
            }
            // if item callback is set, call it
            if (isset($options['item_callback']) && is_callable($options['item_callback'])) {
                $item = call_user_func($options['item_callback'], $item);
            } elseif (isset($options['item_callback']) && is_array($options['item_callback'])) {
                foreach ($options['item_callback'] as $item_callback) {
                    if (is_callable($item_callback)) {
                        $item = call_user_func($item_callback, $item);
                    }
                }
            }

            $params = array();

            foreach ($item as $k => $v) {
                if (!empty($k) && ($skip_col === null || !in_array($k, $skip_col))) {
                    $params[$k] = $v;
                }
            }

            try {
                $obj = IfwPsn_Wp_ORM_Model::factory($modelName)->create($params);

                if (!IfwPsn_Wp_Model_Mapper_Abstract::existsByData($obj) && $obj->save() != false) {
                    $counter++;
                    if (isset($options['item_callback_saved']) && is_callable($options['item_callback_saved'])) {
                        call_user_func($options['item_callback_saved'], $obj, $item);
                    }
                }
            } catch (Exception $e) {
                // object could not be stored in db
            }
        }

        return $counter;
    }

    /**
     * @param $modelName
     * @param $itemList
     * @param array $options
     */
    public static function export($modelName, $itemList, $options = array())
    {
        global $wp_filter, $merged_filters;
        $wp_filter = array();
        $merged_filters = array();

        if (!is_array($itemList)) {
            $itemList = array($itemList);
        }
        $itemList = array_map('intval', $itemList);

        $items = IfwPsn_Wp_ORM_Model::factory($modelName)->where_in('id', $itemList)->find_array();

        if (isset($options['node_name_plural'])) {
            $itemNamePlural = $options['node_name_plural'];
        } elseif (isset($options['item_name_plural'])) {
            $itemNamePlural = $options['item_name_plural'];
        } else {
            $itemNamePlural = 'items';
        }
        if (isset($options['node_name_singular'])) {
            $itemNameSingular = $options['node_name_singular'];
        } elseif (isset($options['item_name_singular'])) {
            $itemNameSingular = $options['item_name_singular'];
        } else {
            $itemNameSingular = 'item';
        }

        if (count($items) > 1 && isset($options['filename_bundle'])) {
            $filename = $options['filename_bundle'];
        } elseif (count($items) == 1 && isset($options['filename'])) {
            $filename = $options['filename'];
        } else {
            $filename = 'Export_'. date('Y-m-d_H_i_s');
        }

//        if (isset($options['filename'])) {
//            $filename = $options['filename'];
//        } else {
//            $filename = 'Export_'. date('Y-m-d_H_i_s');
//        }

        $result = "<$itemNamePlural>\n";

        foreach ( $items as $item ) {

            if (isset($options['item_callback']) && is_callable($options['item_callback'])) {
                $item = call_user_func($options['item_callback'], $item);
            }

            $result .= "\t<$itemNameSingular>\n";
            foreach ($item as $field => $value) {

                if (isset($options['value_callback']) && is_callable($options['value_callback'])) {
                    $value = call_user_func($options['value_callback'], $value);
                } else {
                    $value = '<![CDATA['. $value . ']]>';
                }

                $result .= "\t\t" . '<column name="'. $field .'">'. $value .'</column>' . "\n";
            }
            if (isset($options['item_result_callback']) && is_callable($options['item_result_callback'])) {
                $result .= call_user_func($options['item_result_callback'], $item);
            }
            $result .= "\t</$itemNameSingular>\n";
        }
        $result .= "</$itemNamePlural>\n";

        $xml = new SimpleXMLElement($result);

        $filename .= '.xml';

        // so far export means to download an xml file, so this is directly integrated here.
        // refactoring could return the xml and pass it to an export handler
        header('Content-disposition: attachment; filename="'. $filename .'"');
        header('Content-type: "text/xml"; charset="utf8"');
        echo $xml->asXML();
        exit;
    }
}
