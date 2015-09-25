<?php

use yii\db\Schema;
use yii\db\Migration;

class m150204_124502_alter_table_processedlsr_validationString extends Migration
{
    public function up()
    {
        $sql = "ALTER TABLE `ProcessedLSR` CHANGE `validationString` `validationString` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    }

    public function down()
    {
        echo "m150204_124502_alter_table_processedlsr_validationString cannot be reverted.\n";

        return false;
    }
}
