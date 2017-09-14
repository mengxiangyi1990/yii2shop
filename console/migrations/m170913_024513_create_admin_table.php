<?php

use yii\db\Migration;

/**
 * Handles the creation of table `admin`.
 */
class m170913_024513_create_admin_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('admin', [
            'id' => $this->primaryKey(),
            'username'=>$this->string(),
            'auth_key'=>$this->string(),
            'password_hash'=>$this->string(),
            'password_reset_token'=>$this->string(),
            'email'=>$this->string(),
            'status'=>$this->smallInteger(6),
            'created_at'=>$this->integer(),
            'updated_at'=>$this->integer(),
            'last_login_time'=>$this->integer(),
            'last_login_ip'=>$this->string()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('admin');
    }
}
