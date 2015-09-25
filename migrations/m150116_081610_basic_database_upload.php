<?php

use yii\db\Schema;
use yii\db\Migration;

class m150116_081610_basic_database_upload extends Migration
{
    public function up()
    {
        $sql = "

-- -----------------------------------------------------
-- Table `NRESProperty`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `NRESProperty` (
  `id` INT NOT NULL COMMENT 'unique id',
  `streetAddress` VARCHAR(45) NOT NULL COMMENT 'text value of street address',
  `city` VARCHAR(45) NOT NULL COMMENT 'city name',
  `state` VARCHAR(45) NOT NULL COMMENT 'two letter US state' /* comment truncated */ /*e.g CA for California
*/,
  `zipcode` VARCHAR(45) NOT NULL COMMENT 'US based zip codes',
  `client` VARCHAR(45) NOT NULL COMMENT 'client name',
  `latitude` FLOAT NULL COMMENT 'latitude value of the coordinate',
  `longitude` FLOAT NULL COMMENT 'longitude value of the coordinate',
  `status` INT NOT NULL COMMENT '0 = ACTIVE' /* comment truncated */ /*1 = INACTIVE
*/,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'this table stores the data for NRES property entity';


-- -----------------------------------------------------
-- Table `WeatherAlert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WeatherAlert` (
  `id` INT NOT NULL COMMENT 'unique id for the alert',
  `date` DATETIME NOT NULL COMMENT 'date and time of the alert, provided by the data source (CAP or ASCII)',
  `event` INT NOT NULL COMMENT '0 = HURRICANE' /* comment truncated */ /*1 = TORNADO*/,
  `type` INT NOT NULL COMMENT '0 = pre-storm' /* comment truncated */ /*1 = post storm*/,
  `magnitude` DECIMAL(2) NULL COMMENT 'this number represents the storm magnitude ' /* comment truncated */ /*only used if type is “post-storm”*/,
  `severity` VARCHAR(45) NULL COMMENT 'this number represents the storm severity ' /* comment truncated */ /*only used if type is “pre-storm”*/,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
COMMENT = 'this table stores the data for the weather alert entity';


-- -----------------------------------------------------
-- Table `WeatherAlertCoordinates`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WeatherAlertCoordinates` (
  `WeatherAlert_id` INT NOT NULL COMMENT 'alert id',
  `latitude` DECIMAL(2) NOT NULL COMMENT 'latitude coordinates value',
  `longitude` DECIMAL(2) NOT NULL COMMENT 'longitude coordinates value',
  PRIMARY KEY (`WeatherAlert_id`),
  CONSTRAINT `fk_AlertCoordinates_WeatherAlert`
    FOREIGN KEY (`WeatherAlert_id`)
    REFERENCES `WeatherAlert` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This table contains the coordinates for each weather alert. There are two options: single coordinate or group of coordinates (polygon)';


-- -----------------------------------------------------
-- Table `WeatherAlertArea`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `WeatherAlertArea` (
  `id` INT NOT NULL COMMENT 'unique id for the area',
  `WeatherAlert_id` INT NOT NULL COMMENT 'id of the weather alert',
  PRIMARY KEY (`id`, `WeatherAlert_id`),
  INDEX `fk_WeatherAlertArea_WeatherAlert1_idx` (`WeatherAlert_id` ASC),
  CONSTRAINT `fk_WeatherAlertArea_WeatherAlert1`
    FOREIGN KEY (`WeatherAlert_id`)
    REFERENCES `WeatherAlert` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'table stores all the areas for a specific weather alert' /* comment truncated */ /*a single weather alert could own more than one area
each area can be assigned to only one weather alert

*/;


-- -----------------------------------------------------
-- Table `AreaDefinition`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `AreaDefinition` (
  `id` INT NOT NULL COMMENT 'unique id for the point',
  `WeatherAlertArea_id` INT NOT NULL COMMENT 'id of the weather alert area to which this definition record belongs',
  `latitude` FLOAT NOT NULL COMMENT 'latitude value of the coordinate',
  `longitude` FLOAT NOT NULL COMMENT 'longitude value of the coordinate',
  `radius` FLOAT NULL COMMENT 'radius in kilometers for the circle area' /* comment truncated */ /*if this value exists the area is a circle
if the value is EMPTY the area is a polygon*/,
  PRIMARY KEY (`id`, `WeatherAlertArea_id`),
  INDEX `fk_AreaDefinition_WeatherAlertArea1_idx` (`WeatherAlertArea_id` ASC),
  CONSTRAINT `fk_AreaDefinition_WeatherAlertArea1`
    FOREIGN KEY (`WeatherAlertArea_id`)
    REFERENCES `WeatherAlertArea` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'area definition could be a polygon or a circle' /* comment truncated */ /*for polygon the definition has a set of points (latitude, longitude)
for circle the definition has a circle center point (latitude, longitude) and radius (in km)
each record in this table can be assigned to a single area
if a radius value is provided the area is a circle, otherwise a polygon
*/;


-- -----------------------------------------------------
-- Table `UserReadAlerts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `UserReadAlerts` (
  `User_id` INT NOT NULL COMMENT 'user id',
  `WeatherAlert_id` INT NOT NULL COMMENT 'weather alert id',
  `isRead` VARCHAR(45) NOT NULL DEFAULT 0 COMMENT '0 - False' /* comment truncated */ /*1 - True*/,
  PRIMARY KEY (`User_id`, `WeatherAlert_id`),
  INDEX `fk_UserReadedAlerts_WeatherAlert1_idx` (`WeatherAlert_id` ASC),
  CONSTRAINT `fk_UserReadedAlerts_User1`
    FOREIGN KEY (`User_id`)
    REFERENCES `User` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_UserReadedAlerts_WeatherAlert1`
    FOREIGN KEY (`WeatherAlert_id`)
    REFERENCES `WeatherAlert` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'this table stores the list of weather alerts already “read” by a specific ' /* comment truncated */ /*ser
each record represents the event of a user who reads a specific weather alert*/;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
";

        $this->execute($sql);

        $this->execute("ALTER TABLE `WeatherAlert` ADD `identifier` MEDIUMTEXT NOT NULL;");

        $sql = "
CREATE TABLE IF NOT EXISTS `ProcessedATOMFeeds` (
  `feedURL` VARCHAR(45) NOT NULL COMMENT 'the feedURL processed, this URL is supposed to be unique',
  `updated` DATETIME NOT NULL COMMENT 'date and time when the feed updated' /* comment truncated */ /*this value is provided by the ATOM feed content*/,
  PRIMARY KEY (`feedURL`))
ENGINE = InnoDB
COMMENT = 'table stores records for each ATOM feed already processed' /* comment truncated */ /*each record contains the “feedURL” and the “date” when this feed was updated
*/
    ";
        $this->execute($sql);

      $this->execute("ALTER TABLE `ProcessedATOMFeeds` CHANGE `updated` `updated` VARCHAR(45) NOT NULL COMMENT 'date and time when the feed updated'");

      $this->execute("ALTER TABLE `WeatherAlert` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for the alert'");

      $this->execute("ALTER TABLE `WeatherAlertArea` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for the area';");

      $this->execute("ALTER TABLE `WeatherAlert` CHANGE `date` `date` MEDIUMTEXT NOT NULL COMMENT 'date and time of the alert, provided by the data source (CAP or ASCII)'");

      $this->execute("ALTER TABLE `AreaDefinition` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'unique id for the point';");

//      $this->execute("
//
//-- -----------------------------------------------------
//-- Table `ProcessedATOMFeeds`
//-- -----------------------------------------------------
//CREATE TABLE IF NOT EXISTS `ProcessedATOMFeeds` (
//  `feedURL` VARCHAR(45) NOT NULL COMMENT 'the feedURL processed, this URL is supposed to be unique',
//  `updated` DATETIME NOT NULL COMMENT 'date and time when the feed updated' /* comment truncated */ /*this value is provided by the ATOM feed content*/,
//  PRIMARY KEY (`feedURL`))
//ENGINE = InnoDB
//COMMENT = 'table stores records for each ATOM feed already processed' /* comment truncated */ /*each record contains the “feedURL” and the “date” when this feed was updated
//*/;
//
//      ");
    }

    public function down()
    {
        echo "m150116_081610_basic_database_upload cannot be reverted.\n";

        return false;
    }
}
