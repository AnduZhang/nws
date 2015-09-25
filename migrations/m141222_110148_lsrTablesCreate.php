<?php

use yii\db\Schema;
use yii\db\Migration;

class m141222_110148_lsrTablesCreate extends Migration
{
    public function up()
    {
        $sql = '
         CREATE TABLE IF NOT EXISTS `lsrFilesStatus` (
         `id` int(11) NOT NULL,
         `name` varchar(100) NOT NULL,
         `modifiedDate` int(20) NOT NULL
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        ALTER TABLE `lsrFilesStatus`
         ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id_2` (`id`), ADD KEY `id` (`id`);

        ALTER TABLE `lsrFilesStatus`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

        CREATE TABLE IF NOT EXISTS `lsrFilesContent` (
            `id` int(11) NOT NULL,
              `fileId` int(11) NOT NULL,
              `fileContent` longtext,
              `time` int(20) NOT NULL
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            ALTER TABLE `lsrFilesContent`
            ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`), ADD KEY `fileId` (`fileId`), ADD KEY `fileId_2` (`fileId`), ADD KEY `fileId_3` (`fileId`), ADD KEY `fileId_4` (`fileId`);

            ALTER TABLE `lsrFilesContent`
            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

            ALTER TABLE `lsrFilesContent`
ADD CONSTRAINT `lsrFilesContent_ibfk_1` FOREIGN KEY (`fileId`) REFERENCES `lsrFilesStatus` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
        ';

        $this->execute($sql);
    }

    public function down()
    {
        echo "m141222_110148_lsrTablesCreate cannot be reverted.\n";

        return false;
    }
}
