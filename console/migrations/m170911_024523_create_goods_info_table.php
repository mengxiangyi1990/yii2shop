<?php

use yii\db\Migration;

/**
 * Handles the creation of table `goods_info`.
 */
class m170911_024523_create_goods_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('goods_info', [
            'goods_id' => $this->primaryKey()->comment('商品id'),
            'content' => $this->text()->comment('商品描述')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('goods_info');
    }
}
