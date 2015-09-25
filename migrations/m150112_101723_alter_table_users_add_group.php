<?php

use yii\db\Schema;
use yii\db\Migration;

class m150112_101723_alter_table_users_add_group extends Migration
{
    public function up()
    {
        $sql = "ALTER TABLE `user` ADD `group` VARCHAR(45) NOT NULL DEFAULT 'registered'";
        $this->execute($sql);

    }

    public function down()
    {
        echo "m150112_101723_alter_table_users_add_group cannot be reverted.\n";

        return false;
    }
}
