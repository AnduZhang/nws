<?php

use yii\db\Schema;
use yii\db\Migration;

class m150113_112310_alter_table_users_add_first_last_name extends Migration
{
    public function up()
    {

        $sql = "ALTER TABLE `user` ADD `firstName` VARCHAR(45) NOT NULL AFTER `email`";

        $this->execute($sql);

        $sql = "ALTER TABLE `user` ADD `lastName` VARCHAR(45) NOT NULL AFTER `firstName`";

        $this->execute($sql);


    }

    public function down()
    {
        echo "m150113_112310_alter_table_users_add_first_last_name cannot be reverted.\n";

        return false;
    }
}
