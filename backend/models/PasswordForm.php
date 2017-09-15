<?php
namespace backend\models;


use yii\base\Model;

class PasswordForm extends Model{
    public $o_password;
    public $n_password;
    public $r_password;

    public function rules()
    {
        return [
            ['o_password','required','message'=>'旧密码不能为空'],
            ['o_password','validatePassword'],
            ['n_password','required','message'=>'新密码不能为空'],
            ['r_password', 'compare', 'compareAttribute'=>'n_password','message'=>'两次密码不一致'],
        ];
    }
    public function validatePassword(){
        if(!\Yii::$app->security->validatePassword($this->o_password,\Yii::$app->user->identity->password_hash)){
            $this->addError('o_password','旧密码不正确');
        }
    }

    public function attributeLabels()
    {
        return [
            'n_password'=>'新密码',
            'r_password'=>'确认密码',
            'o_password'=>'旧密码',
        ];
    }


}