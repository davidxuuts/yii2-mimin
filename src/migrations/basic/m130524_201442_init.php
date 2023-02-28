<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\db\Schema;
use yii\db\Migration;
use davidxu\srbac\components\Configs;

class m130524_201442_init extends Migration
{
    private function tableName()
    {
        return Configs::instance()->userTable;
    }

    public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql' || strtolower($this->db->driverName) === 'mariadb') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->tableName(), [
			'id' => $this->primaryKey(),
			'username' => $this->string()->notNull()->unique(),
			'auth_key' => $this->string(32)->notNull(),
			'password_hash' => $this->string()->notNull(),
			'password_reset_token' => $this->string()->unique(),
			'email' => $this->string()->notNull()->unique(),

			'status' => $this->smallInteger()->notNull()->defaultValue(10),
			'created_at' => $this->integer()->notNull(),
			'updated_at' => $this->integer()->notNull(),
		], $tableOptions);
	}

	public function down()
	{
		$this->dropTable($this->tableName());
	}
}
