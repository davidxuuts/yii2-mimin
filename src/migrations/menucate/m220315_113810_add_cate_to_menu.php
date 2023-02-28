<?php
/*
 * Copyright (c) 2023.
 * @author David Xu <david.xu.uts@163.com>
 * All rights reserved.
 */

use davidxu\srbac\components\Configs;
use yii\base\InvalidConfigException;
use yii\db\Migration;

class m220315_113810_add_cate_to_menu extends Migration
{
    /**
     * @throws InvalidConfigException
     */
    private function tableName()
    {
        return Configs::instance()->menuTable;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function safeUp()
    {
        $this->execute('SET foreign_key_checks = 0');
        if ($this->tableExist()) {
            $this->addColumn(
                $this->tableName(), 'cate_id',
                $this->integer()->notNull()->defaultValue(0)->after('id')
                    ->comment('Menu category')
            );
            $this->addForeignKey('FK_AuthMenuCategory', self::tableName(),
                'cate_id', Configs::instance()->menuCateTable, 'id',
                'CASCADE', 'CASCADE'
            );
        }
        $this->execute('SET foreign_key_checks = 1');
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function safeDown()
    {
        $this->execute('SET foreign_key_checks = 0');
        if ($this->tableExist()) {
            $this->dropForeignKey('FK_AuthMenuCategory', $this->tableName());
            $this->dropColumn($this->tableName(), 'cate_id');
        }
        $this->execute('SET foreign_key_checks = 1');
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    protected function tableExist(): bool
    {
        return !($this->db->getTableSchema($this->tableName(), true) === null);
    }
}
