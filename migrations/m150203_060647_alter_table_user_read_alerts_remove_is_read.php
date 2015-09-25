<?php

use yii\db\Schema;
use yii\db\Migration;

class m150203_060647_alter_table_user_read_alerts_remove_is_read extends Migration
{
    public function up()
    {
        $this->execute('ALTER TABLE `UserReadAlerts` DROP `isRead`');
    }

    public function down()
    {
        echo "m150203_060647_alter_table_user_read_alerts_remove_is_read cannot be reverted.\n";

        return false;
    }
}
