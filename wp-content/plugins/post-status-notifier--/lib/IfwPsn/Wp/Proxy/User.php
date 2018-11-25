<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: User.php 478 2015-10-30 12:08:05Z timoreithde $
 */ 
class IfwPsn_Wp_Proxy_User
{
    /**
     * @var array
     */
    protected static $_allUsersEmails;

    /**
     * @var array
     */
    protected static $_roleMembersEmails;



    /**
     * Proxy method for get_current_user_id
     * @return int
     */
    public static function getCurrentUserId()
    {
        return get_current_user_id();
    }

    /**
     * Proxy method for get_userdata
     * @param $userId
     * @return null|WP_User
     */
    public static function getData($userId)
    {
        $result = null;

        if (is_int($userId) && $userId > 0) {
            $result = get_userdata($userId);
        }

        return $result;
    }

    /**
     * @return null|WP_User
     */
    public static function getCurrentUserData()
    {
        return self::getData(self::getCurrentUserId());
    }

    /**
     * @return null|array
     */
    public static function getCurrentUserRoles()
    {
        $data = self::getData(self::getCurrentUserId());

        if ($data instanceof WP_User) {
            $roles = $data->roles;
            if (!is_array($roles)) {
                $roles = array($roles);
            }
        } else {
            $roles = array();
        }

        return $roles;
    }

    /**
     * Determines if the current user is a member of an array of roles
     *
     * @param array $roles
     * @return bool|int
     */
    public static function isCurrentUserMemberOfRoles(array $roles)
    {
        $userRoles = self::getCurrentUserRoles();

        if (empty($roles) or empty($userRoles)) {
            return false;
        }

        return count(array_intersect($roles, $userRoles)) > 0;
    }

    /**
     * @param $userId
     * @return int|mixed|null
     */
    public static function getEmail($userId)
    {
        $result = null;

        $userdata = self::getData((int)$userId);
        if ($userdata instanceof WP_User) {
            $result = $userdata->user_email;
        }

        return $result;
    }

    /**
     * @return int|mixed|null
     */
    public static function getCurrentUserEmail()
    {
        return self::getEmail(self::getCurrentUserId());
    }

    /**
     * @param array $users
     * @return array
     */
    public static function getEmails(array $users)
    {
        $emails = array();

        foreach ($users as $user) {
            if ($user instanceof WP_User) {
                array_push($emails, trim($user->get('user_email')));
            } elseif (is_int($user)) {
                array_push($emails, self::getEmail($user));
            }
        }

        return $emails;
    }

    /**
     * Retrieve user meta field for a user.
     *
     * @param $userId
     * @param $option
     * @param bool $single
     * @return mixed
     */
    public static function getMeta($userId, $option, $single = false)
    {
        return get_user_meta($userId, $option, $single);
    }

    /**
     * Retrieve user meta field for current user
     */
    public static function getCurrentUserMeta($option)
    {
        return self::getMeta(self::getCurrentUserId(), $option);
    }

    /**
     * Retrieve single user meta value for current user
     */
    public static function getCurrentUserMetaSingle($option)
    {
        return self::getMeta(self::getCurrentUserId(), $option, true);
    }

    /**
     * Alias for update_user_meta
     *
     * @param int $user_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     * @return mixed
     */
    public static function updateMeta($user_id, $meta_key, $meta_value, $prev_value = '')
    {
        return update_user_meta($user_id, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Alias for delete_user_meta
     *
     * @param int $user_id
     * @param string $meta_key
     * @param string $meta_value
     * @return bool
     */
    public static function deleteMeta($user_id, $meta_key, $meta_value = '')
    {
        return delete_user_meta($user_id, $meta_key, $meta_value);
    }

    /**
     * @param $email
     * @return bool|WP_User
     */
    public static function getByEmail($email)
    {
        return get_user_by('email', $email);
    }

    /**
     * Get the blog admins display_name (if there is a user matching the blog admin email)
     * @return WP_User|null
     */
    public static function getAdminDisplayName()
    {
        $adminDisplayName = null;

        if (function_exists('get_user_by')) {
            $user = self::getByEmail(IfwPsn_Wp_Proxy_Blog::getAdminEmail());
            if ($user instanceof WP_User) {
                $adminDisplayName = $user->display_name;
            }
        } else {
            $u = IfwPsn_Wp_ORM_Model::factory('IfwPsn_Wp_Model_User')->where_equal('user_email', IfwPsn_Wp_Proxy_Blog::getAdminEmail())->find_one();
            if ($u instanceof IfwPsn_Wp_Model_User) {
                $adminDisplayName = $u->get('display_name');
            }
        }

        return $adminDisplayName;
    }

    /**
     * @param string $roleName
     * @return array
     */
    public static function getUsersByRoleName($roleName)
    {
        return get_users(array('role' => $roleName));
    }

    /**
     * @return array
     */
    public static function getAllUsers()
    {
        return get_users();
    }

    /**
     * @return array
     */
    public static function getAllUsersEmails()
    {
        if (self::$_allUsersEmails === null) {
            self::$_allUsersEmails = self::getEmails(self::getAllUsers());
        }

        return self::$_allUsersEmails;
    }

    /**
     * @param $rolename
     * @return array
     */
    public static function getRoleMembersEmails($rolename)
    {
        if (!isset(self::$_roleMembersEmails[$rolename])) {

            $members = self::getUsersByRoleName($rolename);
            self::$_roleMembersEmails[$rolename] = self::getEmails($members);
        }

        return self::$_roleMembersEmails[$rolename];
    }

}
