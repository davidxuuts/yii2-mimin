<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\base\InvalidConfigException;
use yii\db\Migration;
use davidxu\srbac\components\Configs;

class m221024_072455_update_auth_item_table extends Migration
{
    /**
     * @throws InvalidConfigException
     */
    private function tableName()
    {
        return Configs::instance()->authItemTable;
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
        $this->dropPrimaryKey('name', $this->tableName());
        $this->execute('ALTER TABLE' . $this->tableName() . ' ' . $tableOptions);
        $this->addColumn($this->tableName(),'id', $this->primaryKey() . ' FIRST');
        $this->execute('SET foreign_key_checks = 1');
	}

    /**
     * @throws InvalidConfigException
     */
    public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->dropColumn($this->tableName(), 'id');
        $this->addPrimaryKey('name', $this->tableName(), 'name');
        $this->execute('SET foreign_key_checks = 1');
	}
}
