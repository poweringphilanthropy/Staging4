<?php
/**
 *
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) 2014 ifeelweb.de
 * @version   $Id: Mapper.php 353 2014-12-14 16:55:04Z timoreithde $
 * @package
 */

class Psn_Module_Limitations_Mapper
{
    const LIMIT_TYPE_POST_RULE = 1;
    const LIMIT_TYPE_POST_RULE_STATUS_AFTER = 2;

    /**
     * @param Psn_Model_Rule $rule
     * @return bool
     * @throws IfwPsn_Wp_Plugin_Exception
     */
    public static function isLimited(Psn_Model_Rule $rule)
    {
        $result = false;

        $pm = IfwPsn_Wp_Plugin_Manager::getInstance('Psn');
        $ruleLimitType = $rule->get('limit_type');

        if ($pm->hasOption('global_limitations')) {
            $result = true;
        } elseif (!empty($ruleLimitType)) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param Psn_Model_Rule $rule
     * @param $post
     * @return bool
     * @throws IfwPsn_Wp_Plugin_Exception
     */
    public static function isLimitReached(Psn_Model_Rule $rule, $post)
    {
        $result = false;

        $pm = IfwPsn_Wp_Plugin_Manager::getInstance('Psn');
        $ruleLimitType = $rule->get('limit_type');



        if (!empty($ruleLimitType)) {

            /**
             * Rule custom limitation
             */
            $limitType = (int)$ruleLimitType;
            $limitCount = $rule->get('limit_count');


        } elseif ($pm->hasOption('global_limitations')) {

            /**
             * Options global settings
             */

            $limitType = (int)$pm->getOption('global_limitations_type');
            $limitCount = $pm->getOption('global_limitations_count');
        }



        if (isset($limitType) && isset($limitCount)) {

            if (is_numeric($limitCount) && $limitCount > 0) {
                $limitCount = (int)$limitCount;
            } else {
                $limitCount = 1;
            }

            switch ($limitType) {

                case self::LIMIT_TYPE_POST_RULE:
                    $queryResult = IfwPsn_Wp_ORM_Model::factory('Psn_Module_Limitations_Model_Limitations')
                        ->where('rule_id', $rule->get('id'))->where('post_id', $post->ID)->find_many();

                    if (count($queryResult) >= $limitCount) {
                        $result = true;
                    }
                    break;

                case self::LIMIT_TYPE_POST_RULE_STATUS_AFTER:
                    $queryResult = IfwPsn_Wp_ORM_Model::factory('Psn_Module_Limitations_Model_Limitations')
                        ->where('rule_id', $rule->get('id'))->where('post_id', $post->ID)->where('status_after', $post->post_status)->find_many();

                    if (count($queryResult) >= $limitCount) {
                        $result = true;
                    }
                    break;
                default:
            }
        }

        return $result;
    }

    /**
     * @param Psn_Model_Rule $rule
     * @param $post
     * @return int
     */
    public static function writeMatch(Psn_Model_Rule $rule, $post)
    {
        $entry = IfwPsn_Wp_ORM_Model::factory('Psn_Module_Limitations_Model_Limitations')->create(array(
            'rule_id' => $rule->get('id'),
            'post_id' => $post->ID,
            'status_after' => $post->post_status,
            'timestamp' => date('Y-m-d H:i:s')
        ));
        $entry->save();

        return $entry->get('id');
    }

    /**
     * @return null|string
     * @throws IfwPsn_Wp_Plugin_Exception
     */
    public static function getLimitType()
    {
        $pm = IfwPsn_Wp_Plugin_Manager::getInstance('Psn');

        return (int)$pm->getOption('global_limitations_type');
    }

    /**
     * @param $type
     * @return null|string|void
     */
    public static function getLimitTypeLabel($type)
    {
        switch ($type) {
            case self::LIMIT_TYPE_POST_RULE:
                $result = __('By Rule + Post', 'psn_lmt');
                break;
            case self::LIMIT_TYPE_POST_RULE_STATUS_AFTER:
                $result = __('By Rule + Post + Status After', 'psn_lmt');
                break;
            default:
                $result = null;
        }

        return $result;
    }

    /**
     * @return mixed
     * @throws IfwPsn_Wp_Plugin_Exception
     */
    public static function getLimitCount()
    {
        $pm = IfwPsn_Wp_Plugin_Manager::getInstance('Psn');

        return $pm->getOption('global_limitations_count');
    }

    /**
     * @return mixed
     * @throws IfwPsn_Wp_Plugin_Exception
     */
    public static function getLimitationTrigger()
    {
        $result = null;

        $pm = IfwPsn_Wp_Plugin_Manager::getInstance('Psn');

        if ($pm->hasOption('global_limitations')) {
            $result = 'global';
        }

        return $result;
    }
}
 