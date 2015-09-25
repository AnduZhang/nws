<?php

use yii\db\Schema;
use yii\db\Migration;

class m150206_060837_alter_table_weatherAlerts_add_status extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `WeatherAlert` ADD `msgType` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `identifier`");
        $this->execute("ALTER TABLE `WeatherAlert` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `msgType`");
    }

    public function down()
    {
        echo "m150206_060837_alter_table_weatherAlerts_add_status cannot be reverted.\n";

        return false;
    }
}
