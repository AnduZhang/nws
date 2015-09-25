<?php

use yii\db\Schema;
use yii\db\Migration;

class m150129_090353_change_data_type_in_all_tables extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `WeatherAlert` CHANGE `date` `date` INT(20) NOT NULL COMMENT 'date and time of the alert, provided by the data source (CAP or ASCII)'");
        $this->execute("ALTER TABLE `ProcessedATOMFeeds` CHANGE `updated` `updated` INT(20) NOT NULL COMMENT 'date and time when the feed updated'");
    }

    public function down()
    {
        echo "m150129_090353_change_data_type_in_all_tables cannot be reverted.\n";

        return false;
    }
}
