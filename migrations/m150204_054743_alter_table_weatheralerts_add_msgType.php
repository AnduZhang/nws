<?php

use yii\db\Schema;
use yii\db\Migration;

class m150204_054743_alter_table_weatheralerts_add_msgType extends Migration
{
    public function up()
    {
//        $this->execute("ALTER TABLE `WeatherAlert` ADD `msgType` TINYINT(1) UNSIGNED NULL");
        $this->execute("ALTER TABLE `WeatherAlert` ADD `updates` MEDIUMTEXT NULL");
    }

    public function down()
    {
        echo "m150204_054743_alter_table_weatheralerts_add_msgType cannot be reverted.\n";

        return false;
    }
}