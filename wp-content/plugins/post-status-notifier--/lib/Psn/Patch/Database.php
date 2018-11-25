<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Database.php 427 2015-10-29 19:42:20Z timoreithde $
 * @package   
 */ 
class Psn_Patch_Database implements IfwPsn_Wp_Plugin_Update_Patch_Interface
{
    /**
     * @param IfwPsn_Util_Version $presentVersion
     * @param IfwPsn_Wp_Plugin_Manager $pm
     * @throws IfwPsn_Wp_Plugin_Update_Patch_Exception
     */
    public function execute(IfwPsn_Util_Version $presentVersion, IfwPsn_Wp_Plugin_Manager $pm)
    {
        $this->updateRulesTable();

        IfwPsn_Wp_Proxy_Action::doAction('psn_patch_db');
    }

    /**
     * Updates the rule table, checks for missing fields after version 1.0
     */
    public function updateRulesTable()
    {
        // Updates for version 1.1
        // add bcc column to rules table
        if (!$this->isFieldBcc()) {
            $this->createRulesFieldBcc();
        }

        // Updates for version 1.3
        // add categories column to rules table
        if (!$this->isFieldCategories()) {
            $this->createRulesFieldCategories();
        }

        // Updates for version 1.4
        // add 'to', 'from' column to rules table
        if (!$this->isFieldTo()) {
            $this->createRulesFieldTo();
        }
        if (!$this->isFieldFrom()) {
            $this->createRulesFieldFrom();
        }

        // Updates for version 1.5
        if (!$this->isFieldMailTpl()) {
            $this->createRulesFieldMailTpl();
        }
        if (!$this->isFieldCcSelect()) {
            $this->createRulesFieldCcSelect();
        }
        if (!$this->isFieldBccSelect()) {
            $this->createRulesFieldBccSelect();
        }
        if (!$this->isFieldEditorRestriction()) {
            $this->createRulesFieldEditorRestriction();
        }

        // Updates for version 1.5.1
        if (!$this->isFieldToLoop()) {
            $this->createRulesFieldToLoop();
        }

        // Updates for version 1.8
        if (!$this->isFieldLimitType()) {
            $this->createRulesFieldLimitType();
        }
        if (!$this->isFieldLimitCount()) {
            $this->createRulesFieldLimitCount();
        }
        if (!$this->isFieldToDyn()) {
            $this->createRulesFieldToDyn();
        }

        // Updates for version 1.8.5
        if (!$this->isFieldExcludeCurrentUser()) {
            $this->createRulesFieldExcludeCurrentUser();
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $ruleFields = array (
            'id',
            'name',
            'posttype',
            'status_before',
            'status_after',
            'notification_subject',
            'notification_body',
            'recipient',
            'cc',
            'cc_select',
            'bcc',
            'bcc_select',
            'active',
            'service_email',
            'service_log',
            'categories',
            'from',
        );

        $diff = array_diff(
            IfwPsn_Wp_Proxy_Filter::apply('psn_db_patcher_rule_fields', $ruleFields),
            IfwPsn_Wp_Proxy_Db::getTableFieldNames('psn_rules')
        );

        return empty($diff);
    }

    /**
     * @return bool
     */
    public function isFieldBcc()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'bcc');
    }

    /**
     * @return bool
     */
    public function isFieldCategories()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'categories');
    }

    /**
     * @return bool
     */
    public function isFieldTo()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'to');
    }

    /**
     * @return bool
     */
    public function isFieldFrom()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'from');
    }

    /**
     * @return bool
     */
    public function isFieldMailTpl()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'mail_tpl');
    }

    /**
     * @return bool
     */
    public function isFieldCcSelect()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'cc_select');
    }

    /**
     * @return bool
     */
    public function isFieldBccSelect()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'bcc_select');
    }

    /**
     * @return bool
     */
    public function isFieldEditorRestriction()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'editor_restriction');
    }

    /**
     * @return bool
     */
    public function isFieldToLoop()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'to_loop');
    }

    /**
     * @return bool
     */
    public function isFieldLimitType()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'limit_type');
    }

    /**
     * @return bool
     */
    public function isFieldLimitCount()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'limit_count');
    }

    /**
     * @return bool
     */
    public function isFieldToDyn()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'to_dyn');
    }

    /**
     * @return bool
     */
    public function isFieldExcludeCurrentUser()
    {
        return IfwPsn_Wp_Proxy_Db::columnExists('psn_rules', 'exclude_current_user');
    }

    /**
     * Create field "bcc" on psn_rules table
     * @since 1.1
     */
    public function createRulesFieldBcc()
    {
        $query = sprintf('ALTER TABLE `%s` ADD `bcc` TEXT NULL AFTER `cc`', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "categories" on psn_rules table
     * @since 1.3
     */
    public function createRulesFieldCategories()
    {
        $query = sprintf('ALTER TABLE `%s` ADD `categories` TEXT NULL', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "to" on psn_rules table
     * @since 1.4
     */
    public function createRulesFieldTo()
    {
        // ALTER TABLE  `wp_psn_rules` ADD  `to` VARCHAR( 255 ) NULL AFTER  `recipient`
        $query = sprintf('ALTER TABLE `%s` ADD `to` VARCHAR( 255 ) NULL AFTER `recipient`', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "from" on psn_rules table
     * @since 1.4
     */
    public function createRulesFieldFrom()
    {
        // ALTER TABLE  `wp_psn_rules` ADD  `from` VARCHAR( 255 ) NULL AFTER  `recipient`
        $query = sprintf('ALTER TABLE `%s` ADD `from` VARCHAR( 255 ) NULL ', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "mail_tpl" on psn_rules table
     * @since 1.5
     */
    public function createRulesFieldMailTpl()
    {
        // ALTER TABLE  `wp_psn_rules` ADD  `mail_tpl` INT( 11 ) NULL AFTER  `recipient`
        $query = sprintf('ALTER TABLE `%s` ADD `mail_tpl` INT( 11 ) NULL ', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "cc_select" on psn_rules table
     * @since 1.5
     */
    public function createRulesFieldCcSelect()
    {
        // ALTER TABLE `wp_psn_rules` ADD `cc_select` TEXT NULL AFTER `to`;
        $query = sprintf('ALTER TABLE `%s` ADD `cc_select` TEXT NULL AFTER `to`', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "bcc_select" on psn_rules table
     * @since 1.5
     */
    public function createRulesFieldBccSelect()
    {
        // ALTER TABLE `wp_psn_rules` ADD `bcc_select` TEXT NULL AFTER `cc`;
        $query = sprintf('ALTER TABLE `%s` ADD `bcc_select` TEXT NULL AFTER `cc`', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "bcc_select" on psn_rules table
     * @since 1.5
     */
    public function createRulesFieldEditorRestriction()
    {
        // ALTER TABLE `wp_psn_rules` ADD `editor_restriction` TEXT NULL DEFAULT NULL ;
        $query = sprintf('ALTER TABLE `%s` ADD `editor_restriction` TEXT NULL DEFAULT NULL', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "to_loop" on psn_rules table
     * @since 1.5.1
     */
    public function createRulesFieldToLoop()
    {
        // ALTER TABLE `wp_psn_rules` ADD `editor_restriction` TEXT NULL DEFAULT NULL ;
        $query = sprintf('ALTER TABLE `%s` ADD `to_loop` TINYINT(1) DEFAULT 0', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "limit_type" on psn_rules table
     * @since 1.8
     */
    public function createRulesFieldLimitType()
    {
        // ALTER TABLE `wp_psn_rules` ADD `editor_restriction` TEXT NULL DEFAULT NULL ;
        $query = sprintf('ALTER TABLE `%s` ADD `limit_type` TINYINT(1) NULL', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "limit_count" on psn_rules table
     * @since 1.8
     */
    public function createRulesFieldLimitCount()
    {
        // ALTER TABLE `wp_psn_rules` ADD `editor_restriction` TEXT NULL DEFAULT NULL ;
        $query = sprintf('ALTER TABLE `%s` ADD `limit_count` INT(11) NULL', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "to_dyn" on psn_rules table
     * @since 1.8
     */
    public function createRulesFieldToDyn()
    {
        // ALTER TABLE `wp_psn_rules` ADD  `to_dyn` TEXT NULL AFTER  `to`;
        $query = sprintf('ALTER TABLE `%s` ADD `to_dyn` TEXT NULL AFTER  `to`', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * Create field "exclude_current_user" on psn_rules table
     * @since 1.8.5
     */
    public function createRulesFieldExcludeCurrentUser()
    {
        // ALTER TABLE `wp_psn_rules` ADD  `to_dyn` TEXT NULL AFTER  `to`;
        $query = sprintf('ALTER TABLE `%s` ADD `exclude_current_user` TINYINT (1) NOT NULL DEFAULT "0"', IfwPsn_Wp_Proxy_Db::getTableName('psn_rules'));
        IfwPsn_Wp_Proxy_Db::getObject()->query($query);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Database';
    }
}
