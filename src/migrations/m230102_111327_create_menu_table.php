<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use yii\db\Migration;
use davidxu\srbac\components\Configs;
use davidxu\srbac\models\Route;

/**
 * Migration table of table_menu
 * 
 * @author David Xu <david.xu.uts@gmail.com>
 * @since 1.0
 */
class m230102_111327_create_menu_table extends Migration
{
    /**
     * @throws Exception
     */
    private static function tableName(): string
    {
        return Configs::instance()->menuTable;
    }

    /**
     * @throws Exception
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql' || $this->db->driverName === 'mariadb') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->execute('SET foreign_key_checks = 0');

        $this->createTable($this->tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'parent_id' => $this->integer()->defaultValue(null),
            'route' => $this->string(64)->notNull(),
            'order' => $this->integer()->notNull()->defaultValue(0),
            'data' => $this->binary(),
        ], $tableOptions);
        $this->addForeignKey('FK_AuthMenuRoute', self::tableName(),
            'route', Route::tableName(), 'name',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey('FK_AuthMenu_Parent', self::tableName(),
            'parent_id', self::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );
        $this->createIndex('IDX_AuthMenu_Name', self::tableName(), 'name');
        $this->execute('SET foreign_key_checks = 1');
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->dropIndex('IDX_AuthMenu_Name', self::tableName());
        $this->dropForeignKey('FK_AuthMenu_Parent', self::tableName());
        $this->dropForeignKey('FK_AuthMenuRoute', self::tableName());
        $this->dropTable(self::tableName());
        $this->execute('SET foreign_key_checks = 1');
    }
}
