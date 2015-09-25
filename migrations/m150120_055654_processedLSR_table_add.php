<?php

use yii\db\Schema;
use yii\db\Migration;

class m150120_055654_processedLSR_table_add extends Migration
{
    public function up()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `ProcessedLSR` (
          `id` INT NOT NULL COMMENT 'unique id for this entity',
          `fileName` VARCHAR(45) NOT NULL COMMENT '',
          `validationString` VARCHAR(45) NOT NULL,
          `modifiedDate` INT(20) NOT NULL,
          PRIMARY KEY (`id`))
        ENGINE = InnoDB
        COMMENT = 'table contains a record for each LSR processed' /* comment truncated */ /*the fields are used to identify the LSR already processed*/
        ";

        $this->execute($sql);

        $this->execute("ALTER TABLE `ProcessedLSR` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for this entity'");

        $this->execute("ALTER TABLE `WeatherAlert` CHANGE `identifier` `identifier` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL");
    }

    public function down()
    {
        echo "m150120_055654_processedLSR_table_add cannot be reverted.\n";

        return false;
    }
}
