<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\base\InvalidConfigException;
use yii\db\Migration;
use davidxu\srbac\components\Configs;

class m151024_072453_create_route_table extends Migration
{
    /**
     * @throws InvalidConfigException
     */
    private function tableName()
    {
        return Configs::instance()->routeTable;
    }

    /**
     * @throws InvalidConfigException
     */
    public function up()
	{
        $tableOptions = null;
        if ($this->db->driverName === 'mysql' || $this->db->driverName === 'mariadb') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->execute('SET foreign_key_checks = 0');

		$this->createTable($this->tableName(), [
			'name' => $this->string(64)->notNull(),
			'alias' => $this->string(64)->notNull(),
			'type' => $this->string(64)->notNull(),
            'type_name' => $this->string(64)->defaultValue(null),
            'is_auto' => $this->tinyInteger(2)->null()->defaultValue(0),
			'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'PRIMARY KEY ([[name]])',
		], $tableOptions);
        $this->execute('SET foreign_key_checks = 1');
	}

    /**
     * @throws InvalidConfigException
     */
    public function down()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->dropTable($this->tableName());
        $this->execute('SET foreign_key_checks = 1');
	}
}
