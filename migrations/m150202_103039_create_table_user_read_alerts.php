<?php

use yii\db\Schema;
use yii\db\Migration;

class m150202_103039_create_table_user_read_alerts extends Migration
{
    public function up()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `UserReadAlerts` (
          `User_id` INT(11) NOT NULL COMMENT 'user id',
          `WeatherAlert_id` INT(11) NOT NULL COMMENT 'weather alert id',
          `isRead` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - False' /* comment truncated */ /*1 - True*/,
          PRIMARY KEY (`User_id`, `WeatherAlert_id`),
          INDEX `fk_UserReadedAlerts_WeatherAlert1_idx` (`WeatherAlert_id` ASC),
          CONSTRAINT `fk_UserReadedAlerts_User1`
            FOREIGN KEY (`User_id`)
            REFERENCES `user` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
          CONSTRAINT `fk_UserReadedAlerts_WeatherAlert1`
            FOREIGN KEY (`WeatherAlert_id`)
            REFERENCES `WeatherAlert` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION)
        ENGINE = InnoDB
        ";

        $this->execute($sql);
    }

    public function down()
    {
        echo "m150202_103039_create_table_user_read_alerts cannot be reverted.\n";

        return false;
    }
}
