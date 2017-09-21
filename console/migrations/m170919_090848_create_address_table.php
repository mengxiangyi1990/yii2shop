<?php

use yii\db\Migration;

/**
 * Handles the creation of table `address`.
 */
class m170919_090848_create_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('address', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->comment('收货人'),
            'province' =>$this->string(20)->comment('省'),
            'city' =>$this->string(20)->comment('市'),
            'area' =>$this->string(20)->comment('县'),
            'address' =>$this->string(255)->comment('详细地址'),
            'tel' =>$this->integer()->comment('电话号码'),
            'status' => $this->integer()->comment('(0默认地址,1不是)')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('address');
    }
}
