<?php

use yii\db\Schema;
use yii\db\Migration;

class m150128_082542_alter_table_nresproperties_add_name extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `NRESProperty` ADD `name` VARCHAR(45) NOT NULL AFTER `id`");
        $this->execute("UPDATE `NRESProperty` SET name='Default' where name=''");
    }

    public function down()
    {
        echo "m150128_082542_alter_table_nresproperties_add_name cannot be reverted.\n";

        return false;
    }
}
