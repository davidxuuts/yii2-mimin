<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\base\InvalidConfigException;
use yii\db\Migration;
use davidxu\srbac\components\Configs;

class m221024_072556_update_auth_assignment_table extends Migration
{
    /**
     * @throws InvalidConfigException
     */
    private function tableName()
    {
        return Configs::instance()->authAssignmentTable;
    }

    /**
     * @throws InvalidConfigException
     */
    public function safeUp()
	{
        $tableOptions = null;
        if ($this->db->driverName === 'mysql' || $this->db->driverName === 'mariadb') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->execute('SET foreign_key_checks = 0');
        $this->dropIndex('auth_assignment_user_id_idx', $this->tableName());
        $this->execute('ALTER TABLE' . $this->tableName() . ' ' . $tableOptions);
        $this->alterColumn($this->tableName(), 'user_id', $this->integer());
        $this->createIndex('IDX_AuthAssignmentUserId', $this->tableName(), 'user_id');
        $this->addForeignKey('FK_AuthAssignment_ItemName', $this->tableName(), 'item_name',
            Configs::instance()->authItemTable, 'name', 'CASCADE', 'CASCADE'
        );
        $this->execute('SET foreign_key_checks = 1');
	}

    /**
     * @throws InvalidConfigException
     */
    public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->dropForeignKey('FK_AuthAssignment_ItemName', $this->tableName());
        $this->dropIndex('IDX_AuthAssignmentUserId', $this->tableName());
        $this->alterColumn($this->tableName(), 'user_id', $this->string(64));
        $this->createIndex('auth_assignment_user_id_idx', $this->tableName(), 'user_id');
        $this->execute('SET foreign_key_checks = 1');
	}
}
