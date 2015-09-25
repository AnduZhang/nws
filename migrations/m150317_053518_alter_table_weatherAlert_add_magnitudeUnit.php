<?php

use yii\db\Schema;
use yii\db\Migration;

class m150317_053518_alter_table_weatherAlert_add_magnitudeUnit extends Migration
{
    public function up()
    {
        $this->execute('ALTER TABLE `WeatherAlert` ADD `magnitudeUnit` VARCHAR(45) NULL AFTER `magnitude`');
        $this->execute("ALTER TABLE `WeatherAlert` CHANGE `magnitude` `magnitude` DECIMAL(2,1) NULL DEFAULT NULL COMMENT 'this number represents the storm magnitude '");
    }

    public function down()
    {
        echo "m150317_053518_alter_table_weatherAlert_add_magnitudeUnit cannot be reverted.\n";

        return false;
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
