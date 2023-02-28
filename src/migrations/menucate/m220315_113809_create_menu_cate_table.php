<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use davidxu\srbac\components\Configs;
use yii\base\InvalidConfigException;
use yii\db\Migration;

class m220315_113809_create_menu_cate_table extends Migration
{
    /**
     * @throws InvalidConfigException
     */
    private function tableName()
    {
        return Configs::instance()->menuTable;
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
            'id' => $this->primaryKey()->comment('ID'),
            'title' => $this->string(50)->notNull()->comment('Title'),
            'app_id' => $this->string(20)->notNull()->comment('App ID'),
            'addon' => $this->string(100)->null()->defaultValue('')->comment('Addons name'),
            'icon' => $this->string(50)->null()->defaultValue('')->comment('Icon'),
            'order' => $this->integer()->notNull()->defaultValue(999)->comment('Sort order'),
            'status' => $this->tinyInteger(4)->defaultValue(1)
                ->comment('Status[-1:Deleted;0:Disabled;1:Enabled]')
        ], $tableOptions);
        $this->addCommentOnTable($this->tableName(), 'System menu category table');
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
