<?php

use yii\db\Migration;

/**
 * Handles the creation of table `addcolumn_address`.
 */
class m170922_085643_create_addcolumn_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(\frontend\models\Address::tableName(), 'member_id', 'INT(11)  COMMENT "用户ID" AFTER `status`');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('addcolumn_address');
    }
}
