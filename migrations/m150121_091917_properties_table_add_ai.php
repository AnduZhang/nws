<?php

use yii\db\Schema;
use yii\db\Migration;

class m150121_091917_properties_table_add_ai extends Migration
{
    public function up()
    {

        $this->execute("ALTER TABLE `NRESProperty` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id'");

    }

    public function down()
    {
        echo "m150121_091917_properties_table_add_ai cannot be reverted.\n";

        return false;
    }
}
